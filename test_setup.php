<?php
/**
 * MeatTrack Setup Test Script
 * This script tests if the application is properly configured for XAMPP
 */

// Start output buffering to capture any errors
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Setup Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f8f9fc; }
        .test-card { margin-bottom: 1rem; }
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>MeatTrack Setup Test</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">This script tests if MeatTrack is properly configured for XAMPP.</p>
                        
                        <?php
                        $tests = [];
                        $overall_status = true;
                        
                        // Test 1: PHP Version
                        $php_version = phpversion();
                        $php_ok = version_compare($php_version, '8.0', '>=');
                        $tests[] = [
                            'name' => 'PHP Version',
                            'status' => $php_ok,
                            'message' => "PHP $php_version " . ($php_ok ? '✓' : '✗ (Requires PHP 8.0+)'),
                            'details' => $php_ok ? 'PHP version is compatible' : 'Please upgrade to PHP 8.0 or newer'
                        ];
                        if (!$php_ok) $overall_status = false;
                        
                        // Test 2: Required PHP Extensions
                        $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'session'];
                        $missing_extensions = [];
                        foreach ($required_extensions as $ext) {
                            if (!extension_loaded($ext)) {
                                $missing_extensions[] = $ext;
                            }
                        }
                        $extensions_ok = empty($missing_extensions);
                        $tests[] = [
                            'name' => 'PHP Extensions',
                            'status' => $extensions_ok,
                            'message' => $extensions_ok ? 'All required extensions loaded ✓' : '✗ Missing: ' . implode(', ', $missing_extensions),
                            'details' => $extensions_ok ? 'PDO, PDO MySQL, MBString, and Session extensions are available' : 'Please install missing PHP extensions'
                        ];
                        if (!$extensions_ok) $overall_status = false;
                        
                        // Test 3: File Permissions
                        $writable_dirs = ['.', 'images'];
                        $permission_issues = [];
                        foreach ($writable_dirs as $dir) {
                            if (is_dir($dir) && !is_writable($dir)) {
                                $permission_issues[] = $dir;
                            }
                        }
                        $permissions_ok = empty($permission_issues);
                        $tests[] = [
                            'name' => 'File Permissions',
                            'status' => $permissions_ok,
                            'message' => $permissions_ok ? 'Directory permissions are correct ✓' : '✗ Not writable: ' . implode(', ', $permission_issues),
                            'details' => $permissions_ok ? 'All directories have proper write permissions' : 'Please check directory permissions'
                        ];
                        
                        // Test 4: Configuration File
                        $config_exists = file_exists('config.php');
                        $tests[] = [
                            'name' => 'Configuration File',
                            'status' => $config_exists,
                            'message' => $config_exists ? 'config.php exists ✓' : '✗ config.php not found',
                            'details' => $config_exists ? 'Configuration file is present' : 'Configuration file is missing'
                        ];
                        if (!$config_exists) $overall_status = false;
                        
                        // Test 5: Database Connection
                        $db_connected = false;
                        $db_message = '';
                        $db_details = '';
                        
                        if ($config_exists) {
                            try {
                                require_once 'config.php';
                                
                                // Test database connection
                                $test_pdo = new PDO(
                                    "mysql:host=" . DB_HOST . ";charset=utf8mb4",
                                    DB_USER,
                                    DB_PASS,
                                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                                );
                                
                                // Check if database exists
                                $db_exists = $test_pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'")->fetch();
                                
                                if ($db_exists) {
                                    // Try to connect to the specific database
                                    $app_pdo = new PDO(
                                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                                        DB_USER,
                                        DB_PASS,
                                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                                    );
                                    
                                    // Check if tables exist
                                    $tables = $app_pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                                    $required_tables = ['users', 'inventory', 'meat_types', 'storage_locations'];
                                    $missing_tables = array_diff($required_tables, $tables);
                                    
                                    if (empty($missing_tables)) {
                                        $db_connected = true;
                                        $db_message = 'Database connection successful ✓';
                                        $db_details = 'Connected to ' . DB_NAME . ' with all required tables';
                                    } else {
                                        $db_message = '✗ Missing tables: ' . implode(', ', $missing_tables);
                                        $db_details = 'Database exists but some tables are missing. Please import the SQL file.';
                                    }
                                } else {
                                    $db_message = '✗ Database "' . DB_NAME . '" does not exist';
                                    $db_details = 'Please create the database and import the SQL file.';
                                }
                                
                            } catch (PDOException $e) {
                                $db_message = '✗ Database connection failed';
                                $db_details = 'Error: ' . $e->getMessage() . '. Please check XAMPP MySQL service and database credentials.';
                            }
                        } else {
                            $db_message = '✗ Cannot test without config.php';
                            $db_details = 'Configuration file is required for database testing';
                        }
                        
                        $tests[] = [
                            'name' => 'Database Connection',
                            'status' => $db_connected,
                            'message' => $db_message,
                            'details' => $db_details
                        ];
                        if (!$db_connected) $overall_status = false;
                        
                        // Test 6: XAMPP Services
                        $xampp_apache = false;
                        $xampp_mysql = false;
                        
                        // Check if we're running on XAMPP (simple heuristics)
                        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
                        $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
                        
                        if (strpos($document_root, 'xampp') !== false || strpos($document_root, 'htdocs') !== false) {
                            $xampp_apache = true;
                        }
                        
                        // Test MySQL availability (already tested above, but let's be explicit)
                        if ($db_connected) {
                            $xampp_mysql = true;
                        }
                        
                        $xampp_ok = $xampp_apache && $xampp_mysql;
                        $xampp_msg = '';
                        if ($xampp_apache && $xampp_mysql) {
                            $xampp_msg = 'XAMPP services are running ✓';
                        } elseif ($xampp_apache) {
                            $xampp_msg = '✗ Apache running, but MySQL connection failed';
                        } else {
                            $xampp_msg = 'ℹ️ Not running on XAMPP or services not detected';
                        }
                        
                        $tests[] = [
                            'name' => 'XAMPP Services',
                            'status' => $xampp_ok ? true : 'warning',
                            'message' => $xampp_msg,
                            'details' => 'Apache and MySQL services should be running in XAMPP Control Panel'
                        ];
                        
                        // Display test results
                        foreach ($tests as $test) {
                            $icon_class = 'test-fail';
                            $icon = 'fas fa-times-circle';
                            
                            if ($test['status'] === true) {
                                $icon_class = 'test-pass';
                                $icon = 'fas fa-check-circle';
                            } elseif ($test['status'] === 'warning') {
                                $icon_class = 'test-warning';
                                $icon = 'fas fa-exclamation-triangle';
                            }
                            
                            echo "<div class='card test-card'>";
                            echo "<div class='card-body'>";
                            echo "<div class='d-flex justify-content-between align-items-start'>";
                            echo "<div>";
                            echo "<h6 class='mb-1'><i class='$icon $icon_class me-2'></i>" . $test['name'] . "</h6>";
                            echo "<p class='mb-1'>" . $test['message'] . "</p>";
                            echo "<small class='text-muted'>" . $test['details'] . "</small>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                        
                        <!-- Overall Status -->
                        <div class="card mt-4">
                            <div class="card-body text-center">
                                <?php if ($overall_status): ?>
                                    <div class="test-pass">
                                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                                        <h4>Setup Complete!</h4>
                                        <p>MeatTrack is properly configured and ready to use.</p>
                                        <a href="index.php" class="btn btn-success btn-lg">
                                            <i class="fas fa-arrow-right me-2"></i>Go to Application
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="test-fail">
                                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                        <h4>Setup Issues Detected</h4>
                                        <p>Please resolve the issues above before using MeatTrack.</p>
                                        <button onclick="location.reload()" class="btn btn-warning btn-lg">
                                            <i class="fas fa-redo me-2"></i>Retest
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Setup Guide -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Quick Setup Guide</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li><strong>Start XAMPP:</strong> Open XAMPP Control Panel and start Apache and MySQL services</li>
                                    <li><strong>Create Database:</strong> Go to <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a> and create database "meettrack"</li>
                                    <li><strong>Import Data:</strong> Import the <code>meettrack.sql</code> file into the database</li>
                                    <li><strong>Check Permissions:</strong> Ensure the web directory has proper write permissions</li>
                                    <li><strong>Access Application:</strong> Navigate to <a href="index.php">http://localhost/meattrack</a></li>
                                </ol>
                                
                                <div class="mt-3">
                                    <strong>Default Login Credentials:</strong><br>
                                    Admin: admin@meattrack.com / admin123<br>
                                    Manager: manager@meattrack.com / manager123
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>PHP Version:</strong> <?= phpversion() ?><br>
                                        <strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?><br>
                                        <strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?><br>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Memory Limit:</strong> <?= ini_get('memory_limit') ?><br>
                                        <strong>Upload Max Size:</strong> <?= ini_get('upload_max_filesize') ?><br>
                                        <strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>