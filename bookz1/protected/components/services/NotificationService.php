<?php

/**
 * NotificationService handles notification sending for book events.
 * 
 * Responsibilities:
 * - Send SMS notifications to subscribers when new book is created
 * - Collect subscriber phone numbers from book's authors
 * - Format notification messages
 * - Log notification results
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class NotificationService
{
    /**
     * SmsPilot component name in Yii app.
     * @var string
     */
    protected $smsComponentName = 'smsPilot';
    
    /**
     * Log category for notifications.
     * @var string
     */
    protected $logCategory = 'application.services.NotificationService';
    
    /**
     * Send new book notification to all subscribers of the book's authors.
     * 
     * @param Book $book The newly created book
     * @return array Result array with 'sent', 'failed', 'errors' keys
     */
    public function sendNewBookNotification(Book $book)
    {
        $result = array(
            'sent' => 0,
            'failed' => 0,
            'errors' => array(),
        );
        
        try {
            // Reload book with authors to ensure we have the relationships
            $book = Book::model()->with('authors')->findByPk($book->id);
            
            if (empty($book->authors)) {
                Yii::log("No authors found for book ID {$book->id}, skipping SMS notifications", 
                    CLogger::LEVEL_INFO, $this->logCategory);
                return $result;
            }
            
            // Get subscriber phones
            $phones = $this->getSubscriberPhonesForBook($book);
            
            if (empty($phones)) {
                Yii::log("No subscribers found for book ID {$book->id} authors, skipping SMS notifications", 
                    CLogger::LEVEL_INFO, $this->logCategory);
                return $result;
            }
            
            // Get author names for message
            $authorNames = array();
            foreach ($book->authors as $author) {
                $authorNames[] = $author->full_name;
            }
            $authorNamesStr = implode(', ', $authorNames);
            
            // Format message
            $message = $this->formatNewBookMessage($book, $authorNamesStr);
            
            // Send notifications
            $sendResult = $this->sendSmsNotification($phones, $message, $book->isbn);
            
            // Process result
            $result['sent'] = $sendResult['sent'];
            $result['failed'] = $sendResult['failed'];
            $result['errors'] = $sendResult['errors'];
            
            // Log results
            $this->logNotificationResult($result, $book->title);
            
        } catch (Exception $e) {
            Yii::log("Failed to send SMS notifications for book ID {$book->id}: " . $e->getMessage(), 
                CLogger::LEVEL_ERROR, $this->logCategory);
            $result['errors'][] = array('error' => $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Get subscriber phones for a book's authors.
     * 
     * @param Book $book The book
     * @return array Array of unique phone numbers
     */
    public function getSubscriberPhonesForBook(Book $book)
    {
        $phones = array();
        
        foreach ($book->authors as $author) {
            $authorPhones = $this->getAuthorSubscriberPhones($author->id);
            $phones = array_merge($phones, $authorPhones);
        }
        
        // Remove duplicates
        return array_unique($phones);
    }
    
    /**
     * Get subscriber phones for an author.
     * 
     * @param integer $authorId The author ID
     * @return array Array of phone numbers
     */
    public function getAuthorSubscriberPhones($authorId)
    {
        $subscription = new UserSubscription();
        return $subscription->getAuthorSubscriberPhones($authorId);
    }
    
    /**
     * Format notification message for new book.
     * 
     * @param Book $book The book
     * @param string $authorNames Comma-separated author names
     * @return string Formatted message
     */
    public function formatNewBookMessage(Book $book, $authorNames)
    {
        $message = sprintf(
            'Новая книга от %s: "%s"',
            $authorNames,
            $book->title
        );
        
        if (!empty($book->isbn)) {
            $message .= sprintf(' (ISBN: %s)', $book->isbn);
        }
        
        return $message;
    }
    
    /**
     * Send SMS notification to multiple phones.
     * 
     * @param array $phones Array of phone numbers
     * @param string $message The message text
     * @param string|null $isbn Optional ISBN for tracking
     * @return array Result with 'sent', 'failed', 'errors'
     */
    protected function sendSmsNotification($phones, $message, $isbn = null)
    {
        $result = array(
            'sent' => 0,
            'failed' => 0,
            'errors' => array(),
        );
        
        // Get SMS component
        if (!Yii::app()->hasComponent($this->smsComponentName)) {
            Yii::log("SMS component not configured", CLogger::LEVEL_ERROR, $this->logCategory);
            $result['errors'][] = array('error' => 'SMS component not configured');
            return $result;
        }
        
        $smsComponent = Yii::app()->{$this->smsComponentName};
        
        // Send to each phone individually for better error handling
        foreach ($phones as $phone) {
            try {
                $success = $smsComponent->send($phone, $message);
                
                if ($success) {
                    $result['sent']++;
                } else {
                    $result['failed']++;
                    $result['errors'][] = array(
                        'phone' => $phone,
                        'error' => $smsComponent->getLastError(),
                    );
                }
            } catch (Exception $e) {
                $result['failed']++;
                $result['errors'][] = array(
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Log notification results.
     * 
     * @param array $result The notification result
     * @param string $bookTitle The book title
     */
    public function logNotificationResult($result, $bookTitle)
    {
        if ($result['sent'] > 0) {
            Yii::log("SMS notifications sent for new book '{$bookTitle}': {$result['sent']} successful, {$result['failed']} failed", 
                CLogger::LEVEL_INFO, $this->logCategory);
        }
        
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                $phone = isset($error['phone']) ? $error['phone'] : 'unknown';
                $errorMsg = isset($error['error']) ? $error['error'] : 'unknown error';
                Yii::log("SMS notification error for phone {$phone}: {$errorMsg}", 
                    CLogger::LEVEL_WARNING, $this->logCategory);
            }
        }
    }
    
    /**
     * Send a test SMS notification.
     * 
     * @param string $phone The phone number
     * @param string $message The message
     * @return boolean Whether the SMS was sent
     */
    public function sendTestNotification($phone, $message)
    {
        if (!Yii::app()->hasComponent($this->smsComponentName)) {
            return false;
        }
        
        return Yii::app()->{$this->smsComponentName}->send($phone, $message);
    }
    
    /**
     * Set SMS component name.
     * 
     * @param string $name The component name
     */
    public function setSmsComponentName($name)
    {
        $this->smsComponentName = $name;
    }
    
    /**
     * Get SMS component name.
     * 
     * @return string
     */
    public function getSmsComponentName()
    {
        return $this->smsComponentName;
    }
}
