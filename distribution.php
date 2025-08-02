<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin', 'manager', 'supervisor']);

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 20;
$pagination = getPagination($pdo, 'distribution', $perPage, $page);
$offset = $pagination['offset'];
$totalPages = $pagination['totalPages'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: distribution.php?page=' . $page);
        exit;
    }
    try {
        $pdo->beginTransaction();
        if (isset($_POST['add_distribution'])) {
            $deliveryId = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['delivery_id']);
            $items = $_POST['items'] ?? [];
            if (empty($deliveryId) || empty($_POST['destination']) || empty($_POST['scheduled_datetime']) || empty($items)) {
                throw new Exception('Invalid input data.');
            }
            $stmt = $pdo->prepare('INSERT INTO distribution (delivery_id, destination, scheduled_datetime, status, vehicle, driver, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $deliveryId,
                $_POST['destination'],
                $_POST['scheduled_datetime'],
                'pending',
                $_POST['vehicle'] ?: null,
                $_POST['driver'] ?: null,
                $_SESSION['user_id']
            ]);
            $distributionId = $pdo->lastInsertId();
            $itemStmt = $pdo->prepare('INSERT INTO distribution_items (distribution_id, inventory_id, quantity) VALUES (?, ?, ?)');
            foreach ($items as $item) {
                $quantity = filter_var($item['quantity'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
                if (!$quantity || empty($item['inventory_id'])) {
                    throw new Exception('Invalid item data.');
                }
                $itemStmt->execute([$distributionId, $item['inventory_id'], $quantity]);
                $updateStmt = $pdo->prepare('UPDATE inventory SET status = ? WHERE id = ?');
                $updateStmt->execute(['distributed', $item['inventory_id']]);
            }
            $_SESSION['success_message'] = 'Distribution added successfully.';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $status = in_array($_POST['status'], ['pending', 'in_transit', 'delivered', 'cancelled']) ? $_POST['status'] : null;
            if (!$status || empty($_POST['id'])) {
                throw new Exception('Invalid status or distribution ID.');
            }
            $stmt = $pdo->prepare('UPDATE distribution SET status = ?, completed_at = ? WHERE id = ?');
            $completedAt = $_POST['status'] === 'delivered' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$_POST['status'], $completedAt, $_POST['id']]);
            $_SESSION['success_message'] = 'Status updated successfully.';
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Distribution error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    header('Location: distribution.php?page=' . $page);
    exit;
}

$inventoryItems = $pdo->query('SELECT i.id, i.batch_number, mt.name as meat_type FROM inventory i JOIN meat_types mt ON i.meat_type_id = mt.id WHERE i.status IN ("good", "near_expiry") ORDER BY i.batch_number')->fetchAll(PDO::FETCH_ASSOC);
$distributions = $pdo->query("
    SELECT d.*, SUM(di.quantity) as total_quantity, u.name as created_by_name
    FROM distribution d
    JOIN distribution_items di ON d.id = di.distribution_id
    JOIN users u ON d.created_by = u.id
    GROUP BY d.id
    ORDER BY d.created_at DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Distribution</title>
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
                    <h1 class="h2">Distribution Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDistributionModal">
                        <i class="fas fa-plus me-1"></i>Add Distribution
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
                        <h5>Distribution Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Delivery ID</th>
                                        <th>Destination</th>
                                        <th>Scheduled</th>
                                        <th>Status</th>
                                        <th>Total Qty (kg)</th>
                                        <th>Created By</th>
                                        <th>Update Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($distributions as $dist): ?>
                                        <tr>
                                            <td><?= esc_html($dist['delivery_id']) ?></td>
                                            <td><?= esc_html($dist['destination']) ?></td>
                                            <td><?= date('M j, Y H:i', strtotime($dist['scheduled_datetime'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $dist['status'] === 'delivered' ? 'success' : ($dist['status'] === 'in_transit' ? 'info' : ($dist['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                                    <?= ucfirst($dist['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($dist['total_quantity'], 2) ?></td>
                                            <td><?= esc_html($dist['created_by_name']) ?></td>
                                            <td>
                                                <form method="POST" action="distribution.php?action=update_status" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="id" value="<?= $dist['id'] ?>">
                                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                        <option value="pending" <?= $dist['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="in_transit" <?= $dist['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                                                        <option value="delivered" <?= $dist['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="cancelled" <?= $dist['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
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
                <div class="modal fade" id="addDistributionModal" tabindex="-1" aria-labelledby="addDistributionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addDistributionModalLabel">Add Distribution</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="addDistributionForm">
                                    <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="delivery_id" class="form-label">Delivery ID *</label>
                                            <input type="text" class="form-control" id="delivery_id" name="delivery_id" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="destination" class="form-label">Destination *</label>
                                            <input type="text" class="form-control" id="destination" name="destination" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="scheduled_datetime" class="form-label">Scheduled Date & Time *</label>
                                            <input type="datetime-local" class="form-control" id="scheduled_datetime" name="scheduled_datetime" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="vehicle" class="form-label">Vehicle</label>
                                            <input type="text" class="form-control" id="vehicle" name="vehicle">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="driver" class="form-label">Driver</label>
                                        <input type="text" class="form-control" id="driver" name="driver">
                                    </div>
                                    <div class="mb-3">
                                        <h6>Inventory Items</h6>
                                        <div id="itemsContainer">
                                            <div class="row mb-2 item-row">
                                                <div class="col-md-8">
                                                    <select class="form-select" name="items[0][inventory_id]" required>
                                                        <option value="">Select inventory item</option>
                                                        <?php foreach ($inventoryItems as $item): ?>
                                                            <option value="<?= $item['id'] ?>"><?= esc_html($item['batch_number']) ?> (<?= esc_html($item['meat_type']) ?>)</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" step="0.01" class="form-control" name="items[0][quantity]" placeholder="Qty (kg)" required min="0.01">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-sm remove-item" disabled><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">Add Another Item</button>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="add_distribution" class="btn btn-primary">Add Distribution</button>
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
        let itemIndex = 1;
        document.getElementById('addItemBtn')?.addEventListener('click', () => {
            const container = document.getElementById('itemsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 item-row';
            newRow.innerHTML = `
                <div class="col-md-8">
                    <select class="form-select" name="items[${itemIndex}][inventory_id]" required>
                        <option value="">Select inventory item</option>
                        <?php foreach ($inventoryItems as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= esc_html($item['batch_number']) ?> (<?= esc_html($item['meat_type']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][quantity]" placeholder="Qty (kg)" required min="0.01">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                </div>
            `;
            container.appendChild(newRow);
            itemIndex++;
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-item');
            removeButtons.forEach((btn, idx) => {
                btn.disabled = removeButtons.length === 1;
                btn.addEventListener('click', () => {
                    btn.closest('.item-row').remove();
                    updateRemoveButtons();
                });
            });
        }

        document.getElementById('addDistributionForm')?.addEventListener('submit', e => {
            const deliveryId = new Date(document.getElementById('scheduled_datetime').value);
            if (deliveryId < new Date()) {
                alert('Scheduled date cannot be in the past.');
                e.preventDefault();
            }
        });

        updateRemoveButtons();
    </script>