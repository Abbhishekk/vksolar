<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/../include/covertNumberToWords.php';
$auth->requireAuth();
$auth->requirePermission('invoice_management', 'view');

$title = 'generate_receipt_voucher';

// Fetch all warehouses
$warehouses = $conn->query("SELECT id, name FROM warehouses ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch warehouse invoices if warehouse selected
$warehouse_id = (int)($_GET['warehouse_id'] ?? 0);
$invoices = [];
$warehouse = null;
$total_amount = 0;

if ($warehouse_id) {
    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id = ?");
    $stmt->bind_param("i", $warehouse_id);
    $stmt->execute();
    $warehouse = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($warehouse) {
        $stmt = $conn->prepare("
            SELECT id, invoice_no, invoice_date, total 
            FROM invoices 
            WHERE warehouse_id = ? AND status != 'cancelled'
            ORDER BY invoice_date DESC
        ");
        $stmt->bind_param("i", $warehouse_id);
        $stmt->execute();
        $invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        foreach ($invoices as $inv) {
            $total_amount += $inv['total'];
        }
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
    </style>
</head>
<body>

<?php $cwd = getcwd(); chdir(__DIR__ . '/..');  include 'include/sidebar.php'; chdir($cwd); ?>
<div id="main-content">
<?php $cwd = getcwd(); chdir(__DIR__ . '/..');  include 'include/navbar.php'; chdir($cwd); ?>

<main class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Generate Receipt Voucher</h4>
            </div>
            <div class="card-body">
                <form method="GET" id="warehouseForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Warehouse</label>
                            <select name="warehouse_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Select Warehouse --</option>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>" <?= $wh['id'] == $warehouse_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($wh['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>

                <?php if ($warehouse): ?>
                <form id="receiptForm" method="POST" action="print_reciept_voucher" target="_blank">
                    <input type="hidden" name="warehouse_id" value="<?= $warehouse_id ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">-- Select Mode --</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Online Transfer">Online Transfer</option>
                                <option value="UPI">UPI</option>
                                <option value="NEFT/RTGS">NEFT/RTGS</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receipt Date</label>
                            <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter payment remarks..."></textarea>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Invoices for <?= htmlspecialchars($warehouse['name']) ?></h5>
                        </div>
                        <div class="card-body invoice-list">
                            <?php if (empty($invoices)): ?>
                            <p class="text-muted">No invoices found for this warehouse.</p>
                            <?php else: ?>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Date</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inv['invoice_no']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($inv['invoice_date'])) ?></td>
                                        <td class="text-end">₹<?= number_format($inv['total'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary fw-bold">
                                        <td colspan="2" class="text-end">Total Amount:</td>
                                        <td class="text-end">₹<?= number_format($total_amount, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($invoices)): ?>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="showPreview()">Preview Receipt</button>
                        <button type="submit" class="btn btn-success">Print Receipt</button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Reset</a>
                    </div>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Preview Section -->
        <?php if ($warehouse && !empty($invoices)): ?>
        <div class="mt-4" id="previewSection" style="display:none;">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Receipt Preview</h5>
                    <button class="btn btn-light btn-sm" onclick="document.getElementById('previewSection').style.display='none'">Close Preview</button>
                </div>
                <div class="card-body">
                    <div class="invoice-preview">
                        <!-- HEADER -->
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

                        <!-- WAREHOUSE + RECEIPT META -->
                        <div class="row">
                            <div class="col border">
                                <div class="bold border p5 center">Warehouse Details</div>
                                <div class="p5">
                                    <div style="display:flex;margin:5px 0;">
                                        <span class="bold left-header">Name: </span>
                                        <span class="right-header"><?= htmlspecialchars($warehouse['name']) ?></span>
                                    </div>
                                    <div style="display:flex;margin:5px 0;">
                                        <span class="bold left-header">Address: </span>
                                        <p class="right-header"><?= htmlspecialchars($warehouse['address'] ?? 'N/A') ?></p>
                                    </div>
                                    <div style="display:flex;margin:5px 0;">
                                        <span class="bold left-header">Phone: </span>
                                        <p class="right-header"><?= htmlspecialchars($warehouse['phone'] ?? 'N/A') ?></p>
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
                                    <p class="right-header">RV<?= date('Ymd') . $warehouse_id ?></p>
                                </div>
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Receipt Date: </span>
                                    <p class="right-header" id="preview_date"></p>
                                </div>
                                <div style="display:flex;margin:5px 0;">
                                    <span class="bold left-header">Payment Mode: </span>
                                    <p class="right-header" id="preview_mode"></p>
                                </div>
                            </div>
                        </div>

                        <!-- ITEMS -->
                        <table style="min-height:200px;">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Particulars</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border-bottom: 0;">1</td>
                                    <td style="border-bottom: 0;" class="bold">Account: <br><span style="font-size: x-large;"><?= htmlspecialchars($warehouse['name']) ?></span></td>
                                    <td style="border-bottom: 0;"></td>
                                </tr>
                                <?php foreach($invoices as $inv): ?>
                                <tr>
                                    <td style="border-bottom: 0;border-top: 0;"></td>
                                    <td style="border-bottom: 0;border-top: 0;">Invoice No: <?= htmlspecialchars($inv['invoice_no']) ?> (<?= date('d-m-Y', strtotime($inv['invoice_date'])) ?>)</td>
                                    <td style="border-bottom: 0;border-top: 0;" class="right">₹<?= money($inv['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr id="preview_remarks_row" style="display:none;">
                                    <td style="border-top: 0;"></td>
                                    <td style="border-top: 0;"><strong>Remarks:</strong> <span id="preview_remarks"></span></td>
                                    <td style="border-top: 0;"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="right bold">Total</td>
                                    <td class="right bold">₹<?= money($total_amount) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- TOTALS -->
                        <table>
                            <tr>
                                <td class="center" style="width:60%">
                                    <p class="bold">Total in words</p>
                                </td>
                                <td style="width:40%">
                                    <div style="display:flex; justify-content:space-between;">
                                        <p class="bold m-0">Total Amount: </p>
                                        <p class="m-0">₹<?= money($total_amount) ?></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="center" style="vertical-align: middle">
                                    <span style="font-size:15px;text-transform:uppercase;"><?= convertNumber($total_amount) ?></span>
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
    </div>
</main>
</div>

    <script>
        function showPreview() {
            const form = document.getElementById('receiptForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const date = document.querySelector('[name="receipt_date"]').value;
            const mode = document.querySelector('[name="payment_mode"]').value;
            const remarks = document.querySelector('[name="remarks"]').value;
            
            document.getElementById('preview_date').textContent = new Date(date).toLocaleDateString('en-GB');
            document.getElementById('preview_mode').textContent = mode;
            
            if (remarks) {
                document.getElementById('preview_remarks').textContent = remarks;
                document.getElementById('preview_remarks_row').style.display = '';
            } else {
                document.getElementById('preview_remarks_row').style.display = 'none';
            }
            
            document.getElementById('previewSection').style.display = 'block';
            document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>