<?php
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
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        exit;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
        exit;
    }

    // Check if email already exists
    $emailCheckSql = "SELECT id FROM users WHERE email = ?";
    $existingEmail = $db->fetchOne($emailCheckSql, [$email]);
    
    if ($existingEmail) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
        exit;
    }

    // Check if username already exists
    $usernameCheckSql = "SELECT id FROM users WHERE username = ?";
    $existingUsername = $db->fetchOne($usernameCheckSql, [$username]);
    
    if ($existingUsername) {
        echo json_encode(['success' => false, 'message' => 'This username is already taken']);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Generate email verification token
    $verificationToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + (24 * 3600)); // 24 hours

    $db->beginTransaction();

    try {
        // Create user account
        $userSql = "INSERT INTO users (username, email, password_hash, full_name, is_verified) 
                    VALUES (?, ?, ?, ?, 0)";
        $db->execute($userSql, [$username, $email, $passwordHash, $fullName]);
        $userId = $db->lastInsertId();

        // Create email verification token
        $tokenSql = "INSERT INTO email_verification_tokens (user_id, token, expires_at) 
                     VALUES (?, ?, ?)";
        $db->execute($tokenSql, [$userId, $verificationToken, $expiresAt]);

        // Log user registration
        $logSql = "INSERT INTO activity_log (user_id, action_type, action_details, ip_address, user_agent) 
                   VALUES (?, 'user_registered', ?, ?, ?)";
        $db->execute($logSql, [
            $userId,
            json_encode(['email' => $email, 'username' => $username]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        $db->commit();

        // Send verification email (simplified for demo)
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/api/auth/verify-email.php?token=" . $verificationToken;
        
        // In a real application, you would send this via email
        // For demo purposes, we'll just log it
        error_log("Email verification link for {$email}: {$verificationLink}");

        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Please check your email to verify your account.',
            'verification_link' => $verificationLink // Remove this in production
        ]);

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'email') !== false) {
            echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
        } else if (strpos($e->getMessage(), 'username') !== false) {
            echo json_encode(['success' => false, 'message' => 'This username is already taken']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Account already exists']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
    }
}
?>