<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor']);

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 20;
$pagination = getPagination($pdo, 'condition_monitoring', $perPage, $page);
$offset = $pagination['offset'];
$totalPages = $pagination['totalPages'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: monitoring.php?page=' . $page);
        exit;
    }
    try {
        $pdo->beginTransaction();
        $temperature = filter_var($_POST['temperature'], FILTER_VALIDATE_FLOAT);
        $humidity = filter_var($_POST['humidity'], FILTER_VALIDATE_FLOAT);
        if (!$temperature || !$humidity || empty($_POST['storage_location_id'])) {
            throw new Exception('Invalid input data.');
        }
        $stmt = $pdo->prepare('INSERT INTO condition_monitoring (storage_location_id, temperature, humidity, remarks) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $_POST['storage_location_id'],
            $temperature,
            $humidity,
            $_POST['remarks'] ?: null
        ]);
        $pdo->commit();
        $_SESSION['success_message'] = 'Condition record added successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Monitoring error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    header('Location: monitoring.php?page=' . $page);
    exit;
}

$storageLocations = $pdo->query('SELECT * FROM storage_locations ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$conditions = $pdo->query("
    SELECT cm.*, sl.name as location_name
    FROM condition_monitoring cm
    JOIN storage_locations sl ON cm.storage_location_id = sl.id
    ORDER BY cm.recorded_at DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);

$tempData = $pdo->query("
    SELECT sl.name as location_name, cm.temperature, cm.recorded_at
    FROM condition_monitoring cm
    JOIN storage_locations sl ON cm.storage_location_id = sl.id
    ORDER BY cm.recorded_at DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Condition Monitoring</title>
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
                    <h1 class="h2">Condition Monitoring</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addConditionModal">
                        <i class="fas fa-plus me-1"></i>Add Record
                    </button>
                </div>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= esc_html($_SESSION['success_message']) ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= esc_html($_SESSION['error_message']) ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">Temperature Trends</div>
                    <div class="card-body">
                        <canvas id="tempChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>Condition Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Temperature (°C)</th>
                                        <th>Humidity (%)</th>
                                        <th>Remarks</th>
                                        <th>Recorded At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($conditions as $condition): ?>
                                        <tr>
                                            <td><?= esc_html($condition['location_name']) ?></td>
                                            <td><?= number_format($condition['temperature'], 2) ?></td>
                                            <td><?= number_format($condition['humidity'], 2) ?></td>
                                            <td><?= esc_html($condition['remarks'] ?: 'N/A') ?></td>
                                            <td><?= date('M j, Y H:i', strtotime($condition['recorded_at'])) ?></td>
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
                <div class="modal fade" id="addConditionModal" tabindex="-1" aria-labelledby="addConditionModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addConditionModalLabel">Add Condition Record</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="addConditionForm">
                                    <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                    <div class="mb-3">
                                        <label for="storage_location_id" class="form-label">Storage Location *</label>
                                        <select class="form-select" id="storage_location_id" name="storage_location_id" required>
                                            <option value="">Select location</option>
                                            <?php foreach ($storageLocations as $location): ?>
                                                <option value="<?= $location['id'] ?>"><?= esc_html($location['name']) ?> (<?= esc_html($location['temperature_range']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="temperature" class="form-label">Temperature (°C) *</label>
                                        <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="humidity" class="form-label">Humidity (%) *</label>
                                        <input type="number" step="0.1" class="form-control" id="humidity" name="humidity" required min="0" max="100">
                                    </div>
                                    <div class="mb-3">
                                        <label for="remarks" class="form-label">Remarks</label>
                                        <textarea class="form-control" id="remarks" name="remarks"></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Add Record</button>
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
        document.addEventListener('DOMContentLoaded', () => {
            const ctxTemp = document.getElementById('tempChart')?.getContext('2d');
            if (ctxTemp) {
                new Chart(ctxTemp, {
                    type: 'line',
                    data: {
                        labels: [<?php echo implode(',', array_map(fn($r) => "'".date('M j, H:i', strtotime($r['recorded_at']))."'", $tempData)); ?>],
                        datasets: [<?php
                            $locations = array_unique(array_column($tempData, 'location_name'));
                            $datasets = [];
                            foreach ($locations as $loc) {
                                $data = array_filter($tempData, fn($r) => $r['location_name'] === $loc);
                                $temps = array_map(fn($r) => $r['temperature'], $data);
                                $datasets[] = "{ label: '$loc', data: [".implode(',', $temps)."], borderColor: '#".substr(md5($loc), 0, 6)."', fill: false }";
                            }
                            echo implode(',', $datasets);
                        ?>]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: false, title: { display: true, text: 'Temperature (°C)' } },
                            x: { title: { display: true, text: 'Time' } }
                        }
                    }
                });
            }

            document.getElementById('addConditionForm')?.addEventListener('submit', e => {
                const temp = parseFloat(document.getElementById('temperature').value);
                const humidity = parseFloat(document.getElementById('humidity').value);
                if (isNaN(temp) || isNaN(humidity) || humidity < 0 || humidity > 100) {
                    alert('Please enter valid temperature and humidity (0-100%).');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>