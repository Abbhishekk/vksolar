<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../include/covertNumberToWords.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');
$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid Invoice');
$bank_id = (int)($_GET['bank_id'] ?? 0);

/* ================= FETCH INVOICE ================= */
$stmt = $conn->prepare("
    SELECT i.*, c.name, c.mobile, c.village, c.taluka, c.district, c.pincode, c.bank_name, c.account_number, c.ifsc_code
    FROM invoices i
    JOIN clients c ON c.id = i.reference_id
    WHERE i.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ================= ITEMS ================= */
$stmt = $conn->prepare("
    SELECT ii.*, p.name AS product_name, p.hsn_code, p.description
    FROM invoice_items ii
    JOIN products p ON p.id = ii.product_id
    WHERE ii.invoice_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bank = null;

if ($bank_id) {
    $stmt = $conn->prepare("
        SELECT bank_name, branch_name, account_number, ifsc_code
        FROM company_bank_details
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $bank_id);
    $stmt->execute();
    $bank = $stmt->get_result()->fetch_assoc();

}

$stmt->close();

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
        TAX INVOICE
    </div>
    <div class="small bold" >
        ORIGINAL FOR RECIPIENT
    </div>
</div>

<!-- CUSTOMER + INVOICE META -->
<div class="row">
  <div class="col border ">
    <div class="bold border p5 center">Customer Details</div>
    <div class="p5" >
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header" >Name: </span>
            <span class="right-header" ><?= htmlspecialchars($invoice['name']) ?></span>
        </div>
        <div style="display:flex;justify-items:space-between;width:100%;margin:5px 0;" >
            <span class="bold left-header">Address: </span> <p class="right-header" ><?= htmlspecialchars($invoice['village'].', '.$invoice['taluka'].', '.$invoice['district']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Phone: </span><p class="right-header" ><?= htmlspecialchars($invoice['mobile']) ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Place of Supply: </span><p class="right-header" >Maharashtra (27)</p>
        </div>
    </div>
  </div>

  <div class="col border p5"  >
    <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Invoice No: </span><p class="right-header" ><?= $invoice['invoice_no'] ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Invoice Date: </span><p class="right-header" ><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></p>
        </div>
    
  </div>
</div>

<!-- ITEMS -->
<table style="min-height:30vh;">
<thead>
<tr>
<th rowspan=2 >Sr</th>
<th rowspan=2 >Name of Product / Services</th>
<th rowspan=2 >HSN / SAC</th>
<th rowspan=2 >Qty</th>
<th rowspan=2 >Rate</th>
<th rowspan=2 >Taxable</th>
<th colspan=2>CGST</th>
<th colspan=2>SGST</th>
<th rowspan=2 >Total</th>
</tr>
<tr>
<th>%</th>
<th>Amount</th>
<th>%</th>
<th>Amount</th>
</tr>
</thead>
<tbody>
<?php
$sr=1;
$total_quantity=0;
$total_rate=0;
$total_cgst=0;
$total_sgst=0;
$total=0;
$bank_name    = $bank['bank_name'] ?? '—';
$bank_branch = $bank['branch_name'] ?? '—';
$acc_name    = 'VK SOLAR ENERGY';
$acc_number  = $bank['account_number'] ?? '—';
$bank_ifsc   = $bank['ifsc_code'] ?? '—';

foreach($items as $it):
$taxable = $it['quantity']*$it['rate'];
$cgst = $taxable * ($it['gst_percent']/2)/100;
$sgst = $cgst;
$total_quantity += $it['quantity'];
$total_rate += $it['rate'];
$total_cgst += $cgst;
$total_sgst += $sgst;
$total += $taxable+$cgst+$sgst;
?>
<tr >
<td><?= $sr++ ?></td>
<td>
   <p style="margin:0 0 1em 0"><?= htmlspecialchars($it['product_name']) ?></p>
   <i class="small" ><?= htmlspecialchars($it['description']) ?></i> 
</td>
<td><?= $it['hsn_code'] ?></td>
<td class="right"><?= $it['quantity'] ?></td>
<td class="right"><?= money($it['rate']) ?></td>
<td class="right"><?= money($taxable) ?></td>
<td class="right" ><?= $it["gst_percent"] ?></td>
<td class="right"><?= money($cgst) ?></td>
<td class="right" ><?= $it["gst_percent"] ?></td>


<td class="right"><?= money($sgst) ?></td>
<td class="right"><?= money($taxable+$cgst+$sgst) ?></td>
</tr>
<?php endforeach;
$total_tax = $total_cgst + $total_sgst;
?>
<tr style="height:10px" >
    <td colspan=2 class="right bold">Total</td>
    <td></td>
    <td class="right bold"><?= $total_quantity ?></td>
    <td></td>
    <td class="right bold"><?= money($total_rate) ?></td>
    <td></td>
    <td class="right bold"><?= money($total_cgst) ?></td>
    <td></td>
    <td class="right bold"><?= money($total_sgst) ?></td>
    <td class="right bold"><?= money($total) ?></td>
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
            <p class="bold m-0" > Taxable Amount: </p>
            <p class="m-0" > <?= $total_rate ?> </p>
        </div>
    </td>
</tr>
<tr>
    <td rowspan=2 class="center" style="vertical-align: middle" >
        <span style="font-size:15px;text-transform:uppercase;vertical-align: middle" > <?= convertNumber($total) ?> </span>
    </td>
    <td style="display:flex;justify-content:space-between;" >
        <p class="bold m-0" >CGST: </p>
        <p class="m-0" > <?= money($total_cgst) ?> </p>
    </td>
</tr>
<tr>
    <td style="display:flex;justify-content:space-between;" >
        <p class="bold m-0" >SGST: </p>
        <p class="m-0" > <?= money($total_sgst) ?> </p>
    </td>
</tr>
<tr>
    <td>
        <p class="bold m-0 center" > Bank Details </p>
    </td>
    <td style="display:flex;justify-content:space-between;" >
        <p class="bold m-0" >Total Tax: </p>
        <p class="m-0" > <?= money($total_tax) ?> </p>
    </td>
</tr>
<tr>
    <td rowspan=5 > 
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Name </span><p class="right-header" ><?= $bank_name ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Branch </span><p class="right-header" ><?= $bank_branch ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Acc. Name </span><p class="right-header" ><?= $acc_name ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">Acc. Number </span><p class="right-header" ><?= $acc_number ?></p>
        </div>
        <div style="display:flex;justify-items:space-between;margin:5px 0;">
            <span class="bold left-header">IFSC </span><p class="right-header" ><?= $bank_ifsc ?></p>
        </div>
    </td>
     <td style="display:flex;justify-content:space-between;" >
        <p class="bold m-0" >Total Amount After Tax </p>
        <p class="m-0 bold" style="font-size: 15px;"  > ₹<?= money($total) ?> </p>
    </td>
</tr>
<tr>
    <td class="right" >
        <span class="bold " >(E & O.E.)</span>
    </td>
</tr>
<tr>
    <td  class="center" rowspan=3 >
        <small class="bold" >Certified that the particulars given above are true and correct.</small>
        <p class="bold" >For Vk Solar Energy</p>
    </td>
</tr>

</table>
<table>
    <tbody>
        <tr>
            <td style="width:60%"  > <span class="center bold">Terms and Conditions</span> 
            <div>
                Subject to our home Jurisdiction.
                <ol>
                    <li> Our Responsibility Ceases as soon as goods leaves our premises. </li>
                    <li> Goods once sold will not be taken back. </li>
                    <li> As We Are Supplier, Not Manufacture, All Products Warranty Will Be Provided By Manufacture. </li>
                    <li> PAYMENT 100 & Advance Before Material Dispatch </li>
                </ol>
            </div>        
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
