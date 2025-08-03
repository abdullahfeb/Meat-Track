<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

try {
    // Check if user is logged in via session
    if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
        $userId = $_SESSION['user_id'];
        $sessionToken = $_SESSION['session_token'];
        
        // Validate session token in database
        $sql = "SELECT us.*, u.username, u.email, u.full_name, u.is_verified 
                FROM user_sessions us 
                JOIN users u ON us.user_id = u.id 
                WHERE us.user_id = ? AND us.session_token = ? AND us.expires_at > NOW()";
        
        $session = $db->fetchOne($sql, [$userId, $sessionToken]);
        
        if ($session) {
            // Update last activity
            $updateSql = "UPDATE user_presence SET last_activity = NOW() WHERE user_id = ?";
            $db->execute($updateSql, [$userId]);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $session['user_id'],
                    'username' => $session['username'],
                    'email' => $session['email'],
                    'full_name' => $session['full_name']
                ]
            ]);
            exit;
        }
    }
    
    // Check for remember me cookie
    if (isset($_COOKIE['session_token'])) {
        $sessionToken = $_COOKIE['session_token'];
        
        $sql = "SELECT us.*, u.username, u.email, u.full_name, u.is_verified 
                FROM user_sessions us 
                JOIN users u ON us.user_id = u.id 
                WHERE us.session_token = ? AND us.expires_at > NOW()";
        
        $session = $db->fetchOne($sql, [$sessionToken]);
        
        if ($session) {
            // Restore session
            $_SESSION['user_id'] = $session['user_id'];
            $_SESSION['username'] = $session['username'];
            $_SESSION['email'] = $session['email'];
            $_SESSION['full_name'] = $session['full_name'];
            $_SESSION['session_token'] = $sessionToken;
            
            // Update last activity
            $updateSql = "UPDATE user_presence SET last_activity = NOW() WHERE user_id = ?";
            $db->execute($updateSql, [$session['user_id']]);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $session['user_id'],
                    'username' => $session['username'],
                    'email' => $session['email'],
                    'full_name' => $session['full_name']
                ]
            ]);
            exit;
        }
    }
    
    // No valid session found
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    
} catch (Exception $e) {
    error_log('Session check error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Session validation failed']);
}
?>