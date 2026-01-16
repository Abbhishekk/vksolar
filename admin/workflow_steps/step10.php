<?php
// admin/workflow_steps/step10.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)

// Form => DB document_type mapping (form input name => enum value in client_documents)
$docMap = [
  'aadhar_card'   => 'aadhar',
  'pan_card'      => 'pan_card',
  'electric_bill' => 'electric_bill',
  'bank_passbook' => 'bank_passbook',
  'model_agreement' => 'model_agreement',
  'dcr_certificate' => 'dcr_certificate',
  'bank_statement'  => 'bank_statement',
  'salary_slip'     => 'salary_slip',
  'it_return'       => 'it_return',
  'gumasta'         => 'gumasta',
  'client_signature'=>'client_signature'
];

$requiredDocs = ['aadhar_card','pan_card','electric_bill','bank_passbook','client_signature'];
$optionalDocs = array_diff(array_keys($docMap), $requiredDocs);

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 10.</div>';
    return;
}

// fetch client & existing docs & existing panels
$stmt = $conn->prepare("SELECT id, name, inverter_company_name,inverter_capacity, inverter_serial_number,company_name,wattage, dcr_certificate_number, number_of_panels FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client = $res->fetch_assoc();
$stmt->close();

if (!$client) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
}

// fetch existing documents
$existingDocs = [];
$qr = $conn->prepare("SELECT document_type, file_name, file_path FROM client_documents WHERE client_id = ?");
if ($qr) {
    $qr->bind_param('i', $client_id);
    $qr->execute();
    $r = $qr->get_result();
    while ($rw = $r->fetch_assoc()) $existingDocs[$rw['document_type']] = $rw;
    $qr->close();
}

// fetch existing solar panels into array ordered by panel_number
$existingPanels = [];
$pr = $conn->prepare("SELECT panel_number, company_name, wattage, serial_number FROM solar_panels WHERE client_id = ? ORDER BY panel_number ASC");
if ($pr) {
    $pr->bind_param('i', $client_id);
    $pr->execute();
    $rp = $pr->get_result();
    while ($row = $rp->fetch_assoc()) $existingPanels[intval($row['panel_number'])] = $row['serial_number'];
    $pr->close();
}

// flash messages
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

<!-- Step 10 fragment -->
<form id="step10Form" action="/admin/workflow_steps/save_step10" method="post" enctype="multipart/form-data" onsubmit="return validateStep10()" novalidate>
  <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">
  <!-- hidden CSV backup for compatibility -->
  <?php
    // create a CSV of existing panels for prefill convenience (if any)
    $existing_csv = '';
    if (!empty($existingPanels)) {
        $parts = [];
        foreach ($existingPanels as $num => $sn) $parts[] = $sn;
        $existing_csv = implode(',', $parts);
    }
  ?>
  <input type="hidden" id="existing_panel_serials" value="<?= htmlspecialchars($existing_csv) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-12 mb-4">
      <h6 class="border-bottom pb-2">Required Documents</h6>
    </div>

    <?php foreach ($requiredDocs as $input): 
        $label = ucwords(str_replace(['_','card'],' ', $input)); ?>
      <div class="col-md-6 mb-3">
        <label class="form-label"><?= htmlspecialchars($label) ?> <span class="text-danger">*</span></label>
        <input type="file" class="form-control" name="<?= htmlspecialchars($input) ?>" accept=".pdf,.jpg,.jpeg,.png" <?= !empty($existingDocs[$docMap[$input]]) ? '' : 'required' ?>>
        <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
        <?php if (!empty($existingDocs[$docMap[$input]])): ?>
          <div class="small mt-1">Current: <?= htmlspecialchars($existingDocs[$docMap[$input]]['file_name']) ?> 
            <?php if (!empty($existingDocs[$docMap[$input]]['file_path'])): ?>
              — <a href="<?= htmlspecialchars($existingDocs[$docMap[$input]]['file_path']) ?>" target="_blank">View</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  </div>

  <div class="row mt-4">
    <div class="col-12 mb-3"><h6 class="border-bottom pb-2">Additional Documents (Optional)</h6></div>
    <?php foreach ($optionalDocs as $input):
        $label = ucwords(str_replace('_',' ', $input)); ?>
      <div class="col-md-6 mb-3">
        <label class="form-label"><?= htmlspecialchars($label) ?></label>
        <input type="file" class="form-control" name="<?= htmlspecialchars($input) ?>" accept=".pdf,.jpg,.jpeg,.png">
        <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
        <?php if (!empty($existingDocs[$docMap[$input]])): ?>
          <div class="small mt-1">Current: <?= htmlspecialchars($existingDocs[$docMap[$input]]['file_name']) ?> 
            <?php if (!empty($existingDocs[$docMap[$input]]['file_path'])): ?>
              — <a href="<?= htmlspecialchars($existingDocs[$docMap[$input]]['file_path']) ?>" target="_blank">View</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row mt-4">
    <div class="col-12 mb-3"><h6 class="border-bottom pb-2">System Details <span class="text-danger">*</span></h6></div>

    <div class="col-md-4 mb-3">
      <label class="form-label">Inverter Manufacturing Company <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="inverter_company_name" value="<?= htmlspecialchars($client['inverter_company_name'] ?? '') ?>" required>
    </div>

    <div class="col-md-4 mb-3">
      <label class="form-label">Inverter Serial Number <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="inverter_serial_number" value="<?= htmlspecialchars($client['inverter_serial_number'] ?? '') ?>" required>
    </div>
    
    <div class="col-md-4 mb-3">
      <label class="form-label">Inverter Capacity <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="inverter_capacity" value="<?= htmlspecialchars($client['inverter_capacity'] ?? '') ?>" required>
    </div>

    <div class="col-md-4 mb-3">
      <label class="form-label">DCR Certificate Number <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="dcr_certificate_number" value="<?= htmlspecialchars($client['dcr_certificate_number'] ?? '') ?>" required>
    </div>
    
    <div class="col-md-4 mb-3">
      <label class="form-label">Panel module company name <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="panel_company_name" value="<?= htmlspecialchars($client['company_name'] ?? '') ?>" required>
    </div>
    
    <div class="col-md-4 mb-3">
      <label class="form-label">Panel Wattage <span class="text-danger">*</span></label>
      <input type="text" class="form-control" name="Wattage" value="<?= htmlspecialchars($client['wattage'] ?? '') ?>" required>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label">Number of Solar Panels <span class="text-danger">*</span></label>
      <input type="number" class="form-control" name="number_of_panels" id="numberOfPanels" value="<?= htmlspecialchars($client['number_of_panels'] ?? '') ?>" min="1" max="50" required>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-12 mb-3">
      <button type="button" id="generatePanelBtn" class="btn btn-outline-primary" onclick="window.generatePanelFields && window.generatePanelFields()">
        <i class="bi bi-plus-circle"></i> Generate Panel Serial Number Fields
      </button>
    </div>

    <div id="panelSerialNumbers" class="col-12">
      <!-- dynamic fields will be generated here by global generatePanelFields() -->
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=9&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>

