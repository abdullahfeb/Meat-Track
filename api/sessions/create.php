<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $aiModel = $_POST['ai_model'] ?? 'gpt-3.5-turbo';
    $maxParticipants = (int)($_POST['max_participants'] ?? 10);
    $accessCode = trim($_POST['access_code'] ?? '');
    $expiresAt = $_POST['expires_at'] ?? null;

    // Validate input
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Session title is required']);
        exit;
    }

    if (strlen($title) > 200) {
        echo json_encode(['success' => false, 'message' => 'Title too long (max 200 characters)']);
        exit;
    }

    if ($maxParticipants < 2 || $maxParticipants > 50) {
        echo json_encode(['success' => false, 'message' => 'Max participants must be between 2 and 50']);
        exit;
    }

    if (!in_array($aiModel, ['gpt-3.5-turbo', 'gpt-4', 'claude-3'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid AI model']);
        exit;
    }

    // Generate unique session ID
    $sessionId = bin2hex(random_bytes(16)); // 32 character hex string

    // Validate expiry date if provided
    $expiresAtFormatted = null;
    if (!empty($expiresAt)) {
        $expiresAtFormatted = date('Y-m-d H:i:s', strtotime($expiresAt));
        if (strtotime($expiresAtFormatted) <= time()) {
            echo json_encode(['success' => false, 'message' => 'Expiry date must be in the future']);
            exit;
        }
    }

    // Validate access code if provided
    if (!empty($accessCode)) {
        if (strlen($accessCode) < 4 || strlen($accessCode) > 20) {
            echo json_encode(['success' => false, 'message' => 'Access code must be between 4 and 20 characters']);
            exit;
        }
    }

    $db->beginTransaction();

    try {
        // Create session
        $sessionSql = "INSERT INTO chat_sessions (session_id, title, description, owner_id, access_code, expires_at, max_participants, ai_model) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $db->execute($sessionSql, [
            $sessionId,
            $title,
            $description,
            $userId,
            !empty($accessCode) ? $accessCode : null,
            $expiresAtFormatted,
            $maxParticipants,
            $aiModel
        ]);

        // Add owner as participant
        $participantSql = "INSERT INTO session_participants (session_id, user_id, role, invited_by) 
                          VALUES (?, ?, 'owner', ?)";
        $db->execute($participantSql, [$sessionId, $userId, $userId]);

        // Log session creation
        $logSql = "INSERT INTO activity_log (session_id, user_id, action_type, action_details, ip_address, user_agent) 
                   VALUES (?, ?, 'session_created', ?, ?, ?)";
        $db->execute($logSql, [
            $sessionId,
            $userId,
            json_encode([
                'title' => $title,
                'ai_model' => $aiModel,
                'max_participants' => $maxParticipants
            ]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // Add welcome message
        $welcomeMessage = "Welcome to \"{$title}\"! This collaborative AI session is powered by {$aiModel}. You can now start asking questions and collaborating with your team.";
        $messageSql = "INSERT INTO messages (session_id, message_type, content) VALUES (?, 'system', ?)";
        $db->execute($messageSql, [$sessionId, $welcomeMessage]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Session created successfully',
            'session_id' => $sessionId,
            'session' => [
                'session_id' => $sessionId,
                'title' => $title,
                'description' => $description,
                'ai_model' => $aiModel,
                'max_participants' => $maxParticipants,
                'has_access_code' => !empty($accessCode),
                'expires_at' => $expiresAtFormatted
            ]
        ]);

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Session creation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to create session']);
}
?>