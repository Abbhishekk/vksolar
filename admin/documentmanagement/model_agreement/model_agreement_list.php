<?php
// admin/documentmanagement/model_agreement/model_agreement_list.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';
require_once __DIR__ . '/../../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin','admin','office_staff']);
$auth->requirePermission('quotation_management', 'view');

// Fetch Model Agreement records
$sql = "
    SELECT
        client_id,
        applicant_name,
        consumer_number,
        system_capacity,
        created_at
    FROM model_agreements
    ORDER BY created_at DESC
";

$result = $conn->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Model Agreement List</title>

<?php require_once __DIR__ . '/../../include/head3.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== SAME UI AS COMMISSIONING LIST ===== */

.doc-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    background: #ffffff;
}

.doc-card-header {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    padding: 22px;
    border-radius: 16px 16px 0 0;
    text-align: center;
}

.doc-card-header h4 {
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.table thead th {
    background: #212529;
    color: #fff;
    text-align: center;
    vertical-align: middle;
}

.table tbody td {
    vertical-align: middle;
    text-align: center;
}

.action-btn {
    border-radius: 8px;
    font-weight: 600;
}
</style>
</head>

<body>

<?php
$cwd = getcwd();
chdir(__DIR__ . '/../../');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">

<?php
$cwd = getcwd();
chdir(__DIR__ . '/../../');
include 'include/navbar.php';
chdir($cwd);
?>

<main class="container-fluid mt-4">

    <div class="doc-card">

        <!-- Header -->
        <div class="doc-card-header">
            <h4>Model Agreements</h4>
            <small>List of Model Agreement Records</small>
        </div>

        <!-- Body -->
        <div class="card-body p-4">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Applicant Name</th>
                            <th>Consumer No</th>
                            <th>System Capacity (kW)</th>
                            <th>Created At</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_id']) ?></td>
                                    <td><?= htmlspecialchars($row['applicant_name']) ?></td>
                                    <td><?= htmlspecialchars($row['consumer_number']) ?></td>
                                    <td><?= htmlspecialchars($row['system_capacity']) ?> kW</td>
                                    <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <a href="index.php?client_id=<?= $row['client_id'] ?>"
                                           class="btn btn-sm btn-primary action-btn">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-muted text-center">
                                    No Model Agreements Found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</main>
</div>

</body>
</html>
