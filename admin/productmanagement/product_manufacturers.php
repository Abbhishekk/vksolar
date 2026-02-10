<?php
// admin/productmanagement/product_manufacturers.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'product_manufacturers';

/* ================= AUTO MANUFACTURER ID ================= */
function generateManufacturerId($conn) {
    $res = $conn->query("
        SELECT manufacturer_id 
        FROM product_manufacturers 
        ORDER BY id DESC 
        LIMIT 1
    ");
    if ($res->num_rows > 0) {
        $last = $res->fetch_assoc()['manufacturer_id']; // MAN005
        $num  = (int) substr($last, 3);
        return 'MAN' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
    return 'MAN001';
}

/* ================= ADD / UPDATE ================= */
if (isset($_POST['save_manufacturer'])) {

    $id                = $_POST['id'] ?? '';
    $manufacturer_id   = $_POST['manufacturer_id'];
    $manufacturer_name = strtoupper(trim($_POST['manufacturer_name']));
    $category_id       = (int) $_POST['category_id'];

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE product_manufacturers
            SET manufacturer_name = ?, category_id = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sii", $manufacturer_name, $category_id, $id);
        $stmt->execute();
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO product_manufacturers
            (manufacturer_id, manufacturer_name, category_id)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ssi", $manufacturer_id, $manufacturer_name, $category_id);
        $stmt->execute();
    }

    header("Location: product_manufacturers.php");
    exit;
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM product_manufacturers WHERE id = $id");
    header("Location: product_manufacturers.php");
    exit;
}

/* ================= EDIT FETCH ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $edit = $conn->query("
        SELECT * FROM product_manufacturers WHERE id = $id
    ")->fetch_assoc();
}

/* ================= FETCH ALL MANUFACTURERS ================= */
$manufacturers = $conn->query("
    SELECT m.*, c.category_name
    FROM product_manufacturers m
    JOIN product_categories c ON c.id = m.category_id
    ORDER BY m.id DESC
");

/* ================= FETCH CATEGORIES FOR DROPDOWN ================= */
$categories = $conn->query("
    SELECT id, category_name 
    FROM product_categories 
    ORDER BY category_name
");

$nextManufacturerId = generateManufacturerId($conn);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Product Manufacturers</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.card-custom {
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    border: none;
}
.card-header-custom {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    border-radius: 16px 16px 0 0;
}
.table-scroll {
    max-height: 520px; /* ~15 rows */
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

<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">

<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/navbar.php';
chdir($cwd);
?>

<main class="container-fluid">

<div class="row">

    <!-- FORM (LEFT) -->
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header card-header-custom">
                <h6 class="mb-0">
                    <?= $edit ? 'Edit Manufacturer' : 'Add Manufacturer'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? ''; ?>">

                    <div class="mb-3">
                        <label class="form-label">Manufacturer ID</label>
                        <input type="text" name="manufacturer_id"
                               class="form-control"
                               value="<?= $edit['manufacturer_id'] ?? $nextManufacturerId; ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Manufacturer Company Name</label>
                        <input type="text" name="manufacturer_name"
                               class="form-control"
                               value="<?= $edit['manufacturer_name'] ?? ''; ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id']; ?>"
                                    <?= isset($edit) && $edit['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?= $cat['category_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" name="save_manufacturer"
                            class="btn btn-success w-100">
                        <?= $edit ? 'Update Manufacturer' : 'Save Manufacturer'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE (RIGHT) -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h6 class="mb-0">Product Manufacturers</h6>
            </div>
            <div class="card-body p-0 table-scroll">
                <table class="table table-bordered table-hover mb-0">

                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th width="140">Manufacturer ID</th>
                            <th>Company Name</th>
                            <th>Category</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($manufacturers->num_rows > 0): ?>
                            <?php $i=1; while ($row = $manufacturers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= $row['manufacturer_id']; ?></td>
                                    <td><?= htmlspecialchars($row['manufacturer_name']); ?></td>
                                    <td><?= htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <a href="?edit=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="?delete=<?= $row['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this manufacturer?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No manufacturers found
                                </td>
                            </tr>
                        <?php endif; ?>
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
