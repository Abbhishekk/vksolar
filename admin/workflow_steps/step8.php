<?php
// admin/workflow_steps/step8.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php'; // $conn (mysqli)

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if (!$client_id) {
    echo '<div class="alert alert-warning">Please select or create a client from the left panel before filling Step 8.</div>';
    return;
}

// fetch current values
$sql = "SELECT id, name, bank_loan_status, bank_name, account_number, ifsc_code, jan_samartha_application_no, loan_amount, first_installment_amount, second_installment_amount, remaining_amount
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

<!-- Step 8: Bank Loan Details (matches earlier UI) -->
<form id="step8Form" action="/admin/workflow_steps/save_step8" method="post" onsubmit="return validateStep8()" novalidate>
  <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['id']) ?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Client</label>
    <div class="user-info p-2"><?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Bank Loan Required? <span class="text-danger">*</span></label>
      <select class="form-select" name="bank_loan_status" id="bankLoanStatus" required
              onchange="window.toggleBankLoanFields && window.toggleBankLoanFields(this.value)">
          <option value="">-- Select --</option>
          <option value="no" <?= ($client['bank_loan_status'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
          <option value="yes" <?= ($client['bank_loan_status'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
      </select>
    </div>
  </div>

  <div id="bankLoanFields" style="display: <?= ($client['bank_loan_status'] ?? '') === 'yes' ? 'block' : 'none' ?>;">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="bank_name" value="<?= htmlspecialchars($client['bank_name'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Account Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="account_number" value="<?= htmlspecialchars($client['account_number'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="ifsc_code" value="<?= htmlspecialchars($client['ifsc_code'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Jan Samartha Application No</label>
        <input type="text" class="form-control" name="jan_samartha_application_no" value="<?= htmlspecialchars($client['jan_samartha_application_no'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Loan Amount (₹) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control" name="loan_amount" value="<?= htmlspecialchars($client['loan_amount'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">First Installment Amount (₹)</label>
        <input type="number" step="0.01" class="form-control" name="first_installment_amount" value="<?= htmlspecialchars($client['first_installment_amount'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Second Installment Amount (₹)</label>
        <input type="number" step="0.01" class="form-control" name="second_installment_amount" value="<?= htmlspecialchars($client['second_installment_amount'] ?? '') ?>">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Remaining Amount (₹)</label>
        <input type="number" step="0.01" class="form-control" name="remaining_amount" value="<?= htmlspecialchars($client['remaining_amount'] ?? '') ?>">
      </div>
    </div>
  </div>

  <div class="form-navigation mt-3">
    <div class="row">
      <div class="col-md-6">
        <a href="/admin/workflow.php?step=7&client_id=<?= htmlspecialchars($client['id']) ?>" class="btn btn-secondary">← Previous Step</a>
      </div>
      <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary">Save & Continue →</button>
      </div>
    </div>
  </div>
</form>

<script>
// Initialize fragment: if global toggler exists, call it
(function(){
  try {
    var sel = document.getElementById('bankLoanStatus');
    if (sel && window.toggleBankLoanFields) window.toggleBankLoanFields(sel.value);
  } catch(e){ console.error(e); }
})();
</script>
