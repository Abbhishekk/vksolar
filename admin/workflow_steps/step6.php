<?php
// admin/workflow_steps/step6.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 6.</div>';
    return;
}

// fetch existing values
$sql = "SELECT id, name, pm_suryaghar_registration, pm_suryaghar_app_id, pm_registration_date 
        FROM clients WHERE id = ? LIMIT 1";
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

// flash
if (!empty($_SESSION['workflow_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['workflow_errors'] as $err) echo htmlspecialchars($err) . "<br>";
    echo '</div>';
    unset($_SESSION['workflow_errors']);
}
if (!empty($_SESSION['workflow_success'])) {
    echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['workflow_success']).'</div>';
    unset($_SESSION['workflow_success']);
}
?>

<!-- Step 6 form (PM Suryaghar Registration) -->
<form id="step6Form" action="/admin/workflow_steps/save_step6" method="post" novalidate>
  <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">PM Suryaghar Registration?</label>
      <select name="pm_suryaghar_registration" id="pmSuryagharRegistration" class="form-select" 
              onchange="window.togglePmSuryagharFields && window.togglePmSuryagharFields(this.value)" required>
        <option value="no" <?= ($client['pm_suryaghar_registration'] ?? 'no') === 'no' ? 'selected' : '' ?>>No</option>
        <option value="yes" <?= ($client['pm_suryaghar_registration'] ?? 'no') === 'yes' ? 'selected' : '' ?>>Yes</option>
      </select>
    </div>

    <div class="col-md-6 mb-3" id="pmSuryagharAppIdField" style="display: <?= ($client['pm_suryaghar_registration'] ?? 'no') === 'yes' ? 'block' : 'none' ?>;">
      <label class="form-label">PM Suryaghar Application ID</label>
      <input type="text" name="pm_suryaghar_app_id" class="form-control"
             value="<?= htmlspecialchars($client['pm_suryaghar_app_id'] ?? '') ?>"
             placeholder="Enter PM Suryaghar application id">
    </div>

    <div class="col-md-6 mb-3" id="pmSuryagharDateField" style="display: <?= ($client['pm_suryaghar_registration'] ?? 'no') === 'yes' ? 'block' : 'none' ?>;">
      <label class="form-label">PM Registration Date</label>
      <input type="date" name="pm_registration_date" class="form-control"
             value="<?= htmlspecialchars(!empty($client['pm_registration_date']) ? $client['pm_registration_date'] : '') ?>">
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=5&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>

<!-- Inline fallback initializer (server-side display handles initial state; this helps when fragment injected) -->
<script>
(function(){
  // if global toggler exists, call it for initial state; otherwise rely on server-side style
  try {
    var sel = document.getElementById('pmSuryagharRegistration');
    if (sel && window.togglePmSuryagharFields) window.togglePmSuryagharFields(sel.value);
  } catch(e){ console.error(e); }
})();
</script>
