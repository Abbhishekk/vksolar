<?php
// admin/inventory/warehouse_create.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('inventory_management', 'create');

$upload_url_prefix = '/admin/inventory/';

// read id for edit
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$warehouse = [
  'id'=>0,'name'=>'','code'=>'','address'=>'','city'=>'','state'=>'','pincode'=>'','contact_name'=>'','contact_phone'=>'','image'=>''
];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id = ? LIMIT 1");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) $warehouse = $row;
    $stmt->close();
    // fetch assigned employees
    $assignedEmp = [];
    $q = $conn->prepare("SELECT employee_id FROM warehouse_employees WHERE warehouse_id = ?");
    $q->bind_param('i',$id);
    $q->execute();
    $r = $q->get_result();
    if ($r) while ($er = $r->fetch_assoc()) $assignedEmp[] = $er['employee_id'];
    $q->close();
} else {
    $assignedEmp = [];
}

// fetch employees for selector (your employees table)
// simple fetch of employees for selector (uses employees.full_name)
$employees = [];

$q = $conn->query("SELECT id, full_name, email, phone FROM employees ORDER BY full_name ASC");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $employees[] = [
            'id'    => $r['id'],
            'name'  => $r['full_name'],   // keep 'name' key so existing UI works
            'email' => $r['email'] ?? '',
            'phone' => $r['phone'] ?? ''
        ];
    }
} else {
    // fallback: no employees or query error
    error_log('employees fetch failed: ' . $conn->error);
}


// any flash
$errors = $_SESSION['inv_errors'] ?? null;
$success = $_SESSION['inv_success'] ?? null;
unset($_SESSION['inv_errors'], $_SESSION['inv_success']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $id ? 'Edit' : 'Create' ?> Warehouse</title>
  <?php require_once __DIR__ . '/../include/head2.php'; ?>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
// safely include admin sidebar without changing sidebar.php
$cwd = getcwd();
chdir(__DIR__ . '/..');        // change to /path/to/admin
include 'include/sidebar.php';
chdir($cwd);
 ?>
<div id="main-content">
  <?php 
  $cwd = getcwd();
chdir(__DIR__ . '/..');        // change to /path/to/admin
include 'include/navbar.php';
chdir($cwd);
?>
  <main class="main container py-4">
    <h3><?= $id ? 'Edit' : 'Add' ?> Warehouse</h3>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e)."<br>"; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="warehouse_save" method="post" enctype="multipart/form-data" class="card p-3">
      <input type="hidden" name="id" value="<?= intval($warehouse['id']) ?>">

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Warehouse Name *</label>
          <input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($warehouse['name']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Code</label>
          <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($warehouse['code']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">City</label>
          <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($warehouse['city']) ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($warehouse['address']) ?></textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">State</label>
          <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($warehouse['state']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Pincode</label>
          <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($warehouse['pincode']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Contact Phone</label>
          <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($warehouse['contact_phone']) ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Person</label>
        <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($warehouse['contact_name']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Warehouse Image (optional; max 5MB)</label>
        <input type="file" name="image" accept="image/*" class="form-control">
        <?php if (!empty($warehouse['image'])): ?>
          <div class="mt-2">Current: <img src="<?= $upload_url_prefix . htmlspecialchars($warehouse['image']) ?>" style="height:60px;border-radius:4px"></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Assign Employees</label>
        <select name="employees[]" class="form-select" multiple size="6">
          <?php foreach($employees as $emp): $eid = intval($emp['id']); ?>
            <option value="<?= $eid ?>" <?= in_array($eid, $assignedEmp) ? 'selected' : '' ?>>
              <?= htmlspecialchars($emp['name'] . ' • ' . ($emp['phone'] ?: $emp['email'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
      </div>

      <div class="d-flex justify-content-between">
        <a href="warehouses.php" class="btn btn-secondary">← Back</a>
        <button class="btn btn-primary" type="submit">Save Warehouse</button>
      </div>
    </form>
  </main>
</div>
</body>
</html>
