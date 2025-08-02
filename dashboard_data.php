<?php
require 'config.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_alert'])) {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    try {
        $alertId = $_POST['alert_id'] ?? '';
        if (empty($alertId)) {
            throw new Exception('Invalid alert ID');
        }
        $stmt = $pdo->prepare('INSERT INTO dismissed_alerts (user_id, alert_identifier) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $alertId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Dismiss alert error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error dismissing alert']);
    }
    exit;
}

try {
    $inventory = $pdo->query('
        SELECT status, COUNT(*) as count
        FROM inventory
        GROUP BY status
    ')->fetchAll(PDO::FETCH_ASSOC);

    $spoilage = $pdo->query('
        SELECT mt.name as meat_type, SUM(s.quantity) as total_quantity
        FROM spoilage s
        JOIN meat_types mt ON s.meat_type_id = mt.id
        GROUP BY mt.id
    ')->fetchAll(PDO::FETCH_ASSOC);

    $distribution = $pdo->query('
        SELECT status, COUNT(*) as count
        FROM distribution
        GROUP BY status
    ')->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'inventory' => $inventory,
            'spoilage' => $spoilage,
            'distribution' => $distribution
        ]
    ]);
} catch (PDOException $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
