<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get sessions where user is a participant
    $sql = "SELECT 
                s.*,
                u.full_name as owner_name,
                u.username as owner_username,
                sp.role as user_role,
                ss.message_count,
                ss.unique_contributors,
                ss.last_message_at,
                ss.total_participants as participant_count
            FROM chat_sessions s
            LEFT JOIN users u ON s.owner_id = u.id
            LEFT JOIN session_participants sp ON s.session_id = sp.session_id AND sp.user_id = ?
            LEFT JOIN session_stats ss ON s.session_id = ss.session_id
            WHERE sp.user_id = ?
            ORDER BY s.updated_at DESC";
    
    $sessions = $db->fetchAll($sql, [$userId, $userId]);
    
    // Format the sessions data
    $formattedSessions = [];
    foreach ($sessions as $session) {
        // Check if session is expired
        $isExpired = $session['expires_at'] && strtotime($session['expires_at']) <= time();
        
        $formattedSessions[] = [
            'session_id' => $session['session_id'],
            'title' => $session['title'],
            'description' => $session['description'],
            'ai_model' => $session['ai_model'],
            'max_participants' => $session['max_participants'],
            'is_active' => $session['is_active'] && !$isExpired,
            'expires_at' => $session['expires_at'],
            'created_at' => $session['created_at'],
            'updated_at' => $session['updated_at'],
            'owner_name' => $session['owner_name'],
            'owner_username' => $session['owner_username'],
            'user_role' => $session['user_role'],
            'message_count' => (int)($session['message_count'] ?? 0),
            'participant_count' => (int)($session['participant_count'] ?? 0),
            'unique_contributors' => (int)($session['unique_contributors'] ?? 0),
            'last_message_at' => $session['last_message_at'],
            'has_access_code' => !empty($session['access_code']),
            'is_owner' => $session['user_role'] === 'owner'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'sessions' => $formattedSessions,
        'total' => count($formattedSessions)
    ]);

} catch (Exception $e) {
    error_log('List sessions error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load sessions']);
}
?>