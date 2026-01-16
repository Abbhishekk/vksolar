<?php
// admin/workflow_steps/step13.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // provides $conn (mysqli)

// Get client_id
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 13.</div>';
    return;
}

// fetch client data
$stmt = $conn->prepare("SELECT id, name, pm_redeem_status, subsidy_amount, subsidy_redeem_date FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$res = $stmt->get_result();
$client_data = $res->fetch_assoc();
$stmt->close();

if (!$client_data) {
    echo '<div class="alert alert-danger">Client not found. Please select a valid client.</div>';
    return;
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
    <h5 class="card-title">Step 13: PM Suryaghar Redeem Status</h5>
  </div>
  <div class="card-body">
    <form id="step13Form" action="/admin/workflow_steps/save_step13" method="post" novalidate onsubmit="return validateStep13();">
      <input type="hidden" name="client_id" value="<?= htmlspecialchars($client_data['id']) ?>">

      <div class="mb-3">
        <label class="form-label fw-bold">Client</label>
        <div class="user-info p-2"><?= htmlspecialchars($client_data['name']) ?> (ID: <?= htmlspecialchars($client_data['id']) ?>)</div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">PM Suryaghar Subsidy Redeemed? <span class="text-danger">*</span></label>
          <select class="form-select" name="pm_redeem_status" id="pmRedeemStatus" required onchange="toggleSubsidyFields()">
            <option value="">-- Select --</option>
            <option value="no" <?= (($client_data['pm_redeem_status'] ?? '') === 'no') ? 'selected' : '' ?>>No</option>
            <option value="yes" <?= (($client_data['pm_redeem_status'] ?? '') === 'yes') ? 'selected' : '' ?>>Yes</option>
          </select>
        </div>

        <div class="col-md-6 mb-3" id="subsidyAmountField" style="display: none;">
          <label class="form-label">Subsidy Amount (₹) <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="form-control" name="subsidy_amount" id="subsidyAmount"
                 value="<?= isset($client_data['subsidy_amount']) ? htmlspecialchars($client_data['subsidy_amount']) : '' ?>">
        </div>

        <div class="col-md-6 mb-3" id="subsidyDateField" style="display: none;">
          <label class="form-label">Subsidy Redeem Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control" name="subsidy_redeem_date" id="subsidyRedeemDate"
                 value="<?= isset($client_data['subsidy_redeem_date']) ? htmlspecialchars($client_data['subsidy_redeem_date']) : '' ?>">
        </div>
      </div>

      <div class="form-navigation mt-3">
        <div class="row">
          <div class="col-md-6">
            <a href="/admin/workflow.php?step=12&client_id=<?= htmlspecialchars($client_data['id']) ?>" class="btn btn-secondary">← Previous Step</a>
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
function toggleSubsidyFields() {
    const pmRedeemStatus = document.getElementById('pmRedeemStatus').value;
    const subsidyAmountField = document.getElementById('subsidyAmountField');
    const subsidyDateField = document.getElementById('subsidyDateField');
    const amountEl = document.getElementById('subsidyAmount');
    const dateEl = document.getElementById('subsidyRedeemDate');

    if (pmRedeemStatus === 'yes') {
        subsidyAmountField.style.display = 'block';
        subsidyDateField.style.display = 'block';
        if (amountEl) amountEl.required = true;
        if (dateEl) dateEl.required = true;
    } else {
        subsidyAmountField.style.display = 'none';
        subsidyDateField.style.display = 'none';
        if (amountEl) { amountEl.required = false; amountEl.value = ''; }
        if (dateEl) { dateEl.required = false; dateEl.value = ''; }
    }
}

function validateStep13() {
    const pm = document.getElementById('pmRedeemStatus');
    if (!pm || !pm.value) { alert('Please choose PM Suryaghar redeem status'); pm.focus(); return false; }
    if (pm.value === 'yes') {
        const amount = document.getElementById('subsidyAmount');
        const date = document.getElementById('subsidyRedeemDate');
        if (!amount || amount.value === '' || parseFloat(amount.value) <= 0) {
            alert('Please enter valid subsidy amount');
            if (amount) amount.focus();
            return false;
        }
        if (!date || !date.value) {
            alert('Please enter subsidy redeem date');
            if (date) date.focus();
            return false;
        }
    }
    return true;
}

// initialize on load (for direct loads or AJAX loader should call window.initStep13 after injecting)
document.addEventListener('DOMContentLoaded', function(){
    const initEl = document.getElementById('pmRedeemStatus');
    if (initEl && initEl.value === 'yes') toggleSubsidyFields();
});

// expose initializer for AJAX-injected fragment
window.initStep13 = window.initStep13 || function(step, clientId, stepContent) {
    try {
        const root = stepContent || document;
        const sel = root.querySelector('#pmRedeemStatus');
        if (sel && sel.value === 'yes') toggleSubsidyFields();
        const first = root.querySelector('input, select, textarea, button');
        if (first) first.focus();
    } catch (e) { console.error('initStep13 error', e); }
};
</script>
