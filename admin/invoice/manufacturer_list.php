<?php
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requirePermission('invoice_management','view');
$title = 'manufacturer_list';

$list = $conn->query("
SELECT * FROM product_manufacturers
ORDER BY name
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manufacturers</title>
<?php require_once __DIR__ . '/../include/head2.php'; ?>
</head>
<body>

<?php
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd);
$cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd);
?>

<div id="main-content">
<main class="container-fluid py-4">

<div class="card shadow-sm">
<div class="card-header bg-dark text-white d-flex justify-content-between">
<h5 class="mb-0">🏭 Product Manufacturers</h5>
<a href="manufacturer_create.php" class="btn btn-sm btn-light">+ Add Manufacturer</a>
</div>

<div class="card-body table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-light">
<tr>
<th>#</th>
<th>Name</th>
<th>GST</th>
<th>Contact</th>
<th>Phone</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($list as $i => $m): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($m['name']) ?></td>
<td><?= htmlspecialchars($m['gst_number']) ?></td>
<td><?= htmlspecialchars($m['contact_person']) ?></td>
<td><?= htmlspecialchars($m['phone']) ?></td>
<td>
<span class="badge bg-<?= $m['is_active']?'success':'secondary' ?>">
<?= $m['is_active']?'Active':'Inactive' ?>
</span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</main>
</div>

</body>
</html>
