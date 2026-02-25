<?php

/**
 * BooksController handles book CRUD operations.
 * 
 * Actions:
 * - index: List books with pagination and filters
 * - view: Book detail page
 * - create: Create new book (authenticated users only)
 * - update: Edit book (owner only)
 * - delete: Delete book (owner only)
 * 
 * @package BookManagementSystem
 * @subpackage controllers
 */
class BooksController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * Uses RBAC roles for authorization.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            // Allow all users (guests and authenticated) to view books
            array('allow',
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            // Allow authenticated users to create books
            array('allow',
                'actions' => array('create'),
                'roles' => array('authenticated_user'),
            ),
            // Allow authenticated users to update/delete their own books
            // Additional ownership check is done in the action
            array('allow',
                'actions' => array('update', 'delete'),
                'roles' => array('authenticated_user'),
            ),
            // Deny all other actions
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Lists all books with pagination and filters.
     * Access: Public (Guest and Authenticated Users)
     */
    public function actionIndex()
    {
        $criteria = new CDbCriteria(array(
            'with' => array('authors'),
            'order' => 't.created_at DESC',
        ));

        // Filter by author
        if (isset($_GET['author_id']) && !empty($_GET['author_id'])) {
            $criteria->addCondition('authors.id = :authorId');
            $criteria->params[':authorId'] = (int)$_GET['author_id'];
        }

        // Filter by year
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $criteria->addCondition('t.year_published = :year');
            $criteria->params[':year'] = (int)$_GET['year'];
        }

        // Search by title or ISBN
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $criteria->addCondition('t.title LIKE :search OR t.isbn LIKE :search');
            $criteria->params[':search'] = '%' . $search . '%';
        }

        $dataProvider = new CActiveDataProvider('Book', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));

        // Get all authors for filter dropdown
        $authors = Author::model()->findAll(array('order' => 'full_name ASC'));

        // Get distinct years for filter dropdown
        $years = Yii::app()->db->createCommand()
            ->selectDistinct('year_published')
            ->from('books')
            ->order('year_published DESC')
            ->queryColumn();

        $this->render('index', array(
            'dataProvider' => $dataProvider,
            'authors' => $authors,
            'years' => $years,
        ));
    }

    /**
     * Displays a single book's details.
     * Access: Public (Guest and Authenticated Users)
     * @param integer $id the ID of the book to display
     */
    public function actionView($id)
    {
        $book = $this->loadModel($id);

        $this->render('view', array(
            'book' => $book,
            'isOwner' => $this->isBookOwner($book),
        ));
    }

    /**
     * Creates a new book.
     * Access: Authenticated Users only
     */
    public function actionCreate()
    {
        $book = new Book();

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($book);

        if (isset($_POST['Book'])) {
            $book->attributes = $_POST['Book'];
            $book->created_by = Yii::app()->user->id;

            // Handle cover image upload with security validation
            $coverImage = CUploadedFile::getInstance($book, 'cover_image');
            if ($coverImage !== null) {
                // Validate file type - only allow image files
                $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                $extension = strtolower($coverImage->extensionName);
                
                if (!in_array($extension, $allowedExtensions)) {
                    $book->addError('cover_image', 'Only image files (JPG, PNG, GIF, WEBP) are allowed.');
                } elseif (!in_array($coverImage->type, $allowedMimeTypes)) {
                    $book->addError('cover_image', 'Invalid file type. Only image files are allowed.');
                } elseif (@getimagesize($coverImage->tempName) === false) {
                    // Validate actual image content to prevent MIME spoofing
                    $book->addError('cover_image', 'File is not a valid image.');
                } else {
                    $fileName = 'book_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = Yii::getPathOfAlias('webroot.uploads.covers') . DIRECTORY_SEPARATOR . $fileName;
                    if ($coverImage->saveAs($uploadPath)) {
                        $book->cover_image = '/uploads/covers/' . $fileName;
                    }
                }
            }

            if ($book->save()) {
                // Save book-author relationships
                if (isset($_POST['Book']['authorIds']) && is_array($_POST['Book']['authorIds'])) {
                    $this->saveBookAuthors($book->id, $_POST['Book']['authorIds']);
                }

                // Send SMS notifications to subscribers
                $this->sendNewBookNotifications($book);

                Yii::app()->user->setFlash('success', 'Book "' . $book->title . '" has been created successfully.');
                $this->redirect(array('view', 'id' => $book->id));
            }
        }

        // Get all authors for multi-select
        $authors = Author::model()->findAll(array('order' => 'full_name ASC'));

        $this->render('create', array(
            'book' => $book,
            'authors' => $authors,
        ));
    }

    /**
     * Updates an existing book.
     * Access: Book owner only (or admin)
     * @param integer $id the ID of the book to update
     */
    public function actionUpdate($id)
    {
        $book = $this->loadModel($id);

        // Check ownership - only owner or admin can edit
        if (!$this->isBookOwner($book) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'You are not authorized to edit this book.');
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($book);

        if (isset($_POST['Book'])) {
            $book->attributes = $_POST['Book'];

            // Handle cover image upload with security validation
            $coverImage = CUploadedFile::getInstance($book, 'cover_image');
            if ($coverImage !== null) {
                // Validate file type - only allow image files
                $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                $extension = strtolower($coverImage->extensionName);
                
                if (!in_array($extension, $allowedExtensions)) {
                    $book->addError('cover_image', 'Only image files (JPG, PNG, GIF, WEBP) are allowed.');
                } elseif (!in_array($coverImage->type, $allowedMimeTypes)) {
                    $book->addError('cover_image', 'Invalid file type. Only image files are allowed.');
                } elseif (@getimagesize($coverImage->tempName) === false) {
                    // Validate actual image content to prevent MIME spoofing
                    $book->addError('cover_image', 'File is not a valid image.');
                } else {
                    $fileName = 'book_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = Yii::getPathOfAlias('webroot.uploads.covers') . DIRECTORY_SEPARATOR . $fileName;
                    if ($coverImage->saveAs($uploadPath)) {
                        // Delete old cover AFTER successful upload (avoid race condition)
                        $oldCover = $book->cover_image;
                        $book->cover_image = '/uploads/covers/' . $fileName;
                        if (!empty($oldCover)) {
                            $oldCoverPath = Yii::getPathOfAlias('webroot') . $oldCover;
                            if (file_exists($oldCoverPath) && !@unlink($oldCoverPath)) {
                                Yii::log("Failed to delete old cover image: $oldCoverPath", CLogger::LEVEL_WARNING);
                            }
                        }
                    }
                }
            }

            if ($book->save()) {
                // Update book-author relationships
                BookAuthor::model()->deleteAllByAttributes(array('book_id' => $book->id));
                if (isset($_POST['Book']['authorIds']) && is_array($_POST['Book']['authorIds'])) {
                    $this->saveBookAuthors($book->id, $_POST['Book']['authorIds']);
                }

                Yii::app()->user->setFlash('success', 'Book "' . $book->title . '" has been updated successfully.');
                $this->redirect(array('view', 'id' => $book->id));
            }
        }

        // Get all authors for multi-select
        $authors = Author::model()->findAll(array('order' => 'full_name ASC'));

        // Get current author IDs
        $book->authorIds = CHtml::listData($book->authors, 'id', 'id');

        $this->render('update', array(
            'book' => $book,
            'authors' => $authors,
        ));
    }

    /**
     * Deletes a book.
     * Access: Book owner only (or admin)
     * @param integer $id the ID of the book to delete
     */
    public function actionDelete($id)
    {
        $book = $this->loadModel($id);

        // Check ownership - only owner or admin can delete
        if (!$this->isBookOwner($book) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'You are not authorized to delete this book.');
        }

        $title = $book->title;

        // Delete cover image if exists
        if (!empty($book->cover_image)) {
            $coverPath = Yii::getPathOfAlias('webroot') . $book->cover_image;
            if (file_exists($coverPath) && !@unlink($coverPath)) {
                Yii::log("Failed to delete cover image: $coverPath", CLogger::LEVEL_WARNING);
            }
        }

        $book->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            Yii::app()->user->setFlash('success', 'Book "' . $title . '" has been deleted successfully.');
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Book the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Book::model()->with('authors', 'creator')->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested book does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Book $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'book-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Check if current user is the owner of the book.
     * @param Book $book the book model
     * @return boolean whether the current user is the owner
     */
    protected function isBookOwner($book)
    {
        return !Yii::app()->user->isGuest && (int)Yii::app()->user->id === (int)$book->created_by;
    }

    /**
     * Save book-author relationships.
     * @param integer $bookId the book ID
     * @param array $authorIds array of author IDs
     */
    protected function saveBookAuthors($bookId, $authorIds)
    {
        $order = 0;
        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $bookId;
            $bookAuthor->author_id = (int)$authorId;
            $bookAuthor->author_order = $order++;
            $bookAuthor->save();
        }
    }

    /**
     * Send SMS notifications to subscribers when a new book is created.
     * @param Book $book the newly created book
     */
    protected function sendNewBookNotifications($book)
    {
        try {
            // Reload book with authors to ensure we have the relationships
            $book = Book::model()->with('authors')->findByPk($book->id);
            
            if (empty($book->authors)) {
                Yii::log("No authors found for book ID {$book->id}, skipping SMS notifications", CLogger::LEVEL_INFO, 'application.controllers.BooksController');
                return;
            }

            // Collect all unique phone numbers from subscribers of all book authors
            $phones = array();
            $authorNames = array();
            $subscription = new UserSubscription();
            
            foreach ($book->authors as $author) {
                $authorNames[] = $author->full_name;
                
                // Get subscriber phones for this author
                $authorPhones = $subscription->getAuthorSubscriberPhones($author->id);
                $phones = array_merge($phones, $authorPhones);
            }

            // Remove duplicates
            $phones = array_unique($phones);

            if (empty($phones)) {
                Yii::log("No subscribers found for book ID {$book->id} authors, skipping SMS notifications", CLogger::LEVEL_INFO, 'application.controllers.BooksController');
                return;
            }

            // Format author names
            $authorNamesStr = implode(', ', $authorNames);

            // Send notifications via SMS Pilot
            $result = Yii::app()->smsPilot->sendNewBookNotification(
                $phones,
                $book->title,
                $authorNamesStr,
                $book->isbn
            );

            // Log results
            if ($result['sent'] > 0) {
                Yii::log("SMS notifications sent for new book '{$book->title}': {$result['sent']} successful, {$result['failed']} failed", 
                         CLogger::LEVEL_INFO, 'application.controllers.BooksController');
            }
            
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    Yii::log("SMS notification error for phone {$error['phone']}: {$error['error']}", 
                             CLogger::LEVEL_WARNING, 'application.controllers.BooksController');
                }
            }

        } catch (Exception $e) {
            // Log error but don't interrupt the book creation flow
            Yii::log("Failed to send SMS notifications for book ID {$book->id}: " . $e->getMessage(), 
                     CLogger::LEVEL_ERROR, 'application.controllers.BooksController');
        }
    }
}
