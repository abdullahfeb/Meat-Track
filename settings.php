<?php
require 'config.php';
requireAuth();
restrictToRoles($pdo, ['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: settings.php');
        exit;
    }
    try {
        $pdo->beginTransaction();
        $expiryDaysBefore = filter_var($_POST['expiry_days_before'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $maxTemp = filter_var($_POST['max_temp'], FILTER_VALIDATE_FLOAT);
        $minTemp = filter_var($_POST['min_temp'], FILTER_VALIDATE_FLOAT);
        $maxHumidity = filter_var($_POST['max_humidity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $minHumidity = filter_var($_POST['min_humidity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $spoilageThreshold = filter_var($_POST['spoilage_alert_threshold'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $inventoryThreshold = filter_var($_POST['inventory_low_threshold'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$expiryDaysBefore || !$maxTemp || !$minTemp || !$maxHumidity || !$minHumidity || !$spoilageThreshold || !$inventoryThreshold) {
            throw new Exception('Invalid input data.');
        }
        $stmt = $pdo->prepare('
            UPDATE system_settings SET 
                expiry_alerts = ?, 
                expiry_days_before = ?, 
                max_temp = ?, 
                min_temp = ?, 
                max_humidity = ?, 
                min_humidity = ?, 
                monitoring_alerts = ?, 
                email_alerts = ?, 
                email_recipients = ?, 
                sms_alerts = ?, 
                sms_recipients = ?, 
                spoilage_alert_threshold = ?, 
                inventory_low_threshold = ?
            WHERE id = 1
        ');
        $stmt->execute([
            isset($_POST['expiry_alerts']) ? 1 : 0,
            $expiryDaysBefore,
            $maxTemp,
            $minTemp,
            $maxHumidity,
            $minHumidity,
            isset($_POST['monitoring_alerts']) ? 1 : 0,
            isset($_POST['email_alerts']) ? 1 : 0,
            $_POST['email_recipients'] ?: null,
            isset($_POST['sms_alerts']) ? 1 : 0,
            $_POST['sms_recipients'] ?: null,
            $spoilageThreshold,
            $inventoryThreshold
        ]);
        $pdo->commit();
        $_SESSION['success_message'] = 'Settings updated successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Settings error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    header('Location: settings.php');
    exit;
}

$settings = $pdo->query('SELECT * FROM system_settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Settings</title>
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
                    <h1 class="h2">System Settings</h1>
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
                        <h5>Configure System Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= esc_html($_SESSION['csrf_token']) ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Expiry Alerts</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="expiry_alerts" name="expiry_alerts" <?= $settings['expiry_alerts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="expiry_alerts">Enable Expiry Alerts</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="expiry_days_before" class="form-label">Days Before Expiry</label>
                                        <input type="number" class="form-control" id="expiry_days_before" name="expiry_days_before" value="<?= esc_html($settings['expiry_days_before']) ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Monitoring Alerts</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="monitoring_alerts" name="monitoring_alerts" <?= $settings['monitoring_alerts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="monitoring_alerts">Enable Monitoring Alerts</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_temp" class="form-label">Max Temperature (°C)</label>
                                        <input type="number" step="0.1" class="form-control" id="max_temp" name="max_temp" value="<?= esc_html($settings['max_temp']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="min_temp" class="form-label">Min Temperature (°C)</label>
                                        <input type="number" step="0.1" class="form-control" id="min_temp" name="min_temp" value="<?= esc_html($settings['min_temp']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_humidity" class="form-label">Max Humidity (%)</label>
                                        <input type="number" class="form-control" id="max_humidity" name="max_humidity" value="<?= esc_html($settings['max_humidity']) ?>" min="0" max="100">
                                    </div>
                                    <div class="mb-3">
                                        <label for="min_humidity" class="form-label">Min Humidity (%)</label>
                                        <input type="number" class="form-control" id="min_humidity" name="min_humidity" value="<?= esc_html($settings['min_humidity']) ?>" min="0" max="100">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Email Alerts</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_alerts" name="email_alerts" <?= $settings['email_alerts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email_alerts">Enable Email Alerts</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_recipients" class="form-label">Email Recipients (comma-separated)</label>
                                        <input type="text" class="form-control" id="email_recipients" name="email_recipients" value="<?= esc_html($settings['email_recipients']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>SMS Alerts</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sms_alerts" name="sms_alerts" <?= $settings['sms_alerts'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sms_alerts">Enable SMS Alerts</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sms_recipients" class="form-label">SMS Recipients (comma-separated)</label>
                                        <input type="text" class="form-control" id="sms_recipients" name="sms_recipients" value="<?= esc_html($settings['sms_recipients']) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="spoilage_alert_threshold" class="form-label">Spoilage Alert Threshold (kg)</label>
                                        <input type="number" class="form-control" id="spoilage_alert_threshold" name="spoilage_alert_threshold" value="<?= esc_html($settings['spoilage_alert_threshold']) ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="inventory_low_threshold" class="form-label">Inventory Low Threshold (kg)</label>
                                        <input type="number" class="form-control" id="inventory_low_threshold" name="inventory_low_threshold" value="<?= esc_html($settings['inventory_low_threshold']) ?>" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('form')?.addEventListener('submit', e => {
            const maxTemp = parseFloat(document.getElementById('max_temp').value);
            const minTemp = parseFloat(document.getElementById('min_temp').value);
            const maxHumidity = parseInt(document.getElementById('max_humidity').value);
            const minHumidity = parseInt(document.getElementById('min_humidity').value);
            const expiryDays = parseInt(document.getElementById('expiry_days_before').value);
            const spoilageThreshold = parseInt(document.getElementById('spoilage_alert_threshold').value);
            const inventoryThreshold = parseInt(document.getElementById('inventory_low_threshold').value);

            if (maxTemp <= minTemp) {
                alert('Max temperature must be greater than min temperature.');
                e.preventDefault();
            }
            if (maxHumidity <= minHumidity) {
                alert('Max humidity must be greater than min humidity.');
                e.preventDefault();
            }
            if (expiryDays < 1 || spoilageThreshold < 1 || inventoryThreshold < 1) {
                alert('Thresholds must be positive numbers.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>