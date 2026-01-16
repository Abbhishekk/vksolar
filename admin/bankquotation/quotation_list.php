<?php
// admin/bankquotation/quotation_list.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('quotation_management', 'view');

$title = 'bank_quotation_list';

// Fetch bank quotations data
$sql = "
    SELECT 
        bq.id,
        bq.client_id,
        bq.quotation_number,
        bq.customer_name,
        bq.total_amount,
        bq.final_amount,
        bq.quotation_date,
        bq.created_at,
        c.name as client_name
    FROM bank_quotations bq
    LEFT JOIN clients c ON bq.client_id = c.id
    ORDER BY bq.created_at DESC
";
$result = $conn->query($sql);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bank Quotation List</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Consistent Luxurious Styling ===== */

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
chdir(__DIR__ . '/../');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">
<?php
$cwd = getcwd();
chdir(__DIR__ . '/../');
include 'include/navbar.php';
chdir($cwd);
?>

<main class="container-fluid mt-4">

    <div class="doc-card">

        <!-- Header -->
        <div class="doc-card-header">
            <h4>Bank Quotations</h4>
            <small>Edit and manage previous bank quotations</small>
        </div>

        <!-- Body -->
        <div class="card-body p-4">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Quotation No.</th>
                            <th>Client Name</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Final Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['quotation_number'] ?: 'BQ-' . $row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['client_name'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                                    <td>₹<?= number_format($row['final_amount'], 2) ?></td>
                                    <td><?= date('d M Y', strtotime($row['quotation_date'] ?: $row['created_at'])) ?></td>
                                    <td>
                                        <a href="index.php?client_id=<?= $row['client_id'].'&quotation_id='.$row['id'] ?>"
                                           class="btn btn-sm btn-primary action-btn">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="view_quotation.php?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-info action-btn">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-muted text-center">
                                    No bank quotations found
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