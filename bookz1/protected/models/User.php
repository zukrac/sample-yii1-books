<?php

/**
 * User model class.
 * 
 * Represents a system user for authentication and authorization.
 * Supports roles: 'guest' (default) and 'user' (authenticated).
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $phone
 * @property string $role
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserSubscription[] $subscriptions
 * @property Book[] $books
 *
 * @package BookManagementSystem
 * @subpackage models
 */
class User extends CActiveRecord
{
    /**
     * Password field for registration/password change (not stored in DB).
     * @var string
     */
    public $password;
    
    /**
     * Password confirmation field.
     * @var string
     */
    public $password_confirm;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns the associated database table name.
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'users';
    }

    /**
     * Declares the validation rules.
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            // Required fields for registration
            array('username, password_hash', 'required', 'on' => 'register'),
            array('password, password_confirm', 'required', 'on' => 'register'),
            
            // Required fields for update
            array('username', 'required', 'on' => 'update'),
            
            // String length limits
            array('username', 'length', 'max' => 255),
            array('password_hash', 'length', 'max' => 255),
            array('email', 'length', 'max' => 255),
            array('phone', 'length', 'max' => 15),
            array('role', 'length', 'max' => 50),
            
            // Email validation
            array('email', 'email'),
            array('email', 'unique', 'allowEmpty' => true),
            
            // Username unique
            array('username', 'unique'),
            
            // Phone validation (10-15 digits)
            array('phone', 'match', 'pattern' => '/^\d{10,15}$/', 'message' => 'Phone number must be 10-15 digits.'),
            
            // Password confirmation on register
            array('password_confirm', 'compare', 'compareAttribute' => 'password', 'on' => 'register'),
            
            // Password strength validation on register
            array('password', 'length', 'min' => 8, 'on' => 'register', 'message' => 'Password must be at least 8 characters.'),
            array('password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', 'on' => 'register', 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one digit.'),
            
            // Role validation
            array('role', 'in', 'range' => array('user', 'admin'), 'allowEmpty' => true),
            
            // Safe attributes for mass assignment
            array('username, email, phone, role, password', 'safe', 'on' => 'register'),
            array('email, phone', 'safe', 'on' => 'update'),
        );
    }

    /**
     * Declares customized attribute labels.
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'password' => 'Password',
            'password_confirm' => 'Confirm Password',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'role' => 'Role',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        );
    }

    /**
     * Defines relational rules.
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            // User has many subscriptions to authors
            'subscriptions' => array(self::HAS_MANY, 'UserSubscription', 'user_id'),
            // User has many created books
            'books' => array(self::HAS_MANY, 'Book', 'created_by'),
        );
    }

    /**
     * Returns the default scope for this model.
     * @return array default scope configuration.
     */
    public function defaultScope()
    {
        return array(
            'order' => 'username ASC',
        );
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            // Hash password if provided (for new password field)
            if (!empty($this->password)) {
                $this->password_hash = CPasswordHelper::hashPassword($this->password);
            }
            
            // Set default role if not set
            if ($this->isNewRecord && empty($this->role)) {
                $this->role = 'user';
            }
            
            return true;
        }
        return false;
    }

    /**
     * Verify password against stored hash.
     * @param string $password The password to verify.
     * @return boolean Whether the password is valid.
     */
    public function verifyPassword($password)
    {
        return CPasswordHelper::verifyPassword($password, $this->password_hash);
    }

    /**
     * Get user by username.
     * @param string $username The username to search for.
     * @return User|null The user model or null if not found.
     */
    public function findByUsername($username)
    {
        return $this->findByAttributes(array('username' => $username));
    }

    /**
     * Get user by email.
     * @param string $email The email to search for.
     * @return User|null The user model or null if not found.
     */
    public function findByEmail($email)
    {
        return $this->findByAttributes(array('email' => $email));
    }

    /**
     * Check if user has a specific role.
     * @param string $role The role to check.
     * @return boolean Whether the user has the role.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is authenticated (not a guest).
     * @return boolean Whether the user is authenticated.
     */
    public function isAuthenticated()
    {
        return !Yii::app()->user->isGuest && (int)Yii::app()->user->id === (int)$this->id;
    }
}
