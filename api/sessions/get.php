<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$sessionId = $_GET['session_id'] ?? '';
$userId = $_SESSION['user_id'];

if (empty($sessionId)) {
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit;
}

try {
    // Get session details with user's role
    $sql = "SELECT 
                s.*,
                sp.role as user_role,
                sp.joined_at,
                u.full_name as owner_name,
                u.username as owner_username,
                (SELECT COUNT(*) FROM session_participants WHERE session_id = s.session_id) as participant_count,
                (SELECT COUNT(*) FROM messages WHERE session_id = s.session_id AND is_deleted = 0) as message_count
            FROM chat_sessions s
            LEFT JOIN session_participants sp ON s.session_id = sp.session_id AND sp.user_id = ?
            LEFT JOIN users u ON s.owner_id = u.id
            WHERE s.session_id = ?";
    
    $session = $db->fetchOne($sql, [$userId, $sessionId]);
    
    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
        exit;
    }
    
    // Check if user has access to this session
    if (!$session['user_role']) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Check if session is active and not expired
    $isExpired = $session['expires_at'] && strtotime($session['expires_at']) <= time();
    
    if (!$session['is_active'] || $isExpired) {
        echo json_encode(['success' => false, 'message' => 'Session is no longer active']);
        exit;
    }
    
    // Update user's last seen timestamp
    $updateSql = "UPDATE session_participants SET last_seen_at = NOW() WHERE session_id = ? AND user_id = ?";
    $db->execute($updateSql, [$sessionId, $userId]);
    
    // Update user presence
    $presenceSql = "INSERT INTO user_presence (user_id, session_id, is_online, last_activity) 
                    VALUES (?, ?, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE session_id = ?, is_online = 1, last_activity = NOW()";
    $db->execute($presenceSql, [$userId, $sessionId, $sessionId]);
    
    echo json_encode([
        'success' => true,
        'session' => [
            'session_id' => $session['session_id'],
            'title' => $session['title'],
            'description' => $session['description'],
            'ai_model' => $session['ai_model'],
            'max_participants' => $session['max_participants'],
            'is_active' => $session['is_active'],
            'expires_at' => $session['expires_at'],
            'created_at' => $session['created_at'],
            'owner_name' => $session['owner_name'],
            'owner_username' => $session['owner_username'],
            'user_role' => $session['user_role'],
            'participant_count' => (int)$session['participant_count'],
            'message_count' => (int)$session['message_count'],
            'has_access_code' => !empty($session['access_code']),
            'is_owner' => $session['user_role'] === 'owner'
        ]
    ]);

} catch (Exception $e) {
    error_log('Get session error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load session']);
}
?>