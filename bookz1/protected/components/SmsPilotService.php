<?php

/**
 * SmsPilotService is a Yii application component wrapper for the SMSPilot API.
 * 
 * This component wraps the SMSPilot class from extensions/smspilot/smspilot.class.php
 * and provides a clean interface for sending SMS notifications.
 * 
 * Configuration example (in config/main.php):
 * <pre>
 * 'components' => array(
 *     'smsPilot' => array(
 *         'class' => 'application.components.SmsPilotService',
 *         'apiKey' => 'emulator', // Use 'emulator' for testing
 *         'sender' => 'BookSystem',
 *         'testMode' => true, // Enable test mode
 *     ),
 * ),
 * </pre>
 * 
 * Usage:
 * <pre>
 * // Send SMS
 * $result = Yii::app()->smsPilot->send('79087964781', 'Hello, World!');
 * 
 * // Check balance
 * $balance = Yii::app()->smsPilot->getBalance();
 * </pre>
 *
 * @package BookManagementSystem
 * @subpackage components
 */
class SmsPilotService extends CApplicationComponent
{
    /**
     * @var string API key for SMS Pilot service.
     * Use 'emulator' for testing without sending real SMS.
     */
    public $apiKey = 'emulator';

    /**
     * @var string Default sender name for SMS messages.
     * Must be registered in SMS Pilot account.
     */
    public $sender = 'BookSystem';

    /**
     * @var bool Whether to run in test mode.
     * In test mode, SMS is not actually sent (uses emulator).
     */
    public $testMode = true;

    /**
     * @var string Character encoding for messages.
     */
    public $charset = 'UTF-8';

    /**
     * @var SMSPilot|null The underlying SMSPilot instance.
     */
    private $_smsPilot = null;

    /**
     * @var array Last send result.
     */
    private $_lastResult = null;

    /**
     * @var string|null Last error message.
     */
    private $_lastError = null;

