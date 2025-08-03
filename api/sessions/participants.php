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
    // Check if user has access to this session
    $accessSql = "SELECT role FROM session_participants WHERE session_id = ? AND user_id = ?";
    $userRole = $db->fetchOne($accessSql, [$sessionId, $userId]);
    
    if (!$userRole) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Get participants with online status
    $sql = "SELECT 
                sp.*,
                u.full_name,
                u.username,
                u.email,
                up.is_online,
                up.last_activity,
                inviter.full_name as invited_by_name
            FROM session_participants sp
            JOIN users u ON sp.user_id = u.id
            LEFT JOIN user_presence up ON sp.user_id = up.user_id
            LEFT JOIN users inviter ON sp.invited_by = inviter.id
            WHERE sp.session_id = ?
            ORDER BY sp.role = 'owner' DESC, sp.joined_at ASC";
    
    $participants = $db->fetchAll($sql, [$sessionId]);
    
    // Determine online status (consider online if activity within last 5 minutes)
    $currentTime = time();
    foreach ($participants as &$participant) {
        $lastActivity = strtotime($participant['last_activity']);
        $participant['is_online'] = $participant['is_online'] && 
                                  ($currentTime - $lastActivity) < 300; // 5 minutes
        
        // Format data
        $participant['joined_at'] = date('Y-m-d H:i:s', strtotime($participant['joined_at']));
        $participant['last_activity'] = $participant['last_activity'] ? 
                                      date('Y-m-d H:i:s', strtotime($participant['last_activity'])) : null;
    }
    
    echo json_encode([
        'success' => true,
        'participants' => $participants,
        'count' => count($participants)
    ]);

} catch (Exception $e) {
    error_log('List participants error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load participants']);
}
?>