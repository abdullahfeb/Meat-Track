<?php
require 'config.php';

// If user is authenticated, redirect to dashboard
if (isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeatTrack - Meat Processing & Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .nav-brand-logo {
            height: 40px;
            width: auto;
        }
        .cta-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-meat-packer me-2"></i>
                <strong>MeatTrack</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-3" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Complete Meat Processing & Inventory Management</h1>
                    <p class="lead mb-4">Streamline your meat processing operations with our comprehensive tracking system. Monitor inventory, ensure quality, and manage distribution efficiently.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="login.php" class="btn btn-light btn-lg px-4">Get Started</a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-warehouse display-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold">Powerful Features</h2>
                    <p class="lead text-muted">Everything you need to manage your meat processing business efficiently</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-boxes feature-icon"></i>
                            <h5 class="card-title">Inventory Management</h5>
                            <p class="card-text">Track meat inventory with batch numbers, expiry dates, and storage locations. Real-time monitoring of stock levels.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-thermometer-half feature-icon"></i>
                            <h5 class="card-title">Quality Monitoring</h5>
                            <p class="card-text">Monitor temperature and humidity conditions. Receive alerts when conditions go out of acceptable ranges.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-truck feature-icon"></i>
                            <h5 class="card-title">Distribution Tracking</h5>
                            <p class="card-text">Manage deliveries, track vehicles, and monitor distribution status from preparation to delivery.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-chart-line feature-icon"></i>
                            <h5 class="card-title">Analytics & Reports</h5>
                            <p class="card-text">Generate comprehensive reports on inventory, sales, spoilage, and operational efficiency.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-users feature-icon"></i>
                            <h5 class="card-title">User Management</h5>
                            <p class="card-text">Role-based access control with different permission levels for administrators, managers, and operators.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-bell feature-icon"></i>
                            <h5 class="card-title">Smart Alerts</h5>
                            <p class="card-text">Automated notifications for expiring products, temperature violations, and other critical events.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="cta-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About MeatTrack</h2>
                    <p class="lead mb-4">MeatTrack is a comprehensive meat processing and inventory management system designed to help businesses maintain quality, ensure food safety, and optimize operations.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Real-time inventory tracking</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Quality assurance monitoring</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Comprehensive reporting</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Multi-user role management</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Mobile-responsive design</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-industry display-1 text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>MeatTrack</h5>
                    <p class="text-muted">Professional meat processing management system</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted">&copy; 2025 MeatTrack. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
