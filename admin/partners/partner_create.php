<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin', 'office_staff', 'sales_marketing']);
$auth->requirePermission('partner_management', 'view');

$title = "Add_partner";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //print_r($_POST);
    $type           = $_POST['type'];
    $company_name   = trim($_POST['company_name']);
    $contact_person = trim($_POST['contact_person']);
    $mobile         = trim($_POST['mobile']);
    $email          = trim($_POST['email']);
    $address        = trim($_POST['address']);
    $gst_number     = trim($_POST['gst_number']);
    $created_by     = $_SESSION['user_id'] ?? null;

    if ($type && $company_name) {
        $stmt = $conn->prepare("
            INSERT INTO vendors_retailers
            (type, company_name, contact_person, mobile, email, address, gst_number, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssssi",
            $type,
            $company_name,
            $contact_person,
            $mobile,
            $email,
            $address,
            $gst_number,
            $created_by
        );
        $stmt->execute();
        header("Location: partners?success=1");
        exit;
    } else {
        $error = "Type and Company Name are required.";
    }
}
?>

<!doctype html>
<html>
<head>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<title><?= $title ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../include/sidebar.php'; ?>
<div id="main-content">
<?php include '../include/navbar.php'; ?>

<main class="container py-4">

<div class="alert alert-primary text-center fw-bold mb-4">
  Add Vendor / Retailer
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card shadow-sm p-4">

<div class="row mb-3">
  <div class="col-md-4">
    <label class="fw-bold">Type *</label>
    <select name="type" class="form-select" required>
      <option value="">Select</option>
      <option value="vendor(Manufacturer Company)">Vendor(Manufacturer Company)</option>
      <option value="retailer">Retailer</option>
    </select>
  </div>

  <div class="col-md-8">
    <label class="fw-bold">Company Name *</label>
    <input type="text" name="company_name" class="form-control" required>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label>Contact Person</label>
    <input type="text" name="contact_person" class="form-control">
  </div>
  <div class="col-md-6">
    <label>Mobile</label>
    <input type="text" name="mobile" class="form-control">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label>Email</label>
    <input type="email" name="email" class="form-control">
  </div>
  <div class="col-md-6">
    <label>GST Number</label>
    <input type="text" name="gst_number" class="form-control">
  </div>
</div>

<div class="mb-3">
  <label>Address</label>
  <textarea name="address" class="form-control" rows="3"></textarea>
</div>

<div class="text-center">
  <button class="btn btn-success px-4">
    <i class="bi bi-save"></i> Save
  </button>
  <a href="partners.php" class="btn btn-secondary px-4">
    Back
  </a>
</div>

</form>

</main>
</div>
</body>
</html>
