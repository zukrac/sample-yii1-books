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

        // Build the query for TOP 10 authors
        $sql = "
            SELECT 
                a.id,
                a.full_name,
                COUNT(DISTINCT CASE WHEN b.year_published = :year THEN b.id END) as books_in_year,
                COUNT(DISTINCT b.id) as total_books,
                MAX(CASE WHEN b.year_published = :year THEN b.title END) as latest_book,
                MAX(CASE WHEN b.year_published = :year THEN b.year_published END) as latest_book_year
            FROM authors a
            LEFT JOIN book_authors ba ON a.id = ba.author_id
            LEFT JOIN books b ON ba.book_id = b.id
            GROUP BY a.id, a.full_name
            HAVING books_in_year > 0
            ORDER BY books_in_year DESC, total_books DESC
            LIMIT 10
        ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':year', $selectedYear);
        $authors = $command->queryAll();

        $this->render('topAuthors', array(
            'authors' => $authors,
            'selectedYear' => $selectedYear,
            'years' => $years,
        ));
    }
}
