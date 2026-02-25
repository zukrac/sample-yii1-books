<?php

/**
 * SMS Pilot configuration.
 * 
 * This file contains the configuration for the SMS Pilot service.
 * Settings can be overridden via environment variables for security.
 * 
 * Environment Variables:
 * - SMSPILOT_API_KEY: Your SMS Pilot API key (required for production)
 * - SMSPILOT_SENDER: Sender name (must be registered in SMS Pilot account)
 * - SMSPILOT_TEST_MODE: Set to 'false' in production to send real SMS
 * 
 * For local development, copy bookz1/.env.example to bookz1/.env
 * 
 * @package BookManagementSystem
 * @subpackage config
 */

return array(
    'class' => 'application.components.SmsPilotService',
    
    // API key: use environment variable or 'emulator' for testing
    'apiKey' => getenv('SMSPILOT_API_KEY') ?: 'emulator',
    
    // Sender name: must be registered in SMS Pilot account
    'sender' => getenv('SMSPILOT_SENDER') ?: 'BookSystem',
    
    // Test mode: set to false in production via environment variable
    'testMode' => getenv('SMSPILOT_TEST_MODE') !== 'false',
);
