<?php
require 'config.php';
requireAuth();

$user = getCurrentUser($pdo);
$alerts = [];

try {
    $expiryDaysBefore = $pdo->query("SELECT expiry_days_before FROM system_settings WHERE id = 1")->fetchColumn() ?: 3;
    $inventoryAlerts = $pdo->prepare("
        SELECT i.id, i.batch_number, i.expiry_date, mt.name as meat_type
        FROM inventory i
        JOIN meat_types mt ON i.meat_type_id = mt.id
        WHERE i.status = 'good' AND i.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da 
            WHERE da.user_id = ? AND da.alert_identifier = CONCAT('inv_', i.id)
        )
    ");
    $inventoryAlerts->execute([$expiryDaysBefore, $_SESSION['user_id']]);
    $alerts['inventory'] = $inventoryAlerts->fetchAll();

    $conditionAlerts = $pdo->prepare("
        SELECT cm.id, cm.temperature, cm.humidity, sl.name as location_name
        FROM condition_monitoring cm
        JOIN storage_locations sl ON cm.storage_location_id = sl.id
        JOIN system_settings ss ON (
            cm.temperature > ss.max_temp OR cm.temperature < ss.min_temp OR
            cm.humidity > ss.max_humidity OR cm.humidity < ss.min_humidity
        )
        WHERE NOT EXISTS (
            SELECT 1 FROM dismissed_alerts da 
            WHERE da.user_id = ? AND da.alert_identifier = CONCAT('cond_', cm.id)
        )
        LIMIT 5
    ");
    $conditionAlerts->execute([$_SESSION['user_id']]);
    $alerts['conditions'] = $conditionAlerts->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard alerts error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error loading alerts.';
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-group">
                        <a href="reports.php" class="btn btn-outline-secondary">View Reports</a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
                    </div>
                </div>
                <?php ErrorHandler::displayMessages(); ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Inventory Status</div>
                            <div class="card-body">
                                <canvas id="inventoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Spoilage Overview</div>
                            <div class="card-body">
                                <canvas id="spoilageChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Distribution Status</div>
                            <div class="card-body">
                                <canvas id="distributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">System Alerts</div>
                    <div class="card-body">
                        <?php if (empty($alerts['inventory']) && empty($alerts['conditions'])): ?>
                            <p class="text-muted">No active alerts.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($alerts['inventory'] as $alert): ?>
                                    <li class="list-group-item">
                                        <strong>Inventory Alert:</strong> Batch <?= esc_html($alert['batch_number']) ?> (<?= esc_html($alert['meat_type']) ?>) expires on <?= date('M j, Y', strtotime($alert['expiry_date'])) ?>.
                                        <form method="POST" action="dashboard_data.php" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="alert_id" value="inv_<?= $alert['id'] ?>">
                                            <button type="submit" name="dismiss_alert" class="btn btn-sm btn-outline-secondary float-end">Dismiss</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                                <?php foreach ($alerts['conditions'] as $alert): ?>
                                    <li class="list-group-item">
                                        <strong>Condition Alert:</strong> Abnormal conditions at <?= esc_html($alert['location_name']) ?> (Temp: <?= $alert['temperature'] ?>°C, Humidity: <?= $alert['humidity'] ?>%).
                                        <form method="POST" action="dashboard_data.php" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="alert_id" value="cond_<?= $alert['id'] ?>">
                                            <button type="submit" name="dismiss_alert" class="btn btn-sm btn-outline-secondary float-end">Dismiss</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>