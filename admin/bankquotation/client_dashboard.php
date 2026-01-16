<?php
// admin/documentmanagement/client_dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';

$title = 'client_document_dashboard';

// STEP 2 must not open without client_id
$client_id = 0;

if (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) {
    $client_id = (int) $_POST['client_id'];
} elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
    $client_id = (int) $_GET['client_id'];
}

if ($client_id <= 0) {
    die("Invalid client selection.");
}

// Fetch client details
$stmt = $conn->prepare("
    SELECT id, name, consumer_number, mobile, village, taluka, district
    FROM clients
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if (!$client) {
    die("Client not found.");
}

// Document list (status will be dynamic later)
$documents = [
    'bank_quotation'  => 'Bank Quotation'
];
// ---------------- Document Status Check ----------------
$docStatus = [];

$tables = [
    'bank_quotation'     => 'bank_quotations'
    
];

foreach ($tables as $key => $table) {
    $stmt = $conn->prepare("SELECT id FROM {$table} WHERE client_id = ? LIMIT 1");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $stmt->store_result();

    $docStatus[$key] = $stmt->num_rows > 0 ? 'created' : 'pending';
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Client Documents</title>

<?php require_once __DIR__ . '/../include/head2.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.doc-card {
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    border: none;
    background: #fff;
}

.doc-card-header {
    background: linear-gradient(135deg, #30935C);
    color: #fff;
    padding: 18px 22px;
    border-radius: 16px 16px 0 0;
}

.client-info span {
    display: block;
    font-size: 14px;
    color: #495057;
}

.doc-btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 12px;
}

.doc-status {
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 20px;
}

.status-pending {
    background: #ffeeba;
    color: #856404;
}

.status-created {
    background: #d4edda;
    color: #155724;
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

    <!-- Client Summary -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="doc-card">
                <div class="doc-card-header">
                    <h5 class="mb-0">Client Details</h5>
                </div>
                <div class="card-body client-info">
                    <strong><?= htmlspecialchars($client['name']); ?></strong>
                    <span>Consumer No: <?= htmlspecialchars($client['consumer_number']); ?></span>
                    <span>Mobile: <?= htmlspecialchars($client['mobile']); ?></span>
                    <span>
                        Location:
                        <?= htmlspecialchars($client['village']); ?>,
                        <?= htmlspecialchars($client['taluka']); ?>,
                        <?= htmlspecialchars($client['district']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Actions -->
    <div class="row">
        <?php foreach ($documents as $key => $label): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="doc-card h-100">
                    <div class="card-body text-center">

                        <h6 class="mb-3"><?= $label; ?></h6>

                        <!-- Status placeholder (dynamic later) -->
                        <div class="mb-3">
                            <?php if ($docStatus[$key] === 'created'): ?>
                                <span class="doc-status status-created">
                                    Created
                                </span>
                            <?php else: ?>
                                <span class="doc-status status-pending">
                                    Not Created
                                </span>
                            <?php endif; ?>
                        </div>
                        <form method="post" action="index">
                            <input type="hidden" name="client_id" value="<?= $client_id; ?>">
                            <button type="submit" class="btn btn-success doc-btn w-100">
                                <?= $docStatus[$key] === 'created' ? 'View / Edit' : 'Create'; ?>
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>
</div>

</body>
</html>
