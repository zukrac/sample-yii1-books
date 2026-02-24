<?php

/**
 * Migration: Create user_subscriptions table
 * 
 * Creates the subscriptions table for user subscriptions to authors.
 * Stores phone number for SMS notifications.
 * 
 * @package    BookManagementSystem
 * @subpackage migrations
 */
class m260224_000005_create_subscriptions_table extends CDbMigration
{
    /**
     * Creates the user_subscriptions table with all required fields.
     * Uses MySQL-specific options for better performance and compatibility.
     * 
     * @return void
     */
    public function safeUp()
    {
        $this->createTable('user_subscriptions', array(
            'id' => 'pk',
            'user_id' => 'integer',
            'author_id' => 'integer NOT NULL',
            'phone_number' => 'string',
            'subscribed_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create index on user_id and author_id for lookups
        $this->createIndex('idx_user_subscriptions_user_author', 'user_subscriptions', 'user_id, author_id');
        
        // Create index on author_id for faster lookups
        $this->createIndex('idx_user_subscriptions_author_id', 'user_subscriptions', 'author_id');
        
        // Create index on phone_number for SMS lookups
        $this->createIndex('idx_user_subscriptions_phone_number', 'user_subscriptions', 'phone_number');

        // Add foreign key constraint for user_id referencing users table
        $this->addForeignKey(
            'fk_user_subscriptions_user_id',
            'user_subscriptions',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key constraint for author_id referencing authors table
        $this->addForeignKey(
            'fk_user_subscriptions_author_id',
            'user_subscriptions',
            'author_id',
            'authors',
            'id',
            'CASCADE',
            'CASCADE'
        );

        echo "User_subscriptions table created successfully.\n";
        return true;
    }

    /**
     * Drops the user_subscriptions table.
     * 
     * @return void
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk_user_subscriptions_user_id', 'user_subscriptions');
        $this->dropForeignKey('fk_user_subscriptions_author_id', 'user_subscriptions');
        
        $this->dropTable('user_subscriptions');
        echo "User_subscriptions table dropped successfully.\n";
        return true;
    }
}
