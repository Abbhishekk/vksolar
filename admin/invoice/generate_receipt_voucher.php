<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/../include/covertNumberToWords.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$title = 'generate_receipt_voucher';

// Fetch all clients
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch client advance payments if client selected
$client_id = (int)($_GET['client_id'] ?? 0);
$advance_payments = [];
$client = null;

if ($client_id) {
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($client) {
        $stmt = $conn->prepare("
            SELECT id, amount, payment_mode, payment_date, remarks, receipt_number
            FROM advance_payments 
            WHERE client_id = ?
            ORDER BY payment_date DESC
        ");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $advance_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

function money($v){ return number_format($v,2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Receipt Voucher</title>
    <?php require_once __DIR__.'/../include/head2.php'; ?>
    <style>
        .invoice-preview { max-width: 210mm; margin: 20px auto; border: 1px solid #ddd; padding: 20px; background: white; }
        .invoice-preview .row { display: flex; }
        .invoice-preview .col { flex: 1; line-height: 1.5; }
        .invoice-preview .border { border: 1px solid #000; }
        .invoice-preview .p5 { padding: 5px; }
        .invoice-preview .center { text-align: center; }
        .invoice-preview .right { text-align: right; margin-left: auto; }
        .invoice-preview .bold { font-weight: bold; }
        .invoice-preview table { width: 100%; border-collapse: collapse; }
        .invoice-preview table th, .invoice-preview table td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        .invoice-preview .left-header { display: inline-block; width: 30%; }
        .invoice-preview .right-header { display: inline-block; width: 70%; margin: 0; }
        .invoice-preview .m-0 { margin: 0; }
        .payment-entry { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .new-payment { background-color: #f8f9fa; }
    </style>
</head>
<body>

<?php $cwd = getcwd(); chdir(__DIR__ . '/..');  include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..');  include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Generate Receipt Voucher - Advance Payments</h4>
        </div>
        <div class="card-body">
            <form method="GET" id="clientForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Client</label>
                        <select name="client_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Select Client --</option>
                            <?php foreach ($clients as $cl): ?>
                            <option value="<?= $cl['id'] ?>" <?= $cl['id'] == $client_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if ($client): ?>
            <form id="receiptForm" method="POST" action="print_reciept_voucher" target="_blank">
                <input type="hidden" name="client_id" value="<?= $client_id ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Receipt Date</label>
                        <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">General Remarks</label>
                        <textarea name="general_remarks" class="form-control" rows="2" placeholder="General remarks for receipt..."></textarea>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Advance Payments for <?= htmlspecialchars($client['name']) ?></h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="addNewPayment()">+ Add New Payment</button>
                    </div>
                    <div class="card-body">
                        <div id="paymentsContainer">
                            <?php if (!empty($advance_payments)): ?>
                                <h6>Existing Advance Payments:</h6>
                                <?php foreach ($advance_payments as $payment): ?>
                                <div class="payment-entry">
                                    <div class="form-check">
                                        <input type="checkbox" name="selected_payments[]" value="<?= $payment['id'] ?>" class="form-check-input payment-checkbox" onchange="calculateTotal()">
                                        <label class="form-check-label">
                                            <strong>₹<?= money($payment['amount']) ?></strong> - <?= $payment['payment_mode'] ?> 
                                            (<?= date('d-m-Y', strtotime($payment['payment_date'])) ?>)
                                            <?php if ($payment['remarks']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($payment['remarks']) ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <strong>Selected Total: ₹<span id="selectedTotal">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="showPreview()" id="previewBtn" disabled>Preview Receipt</button>
                    <button type="submit" class="btn btn-success" id="printBtn" disabled>Print Receipt</button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Preview Section -->
    <?php if ($client): ?>
    <div class="mt-4" id="previewSection" style="display:none;">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Receipt Preview</h5>
                <button class="btn btn-light btn-sm" onclick="document.getElementById('previewSection').style.display='none'">Close Preview</button>
            </div>
            <div class="card-body">
                <div class="invoice-preview">
                    <div class="row p5">
                        <div class="col">
                            <div class="bold" style="font-size:25px;">VK Solar Energy</div>
                            SHAHU LAYOUT NEAR JOSHI HOSPITAL, KHADGAON ROAD WADI <br>
                            WADI<br>
                            NAGPUR, Maharashtra - 440023
                        </div>
                        <div class="col right">
                            <div><span class="bold">Name:</span> HARISH KADU</div>
                            <span class="bold">Phone:</span> 9075305275/9657135476<br>
                            <span class="bold">Email:</span> vksolarenergy1989@gmail.com
                        </div>
                    </div>

                    <div class="center border p5" style="display:flex;align-items:center;justify-content:space-between;">
                        <div style="color: #00000095" class="bold">
                            <span style="color:#000" class="bold">GSTIN: </span>27CJXPK1402Q1ZK
                        </div>
                        <div class="bold" style="font-size:20px;color:#0000005c">Receipt Voucher</div>
                        <div style="font-size:11px" class="bold">ORIGINAL FOR RECIPIENT</div>
                    </div>

                    <div class="row">
                        <div class="col border">
                            <div class="bold border p5 center">Client Details</div>
                            <div class="p5">
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Name: </span>
                                    <span class="right-header"><?= htmlspecialchars($client['name']) ?></span>
                                </div>
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Address: </span>
                                    <p class="right-header"><?= htmlspecialchars($client['village'] . ', ' . $client['taluka'] . ', ' . $client['district']) ?></p>
                                </div>
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Phone: </span>
                                    <p class="right-header"><?= htmlspecialchars($client['mobile']) ?></p>
                                </div>
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Place of Supply: </span>
                                    <p class="right-header">Maharashtra (27)</p>
                                </div>
                            </div>
                        </div>

                        <div class="col border p5">
                            <div style="display:flex;margin:5px 0;">
                                <span class="bold left-header">Receipt No: </span>
                                <p class="right-header">RV<?= date('Ymd') . $client_id ?></p>
                            </div>
                            <div style="display:flex;margin:5px 0;">
                                <span class="bold left-header">Receipt Date: </span>
                                <p class="right-header" id="preview_date"></p>
                            </div>
                            <div style="display:flex;margin:5px 0;">
                                <span class="bold left-header">Payment Type: </span>
                                <p class="right-header">Advance Payment</p>
                            </div>
                        </div>
                    </div>

                    <table style="min-height:200px;">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Particulars</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <tr>
                                <td style="border-bottom: 0;">1</td>
                                <td style="border-bottom: 0;" class="bold">Account: <br><span style="font-size: x-large;"><?= htmlspecialchars($client['name']) ?></span></td>
                                <td style="border-bottom: 0;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <table>
                        <tr>
                            <td class="center" style="width:60%">
                                <p class="bold">Total in words</p>
                            </td>
                            <td style="width:40%">
                                <div style="display:flex; justify-content:space-between;">
                                    <p class="bold m-0">Total Amount: </p>
                                    <p class="m-0" id="preview_total">₹0.00</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="center" style="vertical-align: middle">
                                <span style="font-size:15px;text-transform:uppercase;" id="preview_words"></span>
                            </td>
                            <td>
                                <small class="bold">Certified that the particulars given above are true and correct.</small>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="right"><span class="bold">(E & O.E.)</span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="center"><p class="bold">For Vk Solar Energy</p></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="bold center">Authorised Signature</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>
</div>

<script>
let paymentCounter = 0;

function addNewPayment() {
    paymentCounter++;
    const container = document.getElementById('paymentsContainer');
    const newPayment = document.createElement('div');
    newPayment.className = 'payment-entry new-payment';
    newPayment.innerHTML = `
        <h6>New Payment Entry #${paymentCounter}</h6>
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="new_amounts[]" class="form-control new-payment-input" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Mode</label>
                <select name="new_payment_modes[]" class="form-select new-payment-input" required>
                    <option value="">Select Mode</option>
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Online Transfer">Online Transfer</option>
                    <option value="UPI">UPI</option>
                    <option value="NEFT/RTGS">NEFT/RTGS</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="new_payment_dates[]" class="form-control new-payment-input" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <button type="button" class="btn btn-danger form-control" onclick="removePayment(this)">Remove</button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <label class="form-label">Remarks</label>
                <textarea name="new_remarks[]" class="form-control" rows="2" placeholder="Payment remarks..."></textarea>
            </div>
        </div>
    `;
    container.appendChild(newPayment);
    calculateTotal();
}

function removePayment(button) {
    button.closest('.payment-entry').remove();
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    
    // Existing payments
    document.querySelectorAll('.payment-checkbox:checked').forEach(cb => {
        const label = cb.nextElementSibling.textContent;
        const amount = parseFloat(label.match(/₹([\d,]+\.?\d*)/)[1].replace(/,/g, ''));
        total += amount;
    });
    
    // New payments
    document.querySelectorAll('input[name="new_amounts[]"]').forEach(input => {
        if (input.value) {
            total += parseFloat(input.value);
        }
    });
    
    document.getElementById('selectedTotal').textContent = total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    const hasSelection = total > 0;
    document.getElementById('previewBtn').disabled = !hasSelection;
    document.getElementById('printBtn').disabled = !hasSelection;
}

function showPreview() {
    const total = parseFloat(document.getElementById('selectedTotal').textContent.replace(/,/g, ''));
    if (total <= 0) {
        alert('Please select or add at least one payment');
        return;
    }
    
    const form = document.getElementById('receiptForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const date = document.querySelector('[name="receipt_date"]').value;
    document.getElementById('preview_date').textContent = new Date(date).toLocaleDateString('en-GB');
    document.getElementById('preview_total').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    // Convert number to words (you'll need to implement this or use existing function)
    document.getElementById('preview_words').textContent = 'Rupees ' + total.toFixed(2) + ' Only';
    
    document.getElementById('previewSection').style.display = 'block';
    document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
}

// Add event listeners for new payment inputs
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('new-payment-input')) {
        calculateTotal();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('payment-checkbox')) {
        calculateTotal();
    }
});
</script>
</body>
</html>