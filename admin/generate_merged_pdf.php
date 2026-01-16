<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'vendor/autoload.php';
} catch (Exception $e) {
    die('Error loading vendor autoload: ' . $e->getMessage());
}

use Dompdf\Dompdf;
use Dompdf\Options;

// Debug: Log the request method and data
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - GET data: " . print_r($_GET, true) . "\n", FILE_APPEND);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get form data from query parameters
    $firstName = $_GET['firstName'] ?? '';
    $lastName = $_GET['lastName'] ?? '';
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

    // Calculate costs
    $panelPrices = [
        'kirloskar' => 45000, 'waree' => 42000, 'adani' => 48000, 
        'premium' => 52000, 'satvik' => 40000, 'others' => 38000
    ];
    
    $inverterPrices = [
        'kirloskar' => 12000, 'polycab' => 11000, 'v_sole' => 10000,
        'utl' => 11500, 'growat' => 13000, 'k_solar' => 10500, 'others' => 9500
    ];
    
    $systemTypeMultipliers = [
        'on-grid' => 1.0, 'off-grid' => 1.4, 'hybrid' => 1.25
    ];
    
    $panelCost = $systemSize * $panelPrices[$panelCompany];
    $inverterCost = $systemSize * $inverterPrices[$inverterCompany];
    $systemCost = ($panelCost + $inverterCost) * $systemTypeMultipliers[$systemType];
    
    $additionalCost = 0;
    if ($batteryBackup) $additionalCost += $systemSize * 20000;
    if ($monitoringSystem) $additionalCost += 15000;
    if ($maintenancePackage) $additionalCost += 5000;
    
    $totalCost = $systemCost + $additionalCost;
    $subsidy = $systemSize <= 3 ? $totalCost * 0.4 : $totalCost * 0.2;
    $finalCost = $totalCost - $subsidy;
    $monthlySavings = $monthlyBill * 0.8;
    $yearlySavings = $monthlySavings * 12;
    $paybackPeriod = $finalCost / $yearlySavings;

    // Calculate number of panels
    $panelWattage = 545; // Standard panel wattage
    $numberOfPanels = ceil(($systemSize * 1000) / $panelWattage);
    
    // Generate HTML content
    $html = generateMergedHTML($firstName, $lastName, $email, $phone, $address, $propertyType, $roofType, $meterType, $roofArea, $systemSize, $panelCompany, $inverterCompany, $panelModel, $systemType, $monthlyBill, $batteryBackup, $monitoringSystem, $maintenancePackage, $totalCost, $subsidy, $finalCost, $monthlySavings, $yearlySavings, $paybackPeriod, $numberOfPanels);

    // Configure Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Output PDF
    $filename = 'Solar_Quotation_' . $firstName . '_' . $lastName . '_' . date('Y-m-d') . '.pdf';
    $dompdf->stream($filename, array('Attachment' => true));
} else {
    http_response_code(405);
    echo 'Method not allowed';
}

