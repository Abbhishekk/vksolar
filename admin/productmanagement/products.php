<?php
// admin/productmanagement/products.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'products';

/* ================= FETCH CATEGORIES ================= */
$categories = $conn->query("
    SELECT id, category_name
    FROM product_categories
    ORDER BY category_name
");

/* ================= EDIT PRODUCT ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $edit = $conn->query("
        SELECT * FROM products WHERE id = $id
    ")->fetch_assoc();
}

/* ================= SAVE / UPDATE PRODUCT ================= */
if (isset($_POST['save_product'])) {

    $id          = $_POST['id'] ?? '';
    $name        = strtoupper(trim($_POST['name']));
    $category_id = (int) $_POST['category_id'];
    $hsn_code    = trim($_POST['hsn_code']);
    $created_by  = $_SESSION['user_id'] ?? null;
    $product_id = $_POST['product_id'];
    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE products
            SET name=?, category_id=?, hsn_code=?
            WHERE id=?
        ");
        $stmt->bind_param("sisi", $name, $category_id, $hsn_code, $id);
    } else {
        // INSERT
        $stmt = $conn->prepare("
                INSERT INTO products
                (product_id, name, category_id, hsn_code, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssisi",
                $product_id,
                $name,
                $category_id,
                $hsn_code,
                $created_by
            );

    }

    $stmt->execute();
    header("Location: products.php");
    exit;
}

/* ================= DELETE PRODUCT ================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit;
}

/* ================= FETCH PRODUCTS ================= */
$products = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN product_categories c ON c.id = p.category_id
    ORDER BY p.id DESC
");

/* ================= AUTO PRODUCT ID ================= */
function generateProductId($conn) {
    $res = $conn->query("
        SELECT product_id 
        FROM products 
        ORDER BY id DESC 
        LIMIT 1
    ");
    if ($res->num_rows > 0) {
        $last = $res->fetch_assoc()['product_id']; // PROD005
        $num  = (int) substr($last, 4);
        return 'PROD' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
    return 'PROD001';
}

$nextProductId = generateProductId($conn);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Products</title>

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
    <div class="col-lg-4 mb-4">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h6 class="mb-0"><?= $edit ? 'Edit Product' : 'Add Product'; ?></h6>
            </div>
            <div class="card-body">
                <form method="post">

                    <input type="hidden" name="id" value="<?= $edit['id'] ?? ''; ?>">

                    <div class="mb-3">
                        <label class="form-label">Product Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id']; ?>"
                                    <?= isset($edit) && $edit['category_id'] == $c['id'] ? 'selected' : ''; ?>>
                                    <?= $c['category_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product ID</label>
                        <input type="text"
                               name="product_id"
                               class="form-control"
                               value="<?= $edit['product_id'] ?? $nextProductId; ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="<?= $edit['name'] ?? ''; ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">HSN Code</label>
                        <input type="text"
                               name="hsn_code"
                               class="form-control"
                               value="<?= $edit['hsn_code'] ?? ''; ?>">
                    </div>

                    <button type="submit"
                            name="save_product"
                            class="btn btn-success w-100">
                        <?= $edit ? 'Update Product' : 'Save Product'; ?>
                    </button>

                </form>
            </div>
        </div>
    </div>

    <!-- TABLE (RIGHT) -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h6 class="mb-0">Product List</h6>
            </div>
            <div class="card-body p-0 table-scroll">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th width="120">Product ID</th>
                            <th>Category</th>
                            <th>HSN Code</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= $row['product_id']; ?></td>
                            <td><?= $row['category_name']; ?></td>
                            <td><?= $row['hsn_code']; ?></td>
                            <td>
                                <a href="?edit=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="?delete=<?= $row['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this product?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($i === 1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No products found
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
