<?php

/**
 * Migration: Create book_authors table
 * 
 * Creates the junction table for many-to-many relationship between books and authors.
 * Supports multiple authors per book with author ordering.
 * 
 * @package    BookManagementSystem
 * @subpackage migrations
 */
class m260224_000004_create_book_authors_table extends CDbMigration
{
    /**
     * Creates the book_authors junction table with all required fields.
     * Uses MySQL-specific options for better performance and compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('book_authors', array(
            'id' => 'pk',
            'book_id' => 'integer NOT NULL',
            'author_id' => 'integer NOT NULL',
            'author_order' => 'integer DEFAULT 0',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create unique constraint to prevent duplicate book-author pairs
        $this->createIndex('unique_book_author', 'book_authors', 'book_id, author_id', true);
        
        // Create index on author_id for faster lookups
        $this->createIndex('idx_book_authors_author_id', 'book_authors', 'author_id');
        
        // Create index on author_order for sorting
        $this->createIndex('idx_book_authors_author_order', 'book_authors', 'author_order');

        // Add foreign key constraint for book_id referencing books table
        $this->addForeignKey(
            'fk_book_authors_book_id',
            'book_authors',
            'book_id',
            'books',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key constraint for author_id referencing authors table
        $this->addForeignKey(
            'fk_book_authors_author_id',
            'book_authors',
            'author_id',
            'authors',
            'id',
            'CASCADE',
            'CASCADE'
        );

        echo "Book_authors table created successfully.\n";
        return true;
    }

    /**
     * Drops the book_authors table.
     * 
     * @return void
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk_book_authors_book_id', 'book_authors');
        $this->dropForeignKey('fk_book_authors_author_id', 'book_authors');
        
        $this->dropTable('book_authors');
        echo "Book_authors table dropped successfully.\n";
        return true;
    }
}
