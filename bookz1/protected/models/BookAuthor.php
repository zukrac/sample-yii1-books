<?php

/**
 * BookAuthor model class.
 * 
 * Represents the junction table for many-to-many relationship between books and authors.
 * Supports multiple authors per book with author ordering.
 *
 * @property integer $id
 * @property integer $book_id
 * @property integer $author_id
 * @property integer $author_order
 *
 * @property Book $book
 * @property Author $author
 *
 * @package BookManagementSystem
 * @subpackage models
 */
class BookAuthor extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return BookAuthor the static model class
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
        return 'book_authors';
    }

    /**
     * Declares the validation rules.
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            // Required fields
            array('book_id, author_id', 'required'),
            
            // Numerical validation
            array('book_id, author_id, author_order', 'numerical', 'integerOnly' => true),
            
            // Unique combination of book_id and author_id
            array('book_id', 'uniqueWithAuthor'),
            
            // Safe attributes for mass assignment
            array('book_id, author_id, author_order', 'safe'),
        );
    }

    /**
     * Validates that the combination of book_id and author_id is unique.
     * @param string $attribute The attribute being validated.
     * @param array $params Additional parameters.
     */
    public function uniqueWithAuthor($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $criteria = new CDbCriteria;
        $criteria->condition = 'book_id = :book_id AND author_id = :author_id';
        $criteria->params = array(
            ':book_id' => $this->book_id,
            ':author_id' => $this->author_id,
        );

        // Exclude current record on update
        if (!$this->isNewRecord) {
            $criteria->addCondition('id != :id');
            $criteria->params[':id'] = $this->id;
        }

        $existing = self::model()->find($criteria);
        if ($existing !== null) {
            $this->addError($attribute, 'This author is already associated with this book.');
        }
    }

    /**
     * Declares customized attribute labels.
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'book_id' => 'Book',
            'author_id' => 'Author',
            'author_order' => 'Author Order',
        );
    }

    /**
     * Defines relational rules.
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            // BookAuthor belongs to a book
            'book' => array(self::BELONGS_TO, 'Book', 'book_id'),
            // BookAuthor belongs to an author
            'author' => array(self::BELONGS_TO, 'Author', 'author_id'),
        );
    }

    /**
     * Returns the default scope for this model.
     * @return array default scope configuration.
     */
    public function defaultScope()
    {
        return array(
            'order' => 'author_order ASC',
        );
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            // Set default author_order if not set
            if ($this->author_order === null) {
                $this->author_order = $this->getNextAuthorOrder($this->book_id);
            }
            return true;
        }
        return false;
    }

    /**
     * Get the next author order for a book.
     * @param integer $bookId The book ID.
     * @return integer The next author order.
     */
    public function getNextAuthorOrder($bookId)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 'MAX(author_order) as max_order';
        $criteria->condition = 'book_id = :book_id';
        $criteria->params = array(':book_id' => $bookId);

        $result = $this->find($criteria);
        return ($result && $result->max_order !== null) ? $result->max_order + 1 : 0;
    }

    /**
     * Get all authors for a book.
     * @param integer $bookId The book ID.
     * @return array Array of author IDs in order.
     */
    public function getAuthorsByBook($bookId)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'book_id = :book_id';
        $criteria->params = array(':book_id' => $bookId);
        $criteria->order = 'author_order ASC';

        $bookAuthors = $this->findAll($criteria);
        $authors = array();
        foreach ($bookAuthors as $bookAuthor) {
            $authors[] = $bookAuthor->author_id;
        }
        return $authors;
    }

    /**
     * Get all books for an author.
     * @param integer $authorId The author ID.
     * @return array Array of book IDs.
     */
    public function getBooksByAuthor($authorId)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'author_id = :author_id';
        $criteria->params = array(':author_id' => $authorId);

        $bookAuthors = $this->findAll($criteria);
        $books = array();
        foreach ($bookAuthors as $bookAuthor) {
            $books[] = $bookAuthor->book_id;
        }
        return $books;
    }

    /**
     * Reorder authors for a book.
     * @param integer $bookId The book ID.
     * @param array $authorIds Array of author IDs in the desired order.
     * @return boolean Whether the reordering was successful.
     */
    public function reorderAuthors($bookId, $authorIds)
    {
        if (!is_array($authorIds)) {
            return false;
        }

        $transaction = Yii::app()->db->beginTransaction();
        try {
            foreach ($authorIds as $order => $authorId) {
                $bookAuthor = $this->findByAttributes(array(
                    'book_id' => $bookId,
                    'author_id' => $authorId,
                ));
                if ($bookAuthor !== null) {
                    $bookAuthor->author_order = $order;
                    $bookAuthor->save(false, array('author_order'));
                }
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

    /**
     * Remove an author from a book.
     * @param integer $bookId The book ID.
     * @param integer $authorId The author ID.
     * @return boolean Whether the removal was successful.
     */
    public function removeAuthorFromBook($bookId, $authorId)
    {
        return $this->deleteAllByAttributes(array(
            'book_id' => $bookId,
            'author_id' => $authorId,
        )) > 0;
    }

    /**
     * Add an author to a book.
     * @param integer $bookId The book ID.
     * @param integer $authorId The author ID.
     * @param integer $order Optional author order.
     * @return BookAuthor|boolean The new BookAuthor record or false on failure.
     */
    public function addAuthorToBook($bookId, $authorId, $order = null)
    {
        // Check if already exists
        $existing = $this->findByAttributes(array(
            'book_id' => $bookId,
            'author_id' => $authorId,
        ));
        if ($existing !== null) {
            return $existing;
        }

        $bookAuthor = new self;
        $bookAuthor->book_id = $bookId;
        $bookAuthor->author_id = $authorId;
        $bookAuthor->author_order = $order !== null ? $order : $this->getNextAuthorOrder($bookId);

        if ($bookAuthor->save()) {
            return $bookAuthor;
        }
        return false;
    }
}
