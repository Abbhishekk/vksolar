<?php
// admin/workflow_steps/step5.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 5.</div>';
    return;
}

// fetch client
$sql = "SELECT id, name, name_change_require, application_no_name_change FROM clients WHERE id = ? LIMIT 1";
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

// flash messages
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

<!-- Step 5 form (Name Change Require) - matches Step 2/3/4 style -->
<form id="step5Form" action="/admin/workflow_steps/save_step5" method="post" novalidate>
  <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Name Change Required?</label>
<select name="name_change_require" id="nameChangeRequire" class="form-select" required
        onchange="window.toggleNameAppField(this.value)">
    <option value="no" <?= ($client['name_change_require'] ?? 'no') === 'no' ? 'selected' : '' ?>>No</option>
    <option value="yes" <?= ($client['name_change_require'] ?? 'no') === 'yes' ? 'selected' : '' ?>>Yes</option>
</select>

    </div>

    <div class="col-md-6 mb-3" id="nameChangeApplicationField" style="display: <?= ($client['name_change_require'] ?? 'no') === 'yes' ? 'block' : 'none' ?>;">
      <label class="form-label">Application Number</label>
      <input type="text" name="application_no_name_change" class="form-control"
             value="<?= htmlspecialchars($client['application_no_name_change'] ?? '') ?>"
             placeholder="Enter application number">
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=4&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>

<script>
// Toggle application field visibility
(function(){
  const sel = document.getElementById('nameChangeRequire');
  const field = document.getElementById('nameChangeApplicationField');
  if (!sel || !field) return;

  function toggle() {
    field.style.display = (sel.value === 'yes') ? 'block' : 'none';
  }
  sel.addEventListener('change', toggle);
  // init
  toggle();
})();
</script>
