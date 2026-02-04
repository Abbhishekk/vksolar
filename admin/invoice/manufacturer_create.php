<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requirePermission('invoice_management','create');

$title = 'manufacturer_create';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name            = trim($_POST['name']);
    $gst_number      = trim($_POST['gst_number']);
    $contact_person  = trim($_POST['contact_person']);
    $phone           = trim($_POST['phone']);
    $email           = trim($_POST['email']);
    $address         = trim($_POST['address']);

    if ($name === '') {
        $_SESSION['inv_error'] = 'Manufacturer name is required';
    } else {

        $stmt = $conn->prepare("
            INSERT INTO product_manufacturers
            (name, gst_number, contact_person, phone, email, address)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "ssssss",
            $name,
            $gst_number,
            $contact_person,
            $phone,
            $email,
            $address
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['inv_success'] = 'Manufacturer added successfully';
        header("Location: manufacturer_list.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Manufacturer</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
</head>
<body>

<?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd);
?>

<div id="main-content">
<main class="container-fluid py-4">

<div class="card shadow-sm mx-auto" style="max-width:900px">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">➕ Add Product Manufacturer</h5>
</div>

<div class="card-body">

<?php if (!empty($_SESSION['inv_error'])): ?>
<div class="alert alert-danger">
<?= $_SESSION['inv_error']; unset($_SESSION['inv_error']); ?>
</div>
<?php endif; ?>

<form method="post">

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Manufacturer Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">GST Number</label>
    <input type="text" name="gst_number" class="form-control">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Contact Person</label>
    <input type="text" name="contact_person" class="form-control">
  </div>

  <div class="col-md-6">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Address</label>
  <textarea name="address" class="form-control" rows="3"></textarea>
</div>

<div class="d-flex gap-2">
  <button class="btn btn-success">Save Manufacturer</button>
  <a href="manufacturer_list.php" class="btn btn-outline-secondary">Cancel</a>
</div>

</form>

</div>
</div>

</main>
</div>

</body>
</html>
