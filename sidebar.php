<?php
require 'config.php';
requireAuth();
$user = getCurrentUser($pdo);
?>
<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <img src="images/logo.jpeg" alt="MeatTrack Logo" class="img-fluid" style="max-width: 100px;">
            <h4 class="text-white mt-2">MeatTrack</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <?php if (in_array($user['role'], ['admin', 'manager', 'supervisor'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'inventory.php' ? 'active' : '' ?>" href="inventory.php">
                        <i class="fas fa-boxes me-2"></i>Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'distribution.php' ? 'active' : '' ?>" href="distribution.php">
                        <i class="fas fa-truck me-2"></i>Distribution
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'spoilage.php' ? 'active' : '' ?>" href="spoilage.php">
                        <i class="fas fa-exclamation-triangle me-2"></i>Spoilage
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'loss-analysis.php' ? 'active' : '' ?>" href="loss-analysis.php">
                        <i class="fas fa-chart-pie me-2"></i>Loss Analysis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'monitoring.php' ? 'active' : '' ?>" href="monitoring.php">
                        <i class="fas fa-thermometer-half me-2"></i>Monitoring
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="fas fa-file-alt me-2"></i>Reports
                </a>
            </li>
            <?php if ($user['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>" href="users.php">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>