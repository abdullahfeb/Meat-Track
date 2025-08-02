<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor', 'operator', 'viewer']);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Handle inventory form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_inventory') {
    if (!Validator::validateCSRF($_POST['csrf_token'] ?? '')) {
        ErrorHandler::setUserError("Invalid CSRF token.");
    } else {
        // Validate input data
        $validation = Validator::validateArray($_POST, ValidationRules::getInventoryRules());
        
        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                ErrorHandler::setUserError($error);
                break; // Show first error only
            }
        } else {
            try {
                $data = $validation['data'];
                
                // Additional business logic validation
                if (strtotime($data['expiry_date']) <= strtotime($data['processing_date'])) {
                    ErrorHandler::setUserError("Expiry date must be after processing date.");
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory (batch_number, meat_type_id, cut_type, quantity, processing_date, expiry_date, storage_location_id, quality_notes, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'good')
                    ");
                    $stmt->execute([
                        $data['batch_number'],
                        $data['meat_type_id'],
                        $data['cut_type'],
                        $data['quantity'],
                        $data['processing_date'],
                        $data['expiry_date'],
                        $data['storage_location_id'],
                        $data['quality_notes']
                    ]);
                    ErrorHandler::setUserSuccess("Inventory added successfully.");
                }
            } catch (PDOException $e) {
                ErrorHandler::handleDatabaseError($e, "Error adding inventory. Please try again.");
            }
        }
    }
    header('Location: inventory_management.php?page=' . $page);
    exit;
}

// Dismiss alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_alert'])) {
    if (!Validator::validateCSRF($_POST['csrf_token'] ?? '')) {
        ErrorHandler::setUserError("Invalid CSRF token.");
    } else {
        $alertId = Validator::sanitizeText($_POST['alert_identifier'] ?? '');
        if (empty($alertId) || !preg_match('/^(inv|cond)_\d+$/', $alertId)) {
            ErrorHandler::setUserError("Invalid alert identifier.");
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO dismissed_alerts (user_id, alert_identifier)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE dismissed_at = NOW()
                ");
                $stmt->execute([$_SESSION['user_id'], $alertId]);
                ErrorHandler::setUserSuccess("Alert dismissed successfully.");
            } catch (PDOException $e) {
                ErrorHandler::handleDatabaseError($e, "Error dismissing alert. Please try again.");
            }
        }
    }
    header('Location: inventory_management.php?page=' . $page);
    exit;
}

// Fetch system settings
$settings = $pdo->query("SELECT * FROM system_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [
    'expiry_alerts' => 1,
    'expiry_days_before' => 3,
    'max_temp' => 4.0,
    'min_temp' => 0.0,
    'max_humidity' => 80,
    'min_humidity' => 65,
    'monitoring_alerts' => 1,
    'spoilage_alert_threshold' => 5,
    'inventory_low_threshold' => 10
];

// Fetch alerts
$alerts = [];
if ($settings['expiry_alerts']) {
    $stmt = $pdo->prepare("
        SELECT i.id, i.batch_number, mt.name as meat_type, i.expiry_date, 'inventory' as type,
               CASE 
                   WHEN i.expiry_date < CURDATE() THEN 'Expired'
                   ELSE 'Near Expiry'
               END as message
        FROM inventory i
        JOIN meat_types mt ON i.meat_type_id = mt.id
        WHERE i.status IN ('good', 'near_expiry')
        AND i.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da
            WHERE da.user_id = ?
            AND da.alert_identifier = CONCAT('inv_', i.id)
        )
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$settings['expiry_days_before'], $_SESSION['user_id'], $perPage, $offset]);
    $inventoryAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($inventoryAlerts as $alert) {
        $alert['identifier'] = 'inv_' . $alert['id'];
        $alerts[] = $alert;
    }
}

