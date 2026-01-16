<?php
// admin/workflow_steps/step4.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // must supply $conn (mysqli)

// Read client_id from GET
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 4.</div>';
    return;
}

// Fetch existing values
$sql = "SELECT id, name, mahadiscom_user_id, mahadiscom_password FROM clients WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client = $res->fetch_assoc();
$stmt->close();

if (!$client) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
}

// show flash messages if any
if (!empty($_SESSION['workflow_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['workflow_errors'] as $err) {
        echo htmlspecialchars($err) . "<br>";
    }
    echo '</div>';
    unset($_SESSION['workflow_errors']);
}
if (!empty($_SESSION['workflow_success'])) {
    echo '<div class="alert alert-success">';
    echo htmlspecialchars($_SESSION['workflow_success']);
    echo '</div>';
    unset($_SESSION['workflow_success']);
}
?>

<!-- Step 4 form (MAHADISCOM Registration) -->
<form id="step4Form" action="/admin/workflow_steps/save_step4" method="POST" novalidate>
  <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">MAHADISCOM User ID</label>
      <input type="text" name="mahadiscom_user_id" class="form-control" maxlength="255"
             value="<?= htmlspecialchars($client['mahadiscom_user_id']) ?>" placeholder="MAHADISCOM user id (required)">
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label">MAHADISCOM Password</label>
      <input type="text" name="mahadiscom_password" class="form-control" maxlength="255"
             value="<?= htmlspecialchars($client['mahadiscom_password']) ?>" placeholder="MAHADISCOM password (required)">
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=3&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <input type="submit" name="submit" class="btn btn-primary" value="Save & Continue →">
      </div>
    </div>
  </div>
</form>
