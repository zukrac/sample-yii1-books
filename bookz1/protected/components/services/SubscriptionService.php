<?php

/**
 * SubscriptionService handles subscription operations.
 * 
 * Responsibilities:
 * - Handle guest subscription with phone validation
 * - Handle authenticated user subscription
 * - Validate subscription requests
 * - Check subscription status
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class SubscriptionService
{
    /**
     * Phone number regex pattern (10-15 digits).
     * @var string
     */
    protected $phonePattern = '/^\d{10,15}$/';
    
    /**
     * Log category.
     * @var string
     */
    protected $logCategory = 'application.services.SubscriptionService';
    
    /**
     * Subscribe a guest user to an author.
     * 
     * @param string $phoneNumber The phone number
     * @param integer $authorId The author ID
     * @return array Result array with 'success', 'subscription', 'error' keys
     */
    public function subscribeGuest($phoneNumber, $authorId)
    {
        $result = array(
            'success' => false,
            'subscription' => null,
            'error' => null,
        );
        
        // Validate phone number
        if (!$this->validatePhoneNumber($phoneNumber)) {
            $result['error'] = 'Please enter a valid phone number (10-15 digits, no spaces or dashes).';
            return $result;
        }
        
        // Validate author exists
        $author = Author::model()->findByPk($authorId);
        if ($author === null) {
            $result['error'] = 'Author not found.';
            return $result;
        }
        
        // Check if already subscribed
        if (UserSubscription::model()->isPhoneSubscribed($phoneNumber, $authorId)) {
            $result['error'] = 'This phone number is already subscribed to ' . CHtml::encode($author->full_name) . '.';
            return $result;
        }
        
        // Create subscription
        $subscription = UserSubscription::model()->subscribeGuest($phoneNumber, $authorId);
        
        if ($subscription !== false) {
            $result['success'] = true;
            $result['subscription'] = $subscription;
            
            Yii::log("Guest subscription created: Phone={$phoneNumber}, Author={$authorId}", 
                CLogger::LEVEL_INFO, $this->logCategory);
        } else {
            $result['error'] = 'Failed to create subscription. Please try again.';
        }
        
        return $result;
    }
    
    /**
     * Subscribe an authenticated user to an author.
     * 
     * @param integer $userId The user ID
     * @param integer $authorId The author ID
     * @param string|null $phoneNumber Optional phone number
     * @return array Result array with 'success', 'subscription', 'error' keys
     */
    public function subscribeUser($userId, $authorId, $phoneNumber = null)
    {
        $result = array(
            'success' => false,
            'subscription' => null,
            'error' => null,
        );
        
        // Validate author exists
        $author = Author::model()->findByPk($authorId);
        if ($author === null) {
            $result['error'] = 'Author not found.';
            return $result;
        }
        
        // Check if already subscribed
        if (UserSubscription::model()->isUserSubscribed($userId, $authorId)) {
            $result['error'] = 'You are already subscribed to ' . CHtml::encode($author->full_name) . '.';
            return $result;
        }
        
        // Get phone from user profile if not provided
        if (empty($phoneNumber)) {
            $user = User::model()->findByPk($userId);
            if ($user !== null && !empty($user->phone)) {
                $phoneNumber = $user->phone;
            }
        }
        
        // Validate phone if provided
        if (!empty($phoneNumber) && !$this->validatePhoneNumber($phoneNumber)) {
            $result['error'] = 'Please enter a valid phone number (10-15 digits).';
            return $result;
        }
        
        // Create subscription
        $subscription = UserSubscription::model()->subscribeUser($userId, $authorId, $phoneNumber);
        
        if ($subscription !== false) {
            $result['success'] = true;
            $result['subscription'] = $subscription;
            
            Yii::log("User subscription created: User={$userId}, Author={$authorId}", 
                CLogger::LEVEL_INFO, $this->logCategory);
        } else {
            $result['error'] = 'Failed to create subscription. Please try again.';
        }
        
        return $result;
    }
    
    /**
     * Unsubscribe from an author.
     * 
     * @param integer $subscriptionId The subscription ID
     * @param integer $userId The user ID (for ownership check)
     * @return array Result array with 'success', 'error' keys
     */
    public function unsubscribe($subscriptionId, $userId)
    {
        $result = array(
            'success' => false,
            'error' => null,
        );
        
        // Find subscription
        $subscription = UserSubscription::model()->findByPk($subscriptionId);
        
        if ($subscription === null) {
            $result['error'] = 'Subscription not found.';
            return $result;
        }
        
        // Check ownership
        if ($subscription->user_id !== $userId) {
            $result['error'] = 'You are not authorized to modify this subscription.';
            return $result;
        }
        
        // Delete subscription
        if ($subscription->delete()) {
            $result['success'] = true;
            
            Yii::log("Subscription deleted: ID={$subscriptionId}, User={$userId}", 
                CLogger::LEVEL_INFO, $this->logCategory);
        } else {
            $result['error'] = 'Failed to unsubscribe. Please try again.';
        }
        
        return $result;
    }
    
    /**
     * Get subscription status for an author.
     * 
     * @param integer $authorId The author ID
     * @param integer|null $userId The user ID (optional)
     * @param string|null $phone The phone number (optional)
     * @return array Result array with 'isSubscribed', 'subscription' keys
     */
    public function getSubscriptionStatus($authorId, $userId = null, $phone = null)
    {
        $result = array(
            'isSubscribed' => false,
            'subscription' => null,
        );
        
        if ($userId !== null) {
            // Check user subscription
            $subscription = UserSubscription::model()->findByAttributes(array(
                'user_id' => $userId,
                'author_id' => $authorId,
            ));
            
            if ($subscription !== null) {
                $result['isSubscribed'] = true;
                $result['subscription'] = array(
                    'id' => $subscription->id,
                    'subscribed_at' => $subscription->subscribed_at,
                    'phone_number' => $subscription->phone_number,
                );
            }
        } elseif ($phone !== null) {
            // Check phone subscription
            $subscription = UserSubscription::model()->findByAttributes(array(
                'phone_number' => $phone,
                'author_id' => $authorId,
            ));
            
            if ($subscription !== null) {
                $result['isSubscribed'] = true;
                $result['subscription'] = array(
                    'id' => $subscription->id,
                    'subscribed_at' => $subscription->subscribed_at,
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Validate phone number format.
     * 
     * @param string $phone The phone number
     * @return boolean Whether valid
     */
    public function validatePhoneNumber($phone)
    {
        if (empty($phone)) {
            return false;
        }
        
        return preg_match($this->phonePattern, $phone) === 1;
    }
    
    /**
     * Get user subscriptions with author details.
     * 
     * @param integer $userId The user ID
     * @return UserSubscription[]
     */
    public function getUserSubscriptions($userId)
    {
        return UserSubscription::model()->getUserSubscriptions($userId);
    }
    
    /**
     * Get subscription by ID.
     * 
     * @param integer $id The subscription ID
     * @return UserSubscription|null
     */
    public function getSubscription($id)
    {
        return UserSubscription::model()->findByPk($id);
    }
    
    /**
     * Set phone pattern.
     * 
     * @param string $pattern The regex pattern
     */
    public function setPhonePattern($pattern)
    {
        $this->phonePattern = $pattern;
    }
    
    /**
     * Get phone pattern.
     * 
     * @return string
     */
    public function getPhonePattern()
    {
        return $this->phonePattern;
    }
}
