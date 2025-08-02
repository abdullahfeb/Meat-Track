<?php
require 'config.php';
requireAuth();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid inventory ID']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT i.id, i.batch_number, i.meat_type_id, mt.name as meat_type, 
               i.quantity, i.processing_date, i.expiry_date, i.storage_location_id,
               sl.name as storage_location
        FROM inventory i
        JOIN meat_types mt ON i.meat_type_id = mt.id
        JOIN storage_locations sl ON i.storage_location_id = sl.id
        WHERE i.id = ? AND i.status != "deleted"
    ');
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
} catch (PDOException $e) {
    error_log("Get inventory item error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>