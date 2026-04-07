<?php
// includes/upload.php - Secure File Upload Handler

require_once __DIR__ . '/../config/env.php';

class SecureFileUpload {
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private $maxSize;
    private $uploadDir;
    
    public function __construct($subdir = 'payments') {
        $this->maxSize = (int)env('MAX_FILE_SIZE', 2097152);
        $baseDir = rtrim(env('UPLOAD_DIR', 'uploads/'), '/');
        $this->uploadDir = BASE_PATH . '/' . $baseDir . '/' . $subdir . '/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function upload($file, $prefix = 'file'): array {
        $result = [
            'success' => false,
            'filename' => '',
            'error' => ''
        ];
        
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $result['error'] = 'No file selected';
            return $result;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = $this->getUploadError($file['error']);
            return $result;
        }
        
        if ($file['size'] > $this->maxSize) {
            $result['error'] = 'File size exceeds maximum limit of 2MB';
            return $result;
        }
        
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes)) {
            $result['error'] = 'Invalid file type. Only JPG, PNG, and WEBP images are allowed';
            return $result;
        }
        
        $extension = $this->getExtension($mimeType);
        $filename = $prefix . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = 'Failed to save file';
        }
        
        return $result;
    }
    
    public function delete($filename): bool {
        $filepath = $this->uploadDir . basename($filename);
        if (file_exists($filepath) && strpos($filename, '..') === false) {
            return unlink($filepath);
        }
        return false;
    }
    
    private function getMimeType($filepath): string {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filepath);
    }
    
    private function getExtension($mimeType): string {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        return $map[$mimeType] ?? 'bin';
    }
    
    private function getUploadError($errorCode): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
}
