<?php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Access denied');
}

$data = $_POST;
$date = date('d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quotation - <?= htmlspecialchars($data['firstName'] . ' ' . $data['lastName']) ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; }
.header { text-align: center; border-bottom: 2px solid #2e8b57; margin-bottom: 30px; }
h1 { color: #2e8b57; }
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
.total { font-weight: bold; color: #2e8b57; }
</style>
</head>
<body>
<div class="header">
  <h1>VK Solar Quotation</h1>
  <p>Date: <?= $date ?></p>
</div>

<h3>Customer Details</h3>
<p><strong><?= htmlspecialchars($data['firstName'] . ' ' . $data['lastName']) ?></strong><br>
Phone: <?= htmlspecialchars($data['phone']) ?><br>
Email: <?= htmlspecialchars($data['email']) ?><br>
Address: <?= nl2br(htmlspecialchars($data['address'])) ?></p>

<h3>System Details</h3>
<table class="table">
<tr><th>System Size</th><td><?= $data['systemSize'] ?> kW</td></tr>
<tr><th>Panel Company</th><td><?= ucfirst($data['panelCompany']) ?></td></tr>
<tr><th>Inverter Company</th><td><?= ucfirst($data['inverterCompany']) ?></td></tr>
<tr><th>Panel Model</th><td><?= $data['panelModel'] ?> Wp</td></tr>
<tr><th>System Type</th><td><?= ucfirst($data['systemType']) ?></td></tr>
<tr><th>Meter Type</th><td><?= ucfirst($data['meterType']) ?></td></tr>
</table>

<p class="total">Quotation Amount and subsidy details will appear dynamically based on calculation logic.</p>
</body>
</html>
