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
Yii::import('application.components.services.SubscriptionService');
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

        // Use subscription service
        $subscriptionService = new SubscriptionService();
        
        if (Yii::app()->user->isGuest) {
            $phoneNumber = trim(Yii::app()->request->getPost('phone_number', ''));
            $result = $subscriptionService->subscribeGuest($phoneNumber, $authorId);
            
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'You have successfully subscribed to ' . CHtml::encode($author->full_name) . '! You will receive SMS notifications when new books are released.');
            } else {
                Yii::app()->user->setFlash('error', $result['error']);
            }
        } else {
            $phoneNumber = trim(Yii::app()->request->getPost('phone_number', ''));
            $result = $subscriptionService->subscribeUser(Yii::app()->user->id, $authorId, $phoneNumber);
            
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'You have successfully subscribed to ' . CHtml::encode($author->full_name) . '! You will receive SMS notifications when new books are released.');
            } else {
                Yii::app()->user->setFlash('error', $result['error']);
            }
        }

        $this->redirect(array('authors/view', 'id' => $authorId));
    }

    /**
     * Unsubscribe from an author.
     * Only authenticated users can unsubscribe (for security).
     * @param integer $id The subscription ID
     */
    public function actionUnsubscribe($id)
    {
        $subscriptionService = new SubscriptionService();
        $subscription = $subscriptionService->getSubscription($id);
        
        if ($subscription === null) {
            throw new CHttpException(404, 'The requested subscription does not exist.');
        }

        // Check ownership - only the user who owns the subscription can unsubscribe
        if ($subscription->user_id !== Yii::app()->user->id) {
            throw new CHttpException(403, 'You are not authorized to modify this subscription.');
        }

        $authorName = $subscription->author->full_name;
        $authorId = $subscription->author_id;

        $result = $subscriptionService->unsubscribe($id, Yii::app()->user->id);
        
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'You have been unsubscribed from ' . CHtml::encode($authorName) . '.');
        } else {
            Yii::app()->user->setFlash('error', $result['error']);
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

        $subscriptionService = new SubscriptionService();
        
        $result = array(
            'success' => true,
            'isSubscribed' => false,
            'subscription' => null,
        );

        if (!Yii::app()->user->isGuest) {
            $status = $subscriptionService->getSubscriptionStatus($authorId, Yii::app()->user->id);
            $result['isSubscribed'] = $status['isSubscribed'];
            $result['subscription'] = $status['subscription'];
        }

        echo CJSON::encode($result);
        Yii::app()->end();
    }
}
