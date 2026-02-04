<?php
// admin/partners/partners.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requirePermission('partner_management', 'view');

$title = 'partners';

$type = $_GET['type'] ?? ''; // vendor | retailer

$sql = "
SELECT *
FROM vendors_retailers 
";

$params = [];
$types  = '';

if ($type && in_array($type, ['vendor(Manufacturer Company)','retailer'], true)) {
    $sql .= " where type = ?";
    $params[] = $type;
    $types .= 's';
}

$sql .= " ORDER BY company_name";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$partners = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Partners</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.badge-vendor { background:#0d6efd; }
.badge-retailer { background:#198754; }
</style>
</head>
<body>

<?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);?>

<div id="main-content">
    <?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd);
?>

<main class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>🤝 Partners (Vendors & Retailers)</h3>
  <?php if ($auth->checkPermission('partner_management','create')): ?>
    <a href="partner_create.php" class="btn btn-primary">
      + Add Partner
    </a>
  <?php endif; ?>
</div>

<!-- FILTER -->
<form class="row g-2 mb-3">
  <div class="col-md-3">
    <select name="type" class="form-select">
      <option value="">All Types</option>
      <option value="vendor" <?= $type==='vendor(Manufacturer Company)'?'selected':'' ?>>Vendor (Manufacturer Company)</option>
      <option value="retailer" <?= $type==='retailer'?'selected':'' ?>>Retailer</option>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-secondary w-100">Filter</button>
  </div>
</form>

<!-- TABLE -->
<div class="table-responsive">
<table class="table table-bordered table-striped table-sm align-middle">
<thead class="table-light">
<tr>
  <th>#</th>
  <th>Type</th>
  <th>Company Name</th>
  <th>Contact Person</th>
  <th>Phone</th>
  <th>GST</th>
  <th>Status</th>
  <th width="140">Actions</th>
</tr>
</thead>
<tbody>

<?php if (!$partners): ?>
<tr>
  <td colspan="8" class="text-center text-muted">No partners found</td>
</tr>
<?php endif; ?>

<?php foreach ($partners as $i => $p): ?>
<tr>
  <td><?= $i+1 ?></td>
  <td>
    <span class="badge <?= $p['type']==='vendor(Manufacturer Company)'?'badge-vendor':'badge-retailer' ?>">
      <?= ucfirst($p['type']) ?>
    </span>
  </td>
  <td><?= htmlspecialchars($p['company_name']) ?></td>
  <td><?= htmlspecialchars($p['contact_person'] ?? '—') ?></td>
  <td><?= htmlspecialchars($p['mobile'] ?? '—') ?></td>
  <td><?= htmlspecialchars($p['gst_number'] ?? '—') ?></td>
  <td>
    <?= $p['is_active'] ? 
      '<span class="badge bg-success">Active</span>' : 
      '<span class="badge bg-secondary">Inactive</span>' ?>
  </td>
  <td>
    <a href="partner_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
    <a href="partner_delete.php?id=<?= $p['id'] ?>"
       class="btn btn-sm btn-outline-danger"
       onclick="return confirm('Delete this partner?')">
       Delete
    </a>
  </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

</main>
</div>

</body>
</html>
