<?php

/**
 * Migration: Create authors table
 * 
 * Creates the authors table for storing author information.
 * Authors are separate entities from system users.
 * 
 * @package    BookManagementSystem
 * @subpackage migrations
 */
class m260224_000002_create_authors_table extends CDbMigration
{
    /**
     * Creates the authors table with all required fields.
     * Uses MySQL-specific options for better performance and compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('authors', array(
            'id' => 'pk',
            'full_name' => 'string NOT NULL',
            'biography' => 'text',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create index on full_name for searching
        $this->createIndex('idx_authors_full_name', 'authors', 'full_name');

        echo "Authors table created successfully.\n";
        return true;
    }

    /**
     * Drops the authors table.
     * 
     * @return void
     */
    public function safeDown()
    {
        $this->dropTable('authors');
        echo "Authors table dropped successfully.\n";
        return true;
    }
}