<script>
// Provide global helpers so main loader can call window.initStep10() after the fragment is injected.
// This increases the chance the functions are available even if scripts are evaluated differently.

window.generatePanelFields = window.generatePanelFields || function() {
  try {
    const numEl = document.getElementById('numberOfPanels');
    const container = document.getElementById('panelSerialNumbers');
    if (!numEl || !container) return;
    const numberOfPanels = parseInt(numEl.value, 10) || 0;
    if (numberOfPanels <= 0 || numberOfPanels > 50) {
      alert('Please enter a valid number of panels (1-50)');
      return;
    }

    let html = '<div class="row"><div class="col-12"><h6>Panel Serial Numbers <span class="text-danger">*</span></h6></div></div>';
    for (let i = 1; i <= numberOfPanels; i++) {
      html += `
        <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label">Panel ${i} Serial Number</label>
            <input type="text" class="form-control" name="panel_serial_${i}" placeholder="Enter serial number for panel ${i}" required>
          </div>
        </div>
      `;
    }
    container.innerHTML = html;

    // prefill from hidden CSV (#existing_panel_serials)
    const existingCsvEl = document.getElementById('existing_panel_serials');
    if (existingCsvEl && existingCsvEl.value.trim() !== '') {
      const parts = existingCsvEl.value.split(',').map(s => s.trim());
      for (let i = 0; i < parts.length && i < numberOfPanels; i++) {
        const f = container.querySelector('[name="panel_serial_' + (i+1) + '"]');
        if (f) f.value = parts[i];
      }
    }
  } catch (e) {
    console.error('generatePanelFields error', e);
  }
};

window.initStep10 = window.initStep10 || function(step, clientId, stepContent) {
  try {
    // Validate file sizes inside fragment
    const root = stepContent || document;
    root.querySelectorAll('input[type="file"]').forEach(input => {
      input.addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (file && file.size > 5 * 1024 * 1024) {
          alert('File exceeds 5MB. Please choose a smaller file.');
          this.value = '';
        }
      });
    });

    // If number already set, auto-generate fields
    const numEl = root.querySelector('#numberOfPanels');
    if (numEl && parseInt(numEl.value, 10) > 0) {
      setTimeout(() => { if (typeof window.generatePanelFields === 'function') window.generatePanelFields(); }, 20);
    }
  } catch (err) {
    console.error('initStep10 error', err);
  }
};

// Basic client-side validation (fallback)
function validateStep10() {
  const requiredFiles = ['aadhar_card','pan_card','electric_bill','bank_passbook'];
  for (const f of requiredFiles) {
    const el = document.querySelector(`[name="${f}"]`);
    if (el && (!el.files || el.files.length === 0)) {
      // allow if file already exists on server (we don't know here), rely on server-side validation
      continue;
    }
  }
  return true;
}
</script>
