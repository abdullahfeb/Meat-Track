<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update typing status
    $sessionId = $_POST['session_id'] ?? '';
    $isTyping = ($_POST['is_typing'] ?? '0') === '1';
    
    if (empty($sessionId)) {
        echo json_encode(['success' => false, 'message' => 'Session ID is required']);
        exit;
    }
    
    try {
        // Check if user has access to this session
        $accessSql = "SELECT role FROM session_participants WHERE session_id = ? AND user_id = ?";
        $userRole = $db->fetchOne($accessSql, [$sessionId, $userId]);
        
        if (!$userRole) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        // Update typing status
        $updateSql = "INSERT INTO user_presence (user_id, session_id, is_typing, typing_started_at, last_activity) 
                      VALUES (?, ?, ?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE 
                          session_id = ?, 
                          is_typing = ?, 
                          typing_started_at = IF(? = 1, IF(is_typing = 0, NOW(), typing_started_at), NULL),
                          last_activity = NOW()";
        
        $db->execute($updateSql, [
            $userId, $sessionId, $isTyping, $isTyping ? date('Y-m-d H:i:s') : null,
            $sessionId, $isTyping, $isTyping
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Typing status updated']);
        
    } catch (Exception $e) {
        error_log('Update typing status error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update typing status']);
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get typing users for a session
    $sessionId = $_GET['session_id'] ?? '';
    
    if (empty($sessionId)) {
        echo json_encode(['success' => false, 'message' => 'Session ID is required']);
        exit;
    }
    
    try {
        // Check if user has access to this session
        $accessSql = "SELECT role FROM session_participants WHERE session_id = ? AND user_id = ?";
        $userRole = $db->fetchOne($accessSql, [$sessionId, $userId]);
        
        if (!$userRole) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        // Get currently typing users (typing within last 3 seconds)
        $typingSql = "SELECT 
                          up.user_id,
                          u.full_name,
                          u.username,
                          up.typing_started_at
                      FROM user_presence up
                      JOIN users u ON up.user_id = u.id
                      WHERE up.session_id = ? 
                          AND up.is_typing = 1 
                          AND up.typing_started_at > DATE_SUB(NOW(), INTERVAL 3 SECOND)
                      ORDER BY up.typing_started_at ASC";
        
        $typingUsers = $db->fetchAll($typingSql, [$sessionId]);
        
        echo json_encode([
            'success' => true,
            'typing_users' => $typingUsers,
            'count' => count($typingUsers)
        ]);
        
    } catch (Exception $e) {
        error_log('Get typing users error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get typing users']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>