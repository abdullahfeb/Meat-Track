<?php
// Enhanced session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Regenerate session ID on login for security
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
require_once 'error_handler.php';
require_once 'validation.php';

// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'meettrack');
define('DB_USER', 'root'); // Default XAMPP MySQL username
define('DB_PASS', ''); // Default XAMPP MySQL password (empty)

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("System error. Please contact the administrator.");
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Centralized CSRF validation
function validateCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication check
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser($pdo) {
    if (!isAuthenticated()) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Restrict access to specific roles
function restrictToRoles($pdo, $allowedRoles) {
    $user = getCurrentUser($pdo);
    if (!$user || !in_array($user['role'], $allowedRoles)) {
        $_SESSION['error_message'] = "Access denied.";
        header('Location: index.php');
        exit;
    }
}

// HTML escape function
function esc_html($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Pagination helper
function getPagination($pdo, $table, $perPage, $page) {
    $totalRecords = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($page - 1) * $perPage;
    return ['totalPages' => $totalPages, 'offset' => $offset];
}
?>