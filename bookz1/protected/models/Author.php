<?php

/**
 * Author model class.
 * 
 * Represents an author entity (separate from system users).
 * Authors can have multiple books and user subscriptions.
 *
 * @property integer $id
 * @property string $full_name
 * @property string $biography
 * @property string $created_at
 *
 * @property BookAuthor[] $bookAuthors
 * @property Book[] $books
 * @property UserSubscription[] $subscriptions
 *
 * @package BookManagementSystem
 * @subpackage models
 */
class Author extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Author the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns the associated database table name.
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'authors';
    }

    /**
     * Declares the validation rules.
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            // Required fields
            array('full_name', 'required'),
            
            // String length limits
            array('full_name', 'length', 'max' => 255, 'min' => 2),
            
            // Biography can be null
            array('biography', 'safe'),
            
            // Search scenario safe attributes
            array('full_name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Declares customized attribute labels.
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'full_name' => 'Full Name (ФИО)',
            'biography' => 'Biography',
            'created_at' => 'Created At',
        );
    }

    /**
     * Defines relational rules.
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            // Author has many book-author junction records
            'bookAuthors' => array(self::HAS_MANY, 'BookAuthor', 'author_id'),
            // Author has many books through the junction table (MANY_MANY)
            'books' => array(self::MANY_MANY, 'Book', 'book_authors(author_id, book_id)'),
            // Author has many subscriptions
            'subscriptions' => array(self::HAS_MANY, 'UserSubscription', 'author_id'),
        );
    }

    /**
     * Returns the default scope for this model.
     * @return array default scope configuration.
     */
    public function defaultScope()
    {
        return array(
            'order' => 'full_name ASC',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('full_name', $this->full_name, true);
        $criteria->compare('biography', $this->biography, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 50,
            ),
        ));
    }

    /**
     * Get the count of books for this author.
     * @return integer The number of books.
     */
    public function getBookCount()
    {
        return count($this->books);
    }

    /**
     * Get the count of subscriptions for this author.
     * @return integer The number of subscriptions.
     */
    public function getSubscriptionCount()
    {
        return count($this->subscriptions);
    }

    /**
     * Get authors with book counts for TOP 10 report.
     * @param integer $year The year to filter by.
     * @return array Array of authors with book counts.
     */
    public function getTopAuthorsByYear($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        // Use database-agnostic SQL (year_published is stored as integer)
        $sql = "
            SELECT 
                a.id,
                a.full_name,
                COUNT(DISTINCT CASE WHEN b.year_published = :year THEN b.id END) as books_in_year,
                COUNT(DISTINCT b.id) as total_books,
                MAX(CASE WHEN b.year_published = :year THEN b.title END) as latest_book
            FROM authors a
            LEFT JOIN book_authors ba ON a.id = ba.author_id
            LEFT JOIN books b ON ba.book_id = b.id
            GROUP BY a.id, a.full_name
            HAVING books_in_year > 0
            ORDER BY books_in_year DESC
            LIMIT 10
        ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':year', $year, PDO::PARAM_INT);

        return $command->queryAll();
    }

    /**
     * Check if a user is subscribed to this author.
     * @param integer $userId The user ID to check.
     * @return boolean Whether the user is subscribed.
     */
    public function isUserSubscribed($userId)
    {
        if ($userId === null) {
            return false;
        }

        $subscription = UserSubscription::model()->findByAttributes(array(
            'user_id' => $userId,
            'author_id' => $this->id,
        ));

        return $subscription !== null;
    }

    /**
     * Check if a phone number is subscribed to this author.
     * @param string $phone The phone number to check.
     * @return boolean Whether the phone is subscribed.
     */
    public function isPhoneSubscribed($phone)
    {
        if (empty($phone)) {
            return false;
        }

        $subscription = UserSubscription::model()->findByAttributes(array(
            'phone_number' => $phone,
            'author_id' => $this->id,
        ));

        return $subscription !== null;
    }

    /**
     * Get all subscribers (phone numbers) for SMS notifications.
     * @return array Array of phone numbers.
     */
    public function getSubscriberPhones()
    {
        $phones = array();
        foreach ($this->subscriptions as $subscription) {
            if (!empty($subscription->phone_number)) {
                $phones[] = $subscription->phone_number;
            } elseif (!empty($subscription->user) && !empty($subscription->user->phone)) {
                $phones[] = $subscription->user->phone;
            }
        }
        return array_unique($phones);
    }

    /**
     * Get the latest book by this author.
     * @return Book|null The latest book or null if none.
     */
    public function getLatestBook()
    {
        $criteria = new CDbCriteria;
        $criteria->with = array('books');
        $criteria->order = 'books.year_published DESC';
        $criteria->limit = 1;

        $bookAuthor = BookAuthor::model()->with('book')->findByAttributes(
            array('author_id' => $this->id),
            array('order' => 'book.year_published DESC')
        );

        return $bookAuthor ? $bookAuthor->book : null;
    }
}
