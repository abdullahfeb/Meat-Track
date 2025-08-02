<?php
require 'config.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_alert'])) {
    if (!ErrorHandler::validateCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    try {
        $alertId = ErrorHandler::sanitizeInput($_POST['alert_id'] ?? '');
        if (empty($alertId) || !preg_match('/^(inv|cond)_\d+$/', $alertId)) {
            throw new Exception('Invalid alert ID format');
        }
        
        $stmt = $pdo->prepare('INSERT INTO dismissed_alerts (user_id, alert_identifier) VALUES (?, ?) ON DUPLICATE KEY UPDATE dismissed_at = NOW()');
        $stmt->execute([$_SESSION['user_id'], $alertId]);
        echo json_encode(['success' => true, 'message' => 'Alert dismissed successfully']);
    } catch (Exception $e) {
        ErrorHandler::logError("Dismiss alert error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error dismissing alert']);
    }
    exit;
}

try {
    // Get inventory statistics
    $stmt = $pdo->prepare('
        SELECT status, COUNT(*) as count, SUM(quantity) as total_quantity
        FROM inventory
        GROUP BY status
    ');
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get spoilage statistics (last 30 days)
    $stmt = $pdo->prepare('
        SELECT mt.name as meat_type, SUM(s.quantity) as total_quantity, COUNT(*) as count
        FROM spoilage s
        JOIN meat_types mt ON s.meat_type_id = mt.id
        WHERE s.recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY mt.id, mt.name
        ORDER BY total_quantity DESC
        LIMIT 10
    ');
    $stmt->execute();
    $spoilage = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get distribution statistics
    $stmt = $pdo->prepare('
        SELECT status, COUNT(*) as count
        FROM distribution
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY status
    ');
    $stmt->execute();
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent alerts count
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as alert_count
        FROM (
            SELECT id FROM inventory 
            WHERE status IN ("near_expiry", "expired") 
            AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            UNION
            SELECT id FROM condition_monitoring 
            WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ) as alerts
    ');
    $stmt->execute();
    $alertCount = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'inventory' => $inventory,
            'spoilage' => $spoilage,
            'distribution' => $distribution,
            'alert_count' => $alertCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (PDOException $e) {
    ErrorHandler::handleDatabaseError($e, 'Error loading dashboard data');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
