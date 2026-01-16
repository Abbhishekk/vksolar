<?php
require_once __DIR__ . '/../../connect/auth_middleware.php';

// admin/documentmanagement/work_completion/work_completion_list.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../connect/db.php';
$auth->requirePermission('quotation_management', 'create');

// Optional auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

/*
 IMPORTANT ⚠️
 Replace REAL_CAPACITY_COLUMN with the actual column name
 from your work_completion table (example: plant_capacity, capacity_kw, etc.)
*/
$sql = "
    SELECT 
        client_id,
        sanctioned_capacity,
        name,
        created_at
    FROM work_completion_reports
    ORDER BY created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query Error: " . $conn->error);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Work Completion Report List</title>

<?php require_once __DIR__ . '/../../include/head3.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Same UI as other document lists ===== */

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
}

.table thead th {
    background: #212529;
    color: #fff;
    text-align: center;
}

.table tbody td {
    text-align: center;
    vertical-align: middle;
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
            <h4>Work Completion Reports</h4>
            <small>List of Work Completion Records</small>
        </div>

        <!-- Body -->
        <div class="card-body p-4">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>System Capacity (KW)</th>
                            <th>Consumer Name</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_id']) ?></td>
                                    <td><?= htmlspecialchars($row['sanctioned_capacity']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
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
                                <td colspan="5" class="text-muted text-center">
                                    No work completion records found
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
