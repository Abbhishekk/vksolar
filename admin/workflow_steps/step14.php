<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if (!$client_id) {
    echo '<div class="alert alert-warning">Please select a client before filling Step 14.</div>';
    return;
}

$stmt = $conn->prepare("SELECT id, name, reference_name, reference_contact FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client_data = $res->fetch_assoc();
$stmt->close();

if (!$client_data) {
    echo '<div class="alert alert-danger">Client not found.</div>';
    return;
}

if (!empty($_SESSION['workflow_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['workflow_errors'] as $e) echo htmlspecialchars($e) . "<br>";
    echo '</div>';
    unset($_SESSION['workflow_errors']);
}

if (!empty($_SESSION['workflow_success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['workflow_success']) . '</div>';
    unset($_SESSION['workflow_success']);
}
?>

<form action="/admin/workflow_steps/save_step14" method="POST">
    <input type="hidden" name="client_id" value="<?= htmlspecialchars($client_data['id']) ?>">

    <div class="mb-3">
        <label class="form-label fw-bold">Client</label>
        <div class="user-info p-2">
            <?= htmlspecialchars($client_data['name']) ?> (ID: <?= $client_data['id'] ?>)
        </div>
    </div>

    <div class="row">

        <div class="col-md-6 mb-3">
            <label class="form-label">Reference Name</label>
            <input type="text" class="form-control" name="reference_name"
                   value="<?= htmlspecialchars($client_data['reference_name'] ?? '') ?>">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Reference Contact</label>
            <input type="text" class="form-control" name="reference_contact"
                   value="<?= htmlspecialchars($client_data['reference_contact'] ?? '') ?>">
        </div>

    </div>

    <div class="form-navigation mt-3">
        <div class="row">
            <div class="col-md-6">
                <a href="/admin/workflow.php?step=13&client_id=<?= $client_id ?>" class="btn btn-secondary">← Previous</a>
            </div>
            <div class="col-md-6 text-end">
                <button type="submit" class="btn btn-primary">Save & Finish</button>
            </div>
        </div>
    </div>
</form>