function generateMergedHTML($firstName, $lastName, $email, $phone, $address, $propertyType, $roofType, $meterType, $roofArea, $systemSize, $panelCompany, $inverterCompany, $panelModel, $systemType, $monthlyBill, $batteryBackup, $monitoringSystem, $maintenancePackage, $totalCost, $subsidy, $finalCost, $monthlySavings, $yearlySavings, $paybackPeriod, $numberOfPanels) {
    
    $customerName = trim($firstName . ' ' . $lastName);
    $systemSizeKw = number_format($systemSize, 2);
    $totalCostFormatted = '₹' . number_format($totalCost, 2);
    $subsidyFormatted = '₹' . number_format($subsidy, 2);
    $finalCostFormatted = '₹' . number_format($finalCost, 2);
    $monthlySavingsFormatted = '₹' . number_format($monthlySavings, 2);
    $yearlySavingsFormatted = '₹' . number_format($yearlySavings, 2);
    $paybackPeriodFormatted = number_format($paybackPeriod, 1) . ' years';
    
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar Quotation - ' . $customerName . '</title>
    <style>
    /* about-section styles */
    /* Print as landscape */
    @page { size: A4 landscape; margin: 20px; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
    section { page-break-after: always; }
    section:last-child { page-break-after: auto; }

    /* Canvas wrapper sized like a slide */
    #about-section .slide {
      width: 100%; /* landscape width */
      height: 100vh; /* landscape height */
      /* margin: 20px auto; */
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      display:flex;
      background: #f7efe3; /* warm cream */
      overflow:hidden;
    }

    #about-section .left {
      flex: 0 0 63%; /* similar to provided image */
      padding: 48px 54px;
      box-sizing: border-box;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }

    #about-section .right {
      flex: 0 0 37%;
      padding: 18px;
      box-sizing:border-box;
      background: transparent;
      display:flex;
      align-items:stretch;
    }

    /* Image panel with thick orange frame */
    #about-section .image-wrap{
      flex:1;
      padding:14px; /* outer orange frame thickness */
      background: #f07d00; /* orange frame */
      display:flex;
      align-items:center;
      justify-content:center;
    }
    #about-section .image-wrap .photo{
      width:100%;
      height:100%;
      background: #ddd no-repeat center/cover;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      border: 12px solid #fff; /* white inner gap to mimic the original */
      box-sizing:border-box;
    }

    #about-section h1.title{
      margin:0 0 18px 0;
      font-size:42px;
      font-weight:700;
      color:#111;
    }
    #about-section h1.title .accent{ color:#f07d00; }

    #about-section .bullets{
      color:#333;
      font-size:18px;
      line-height:1.6;
    }
    #about-section .bullets li{ margin:14px 0; }
    #about-section .bullets li strong{ color:#f07d00; }

    /* Stat boxes */
    #about-section .stats{
      display:flex;
      gap:24px;
      margin-top:28px;
    }
    #about-section .stat{
      background: linear-gradient(180deg,#ff853e 0%, #f0447a 100%);
      flex:1 1 0;
      padding:18px 20px;
      border-radius:12px;
      box-shadow: 0 10px 20px rgba(240,68,122,0.18);
      color:#fff;
      text-align:center;
      min-width:150px;
    }
    #about-section .stat .num{ font-size:28px; font-weight:800; margin-bottom:6px; }
    #about-section .stat .sub{ font-size:14px; opacity:0.95; }

    /* Footer small url */
    #about-section .footer-url{ font-size:14px; color:#6b6b6b; margin-top:18px; }

    /* Make slide responsive for preview on smaller screens */
    @media (max-width:1400px){
      #about-section .slide{ transform:scale(0.9); transform-origin:top center }
    }
    @media (max-width:980px){
      #about-section .slide{ width:100%; height:auto; flex-direction:column }
      #about-section .left{flex:1}
      #about-section .right{flex:1}
      #about-section .image-wrap{padding:10px}
      #about-section .image-wrap .photo{border-width:8px}
      #about-section .stats{flex-direction:row}
    }

    /* bank-details styles */

     #bank-details * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        #bank-details body {
            background-color: #f5f9f5;
            color: #333;
            line-height: 1.4;
            padding: 10px;
        }
        
        #bank-details .container {
            width: 100%;
            height: 100vh;
            margin: 0;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        #bank-details header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4caf50;
        }
        
        #bank-details .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 5px;
        }
        
        #bank-details .tagline {
            font-size: 14px;
            color: #4caf50;
            margin-bottom: 10px;
        }
        
        #bank-details .contact-info {
            font-size: 12px;
            color: #666;
        }
        
        #bank-details .page-title {
            text-align: center;
            margin: 10px 0 15px;
            color: #2e7d32;
            font-size: 24px;
            font-weight: 600;
            padding: 10px;
            background-color: #f1f8e9;
            border-radius: 4px;
        }
        
        #bank-details .content {
            display: flex;
            gap: 30px;
            flex: 1;
            align-items: flex-start;
        }
        
        #bank-details .left-column,
        #bank-details .right-column {
            flex: 1;
            height: 100%;
        }
        
        #bank-details .section {
            margin-bottom: 20px;
        }
        
        #bank-details .section-title {
            color: #2e7d32;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        #bank-details .bank-details {
            background: #f8fdf8;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #4caf50;
        }
        
        #bank-details .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        #bank-details .detail-label {
            font-weight: 600;
            width: 120px;
            color: #2e7d32;
        }
        
        #bank-details .detail-value {
            flex: 1;
        }
        
        #bank-details .scope-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        #bank-details .scope-list li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            font-size: 13px;
        }
        
        #bank-details .scope-list li:before {
            content: "•";
            color: #4caf50;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
       #bank-details  .exclusion-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        #bank-details .exclusion-list li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            font-size: 13px;
        }
        
        #bank-details .exclusion-list li:before {
            content: "✕";
            color: #f44336;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        #bank-details .signature-area {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        #bank-details .signature-box {
            width: 45%;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 12px;
            text-align: center;
        }
        
        #bank-details .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        
        /* Print Styles for landscape */
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
            
            #bank-details .container {
                box-shadow: none;
                width: 100%;
                height: 100%;
                padding: 0;
                page-break-inside: avoid;
            }
            
            #bank-details .page-title {
                background-color: #f1f8e9 !important;
                page-break-after: avoid;
                font-size: 22px;
            }
            
            #bank-details .bank-details {
                background: #f8fdf8 !important;
            }
            
            #bank-details .content {
                page-break-inside: avoid;
            }
        }
        
        

        /* consumer-details styles */
         #consumer-details * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        #consumer-details body {
            background-color: #f5f9f5;
            color: #333;
            line-height: 1.6;
        }
        
        #consumer-details .container {
            width: 100%;
            height: 100vh;
            margin: 0;
            background: white;
            padding: 15px 30px;
            position: relative;
            display: flex;
            flex-direction: column;
          
        }
        
       #consumer-details  header {
            background: linear-gradient(to right, #2e7d32, #4caf50);
            color: white;
            padding: 25px 0;
            text-align: center;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        #consumer-details .logo {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        #consumer-details .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        #consumer-details .content {
            display: flex;
            gap: 20px;
            flex: 1;
            align-items: flex-start;
            
        }
        
        #consumer-details .column {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            
        }
        
        #consumer-details .column-header {
            background: #4caf50;
            color: white;
            padding: 10px 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        #consumer-details .vendor-details,  #consumer-details .consumer-details {
            padding: 10px;
        }
        
        #consumer-details .vendor-info,  #consumer-details .consumer-info {
            margin-bottom: 15px;
        }
        
        #consumer-details .vendor-info h3,  #consumer-details .consumer-info h3 {
            color: #2e7d32;
            margin-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 2px;
            font-size: 1rem;
        }
        
        #consumer-details .vendor-info p,  #consumer-details .consumer-info p {
            margin-bottom: 5px;
            display: flex;
            align-items: flex-start;
            font-size: 0.85rem;
        }
        
        #consumer-details .vendor-info i,  #consumer-details .consumer-info i {
            color: #4caf50;
            margin-right: 10px;
            margin-top: 3px;
            min-width: 20px;
        }
        
        #consumer-details .services {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        #consumer-details .service-tag {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        #consumer-details .branch-offices {
            margin-top: 20px;
        }
        
        #consumer-details .branch-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        #consumer-details .branch {
            background: #f1f8e9;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        
        #consumer-details .contact-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        #consumer-details .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            width: 48%;
        }
        
        #consumer-details .contact-icon {
            background: #4caf50;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        #consumer-details .consumer-card {
            background: #f8fdf8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        #consumer-details .consumer-card h4 {
            color: #2e7d32;
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        #consumer-details .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        #consumer-details .status-approved {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        #consumer-details .status-pending {
            background: #fff8e1;
            color: #ff8f00;
        }
        
        #consumer-details .status-completed {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        #consumer-details footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            #consumer-details .content {
                flex-direction: column;
            }
            
            #consumer-details .contact-item {
                width: 100%;
            }
        }
        
        /* Print Styles for landscape */
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
            
            #consumer-details .container {
                box-shadow: none;
                width: 100%;
                height: 100%;
                padding: 0;
                page-break-inside: avoid;
                background: white !important;
            }
            
            #consumer-details .column-header {
                background: #4caf50 !important;
            }
            
            #consumer-details .service-tag {
                background: #e8f5e9 !important;
            }
            
            #consumer-details .branch {
                background: #f1f8e9 !important;
            }
            
            #consumer-details .consumer-card {
                background: #f8fdf8 !important;
            }
            
            #consumer-details .content {
                page-break-inside: avoid;
                display: flex !important;
                flex-direction: row !important;
                gap: 20px;
            }
            
            #consumer-details .column {
                box-shadow: none;
                flex: 1 !important;
                width: 48% !important;
            }
        }

        /* description styles */
         #description * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
        }
        
       #description body {
            background-color: #f5f9f5;
            color: #333;
            line-height: 1.4;
            padding: 0;
            margin: 0;
        }
        
       #description {
            width: 100%;
            height: 100vh;
            background: white;
            padding: 20px 40px;
            position: relative;
            overflow: hidden;
        }
        
       #description .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4caf50;
        }
        
       #description .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 5px;
        }
        
       #description .tagline {
            font-size: 14px;
            color: #4caf50;
            margin-bottom: 10px;
        }
        
       #description .contact-info {
            font-size: 12px;
            color: #666;
        }
        
       #description .page-title {
            text-align: center;
            margin: 8px 0;
            color: #2e7d32;
            font-size: 18px;
            font-weight: 600;
            padding: 6px;
            background-color: #f1f8e9;
            border-radius: 4px;
        }
        
       #description .materials-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11px;
        }
        
      #description  .materials-table thead {
            background-color: #4caf50;
            color: white;
        }
        
      #description  .materials-table th {
            padding: 6px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        
       #description .materials-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        
       #description .materials-table tbody tr:nth-child(even) {
            background-color: #f8fdf8;
        }
        
       #description .specifications {
            margin-top: 12px;
            margin-bottom: 8px;
        }
        
      #description  .specifications h2 {
            color: #2e7d32;
            margin-bottom: 6px;
            font-size: 15px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 3px;
        }
        
       #description .spec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
       #description .spec-item {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
       #description .spec-item strong {
            color: #2e7d32;
            display: block;
            margin-bottom: 1px;
        }
        
      #description  .summary-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }
        
       #description .summary-item {
            text-align: center;
            flex: 1;
        }
        
       #description .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #4caf50;
        }
        
       #description .summary-label {
            font-size: 12px;
            color: #666;
        }
        
       #description .footer {
            position: absolute;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        
       #description .brands {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        
       #description .brand-tag {
            background: #f1f8e9;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            color: #2e7d32;
        }
        
        /* Print page break styles */
        @media print {
           body {
                background: white !important;
                padding: 0;
            }
            
           #description {
                box-shadow: none;
                width: 100%;
                background: white !important;
            }
            
            #description .page-title {
                background-color: #f1f8e9 !important;
            }
            
            #description .materials-table thead {
                background-color: #4caf50 !important;
            }
            
            #description .materials-table tbody tr:nth-child(even) {
                background-color: #f8fdf8 !important;
            }
            

        }
        
        /* Layout sections vertically */
        section {
            display: block;
            width: 100%;
            margin-bottom: 40px;
        }
        
        @media print {
            
            
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            section {
                margin: 0 !important;
                width: 100vw !important;
            }
            
            #cover-page {
                padding: 20px !important;
            }
            
            #consumer-details .container,
            #bank-details .container {
                padding: 10px !important;
                margin: 0 !important;
            }
            
            #description {
                padding: 10px !important;
            }
        }

        /* cover-page styles */
        #cover-page {
            width: 100%;
            height: 100vh;
            background: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        #cover-page .cover-title {
            font-size: 48px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 20px;
        }
        
        #cover-page .cover-subtitle {
            font-size: 24px;
            color: #4caf50;
            margin-bottom: 40px;
        }
        
        #cover-page .cover-content {
            max-width: 600px;
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }
        

        
        /* Download button styles */
        .download-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4caf50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .download-btn:hover {
            background: #45a049;
        }
        
        @media print {
            .download-btn {
                display: none;
            }
        }
  </style>
