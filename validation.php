<?php
/**
 * Input Validation Utility for MeatTrack
 * Provides comprehensive validation functions for all user inputs
 */

class Validator {
    
    /**
     * Validate and sanitize basic text input
     */
    public static function sanitizeText($input, $maxLength = 255) {
        if (!is_string($input)) {
            return '';
        }
        
        $sanitized = trim($input);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        if ($maxLength > 0 && strlen($sanitized) > $maxLength) {
            $sanitized = substr($sanitized, 0, $maxLength);
        }
        
        return $sanitized;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate and sanitize numeric input
     */
    public static function validateNumber($input, $min = null, $max = null, $decimals = 2) {
        if (!is_numeric($input)) {
            return false;
        }
        
        $number = floatval($input);
        
        if ($min !== null && $number < $min) {
            return false;
        }
        
        if ($max !== null && $number > $max) {
            return false;
        }
        
        return round($number, $decimals);
    }
    
    /**
     * Validate date format (YYYY-MM-DD)
     */
    public static function validateDate($date) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return false;
        }
        
        return date('Y-m-d', $timestamp) === $date;
    }
    
    /**
     * Validate datetime format (YYYY-MM-DD HH:MM:SS)
     */
    public static function validateDateTime($datetime) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
            return false;
        }
        
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return false;
        }
        
        return date('Y-m-d H:i:s', $timestamp) === $datetime;
    }
    
    /**
     * Validate batch number format
     */
    public static function validateBatchNumber($batchNumber) {
        // Batch numbers should be alphanumeric with hyphens/underscores, 3-50 characters
        return preg_match('/^[A-Za-z0-9_-]{3,50}$/', $batchNumber);
    }
    
    /**
     * Validate delivery ID format
     */
    public static function validateDeliveryId($deliveryId) {
        // Delivery IDs should follow format: DL-YYYY-MM-DD-XXX
        return preg_match('/^DL-\d{4}-\d{2}-\d{2}-\d{3}$/', $deliveryId);
    }
    
    /**
     * Validate user role
     */
    public static function validateUserRole($role) {
        $allowedRoles = ['admin', 'manager', 'supervisor', 'operator', 'viewer'];
        return in_array($role, $allowedRoles);
    }
    
    /**
     * Validate inventory status
     */
    public static function validateInventoryStatus($status) {
        $allowedStatuses = ['good', 'near_expiry', 'expired', 'spoiled'];
        return in_array($status, $allowedStatuses);
    }
    
    /**
     * Validate distribution status
     */
    public static function validateDistributionStatus($status) {
        $allowedStatuses = ['preparing', 'in_transit', 'delivered', 'cancelled'];
        return in_array($status, $allowedStatuses);
    }
    
    /**
     * Validate temperature range
     */
    public static function validateTemperature($temperature) {
        return self::validateNumber($temperature, -50, 100, 2);
    }
    
    /**
     * Validate humidity percentage
     */
    public static function validateHumidity($humidity) {
        return self::validateNumber($humidity, 0, 100, 2);
    }
    
    /**
     * Validate quantity
     */
    public static function validateQuantity($quantity) {
        return self::validateNumber($quantity, 0.01, 999999, 2);
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        // Remove all non-numeric characters except + at the beginning
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Check if it matches common phone patterns
        return preg_match('/^(\+?1?[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})$/', $phone) ||
               preg_match('/^\+?[1-9]\d{1,14}$/', $cleaned);
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'message' => 'Invalid file upload.'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['valid' => false, 'message' => 'No file was uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['valid' => false, 'message' => 'File size exceeds limit.'];
            default:
                return ['valid' => false, 'message' => 'Unknown file upload error.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File size exceeds ' . number_format($maxSize / 1024 / 1024, 2) . 'MB limit.'];
        }
        
        if (!empty($allowedTypes)) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return ['valid' => false, 'message' => 'File type not allowed.'];
            }
        }
        
        return ['valid' => true, 'message' => 'File is valid.'];
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate and sanitize array of data
     */
    public static function validateArray($data, $rules) {
        $validatedData = [];
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Check if field is required
            if (isset($rule['required']) && $rule['required'] && (is_null($value) || $value === '')) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            // Skip validation if field is not required and empty
            if (is_null($value) || $value === '') {
                $validatedData[$field] = null;
                continue;
            }
            
            // Apply validation based on type
            switch ($rule['type']) {
                case 'text':
                    $maxLength = $rule['max_length'] ?? 255;
                    $validatedData[$field] = self::sanitizeText($value, $maxLength);
                    break;
                    
                case 'email':
                    if (self::validateEmail($value)) {
                        $validatedData[$field] = filter_var($value, FILTER_SANITIZE_EMAIL);
                    } else {
                        $errors[$field] = ucfirst($field) . ' must be a valid email address';
                    }
                    break;
                    
                case 'number':
                    $min = $rule['min'] ?? null;
                    $max = $rule['max'] ?? null;
                    $decimals = $rule['decimals'] ?? 2;
                    $validated = self::validateNumber($value, $min, $max, $decimals);
                    if ($validated !== false) {
                        $validatedData[$field] = $validated;
                    } else {
                        $errors[$field] = ucfirst($field) . ' must be a valid number';
                        if ($min !== null || $max !== null) {
                            $errors[$field] .= ' between ' . ($min ?? 'negative infinity') . ' and ' . ($max ?? 'positive infinity');
                        }
                    }
                    break;
                    
                case 'date':
                    if (self::validateDate($value)) {
                        $validatedData[$field] = $value;
                    } else {
                        $errors[$field] = ucfirst($field) . ' must be a valid date (YYYY-MM-DD)';
                    }
                    break;
                    
                case 'datetime':
                    if (self::validateDateTime($value)) {
                        $validatedData[$field] = $value;
                    } else {
                        $errors[$field] = ucfirst($field) . ' must be a valid datetime (YYYY-MM-DD HH:MM:SS)';
                    }
                    break;
                    
                case 'select':
                    $options = $rule['options'] ?? [];
                    if (in_array($value, $options)) {
                        $validatedData[$field] = $value;
                    } else {
                        $errors[$field] = ucfirst($field) . ' must be one of: ' . implode(', ', $options);
                    }
                    break;
                    
                default:
                    $validatedData[$field] = self::sanitizeText($value);
            }
        }
        
        return [
            'valid' => empty($errors),
            'data' => $validatedData,
            'errors' => $errors
        ];
    }
    
    /**
     * Generate secure filename
     */
    public static function generateSecureFilename($originalName, $prefix = '') {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = $prefix . date('YmdHis') . '_' . bin2hex(random_bytes(8));
        
        if ($extension) {
            $filename .= '.' . $extension;
        }
        
        return $filename;
    }
    
    /**
     * Validate CSRF token using ErrorHandler
     */
    public static function validateCSRF($token) {
        return ErrorHandler::validateCSRF($token);
    }
}

