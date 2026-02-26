<?php

/**
 * ReportController handles reporting functionality.
 * 
 * Actions:
 * - topAuthors: TOP 10 Authors by book count with year filter
 * 
 * @package BookManagementSystem
 * @subpackage controllers
 */
class ReportController extends Controller
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
            // Allow all users (guests and authenticated) to view reports
            array('allow',
                'actions' => array('topAuthors'),
                'users' => array('*'),
            ),
            // Deny all other actions
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays TOP 10 Authors by book count.
     * Access: Public (Guest and Authenticated Users)
     * 
     * Features:
     * - Year filter (default: current year)
     * - Displays: Rank, Author Name, Books in Year, Total Books, Latest Book
     */
    public function actionTopAuthors()
    {
        // Get selected year (default to current year)
        $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

        // Get distinct years for filter dropdown
        $years = Yii::app()->db->createCommand()
            ->selectDistinct('year_published')
            ->from('books')
            ->order('year_published DESC')
            ->queryColumn();

        // If no years in database, add current year
        if (empty($years)) {
            $years = array($selectedYear);
        }

        // Get TOP 10 authors using model method
        $authors = Author::model()->getTopAuthorsByYear($selectedYear);

        $this->render('topAuthors', array(
            'authors' => $authors,
            'selectedYear' => $selectedYear,
            'years' => $years,
        ));
    }
}
