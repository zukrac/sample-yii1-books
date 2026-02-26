<?php

/**
 * BookManagementService handles complex book operations.
 * 
 * Responsibilities:
 * - Create book with author associations
 * - Update book with cover image handling
 * - Delete book with all related data
 * - Check ownership
 * - Get books with filters
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class BookManagementService
{
    /**
     * FileUploadService instance.
     * @var FileUploadService
     */
    protected $fileUploadService;
    
    /**
     * NotificationService instance.
     * @var NotificationService
     */
    protected $notificationService;
    
    /**
     * Log category.
     * @var string
     */
    protected $logCategory = 'application.services.BookManagementService';
    
    /**
     * Constructor.
     * 
     * @param FileUploadService $fileUploadService Optional file service
     * @param NotificationService $notificationService Optional notification service
     */
    public function __construct(
        FileUploadService $fileUploadService = null, 
        NotificationService $notificationService = null
    ) {
        $this->fileUploadService = $fileUploadService ?: new FileUploadService();
        $this->notificationService = $notificationService ?: new NotificationService();
    }
    
    /**
     * Create a new book with authors and cover image.
     * 
     * @param Book $book The book model (should be populated with attributes)
     * @param array $authorIds Array of author IDs
     * @param CUploadedFile $coverImage Optional uploaded cover image
     * @param integer $createdBy User ID who is creating the book
     * @return array Result array with 'success', 'book', 'errors' keys
     */
    public function createBook(Book $book, $authorIds, CUploadedFile $coverImage = null, $createdBy = null)
    {
        $result = array(
            'success' => false,
            'book' => null,
            'errors' => array(),
        );
        
        try {
            // Set created by
            if ($createdBy !== null) {
                $book->created_by = $createdBy;
            }
            
            // Handle cover image upload
            if ($coverImage !== null) {
                $coverPath = $this->fileUploadService->saveCoverImage($coverImage);
                if ($coverPath === false) {
                    $book->addError('cover_image', $this->fileUploadService->getLastError());
                    $result['errors'] = $book->getErrors();
                    return $result;
                }
                $book->cover_image = $coverPath;
            }
            
            // Save the book
            if (!$book->save()) {
                $result['errors'] = $book->getErrors();
                return $result;
            }
            
            // Save author associations
            if (!empty($authorIds) && is_array($authorIds)) {
                $book->updateAuthorAssociations($authorIds);
            }
            
            // Send notifications
            $this->notificationService->sendNewBookNotification($book);
            
            $result['success'] = true;
            $result['book'] = $book;
            
            Yii::log("Book created: ID={$book->id}, Title='{$book->title}'", 
                CLogger::LEVEL_INFO, $this->logCategory);
            
        } catch (Exception $e) {
            Yii::log("Failed to create book: " . $e->getMessage(), 
                CLogger::LEVEL_ERROR, $this->logCategory);
            $result['errors'] = array('exception' => $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Update an existing book with authors and cover image.
     * 
     * @param Book $book The book model (should be loaded)
     * @param array $authorIds Array of author IDs
     * @param CUploadedFile $coverImage Optional new uploaded cover image
     * @return array Result array with 'success', 'book', 'errors' keys
     */
    public function updateBook(Book $book, $authorIds, CUploadedFile $coverImage = null)
    {
        $result = array(
            'success' => false,
            'book' => null,
            'errors' => array(),
        );
        
        try {
            $oldCover = $book->cover_image;
            
            // Handle cover image upload
            if ($coverImage !== null) {
                $coverPath = $this->fileUploadService->saveCoverImage($coverImage, $oldCover);
                if ($coverPath === false) {
                    $book->addError('cover_image', $this->fileUploadService->getLastError());
                    $result['errors'] = $book->getErrors();
                    return $result;
                }
                $book->cover_image = $coverPath;
            }
            
            // Save the book
            if (!$book->save()) {
                $result['errors'] = $book->getErrors();
                return $result;
            }
            
            // Update author associations
            if ($authorIds !== null && is_array($authorIds)) {
                $book->updateAuthorAssociations($authorIds);
            }
            
            $result['success'] = true;
            $result['book'] = $book;
            
            Yii::log("Book updated: ID={$book->id}, Title='{$book->title}'", 
                CLogger::LEVEL_INFO, $this->logCategory);
            
        } catch (Exception $e) {
            Yii::log("Failed to update book: " . $e->getMessage(), 
                CLogger::LEVEL_ERROR, $this->logCategory);
            $result['errors'] = array('exception' => $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Delete a book including its cover image.
     * 
     * @param Book $book The book model to delete
     * @return boolean Whether deletion was successful
     */
    public function deleteBook(Book $book)
    {
        try {
            $bookId = $book->id;
            $title = $book->title;
            
            // Delete cover image if exists
            if (!empty($book->cover_image)) {
                $this->fileUploadService->deleteCoverImage($book->cover_image);
            }
            
            // Delete the book (cascades to book_authors)
            $success = $book->delete();
            
            if ($success) {
                Yii::log("Book deleted: ID={$bookId}, Title='{$title}'", 
                    CLogger::LEVEL_INFO, $this->logCategory);
            }
            
            return $success;
            
        } catch (Exception $e) {
            Yii::log("Failed to delete book: " . $e->getMessage(), 
                CLogger::LEVEL_ERROR, $this->logCategory);
            return false;
        }
    }
    
    /**
     * Check if user can modify a book.
     * 
     * @param Book $book The book model
     * @param integer $userId The user ID to check
     * @return boolean Whether the user can modify
     */
    public function canModify(Book $book, $userId)
    {
        return $book->canModify($userId);
    }
    
    /**
     * Get books with filters.
     * 
     * @param array $filters Filter array with keys: author_id, year, search
     * @return CActiveDataProvider Data provider
     */
    public function getBooksWithFilters($filters = array())
    {
        return Book::model()->searchWithFilters($filters);
    }
    
    /**
     * Get all authors for dropdown.
     * 
     * @return Author[] Array of authors
     */
    public function getAllAuthors()
    {
        return Author::model()->findAll(array('order' => 'full_name ASC'));
    }
    
    /**
     * Get all available years for filtering.
     * 
     * @return array Array of years
     */
    public function getAvailableYears()
    {
        return Yii::app()->db->createCommand()
            ->selectDistinct('year_published')
            ->from('books')
            ->order('year_published DESC')
            ->queryColumn();
    }
    
    /**
     * Get file upload service.
     * 
     * @return FileUploadService
     */
    public function getFileUploadService()
    {
        return $this->fileUploadService;
    }
    
    /**
     * Get notification service.
     * 
     * @return NotificationService
     */
    public function getNotificationService()
    {
        return $this->notificationService;
    }
}
