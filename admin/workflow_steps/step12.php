<?php
// admin/workflow_steps/step12.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

// get client_id
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 12.</div>';
    return;
}

// fetch client data
$stmt = $conn->prepare("SELECT id, name, meter_number, meter_installation_date FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client = $res->fetch_assoc();
$stmt->close();

if (!$client) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
}

// fetch existing meter photo (client_documents)
$existingPhoto = null;
$qr = $conn->prepare("SELECT file_name, file_path FROM client_documents WHERE client_id = ? AND document_type = 'meter_photo' LIMIT 1");
if ($qr) {
    $qr->bind_param('i', $client_id);
    $qr->execute();
    $r = $qr->get_result();
    if ($r && $r->num_rows) {
        $rw = $r->fetch_assoc();
        $existingPhoto = $rw;
    }
    $qr->close();
}

// flash messages
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
    <h5 class="card-title">Step 12: Meter Installation Photo</h5>
  </div>
  <div class="card-body">
    <form id="step12Form" action="/admin/workflow_steps/save_step12" method="post" enctype="multipart/form-data" onsubmit="return validateStep12()" novalidate>
      <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">

      <div class="mb-3">
        <label class="form-label fw-bold">Client</label>
        <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Meter Installation Photo</label>
          <input type="file" class="form-control" name="meter_photo" accept="image/*">
          <small class="text-muted">JPG, PNG (Max 5MB). Optional.</small>

          <?php if ($existingPhoto): ?>
            <div class="small mt-1">Current: <?= htmlspecialchars($existingPhoto['file_name']) ?> — <a href="<?= htmlspecialchars($existingPhoto['file_path'] ?? '#') ?>" target="_blank">View</a></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Meter Number</label>
          <input type="text" class="form-control" name="meter_number" value="<?= htmlspecialchars($client['meter_number'] ?? '') ?>" placeholder="Enter meter number">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Installation Date</label>
          <input type="date" class="form-control" name="meter_installation_date" value="<?= htmlspecialchars($client['meter_installation_date'] ?? '') ?>">
        </div>
      </div>

      <div class="form-navigation mt-3">
        <div class="row">
          <div class="col-md-6">
            <a href="/admin/workflow.php?step=11&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
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
function validateStep12() {
  // meter photo optional. meter_number optional. installation date optional but if provided must be valid date.
  const dateEl = document.querySelector('input[name="meter_installation_date"]');
  if (dateEl && dateEl.value) {
    // basic HTML date input yields YYYY-MM-DD - leave browser validation for format, but extra check:
    const d = new Date(dateEl.value);
    if (isNaN(d.getTime())) {
      alert('Invalid installation date.');
      dateEl.focus();
      return false;
    }
  }
  // file size guard handled by initStep12
  return true;
}

// initializer for AJAX-injected fragments
window.initStep12 = window.initStep12 || function(step, clientId, stepContent) {
  try {
    const formRoot = stepContent || document;
    // attach file size guard for meter photo
    const f = formRoot.querySelector('input[name="meter_photo"]');
    if (f) {
      f.addEventListener('change', function(){
        if (!this.files || !this.files[0]) return;
        if (this.files[0].size > 5 * 1024 * 1024) {
          alert('File exceeds 5MB limit. Please choose a smaller file.');
          this.value = '';
        }
      });
    }

    // focus first input for accessibility
    const first = formRoot.querySelector('input, select, textarea, button');
    if (first) first.focus();
  } catch (e) {
    console.error('initStep12 error', e);
  }
};

// If fragment loaded directly (not via AJAX loader), init now
try {
  if (!window.__workflow_frag_injected_by_ajax) window.initStep12(12, <?= intval($client_id) ?>, document.getElementById('step12Form'));
} catch(e){}
</script>
