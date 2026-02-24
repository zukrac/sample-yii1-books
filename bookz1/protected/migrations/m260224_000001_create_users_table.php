<?php

/**
 * Migration: Create users table
 * 
 * Creates the users table for authentication and user management.
 * 
 * @package    BookManagementSystem
 * @subpackage migrations
 */
class m260224_000001_create_users_table extends CDbMigration
{
    /**
     * Creates the users table with all required fields.
     * Uses MySQL-specific options for better performance and compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('users', array(
            'id' => 'pk',
            'username' => 'string NOT NULL',
            'password_hash' => 'string NOT NULL',
            'email' => 'string',
            'phone' => 'string',
            'role' => 'string DEFAULT \'user\'',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create unique index on username
        $this->createIndex('idx_users_username_unique', 'users', 'username', true);
        
        // Create unique index on email
        $this->createIndex('idx_users_email_unique', 'users', 'email', true);
        
        // Create index on role for filtering
        $this->createIndex('idx_users_role', 'users', 'role');

        echo "Users table created successfully.\n";
        return true;
    }

    /**
     * Drops the users table.
     * 
     * @return void
     */
    public function safeDown()
    {
        $this->dropTable('users');
        echo "Users table dropped successfully.\n";
        return true;
    }
}