if ($settings['monitoring_alerts']) {
    $stmt = $pdo->prepare("
        SELECT cm.id, sl.name as location, cm.temperature, cm.humidity, cm.recorded_at, 'condition' as type,
               CASE 
                   WHEN cm.temperature > ? OR cm.temperature < ?
                   THEN CONCAT('Temperature out of range: ', cm.temperature, '°C')
                   WHEN cm.humidity > ? OR cm.humidity < ?
                   THEN CONCAT('Humidity out of range: ', cm.humidity, '%')
               END as message
        FROM condition_monitoring cm
        JOIN storage_locations sl ON cm.storage_location_id = sl.id
        WHERE (
            cm.temperature > ? OR cm.temperature < ?
            OR cm.humidity > ? OR cm.humidity < ?
        )
        AND NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da
            WHERE da.user_id = ?
            AND da.alert_identifier = CONCAT('cond_', cm.id)
        )
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([
        $settings['max_temp'], $settings['min_temp'],
        $settings['max_humidity'], $settings['min_humidity'],
        $settings['max_temp'], $settings['min_temp'],
        $settings['max_humidity'], $settings['min_humidity'],
        $_SESSION['user_id'],
        $perPage, $offset
    ]);
    $conditionAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($conditionAlerts as $alert) {
        $alert['identifier'] = 'cond_' . $alert['id'];
        $alerts[] = $alert;
    }
}

// Count total alerts for pagination
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count
    FROM (
        SELECT i.id
        FROM inventory i
        WHERE i.status IN ('good', 'near_expiry')
        AND i.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da
            WHERE da.user_id = ?
            AND da.alert_identifier = CONCAT('inv_', i.id)
        )
        UNION
        SELECT cm.id
        FROM condition_monitoring cm
        WHERE (
            cm.temperature > ? OR cm.temperature < ?
            OR cm.humidity > ? OR cm.humidity < ?
        )
        AND NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da
            WHERE da.user_id = ?
            AND da.alert_identifier = CONCAT('cond_', cm.id)
        )
    ) as combined
");
$stmt->execute([
    $settings['expiry_days_before'],
    $_SESSION['user_id'],
    $settings['max_temp'], $settings['min_temp'],
    $settings['max_humidity'], $settings['min_humidity'],
    $_SESSION['user_id']
]);
$totalAlerts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($totalAlerts / $perPage);

