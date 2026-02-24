<?php

/**
 * UserSubscription model class.
 * 
 * Represents user subscriptions to authors for SMS notifications.
 * Supports both authenticated users and guest users (via phone number).
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $author_id
 * @property string $phone_number
 * @property string $subscribed_at
 *
 * @property User $user
 * @property Author $author
 *
 * @package BookManagementSystem
 * @subpackage models
 */
class UserSubscription extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserSubscription the static model class
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
        return 'user_subscriptions';
    }

    /**
     * Declares the validation rules.
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            // Required fields
            array('author_id', 'required'),
            
            // Numerical validation
            array('user_id, author_id', 'numerical', 'integerOnly' => true),
            
            // Phone number validation (10-15 digits)
            array('phone_number', 'match', 'pattern' => '/^\d{10,15}$/', 'message' => 'Phone number must be 10-15 digits.', 'allowEmpty' => true),
            
            // Phone number length
            array('phone_number', 'length', 'max' => 15),
            
            // Either user_id or phone_number must be provided
            array('phone_number', 'validateContactInfo'),
            
            // Unique subscription validation
            array('author_id', 'validateUniqueSubscription'),
            
            // Safe attributes for mass assignment
            array('user_id, author_id, phone_number', 'safe'),
        );
    }

    /**
     * Validates that either user_id or phone_number is provided.
     * @param string $attribute The attribute being validated.
     * @param array $params Additional parameters.
     */
    public function validateContactInfo($attribute, $params)
    {
        if ($this->user_id === null && empty($this->phone_number)) {
            $this->addError('phone_number', 'Either user account or phone number is required for subscription.');
        }
    }

    /**
     * Validates that the subscription is unique.
     * For authenticated users: unique (user_id, author_id)
     * For guests: unique (phone_number, author_id)
     * @param string $attribute The attribute being validated.
     * @param array $params Additional parameters.
     */
    public function validateUniqueSubscription($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $criteria = new CDbCriteria;
        $criteria->condition = 'author_id = :author_id';
        $criteria->params = array(':author_id' => $this->author_id);

        if ($this->user_id !== null) {
            // Check for existing subscription by user
            $criteria->addCondition('user_id = :user_id');
            $criteria->params[':user_id'] = $this->user_id;
        } elseif (!empty($this->phone_number)) {
            // Check for existing subscription by phone
            $criteria->addCondition('phone_number = :phone_number');
            $criteria->params[':phone_number'] = $this->phone_number;
        }

        // Exclude current record on update
        if (!$this->isNewRecord) {
            $criteria->addCondition('id != :id');
            $criteria->params[':id'] = $this->id;
        }

        $existing = self::model()->find($criteria);
        if ($existing !== null) {
            $this->addError($attribute, 'You are already subscribed to this author.');
        }
    }

    /**
     * Declares customized attribute labels.
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'author_id' => 'Author',
            'phone_number' => 'Phone Number',
            'subscribed_at' => 'Subscribed At',
        );
    }

    /**
     * Defines relational rules.
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            // Subscription belongs to a user (can be null for guests)
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            // Subscription belongs to an author
            'author' => array(self::BELONGS_TO, 'Author', 'author_id'),
        );
    }

    /**
     * Returns the default scope for this model.
     * @return array default scope configuration.
     */
    public function defaultScope()
    {
        return array(
            'order' => 'subscribed_at DESC',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('author_id', $this->author_id);
        $criteria->compare('phone_number', $this->phone_number, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            // Set subscribed_at for new records
            if ($this->isNewRecord && $this->subscribed_at === null) {
                $this->subscribed_at = new CDbExpression('CURRENT_TIMESTAMP');
            }
            return true;
        }
        return false;
    }

    /**
     * Subscribe a user to an author.
     * @param integer $userId The user ID.
     * @param integer $authorId The author ID.
     * @param string $phone Optional phone number.
     * @return UserSubscription|boolean The subscription record or false on failure.
     */
    public function subscribeUser($userId, $authorId, $phone = null)
    {
        // Check if already subscribed
        $existing = $this->findByAttributes(array(
            'user_id' => $userId,
            'author_id' => $authorId,
        ));
        if ($existing !== null) {
            return $existing;
        }

        $subscription = new self;
        $subscription->user_id = $userId;
        $subscription->author_id = $authorId;
        $subscription->phone_number = $phone;

        if ($subscription->save()) {
            return $subscription;
        }
        return false;
    }

    /**
     * Subscribe a guest (by phone) to an author.
     * @param string $phone The phone number.
     * @param integer $authorId The author ID.
     * @return UserSubscription|boolean The subscription record or false on failure.
     */
    public function subscribeGuest($phone, $authorId)
    {
        // Validate phone number
        if (!preg_match('/^\d{10,15}$/', $phone)) {
            return false;
        }

        // Check if already subscribed
        $existing = $this->findByAttributes(array(
            'phone_number' => $phone,
            'author_id' => $authorId,
        ));
        if ($existing !== null) {
            return $existing;
        }

        $subscription = new self;
        $subscription->user_id = null;
        $subscription->author_id = $authorId;
        $subscription->phone_number = $phone;

        if ($subscription->save()) {
            return $subscription;
        }
        return false;
    }

    /**
     * Unsubscribe a user from an author.
     * @param integer $userId The user ID.
     * @param integer $authorId The author ID.
     * @return boolean Whether the unsubscription was successful.
     */
    public function unsubscribeUser($userId, $authorId)
    {
        return $this->deleteAllByAttributes(array(
            'user_id' => $userId,
            'author_id' => $authorId,
        )) > 0;
    }

    /**
     * Unsubscribe a guest from an author.
     * @param string $phone The phone number.
     * @param integer $authorId The author ID.
     * @return boolean Whether the unsubscription was successful.
     */
    public function unsubscribeGuest($phone, $authorId)
    {
        return $this->deleteAllByAttributes(array(
            'phone_number' => $phone,
            'author_id' => $authorId,
        )) > 0;
    }

    /**
     * Get all subscriptions for a user.
     * @param integer $userId The user ID.
     * @return UserSubscription[] Array of subscriptions.
     */
    public function getUserSubscriptions($userId)
    {
        return $this->with('author')->findAllByAttributes(array(
            'user_id' => $userId,
        ));
    }

    /**
     * Get all subscriptions for an author.
     * @param integer $authorId The author ID.
     * @return UserSubscription[] Array of subscriptions.
     */
    public function getAuthorSubscriptions($authorId)
    {
        return $this->with('user')->findAllByAttributes(array(
            'author_id' => $authorId,
        ));
    }

    /**
     * Get all phone numbers subscribed to an author.
     * @param integer $authorId The author ID.
     * @return array Array of phone numbers.
     */
    public function getAuthorSubscriberPhones($authorId)
    {
        $phones = array();
        $subscriptions = $this->with('user')->findAllByAttributes(array(
            'author_id' => $authorId,
        ));

        foreach ($subscriptions as $subscription) {
            if (!empty($subscription->phone_number)) {
                $phones[] = $subscription->phone_number;
            } elseif (!empty($subscription->user) && !empty($subscription->user->phone)) {
                $phones[] = $subscription->user->phone;
            }
        }

        return array_unique($phones);
    }

    /**
     * Check if a user is subscribed to an author.
     * @param integer $userId The user ID.
     * @param integer $authorId The author ID.
     * @return boolean Whether the user is subscribed.
     */
    public function isUserSubscribed($userId, $authorId)
    {
        return $this->findByAttributes(array(
            'user_id' => $userId,
            'author_id' => $authorId,
        )) !== null;
    }

    /**
     * Check if a phone is subscribed to an author.
     * @param string $phone The phone number.
     * @param integer $authorId The author ID.
     * @return boolean Whether the phone is subscribed.
     */
    public function isPhoneSubscribed($phone, $authorId)
    {
        return $this->findByAttributes(array(
            'phone_number' => $phone,
            'author_id' => $authorId,
        )) !== null;
    }

    /**
     * Get subscription count for an author.
     * @param integer $authorId The author ID.
     * @return integer The number of subscriptions.
     */
    public function getSubscriptionCount($authorId)
    {
        return $this->countByAttributes(array(
            'author_id' => $authorId,
        ));
    }

    /**
     * Get subscription count for a user.
     * @param integer $userId The user ID.
     * @return integer The number of subscriptions.
     */
    public function getUserSubscriptionCount($userId)
    {
        return $this->countByAttributes(array(
            'user_id' => $userId,
        ));
    }
}
