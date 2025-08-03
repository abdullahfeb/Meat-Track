<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Find user by email
    $sql = "SELECT id, username, email, password_hash, full_name, is_verified FROM users WHERE email = ? AND is_verified = 1";
    $user = $db->fetchOne($sql, [$email]);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Log failed login attempt
        $logSql = "INSERT INTO activity_log (user_id, action_type, action_details, ip_address, user_agent) 
                   VALUES (?, 'failed_login', ?, ?, ?)";
        $db->execute($logSql, [
            $user['id'],
            json_encode(['email' => $email]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Generate session token
    $sessionToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + ($remember ? 30 * 24 * 3600 : 24 * 3600)); // 30 days if remember, 1 day otherwise

    // Store session in database
    $sessionSql = "INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
                   VALUES (?, ?, ?, ?, ?)";
    $db->execute($sessionSql, [
        $user['id'],
        $sessionToken,
        $expiresAt,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['session_token'] = $sessionToken;

    // Set cookie if remember me is checked
    if ($remember) {
        setcookie('session_token', $sessionToken, time() + (30 * 24 * 3600), '/', '', false, true);
    }

    // Log successful login
    $logSql = "INSERT INTO activity_log (user_id, action_type, action_details, ip_address, user_agent) 
               VALUES (?, 'user_login', ?, ?, ?)";
    $db->execute($logSql, [
        $user['id'],
        json_encode(['success' => true]),
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    // Update user presence
    $presenceSql = "INSERT INTO user_presence (user_id, is_online, last_activity) 
                    VALUES (?, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE is_online = 1, last_activity = NOW()";
    $db->execute($presenceSql, [$user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name']
        ]
    ]);

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
}
?>