<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('inventory_management', 'create');

$title = 'warehouses';

$edit = null;
$assignedEmp = [];

/* ================= EDIT FETCH ================= */
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $q = $conn->prepare("SELECT employee_id FROM warehouse_employees WHERE warehouse_id=?");
    $q->bind_param("i",$id);
    $q->execute();
    $r = $q->get_result();
    while ($er = $r->fetch_assoc()) {
        $assignedEmp[] = $er['employee_id'];
    }
    $q->close();
}

/* ================= FETCH EMPLOYEES ================= */
$employees = [];
$res = $conn->query("SELECT id, full_name, phone FROM employees ORDER BY full_name ASC");
while ($row = $res->fetch_assoc()) {
    $employees[] = $row;
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Check stock exists
    $check = $conn->prepare("SELECT id FROM warehouse_stock WHERE warehouse_id=? LIMIT 1");
    $check->bind_param("i",$id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['inv_errors'] = ["Cannot delete warehouse. Stock exists."];
    } else {

        // Fetch image name first
        $imgQuery = $conn->prepare("SELECT image FROM warehouses WHERE id=?");
        $imgQuery->bind_param("i",$id);
        $imgQuery->execute();
        $imgRes = $imgQuery->get_result()->fetch_assoc();
        $imgQuery->close();

        if (!empty($imgRes['image'])) {
            $imagePath = __DIR__ . '/uploads/' . $imgRes['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // delete file
            }
        }

        $conn->query("DELETE FROM warehouses WHERE id=$id");
        $_SESSION['inv_success'] = "Warehouse deleted successfully.";
    }

    header("Location: warehouses.php");
    exit;
}


/* ================= FETCH LIST ================= */
$warehouses = $conn->query("
    SELECT w.*,
    GROUP_CONCAT(e.full_name SEPARATOR ', ') AS employee_names
    FROM warehouses w
    LEFT JOIN warehouse_employees we ON we.warehouse_id = w.id
    LEFT JOIN employees e ON e.id = we.employee_id
    GROUP BY w.id
    ORDER BY w.id DESC
");

$errors = $_SESSION['inv_errors'] ?? null;
$success = $_SESSION['inv_success'] ?? null;
unset($_SESSION['inv_errors'], $_SESSION['inv_success']);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Warehouses</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.card-custom {
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
}
.card-header-custom {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    border-radius: 16px 16px 0 0;
}
.table-scroll {
    max-height: 520px;
    overflow-y: auto;
}
.table-scroll thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 2;
}
</style>
</head>
<body>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div id="main-content">
<?php include __DIR__ . '/../include/navbar.php'; ?>

<main class="container-fluid py-4">

<?php if ($errors): ?>
<div class="alert alert-danger"><?php foreach ($errors as $e) echo $e."<br>"; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
<h3 class="mb-0">Warehouses</h3>
<a href="warehouses_cards.php" class="btn btn-primary">All Warehouses</a>
</div>

<div class="row">

<!-- LEFT FORM -->
<div class="col-lg-4 mb-4">
<div class="card card-custom">
<div class="card-header card-header-custom">
<h6 class="mb-0"><?= $edit ? 'Edit Warehouse' : 'Add Warehouse'; ?></h6>
</div>
<div class="card-body">

<form action="warehouse_save" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

<div class="mb-3">
<label class="form-label">Warehouse Name *</label>
<input type="text" name="name" required class="form-control"
value="<?= $edit['name'] ?? '' ?>">
</div>

<div class="mb-3">
<label class="form-label">Code</label>
<input type="text" name="code" class="form-control"
value="<?= $edit['code'] ?? '' ?>">
</div>

<div class="mb-3">
<label class="form-label">Address</label>
<textarea name="address" class="form-control"><?= $edit['address'] ?? '' ?></textarea>
</div>

<div class="row">
<div class="col-md-4 mb-3">
<label class="form-label">City</label>
<input type="text" name="city" class="form-control"
value="<?= $edit['city'] ?? '' ?>">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">State</label>
<input type="text" name="state" class="form-control"
value="<?= $edit['state'] ?? '' ?>">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Pincode</label>
<input type="text" name="pincode" class="form-control"
value="<?= $edit['pincode'] ?? '' ?>">
</div>
</div>

<div class="mb-3">
<label class="form-label">Contact Person</label>
<input type="text" name="contact_name" class="form-control"
value="<?= $edit['contact_name'] ?? '' ?>">
</div>

<div class="mb-3">
<label class="form-label">Contact Phone</label>
<input type="text" name="contact_phone" class="form-control"
value="<?= $edit['contact_phone'] ?? '' ?>">
</div>

<div class="mb-3">
<label class="form-label">Warehouse Image</label>
<input type="file" name="image" accept="image/*" class="form-control">
<?php if (!empty($edit['image'])): ?>
<div class="mt-2">
<img src="uploads/<?= htmlspecialchars($edit['image']) ?>"
style="height:60px;border-radius:6px;">
</div>
<?php endif; ?>
</div>

<div class="mb-3">
<label class="form-label">Assign Employees</label>
<select name="employees[]" class="form-select" multiple size="5">
<?php foreach ($employees as $emp): ?>
<option value="<?= $emp['id'] ?>"
<?= in_array($emp['id'], $assignedEmp) ? 'selected' : '' ?>>
<?= $emp['full_name'] ?> • <?= $emp['phone'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<button class="btn btn-success w-100">
<?= $edit ? 'Update Warehouse' : 'Save Warehouse' ?>
</button>

</form>
</div>
</div>
</div>

<!-- RIGHT TABLE -->
<div class="col-lg-8">
<div class="card card-custom">
<div class="card-header card-header-custom">
<h6 class="mb-0">Warehouse List</h6>
</div>
<div class="card-body p-0 table-scroll">
<table class="table table-bordered table-hover mb-0">
<thead class="table-light">
<tr>
<th>#</th>
<th>Name</th>
<th>Code</th>
<th>City</th>
<th>Employees</th>
<th width="">Action</th>
</tr>
</thead>
<tbody>
<?php $i=1; while ($row = $warehouses->fetch_assoc()): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= $row['code'] ?></td>
<td><?= $row['city'] ?></td>
<td><?= htmlspecialchars($row['employee_names'] ?? '-') ?></td>
<td>
<a href="warehouse_view.php?id=<?= $row['id'] ?>"
       class="btn btn-sm btn-info">
       View
</a>
<a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
<a href="?delete=<?= $row['id'] ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this warehouse?')">
Delete
</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>

</div>
</main>
</div>
</body>
</html>
