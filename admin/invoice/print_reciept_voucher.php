<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../include/covertNumberToWords.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$client_id = (int)($_POST['client_id'] ?? 0);
if (!$client_id) die('Invalid Client');

$receipt_date = $_POST['receipt_date'] ?? date('Y-m-d');
$general_remarks = $_POST['general_remarks'] ?? '';

// Get selected existing payments
$selected_payments = $_POST['selected_payments'] ?? [];

// Get new payment entries
$new_amounts = $_POST['new_amounts'] ?? [];
$new_payment_modes = $_POST['new_payment_modes'] ?? [];
$new_payment_dates = $_POST['new_payment_dates'] ?? [];
$new_remarks = $_POST['new_remarks'] ?? [];

/* ================= FETCH CLIENT ================= */
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$client) die('Client not found');

/* ================= FETCH SELECTED EXISTING PAYMENTS ================= */
$existing_payments = [];
if (!empty($selected_payments)) {
    $placeholders = str_repeat('?,', count($selected_payments) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT id, amount, payment_mode, payment_date, remarks
        FROM advance_payments 
        WHERE id IN ($placeholders) AND client_id = ?
        ORDER BY payment_date DESC
    ");
    $types = str_repeat('i', count($selected_payments)) . 'i';
    $stmt->bind_param($types, ...array_merge($selected_payments, [$client_id]));
    $stmt->execute();
    $existing_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/* ================= PROCESS NEW PAYMENTS ================= */
$new_payments = [];
for ($i = 0; $i < count($new_amounts); $i++) {
    if (!empty($new_amounts[$i]) && !empty($new_payment_modes[$i]) && !empty($new_payment_dates[$i])) {
        // Generate receipt number
        $receipt_number = 'RV' . date('Ymd') . str_pad($client_id, 3, '0', STR_PAD_LEFT) . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        
        // Insert new payment
        $stmt = $conn->prepare("
            INSERT INTO advance_payments (client_id, amount, payment_mode, payment_date, remarks, receipt_number, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $remarks_value = $new_remarks[$i] ?? '';
        $user_id = $_SESSION['user_id'] ?? 1;
        $stmt->bind_param("idssssi", $client_id, $new_amounts[$i], $new_payment_modes[$i], $new_payment_dates[$i], $remarks_value, $receipt_number, $user_id);
        $stmt->execute();
        $new_payment_id = $conn->insert_id;
        $stmt->close();
        
        $new_payments[] = [
            'id' => $new_payment_id,
            'amount' => $new_amounts[$i],
            'payment_mode' => $new_payment_modes[$i],
            'payment_date' => $new_payment_dates[$i],
            'remarks' => $remarks_value
        ];
    }
}

// Combine all payments
$all_payments = array_merge($existing_payments, $new_payments);
$total_amount = 0;
foreach ($all_payments as $payment) {
    $total_amount += $payment['amount'];
}

if (empty($all_payments)) die('No payments selected or added');

function money($v){ return number_format($v,2); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt Voucher</title>

<style>
@page {
    size: A4;
    margin: 10mm;
}

html, body {
    width: 210mm;
    height: 297mm;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11.5px;
    color:#000;
}

.invoice {
    width: 100%;
    min-height: 277mm;
    border: 1px solid #000;
    padding: 6mm;
    box-sizing: border-box;
}

.row { display:flex; }
.col { flex:1; line-height:1.5; }

.border { border:1px solid #000; }
.p5 { padding:5px; }

.center { text-align:center; }
.right { text-align:right;margin-left:auto }
.bold { font-weight:bold; }
.small { font-size:11px; }

table {
    width:100%;
    border-collapse:collapse;
}

table th, table td {
    border:1px solid #000;
    padding:4px;
    vertical-align:top;
}

.left-header{
    display: inline-block;
    width: 30%;
}
.right-header{
    display: inline-block;
    width: 70%;
    margin:0;
}
.m-0{
    margin: 0;
}
</style>

</head>

<body onload="window.print()">
<div class="invoice">

<!-- HEADER -->
<div class="row p5">
  <div class="col">
    <div class="bold" style="font-size:25px;">VK Solar Energy</div>
    SHAHU LAYOUT NEAR JOSHI HOSPITAL, KHADGAON ROAD WADI <br>
    WADI<br>
    NAGPUR, Maharashtra - 440023
  </div>
  <div class="col right">
    <div> <span class="bold">Name:</span> HARISH KADU</div>
    <span class="bold">Phone:</span> 9075305275/9657135476<br>
    <span class="bold">Email:</span> vksolarenergy1989@gmail.com
  </div>
</div>

<div class="center border p5" style="display:flex;align-items:center;justify-content:space-between;">
    <div style="color: #00000095" class="bold">
        <span style="color:#000" class="bold">GSTIN: </span>27CJXPK1402Q1ZK
    </div>
    <div class="bold" style="font-size:20px;color:#0000005c">
        Receipt Voucher
    </div>
    <div class="small bold">
        ORIGINAL FOR RECIPIENT
    </div>
</div>

<!-- CLIENT + RECEIPT META -->
<div class="row">
  <div class="col border">
    <div class="bold border p5 center">Client Details</div>
    <div class="p5">
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Name: </span>
            <span class="right-header"><?= htmlspecialchars($client['name']) ?></span>
        </div>
        <div style="display:flex;justify-items:space-between;width:100%;margin:5px 0;">
            <span class="bold left-header">Address: </span> 
            <p class="right-header"><?= htmlspecialchars($client['village'] . ', ' . $client['taluka'] . ', ' . $client['district']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Phone: </span>
            <p class="right-header"><?= htmlspecialchars($client['mobile']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Place of Supply: </span>
            <p class="right-header">Maharashtra (27)</p>
        </div>
    </div>
  </div>

  <div class="col border p5">
    <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Receipt No: </span>
            <p class="right-header">RV<?= date('Ymd') . $client_id ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Receipt Date: </span>
            <p class="right-header"><?= date('d-m-Y', strtotime($receipt_date)) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Payment Type: </span>
            <p class="right-header">Advance Payment</p>
        </div>
  </div>
</div>

<!-- ITEMS -->
<table style="min-height:30vh;">
<thead>
<tr>
<th rowspan=2>Sr</th>
<th rowspan=2>Particulars</th>
<th rowspan=2>Amount</th>
</tr>
</thead>
<tbody>
    <tr>
        <td style="border-bottom: 0;">1</td>
        <td style="border-bottom: 0;" class="bold">Account: <br> <span style="font-size: x-large;"><?= htmlspecialchars($client['name']) ?></span></td>
        <td style="border-bottom: 0;"></td>
    </tr>
<?php
$sr = 2;
foreach($all_payments as $payment):
?>
    <tr style="border: 0 !important;">
        <td style="border-bottom: 0;border-top: 0 !important;"></td>
        <td style="border-bottom: 0 !important;border-top: 0 !important;">
            Advance Payment - <?= htmlspecialchars($payment['payment_mode']) ?> 
            (<?= date('d-m-Y', strtotime($payment['payment_date'])) ?>)
            <?php if (!empty($payment['remarks'])): ?>
                <br><small><?= htmlspecialchars($payment['remarks']) ?></small>
            <?php endif; ?>
        </td>
        <td style="border-bottom: 0 !important;border-top: 0 !important;" class="right">₹<?= money($payment['amount']) ?></td>
    </tr>
<?php endforeach; ?>
<?php if ($general_remarks): ?>
    <tr style="border: 0 !important;">
        <td style="border-top: 0;"></td>
        <td colspan="1" style="border-top: 0 !important;"><strong>General Remarks:</strong> <?= htmlspecialchars($general_remarks) ?></td>
        <td style="border-top:0 !important"></td>
    </tr>
<?php endif; ?>
<tr style="height:10px">
    <td colspan=2 class="right bold">Total</td>
    <td class="right bold">₹<?= money($total_amount) ?></td>
</tr>
</tbody>
</table>

<!-- TOTALS -->
<table>
<tr>
<td colspan=2></td>
</tr>
<tr>
    <td class="center" style="width:60%">
        <p class="bold">Total in words</p>
    </td>
    <td style="width:40%">
        <div style="display:flex; justify-content:space-between;width:100%">
            <p class="bold m-0">Total Amount: </p>
            <p class="m-0">₹<?= money($total_amount) ?></p>
        </div>
    </td>
</tr>
<tr>
    <td rowspan=2 class="center" style="vertical-align: middle">
        <span style="font-size:15px;text-transform:uppercase;vertical-align: middle"><?= convertNumber($total_amount) ?></span>
    </td>
    <td style="display:flex;justify-content:space-between;">
        <small class="bold">Certified that the particulars given above are true and correct.</small>
    </td>
</tr>
<tr>
    <td style="display:flex;justify-content:space-between;">
    </td>
</tr>

<tr>
    <td rowspan=5></td>
     <td style="display:flex;justify-content:space-between;">
    </td>
</tr>
<tr>
    <td class="right">
        <span class="bold">(E & O.E.)</span>
    </td>
</tr>
<tr>
    <td class="center" rowspan=3>
        <p class="bold">For Vk Solar Energy</p>
    </td>
</tr>

</table>
<table>
    <tbody>
        <tr>
            <td style="width:60%"><span class="center bold"></span></td>
            <td style="width:40%">
                <pre>

                </pre>
            </td>
        </tr>
        <tr>
            <td rowspan=3></td>
            <td style="width:40%" class="bold center">
                Authorised Signature
            </td>
        </tr>
    </tbody>
</table>

</div>
</body>
</html>