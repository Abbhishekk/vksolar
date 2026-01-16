<?php
// admin/documentmanagement/document_dashboard.php
require_once __DIR__ . '/../connect/auth_middleware.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$title = 'document_dashboard';
$auth->requirePermission('reports', 'create');

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Document Dashboard</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Same UI language as client_dashboard ===== */

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
                    <h5>Document Dashboard</h5>
                    <small>Manage all document types</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Cards -->
    <div class="row">

        <!-- Declaration -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">

                    <h6 class="mb-3">Declaration</h6>

                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>

                    <a href="declaration/declaration_list.php"
                       class="btn btn-success doc-btn w-100">
                        Open
                    </a>

                </div>
            </div>
        </div>
        
        <!-- Commissioning -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">
        
                    <h6 class="mb-3">Commissioning</h6>
        
                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>
        
                    <a href="commissioning/commissioning_list.php"
                       class="btn btn-success doc-btn w-100">
                        Open
                    </a>
        
                </div>
            </div>
        </div>

<!-- Work Completion Report -->
<div class="col-lg-4 col-md-6 mb-4">
    <div class="doc-card h-100">
        <div class="card-body text-center">

            <h6 class="mb-3">Work Completion Report</h6>

            <div class="mb-3">
                <span class="doc-status status-available">
                    Available
                </span>
            </div>

            <a href="wcr/work_completion_list.php"
               class="btn btn-success doc-btn w-100">
                Open
            </a>

        </div>
    </div>
</div>

        <!-- Model Agreement -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">

                    <h6 class="mb-3">Model Agreement</h6>

                    <div class="mb-3">
                        <span class="doc-status status-available">
                             Available
                        </span>
                    </div>

                    <a href="model_agreement/model_agreement_list.php"
                       class="btn btn-success doc-btn w-100">
                        Open
                    </a>

                </div>
            </div>
        </div>

        <!-- Net Metering -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">

                    <h6 class="mb-3">Net Metering</h6>

                    <div class="mb-3">
                        <span class="doc-status status-available">
                           Available
                        </span>
                    </div>

                    <a href="net_metering/net_metering_list.php"
                       class="btn btn-success doc-btn w-100">
                        Open
                    </a>

                </div>
            </div>
        </div>
        <!-- undertaking Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="doc-card h-100">
                <div class="card-body text-center">
        
                    <h6 class="mb-3">undertaking </h6>
        
                    <div class="mb-3">
                        <span class="doc-status status-available">
                            Available
                        </span>
                    </div>
        
                    <a href="undertaking/undertaking_list.php"
                       class="btn btn-success doc-btn w-100">
                        Open
                    </a>
        
                </div>
            </div>
        </div>
        

    </div>

</main>
</div>

</body>
</html>