</head>
<body>
    <section id="cover-page">
        <img src="./image.png" class="" style="border-radius: 5rem;width: 100%;" alt="">
    </section>
    
    <section id="about-section">
        <div class="slide">
            <div class="left">
                <div>
                    <h1 class="title">About <span class="accent">VK Solar Energy</span></h1>
                    <ul class="bullets">
                        <li><strong>VK Solar Energy</strong> is a leading solar energy solutions provider, committed to delivering high-quality solar installations for residential and commercial properties.</li>
                        <li><strong>Authorized Channel Partner</strong> of KIRLOSKAR SOLAR TECHNOLOGY PVT.LTD., ensuring premium quality products and services.</li>
                        <li>Registered & Approved by MSEDCL, MSME with GSTIN: 27CJXPK1402Q1ZK</li>
                        <li>Comprehensive services including design, installation, commissioning, and maintenance of solar power systems.</li>
                    </ul>
                </div>
                <div>
                    <div class="stats">
                        <div class="stat">
                            <div class="num">245+</div>
                            <div class="sub">Happy Customers</div>
                        </div>
                        <div class="stat">
                            <div class="num">198</div>
                            <div class="sub">Projects Completed</div>
                        </div>
                        <div class="stat">
                            <div class="num">94%</div>
                            <div class="sub">Customer Satisfaction</div>
                        </div>
                    </div>
                    <div class="footer-url">vksolarenergy1989@gmail.com</div>
                </div>
            </div>
            <div class="right">
                <div class="image-wrap">
                    <div class="photo" style="background: linear-gradient(45deg, #4caf50, #2e7d32); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">VK SOLAR</div>
                </div>
            </div>
        </div>
    </section>
   
    <section id="consumer-details">
        <div class="container">
            <div class="content">
                <div class="column">
                    <div class="column-header">Vendor Information</div>
                    <div class="vendor-details">
                        <div class="vendor-info">
                            <h3>Company Details</h3>
                            <p><strong>VK SOLAR ENERGY</strong></p>
                            <p>Authorized Channel Partner: KIRLOSKAR SOLAR TECHNOLOGY PVT.LTD.</p>
                            <p>Registered & Approved By MSEDCL, MSME</p>
                            <p>GSTIN NO: 27CJXPK1402Q1ZK</p>
                        </div>
                        
                        <div class="vendor-info">
                            <h3>Services Offered</h3>
                            <div class="services">
                                <span class="service-tag">General Supplier</span>
                                <span class="service-tag">Installation of Solar On-Grid Plants</span>
                                <span class="service-tag">Solar Water Heater</span>
                                <span class="service-tag">Solar Street Light</span>
                            </div>
                        </div>
                        
                        <div class="vendor-info">
                            <h3>Office Address</h3>
                            <p>NEAR DR.A.V.JOSHI CLINIC KHADGAON ROAD KOHALE LAYOUT WADI NAGPUR 440023</p>
                        </div>
                        
                        <div class="contact-info">
                            <div class="contact-item">
                                <div class="contact-icon">📞</div>
                                <div><strong>Phone</strong><br>9075305275 / 9657135476</div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">✉️</div>
                                <div><strong>Email</strong><br>vksolarenergy1989@gmail.com</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="column">
                    <div class="column-header">Customer Information</div>
                    <div class="consumer-details">
                        <div class="consumer-info">
                            <h3>Customer Details</h3>
                            <div class="consumer-card">
                                <h4>' . $customerName . '</h4>
                                <p>📍 ' . $address . '</p>
                                <p>📞 ' . $phone . '</p>
                                ' . ($email ? '<p>✉️ ' . $email . '</p>' : '') . '
                                <p>🏠 Property Type: ' . ucfirst($propertyType) . '</p>
                                <p>🏠 Roof Type: ' . ucfirst($roofType) . '</p>
                                <p>⚡ Meter Type: ' . ucfirst($meterType) . '</p>
                                <p>📐 Roof Area: ' . $roofArea . ' sq ft</p>
                                <span class="status status-approved">New Quotation</span>
                            </div>
                        </div>
                        
                        <div class="consumer-info">
                            <h3>System Requirements</h3>
                            <p>⚡ System Size: ' . $systemSizeKw . ' kW</p>
                            <p>🔋 System Type: ' . ucfirst(str_replace('-', ' ', $systemType)) . '</p>
                            <p>💡 Monthly Bill: ₹' . number_format($monthlyBill, 2) . '</p>
                            <p>🔋 Battery Backup: ' . ($batteryBackup ? 'Yes' : 'No') . '</p>
                            <p>📊 Monitoring System: ' . ($monitoringSystem ? 'Yes' : 'No') . '</p>
                            <p>🔧 Maintenance Package: ' . ($maintenancePackage ? 'Yes' : 'No') . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description">
        <div class="page-title">SOLAR SYSTEM MATERIALS DETAILS</div>
        
        <table class="materials-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Specification</th>
                    <th>Qty</th>
                    <th>Make</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Solar Module BIFICIAL HALF-CUT</td>
                    <td>24V, 545Wp</td>
                    <td>' . $numberOfPanels . '</td>
                    <td>' . strtoupper($panelCompany) . ' SOLAR</td>
                </tr>
                <tr>
                    <td>Solar Grid Inverter</td>
                    <td>1 PH, ' . ceil($systemSize) . ' KW</td>
                    <td>01</td>
                    <td>' . strtoupper($inverterCompany) . '</td>
                </tr>
                <tr>
                    <td>Online monitoring</td>
                    <td>Lan Cable/WIFI Based</td>
                    <td>' . ($monitoringSystem ? '01' : '00') . '</td>
                    <td>' . strtoupper($inverterCompany) . '</td>
                </tr>
                <tr>
                    <td>Fabricated Structure Panel mounting</td>
                    <td>Modified Rooftop structure GI/AL Purlin Height-8 TO 6 Feet</td>
                    <td>' . $systemSizeKw . ' kW</td>
                    <td>FURTUNE/APPOLO</td>
                </tr>
                <tr>
                    <td>Solar Cables – For AC purpose & Accessories</td>
                    <td>2 core, 4 sq mm insulated wire</td>
                    <td>As required</td>
                    <td>Polycab / RR</td>
                </tr>
                <tr>
                    <td>Solar Cables – For DC purpose & accessories</td>
                    <td>1 core, 4 sq mm insulated wire</td>
                    <td>As required</td>
                    <td>RR / POLYCAB</td>
                </tr>
                <tr>
                    <td>AC side Breaker with ACDB</td>
                    <td>Input terminal with 20A, Enclosure with IP65 protection</td>
                    <td>01</td>
                    <td>SPD- HAVELS / ABB MCB-C&S / L&T</td>
                </tr>
                <tr>
                    <td>DC side Breaker with DCDB</td>
                    <td>Positive terminal with 20A, Enclosure with IP65 protection</td>
                    <td>01</td>
                    <td>D-ELMEX Fuse- ELMEX</td>
                </tr>
                <tr>
                    <td>Junction box, Earthing, LA and Accessories</td>
                    <td>As required</td>
                    <td>As required</td>
                    <td>Branded</td>
                </tr>
                <tr>
                    <td>Panel Mounting, Fitting and Installation</td>
                    <td>As required</td>
                    <td>As required</td>
                    <td>Standard</td>
                </tr>
                <tr>
                    <td>Net meter & Gen Meter</td>
                    <td>1Ph/3Ph</td>
                    <td>As required</td>
                    <td>HPL OR SECURE MSEDCL Approved</td>
                </tr>
                ' . ($batteryBackup ? '<tr><td>Battery Backup System</td><td>Lithium-ion/Lead Acid</td><td>As per system size</td><td>Branded</td></tr>' : '') . '
            </tbody>
        </table>
        
        <div class="specifications">
            <h2>System Specifications & Cost Breakdown</h2>
            <div class="spec-grid">
                <div class="spec-item">
                    <strong>Total System Capacity</strong>
                    <span>' . $systemSizeKw . ' kWp (' . $numberOfPanels . ' x 545Wp modules)</span>
                </div>
                <div class="spec-item">
                    <strong>System Type</strong>
                    <span>' . ucfirst(str_replace('-', ' ', $systemType)) . '</span>
                </div>
                <div class="spec-item">
                    <strong>Total System Cost</strong>
                    <span>' . $totalCostFormatted . '</span>
                </div>
                <div class="spec-item">
                    <strong>Government Subsidy</strong>
                    <span>' . $subsidyFormatted . '</span>
                </div>
                <div class="spec-item">
                    <strong>Final Cost (After Subsidy)</strong>
                    <span>' . $finalCostFormatted . '</span>
                </div>
                <div class="spec-item">
                    <strong>Monthly Savings</strong>
                    <span>' . $monthlySavingsFormatted . '</span>
                </div>
                <div class="spec-item">
                    <strong>Yearly Savings</strong>
                    <span>' . $yearlySavingsFormatted . '</span>
                </div>
                <div class="spec-item">
                    <strong>Payback Period</strong>
                    <span>' . $paybackPeriodFormatted . '</span>
                </div>
            </div>
        </div>
    </section>

    <section id="bank-details">
        <div class="container">
            <div class="page-title">BANK DETAILS & SCOPE OF WORK</div>
            
            <div class="content">
                <div class="left-column">
                    <div class="section">
                        <div class="section-title">Bank Details</div>
                        <div class="bank-details">
                            <div class="detail-row">
                                <div class="detail-label">NAME</div>
                                <div class="detail-value">VK SOLAR ENERGY</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">BANK NAME</div>
                                <div class="detail-value">HDFC BANK DATAWAIDINGP</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">ACCOUNT NO</div>
                                <div class="detail-value">50200065621522</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">IFSC CODE</div>
                                <div class="detail-value">HDFC0004224</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Scope of Client</div>
                        <ul class="scope-list">
                            <li>Adequate shade free roof top/ground allocation for installation of solar plant.</li>
                            <li>Availability of grid and approach to site.</li>
                            <li>Temporary power and water arrangement during EPC.</li>
                            <li>Internet Connection.</li>
                            <li>Material to be stocked safely at the site.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="right-column">
                    <div class="section">
                        <div class="section-title">Scope of Work</div>
                        <ul class="scope-list">
                            <li>Licensing for Net metering approval and commissioning</li>
                            <li>Designing of Solar PV Plant.</li>
                            <li>Supply and installation of module mounting structure.</li>
                            <li>Installation of PV modules</li>
                            <li>Supply and installation of inverters, distribution boards, energy meters etc.</li>
                            <li>Supply and installation of associated cables and electrical works.</li>
                            <li>Commissioning and trial run-out of solar plant</li>
                        </ul>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Warranty Terms</div>
                        <ul class="scope-list">
                            <li>Solar Panels: 25 years performance warranty</li>
                            <li>Inverter: 5-10 years manufacturer warranty</li>
                            <li>Installation: 2 years workmanship warranty</li>
                            <li>Structure: 10 years warranty against corrosion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>';
}
?>