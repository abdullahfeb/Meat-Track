<?php
header('Content-Type: text/html');

require_once '../../config/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo '<h1>Invalid Verification Link</h1><p>The verification link is invalid or missing.</p>';
    exit;
}

try {
    // Find verification token
    $sql = "SELECT vt.*, u.email, u.full_name 
            FROM email_verification_tokens vt
            JOIN users u ON vt.user_id = u.id
            WHERE vt.token = ? AND vt.is_used = 0 AND vt.expires_at > NOW()";
    
    $verification = $db->fetchOne($sql, [$token]);
    
    if (!$verification) {
        echo '<h1>Invalid or Expired Token</h1><p>The verification link is invalid, expired, or has already been used.</p>';
        exit;
    }
    
    $db->beginTransaction();
    
    try {
        // Mark user as verified
        $updateUserSql = "UPDATE users SET is_verified = 1 WHERE id = ?";
        $db->execute($updateUserSql, [$verification['user_id']]);
        
        // Mark token as used
        $updateTokenSql = "UPDATE email_verification_tokens SET is_used = 1 WHERE id = ?";
        $db->execute($updateTokenSql, [$verification['id']]);
        
        // Log verification
        $logSql = "INSERT INTO activity_log (user_id, action_type, action_details) VALUES (?, 'email_verified', ?)";
        $db->execute($logSql, [
            $verification['user_id'],
            json_encode(['email' => $verification['email'], 'verified_at' => date('Y-m-d H:i:s')])
        ]);
        
        $db->commit();
        
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Verified - CollabAI</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8fafc; }
                .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
                .success { color: #10b981; font-size: 3rem; margin-bottom: 20px; }
                h1 { color: #1e293b; margin-bottom: 15px; }
                p { color: #64748b; margin-bottom: 30px; }
                .btn { background: #4f46e5; color: white; padding: 12px 24px; border: none; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; }
                .btn:hover { background: #4338ca; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success">✓</div>
                <h1>Email Verified Successfully!</h1>
                <p>Thank you, ' . htmlspecialchars($verification['full_name']) . '! Your email address has been verified and your account is now active.</p>
                <a href="/collabai/login.html" class="btn">Login to Your Account</a>
            </div>
        </body>
        </html>';
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Email verification error: ' . $e->getMessage());
    echo '<h1>Verification Failed</h1><p>An error occurred while verifying your email. Please try again or contact support.</p>';
}
?>