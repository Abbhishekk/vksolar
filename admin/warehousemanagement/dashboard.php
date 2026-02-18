<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'warehouse_dashboard';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Warehouse Dashboard</title>

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
include __DIR__ . '/../include/sidebar.php';
?>

<div id="main-content">

<?php
include __DIR__ . '/../include/navbar.php';
?>

<main class="container-fluid">

    <!-- Title Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="mb-0">Warehouse Management Dashboard</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">
                        Manage warehouses, stock movements and inventory reports.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row">

        <!-- Manage Warehouses -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">🏬</div>
                    <h6 class="mb-3">Warehouses</h6>
                    <a href="warehouses.php"
                       class="btn btn-success dashboard-btn w-100">
                        Manage Warehouses
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock In -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">📥</div>
                    <h6 class="mb-3">Stock In</h6>
                    <span class="text-danger" >Unavailable</span>
                    <a href="#"
                       class="btn btn-success dashboard-btn w-100" style="pointer-events: none; opacity: 0.6;">
                        Add Stock
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Out -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">📤</div>
                    <h6 class="mb-3">Stock Out</h6>
                    <span class="text-danger" >Unavailable</span>
                    <a href="#"  style="pointer-events: none; opacity: 0.6;"
                       class="btn btn-success dashboard-btn w-100">
                        Issue Stock
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Report -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card h-100 text-center">
                <div class="card-body">
                    <div class="dashboard-icon mb-3">📊</div>
                    <h6 class="mb-3">Stock Report</h6>
                    <a href="../stock_management/stock_report.php"
                       class="btn btn-success dashboard-btn w-100">
                        View Report
                    </a>
                </div>
            </div>
        </div>

    </div>

</main>
</div>

</body>
</html>
