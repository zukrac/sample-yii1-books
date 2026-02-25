<?php

/**
 * BookNotificationCommand is a console command for sending SMS notifications.
 * 
 * This command can be used to send notifications about new books to subscribers
 * either for specific books or for all recently created books.
 * 
 * Usage examples:
 *   ./yiic bookNotification notify --bookId=123
 *   ./yiic bookNotification notifyRecent --hours=24
 *   ./yiic bookNotification test --phone=79087964781
 * 
 * @package BookManagementSystem
 * @subpackage commands
 */
class BookNotificationCommand extends CConsoleCommand
{
    /**
     * Send SMS notification for a specific book.
     * 
     * @param integer $bookId The book ID to send notifications for.
     * @return int Exit code (0 for success, 1 for error).
     */
    public function actionNotify($bookId = null)
    {
        if ($bookId === null) {
            echo "Error: bookId parameter is required.\n";
            echo "Usage: ./yiic bookNotification notify --bookId=123\n";
            return 1;
        }

        $book = Book::model()->with('authors')->findByPk($bookId);
        if ($book === null) {
            echo "Error: Book with ID {$bookId} not found.\n";
            return 1;
        }

        echo "Sending notifications for book: {$book->title}\n";
        
        $result = $this->sendBookNotifications($book);
        
        echo "Results: {$result['sent']} sent, {$result['failed']} failed\n";
        
        if (!empty($result['errors'])) {
            echo "Errors:\n";
            foreach ($result['errors'] as $error) {
                echo "  - Phone {$error['phone']}: {$error['error']}\n";
            }
            return 1;
        }
        
        return 0;
    }

    /**
     * Send SMS notifications for all books created in the last N hours.
     * Useful for scheduled/cron processing.
     * 
     * @param integer $hours Number of hours to look back (default: 24).
     * @return int Exit code (0 for success, 1 for error).
     */
    public function actionNotifyRecent($hours = 24)
    {
        $hours = (int)$hours;
        if ($hours <= 0) {
            echo "Error: hours must be a positive integer.\n";
            return 1;
        }

        echo "Finding books created in the last {$hours} hours...\n";

        $criteria = new CDbCriteria();
        $criteria->with = array('authors');
        $criteria->addCondition('t.created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)');
        $criteria->params[':hours'] = $hours;
        $criteria->order = 't.created_at DESC';

        $books = Book::model()->findAll($criteria);

        if (empty($books)) {
            echo "No books found in the specified time period.\n";
            return 0;
        }

        echo "Found " . count($books) . " book(s) to process.\n\n";

        $totalSent = 0;
        $totalFailed = 0;
        $allErrors = array();

        foreach ($books as $book) {
            echo "Processing book ID {$book->id}: {$book->title}\n";
            $result = $this->sendBookNotifications($book);
            $totalSent += $result['sent'];
            $totalFailed += $result['failed'];
            $allErrors = array_merge($allErrors, $result['errors']);
            echo "  Sent: {$result['sent']}, Failed: {$result['failed']}\n\n";
        }

        echo "=== Summary ===\n";
        echo "Total sent: {$totalSent}\n";
        echo "Total failed: {$totalFailed}\n";

        if (!empty($allErrors)) {
            echo "Errors:\n";
            foreach ($allErrors as $error) {
                echo "  - Phone {$error['phone']}: {$error['error']}\n";
            }
        }

        return ($totalFailed > 0) ? 1 : 0;
    }

