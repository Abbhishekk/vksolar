<?php
$firstName = $_GET['firstName'] ?? 'Test';
$lastName = $_GET['lastName'] ?? 'User';
$email = $_GET['email'] ?? '';
$phone = $_GET['phone'] ?? '';
$address = $_GET['address'] ?? '';
$propertyType = $_GET['propertyType'] ?? '';
$roofType = $_GET['roofType'] ?? '';
$meterType = $_GET['meterType'] ?? '';
$roofArea = $_GET['roofArea'] ?? '';
$systemSize = floatval($_GET['systemSize'] ?? 0);
$panelCompany = $_GET['panelCompany'] ?? '';
$inverterCompany = $_GET['inverterCompany'] ?? '';
$panelModel = $_GET['panelModel'] ?? '';
$systemType = $_GET['systemType'] ?? '';
$monthlyBill = floatval($_GET['monthlyBill'] ?? 0);
$batteryBackup = ($_GET['batteryBackup'] ?? '0') === '1';
$monitoringSystem = ($_GET['monitoringSystem'] ?? '0') === '1';
$maintenancePackage = ($_GET['maintenancePackage'] ?? '0') === '1';

$customerName = trim($firstName . ' ' . $lastName);
$systemSizeKw = number_format($systemSize, 2);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solar Quotation - <?= $customerName ?></title>
    <style>
        @page { size: A4 landscape; margin: 20px; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .page { width: 100%; page-break-after: always; padding: 20px; }
        .page:last-child { page-break-after: auto; }
        .header { background: #4caf50; color: white; padding: 20px; text-align: center; margin-bottom: 20px; }
        .content { padding: 20px; background: #f9f9f9; border: 1px solid #ddd; }
        .two-column { display: flex; gap: 20px; }
        .column { flex: 1; background: white; padding: 15px; border: 1px solid #ccc; }
        .section-title { color: #2e7d32; font-size: 18px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #4caf50; padding-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #333; }
        .info-value { color: #666; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Print PDF</button>
    </div>

    <!-- Page 1: Cover -->
    <div class="page">
        <div class="header">
            <h1>VK Solar Energy</h1>
            <h2>Solar Quotation for <?= $customerName ?></h2>
        </div>
        <div class="content">
            <div class="section-title">System Overview</div>
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value"><?= $customerName ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">System Size:</span>
                <span class="info-value"><?= $systemSizeKw ?> kW</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generated on:</span>
                <span class="info-value"><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>
    </div>

    <!-- Page 2: Details -->
    <div class="page">
        <div class="header">
            <h2>System Details & Specifications</h2>
        </div>
        <div class="two-column">
            <div class="column">
                <div class="section-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= $customerName ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?= $phone ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= $email ?: 'N/A' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?= $address ?></span>
                </div>
            </div>
            <div class="column">
                <div class="section-title">System Configuration</div>
                <div class="info-row">
                    <span class="info-label">System Size:</span>
                    <span class="info-value"><?= $systemSizeKw ?> kW</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Panel Company:</span>
                    <span class="info-value"><?= ucfirst($panelCompany) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">System Type:</span>
                    <span class="info-value"><?= ucfirst(str_replace('-', ' ', $systemType)) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Property Type:</span>
                    <span class="info-value"><?= ucfirst($propertyType) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Page 3: Bank Details -->
    <div class="page">
        <div class="header">
            <h2>Bank Details & Contact Information</h2>
        </div>
        <div class="two-column">
            <div class="column">
                <div class="section-title">Bank Details</div>
                <div class="info-row">
                    <span class="info-label">Account Name:</span>
                    <span class="info-value">VK SOLAR ENERGY</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Bank:</span>
                    <span class="info-value">HDFC BANK DATAWAIDINGP</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account No:</span>
                    <span class="info-value">50200065621522</span>
                </div>
                <div class="info-row">
                    <span class="info-label">IFSC Code:</span>
                    <span class="info-value">HDFC0004224</span>
                </div>
            </div>
            <div class="column">
                <div class="section-title">Contact Information</div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">9075305275 / 9657135476</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">vksolarenergy1989@gmail.com</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">NEAR DR.A.V.JOSHI CLINIC KHADGAON ROAD KOHALE LAYOUT WADI NAGPUR 440023</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>