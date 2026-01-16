<?php
// admin/inventory/product_serial_view.php
require_once "connect/auth_middleware.php";
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
$auth->requirePermission('inventory_management', 'view');

$serial_id = intval($_GET['id'] ?? 0);
if (!$serial_id) {
    header('Location: product_serials.php');
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        
        $stmt = $conn->prepare("UPDATE product_serials SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssi', $new_status, $notes, $serial_id);
        
        if ($stmt->execute()) {
            $_SESSION['inv_success'] = 'Serial status updated successfully.';
        } else {
            $_SESSION['inv_error'] = 'Failed to update serial status.';
        }
        $stmt->close();
        
        header("Location: product_serial_view.php?id=$serial_id");
        exit;
    }
}

// Fetch serial details
$stmt = $conn->prepare("
    SELECT ps.*, 
           p.name as product_name, p.sku as product_sku, p.brand,
           w.name as warehouse_name
    FROM product_serials ps
    LEFT JOIN products p ON p.id = ps.product_id
    LEFT JOIN warehouses w ON w.id = ps.warehouse_id
    WHERE ps.id = ?
    LIMIT 1
");
$stmt->bind_param('i', $serial_id);
$stmt->execute();
$serial = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$serial) {
    $_SESSION['inv_error'] = 'Serial not found.';
    header('Location: product_serials.php');
    exit;
}

// Get movement history for this serial
$movements = [];
$stmt = $conn->prepare("
    SELECT sm.*, 
           wf.name as warehouse_from_name,
           wt.name as warehouse_to_name
    FROM stock_movements sm
    LEFT JOIN warehouses wf ON wf.id = sm.warehouse_from
    LEFT JOIN warehouses wt ON wt.id = sm.warehouse_to
    WHERE sm.related_id = ? AND sm.movement_type LIKE '%serial%'
    ORDER BY sm.created_at DESC
    LIMIT 20
");
$stmt->bind_param('i', $serial_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $movements[] = $row;
}
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Serial Details - <?= htmlspecialchars($serial['serial_number']) ?></title>
    <?php require_once __DIR__ . '/../include/head2.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..'); include 'include/navbar.php'; chdir($cwd); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Serial Details</h3>
        <div>
            <a href="product_serials.php" class="btn btn-outline-secondary">← Back to Serials</a>
            <a href="stock_transfer.php?serial_id=<?= $serial_id ?>" class="btn btn-primary">Transfer</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['inv_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['inv_success']); unset($_SESSION['inv_success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['inv_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['inv_error']); unset($_SESSION['inv_error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Serial Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Serial Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Serial Number:</strong></td>
                                    <td><?= htmlspecialchars($serial['serial_number']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Product:</strong></td>
                                    <td>
                                        <?= htmlspecialchars($serial['product_name']) ?>
                                        <?php if ($serial['product_sku']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($serial['product_sku']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Brand:</strong></td>
                                    <td><?= htmlspecialchars($serial['brand'] ?? '—') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?= $serial['status'] === 'in_stock' ? 'success' : ($serial['status'] === 'sold' ? 'danger' : 'warning') ?>">
                                            <?= htmlspecialchars(ucfirst($serial['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Warehouse:</strong></td>
                                    <td><?= htmlspecialchars($serial['warehouse_name'] ?? '—') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?= htmlspecialchars($serial['created_at']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td><?= htmlspecialchars($serial['updated_at']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($serial['notes']): ?>
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-1"><?= nl2br(htmlspecialchars($serial['notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Movement History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Movement History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($movements)): ?>
                        <p class="text-muted">No movement history available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movements as $move): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($move['created_at']) ?></td>
                                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $move['movement_type']))) ?></td>
                                        <td><?= htmlspecialchars($move['warehouse_from_name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($move['warehouse_to_name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($move['note'] ?? '—') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Update Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="update_status">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="in_stock" <?= $serial['status'] === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                                <option value="sold" <?= $serial['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                                <option value="transferred" <?= $serial['status'] === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                                <option value="reserved" <?= $serial['status'] === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                <option value="damaged" <?= $serial['status'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                                <option value="returned" <?= $serial['status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add notes about this status change..."><?= htmlspecialchars($serial['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
</body>
</html>