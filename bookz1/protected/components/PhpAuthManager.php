<?php

/**
 * PhpAuthManager extends CPhpAuthManager to provide automatic role assignment.
 * 
 * This class automatically assigns the 'authenticated_user' role to logged-in users
 * and 'guest' role to unauthenticated users.
 * 
 * @package BookManagementSystem
 * @subpackage components
 */
class PhpAuthManager extends CPhpAuthManager
{
    /**
     * Initializes the application component.
     * This method overrides the parent implementation by assigning roles to users.
     */
    public function init()
    {
        // Load authorization data from file
        if ($this->authFile === null) {
            $this->authFile = Yii::getPathOfAlias('application.config.auth') . '.php';
        }
        
        parent::init();
        
        // Assign roles based on user authentication status
        if (!Yii::app()->user->isGuest) {
            $userId = Yii::app()->user->id;
            
            // Check if user already has a role assigned in this session
            $assignedRole = Yii::app()->user->getState('assignedRole');
            
            // If role not cached in session, fetch from database
            if ($assignedRole === null) {
                $user = User::model()->findByPk($userId);
                
                if ($user !== null) {
                    $assignedRole = ($user->role === 'admin') ? 'admin' : 'authenticated_user';
                    Yii::app()->user->setState('assignedRole', $assignedRole);
                }
            }
            
            // Assign the role if not already assigned
            if ($assignedRole !== null && !$this->isAssigned($assignedRole, $userId)) {
                $this->assign($assignedRole, $userId);
            }
        }
    }
}