    /**
     * Test SMS functionality by sending a test message.
     * 
     * @param string $phone Phone number to send test message to.
     * @return int Exit code (0 for success, 1 for error).
     */
    public function actionTest($phone = null)
    {
        if ($phone === null) {
            echo "Error: phone parameter is required.\n";
            echo "Usage: ./yiic bookNotification test --phone=79087964781\n";
            return 1;
        }

        // Validate phone format
        if (!preg_match('/^\d{10,15}$/', $phone)) {
            echo "Error: Phone number must be 10-15 digits.\n";
            return 1;
        }

        echo "Sending test SMS to {$phone}...\n";

        $message = "Test SMS from Book Management System. Sent at " . date('Y-m-d H:i:s');

        if (Yii::app()->smsPilot->send($phone, $message)) {
            echo "SMS sent successfully!\n";
            echo "Cost: " . Yii::app()->smsPilot->getLastCost() . " руб.\n";
            echo "Balance: " . Yii::app()->smsPilot->getLastBalance() . " руб.\n";
            return 0;
        } else {
            echo "Failed to send SMS: " . Yii::app()->smsPilot->getLastError() . "\n";
            return 1;
        }
    }

    /**
     * Check SMS Pilot account balance.
     * 
     * @return int Exit code.
     */
    public function actionBalance()
    {
        echo "Checking SMS Pilot account balance...\n";

        $balance = Yii::app()->smsPilot->getBalance();

        if ($balance !== false) {
            echo "Current balance: {$balance} руб.\n";
            return 0;
        } else {
            echo "Failed to check balance: " . Yii::app()->smsPilot->getLastError() . "\n";
            return 1;
        }
    }

    /**
     * Get SMS Pilot account info.
     * 
     * @return int Exit code.
     */
    public function actionInfo()
    {
        echo "Getting SMS Pilot account info...\n";

        $info = Yii::app()->smsPilot->getInfo();

        if ($info !== false) {
            echo "Account Information:\n";
            foreach ($info as $key => $value) {
                echo "  {$key}: {$value}\n";
            }
            return 0;
        } else {
            echo "Failed to get account info: " . Yii::app()->smsPilot->getLastError() . "\n";
            return 1;
        }
    }

    /**
     * Send SMS notifications for a book to all subscribers.
     * 
     * @param Book $book The book to send notifications for.
     * @return array Results with 'sent', 'failed', and 'errors' keys.
     */
    protected function sendBookNotifications($book)
    {
        $results = array(
            'sent' => 0,
            'failed' => 0,
            'errors' => array(),
        );

        if (empty($book->authors)) {
            echo "  Warning: No authors found for this book.\n";
            return $results;
        }

        // Collect all unique phone numbers from subscribers
        $phones = array();
        $authorNames = array();

        foreach ($book->authors as $author) {
            $authorNames[] = $author->full_name;

            $subscription = new UserSubscription();
            $authorPhones = $subscription->getAuthorSubscriberPhones($author->id);
            $phones = array_merge($phones, $authorPhones);
        }

        // Remove duplicates
        $phones = array_unique($phones);

        if (empty($phones)) {
            echo "  Warning: No subscribers found for this book's authors.\n";
            return $results;
        }

        // Format author names
        $authorNamesStr = implode(', ', $authorNames);

        // Send notifications
        $result = Yii::app()->smsPilot->sendNewBookNotification(
            $phones,
            $book->title,
            $authorNamesStr,
            $book->isbn
        );

        return $result;
    }

    /**
     * Provides the command help.
     * @return string Help text.
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic bookNotification <action> [options]

DESCRIPTION
  This command provides SMS notification functionality for the Book Management System.

ACTIONS
  notify --bookId=<id>
    Send SMS notifications for a specific book.
    Example: ./yiic bookNotification notify --bookId=123

  notifyRecent [--hours=<n>]
    Send SMS notifications for all books created in the last N hours.
    Default hours: 24.
    Example: ./yiic bookNotification notifyRecent --hours=48

  test --phone=<number>
    Send a test SMS message to the specified phone number.
    Example: ./yiic bookNotification test --phone=79087964781

  balance
    Check the SMS Pilot account balance.
    Example: ./yiic bookNotification balance

  info
    Get SMS Pilot account information.
    Example: ./yiic bookNotification info

EOD;
    }
}
