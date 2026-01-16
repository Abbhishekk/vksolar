<?php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
require_once '../vendor/autoload.php'; // adjust path to your dompdf autoload.php

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Access denied');
}

$data = $_POST;
$date = date('d M Y');

$html = "
<html>
<head>
<style>
body { font-family: DejaVu Sans, sans-serif; margin: 30px; }
h1 { color: #2e8b57; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 10px; }
.total { font-weight: bold; color: #2e8b57; margin-top: 20px; }
</style>
</head>
<body>
<h1>VK Solar Quotation</h1>
<p><b>Date:</b> $date</p>
<h3>Customer: {$data['firstName']} {$data['lastName']}</h3>
<p><b>Phone:</b> {$data['phone']}<br><b>Email:</b> {$data['email']}<br><b>Address:</b> {$data['address']}</p>
<table>
<tr><th>System Size</th><td>{$data['systemSize']} kW</td></tr>
<tr><th>Panel Company</th><td>{$data['panelCompany']}</td></tr>
<tr><th>Inverter Company</th><td>{$data['inverterCompany']}</td></tr>
<tr><th>Panel Model</th><td>{$data['panelModel']} Wp</td></tr>
<tr><th>System Type</th><td>{$data['systemType']}</td></tr>
<tr><th>Meter Type</th><td>{$data['meterType']}</td></tr>
</table>
<p class='total'>Quotation Generated Automatically</p>
</body></html>
";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("quotation_{$data['firstName']}_{$date}.pdf", ["Attachment" => true]);
exit;
