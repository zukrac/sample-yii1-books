<?php

/**
 * SiteController handles the main site actions.
 * 
 * Actions:
 * - index: Homepage
 * - error: Error handling
 * - contact: Contact form
 * - login: User login (redirects to UserController)
 * - logout: User logout (redirects to UserController)
 * 
 * @package BookManagementSystem
 * @subpackage controllers
 */
class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 * @return array action configurations
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
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
			// Allow all users to view index, error, contact, and page
			array('allow',
				'actions'=>array('index', 'error', 'contact', 'page', 'captcha'),
				'users'=>array('*'),
			),
			// Allow only guests to login
			array('allow',
				'actions'=>array('login'),
				'users'=>array('?'),
			),
			// Allow authenticated users to logout
			array('allow',
				'actions'=>array('logout'),
				'users'=>array('@'),
			),
			// Deny all other actions
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page.
	 * Redirects to UserController login action for consistency.
	 */
	public function actionLogin()
	{
		// Redirect to UserController login for consistency
		$this->redirect(array('user/login'));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 * Redirects to UserController logout action for consistency.
	 */
	public function actionLogout()
	{
		// Redirect to UserController logout for consistency
		$this->redirect(array('user/logout'));
	}
}