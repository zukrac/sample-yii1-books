<?php

/**
 * AuthorsController handles author viewing and inline creation.
 * 
 * Actions:
 * - index: List authors with pagination
 * - view: Author detail page with books
 * - createInline: AJAX inline author creation
 * 
 * @package BookManagementSystem
 * @subpackage controllers
 */
class AuthorsController extends Controller
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
     * Uses RBAC roles for authorization.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            // Allow all users (guests and authenticated) to view authors
            array('allow',
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            // Allow authenticated users to create authors inline
            array('allow',
                'actions' => array('createInline'),
                'roles' => array('authenticated_user'),
            ),
            // Deny all other actions
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Lists all authors with pagination.
     * Access: Public (Guest and Authenticated Users)
     */
    public function actionIndex()
    {
        $criteria = new CDbCriteria(array(
            'with' => array('books'),
            'order' => 't.full_name ASC',
        ));

        $dataProvider = new CActiveDataProvider('Author', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 50,
            ),
        ));

        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Displays a single author's details with their books.
     * Access: Public (Guest and Authenticated Users)
     * @param integer $id the ID of the author to display
     */
    public function actionView($id)
    {
        $author = $this->loadModel($id);

        // Get author's books with pagination
        $booksProvider = new CActiveDataProvider('Book', array(
            'criteria' => array(
                'with' => array('authors'),
                'join' => 'JOIN book_authors ba ON t.id = ba.book_id',
                'condition' => 'ba.author_id = :authorId',
                'params' => array(':authorId' => $author->id),
                'order' => 't.year_published DESC',
            ),
            'pagination' => array(
                'pageSize' => 10,
            ),
        ));

        // Check if current user is subscribed to this author
        $isSubscribed = false;
        $subscription = null;
        if (!Yii::app()->user->isGuest) {
            $subscription = UserSubscription::model()->findByAttributes(array(
                'user_id' => Yii::app()->user->id,
                'author_id' => $author->id,
            ));
            $isSubscribed = $subscription !== null;
        }

        $this->render('view', array(
            'author' => $author,
            'booksProvider' => $booksProvider,
            'isSubscribed' => $isSubscribed,
            'subscription' => $subscription,
        ));
    }

    /**
     * Creates a new author via AJAX (inline creation).
     * Access: Authenticated Users only
     */
    public function actionCreateInline()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }

        $author = new Author();

        if (isset($_POST['Author'])) {
            $author->attributes = $_POST['Author'];
            
            if ($author->save()) {
                echo CJSON::encode(array(
                    'success' => true,
                    'author' => array(
                        'id' => $author->id,
                        'full_name' => $author->full_name,
                    ),
                ));
                Yii::app()->end();
            } else {
                echo CJSON::encode(array(
                    'success' => false,
                    'errors' => $author->getErrors(),
                ));
                Yii::app()->end();
            }
        }

        // Return form for GET request (if needed)
        $this->renderPartial('_inlineForm', array(
            'author' => $author,
        ), false, true);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Author the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Author::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested author does not exist.');
        }
        return $model;
    }
}
