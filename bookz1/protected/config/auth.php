<?php

/**
 * RBAC Authorization Data
 * 
 * This file defines the roles and permissions for the Book Management System.
 * 
 * Roles:
 * - guest: Unauthenticated users with view-only access
 * - authenticated_user: Authenticated users with CRUD operations on own content
 * - admin: Administrator with full access
 * 
 * @package BookManagementSystem
 * @subpackage config
 */

return array(
    // Operations (permissions)
    'viewBooks' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'View books list and details',
        'bizRule' => null,
        'data' => null,
    ),
    'viewAuthors' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'View authors list and details',
        'bizRule' => null,
        'data' => null,
    ),
    'viewReport' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'View TOP 10 Authors report',
        'bizRule' => null,
        'data' => null,
    ),
    'subscribe' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Subscribe to author updates',
        'bizRule' => null,
        'data' => null,
    ),
    'createBook' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Create new books',
        'bizRule' => null,
        'data' => null,
    ),
    'updateOwnBook' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Edit own created books',
        'bizRule' => 'return isset($params["book"]) && Yii::app()->user->id == $params["book"]->created_by;',
        'data' => null,
    ),
    'deleteOwnBook' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Delete own created books',
        'bizRule' => 'return isset($params["book"]) && Yii::app()->user->id == $params["book"]->created_by;',
        'data' => null,
    ),
    'manageSubscription' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Manage personal subscriptions',
        'bizRule' => null,
        'data' => null,
    ),
    'editProfile' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Edit personal profile',
        'bizRule' => null,
        'data' => null,
    ),
    'updateAnyBook' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Edit any book (admin)',
        'bizRule' => null,
        'data' => null,
    ),
    'deleteAnyBook' => array(
        'type' => CAuthItem::TYPE_OPERATION,
        'description' => 'Delete any book (admin)',
        'bizRule' => null,
        'data' => null,
    ),
    
    // Tasks (grouped operations)
    'bookManagement' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Book management operations',
        'children' => array('viewBooks', 'createBook', 'updateOwnBook', 'deleteOwnBook'),
        'bizRule' => null,
        'data' => null,
    ),
    'bookViewing' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Book viewing operations',
        'children' => array('viewBooks'),
        'bizRule' => null,
        'data' => null,
    ),
    'authorViewing' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Author viewing operations',
        'children' => array('viewAuthors'),
        'bizRule' => null,
        'data' => null,
    ),
    'subscriptionManagement' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Subscription management operations',
        'children' => array('subscribe', 'manageSubscription'),
        'bizRule' => null,
        'data' => null,
    ),
    'profileManagement' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Profile management operations',
        'children' => array('editProfile'),
        'bizRule' => null,
        'data' => null,
    ),
    'reportViewing' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Report viewing operations',
        'children' => array('viewReport'),
        'bizRule' => null,
        'data' => null,
    ),
    'adminBookManagement' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'Admin book management operations',
        'children' => array('updateAnyBook', 'deleteAnyBook'),
        'bizRule' => null,
        'data' => null,
    ),
    
    // Roles
    'guest' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Guest (unauthenticated user)',
        'children' => array(
            'bookViewing',
            'authorViewing',
            'reportViewing',
            'subscribe',
        ),
        'bizRule' => null,
        'data' => null,
    ),
    'authenticated_user' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Authenticated user with CRUD on own content',
        'children' => array(
            'guest',
            'bookManagement',
            'subscriptionManagement',
            'profileManagement',
        ),
        'bizRule' => null,
        'data' => null,
    ),
    'admin' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Administrator with full access',
        'children' => array(
            'authenticated_user',
            'adminBookManagement',
        ),
        'bizRule' => null,
        'data' => null,
    ),
);
