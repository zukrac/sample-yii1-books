<?php

/**
 * RateLimitService handles rate limiting for authentication attempts.
 * 
 * Responsibilities:
 * - Track failed login attempts by IP
 * - Implement lockout after max attempts
 * - Check if IP is currently locked out
 * - Clear failed attempts on successful login
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class RateLimitService
{
    /**
     * Maximum failed login attempts before lockout.
     */
    const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Lockout duration in seconds (15 minutes).
     */
    const LOCKOUT_DURATION = 900;
    
    /**
     * Cache key prefix for login attempts.
     * @var string
     */
    protected $cacheKeyPrefix = 'login_attempts_';
    
    /**
     * Cache component name.
     * @var string
     */
    protected $cacheComponentName = 'cache';
    
    /**
     * Log category.
     * @var string
     */
    protected $logCategory = 'application.services.RateLimitService';
    
    /**
     * Check if IP is locked out due to too many failed attempts.
     * 
     * @param string $ip The IP address to check
     * @return boolean Whether the IP is locked out
     */
    public function isLockedOut($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return false; // Rate limiting disabled, allow login
        }
        
        $attempts = $this->getFailedAttempts($ip);
        return $attempts >= self::MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Record a failed login attempt.
     * 
     * @param string $ip The IP address
     * @return integer The new attempt count (or 0 if rate limiting disabled)
     */
    public function recordFailedAttempt($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return 0; // Rate limiting disabled
        }
        
        $cacheKey = $this->getCacheKey($ip);
        $timeKey = $this->getCacheKey($ip) . '_time';
        
        $attempts = $this->getFailedAttempts($ip);
        $newAttempts = $attempts === false ? 1 : $attempts + 1;
        
        $cache->set($cacheKey, $newAttempts, self::LOCKOUT_DURATION);
        $cache->set($timeKey, time() + self::LOCKOUT_DURATION, self::LOCKOUT_DURATION);
        
        Yii::log("Failed login attempt for IP {$ip}, count: {$newAttempts}", 
            CLogger::LEVEL_WARNING, $this->logCategory);
        
        return $newAttempts;
    }
    
    /**
     * Clear failed attempts after successful login.
     * 
     * @param string $ip The IP address
     */
    public function clearFailedAttempts($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return; // Rate limiting disabled
        }
        
        $cacheKey = $this->getCacheKey($ip);
        $timeKey = $this->getCacheKey($ip) . '_time';
        
        $cache->delete($cacheKey);
        $cache->delete($timeKey);
        
        Yii::log("Cleared failed login attempts for IP {$ip}", 
            CLogger::LEVEL_INFO, $this->logCategory);
    }
    
    /**
     * Get remaining lockout time in seconds.
     * 
     * @param string $ip The IP address
     * @return integer Remaining time in seconds, 0 if not locked out
     */
    public function getRemainingLockoutTime($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return 0;
        }
        
        $timeKey = $this->getCacheKey($ip) . '_time';
        $ttl = $cache->get($timeKey);
        
        if ($ttl === false) {
            return 0;
        }
        
        $remaining = $ttl - time();
        return max(0, $remaining);
    }
    
    /**
     * Get remaining lockout time formatted as minutes.
     * 
     * @param string $ip The IP address
     * @return string Formatted time (e.g., "5 minutes")
     */
    public function getFormattedRemainingTime($ip)
    {
        $remainingSeconds = $this->getRemainingLockoutTime($ip);
        
        if ($remainingSeconds <= 0) {
            return '0 minutes';
        }
        
        $minutes = ceil($remainingSeconds / 60);
        
        if ($minutes === 1) {
            return '1 minute';
        }
        
        return $minutes . ' minutes';
    }
    
    /**
     * Get current failed attempt count.
     * 
     * @param string $ip The IP address
     * @return integer|false The attempt count or false if not set
     */
    public function getFailedAttempts($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return false;
        }
        
        $cacheKey = $this->getCacheKey($ip);
        return $cache->get($cacheKey);
    }
    
    /**
     * Get lockout expiration timestamp.
     * 
     * @param string $ip The IP address
     * @return integer|false The expiration timestamp or false if not set
     */
    public function getLockoutExpiration($ip)
    {
        $cache = $this->getCache();
        if ($cache === null) {
            return false;
        }
        
        $timeKey = $this->getCacheKey($ip) . '_time';
        return $cache->get($timeKey);
    }
    
    /**
     * Check if rate limiting is enabled.
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        try {
            return Yii::app()->hasComponent($this->cacheComponentName) && $this->getCache() !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get cache key for IP.
     * 
     * @param string $ip The IP address
     * @return string The cache key
     */
    protected function getCacheKey($ip)
    {
        return $this->cacheKeyPrefix . $ip;
    }
    
    /**
     * Get cache component.
     * 
     * @return CCache|null Cache component or null if not configured
     */
    protected function getCache()
    {
        if (!Yii::app()->hasComponent($this->cacheComponentName)) {
            Yii::log("Cache component '{$this->cacheComponentName}' is not configured. Rate limiting is disabled.", 
                CLogger::LEVEL_WARNING, $this->logCategory);
            return null;
        }
        
        return Yii::app()->{$this->cacheComponentName};
    }
    
    /**
     * Set cache component name.
     * 
     * @param string $name The component name
     */
    public function setCacheComponentName($name)
    {
        $this->cacheComponentName = $name;
    }
    
    /**
     * Get cache component name.
     * 
     * @return string
     */
    public function getCacheComponentName()
    {
        return $this->cacheComponentName;
    }
    
    /**
     * Set cache key prefix.
     * 
     * @param string $prefix The prefix
     */
    public function setCacheKeyPrefix($prefix)
    {
        $this->cacheKeyPrefix = $prefix;
    }
    
    /**
     * Get cache key prefix.
     * 
     * @return string
     */
    public function getCacheKeyPrefix()
    {
        return $this->cacheKeyPrefix;
    }
    
    /**
     * Get maximum login attempts constant.
     * 
     * @return integer
     */
    public static function getMaxLoginAttempts()
    {
        return self::MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Get lockout duration constant.
     * 
     * @return integer
     */
    public static function getLockoutDuration()
    {
        return self::LOCKOUT_DURATION;
    }
}
