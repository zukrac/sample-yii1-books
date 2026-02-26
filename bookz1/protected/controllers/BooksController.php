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
Yii::import('application.components.services.BookManagementService');
Yii::import('application.components.services.FileUploadService');
Yii::import('application.components.services.NotificationService');
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
        $filters = array(
            'author_id' => isset($_GET['author_id']) && !empty($_GET['author_id']) ? (int)$_GET['author_id'] : null,
            'year' => isset($_GET['year']) && !empty($_GET['year']) ? (int)$_GET['year'] : null,
            'search' => isset($_GET['search']) && !empty($_GET['search']) ? trim($_GET['search']) : null,
        );

        $bookService = new BookManagementService();
        $dataProvider = $bookService->getBooksWithFilters($filters);

        // Get all authors for filter dropdown
        $authors = $bookService->getAllAuthors();

        // Get distinct years for filter dropdown
        $years = $bookService->getAvailableYears();

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
            'isOwner' => $book->canModify(Yii::app()->user->id),
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

            // Handle cover image upload
            $coverImage = CUploadedFile::getInstance($book, 'cover_image');
            
            $bookService = new BookManagementService();
            $authorIds = isset($_POST['Book']['authorIds']) ? $_POST['Book']['authorIds'] : array();
            
            $result = $bookService->createBook(
                $book, 
                $authorIds, 
                $coverImage, 
                Yii::app()->user->id
            );

            if ($result['success']) {
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
        $bookService = new BookManagementService();
        if (!$bookService->canModify($book, Yii::app()->user->id) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'You are not authorized to edit this book.');
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($book);

        if (isset($_POST['Book'])) {
            $book->attributes = $_POST['Book'];

            // Handle cover image upload
            $coverImage = CUploadedFile::getInstance($book, 'cover_image');
            
            $authorIds = isset($_POST['Book']['authorIds']) ? $_POST['Book']['authorIds'] : null;
            
            $result = $bookService->updateBook($book, $authorIds, $coverImage);

            if ($result['success']) {
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
        $bookService = new BookManagementService();
        if (!$bookService->canModify($book, Yii::app()->user->id) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'You are not authorized to delete this book.');
        }

        $title = $book->title;

        $bookService->deleteBook($book);

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
}