    /**
     * Initializes the component.
     * Loads the SMSPilot class and creates an instance.
     */
    public function init()
    {
        parent::init();

        // Load the SMSPilot class
        $smsPilotPath = Yii::getPathOfAlias('ext.smspilot.smspilot.class') . '.php';
        if (file_exists($smsPilotPath)) {
            require_once($smsPilotPath);
            $this->_smsPilot = new SMSPilot($this->apiKey, $this->charset, $this->sender);
        } else {
            Yii::log("SMSPilot class not found at: $smsPilotPath", CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            throw new CException("SMSPilot class not found. Please ensure the extension is installed.");
        }
    }

    /**
     * Send an SMS message.
     *
     * @param string|array $to Phone number(s) to send to. Can be a single number or array of numbers.
     *                         Format: international format without + (e.g., '79087964781')
     * @param string $message The message text to send.
     * @param string|null $sender Optional sender name (overrides default).
     * @return bool Whether the SMS was sent successfully.
     */
    public function send($to, $message, $sender = null)
    {
        $this->_lastError = null;
        $this->_lastResult = null;

        if ($this->_smsPilot === null) {
            $this->_lastError = 'SMSPilot not initialized';
            Yii::log($this->_lastError, CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            return false;
        }

        // Log in test mode
        if ($this->testMode) {
            Yii::log("SMS Test Mode: Would send to " . (is_array($to) ? implode(', ', $to) : $to) . " - Message: $message", CLogger::LEVEL_INFO, 'application.components.SmsPilotService');
        }

        // Use provided sender or default
        $from = $sender !== null ? $sender : $this->sender;

        try {
            $result = $this->_smsPilot->send($to, $message, $from);
            $this->_lastResult = $result;

            if ($result === false) {
                $this->_lastError = $this->_smsPilot->error;
                Yii::log("SMS send failed: " . $this->_lastError, CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
                return false;
            }

            Yii::log("SMS sent successfully to " . (is_array($to) ? implode(', ', $to) : $to) . 
                     " - Cost: " . $this->_smsPilot->cost . " руб. - Balance: " . $this->_smsPilot->balance . " руб.", 
                     CLogger::LEVEL_INFO, 'application.components.SmsPilotService');
            return true;

        } catch (Exception $e) {
            $this->_lastError = $e->getMessage();
            Yii::log("SMS send exception: " . $this->_lastError, CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            return false;
        }
    }

    /**
     * Send SMS notification about a new book to subscribers.
     *
     * @param array $phones Array of phone numbers to send to.
     * @param string $bookTitle The book title.
     * @param string $authorNames The author name(s).
     * @param string|null $isbn The ISBN (optional).
     * @return array Results with 'sent' count and 'failed' count.
     */
    public function sendNewBookNotification($phones, $bookTitle, $authorNames, $isbn = null)
    {
        $results = array(
            'sent' => 0,
            'failed' => 0,
            'errors' => array(),
        );

        if (empty($phones)) {
            Yii::log("No phones provided for new book notification", CLogger::LEVEL_WARNING, 'application.components.SmsPilotService');
            return $results;
        }

        // Format message according to PRD specification
        // "Новая книга от <Author Name>: "<Book Title>" (ISBN: <ISBN>)"
        $message = "Новая книга от {$authorNames}: \"{$bookTitle}\"";
        if ($isbn) {
            $message .= " (ISBN: {$isbn})";
        }

        // Send to all phones
        foreach ($phones as $phone) {
            if ($this->send($phone, $message)) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = array(
                    'phone' => $phone,
                    'error' => $this->getLastError(),
                );
            }
        }

        Yii::log("New book notification sent: {$results['sent']} successful, {$results['failed']} failed", 
                 CLogger::LEVEL_INFO, 'application.components.SmsPilotService');

        return $results;
    }

    /**
     * Check the account balance.
     *
     * @return string|false The balance amount or false on error.
     */
    public function getBalance()
    {
        if ($this->_smsPilot === null) {
            return false;
        }

        try {
            return $this->_smsPilot->balance();
        } catch (Exception $e) {
            Yii::log("SMS balance check failed: " . $e->getMessage(), CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            return false;
        }
    }

    /**
     * Check SMS delivery status.
     *
     * @param array $ids Array of SMS IDs to check.
     * @return array|false Status array or false on error.
     */
    public function checkStatus($ids)
    {
        if ($this->_smsPilot === null) {
            return false;
        }

        try {
            return $this->_smsPilot->check($ids);
        } catch (Exception $e) {
            Yii::log("SMS status check failed: " . $e->getMessage(), CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            return false;
        }
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message or null if no error.
     */
    public function getLastError()
    {
        return $this->_lastError;
    }

    /**
     * Get the last send result.
     *
     * @return array|null The last result or null if no send attempted.
     */
    public function getLastResult()
    {
        return $this->_lastResult;
    }

    /**
     * Get the cost of the last sent SMS.
     *
     * @return string|null The cost or null if not available.
     */
    public function getLastCost()
    {
        if ($this->_smsPilot !== null) {
            return $this->_smsPilot->cost;
        }
        return null;
    }

    /**
     * Get the remaining balance after last send.
     *
     * @return string|null The balance or null if not available.
     */
    public function getLastBalance()
    {
        if ($this->_smsPilot !== null) {
            return $this->_smsPilot->balance;
        }
        return null;
    }

    /**
     * Get status by phone number from last send.
     *
     * @param string $phone The phone number.
     * @return array|false Status array or false if not found.
     */
    public function getStatusByPhone($phone)
    {
        if ($this->_smsPilot !== null) {
            return $this->_smsPilot->statusByPhone($phone);
        }
        return false;
    }

    /**
     * Get API info.
     *
     * @return array|false Info array or false on error.
     */
    public function getInfo()
    {
        if ($this->_smsPilot === null) {
            return false;
        }

        try {
            if ($this->_smsPilot->info()) {
                return $this->_smsPilot->info;
            }
            return false;
        } catch (Exception $e) {
            Yii::log("SMS info check failed: " . $e->getMessage(), CLogger::LEVEL_ERROR, 'application.components.SmsPilotService');
            return false;
        }
    }
}
