<?php

/**
 * Book model class.
 * 
 * Represents a book entity with title, year, ISBN, and author associations.
 * Supports multiple authors per book and tracks the creator user.
 *
 * @property integer $id
 * @property string $title
 * @property integer $year_published
 * @property string $description
 * @property string $isbn
 * @property string $cover_image
 * @property integer $created_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $creator
 * @property BookAuthor[] $bookAuthors
 * @property Author[] $authors
 *
 * @package BookManagementSystem
 * @subpackage models
 */
class Book extends CActiveRecord
{
    /**
     * Author IDs for multi-select (not stored in DB directly).
     * @var array
     */
    public $author_ids = array();

    /**
     * Cover image file upload instance.
     * @var CUploadedFile
     */
    public $cover_image_file;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Book the static model class
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
        return 'books';
    }

    /**
     * Declares the validation rules.
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            // Required fields
            array('title, year_published', 'required'),
            
            // String length limits
            array('title', 'length', 'max' => 255),
            array('isbn', 'length', 'max' => 20),
            array('cover_image', 'length', 'max' => 500),
            
            // Title must be unique
            array('title', 'unique', 'message' => 'This book title already exists.'),
            
            // ISBN must be unique (if provided)
            array('isbn', 'unique', 'allowEmpty' => true, 'message' => 'This ISBN already exists.'),
            
            // Year validation (1000-2100 range)
            array('year_published', 'numerical', 'integerOnly' => true, 'min' => 1000, 'max' => 2100, 'message' => 'Year must be between 1000 and 2100.'),
            
            // ISBN format validation (optional)
            array('isbn', 'match', 'pattern' => '/^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[0-9X]$/i', 'allowEmpty' => true, 'message' => 'Invalid ISBN format.'),
            
            // Description can be null
            array('description', 'safe'),
            
            // Cover image file upload validation
            array('cover_image_file', 'file', 'types' => 'jpg, jpeg, png, gif', 'allowEmpty' => true, 'maxSize' => 5 * 1024 * 1024, 'tooLarge' => 'Cover image must be less than 5MB.'),
            
            // Author IDs for multi-select
            array('author_ids', 'type', 'type' => 'array', 'allowEmpty' => true),
            
            // Created by must be a valid user
            array('created_by', 'numerical', 'integerOnly' => true),
            
            // Safe attributes for mass assignment
            array('title, year_published, description, isbn, cover_image, author_ids', 'safe', 'on' => 'create, update'),
            array('cover_image_file', 'safe', 'on' => 'create, update'),
            
            // Search scenario safe attributes
            array('title, year_published, isbn, created_by', 'safe', 'on' => 'search'),
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
            'title' => 'Title',
            'year_published' => 'Year Published',
            'description' => 'Description',
            'isbn' => 'ISBN',
            'cover_image' => 'Cover Image',
            'cover_image_file' => 'Cover Image',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'author_ids' => 'Authors',
        );
    }

    /**
     * Defines relational rules.
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            // Book belongs to a user (creator)
            'creator' => array(self::BELONGS_TO, 'User', 'created_by'),
            // Book has many book-author junction records
            'bookAuthors' => array(self::HAS_MANY, 'BookAuthor', 'book_id'),
            // Book has many authors through the junction table (MANY_MANY)
            'authors' => array(self::MANY_MANY, 'Author', 'book_authors(book_id, author_id)'),
        );
    }

    /**
     * Returns the default scope for this model.
     * @return array default scope configuration.
     */
    public function defaultScope()
    {
        return array(
            'order' => 'title ASC',
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
        $criteria->compare('title', $this->title, true);
        $criteria->compare('year_published', $this->year_published);
        $criteria->compare('isbn', $this->isbn, true);
        $criteria->compare('created_by', $this->created_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));
    }

    /**
     * Search books with filters.
     * @param array $filters Filters array with keys: author_id, year, search.
     * @return CActiveDataProvider the data provider.
     */
    public function searchWithFilters($filters = array())
    {
        $criteria = new CDbCriteria;
        $criteria->with = array('authors');

        // Filter by author
        if (!empty($filters['author_id'])) {
            $criteria->with = array('bookAuthors');
            $criteria->addCondition('EXISTS (SELECT 1 FROM book_authors ba WHERE ba.book_id = t.id AND ba.author_id = :author_id)');
            $criteria->params[':author_id'] = $filters['author_id'];
        }

        // Filter by year
        if (!empty($filters['year'])) {
            $criteria->compare('year_published', $filters['year']);
        }

        // Search by title or ISBN
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $criteria->addCondition('title LIKE :search OR isbn LIKE :search');
            $criteria->params[':search'] = '%' . $searchTerm . '%';
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            // Set created_by for new records (only in web application with user component)
            if ($this->isNewRecord && $this->created_by === null) {
                // Check if user component exists (not available in console apps)
                if (Yii::app()->hasComponent('user') && !Yii::app()->user->isGuest) {
                    $this->created_by = Yii::app()->user->id;
                }
            }
            
            return true;
        }
        return false;
    }

    /**
     * This is invoked after the record is saved.
     */
    protected function afterSave()
    {
        parent::afterSave();

        // Update author associations only if author_ids is explicitly set and non-empty
        if (!empty($this->author_ids)) {
            $this->updateAuthorAssociations($this->author_ids);
        }
    }

    /**
     * This is invoked after the record is found.
     */
    protected function afterFind()
    {
        parent::afterFind();

        // Load author IDs for edit form
        $this->author_ids = array();
        foreach ($this->bookAuthors as $bookAuthor) {
            $this->author_ids[] = $bookAuthor->author_id;
        }
    }

    /**
     * Update book-author associations.
     * @param array $authorIds Array of author IDs.
     */
    public function updateAuthorAssociations($authorIds)
    {
        // Delete existing associations
        BookAuthor::model()->deleteAllByAttributes(array('book_id' => $this->id));

        // Create new associations
        if (is_array($authorIds)) {
            $order = 0;
            foreach ($authorIds as $authorId) {
                $bookAuthor = new BookAuthor;
                $bookAuthor->book_id = $this->id;
                $bookAuthor->author_id = (int) $authorId;
                $bookAuthor->author_order = $order++;
                $bookAuthor->save();
            }
        }
    }

    /**
     * Handle cover image upload.
     * @param CUploadedFile $file The uploaded file.
     * @return boolean Whether the upload was successful.
     */
    public function handleCoverImageUpload($file)
    {
        if ($file === null) {
            return false;
        }

        // Generate unique filename
        $extension = pathinfo($file->getName(), PATHINFO_EXTENSION);
        $filename = 'book_cover_' . $this->id . '_' . time() . '.' . $extension;
        
        // Define upload path
        $uploadPath = Yii::getPathOfAlias('webroot.uploads.covers') . DIRECTORY_SEPARATOR;
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Save file
        $filePath = $uploadPath . $filename;
        if ($file->saveAs($filePath)) {
            $this->cover_image = '/uploads/covers/' . $filename;
            $this->save(false, array('cover_image'));
            return true;
        }

        return false;
    }

    /**
     * Get the authors as a comma-separated string.
     * @return string The authors string.
     */
    public function getAuthorsString()
    {
        $authors = array();
        foreach ($this->authors as $author) {
            $authors[] = $author->full_name;
        }
        return implode(', ', $authors);
    }

    /**
     * Check if a user can edit/delete this book.
     * @param integer $userId The user ID to check.
     * @return boolean Whether the user can modify this book.
     */
    public function canModify($userId)
    {
        // Allow if user is the creator
        if ($this->created_by === $userId) {
            return true;
        }

        // Allow if user is admin (if you have admin role)
        $user = User::model()->findByPk($userId);
        if ($user && $user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Get all subscribers for this book's authors.
     * @return array Array of phone numbers.
     */
    public function getSubscriberPhones()
    {
        $phones = array();
        foreach ($this->authors as $author) {
            $authorPhones = $author->getSubscriberPhones();
            $phones = array_merge($phones, $authorPhones);
        }
        return array_unique($phones);
    }

    /**
     * Get books by author.
     * @param integer $authorId The author ID.
     * @return Book[] Array of books.
     */
    public function getBooksByAuthor($authorId)
    {
        $criteria = new CDbCriteria;
        $criteria->with = array('bookAuthors');
        $criteria->condition = 'EXISTS (SELECT 1 FROM book_authors ba WHERE ba.book_id = t.id AND ba.author_id = :author_id)';
        $criteria->params = array(':author_id' => $authorId);
        
        return $this->findAll($criteria);
    }

    /**
     * Get recent books.
     * @param integer $limit The number of books to return.
     * @return Book[] Array of recent books.
     */
    public function getRecentBooks($limit = 10)
    {
        $criteria = new CDbCriteria;
        $criteria->order = 'created_at DESC';
        $criteria->limit = $limit;
        
        return $this->findAll($criteria);
    }
}
