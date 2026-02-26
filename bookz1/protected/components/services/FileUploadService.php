<?php

/**
 * FileUploadService handles file upload operations for cover images.
 * 
 * Responsibilities:
 * - Validate uploaded cover images (extension, MIME type, actual image content)
 * - Save uploaded files to webroot/uploads/covers/
 * - Delete old cover images when updating
 * - Generate unique filenames
 * 
 * @package BookManagementSystem
 * @subpackage components.services
 */
class FileUploadService
{
    /**
     * Allowed file extensions.
     * @var array
     */
    protected $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    /**
     * Allowed MIME types.
     * @var array
     */
    protected $allowedMimeTypes = array(
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp'
    );
    
    /**
     * Maximum file size in bytes (5MB).
     * @var integer
     */
    protected $maxFileSize = 5 * 1024 * 1024;
    
    /**
     * Last validation error message.
     * @var string
     */
    protected $lastError = null;
    
    /**
     * Upload directory alias.
     * @var string
     */
    protected $uploadPathAlias = 'webroot.uploads.covers';
    
    /**
     * Validate uploaded image file.
     * 
     * @param CUploadedFile $file The uploaded file
     * @return boolean Whether the file is valid
     */
    public function validateImageFile($file)
    {
        $this->lastError = null;
        
        if ($file === null) {
            $this->lastError = 'No file uploaded.';
            return false;
        }
        
        // Check extension
        $extension = strtolower($file->getExtensionName());
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->lastError = 'Only image files (JPG, PNG, GIF, WEBP) are allowed.';
            return false;
        }
        
        // Check MIME type
        if (!in_array($file->type, $this->allowedMimeTypes)) {
            $this->lastError = 'Invalid file type. Only image files are allowed.';
            return false;
        }
        
        // Validate actual image content to prevent MIME spoofing
        $imageInfo = @getimagesize($file->tempName);
        if ($imageInfo === false) {
            $this->lastError = 'File is not a valid image.';
            return false;
        }
        
        // Check MIME type from image info
        $detectedMime = $imageInfo['mime'];
        if (!in_array($detectedMime, $this->allowedMimeTypes)) {
            $this->lastError = 'Image MIME type not allowed.';
            return false;
        }
        
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            $this->lastError = 'Cover image must be less than 5MB.';
            return false;
        }
        
        return true;
    }
    
    /**
     * Save cover image and return the saved path.
     * 
     * @param CUploadedFile $file The uploaded file
     * @param string $oldPath Optional old cover path to delete
     * @return string|boolean The saved path or false on failure
     */
    public function saveCoverImage($file, $oldPath = null)
    {
        if (!$this->validateImageFile($file)) {
            return false;
        }
        
        // Generate unique filename
        $extension = strtolower($file->getExtensionName());
        $filename = 'book_' . time() . '_' . uniqid() . '.' . $extension;
        
        // Get upload path
        $uploadPath = Yii::getPathOfAlias($this->uploadPathAlias);
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filePath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
        
        if ($file->saveAs($filePath)) {
            // Delete old cover after successful upload
            if (!empty($oldPath)) {
                $this->deleteCoverImage($oldPath);
            }
            
            return '/uploads/covers/' . $filename;
        }
        
        $this->lastError = 'Failed to save the uploaded file.';
        return false;
    }
    
    /**
     * Delete cover image file.
     * 
     * @param string $coverPath The cover path (e.g., '/uploads/covers/filename.jpg')
     * @return boolean Whether deletion was successful
     */
    public function deleteCoverImage($coverPath)
    {
        if (empty($coverPath)) {
            return false;
        }
        
        $fullPath = Yii::getPathOfAlias('webroot') . $coverPath;
        
        if (file_exists($fullPath)) {
            if (@unlink($fullPath)) {
                return true;
            } else {
                Yii::log("Failed to delete cover image: $fullPath", CLogger::LEVEL_WARNING, 'application.services.FileUploadService');
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Get allowed extensions.
     * 
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }
    
    /**
     * Get allowed MIME types.
     * 
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }
    
    /**
     * Get last validation error message.
     * 
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }
    
    /**
     * Set upload path alias.
     * 
     * @param string $alias The Yii path alias
     */
    public function setUploadPathAlias($alias)
    {
        $this->uploadPathAlias = $alias;
    }
    
    /**
     * Get upload path alias.
     * 
     * @return string
     */
    public function getUploadPathAlias()
    {
        return $this->uploadPathAlias;
    }
}
