<?php
// workflow_steps/step2.php
if (session_status() === PHP_SESSION_NONE) session_start();

// include DB connection (adjust path if your file is elsewhere)
require_once __DIR__ . '/../connect/db.php'; // provides $conn

// get client_id from GET or session (prefer GET)
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    // no client selected - show message
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 2.</div>';
    return;
}

// fetch existing client data
$sql = "SELECT id, name, adhar, mobile, email, district, block, taluka,pincode, village FROM clients WHERE id = ? LIMIT 1";
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

// show any flash messages specific to step2 (optional)
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
<!-- Step 2 form (Communication & Address) -->
<form id="step2Form" action="/admin/workflow_steps/save_step2" method="POST" novalidate>
  <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">ADHAR NUMBER</label>
      <input type="text" name="adhar" class="form-control" maxlength="20" value="<?= htmlspecialchars($client['adhar']) ?>" placeholder="Enter adhar number">
      <small class="form-text text-muted">Enter a 12-digit adhar number.</small>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Mobile</label>
      <input type="text" name="mobile" class="form-control" maxlength="20" value="<?= htmlspecialchars($client['mobile']) ?>" placeholder="Enter mobile number">
      <small class="form-text text-muted">Enter a 10-digit mobile number (optional if email provided).</small>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['email']) ?>" placeholder="Enter email address">
      <small class="form-text text-muted">Enter email (optional if mobile provided).</small>
    </div>

    <div class="col-md-4 mb-3">
      <label class="form-label">District</label>
      <input type="text" name="district" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['district']) ?>" placeholder="District">
    </div>

    <div class="col-md-4 mb-3">
      <label class="form-label">Block</label>
      <input type="text" name="block" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['block']) ?>" placeholder="Block">
    </div>

    <div class="col-md-4 mb-3">
      <label class="form-label">Taluka</label>
      <input type="text" name="taluka" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['taluka']) ?>" placeholder="Taluka">
    </div>
    
    <div class="col-md-4 mb-3">
      <label class="form-label">Pincode</label>
      <input type="text" name="pincode" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['pincode']) ?>" placeholder="pincode">
    </div>

    <div class="col-md-12 mb-3">
      <label class="form-label">Village / Address</label>
      <input type="text" name="village" class="form-control" maxlength="255" value="<?= htmlspecialchars($client['village']) ?>" placeholder="Village or locality">
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=1&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <input type="submit" name="submit" class="btn btn-primary" value="Save and continue">
      </div>
    </div>
  </div>
</form>
