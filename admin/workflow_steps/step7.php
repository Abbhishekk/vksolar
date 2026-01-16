<?php
// admin/workflow_steps/step7.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 7.</div>';
    return;
}

// fetch current values
$sql = "SELECT id, name, load_change_application_number, rooftop_solar_application_number FROM clients WHERE id = ? LIMIT 1";
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

<!-- Step 7: MAHADISCOM Sanction Load (matches earlier UI) -->
<form id="step7Form" action="/admin/workflow_steps/save_step7" method="post" onsubmit="return validateStep7()" novalidate>
  <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Load Change Application Number <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="load_change_application_number"
             value="<?= htmlspecialchars($client['load_change_application_number'] ?? '') ?>" required>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label">Rooftop Solar Application Number <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="rooftop_solar_application_number"
             value="<?= htmlspecialchars($client['rooftop_solar_application_number'] ?? '') ?>" required>
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=6&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>

<script>
// Client-side validation (keeps UX snappy)
function validateStep7() {
    const loadAppNo = document.querySelector('input[name="load_change_application_number"]').value.trim();
    const solarAppNo = document.querySelector('input[name="rooftop_solar_application_number"]').value.trim();

    if (!loadAppNo) {
        alert('Please enter Load Change Application Number');
        return false;
    }
    if (!solarAppNo) {
        alert('Please enter Rooftop Solar Application Number');
        return false;
    }
    return true;
}
</script>
