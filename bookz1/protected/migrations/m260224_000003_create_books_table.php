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
     * Uses Yii abstract column types for database compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('books', array(
            'id' => 'pk',
            'title' => 'string NOT NULL UNIQUE',
            'year_published' => 'integer NOT NULL',
            'description' => 'text',
            'isbn' => 'string UNIQUE',
            'cover_image' => 'string',
            'created_by' => 'integer',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp',
        ));

        // Create index on title for searching
        $this->createIndex('idx_books_title', 'books', 'title');
        
        // Create index on year_published for filtering
        $this->createIndex('idx_books_year_published', 'books', 'year_published');
        
        // Create index on isbn for lookups
        $this->createIndex('idx_books_isbn', 'books', 'isbn');
        
        // Create index on created_by for filtering by creator
        $this->createIndex('idx_books_created_by', 'books', 'created_by');

        // Add foreign key constraint for created_by referencing users table
        // Note: SQLite requires PRAGMA foreign_keys = ON; to enforce FK constraints
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
