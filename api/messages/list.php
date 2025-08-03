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
$afterId = (int)($_GET['after'] ?? 0);
$limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 messages at once
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

    // Get messages
    $sql = "SELECT 
                m.*,
                u.full_name,
                u.username
            FROM messages m
            LEFT JOIN users u ON m.user_id = u.id
            WHERE m.session_id = ? 
                AND m.is_deleted = 0 
                AND m.id > ?
            ORDER BY m.created_at ASC, m.id ASC
            LIMIT ?";
    
    $messages = $db->fetchAll($sql, [$sessionId, $afterId, $limit]);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);

} catch (Exception $e) {
    error_log('List messages error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load messages']);
}
?>