// Fetch inventory data for table
$stmt = $pdo->prepare("
    SELECT i.*, mt.name as meat_type, sl.name as storage_location
    FROM inventory i
    JOIN meat_types mt ON i.meat_type_id = mt.id
    JOIN storage_locations sl ON i.storage_location_id = sl.id
    ORDER BY i.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total inventory count for pagination
$totalInventoryCount = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$totalInventoryPages = ceil($totalInventoryCount / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-boxes me-2"></i>Inventory Management</h1>
                    <div class="btn-group">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">
                            <i class="fas fa-plus me-1"></i>Add Inventory
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>

                <?php ErrorHandler::displayMessages(); ?>

                <!-- Alerts Section -->
                <?php if (!empty($alerts)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Active Alerts</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Details</th>
                                                <th>Message</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($alerts as $alert): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-<?= $alert['type'] === 'inventory' ? 'warning' : 'danger' ?>">
                                                            <?= ucfirst($alert['type']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($alert['type'] === 'inventory'): ?>
                                                            <strong>Batch:</strong> <?= esc_html($alert['batch_number']) ?><br>
                                                            <strong>Type:</strong> <?= esc_html($alert['meat_type']) ?><br>
                                                            <strong>Expiry:</strong> <?= date('M j, Y', strtotime($alert['expiry_date'])) ?>
                                                        <?php else: ?>
                                                            <strong>Location:</strong> <?= esc_html($alert['location']) ?><br>
                                                            <strong>Temp:</strong> <?= number_format($alert['temperature'], 2) ?>°C<br>
                                                            <strong>Humidity:</strong> <?= number_format($alert['humidity'], 2) ?>%
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= esc_html($alert['message']) ?></td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                            <input type="hidden" name="alert_identifier" value="<?= esc_html($alert['identifier']) ?>">
                                                            <button type="submit" name="dismiss_alert" class="btn btn-sm btn-secondary">
                                                                <i class="fas fa-times me-1"></i>Dismiss
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Inventory Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Inventory Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Batch Number</th>
                                                <th>Meat Type</th>
                                                <th>Cut Type</th>
                                                <th>Quantity (kg)</th>
                                                <th>Processing Date</th>
                                                <th>Expiry Date</th>
                                                <th>Storage Location</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inventoryItems as $item): ?>
                                                <tr>
                                                    <td><?= esc_html($item['batch_number']) ?></td>
                                                    <td><?= esc_html($item['meat_type']) ?></td>
                                                    <td><?= esc_html($item['cut_type'] ?: 'N/A') ?></td>
                                                    <td><?= number_format($item['quantity'], 2) ?></td>
                                                    <td><?= date('M j, Y', strtotime($item['processing_date'])) ?></td>
                                                    <td><?= date('M j, Y', strtotime($item['expiry_date'])) ?></td>
                                                    <td><?= esc_html($item['storage_location']) ?></td>
                                                    <td>
                                                        <?php
                                                        $badgeClass = match($item['status']) {
                                                            'good' => 'bg-success',
                                                            'near_expiry' => 'bg-warning',
                                                            'spoiled' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-info" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalInventoryPages > 1): ?>
                                <nav aria-label="Inventory pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $totalInventoryPages; $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Modal -->
                <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="inventoryModalLabel">
                                    <i class="fas fa-plus me-2"></i>Add New Inventory Item
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="inventoryForm" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="add_inventory">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="batch_number" class="form-label">Batch Number *</label>
                                                <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="meat_type_id" class="form-label">Meat Type *</label>
                                                <select class="form-select" id="meat_type_id" name="meat_type_id" required>
                                                    <option value="">Select type</option>
                                                    <?php
                                                    $meatTypes = $pdo->query("SELECT * FROM meat_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($meatTypes as $type):
                                                    ?>
                                                        <option value="<?= $type['id'] ?>"><?= esc_html($type['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cut_type" class="form-label">Cut Type</label>
                                                <input type="text" class="form-control" id="cut_type" name="cut_type" placeholder="e.g., Sirloin, Ribeye">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity (kg) *</label>
                                                <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required min="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="processing_date" class="form-label">Processing Date *</label>
                                                <input type="date" class="form-control" id="processing_date" name="processing_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="expiry_date" class="form-label">Expiry Date *</label>
                                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="storage_location_id" class="form-label">Storage Location *</label>
                                        <select class="form-select" id="storage_location_id" name="storage_location_id" required>
                                            <option value="">Select location</option>
                                            <?php
                                            $locations = $pdo->query("SELECT * FROM storage_locations ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($locations as $location):
                                            ?>
                                                <option value="<?= $location['id'] ?>"><?= esc_html($location['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quality_notes" class="form-label">Quality Notes</label>
                                        <textarea class="form-control" id="quality_notes" name="quality_notes" rows="3" placeholder="Optional quality observations or notes"></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Inventory Item
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set current date as default for processing date
        document.getElementById('processing_date').valueAsDate = new Date();
        
        // Auto-calculate expiry date based on processing date (add 30 days by default)
        document.getElementById('processing_date').addEventListener('change', function() {
            const processingDate = new Date(this.value);
            processingDate.setDate(processingDate.getDate() + 30);
            document.getElementById('expiry_date').valueAsDate = processingDate;
        });

        // Form validation
        document.getElementById('inventoryForm').addEventListener('submit', function(e) {
            const processingDate = new Date(document.getElementById('processing_date').value);
            const expiryDate = new Date(document.getElementById('expiry_date').value);
            
            if (expiryDate <= processingDate) {
                e.preventDefault();
                alert('Expiry date must be after processing date.');
                return false;
            }
        });
    </script>
</body>
</html>