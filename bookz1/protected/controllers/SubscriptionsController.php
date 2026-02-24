<?php

/**
 * SubscriptionsController handles user subscriptions to authors.
 * 
 * Actions:
 * - subscribe: Subscribe to an author (guests with phone, authenticated users)
 * - unsubscribe: Unsubscribe from an author
 * 
 * @package BookManagementSystem
 * @subpackage controllers
 */
class SubscriptionsController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            // Allow all users (guests and authenticated) to subscribe and check status
            array('allow',
                'actions' => array('subscribe', 'checkStatus'),
                'users' => array('*'),
            ),
            // Allow authenticated users to unsubscribe
            array('allow',
                'actions' => array('unsubscribe'),
                'users' => array('@'),
            ),
            // Deny all other actions
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Subscribe to an author.
     * For guests: requires phone_number parameter
     * For authenticated users: uses user's phone or allows to specify one
     * 
     * Accepts POST parameters:
     * - author_id: Required. The author ID to subscribe to
     * - phone_number: Required for guests, optional for authenticated users
     */
    public function actionSubscribe()
    {
        // Only accept POST requests
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request. Please use the subscription form.');
        }

        // Validate CSRF token
        $csrfToken = Yii::app()->request->getPost(Yii::app()->request->csrfTokenName);
        if (!Yii::app()->request->validateCsrfToken($csrfToken)) {
            throw new CHttpException(400, 'Invalid security token. Please refresh the page and try again.');
        }

        // Get author_id from POST using Yii's request component
        $authorId = (int) Yii::app()->request->getPost('author_id', 0);
        
        if ($authorId === 0) {
            Yii::app()->user->setFlash('error', 'Author ID is required.');
            $this->redirect(array('authors/index'));
        }

        // Check if author exists
        $author = Author::model()->findByPk($authorId);
        if ($author === null) {
            throw new CHttpException(404, 'The requested author does not exist.');
        }

        // Handle guest subscription
        if (Yii::app()->user->isGuest) {
            $this->handleGuestSubscription($author);
        } else {
            $this->handleUserSubscription($author);
        }
    }

    /**
     * Handle subscription for guest users.
     * @param Author $author The author to subscribe to
     */
    private function handleGuestSubscription($author)
    {
        $phoneNumber = trim(Yii::app()->request->getPost('phone_number', ''));
        
        // Validate phone number format (10-15 digits)
        if (!preg_match('/^\d{10,15}$/', $phoneNumber)) {
            Yii::app()->user->setFlash('error', 'Please enter a valid phone number (10-15 digits, no spaces or dashes).');
            $this->redirect(array('authors/view', 'id' => $author->id));
        }

        // Check if already subscribed
        if (UserSubscription::model()->isPhoneSubscribed($phoneNumber, $author->id)) {
            Yii::app()->user->setFlash('info', 'This phone number is already subscribed to ' . CHtml::encode($author->full_name) . '.');
            $this->redirect(array('authors/view', 'id' => $author->id));
        }

        // Create subscription
        $subscription = UserSubscription::model()->subscribeGuest($phoneNumber, $author->id);
        
        if ($subscription !== false) {
            Yii::app()->user->setFlash('success', 'You have successfully subscribed to ' . CHtml::encode($author->full_name) . '! You will receive SMS notifications when new books are released.');
        } else {
            Yii::app()->user->setFlash('error', 'Failed to create subscription. Please try again.');
        }

        $this->redirect(array('authors/view', 'id' => $author->id));
    }

    /**
     * Handle subscription for authenticated users.
     * @param Author $author The author to subscribe to
     */
    private function handleUserSubscription($author)
    {
        $userId = Yii::app()->user->id;
        $user = User::model()->findByPk($userId);
        
        // Check if already subscribed
        if (UserSubscription::model()->isUserSubscribed($userId, $author->id)) {
            Yii::app()->user->setFlash('info', 'You are already subscribed to ' . CHtml::encode($author->full_name) . '.');
            $this->redirect(array('authors/view', 'id' => $author->id));
        }

        // Get phone number from POST or user profile
        $phoneNumber = trim(Yii::app()->request->getPost('phone_number', ''));
        
        // If no phone provided in POST, use user's profile phone
        if (empty($phoneNumber) && !empty($user->phone)) {
            $phoneNumber = $user->phone;
        }

        // Validate phone number if provided
        if (!empty($phoneNumber) && !preg_match('/^\d{10,15}$/', $phoneNumber)) {
            Yii::app()->user->setFlash('error', 'Please enter a valid phone number (10-15 digits).');
            $this->redirect(array('authors/view', 'id' => $author->id));
        }

        // Create subscription
        $subscription = UserSubscription::model()->subscribeUser($userId, $author->id, $phoneNumber);
        
        if ($subscription !== false) {
            Yii::app()->user->setFlash('success', 'You have successfully subscribed to ' . CHtml::encode($author->full_name) . '! You will receive SMS notifications when new books are released.');
        } else {
            Yii::app()->user->setFlash('error', 'Failed to create subscription. Please try again.');
        }

        $this->redirect(array('authors/view', 'id' => $author->id));
    }

    /**
     * Unsubscribe from an author.
     * Only authenticated users can unsubscribe (for security).
     * @param integer $id The subscription ID
     */
    public function actionUnsubscribe($id)
    {
        $subscription = UserSubscription::model()->findByPk($id);
        
        if ($subscription === null) {
            throw new CHttpException(404, 'The requested subscription does not exist.');
        }

        // Check ownership - only the user who owns the subscription can unsubscribe
        if ($subscription->user_id !== Yii::app()->user->id) {
            throw new CHttpException(403, 'You are not authorized to modify this subscription.');
        }

        $authorName = $subscription->author->full_name;
        $authorId = $subscription->author_id;

        if ($subscription->delete()) {
            Yii::app()->user->setFlash('success', 'You have been unsubscribed from ' . CHtml::encode($authorName) . '.');
        } else {
            Yii::app()->user->setFlash('error', 'Failed to unsubscribe. Please try again.');
        }

        // Redirect back to where user came from
        $returnUrl = Yii::app()->request->urlReferrer;
        if ($returnUrl) {
            $this->redirect($returnUrl);
        } else {
            $this->redirect(array('authors/view', 'id' => $authorId));
        }
    }

    /**
     * AJAX action to check subscription status.
     * Returns JSON with subscription status.
     */
    public function actionCheckStatus()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Invalid request.');
        }

        $authorId = (int) Yii::app()->request->getQuery('author_id', 0);
        
        if ($authorId === 0) {
            echo CJSON::encode(array('success' => false, 'error' => 'Author ID required'));
            Yii::app()->end();
        }

        $result = array(
            'success' => true,
            'isSubscribed' => false,
            'subscription' => null,
        );

        if (!Yii::app()->user->isGuest) {
            $subscription = UserSubscription::model()->findByAttributes(array(
                'user_id' => Yii::app()->user->id,
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
        }

        echo CJSON::encode($result);
        Yii::app()->end();
    }
}
