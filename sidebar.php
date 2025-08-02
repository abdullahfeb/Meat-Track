<?php
require 'config.php';
requireAuth();
$user = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Navigation Toggle -->
<div class="mobile-nav d-lg-none">
    <div class="d-flex justify-content-between align-items-center">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <span class="text-white fw-bold">MeatTrack</span>
        <div></div>
    </div>
</div>

<nav class="col-lg-2 d-lg-block sidebar" id="sidebarMenu">
    <div class="position-sticky">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center justify-content-center">
                <i class="fas fa-meat-packer me-2 text-white" style="font-size: 1.5rem;"></i>
                <span class="brand-text">MeatTrack</span>
            </div>
            <small class="text-muted d-block mt-1 text-center">Meat Processing System</small>
        </div>
        
        <!-- User Info -->
        <div class="user-info px-3 py-2 mb-3" style="background-color: rgba(255,255,255,0.1); border-radius: 0.5rem; margin: 0 10px;">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2">
                    <i class="fas fa-user-circle text-white" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <small class="text-white fw-bold d-block"><?= esc_html($user['name'] ?? 'User') ?></small>
                    <small class="text-muted"><?= ucfirst(esc_html($user['role'] ?? 'user')) ?></small>
                </div>
            </div>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Core Operations -->
            <?php if (in_array($user['role'], ['admin', 'manager', 'supervisor', 'operator'])): ?>
            <li class="nav-item">
                <a class="nav-link <?= in_array($currentPage, ['inventory.php', 'inventory_management.php']) ? 'active' : '' ?>" href="inventory.php">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'monitoring.php' ? 'active' : '' ?>" href="monitoring.php">
                    <i class="fas fa-thermometer-half"></i>
                    <span>Monitoring</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($user['role'], ['admin', 'manager', 'supervisor'])): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'spoilage.php' ? 'active' : '' ?>" href="spoilage.php">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Spoilage</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'distribution.php' ? 'active' : '' ?>" href="distribution.php">
                    <i class="fas fa-truck"></i>
                    <span>Distribution</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Reports Section -->
            <li class="nav-item mt-3">
                <small class="text-muted ps-3 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Reports</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <?php if (in_array($user['role'], ['admin', 'manager', 'supervisor'])): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'loss-analysis.php' ? 'active' : '' ?>" href="loss-analysis.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Loss Analysis</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Administration Section -->
            <?php if (in_array($user['role'], ['admin', 'manager'])): ?>
            <li class="nav-item mt-3">
                <small class="text-muted ps-3 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Administration</small>
            </li>
            <?php if ($user['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>" href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Account Section -->
            <li class="nav-item mt-3">
                <small class="text-muted ps-3 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Account</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showProfileModal()">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        
        <!-- System Status -->
        <div class="system-status mt-4 px-3 mb-3">
            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">System Status</small>
            <div class="status-item d-flex justify-content-between align-items-center mt-2">
                <span class="text-muted" style="font-size: 0.8rem;">Database</span>
                <span class="badge bg-success">Online</span>
            </div>
            <div class="status-item d-flex justify-content-between align-items-center mt-1">
                <span class="text-muted" style="font-size: 0.8rem;">Last Backup</span>
                <span class="text-muted" style="font-size: 0.7rem;"><?= date('M j') ?></span>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMenu');
    sidebar.classList.toggle('show');
}

function showProfileModal() {
    // This could open a profile editing modal
    alert('Profile editing functionality would be implemented here');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebarMenu');
    const toggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth < 992 && 
        !sidebar.contains(event.target) && 
        !toggle.contains(event.target) && 
        sidebar.classList.contains('show')) {
        sidebar.classList.remove('show');
    }
});

// Close sidebar when window is resized to desktop
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebarMenu');
    if (window.innerWidth >= 992) {
        sidebar.classList.remove('show');
    }
});
</script>