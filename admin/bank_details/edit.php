<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin', 'office_staff', 'sales_marketing']);
$auth->requirePermission('bank_details_management', 'edit');

$title = "Edit Bank Details";

/* =========================
   VALIDATE ID
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid bank reference');
}

$bank_id = (int)$_GET['id'];

/* =========================
   FETCH BANK DATA
========================= */
$stmt = $conn->prepare("SELECT * FROM company_bank_details WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $bank_id);
$stmt->execute();
$bank = $stmt->get_result()->fetch_assoc();

if (!$bank) {
    die('Bank record not found');
}

/* =========================
   HANDLE UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bank_name      = trim($_POST['bank_name']);
    $branch_name    = trim($_POST['branch_name']);
    $account_number = trim($_POST['account_number']);
    $account_type   = trim($_POST['account_type']);
    $ifsc_code      = trim($_POST['ifsc_code']);
    $bank_gst       = trim($_POST['bank_gst']);
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    if ($bank_name && $branch_name && $account_number && $ifsc_code) {

        $upd = $conn->prepare("
            UPDATE company_bank_details SET
                bank_name = ?,
                branch_name = ?,
                account_number = ?,
                account_type = ?,
                ifsc_code = ?,
                bank_gst = ?,
                is_active = ?
            WHERE id = ?
        ");

        $upd->bind_param(
            "ssssssii",
            $bank_name,
            $branch_name,
            $account_number,
            $account_type,
            $ifsc_code,
            $bank_gst,
            $is_active,
            $bank_id
        );

        if ($upd->execute()) {
            header("Location: index.php?updated=1");
            exit;
        } else {
            $error = "Failed to update bank details.";
        }
    } else {
        $error = "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../include/head2.php'; ?>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>

<body>

<!-- ===================== SIDEBAR ===================== -->
<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">

<!-- ===================== NAVBAR ===================== -->
<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/navbar.php';
chdir($cwd);
?>

<header class="professional-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="logo-container">
                    <i class="bi bi-bank logo-icon"></i>
                    <div class="header-content">
                        <h1>Edit Bank Details</h1>
                        <p>Update company bank account information</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="alert alert-primary text-center text-dark fw-bold mb-4">
    Edit Company Bank Details
</div>

<div class="main-container">

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="professional-card">
        <div class="card-body">

            <form method="POST">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control"
                               value="<?= htmlspecialchars($bank['bank_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="branch_name" class="form-control"
                               value="<?= htmlspecialchars($bank['branch_name']) ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control"
                               value="<?= htmlspecialchars($bank['account_number']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Type</label>
                        <input type="text" name="account_type" class="form-control"
                               value="<?= htmlspecialchars($bank['account_type']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">IFSC Code <span class="text-danger">*</span></label>
                        <input type="text" name="ifsc_code" class="form-control"
                               value="<?= htmlspecialchars($bank['ifsc_code']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank GST Number</label>
                        <input type="text" name="bank_gst" class="form-control"
                               value="<?= htmlspecialchars($bank['bank_gst']) ?>">
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                        <?= $bank['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="is_active">
                        Active Bank
                    </label>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save"></i> Update Bank Details
                    </button>

                    <a href="index.php" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

            </form>

        </div>
    </div>
</div>

</div>
</body>
</html>
