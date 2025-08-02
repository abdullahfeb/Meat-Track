<?php
/**
 * Centralized Error Handling for MeatTrack
 * Provides consistent error logging and user feedback
 */

class ErrorHandler {
    
    /**
     * Log error to system log
     */
    public static function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $user_id = $_SESSION['user_id'] ?? 'anonymous';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $log_entry = "[$timestamp] User: $user_id | IP: $ip | Message: $message";
        
        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }
        
        error_log($log_entry);
    }
    
    /**
     * Set user-friendly error message
     */
    public static function setUserError($message) {
        $_SESSION['error_message'] = $message;
    }
    
    /**
     * Set success message
     */
    public static function setUserSuccess($message) {
        $_SESSION['success_message'] = $message;
    }
    
    /**
     * Set warning message
     */
    public static function setUserWarning($message) {
        $_SESSION['warning_message'] = $message;
    }
    
    /**
     * Display all user messages
     */
    public static function displayMessages() {
        $messages = [
            'error' => $_SESSION['error_message'] ?? null,
            'success' => $_SESSION['success_message'] ?? null,
            'warning' => $_SESSION['warning_message'] ?? null
        ];
        
        foreach ($messages as $type => $message) {
            if ($message) {
                $icon = match($type) {
                    'error' => 'fas fa-exclamation-triangle',
                    'success' => 'fas fa-check-circle',
                    'warning' => 'fas fa-exclamation-circle',
                    default => 'fas fa-info-circle'
                };
                
                $class = match($type) {
                    'error' => 'alert-danger',
                    'success' => 'alert-success',
                    'warning' => 'alert-warning',
                    default => 'alert-info'
                };
                
                echo "<div class='alert $class alert-dismissible fade show' role='alert'>";
                echo "<i class='$icon me-2'></i>" . htmlspecialchars($message);
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                echo "</div>";
                
                unset($_SESSION[$type . '_message']);
            }
        }
    }
    
    /**
     * Handle database errors
     */
    public static function handleDatabaseError(PDOException $e, $user_message = "A database error occurred. Please try again.") {
        self::logError("Database Error: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        self::setUserError($user_message);
    }
    
    /**
     * Handle validation errors
     */
    public static function handleValidationError($field, $message) {
        self::logError("Validation Error: $field - $message");
        self::setUserError("Validation Error: $message");
    }
    
    /**
     * Handle authentication errors
     */
    public static function handleAuthError($message = "Authentication required") {
        self::logError("Authentication Error: $message");
        header('Location: login.php');
        exit;
    }
    
    /**
     * Handle permission errors
     */
    public static function handlePermissionError($required_role, $user_role) {
        self::logError("Permission Denied: Required role '$required_role', user has '$user_role'");
        self::setUserError("Access denied. Insufficient permissions.");
        header('Location: dashboard.php');
        exit;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            self::logError("CSRF Token Validation Failed");
            self::setUserError("Security validation failed. Please try again.");
            return false;
        }
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($fields, $data) {
        $missing = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::handleValidationError('required_fields', 'Missing required fields: ' . implode(', ', $missing));
            return false;
        }
        
        return true;
    }
    
    /**
     * Check system health
     */
    public static function checkSystemHealth($pdo) {
        $health = [
            'database' => false,
            'disk_space' => false,
            'memory' => false
        ];
        
        try {
            // Check database connection
            $pdo->query("SELECT 1");
            $health['database'] = true;
        } catch (PDOException $e) {
            self::logError("Database health check failed: " . $e->getMessage());
        }
        
        // Check disk space (at least 100MB free)
        $free_space = disk_free_space(__DIR__);
        $health['disk_space'] = $free_space > 100 * 1024 * 1024; // 100MB
        
        // Check memory usage (under 80%)
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = self::convertToBytes($memory_limit);
        $health['memory'] = ($memory_usage / $memory_limit_bytes) < 0.8;
        
        return $health;
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private static function convertToBytes($value) {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value
        };
    }
    
    /**
     * Generate system status report
     */
    public static function getSystemStatus($pdo) {
        $status = [
            'timestamp' => date('Y-m-d H:i:s'),
            'health' => self::checkSystemHealth($pdo),
            'stats' => []
        ];
        
        try {
            // Get database statistics
            $status['stats']['total_inventory'] = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
            $status['stats']['active_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
            $status['stats']['recent_alerts'] = $pdo->query("SELECT COUNT(*) FROM condition_monitoring WHERE recorded_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetchColumn();
        } catch (PDOException $e) {
            self::logError("Failed to get system statistics: " . $e->getMessage());
        }
        
        return $status;
    }
}

/**
 * Enhanced HTML escape function
 */
function esc_html($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function format_currency($amount, $currency = 'USD') {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function format_datetime($datetime, $format = 'M j, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Calculate time ago
 */
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}
?>