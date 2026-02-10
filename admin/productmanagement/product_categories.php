<?php
// admin/productmanagement/product_categories.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'product_categories';

/* ================= AUTO CATEGORY ID ================= */
function generateCategoryId($conn) {
    $res = $conn->query("SELECT category_id FROM product_categories ORDER BY id DESC LIMIT 1");
    if ($res->num_rows > 0) {
        $last = $res->fetch_assoc()['category_id']; // CAT005
        $num  = (int) substr($last, 3);
        return 'CAT' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
    return 'CAT001';
}

/* ================= ADD / UPDATE ================= */
if (isset($_POST['save_category'])) {

    $id            = $_POST['id'] ?? '';
    $category_id   = $_POST['category_id'];
    $category_name = strtoupper(trim($_POST['category_name']));

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE product_categories
            SET category_name = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $category_name, $id);
        $stmt->execute();
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO product_categories (category_id, category_name)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $category_id, $category_name);
        $stmt->execute();
    }

    header("Location: product_categories.php");
    exit;
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM product_categories WHERE id = $id");
    header("Location: product_categories.php");
    exit;
}

/* ================= EDIT FETCH ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $edit = $conn->query("
        SELECT * FROM product_categories WHERE id = $id
    ")->fetch_assoc();
}

/* ================= FETCH ALL ================= */
$categories = $conn->query("
    SELECT * FROM product_categories ORDER BY id DESC
");

$nextCategoryId = generateCategoryId($conn);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Product Categories</title>

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
                    <?= $edit ? 'Edit Category' : 'Add Category'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? ''; ?>">

                    <div class="mb-3">
                        <label class="form-label">Category ID</label>
                        <input type="text" name="category_id"
                               class="form-control"
                               value="<?= $edit['category_id'] ?? $nextCategoryId; ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name"
                               class="form-control"
                               value="<?= $edit['category_name'] ?? ''; ?>"
                               required>
                    </div>

                    <button type="submit" name="save_category"
                            class="btn btn-success w-100">
                        <?= $edit ? 'Update Category' : 'Save Category'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE (RIGHT) -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h6 class="mb-0">Product Categories</h6>
            </div>
            <div class="card-body p-0 table-scroll">
                <table class="table table-bordered table-hover mb-0">

                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th width="140">Category ID</th>
                            <th>Category Name</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($categories->num_rows > 0): ?>
                            <?php $i=1; while ($row = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $row['category_id']; ?></td>
                                    <td><?= htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <a href="?edit=<?= $row['id']; ?>"
                                           class="btn btn-sm btn-primary">Edit</a>

                                        <a href="?delete=<?= $row['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this category?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php $i++; endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No categories found
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