/**
 * Validation rules for different forms
 */
class ValidationRules {
    
    public static function getInventoryRules() {
        return [
            'batch_number' => [
                'type' => 'text',
                'required' => true,
                'max_length' => 50
            ],
            'meat_type_id' => [
                'type' => 'number',
                'required' => true,
                'min' => 1
            ],
            'cut_type' => [
                'type' => 'text',
                'required' => false,
                'max_length' => 100
            ],
            'quantity' => [
                'type' => 'number',
                'required' => true,
                'min' => 0.01,
                'max' => 999999
            ],
            'processing_date' => [
                'type' => 'date',
                'required' => true
            ],
            'expiry_date' => [
                'type' => 'date',
                'required' => true
            ],
            'storage_location_id' => [
                'type' => 'number',
                'required' => true,
                'min' => 1
            ],
            'quality_notes' => [
                'type' => 'text',
                'required' => false,
                'max_length' => 1000
            ]
        ];
    }
    
    public static function getSpoilageRules() {
        return [
            'batch_number' => [
                'type' => 'text',
                'required' => true,
                'max_length' => 50
            ],
            'meat_type_id' => [
                'type' => 'number',
                'required' => true,
                'min' => 1
            ],
            'quantity' => [
                'type' => 'number',
                'required' => true,
                'min' => 0.01,
                'max' => 999999
            ],
            'reason' => [
                'type' => 'select',
                'required' => true,
                'options' => ['expiry', 'contamination', 'damage', 'temperature', 'other']
            ],
            'disposal_method' => [
                'type' => 'select',
                'required' => true,
                'options' => ['landfill', 'incineration', 'composting', 'rendering', 'other']
            ],
            'storage_location_id' => [
                'type' => 'number',
                'required' => true,
                'min' => 1
            ],
            'description' => [
                'type' => 'text',
                'required' => false,
                'max_length' => 1000
            ]
        ];
    }
    
    public static function getMonitoringRules() {
        return [
            'storage_location_id' => [
                'type' => 'number',
                'required' => true,
                'min' => 1
            ],
            'temperature' => [
                'type' => 'number',
                'required' => true,
                'min' => -50,
                'max' => 100,
                'decimals' => 2
            ],
            'humidity' => [
                'type' => 'number',
                'required' => true,
                'min' => 0,
                'max' => 100,
                'decimals' => 2
            ],
            'remarks' => [
                'type' => 'text',
                'required' => false,
                'max_length' => 500
            ]
        ];
    }
    
    public static function getUserRules() {
        return [
            'name' => [
                'type' => 'text',
                'required' => true,
                'max_length' => 100
            ],
            'email' => [
                'type' => 'email',
                'required' => true
            ],
            'role' => [
                'type' => 'select',
                'required' => true,
                'options' => ['admin', 'manager', 'supervisor', 'operator', 'viewer']
            ],
            'phone' => [
                'type' => 'text',
                'required' => false,
                'max_length' => 20
            ]
        ];
    }
}
?>