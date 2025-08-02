<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor']);

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 20;
$pagination = getPagination($pdo, 'inventory', $perPage, $page);
$offset = $pagination['offset'];
$totalPages = $pagination['totalPages'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: inventory.php?page=' . $page);
        exit;
    }
    try {
        $pdo->beginTransaction();
        if (isset($_POST['add_inventory'])) {
            $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
            $batchNumber = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['batch_number']);
            if (!$quantity || empty($batchNumber) || empty($_POST['meat_type_id']) || empty($_POST['processing_date']) || empty($_POST['expiry_date']) || empty($_POST['storage_location_id'])) {
                throw new Exception('Invalid input data.');
            }
            $stmt = $pdo->prepare('INSERT INTO inventory (batch_number, meat_type_id, cut_type, quantity, processing_date, expiry_date, storage_location_id, quality_notes, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $batchNumber,
                $_POST['meat_type_id'],
                $_POST['cut_type'] ?: null,
                $quantity,
                $_POST['processing_date'],
                $_POST['expiry_date'],
                $_POST['storage_location_id'],
                $_POST['quality_notes'] ?: null,
                'good',
                $_SESSION['user_id']
            ]);
            $_SESSION['success_message'] = 'Inventory item added successfully.';
        } elseif (isset($_POST['export'])) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="inventory.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Batch #', 'Meat Type', 'Cut Type', 'Quantity (kg)', 'Processing Date', 'Expiry Date', 'Storage Location', 'Status', 'Quality Notes']);
            $records = $pdo->query('
                SELECT i.batch_number, mt.name as meat_type, i.cut_type, i.quantity, i.processing_date, i.expiry_date, sl.name as storage_location, i.status, i.quality_notes
                FROM inventory i
                JOIN meat_types mt ON i.meat_type_id = mt.id
                JOIN storage_locations sl ON i.storage_location_id = sl.id
                ORDER BY i.created_at DESC
            ')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($records as $record) {
                fputcsv($output, [
                    $record['batch_number'],
                    $record['meat_type'],
                    $record['cut_type'] ?: 'N/A',
                    number_format($record['quantity'], 2),
                    date('Y-m-d', strtotime($record['processing_date'])),
                    date('Y-m-d', strtotime($record['expiry_date'])),
                    $record['storage_location'],
                    ucfirst($record['status']),
                    $record['quality_notes'] ?: 'None'
                ]);
            }
            fclose($output);
            exit;
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Inventory error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    header('Location: inventory.php?page=' . $page);
    exit;
}

$meatTypes = $pdo->query('SELECT * FROM meat_types ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$storageLocations = $pdo->query('SELECT * FROM storage_locations ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$inventoryItems = $pdo->query("
    SELECT i.*, mt.name as meat_type, sl.name as storage_location
    FROM inventory i
    JOIN meat_types mt ON i.meat_type_id = mt.id
    JOIN storage_locations sl ON i.storage_location_id = sl.id
    ORDER BY i.created_at DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Inventory</title>
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
                    <h1 class="h2">Inventory Management</h1>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                            <i class="fas fa-plus me-1"></i>Add Inventory
                        </button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                            <button type="submit" name="export" class="btn btn-secondary">
                                <i class="fas fa-download me-1"></i>Export CSV
                            </button>
                        </form>
                    </div>
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
                        <h5>Inventory Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Batch #</th>
                                        <th>Meat Type</th>
                                        <th>Cut Type</th>
                                        <th>Quantity (kg)</th>
                                        <th>Processing Date</th>
                                        <th>Expiry Date</th>
                                        <th>Storage Location</th>
                                        <th>Status</th>
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
                                                <span class="badge bg-<?= $item['status'] === 'good' ? 'success' : ($item['status'] === 'near_expiry' ? 'warning' : ($item['status'] === 'spoiled' ? 'danger' : 'info')) ?>">
                                                    <?= ucfirst($item['status']) ?>
                                                </span>
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
                <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addInventoryModalLabel">Add Inventory Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="addInventoryForm">
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
                                            <?php foreach ($storageLocations as $location): ?>
                                                <option value="<?= $location['id'] ?>"><?= esc_html($location['name']) ?> (<?= esc_html($location['temperature_range']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quality_notes" class="form-label">Quality Notes</label>
                                        <textarea class="form-control" id="quality_notes" name="quality_notes"></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="add_inventory" class="btn btn-primary">Add Inventory</button>
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
        document.getElementById('addInventoryForm')?.addEventListener('submit', e => {
            const quantity = parseFloat(document.getElementById('quantity').value);
            const processingDate = new Date(document.getElementById('processing_date').value);
            const expiryDate = new Date(document.getElementById('expiry_date').value);
            if (quantity <= 0) {
                alert('Quantity must be greater than 0.');
                e.preventDefault();
            }
            if (expiryDate < processingDate) {
                alert('Expiry date cannot be before processing date.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>