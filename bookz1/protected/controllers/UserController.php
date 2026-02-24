<?php

/**
 * UserController handles user authentication and profile management.
 * 
 * Actions:
 * - login: User authentication
 * - logout: User logout
 * - register: User registration
 * - profile: User profile with subscriptions
 */
class UserController extends Controller
{
    /**
     * Maximum failed login attempts before rate limiting.
     */
    const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Lockout duration in seconds (15 minutes).
     */
    const LOCKOUT_DURATION = 900;

    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the registration page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
        );
    }

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
            // Allow guests to login and register
            array('allow',
                'actions' => array('login', 'register'),
                'users' => array('?'),
            ),
            // Allow authenticated users to logout and view profile
            array('allow',
                'actions' => array('logout', 'profile'),
                'users' => array('@'),
            ),
            // Deny all other actions
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays the login page.
     * Handles user authentication via LoginForm model.
     * Implements rate limiting to prevent brute force attacks.
     */
    public function actionLogin()
    {
        // Check if user is currently locked out
        $ip = Yii::app()->request->userHostAddress;
        $cacheKey = 'login_attempts_' . $ip;
        $attempts = Yii::app()->cache->get($cacheKey);
        
        if ($attempts !== false && $attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $ttl = Yii::app()->cache->get($cacheKey . '_time');
            $remainingTime = $ttl ? max(0, $ttl - time()) : self::LOCKOUT_DURATION;
            Yii::app()->user->setFlash('error', 'Too many failed login attempts. Please try again in ' . ceil($remainingTime / 60) . ' minutes.');
            $this->render('login', array('model' => new LoginForm));
            return;
        }

        $model = new LoginForm;

        // If it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // Collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // Validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                // Clear failed attempts on successful login
                Yii::app()->cache->delete($cacheKey);
                Yii::app()->cache->delete($cacheKey . '_time');
                Yii::app()->user->setFlash('success', 'You have been successfully logged in.');
                $this->redirect(Yii::app()->user->returnUrl);
            } else {
                // Increment failed attempts
                $newAttempts = $attempts === false ? 1 : $attempts + 1;
                Yii::app()->cache->set($cacheKey, $newAttempts, self::LOCKOUT_DURATION);
                Yii::app()->cache->set($cacheKey . '_time', time() + self::LOCKOUT_DURATION, self::LOCKOUT_DURATION);
                
                if ($newAttempts >= self::MAX_LOGIN_ATTEMPTS) {
                    Yii::app()->user->setFlash('error', 'Too many failed login attempts. Please try again in ' . ceil(self::LOCKOUT_DURATION / 60) . ' minutes.');
                }
            }
        }

        // Display the login form
        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirects to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        Yii::app()->user->setFlash('success', 'You have been successfully logged out.');
        $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * Displays the registration page.
     * Handles new user registration.
     */
    public function actionRegister()
    {
        $model = new User('register');

        // If it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // Collect user input data
        if (isset($_POST['User'])) {
            $model->attributes = $_POST['User'];
            
            if ($model->validate()) {
                // Save the user (password will be hashed in beforeSave)
                if ($model->save(false)) {
                    // Auto-login after registration
                    $identity = new UserIdentity($model->username, $model->password);
                    if ($identity->authenticate()) {
                        Yii::app()->user->login($identity);
                        Yii::app()->user->setFlash('success', 'Registration successful! Welcome, ' . $model->username . '!');
                        $this->redirect(array('profile'));
                    }
                }
            }
        }

        // Display the registration form
        $this->render('register', array('model' => $model));
    }

    /**
     * Displays user profile with subscriptions.
     */
    public function actionProfile()
    {
        $user = User::model()->findByPk(Yii::app()->user->id);
        
        if ($user === null) {
            throw new CHttpException(404, 'User not found.');
        }

        // Get user's subscriptions with author details
        $subscriptions = UserSubscription::model()->with('author')->findAllByAttributes(
            array('user_id' => $user->id)
        );

        // Get user's books
        $booksProvider = new CActiveDataProvider('Book', array(
            'criteria' => array(
                'condition' => 'created_by = :userId',
                'params' => array(':userId' => $user->id),
                'with' => array('authors'),
            ),
            'pagination' => array(
                'pageSize' => 10,
            ),
        ));

        $this->render('profile', array(
            'user' => $user,
            'subscriptions' => $subscriptions,
            'booksProvider' => $booksProvider,
        ));
    }
}
