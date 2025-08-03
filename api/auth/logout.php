<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $userId = $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        // Update user presence to offline
        $presenceSql = "UPDATE user_presence SET is_online = 0, is_typing = 0, typing_started_at = NULL WHERE user_id = ?";
        $db->execute($presenceSql, [$userId]);
        
        // Log logout activity
        $logSql = "INSERT INTO activity_log (user_id, action_type, action_details, ip_address, user_agent) 
                   VALUES (?, 'user_logout', ?, ?, ?)";
        $db->execute($logSql, [
            $userId,
            json_encode(['logout_time' => date('Y-m-d H:i:s')]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        // Remove session from database if token exists
        if (isset($_SESSION['session_token'])) {
            $deleteSql = "DELETE FROM user_sessions WHERE session_token = ?";
            $db->execute($deleteSql, [$_SESSION['session_token']]);
        }
    }
    
    // Clear session data
    session_unset();
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['session_token'])) {
        setcookie('session_token', '', time() - 3600, '/', '', false, true);
    }
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    
} catch (Exception $e) {
    error_log('Logout error: ' . $e->getMessage());
    
    // Still clear session even if database operations fail
    session_unset();
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>