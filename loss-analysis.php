<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor']);

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 20;
$pagination = getPagination($pdo, 'loss_records', $perPage, $page);
$offset = $pagination['offset'];
$totalPages = $pagination['totalPages'];

try {
    $lossRecords = $pdo->query("
        SELECT lr.*, mt.name as meat_type, i.batch_number, u.name as created_by_name
        FROM loss_records lr
        JOIN meat_types mt ON lr.meat_type_id = mt.id
        LEFT JOIN inventory i ON lr.inventory_id = i.id
        JOIN users u ON lr.created_by = u.id
        ORDER BY lr.recorded_at DESC
        LIMIT $perPage OFFSET $offset
    ")->fetchAll(PDO::FETCH_ASSOC);

    $lossByStage = $pdo->query("
        SELECT stage, SUM(quantity) as total_quantity
        FROM loss_records
        GROUP BY stage
    ")->fetchAll(PDO::FETCH_ASSOC);

    $lossByMeatType = $pdo->query("
        SELECT mt.name as meat_type, SUM(lr.quantity) as total_quantity
        FROM loss_records lr
        JOIN meat_types mt ON lr.meat_type_id = mt.id
        GROUP BY mt.id
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Loss analysis error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error loading loss records.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Loss Analysis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Loss Analysis</h1>
                    <div>
                        <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
                    </div>
                </div>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= esc_html($_SESSION['error_message']) ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Loss by Stage</div>
                            <div class="card-body">
                                <canvas id="lossByStageChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Loss by Meat Type</div>
                            <div class="card-body">
                                <canvas id="lossByMeatTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>Loss Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Batch #</th>
                                        <th>Meat Type</th>
                                        <th>Stage</th>
                                        <th>Quantity (kg)</th>
                                        <th>Reason</th>
                                        <th>Action Taken</th>
                                        <th>Created By</th>
                                        <th>Recorded At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lossRecords as $record): ?>
                                        <tr>
                                            <td><?= esc_html($record['batch_number'] ?: 'N/A') ?></td>
                                            <td><?= esc_html($record['meat_type']) ?></td>
                                            <td><?= ucfirst($record['stage']) ?></td>
                                            <td><?= number_format($record['quantity'], 2) ?></td>
                                            <td><?= esc_html($record['reason']) ?></td>
                                            <td><?= esc_html($record['action_taken'] ?: 'None') ?></td>
                                            <td><?= esc_html($record['created_by_name']) ?></td>
                                            <td><?= date('M j, Y H:i', strtotime($record['recorded_at'])) ?></td>
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
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctxStage = document.getElementById('lossByStageChart')?.getContext('2d');
            const ctxMeatType = document.getElementById('lossByMeatTypeChart')?.getContext('2d');

            if (ctxStage) {
                new Chart(ctxStage, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo implode(',', array_map(fn($r) => "'".ucfirst($r['stage'])."'", $lossByStage)); ?>],
                        datasets: [{
                            label: 'Loss (kg)',
                            data: [<?php echo implode(',', array_map(fn($r) => $r['total_quantity'], $lossByStage)); ?>],
                            backgroundColor: '#e74a3b'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            if (ctxMeatType) {
                new Chart(ctxMeatType, {
                    type: 'pie',
                    data: {
                        labels: [<?php echo implode(',', array_map(fn($r) => "'".esc_html($r['meat_type'])."'", $lossByMeatType)); ?>],
                        datasets: [{
                            data: [<?php echo implode(',', array_map(fn($r) => $r['total_quantity'], $lossByMeatType)); ?>],
                            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
    </script>
</body>
</html>