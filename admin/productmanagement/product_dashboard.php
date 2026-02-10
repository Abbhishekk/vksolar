<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'product_dashboard';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Product Dashboard</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.dashboard-card {
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    border: none;
    background: #fff;
    transition: all 0.3s ease;
}
.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.12);
}
.dashboard-card-header {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    padding: 18px 22px;
    border-radius: 16px 16px 0 0;
}
.dashboard-icon {
    font-size: 48px;
    color: #30935C;
}
.dashboard-btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 12px;
}
</style>
</head>

<body>

<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">

<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/navbar.php';
chdir($cwd);
?>

<main class="container-fluid">

    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="mb-0">Product Management Dashboard</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">
                        Manage manufacturers, categories, and products from one place.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row">

        <!-- Manufacturer Company -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">🏭</div>
                    <h6 class="mb-3">Manufacturer Company</h6>
                    <a href="product_manufacturers.php" class="btn btn-success dashboard-btn w-100">
                        Manage Companies
                    </a>
                </div>
            </div>
        </div>

        <!-- Product Category -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">📦</div>
                    <h6 class="mb-3">Product Categories</h6>
                    <a href="product_categories" class="btn btn-success dashboard-btn w-100">
                        Manage Categories
                    </a>
                </div>
            </div>
        </div>

        <!--  Products -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">➕  ⧸  📋</div>
                    <h6 class="mb-3">Products</h6>
                    <a href="products/add.php" class="btn btn-success dashboard-btn w-100">
                        Manage Product
                    </a>
                </div>
            </div>
        </div>


    </div>

</main>
</div>

</body>
</html>
