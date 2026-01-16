<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../include/covertNumberToWords.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$client_id = (int)($_POST['client_id'] ?? 0);
if (!$client_id) die('Invalid Client');

$payment_mode = $_POST['payment_mode'] ?? '';
$receipt_date = $_POST['receipt_date'] ?? date('Y-m-d');
$remarks = $_POST['remarks'] ?? '';

/* ================= FETCH CLIENT ================= */
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$client) die('Client not found');

/* ================= FETCH INVOICES ================= */
$stmt = $conn->prepare("
    SELECT id, invoice_no, invoice_date, total 
    FROM invoices 
    WHERE reference_id = ? AND status != 'cancelled'
    ORDER BY invoice_date DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_amount = 0;
foreach ($invoices as $inv) {
    $total_amount += $inv['total'];
}

function money($v){ return number_format($v,2); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Tax Invoice</title>

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
<!-- <body> -->
<div class="invoice">

<!-- HEADER -->
<div class="row  p5">
  <div class="col">
    <div class="bold" style="font-size:25px;" >VK Solar Energy</div>
    SHAHU LAYOUT NEAR JOSHI HOSPITAL, KHADGAON ROAD WADI <br>
    WADI<br>
    NAGPUR, Maharashtra - 440023
  </div>
  <div class="col right">
    <div> <span  class="bold">Name:</span> HARISH KADU</div>
    <span class="bold" >Phone:</span> 9075305275/9657135476<br>
    <span class="bold" >Email:</span> vksolarenergy1989@gmail.com
  </div>
</div>

<div class="center border p5" style="display:flex;align-items:center;justify-content:space-between;" >
    <div style="color: #00000095" class="bold" >
        <span style="color:#000" class="bold" >GSTIN: </span>27CJXPK1402Q1ZK
    </div>
    <div class="bold" style="font-size:20px;color:#0000005c" >
        Receipt Voucher
    </div>
    <div class="small bold" >
        ORIGINAL FOR RECIPIENT
    </div>
</div>

<!-- CUSTOMER + INVOICE META -->
<div class="row">
  <div class="col border ">
    <div class="bold border p5 center">Client Details</div>
    <div class="p5" >
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header" >Name: </span>
            <span class="right-header" ><?= htmlspecialchars($client['name']) ?></span>
        </div>
        <div style="display:flex;justify-items:space-between;width:100%;margin:5px 0;" >
            <span class="bold left-header">Address: </span> <p class="right-header" ><?= htmlspecialchars($client['village'] . ', ' . $client['taluka'] . ', ' . $client['district']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Phone: </span><p class="right-header" ><?= htmlspecialchars($client['mobile']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Place of Supply: </span><p class="right-header" >Maharashtra (27)</p>
        </div>
    </div>
  </div>

  <div class="col border p5"  >
    <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Receipt No: </span><p class="right-header" >RV<?= date('Ymd') . $client_id ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Receipt Date: </span><p class="right-header" ><?= date('d-m-Y', strtotime($receipt_date)) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Payment Mode: </span><p class="right-header" ><?= htmlspecialchars($payment_mode) ?></p>
        </div>
    
  </div>
</div>

<!-- ITEMS -->
<table style="min-height:30vh;">
<thead>
<tr>
<th rowspan=2 >Sr</th>
<th rowspan=2 >Particulars</th>
<th rowspan=2 >Amount</th>
</tr>

</thead>
<tbody>
    <tr>
        <td style="border-bottom: 0;">1</td>
        <td style="border-bottom: 0;" class="bold">Account: <br> <span style="font-size: x-large;" >  <?= htmlspecialchars($client['name']) ?></span></td>
        <td style="border-bottom: 0;"></td>
    </tr>
<?php
$sr = 2;
foreach($invoices as $inv):
?>
    <tr style="border: 0 !important;" >
        <td style="border-bottom: 0;border-top: 0 !important;" ></td>
        <td style="border-bottom: 0 !important;border-top: 0 !important;">Invoice No: <?= htmlspecialchars($inv['invoice_no']) ?> (<?= date('d-m-Y', strtotime($inv['invoice_date'])) ?>)</td>
        <td style="border-bottom: 0 !important;border-top: 0 !important;" class="right">₹<?= money($inv['total']) ?></td>
    </tr>
<?php endforeach; ?>
<?php if ($remarks): ?>
    <tr style="border: 0 !important;">
        <td style="border-top: 0;" ></td>
        <td colspan="1" style="border-top: 0 !important;"><strong>Remarks:</strong> <?= htmlspecialchars($remarks) ?></td>
        <td style="border-top:0 !important" ></td>
    </tr>
<?php endif; ?>
<tr style="height:10px" >
    <td colspan=2 class="right bold">Total</td>
    <td class="right bold">₹<?= money($total_amount) ?></td>
</tr>
</tbody>
</table>

<!-- TOTALS -->
<table>
<tr>
<td colspan=2 ></td>
</tr>
<tr>
    <td class="center" style="width:60%" >
        <p class="bold" >Total in words</p>
    </td>
    <td style="width:40%" >
        <div style="display:flex; justify-content:space-between;width:100%" >
            <p class="bold m-0" > Total Amount: </p>
            <p class="m-0" > ₹<?= money($total_amount) ?> </p>
        </div>
    </td>
</tr>
<tr>
    <td rowspan=2 class="center" style="vertical-align: middle" >
        <span style="font-size:15px;text-transform:uppercase;vertical-align: middle" > <?= convertNumber($total_amount) ?> </span>
    </td>
    <td style="display:flex;justify-content:space-between;" >
        <small class="bold" >Certified that the particulars given above are true and correct.</small>
        
    </td>
</tr>
<tr>
    <td style="display:flex;justify-content:space-between;" >
       
    </td>
</tr>

<tr>
    <td rowspan=5 > 
       
    </td>
     <td style="display:flex;justify-content:space-between;" >
       
    </td>
</tr>
<tr>
    <td class="right" >
        <span class="bold " >(E & O.E.)</span>
    </td>
</tr>
<tr>
    <td  class="center" rowspan=3 >
        <p class="bold" >For Vk Solar Energy</p>
    </td>
</tr>

</table>
<table>
    <tbody>
        <tr>
            <td style="width:60%"  > <span class="center bold"></span> 
               
        </td>
            <td style="width:40%" >
                <pre>

                </pre>
            </td>
        </tr>
        <tr>
            <td rowspan=3 >
                
            </td>
            <td style="width:40%" class="bold center" >
                Authorised Signature
            </td>
        </tr>
       
    </tbody>
</table>


</div>
</body>
</html>
