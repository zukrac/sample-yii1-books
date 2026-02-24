<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user against the database.
 * 
 * @property integer $id User ID
 * @property string $name Username
 */
class UserIdentity extends CUserIdentity
{
    /**
     * User ID after successful authentication.
     * @var integer
     */
    private $_id;
    
    /**
     * User role after successful authentication.
     * @var string
     */
    private $_role;

    /**
     * Authenticates a user against the database.
     * Uses bcrypt password hashing via CPasswordHelper.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        // Find user by username
        $user = User::model()->findByAttributes(array('username' => $this->username));
        
        if ($user === null) {
            // User not found
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif (!$user->verifyPassword($this->password)) {
            // Invalid password
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            // Authentication successful
            $this->_id = $user->id;
            $this->_role = $user->role;
            $this->username = $user->username;
            $this->errorCode = self::ERROR_NONE;
            
            // Store additional user data in session
            $this->setState('role', $user->role);
            $this->setState('email', $user->email);
        }
        
        return !$this->errorCode;
    }

    /**
     * Returns the user ID.
     * @return integer User ID
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Returns the user role.
     * @return string User role
     */
    public function getRole()
    {
        return $this->_role;
    }
}
