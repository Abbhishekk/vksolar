<?php
// admin/workflow_steps/step11.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// Get client_id from GET
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 11.</div>';
    return;
}

// fetch client and current value
$stmt = $conn->prepare("SELECT id, name, rts_portal_status FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client = $res->fetch_assoc();
$stmt->close();

if (!$client) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
}

// flash messages (if any)
if (!empty($_SESSION['workflow_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['workflow_errors'] as $err) echo htmlspecialchars($err) . "<br>";
    echo '</div>';
    unset($_SESSION['workflow_errors']);
}
if (!empty($_SESSION['workflow_success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['workflow_success']) . '</div>';
    unset($_SESSION['workflow_success']);
}
?>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Step 11: RTS Portal Status</h5>
  </div>
  <div class="card-body">
    <form id="step11Form" action="/admin/workflow_steps/save_step11" method="post" novalidate onsubmit="return validateStep11();">
      <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">

      <div class="mb-3">
        <label class="form-label fw-bold">Client</label>
        <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">RTS Portal Documents Updated? <span class="text-danger">*</span></label>
          <select class="form-select" name="rts_portal_status" id="rtsPortalStatus" required>
            <option value="no" <?= (($client['rts_portal_status'] ?? 'no') === 'no') ? 'selected' : '' ?>>No</option>
            <option value="yes" <?= (($client['rts_portal_status'] ?? 'no') === 'yes') ? 'selected' : '' ?>>Yes</option>
          </select>
        </div>
      </div>

      <div class="form-navigation mt-3">
        <div class="row">
          <div class="col-md-6">
            <a href="/admin/workflow.php?step=10&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
          </div>
          <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-primary">Save & Continue →</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function validateStep11() {
  const sel = document.getElementById('rtsPortalStatus');
  if (!sel || !sel.value) {
    alert('Please select RTS Portal status.');
    return false;
  }
  return true;
}

// If fragments are injected via AJAX, make this initializer available for loader to call
window.initStep11 = window.initStep11 || function(step, clientId, stepContent) {
  // nothing complex needed, but keep placeholder for consistency
  const form = (stepContent || document).querySelector('#step11Form');
  if (form) {
    // example: focus select for accessibility
    const sel = form.querySelector('#rtsPortalStatus');
    if (sel) sel.focus();
  }
};
</script>
