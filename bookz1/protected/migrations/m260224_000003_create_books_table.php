<?php

/**
 * Migration: Create books table
 * 
 * Creates the books table for storing book information.
 * Includes foreign key reference to users table for created_by field.
 * 
 * @package    BookManagementSystem
 * @subpackage migrations
 */
class m260224_000003_create_books_table extends CDbMigration
{
    /**
     * Creates the books table with all required fields.
     * Uses MySQL-specific options for better performance and compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('books', array(
            'id' => 'pk',
            'title' => 'string NOT NULL',
            'year_published' => 'integer NOT NULL',
            'description' => 'text',
            'isbn' => 'string',
            'cover_image' => 'string',
            'created_by' => 'integer',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create unique index on title
        $this->createIndex('idx_books_title_unique', 'books', 'title', true);
        
        // Create index on year_published for filtering
        $this->createIndex('idx_books_year_published', 'books', 'year_published');
        
        // Create unique index on isbn
        $this->createIndex('idx_books_isbn_unique', 'books', 'isbn', true);
        
        // Create index on created_by for filtering by creator
        $this->createIndex('idx_books_created_by', 'books', 'created_by');

        // Add foreign key constraint for created_by referencing users table
        $this->addForeignKey(
            'fk_books_created_by',
            'books',
            'created_by',
            'users',
            'id',
            'SET NULL',
            'CASCADE'
        );

        echo "Books table created successfully.\n";
        return true;
    }

    /**
     * Drops the books table.
     * 
     * @return void
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk_books_created_by', 'books');
        
        $this->dropTable('books');
        echo "Books table dropped successfully.\n";
        return true;
    }
}
