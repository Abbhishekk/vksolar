<?php
// admin/workflow_steps/step9.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

/**
 * Step 9: Fitting Photos
 * Uses client_documents table for storing filenames.
 *
 * Expects client_id ?client_id=NN in query string (or history state) and shows a friendly
 * warning if not present.
 */

// document types used in this step
$docTypes = [
    'solar_panel_photo' => 'Solar Panel Structure Photo',
    'inverter_photo'    => 'Inverter Photo',
    'geotag_photo'      => 'Geotag Photo'
];

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 9.</div>';
    return;
}

// fetch client basic info
$stmt = $conn->prepare("SELECT id, name FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client = $res->fetch_assoc();
$stmt->close();

if (!$client) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
}

// fetch all existing documents for this client and map by document_type
$existing = [];
$qr = $conn->prepare("SELECT document_type, file_name, file_path FROM client_documents WHERE client_id = ?");
if ($qr) {
    $qr->bind_param('i', $client_id);
    $qr->execute();
    $r = $qr->get_result();
    while ($row = $r->fetch_assoc()) {
        $existing[$row['document_type']] = $row; // includes file_name, file_path
    }
    $qr->close();
}

// flash messages (same style as other steps)
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

<!-- Step 9: Fitting Photos (fragment) -->
<form id="step9Form" action="/admin/workflow_steps/save_step9" method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-12">
      <p class="text-muted small">Upload fitting photos (optional). Allowed types: jpg, png, webp. Max 5MB each.</p>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label"><?= htmlspecialchars($docTypes['solar_panel_photo']) ?></label>
      <input type="file" class="form-control" name="solar_panel_photo" accept="image/*">
      <?php if (!empty($existing['solar_panel_photo'])): ?>
        <small class="text-muted">Current: <?= htmlspecialchars($existing['solar_panel_photo']['file_name']) ?></small><br>
        <?php if (!empty($existing['solar_panel_photo']['file_path'])): ?>
            <a href="<?= htmlspecialchars($existing['solar_panel_photo']['file_path']) ?>" target="_blank" class="small">View</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label"><?= htmlspecialchars($docTypes['inverter_photo']) ?></label>
      <input type="file" class="form-control" name="inverter_photo" accept="image/*">
      <?php if (!empty($existing['inverter_photo'])): ?>
        <small class="text-muted">Current: <?= htmlspecialchars($existing['inverter_photo']['file_name']) ?></small><br>
        <?php if (!empty($existing['inverter_photo']['file_path'])): ?>
            <a href="<?= htmlspecialchars($existing['inverter_photo']['file_path']) ?>" target="_blank" class="small">View</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label"><?= htmlspecialchars($docTypes['geotag_photo']) ?></label>
      <input type="file" class="form-control" name="geotag_photo" accept="image/*">
      <?php if (!empty($existing['geotag_photo'])): ?>
        <small class="text-muted">Current: <?= htmlspecialchars($existing['geotag_photo']['file_name']) ?></small><br>
        <?php if (!empty($existing['geotag_photo']['file_path'])): ?>
            <a href="<?= htmlspecialchars($existing['geotag_photo']['file_path']) ?>" target="_blank" class="small">View</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=8&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>
