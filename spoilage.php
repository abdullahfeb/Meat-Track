<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor']);

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 20;
$pagination = getPagination($pdo, 'spoilage', $perPage, $page);
$offset = $pagination['offset'];
$totalPages = $pagination['totalPages'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: spoilage.php?page=' . $page);
        exit;
    }
    try {
        $pdo->beginTransaction();
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
        $batchNumber = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['batch_number']);
        if (!$quantity || empty($batchNumber) || empty($_POST['meat_type_id']) || empty($_POST['processing_date']) || empty($_POST['storage_location_id']) || !in_array($_POST['reason'], ['temperature fluctuation', 'expired', 'contamination', 'improper handling', 'packaging failure', 'other']) || !in_array($_POST['disposal_method'], ['incineration', 'landfill', 'rendering', 'composting', 'other'])) {
            throw new Exception('Invalid input data.');
        }
        $inventoryStmt = $pdo->prepare('SELECT id FROM inventory WHERE batch_number = ?');
        $inventoryStmt->execute([$batchNumber]);
        $inventoryId = $inventoryStmt->fetchColumn() ?: null;

        $stmt = $pdo->prepare('INSERT INTO spoilage (inventory_id, batch_number, meat_type_id, quantity, processing_date, storage_location_id, reason, disposal_method, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $inventoryId,
            $batchNumber,
            $_POST['meat_type_id'],
            $quantity,
            $_POST['processing_date'],
            $_POST['storage_location_id'],
            $_POST['reason'],
            $_POST['disposal_method'],
            $_SESSION['user_id']
        ]);

        if ($inventoryId) {
            $updateStmt = $pdo->prepare('UPDATE inventory SET status = "spoiled", quantity = quantity - ? WHERE id = ?');
            $updateStmt->execute([$quantity, $inventoryId]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = 'Spoilage record added successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        ErrorHandler::handleDatabaseError($e, 'Error recording spoilage. Please try again.');
    }
    header('Location: spoilage.php?page=' . $page);
    exit;
}

// Use prepared statements for better security
$stmt = $pdo->prepare('SELECT * FROM meat_types ORDER BY name');
$stmt->execute();
$meatTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT * FROM storage_locations ORDER BY name');
$stmt->execute();
$storageLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT s.*, mt.name as meat_type, sl.name as storage_location, u.name as recorded_by_name
    FROM spoilage s
    JOIN meat_types mt ON s.meat_type_id = mt.id
    JOIN storage_locations sl ON s.storage_location_id = sl.id
    JOIN users u ON s.recorded_by = u.id
    ORDER BY s.recorded_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$spoilageRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Spoilage Management</title>
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
                    <h1 class="h2">Spoilage Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSpoilageModal">
                        <i class="fas fa-plus me-1"></i>Add Spoilage Record
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
                <div class="card">
                    <div class="card-header">
                        <h5>Spoilage Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Batch #</th>
                                        <th>Meat Type</th>
                                        <th>Quantity (kg)</th>
                                        <th>Reason</th>
                                        <th>Disposal Method</th>
                                        <th>Storage Location</th>
                                        <th>Recorded By</th>
                                        <th>Recorded At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($spoilageRecords as $record): ?>
                                        <tr>
                                            <td><?= esc_html($record['batch_number']) ?></td>
                                            <td><?= esc_html($record['meat_type']) ?></td>
                                            <td><?= number_format($record['quantity'], 2) ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $record['reason'])) ?></td>
                                            <td><?= ucfirst($record['disposal_method']) ?></td>
                                            <td><?= esc_html($record['storage_location']) ?></td>
                                            <td><?= esc_html($record['recorded_by_name']) ?></td>
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
                <div class="modal fade" id="addSpoilageModal" tabindex="-1" aria-labelledby="addSpoilageModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSpoilageModalLabel">Add Spoilage Record</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="addSpoilageForm">
                                    <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                    <div class="mb-3">
                                        <label for="batch_number" class="form-label">Batch Number *</label>
                                        <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meat_type_id" class="form-label">Meat Type *</label>
                                        <select class="form-select" id="meat_type_id" name="meat_type_id" required>
                                            <option value="">Select type</option>
                                            <?php foreach ($meatTypes as $type): ?>
                                                <option value="<?= $type['id'] ?>"><?= esc_html($type['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
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
                                        <label for="storage_location_id" class="form-label">Storage Location *</label>
                                        <select class="form-select" id="storage_location_id" name="storage_location_id" required>
                                            <option value="">Select location</option>
                                            <?php foreach ($storageLocations as $location): ?>
                                                <option value="<?= $location['id'] ?>"><?= esc_html($location['name']) ?> (<?= esc_html($location['temperature_range']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason *</label>
                                        <select class="form-select" id="reason" name="reason" required>
                                            <option value="">Select reason</option>
                                            <option value="temperature fluctuation">Temperature Fluctuation</option>
                                            <option value="expired">Expired</option>
                                            <option value="contamination">Contamination</option>
                                            <option value="improper handling">Improper Handling</option>
                                            <option value="packaging failure">Packaging Failure</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="disposal_method" class="form-label">Disposal Method *</label>
                                        <select class="form-select" id="disposal_method" name="disposal_method" required>
                                            <option value="">Select method</option>
                                            <option value="incineration">Incineration</option>
                                            <option value="landfill">Landfill</option>
                                            <option value="rendering">Rendering</option>
                                            <option value="composting">Composting</option>
                                            <option value="other">Other</option>
                                        </select>
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
        document.getElementById('addSpoilageForm')?.addEventListener('submit', e => {
            const quantity = parseFloat(document.getElementById('quantity').value);
            const processingDate = new Date(document.getElementById('processing_date').value);
            if (quantity <= 0) {
                alert('Quantity must be greater than 0.');
                e.preventDefault();
            }
            if (processingDate > new Date()) {
                alert('Processing date cannot be in the future.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>