<?php
require 'config.php';
requireAuth();
header('Location: dashboard.php');
exit;
restrictToRoles($pdo, ['admin', 'manager', 'supervisor', 'operator', 'viewer']);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Handle inventory form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_inventory') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO inventory (batch_number, meat_type_id, cut_type, quantity, processing_date, expiry_date, storage_location_id, quality_notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'good')
            ");
            $stmt->execute([
                $_POST['batch_number'],
                $_POST['meat_type_id'],
                $_POST['cut_type'] ?: null,
                $_POST['quantity'],
                $_POST['processing_date'],
                $_POST['expiry_date'],
                $_POST['storage_location_id'],
                $_POST['quality_notes'] ?: null
            ]);
            $_SESSION['success_message'] = "Inventory added successfully.";
        } catch (PDOException $e) {
            error_log("Error adding inventory: " . $e->getMessage());
            $_SESSION['error_message'] = "Error adding inventory.";
        }
    }
    header('Location: index.php?page=' . $page);
    exit;
}

// Dismiss alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_alert'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO dismissed_alerts (user_id, alert_identifier)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE dismissed_at = NOW()
            ");
            $stmt->execute([$_SESSION['user_id'], $_POST['alert_identifier']]);
        } catch (PDOException $e) {
            error_log("Error dismissing alert: " . $e->getMessage());
            $_SESSION['error_message'] = "Error dismissing alert.";
        }
    }
    header('Location: index.php?page=' . $page);
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

// Fetch inventory stats
$inventoryStats = $pdo->query("
    SELECT 
        mt.name as meat_type,
        SUM(i.quantity) as total_quantity,
        SUM(CASE WHEN i.status = 'good' THEN i.quantity ELSE 0 END) as good_quantity,
        SUM(CASE WHEN i.status = 'near_expiry' THEN i.quantity ELSE 0 END) as near_expiry_quantity,
        SUM(CASE WHEN i.status = 'spoiled' THEN i.quantity ELSE 0 END) as spoiled_quantity
    FROM inventory i
    JOIN meat_types mt ON i.meat_type_id = mt.id
    GROUP BY mt.id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent spoilage
$recentSpoilage = $pdo->query("
    SELECT s.batch_number, mt.name as meat_type, s.quantity, s.reason, s.recorded_at
    FROM spoilage s
    JOIN meat_types mt ON s.meat_type_id = mt.id
    ORDER BY s.recorded_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">Add Inventory</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
                    </div>
                </div>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= esc_html($_SESSION['error_message']) ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= esc_html($_SESSION['success_message']) ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                <div class="row">
                    <?php foreach ($inventoryStats as $stat): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <?= esc_html($stat['meat_type']) ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($stat['total_quantity'], 2) ?> kg
                                            </div>
                                            <div class="text-xs text-success">
                                                Good: <?= number_format($stat['good_quantity'], 2) ?> kg
                                            </div>
                                            <div class="text-xs text-warning">
                                                Near Expiry: <?= number_format($stat['near_expiry_quantity'], 2) ?> kg
                                            </div>
                                            <div class="text-xs text-danger">
                                                Spoiled: <?= number_format($stat['spoiled_quantity'], 2) ?> kg
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Meat Type Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="meatTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Loss by Stage</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="lossStageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Spoilage Statistics</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Spoilage Percentage:</strong> <span id="spoilagePercentage">0%</span></p>
                                <p><strong>Total Spoiled:</strong> <span id="totalSpoiled">0 kg</span></p>
                                <p><strong>Total Inventory:</strong> <span id="totalInventory">0 kg</span></p>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Condition Alerts</h6>
                            </div>
                            <div class="card-body">
                                <div id="conditionAlerts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Expiring Soon</h6>
                            </div>
                            <div class="card-body">
                                <ul id="expiringSoonList" class="list-group"></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Spoilage</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Batch</th>
                                                <th>Type</th>
                                                <th>Qty (kg)</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentSpoilage as $spoilage): ?>
                                                <tr>
                                                    <td><?= esc_html($spoilage['batch_number']) ?></td>
                                                    <td><?= esc_html($spoilage['meat_type']) ?></td>
                                                    <td><?= number_format($spoilage['quantity'], 2) ?></td>
                                                    <td><?= esc_html($spoilage['reason']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Active Alerts</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
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
                                                    <td><?= ucfirst($alert['type']) ?></td>
                                                    <td>
                                                        <?php if ($alert['type'] === 'inventory'): ?>
                                                            Batch: <?= esc_html($alert['batch_number']) ?><br>
                                                            Type: <?= esc_html($alert['meat_type']) ?><br>
                                                            Expiry: <?= date('M j, Y', strtotime($alert['expiry_date'])) ?>
                                                        <?php else: ?>
                                                            Location: <?= esc_html($alert['location']) ?><br>
                                                            Temp: <?= number_format($alert['temperature'], 2) ?>°C<br>
                                                            Humidity: <?= number_format($alert['humidity'], 2) ?>%
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= esc_html($alert['message']) ?></td>
                                                    <td>
                                                        <form method="POST">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                            <input type="hidden" name="alert_identifier" value="<?= esc_html($alert['identifier']) ?>">
                                                            <button type="submit" name="dismiss_alert" class="btn btn-sm btn-secondary">Dismiss</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <nav>
                                        <ul class="pagination">
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Inventory Modal -->
                <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="inventoryModalLabel">Add Inventory</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="inventoryForm" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="add_inventory">
                                    <div class="mb-3">
                                        <label for="batch_number" class="form-label">Batch Number *</label>
                                        <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                                    </div>
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
                                    <div class="mb-3">
                                        <label for="cut_type" class="form-label">Cut Type</label>
                                        <input type="text" class="form-control" id="cut_type" name="cut_type">
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity (kg) *</label>
                                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required min="0.01">
                                    </div>
                                    <div class="mb-3">
                                        <label for="processing_date" class="form-label">Processing Date *</label>
                                        <input type="date" class="form-control" id="processing_date" name="processing_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="expiry_date" class="form-label">Expiry Date *</label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
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
                                        <textarea class="form-control" id="quality_notes" name="quality_notes"></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="button" id="saveInventory" class="btn btn-primary">Save</button>
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
    <script src="dashboard.js"></script>
</body>
</html>
