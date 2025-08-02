<?php
require 'config.php';
requireAuth();

$reportType = $_GET['type'] ?? 'inventory';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    $data = [];
    if ($reportType === 'inventory') {
        $data = $pdo->query("
            SELECT i.batch_number, mt.name as meat_type, i.quantity, i.status, i.expiry_date, sl.name as storage_location
            FROM inventory i
            JOIN meat_types mt ON i.meat_type_id = mt.id
            JOIN storage_locations sl ON i.storage_location_id = sl.id
            WHERE i.created_at BETWEEN ? AND ?
            ORDER BY i.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC, [$startDate, $endDate]);
    } elseif ($reportType === 'spoilage') {
        $data = $pdo->query("
            SELECT s.batch_number, mt.name as meat_type, s.quantity, s.reason, s.disposal_method, s.recorded_at
            FROM spoilage s
            JOIN meat_types mt ON s.meat_type_id = mt.id
            WHERE s.recorded_at BETWEEN ? AND ?
            ORDER BY s.recorded_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC, [$startDate, $endDate]);
    } elseif ($reportType === 'distribution') {
        $data = $pdo->query("
            SELECT d.delivery_id, d.destination, d.scheduled_datetime, d.status, SUM(di.quantity) as total_quantity
            FROM distribution d
            JOIN distribution_items di ON d.id = di.distribution_id
            WHERE d.created_at BETWEEN ? AND ?
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC, [$startDate, $endDate]);
    }
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error generating report.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Reports</title>
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
                    <h1 class="h2">Reports</h1>
                    <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
                </div>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= esc_html($_SESSION['error_message']) ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Filter Report</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="type" class="form-label">Report Type</label>
                                <select class="form-select" id="type" name="type" onchange="this.form.submit()">
                                    <option value="inventory" <?= $reportType === 'inventory' ? 'selected' : '' ?>>Inventory</option>
                                    <option value="spoilage" <?= $reportType === 'spoilage' ? 'selected' : '' ?>>Spoilage</option>
                                    <option value="distribution" <?= $reportType === 'distribution' ? 'selected' : '' ?>>Distribution</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= esc_html($startDate) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= esc_html($endDate) ?>">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5><?= ucfirst($reportType) ?> Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <?php if ($reportType === 'inventory'): ?>
                                        <tr>
                                            <th>Batch #</th>
                                            <th>Meat Type</th>
                                            <th>Quantity (kg)</th>
                                            <th>Status</th>
                                            <th>Expiry Date</th>
                                            <th>Storage Location</th>
                                        </tr>
                                    <?php elseif ($reportType === 'spoilage'): ?>
                                        <tr>
                                            <th>Batch #</th>
                                            <th>Meat Type</th>
                                            <th>Quantity (kg)</th>
                                            <th>Reason</th>
                                            <th>Disposal Method</th>
                                            <th>Recorded At</th>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <th>Delivery ID</th>
                                            <th>Destination</th>
                                            <th>Scheduled</th>
                                            <th>Status</th>
                                            <th>Total Quantity (kg)</th>
                                        </tr>
                                    <?php endif; ?>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <?php if ($reportType === 'inventory'): ?>
                                                <td><?= esc_html($row['batch_number']) ?></td>
                                                <td><?= esc_html($row['meat_type']) ?></td>
                                                <td><?= number_format($row['quantity'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $row['status'] === 'good' ? 'success' : ($row['status'] === 'near_expiry' ? 'warning' : ($row['status'] === 'spoiled' ? 'danger' : 'info')) ?>">
                                                        <?= ucfirst($row['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($row['expiry_date'])) ?></td>
                                                <td><?= esc_html($row['storage_location']) ?></td>
                                            <?php elseif ($reportType === 'spoilage'): ?>
                                                <td><?= esc_html($row['batch_number']) ?></td>
                                                <td><?= esc_html($row['meat_type']) ?></td>
                                                <td><?= number_format($row['quantity'], 2) ?></td>
                                                <td><?= esc_html($row['reason']) ?></td>
                                                <td><?= ucfirst($row['disposal_method']) ?></td>
                                                <td><?= date('M j, Y H:i', strtotime($row['recorded_at'])) ?></td>
                                            <?php else: ?>
                                                <td><?= esc_html($row['delivery_id']) ?></td>
                                                <td><?= esc_html($row['destination']) ?></td>
                                                <td><?= date('M j, Y H:i', strtotime($row['scheduled_datetime'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $row['status'] === 'delivered' ? 'success' : ($row['status'] === 'in_transit' ? 'info' : ($row['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                                        <?= ucfirst($row['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= number_format($row['total_quantity'], 2) ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>