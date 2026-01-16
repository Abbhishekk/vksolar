<?php
// admin/bankquotation/quotation_dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('quotation_management', 'view');

$title = 'bank_quotation_dashboard';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bank Quotation Dashboard</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Same UI language as document_dashboard ===== */

.doc-card {
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    border: none;
    background: #fff;
    transition: transform .2s ease, box-shadow .2s ease;
}

.doc-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.12);
}

.doc-card-header {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    padding: 18px 22px;
    border-radius: 16px 16px 0 0;
    text-align: center;
}

.doc-card-header h5 {
    margin: 0;
    font-weight: 600;
}

.doc-btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 12px;
}

.doc-status {
    font-size: 13px;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-block;
}

.status-available {
    background: #d4edda;
    color: #155724;
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

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="doc-card">
                <div class="doc-card-header">
                    <h5>Bank Quotation Dashboard</h5>
                    <small>Manage bank quotations</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotation Management Cards -->
    <div class="row">

        <!-- Create New Quotation -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">

                    <h6 class="mb-3">Create New Quotation</h6>

                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>

                    <a href="select_client.php"
                       class="btn btn-success doc-btn w-100">
                        Create New
                    </a>

                </div>
            </div>
        </div>
        
        <!-- Edit Previous Quotations -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">
        
                    <h6 class="mb-3">Edit Previous Quotations</h6>
        
                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>
        
                    <a href="quotation_list.php"
                       class="btn btn-success doc-btn w-100">
                        View & Edit
                    </a>
        
                </div>
            </div>
        </div>

        <!-- View All Quotations -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">

                    <h6 class="mb-3">View All Quotations</h6>

                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>

                    <a href="../view_quotations.php"
                       class="btn btn-success doc-btn w-100">
                        View All
                    </a>

                </div>
            </div>
        </div>

    </div>

</main>
</div>

</body>
</html>