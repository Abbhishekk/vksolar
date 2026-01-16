<?php
// admin/bankquotation/view_quotation.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

// Optional auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$quotation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quotation_id <= 0) {
    die('Invalid quotation ID');
}

// Fetch quotation data
$stmt = $conn->prepare("
    SELECT 
        bq.*,
        c.name as client_name,
        c.mobile as client_mobile,
        c.email as client_email
    FROM bank_quotations bq
    LEFT JOIN clients c ON bq.client_id = c.id
    WHERE bq.id = ?
");
$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$quotation = $stmt->get_result()->fetch_assoc();

if (!$quotation) {
    die('Quotation not found');
}

// Fetch quotation products
$stmt = $conn->prepare("
    SELECT * FROM bank_quotation_products 
    WHERE quotation_id = ?
    ORDER BY id
");
$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>View Bank Quotation</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
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

.info-row {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}

.info-label {
    font-weight: 600;
    color: #555;
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
            <h4>Bank Quotation Details</h4>
            <small>Quotation #<?= htmlspecialchars($quotation['quotation_number'] ?: 'BQ-' . $quotation['id']) ?></small>
        </div>

        <!-- Body -->
        <div class="card-body p-4">
            
            <!-- Customer Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="mb-3">Customer Information</h5>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Name:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['customer_name']) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Phone:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['customer_phone']) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Email:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['customer_email'] ?: 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Address:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['customer_address']) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="mb-3">Quotation Details</h5>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Date:</div>
                            <div class="col-8"><?= date('d M Y', strtotime($quotation['quotation_date'])) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Validity:</div>
                            <div class="col-8"><?= date('d M Y', strtotime($quotation['validity_date'])) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">Plant Capacity:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['plant_capacity']) ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 info-label">System Type:</div>
                            <div class="col-8"><?= htmlspecialchars($quotation['system_type']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <?php if (!empty($products)): ?>
            <div class="mb-4">
                <h5 class="mb-3">Products/Services</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['description']) ?></td>
                                <td><?= $product['quantity'] ?></td>
                                <td>₹<?= number_format($product['unit_price'], 2) ?></td>
                                <td>₹<?= number_format($product['quantity'] * $product['unit_price'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Financial Summary -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Financial Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount:</span>
                                <span>₹<?= number_format($quotation['total_amount'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subsidy:</span>
                                <span>₹<?= number_format($quotation['subsidy'], 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Final Amount:</span>
                                <span>₹<?= number_format($quotation['final_amount'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 text-center">
                <a href="quotation_list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <a href="index.php?client_id=<?= $quotation['client_id'] ?>" class="btn btn-primary">
                    <i class="bi bi-pencil-square"></i> Edit Quotation
                </a>
            </div>

        </div>
    </div>

</main>
</div>

</body>
</html>