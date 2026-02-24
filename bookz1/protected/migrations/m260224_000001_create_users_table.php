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
     * Uses Yii abstract column types for database compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('users', array(
            'id' => 'pk',
            'username' => 'string NOT NULL UNIQUE',
            'password_hash' => 'string NOT NULL',
            'email' => 'string UNIQUE',
            'phone' => 'string',
            'role' => 'string DEFAULT \'user\'',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp',
        ));

        // Create index on username for faster lookups
        $this->createIndex('idx_users_username', 'users', 'username');
        
        // Create index on email for faster lookups
        $this->createIndex('idx_users_email', 'users', 'email');
        
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
