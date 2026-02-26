<?php

/**
 * AuthorService handles author-related operations.
 * 
 * Responsibilities:
 * - Get author with books (for view page)
 * - Create author inline (AJAX)
 * - Get subscription status for author
 * - Get available years for filtering
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class AuthorService
{
    /**
     * Default page size for books list.
     * @var integer
     */
    protected $defaultPageSize = 10;
    
    /**
     * Log category.
     * @var string
     */
    protected $logCategory = 'application.services.AuthorService';
    
    /**
     * Get author with books and pagination.
     * 
     * @param integer $authorId The author ID
     * @param integer $pageSize Number of books per page
     * @return array Array with 'author' and 'booksProvider' keys
     */
    public function getAuthorWithBooks($authorId, $pageSize = 10)
    {
        $result = array(
            'author' => null,
            'booksProvider' => null,
        );
        
        // Load author
        $author = Author::model()->findByPk($authorId);
        if ($author === null) {
            return $result;
        }
        
        $result['author'] = $author;
        
        // Create books data provider with custom query
        $criteria = new CDbCriteria(array(
            'with' => array('authors'),
            'join' => 'JOIN book_authors ba ON t.id = ba.book_id',
            'condition' => 'ba.author_id = :authorId',
            'params' => array(':authorId' => $authorId),
            'order' => 't.year_published DESC',
        ));
        
        $result['booksProvider'] = new CActiveDataProvider('Book', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
        
        return $result;
    }
    
    /**
     * Create an author inline (for AJAX).
     * 
     * @param string $fullName The author's full name
     * @return array Result array with 'success', 'author', 'errors' keys
     */
    public function createInlineAuthor($fullName)
    {
        $result = array(
            'success' => false,
            'author' => null,
            'errors' => array(),
        );
        
        $author = new Author();
        $author->full_name = $fullName;
        
        if ($author->save()) {
            $result['success'] = true;
            $result['author'] = array(
                'id' => $author->id,
                'full_name' => $author->full_name,
            );
            
            Yii::log("Inline author created: ID={$author->id}, Name='{$author->full_name}'", 
                CLogger::LEVEL_INFO, $this->logCategory);
        } else {
            $result['errors'] = $author->getErrors();
        }
        
        return $result;
    }
    
    /**
     * Get subscription status for an author.
     * 
     * @param integer $authorId The author ID
     * @param integer|null $userId The user ID (optional)
     * @param string|null $phone The phone number (optional)
     * @return array Result with 'isSubscribed', 'subscription' keys
     */
    public function getSubscriptionStatus($authorId, $userId = null, $phone = null)
    {
        $result = array(
            'isSubscribed' => false,
            'subscription' => null,
        );
        
        // For authenticated users
        if ($userId !== null) {
            $subscription = UserSubscription::model()->findByAttributes(array(
                'user_id' => $userId,
                'author_id' => $authorId,
            ));
            
            if ($subscription !== null) {
                $result['isSubscribed'] = true;
                $result['subscription'] = $subscription;
            }
        }
        
        return $result;
    }
    
    /**
     * Get all authors for dropdown/list.
     * 
     * @param string $order OrderBy clause
     * @return Author[]
     */
    public function getAllAuthors($order = 'full_name ASC')
    {
        return Author::model()->findAll(array(
            'order' => $order,
        ));
    }
    
    /**
     * Get available years for filter dropdown.
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
     * Search authors with pagination.
     * 
     * @param Author $model The author model with search criteria
     * @return CActiveDataProvider Data provider
     */
    public function searchAuthors(Author $model = null)
    {
        if ($model === null) {
            $model = new Author();
        }
        
        return $model->search();
    }
    
    /**
     * Get author by ID.
     * 
     * @param integer $id The author ID
     * @return Author|null
     */
    public function getAuthor($id)
    {
        return Author::model()->findByPk($id);
    }
    
    /**
     * Get TOP authors by year.
     * 
     * @param integer|null $year The year to filter by
     * @return array Authors data
     */
    public function getTopAuthorsByYear($year = null)
    {
        return Author::model()->getTopAuthorsByYear($year);
    }
    
    /**
     * Set default page size.
     * 
     * @param integer $size The page size
     */
    public function setDefaultPageSize($size)
    {
        $this->defaultPageSize = (int) $size;
    }
    
    /**
     * Get default page size.
     * 
     * @return integer
     */
    public function getDefaultPageSize()
    {
        return $this->defaultPageSize;
    }
}
