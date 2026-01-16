<?php
// Session start karna
session_start();

// Session se data retrieve karna
$data = $_SESSION['quotation_data'] ?? null;

if (!$data) {
    // Agar data nahi mila toh error message
    die("No quotation data found. Please generate quotation from the main form.");
}

// Variables assign karna
$customer_name = $data['customer_name'] ?? '';
$contact = $data['contact'] ?? '';
$email = $data['email'] ?? '';
$system_size = $data['system_size'] ?? '';
$panel_company = $data['panel_company'] ?? '';
$inverter_company = $data['inverter_company'] ?? '';
$panel_model = $data['panel_model'] ?? '';
$system_type = $data['system_type'] ?? '';
$meter_type = $data['meter_type'] ?? '';
$current_monthly_bill = $data['current_monthly_bill'] ?? '';
$estimated_monthly_savings = $data['estimated_monthly_savings'] ?? '';
$estimated_yearly_savings = $data['estimated_yearly_savings'] ?? '';
$payback_period = $data['payback_period'] ?? '';
$savings_25_years = $data['savings_25_years'] ?? '';
$investment = $data['investment'] ?? '';
$subsidy_amount = $data['subsidy_amount'] ?? '';

// NEW: Fetch all the additional form fields
$panel_quantity = $data['panel_quantity'] ?? '';
$inverter_quantity = $data['inverter_quantity'] ?? '';
$inverter_type = $data['inverter_type'] ?? '';
$inverter_capacity = $data['inverter_capacity'] ?? '';
$vendor_name = $data['vendor_name'] ?? '';
$vendor_contact = $data['vendor_contact'] ?? '';
$vendor_email = $data['vendor_email'] ?? '';

$property_type = $data['property_type'] ?? '';
$roof_type = $data['roof_type'] ?? '';

// Calculate panel wattage from panel model
$panel_wattage = 0;
if (!empty($panel_model)) {
    $panel_wattage = intval(str_replace('W', '', $panel_model));
}

// Calculate panel count based on system size and panel wattage
$panel_count = 0;
if (!empty($system_size) && $panel_wattage > 0) {
    $panel_count = ceil(($system_size * 1000) / $panel_wattage);
}

// Calculate actual system size based on panel count and wattage
$actual_system_size = ($panel_count * $panel_wattage) / 1000;

// ROI calculate karna
$roi = '';
if (!empty($investment) && !empty($estimated_yearly_savings) && $investment > 0) {
    $roi_value = ($estimated_yearly_savings / $investment) * 100;
    $roi = number_format($roi_value, 1) . '%';
} else {
    $roi = '_________';
}

// Calculate subsidy and final cost
$subsidy = '';
$final_cost = '';
if (!empty($investment) && $investment > 0) {
    // Use actual subsidy amount from form, or calculate 20% if not provided
    if (!empty($subsidy_amount) && $subsidy_amount > 0) {
        $subsidy_value = $subsidy_amount;
    } else {
        $subsidy_value = $investment * 0.20;
    }
    $subsidy = '₹' . number_format($subsidy_value);
    $final_cost_value = $investment - $subsidy_value;
    $final_cost = '₹' . number_format($final_cost_value);
} else {
    $subsidy = '_________';
    $final_cost = '_________';
}

// For calculations in the template
$total_cost = $investment;
$monthly_savings = $estimated_monthly_savings;

// Current date
$currentDate = date('d/m/Y');

// Default values agar empty hain
function getValue($value, $default = '_________') {
    return !empty($value) ? $value : $default;
}

// Format currency values
function formatCurrency($value) {
    if (empty($value) || $value === '_________') {
        return '_________';
    }
    return '₹' . number_format($value);
}

// Extract first and last name from customer_name
$name_parts = explode(' ', $customer_name);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Quotation - <?php echo htmlspecialchars($customer_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
    /* =========================
       ORIGINAL STYLES (kept intact)
       ========================= */
    @page { size: A4 landscape; margin: 0; }
    html,body{height:100%;margin:0;font-family:'Poppins',sans-serif}

    /* about-section styles */
    #about-section .slide {
        width: 100%;
        height: 50rem;
        box-shadow: 0 10px 30px #4CAF50;
        display:flex;
        background: #f7efe3;
        overflow:hidden;
    }

    #about-section .left {
        flex: 0 0 63%;
        padding: 30px 40px;
        box-sizing: border-box;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
    }

    #about-section .right {
        flex: 0 0 37%;
        padding: 15px;
        box-sizing:border-box;
        background: transparent;
        display:flex;
        align-items:stretch;
    }

    #about-section .image-wrap{
        flex:1;
        padding:12px;
        background: #4CAF50;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    #about-section .image-wrap .photo{
        width:100%;
        height:100%;
        background: #ddd no-repeat center/cover;
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        border: 8px solid #fff;
        box-sizing:border-box;
    }

    #about-section h1.title{
        margin:0 0 12px 0;
        font-size:32px;
        font-weight:700;
        color:#111;
    }
    #about-section h1.title .accent{ color:#4CAF50; }

    #about-section .bullets{
        color:#333;
        font-size:14px;
        line-height:1.4;
    }
    #about-section .bullets li{ margin:8px 0; }
    #about-section .bullets li strong{ color:#f07d00; }

    #about-section .stats{
        display:flex;
        gap:16px;
        margin-top:20px;
    }
    #about-section .stat{
        background: linear-gradient(180deg,#60c463 0%, #4CAF50 100%);
        flex:1 1 0;
        padding:12px 16px;
        border-radius:8px;
        box-shadow: 0 6px 16px rgba(240,68,122,0.18);
        color:#fff;
        text-align:center;
        min-width:120px;
    }
    #about-section .stat .num{ font-size:22px; font-weight:800; margin-bottom:4px; }
    #about-section .stat .sub{ font-size:11px; opacity:0.95; }
    
    
  /* Section layout */
#description .slide {
    padding: 40px 20px;
}

#description .left {
    max-width: 1100px;
    margin: 0 auto;
}

/* Stats grid */
#description .stats {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;   /* center all cards */
    gap: 20px;
    margin-top: 25px;
}

/* Stat cards */
#description .stat {
    flex: 1 1 30%;
    background: linear-gradient(180deg, #60c463 0%, #4CAF50 100%);
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    color: #fff;
    text-align: center;
    min-width: 260px;          /* slight bump, you can keep 250 too */
}

/* Sub text */
#description .stat .sub {
    font-size: 18px;
    font-weight: 600;
    line-height: 1.5;
}
#description .left {
    max-width: 1100px;
    margin: 0 auto;
    text-align: center;
}

/* Image block */
#description .stat-img {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    width: 100%;
}

#description .stat-img img {
    width: 100%;
    max-width: 900px;   /* keeps it centered and not too large */
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}


/* ===== PRINT SETTINGS ===== */
@media print {

    /* A4 landscape for all pages */
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    /* Generic helper: any element with this class will try to stay on one page */
    .print-keep-together {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* Make description section stay together */
    #description,
    #description .slide,
    #description .left,
    #description .stats,
    #description .stat,
    #description .stat-img {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* Flex can cause weird breaks in print, make it block for safety */
    /*#description .stats {*/
    /*    display: block;*/
    /*}*/

    /* Optional: slightly shrink content on print so it fits better on one page */
    #description {
        transform: scale(0.95);
        transform-origin: top center;
    }

    /* Prevent image from being too tall and pushing to next page */
    #description .stat-img img {
        max-height: 5cm;
        object-fit: cover;
    }
}



    #about-section .footer-url{ font-size:12px; color:#6b6b6b; margin-top:12px; }
    
    
    

    #about-section .footer-url{ font-size:12px; color:#6b6b6b; margin-top:12px; }

    /* consumer-details styles */
    #consumer-details * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    
    #consumer-details .vendor-details, #consumer-details .consumer-details {
        padding: 10px;
    }
    
    #consumer-details .vendor-info, #consumer-details .consumer-info {
        margin-bottom: 15px;
    }
    
    #consumer-details .vendor-info h3, #consumer-details .consumer-info h3 {
        color: #2e7d32;
        margin-bottom: 5px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 2px;
        font-size: 1rem;
    }
    
    #consumer-details .vendor-info p, #consumer-details .consumer-info p {
        margin-bottom: 5px;
        display: flex;
        align-items: flex-start;
        font-size: 0.85rem;
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

    /* description styles */
    #description {
        width: 100%;
        height: 100%;
        background: white;
        padding: 20px 40px;
        position: relative;
        overflow: hidden;
    }
    
    #description .page-title {
        text-align: center;
        margin: 8px 0;
        color: #2e7d32;
        font-size: 12px;
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
    
    #description .materials-table thead {
        background-color: #4caf50;
        color: white;
    }
    
    #description .materials-table th {
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
    
    #description .specifications h2 {
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

    /* bank-details styles */
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
    
    #bank-details .left-column, #bank-details .right-column {
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
    
    #bank-details .exclusion-list {
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

    /* cover-page styles */
    #cover-page {
        width: 100%;
        height: 55rem;
        background: white;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
         margin-left:0px;
         padding-left:0px;
      
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

    /* Print and Back buttons styles */
    .print-btn {
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
    
    .print-btn:hover {
        background: #45a049;
    }

    /* Back button styles */
    .back-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        background: #2c3e50;
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

    .back-btn:hover {
        background: #34495e;
    }
    
    section {
        display: block;
        width: 100%;
        margin-bottom: 40px;
    }
    
    @media print {
        .print-btn, .back-btn { display: none; }
        body { margin: 0 !important; padding: 0 !important; }
        section { margin: 0 !important; width: 100vw !important; }
        #cover-page { padding: 40px !important; }
        #consumer-details .container, #bank-details .container { padding: 10px !important; margin: 0 !important; }
        #description { padding: 10px !important; }
    }
    
    #client-list {
  --brand: #4caf50;
  --page-bg: #f6fff5;
  --text: #111;
  --muted: #666;
  --card-shadow: 0 6px 18px rgba(0,0,0,0.08);
  --compact-font-size: 11px;
  box-sizing: border-box;
}

    /* container that matches A4 width for the module */
    #client-list .page {
      max-width: 210mm; /* A4 width */
      margin: 18px auto;
      padding: 14px;
      box-sizing: border-box;
    }

    /* modular card look */
    #client-list .card {
      background: white;
      border-radius: 12px;
      padding: 10px 14px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
    }

    #client-list header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 6px;
    }

    #client-list .logo-dot {
      width: 46px; height: 46px; border-radius: 8px;
      background: linear-gradient(135deg, var(--brand), #2e8b3a);
      display: flex; align-items: center; justify-content: center;
      color: #000; font-weight: 700; font-size: 18px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }

    #client-list h2{
      font-size: 14px;
      margin:0;
      letter-spacing:0.2px;
      color: var(--text);
    }

    #client-list .meta {
      margin-left: auto;
      text-align: right;
      font-size: 10px;
      color: var(--muted);
    }

    /* Table styling (compact) - scoped */
    #client-list table{
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: var(--compact-font-size);
      line-height: 1.1;
    }

    #client-list thead th{
      background: linear-gradient(180deg,var(--brand), #3da64a);
      color:#000;
      font-weight:700;
      padding:7px 6px;
      border:1px solid rgba(0,0,0,0.08);
      text-align:center;
      vertical-align:middle;
      font-size:12px;
    }

    #client-list tbody td{
      padding:6px 6px;
      border:1px solid rgba(0,0,0,0.06);
      vertical-align:middle;
      word-wrap:break-word;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    #client-list tbody tr:nth-child(odd){ background: #ffffff; }
    #client-list tbody tr:nth-child(even){ background: #fbfff9; }

    /* Compact column widths */
    #client-list th:nth-child(1), #client-list td:nth-child(1) { width:6%; text-align:center; }
    #client-list th:nth-child(2), #client-list td:nth-child(2) { width:74%; padding-left:8px; text-align:left; }
    #client-list th:nth-child(3), #client-list td:nth-child(3) { width:20%; text-align:center; }

    /* Make header repeat when printing - scoped */
    #client-list thead { display: table-header-group; }
    #client-list tfoot { display: table-row-group; }


    /* small screens */
    @media (max-width:600px){
      #client-list { --compact-font-size: 10px; }
      #client-list .page{ margin:8px; padding:8px; }
      #client-list thead th{ font-size:11px; }
    }
    #client-list .page-title{
         text-align: center;
            margin: 8px 0;
            color: #2e7d32;
            font-size: 18px;
            font-weight: 600;
            padding: 6px;
            background-color: #f1f8e9;
            border-radius: 4px;
            width: 100%;
    }
    
    
    #solar-quote {
      --brand: #4caf50;
      --page-bg: #f6fff5;
      --text: #111;
      --muted: #666;
      --card-shadow: 0 6px 18px rgba(0,0,0,0.08);
      --compact-font-size: 11px;
      box-sizing: border-box;
      background: var(--page-bg);
      padding: 12px;
    }

    /* container that matches A4 width for the module */
    #solar-quote .page {
      max-width: 210mm; /* A4 width */
      margin: 18px auto;
      padding: 14px;
      box-sizing: border-box;
    }

    /* modular card look */
    #solar-quote .card {
      background: white;
      border-radius: 12px;
      padding: 12px 18px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
    }

    #solar-quote header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 6px;
    }

    #solar-quote .logo-dot {
      width: 46px; height: 46px; border-radius: 8px;
      background: linear-gradient(135deg, var(--brand), #2e8b3a);
      display: flex; align-items: center; justify-content: center;
      color: #000; font-weight: 700; font-size: 18px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }

    #solar-quote h2{
      font-size: 16px;
      margin:0;
      letter-spacing:0.2px;
      color: var(--text);
    }

    #solar-quote .meta {
      margin-left: auto;
      text-align: right;
      font-size: 12px;
      color: var(--muted);
    }

    /* Table styling (compact) - scoped */
    #solar-quote table{
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: var(--compact-font-size);
      line-height: 1.2;
      margin-top: 8px;
    }

    #solar-quote thead th{
      background: linear-gradient(180deg,var(--brand), #3da64a);
      color:#000;
      font-weight:700;
      padding:8px 6px;
      border:1px solid rgba(0,0,0,0.08);
      text-align:center;
      vertical-align:middle;
      font-size:12px;
    }

    #solar-quote tbody td{
      padding:8px 8px;
      border:1px solid rgba(0,0,0,0.06);
      vertical-align:middle;
      word-wrap:break-word;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      font-size:8px;
    }

    #solar-quote tbody tr:nth-child(odd){ background: #ffffff; }
    #solar-quote tbody tr:nth-child(even){ background: #fbfff9; }

    /* Column widths for the invoice table */
    #solar-quote th.col-1, #solar-quote td.col-1 { width:6%; text-align:center; }
    #solar-quote th.col-2, #solar-quote td.col-2 { width:44%; padding-left:8px; text-align:left; }
    #solar-quote th.col-3, #solar-quote td.col-3 { width:12%; text-align:center; }
    #solar-quote th.col-4, #solar-quote td.col-4 { width:12%; text-align:center; }
    #solar-quote th.col-5, #solar-quote td.col-5 { width:13%; text-align:right; padding-right:12px; }

    /* totals footer */
    #solar-quote tfoot td{ font-weight:700; background:transparent; border:none; }

    .terms{ margin-top:5px; font-size:13px; color:var(--text); }
    .terms h3{ margin:6px 0; color:var(--brand); }
    .terms p, .terms li{ margin:6px 0; color:var(--muted); line-height:1.4; }

    @media print {
      /* Only modify styles for the module when printing */
      #solar-quote .page { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
      #solar-quote .card { border-radius: 0; padding: 6mm; box-shadow: none; }
      #solar-quote header .logo-dot { display: none; } /* save space */
      #solar-quote h2 { font-size: 14px; }
      #solar-quote table { font-size: 11px; }
      #solar-quote tbody td { white-space: normal; } /* allow wrapping on print */
      tr, td, th { page-break-inside: avoid; }
    }

    /* small screens */
    @media (max-width:600px){
      #solar-quote { --compact-font-size: 10px; }
      #solar-quote .page{ margin:8px; padding:8px; }
      #solar-quote thead th{ font-size:11px; }
      #solar-quote tbody td{ white-space:normal; }
    }

    /* ====== End of original styles ====== */


    /* =========================
       PRINT-SAFE OVERRIDES (minimal, appended)
       - preserves your on-screen design and colors
       - only takes effect when printing / Save as PDF
       ========================= */

    /* Use landscape as you originally set; set safe margin for print */
    @page { size: A4 landscape; margin: 10mm; }

    /* Small screen-safe tweak to ensure flex children can shrink (prevents overflow) */
    html, body { overflow-x: hidden; }
    #about-section .left, #about-section .right { min-width: 0; }

    /* Print-specific rules */
    @media print {
      /* Ensure printing doesn't keep fullscreen vh heights and allows multi-page flow */
      html, body {
        height: auto !important;
        overflow: visible !important;
      }

      /* Hide interactive UI */
      .print-btn, .back-btn { display: none !important; }

      /* Make major wrappers flow and avoid clipping */
      section,
      #about-section .slide,
      #cover-page,
      #consumer-details .container,
      #bank-details .container,
      #description,
      #client-list .page,
      #solar-quote .page {
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
        box-shadow: none !important; /* visual-only removed for print */
        page-break-inside: avoid !important;
      }

      /* Clamp module pages to A4 printable width and add safe padding */
      .page, #client-list .page, #solar-quote .page {
        max-width: 297mm !important; /* landscape width (A4 width in mm) */
        width: 100% !important;
        margin: 0 auto !important;
        padding-left: 6mm !important;
        padding-right: 6mm !important;
        box-sizing: border-box !important;
        overflow: visible !important;
      }

      /* Tables: allow header repeat and sensible breaking */
      table { page-break-inside: auto !important; border-collapse: collapse !important; }
      thead { display: table-header-group !important; }
      tfoot { display: table-row-group !important; }
      tr { page-break-inside: avoid !important; break-inside: avoid !important; }

      /* Allow wrapping on print so long texts don't push columns off page */
      #solar-quote tbody td,
      #client-list tbody td,
      .materials-table td,
      #client-list td,
      #solar-quote td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow: visible !important;
        text-overflow: clip !important;
      }

      /* Force cover on its own page, others start on new pages by default */
      #cover-page { page-break-after: always !important; }
      #about-section, #consumer-details, #description, #client-list, #solar-quote, #bank-details {
        page-break-before: always !important;
        break-before: page !important;
      }

      /* Images should fit page width */
      img, .photo { max-width: 100% !important; height: auto !important; }

      /* Prevent any element exceeding page width in print */
      * { max-width: 100% !important; box-sizing: border-box !important; }

      /* Keep your colors intact as much as user agent allows */
      body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }

    /* =========================
       NOTES:
       - This appended block only adds print-targeted rules; it won't remove your colors.
       - If you prefer the PDF in portrait, change the @page size above to "A4 portrait".
       - If you want two sections to share a printed page (e.g., about + consumer), remove that selector from the page-break-before list.
       ========================= */
       /* =========================
   PRINT: remove unwanted blank first page
   (Append this at the END of your existing <style>)
   ========================= */

@page { size: A4 landscape; margin: 10mm; } /* safe override */

/* Safety: ensure no browser default margins create a blank page */
html, body {
  margin: 0 !important;
  padding: 0 !important;
  height: auto !important;
  overflow: visible !important;
}

/* Print-specific adjustments */
@media print {

  /* Prevent a forced page break before the very first element */
  body > *:first-child,
  body > section:first-child {
    page-break-before: avoid !important;
    break-before: avoid !important;
    margin-top: 0 !important;
  }

  /* If any of these sections happen to be first on the page, do NOT force a break before them */
  #cover-page:first-of-type,
  #about-section:first-of-type,
  #consumer-details:first-of-type,
  #description:first-of-type,
  #client-list:first-of-type,
  #solar-quote:first-of-type,
  #bank-details:first-of-type {
    page-break-before: avoid !important;
    break-before: avoid !important;
  }

  /* Keep cover alone (after it) but avoid creating a blank page before it */
  #cover-page {
    page-break-after: always !important;
    break-after: page !important;
    page-break-before: avoid !important;
    break-before: avoid !important;
    margin-top: 0 !important;
  }

  /* Make major sections flow naturally and remove forced viewport heights for print */
  section,
  .page,
  #about-section .slide,
  #consumer-details .container,
  #bank-details .container,
  #description,
  #client-list .page,
  #solar-quote .page {
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* Ensure nothing exceeds printable width or creates extra blank page */
  .page, #client-list .page, #solar-quote .page {
    width: 100% !important;
    max-width: 297mm !important; /* landscape A4 width */
    padding-left: 6mm !important;
    padding-right: 6mm !important;
    box-sizing: border-box !important;
    margin: 0 auto !important;
  }

  /* Avoid top margins from large elements causing an empty page in some engines */
  body > header,
  body > div:first-child {
    margin-top: 0 !important;
    padding-top: 0 !important;
  }

  /* Hide the print button (interactive element) */
  .print-btn, .back-btn { display: none !important; }

  /* Keep colors stable where possible */
  body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

  /* Small extra safety: prevent any element from overflowing horizontally */
  * { max-width: 100% !important; box-sizing: border-box !important; }
}
.newcontainer {
    width: 100%;
    margin-right: auto;
    margin-left: auto;
    padding-right: 15px;
    padding-left: 15px;
}
/* Extra small (default) */
.newcontainer {
    max-width: 100%;
}
/* Small (≥576px) */
@media (min-width: 576px) {
    .newcontainer {
        max-width: 540px;
    }
}
/* Medium (≥768px) */
@media (min-width: 768px) {
    .newcontainer {
        max-width: 720px;
    }
}
/* Large (≥992px) */
@media (min-width: 992px) {
    .newcontainer {
        max-width: 960px;
    }
}
/* Extra large (≥1200px) */
@media (min-width: 1200px) {
    .newcontainer {
        max-width: 1140px;
    }
}
/* XXL (≥1400px) */
@media (min-width: 1400px) {
    .newcontainer {
        max-width: 1320px;
    }
}
/* --- VK Partner section --- */
#vk-partner {
    padding: 60px 20px;
}
#vk-partner .vk-hero-card {
    background: #188a4d;
    border-radius: 32px;
    padding: 35px 40px;
    display: flex;
    align-items: center;
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
}
/* Left image */
#vk-partner .vk-hero-image {
    flex: 0 0 45%;
    position: relative;
}
#vk-partner .vk-hero-image img {
    width: 100%;
    display: block;
    border-radius: 24px;
    object-fit: cover;
}
/* Tag on image */
#vk-partner .vk-hero-tag {
    position: absolute;
    left: 12px;
    bottom: 12px;
    background: rgba(24,138,77,0.95);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
/* Right content */
#vk-partner .vk-hero-content {
    flex: 1 1 55%;
    color: #ffffff;
}
#vk-partner .vk-hero-eyebrow {
    font-size: 14px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #ffeb99;
    margin-bottom: 6px;
}
#vk-partner .vk-hero-title {
    font-size: 26px;
    line-height: 1.3;
    margin: 0 0 16px;
    font-weight: 800;
}
#vk-partner .vk-hero-text {
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 18px;
}
/* Bullet list */
#vk-partner .vk-hero-list {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
    font-size: 15px;
}
#vk-partner .vk-hero-list li {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
/* Button */
#vk-partner .vk-hero-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 26px;
    border-radius: 999px;
    background: #0f5f34;
    color: #ffffff;
    font-weight: 600;
    text-decoration: none;
    font-size: 15px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
#vk-partner .vk-hero-btn:hover {
    background: #0b4725;
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.3);
}
/* --- Responsive --- */
@media (max-width: 900px) {
    #vk-partner .vk-hero-card {
        flex-direction: row;
        padding: 24px 20px;
    }
    #vk-partner .vk-hero-image,
    #vk-partner .vk-hero-content {
        flex: 1 1 100%;
    }
    #vk-partner .vk-hero-title {
        font-size: 22px;
    }
}
/* --- PRINT: keep this block on same page (works with your landscape setup) --- */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    #vk-partner,
    #vk-partner .vk-hero-card,
    #vk-partner .vk-hero-image,
    #vk-partner .vk-hero-content {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    /* Slight padding, but not tiny */
    #vk-partner {
        padding: 6mm 6mm;
    }
    #vk-partner .vk-hero-card {
        max-width: 100%;
        padding: 8mm 9mm;
        border-radius: 18px;
        box-shadow: none;
        gap: 8mm;
    }
    /* Make image a bit taller again, but still limited */
    #vk-partner .vk-hero-image img {
        max-height: 85mm;    /* was 55mm – now bigger */
        object-fit: cover;
    }
    #vk-partner .vk-hero-tag {
        font-size: 12px;
        padding: 4px 9px;
    }
    /* Increase text sizes a bit */
    #vk-partner .vk-hero-title {
        font-size: 18pt;     /* was 14pt */
        margin-bottom: 10px;
    }
    #vk-partner .vk-hero-text,
    #vk-partner .vk-hero-list {
        font-size: 14pt;   /* was 9.5pt */
        line-height: 1.45;
        margin-bottom: 12px;
    }
    #vk-partner .vk-hero-list li {
        margin-bottom: 5px;
    }
    #vk-partner .vk-hero-btn {
        padding: 7px 18px;
        font-size: 10pt;
        box-shadow: none;
    }
}
/* === Partner Contact Section (screen) === */
#partner-contact {
    padding: 60px 20px;
}
#partner-contact .partner-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #02152a;           /* dark navy */
    border-radius: 32px;
    padding: 32px 40px 40px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
     margin-bottom:5px;
}
#partner-contact .partner-heading {
    font-size: 30px;
    font-weight: 800;
    margin: 0 0 10px;
}
#partner-contact .partner-subtitle {
    margin: 0 0 24px;
    font-size: 15px;
    line-height: 1.6;
    color: #e3f2fd;
}
/* SCREEN (side by side) */
#partner-contact .partner-cols {
    display: flex;
    flex-wrap: nowrap;      /* always in one row */
    gap: 24px;
    margin-top: 10px;
}
#partner-contact .partner-box {
    width: 50%;             /* equal width */
    min-width: 300px;
    background: #031d3b;    
    border-radius: 24px;
    padding: 22px 26px;
}
#partner-contact .partner-box-title {
    font-size: 22px;
    margin: 0 0 14px;
    font-weight: 700;
}
#partner-contact .partner-list {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 15px;
    line-height: 1.7;
}
#partner-contact .partner-list li {
    margin-bottom: 6px;
}
#partner-contact .partner-box p {
    margin: 0 0 6px;
    font-size: 15px;
    line-height: 1.7;
}
#partner-contact .partner-box .label {
    font-weight: 600;
}
/* Responsive */
@media (max-width: 900px) {
    #partner-contact .partner-card {
        padding: 24px 20px 28px;
        border-radius: 24px;
    }
    #partner-contact .partner-heading {
        font-size: 26px;
    }
    #partner-contact .partner-box {
        flex: 1 1 100%;
    }
}
/* === PRINT: keep on one landscape page === */
@media print {
    /* partner-contact must stay on one page */
    #partner-contact,
    #partner-contact .partner-card,
    #partner-contact .partner-cols,
    #partner-contact .partner-box {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #partner-contact {
        padding: 6mm 6mm;
    }
    #partner-contact .partner-card {
        max-width: 100%;
        padding: 8mm 9mm;
        border-radius: 18px;
        box-shadow: none;
    }
    /* ⭐ keep the two boxes side by side in print */
    #partner-contact .partner-cols {
        display: flex;
        flex-wrap: nowrap;   /* no wrapping */
        gap: 6mm;
    }
    #partner-contact .partner-box {
        width: 50%;
        min-width: 0;        /* allow shrink to fit page */
        padding: 6mm 7mm;
        border-radius: 14px;
    }
    #partner-contact .partner-heading {
        font-size: 18pt;
        margin-bottom: 6px;
    }
    #partner-contact .partner-subtitle,
    #partner-contact .partner-box p,
    #partner-contact .partner-list {
        font-size: 14pt;
        line-height: 1.4;
        margin-bottom: 4px;
    }
}
/* Response buttons section */
.response-buttons {
    display: flex;
    gap: 18px;
    margin-top: 20px;
    justify-content: center;
}
.response-btn {
    padding: 12px 24px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    color: white;
    display: inline-block;
    transition: 0.2s ease;
}
/* Button colors */
.response-btn.accept   { background: #2ecc71; }
.response-btn.reject   { background: #e74c3c; }
.response-btn.review   { background: #f1c40f; color: #000; }
/* Hover effects */
.response-btn:hover {
    opacity: 0.85;
    transform: translateY(-2px);
}
/* ===== Partner Benefits (screen) ===== */
#partner-benefits {
    padding: 50px 20px;
    background: #f5f8f5;           /* light grey/green background */
}
#partner-benefits .benefits-inner {
    max-width: 1200px;
    margin: 0 auto;
}
#partner-benefits .benefits-heading {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px;
}
#partner-benefits .benefits-highlight {
    background: #c8f7c5;
    border-bottom: 3px solid #2e7d32;
    padding: 0 6px 2px;
}
#partner-benefits .benefits-subtitle {
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 26px;
    max-width: 800px;
}
#partner-benefits .text-highlight {
    font-weight: 600;
    background: #e0f6dd;
}
/* grid of cards */
#partner-benefits .benefits-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-gap: 10px;
}
#partner-benefits .benefit-card {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    border: 2px solid #2e7d32;
    border-radius: 14px;
    padding: 18px 22px;
    background: #ffffff;
}
#partner-benefits .benefit-icon {
    flex: 0 0 52px;
    height: 52px;
    border-radius: 50%;
    border: 2px solid #2e7d32;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
#partner-benefits .benefit-content h3 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
}
#partner-benefits .benefit-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}
/* responsive */
@media (max-width: 900px) {
    #partner-benefits .benefits-grid {
        grid-template-columns: 1fr 1fr; 
    }
    #partner-benefits .benefits-heading {
        font-size: 26px;
    }
}
/* ===== PRINT: keep section on one landscape page ===== */
@media print {
    #partner-benefits,
    #partner-benefits .benefits-inner,
    #partner-benefits .benefits-grid,
    #partner-benefits .benefit-card {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #partner-benefits {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #partner-benefits .benefits-heading {
        font-size: 16pt;
        margin-bottom: 4mm;
    }
    #partner-benefits .benefits-subtitle {
        font-size: 10pt;
        margin-bottom: 6mm;
    }
    #partner-benefits .benefits-grid {
        gap: 4mm;
    }
    #partner-benefits .benefit-card {
        padding: 4mm 5mm;
        border-radius: 8px;
    }
    #partner-benefits .benefit-content h3 {
        font-size: 10pt;
    }
    #partner-benefits .benefit-content p {
        font-size: 10pt;
    }
}
/* ===== Cost Breakdown (screen) ===== */
#cost-breakdown {
    padding: 50px 20px;
    background: #f5f8f5;
}
#cost-breakdown .cost-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 32px 32px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #2e7d32;
}
#cost-breakdown .cost-heading {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px;
}
#cost-breakdown .cost-subtitle {
    margin: 0 0 18px;
    font-size: 14px;
    line-height: 1.6;
    color: #555;
}
#cost-breakdown .cost-table-wrapper {
    overflow-x: auto;
}
#cost-breakdown .cost-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
#cost-breakdown .cost-table thead th {
    background: #e8f5e9;
    border-bottom: 2px solid #2e7d32;
    padding: 10px 8px;
    text-align: left;
}
#cost-breakdown .cost-table tbody td {
    padding: 8px 8px;
    border-bottom: 1px solid #e0e0e0;
}
#cost-breakdown .cost-table tbody tr:nth-child(even) {
    background: #fafafa;
}
#cost-breakdown .cost-table tfoot td {
    padding: 8px 8px;
    font-weight: 600;
    border-top: 2px solid #2e7d32;
}
#cost-breakdown .cost-table .text-right {
    text-align: right;
}
#cost-breakdown .cost-table .total-label {
    font-size: 15px;
}
#cost-breakdown .cost-table .total-value {
    font-size: 15px;
    color: #1b5e20;
}
#cost-breakdown .cost-note {
    margin-top: 10px;
    font-size: 12px;
    color: #666;
}
/* ===== Responsive ===== */
@media (max-width: 768px) {
    #cost-breakdown .cost-card {
        padding: 22px 18px 24px;
        border-radius: 18px;
    }
    #cost-breakdown .cost-heading {
        font-size: 22px;
    }
    #cost-breakdown .cost-table {
        font-size: 13px;
    }
}
/* ===== Print: keep on one landscape page ===== */
@media print {
    #cost-breakdown,
    #cost-breakdown .cost-card,
    #cost-breakdown .cost-table-wrapper,
    #cost-breakdown .cost-table {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #cost-breakdown {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #cost-breakdown .cost-card {
        max-width: 100%;
        padding: 7mm 8mm;
        border-radius: 12px;
        box-shadow: none;
        border-left-width: 4px;
    }
    #cost-breakdown .cost-heading {
        font-size: 16pt;
        margin-bottom: 3mm;
    }
    #cost-breakdown .cost-subtitle {
        font-size: 10.5pt;
        margin-bottom: 4mm;
    }
    #cost-breakdown .cost-table {
        font-size: 9.5pt;
    }
    #cost-breakdown .cost-table thead th,
    #cost-breakdown .cost-table tbody td,
    #cost-breakdown .cost-table tfoot td {
        padding: 2mm 1.5mm;
    }
    #cost-breakdown .cost-note {
        font-size: 9pt;
        margin-top: 2mm;
    }
}
/* ===== ROI & Savings (screen) ===== */
#roi-savings {
    padding: 50px 20px;
    background: #f5f8f5;
}
#roi-savings .roi-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 32px 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #1b5e20;
}
#roi-savings .roi-heading {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px;
}
#roi-savings .roi-subtitle {
    margin: 0 0 18px;
    font-size: 14px;
    line-height: 1.6;
    color: #555;
}
/* Summary badges */
#roi-savings .roi-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 20px;
}
#roi-savings .roi-badge {
    flex: 1 1 30%;
    min-width: 220px;
    background: #e8f5e9;
    border-radius: 16px;
    padding: 10px 14px;
    border: 1px solid #2e7d32;
}
#roi-savings .roi-badge .label {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #2e7d32;
    margin-bottom: 4px;
}
#roi-savings .roi-badge .value {
    font-size: 16px;
    font-weight: 700;
}
/* Table */
#roi-savings .roi-table-wrapper {
    overflow-x: auto;
}
#roi-savings .roi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
#roi-savings .roi-table thead th {
    background: #e8f5e9;
    border-bottom: 2px solid #2e7d32;
    padding: 9px 8px;
    text-align: left;
}
#roi-savings .roi-table tbody td {
    padding: 7px 8px;
    border-bottom: 1px solid #e0e0e0;
}
#roi-savings .roi-table tbody tr:nth-child(even):not(.roi-payback-row) {
    background: #fafafa;
}
#roi-savings .roi-table .roi-payback-row {
    background: #fffde7;
    font-weight: 600;
}
#roi-savings .roi-footer-notes {
    margin-top: 14px;
}
#roi-savings .roi-footer-notes p {
    margin: 0 0 4px;
    font-size: 13px;
}
#roi-savings .roi-footer-notes ul {
    margin: 0;
    padding-left: 18px;
    font-size: 12.5px;
    color: #555;
}
/* Responsive */
@media (max-width: 768px) {
    #roi-savings .roi-card {
        padding: 22px 18px 24px;
        border-radius: 18px;
    }
    #roi-savings .roi-heading {
        font-size: 22px;
    }
    #roi-savings .roi-table {
        font-size: 13px;
    }
}
/* ===== Print: keep ROI section on one landscape page ===== */
@media print {
    #roi-savings,
    #roi-savings .roi-card,
    #roi-savings .roi-table-wrapper,
    #roi-savings .roi-table {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #roi-savings {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #roi-savings .roi-card {
        max-width: 100%;
        padding: 7mm 8mm;
        border-radius: 12px;
        box-shadow: none;
        border-left-width: 4px;
    }
    #roi-savings .roi-heading {
        font-size: 12pt;
        margin-bottom: 3mm;
    }
    #roi-savings .roi-subtitle {
        font-size: 8pt;
        margin-bottom: 4mm;
    }
    #roi-savings .roi-summary {
        gap: 3mm;
        margin-bottom: 4mm;
    }
    #roi-savings .roi-badge {
        padding: 3mm 3mm;
        border-radius: 8px;
    }
    #roi-savings .roi-badge .label {
        font-size: 10pt;
    }
    #roi-savings .roi-badge .value {
        font-size: 12pt;
    }
    #roi-savings .roi-table {
        font-size: 11pt;
    }
    #roi-savings .roi-table thead th,
    #roi-savings .roi-table tbody td {
        padding: 2mm 1.5mm;
    }
    #roi-savings .roi-footer-notes p,
    #roi-savings .roi-footer-notes ul {
        font-size: 8pt;
    }
}
/* ===== Govt. Subsidy Section (screen) ===== */
#govt-subsidy {
    padding: 50px 20px;
    background: #f5f8f5;
}
#govt-subsidy .subsidy-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 32px 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #2e7d32;
}
#govt-subsidy .subsidy-heading {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px;
}
#govt-subsidy .subsidy-subtitle {
    margin: 0 0 18px;
    font-size: 14px;
    line-height: 1.6;
    color: #555;
}
/* summary badges */
#govt-subsidy .subsidy-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 20px;
}
#govt-subsidy .subsidy-badge {
    flex: 1 1 30%;
    min-width: 220px;
    background: #e8f5e9;
    border-radius: 16px;
    padding: 10px 14px;
    border: 1px solid #2e7d32;
}
#govt-subsidy .subsidy-badge .label {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #2e7d32;
    margin-bottom: 4px;
}
#govt-subsidy .subsidy-badge .value {
    font-size: 15px;
    font-weight: 700;
}
/* table */
#govt-subsidy .subsidy-table-wrapper {
    overflow-x: auto;
    margin-bottom: 18px;
}
#govt-subsidy .subsidy-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
#govt-subsidy .subsidy-table thead th {
    background: #e8f5e9;
    border-bottom: 2px solid #2e7d32;
    padding: 9px 8px;
    text-align: left;
}
#govt-subsidy .subsidy-table tbody td {
    padding: 7px 8px;
    border-bottom: 1px solid #e0e0e0;
}
#govt-subsidy .subsidy-table tbody tr:nth-child(even) {
    background: #fafafa;
}
/* bottom two columns */
#govt-subsidy .subsidy-bottom {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-top: 4px;
}
#govt-subsidy .subsidy-column {
    flex: 1 1 45%;
    min-width: 260px;
}
#govt-subsidy .subsidy-small-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 8px;
}
#govt-subsidy .subsidy-list {
    margin: 0;
    padding-left: 18px;
    font-size: 13px;
    line-height: 1.6;
    color: #555;
}
#govt-subsidy .subsidy-note {
    margin-top: 12px;
    font-size: 12px;
    color: #666;
}
/* responsive */
@media (max-width: 768px) {
    #govt-subsidy .subsidy-card {
        padding: 22px 18px 24px;
        border-radius: 18px;
    }
    #govt-subsidy .subsidy-heading {
        font-size: 22px;
    }
    #govt-subsidy .subsidy-table {
        font-size: 13px;
    }
}
/* ===== Print: keep on one landscape page ===== */
@media print {
    #govt-subsidy,
    #govt-subsidy .subsidy-card,
    #govt-subsidy .subsidy-table-wrapper,
    #govt-subsidy .subsidy-table {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #govt-subsidy {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #govt-subsidy .subsidy-card {
        max-width: 100%;
        padding: 7mm 8mm;
        border-radius: 12px;
        box-shadow: none;
        border-left-width: 4px;
    }
    #govt-subsidy .subsidy-heading {
        font-size: 12pt;
        margin-bottom: 3mm;
    }
    #govt-subsidy .subsidy-subtitle {
        font-size: 8pt;
        margin-bottom: 4mm;
    }
    #govt-subsidy .subsidy-summary {
        gap: 3mm;
        margin-bottom: 4mm;
    }
    #govt-subsidy .subsidy-badge {
        padding: 3mm 3mm;
        border-radius: 8px;
    }
    #govt-subsidy .subsidy-badge .label {
        font-size: 8pt;
    }
    #govt-subsidy .subsidy-badge .value {
        font-size: 10pt;
    }
    #govt-subsidy .subsidy-table {
        font-size: 10pt;
    }
    #govt-subsidy .subsidy-table thead th,
    #govt-subsidy .subsidy-table tbody td {
        padding: 2mm 1.5mm;
    }
    #govt-subsidy .subsidy-bottom {
        gap: 4mm;
    }
    #govt-subsidy .subsidy-list {
        font-size: 9pt;
    }
    #govt-subsidy .subsidy-note {
        font-size: 10pt;
        margin-top: 2mm;
    }
}
/* ========== 1. MATERIALS DETAILS ========== */
#materials-details {
    padding: 50px 20px;
    background: #f5f8f5;
    
}
#materials-details .materials-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 32px 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #2e7d32;
}
.materials-table-wrapper {
    overflow-x: auto;
    margin-bottom: 20px;
}
.materials-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8px;
}
.materials-table thead th {
    background: #e8f5e9;
    border-bottom: 2px solid #2e7d32;
    padding: 9px 8px;
    text-align: left;
}
.materials-table tbody td {
    padding: 7px 8px;
    border-bottom: 1px solid #e0e0e0;
}
.materials-table tbody tr:nth-child(even) {
    background: #fafafa;
}
/* Specs grid */
#materials-details .spec-title {
    font-size: 10px;
    margin: 8px 0 12px;
    font-weight: 800;
}
#materials-details .spec-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}
#materials-details .spec-item {
    background: #f1f8e9;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 8px;
}
#materials-details .spec-item strong {
    display: block;
    margin-bottom: 4px;
}
/* ========== 2. COSTING DETAILS ========== */
#costing-details {
    padding: 50px 20px;
    background: #ffffff;
}
#costing-details .costing-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 32px 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #1b5e20;
}
#costing-details .costing-table {
    margin-bottom: 18px;
}
.section-subheading {
    font-size: 18px;
    margin: 10px 0 8px;
}
.section-subheading .accent {
    border-bottom: 2px solid #2e7d32;
    padding-bottom: 2px;
}
.terms-list {
    font-size: 13px;
    line-height: 1.6;
    padding-left: 18px;
}
/* ========== 3. BANK DETAILS & SCOPE OF WORK ========== */
/* ===== BANK DETAILS & SCOPE (SCREEN) ===== */
#bank-details {
    padding: 40px 20px;
    background: #f5f8f5;
}
#bank-details .bank-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 24px 22px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-left: 5px solid #0d47a1;
}
/* 2 x 2 grid of small cards */
#bank-details .bank-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-auto-rows: auto;
    gap: 16px;
    margin-top: 10px;
}
#bank-details .bank-box {
    background: #f3f6fb;
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 13px;
}
#bank-details .bank-box-title {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 6px;
}
/* small tables for bank + cost */
#bank-details .bank-mini-table {
    width: 100%;
    border-collapse: collapse;
}
#bank-details .bank-mini-table td {
    padding: 2px 0;
    vertical-align: top;
}
#bank-details .bank-mini-table .label {
    width: 40%;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.03em;
}
#bank-details .bank-mini-table .value {
    width: 60%;
}
/* lists */
#bank-details .scope-list,
#bank-details .exclusion-list {
    margin: 0;
    padding-left: 16px;
    font-size: 12.5px;
    line-height: 1.5;
}
/* responsive: stack on small screen */
@media (max-width: 768px) {
    #bank-details .bank-card {
        padding: 18px 16px 20px;
        border-radius: 16px;
        
    }
    #bank-details .bank-grid {
        grid-template-columns: 1fr;
    }
}
  
  @media print {
    #bank-details {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #bank-details .bank-card {
        max-width: 100%;
        padding: 6mm 7mm;
        border-radius: 10px;
        box-shadow: none;
        border-left-width: 3px;
    }
    #bank-details .bank-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 3mm;
    }
    #bank-details .bank-box {
        padding: 3mm 3mm;
        border-radius: 6px;
        font-size: 14pt;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #bank-details .bank-box-title {
        font-size: 14pt;
        margin-bottom: 2mm;
    }
    #bank-details .bank-mini-table td {
        padding: 1mm 0;
    }
    #bank-details .bank-mini-table .label {
        font-size: 12pt;
    }
    #bank-details .scope-list,
    #bank-details .exclusion-list {
        font-size: 22pt;
        line-height: 1.75;
    }
}
/* ========== PRINT (stick to one page each) ========== */
@media print {
    #materials-details,
    #materials-details .materials-card,
    #materials-details .materials-table,
    #materials-details .spec-grid,
    #costing-details,
    #costing-details .costing-card,
    #bank-details,
    #bank-details .bank-card,
    #bank-details .bank-layout {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #materials-details,
    #costing-details,
    #bank-details {
        padding: 6mm 6mm;
        background: #ffffff;
    }
    #materials-details .materials-card,
    #costing-details .costing-card,
    #bank-details .bank-card {
        max-width: 100%;
        padding: 7mm 8mm;
        border-radius: 12px;
        box-shadow: none;
        font-size: 10px;
    }
    .materials-table,
    .costing-table {
        font-size: 10px;
    }
    .materials-table thead th,
    .materials-table tbody td,
    .costing-table thead th,
    .costing-table tbody td {
        padding: 2mm 1.5mm;
    }
    #materials-details .spec-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 3mm;
    }
    #materials-details .spec-item {
        font-size: 9pt;
        padding: 2mm 2mm;
    }
    .terms-list,
    .scope-list,
    .exclusion-list {
        font-size: 10px;
    }
}
/* ========= ABOUT SECTION ========= */
#about-vksolar {
    padding: 50px 20px;
    background: #f5f8f5;
}
#about-vksolar .about-card {
    max-width: 1200px;
    margin: auto;
    background: #ffffff;
    padding: 30px 36px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-left: 6px solid #2e7d32;
}
/* Main Grid */
#about-vksolar .about-grid {
    display: flex;
    gap: 32px;
    align-items: center;
}
/* LEFT CONTENT */
#about-vksolar .about-info {
    flex: 1 1 60%;
}
#about-vksolar .section-title {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 20px;
}
/* 2 Columns of Bullet Points */
#about-vksolar .about-list-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
#about-vksolar .about-list {
    padding-left: 18px;
    font-size: 14px;
    line-height: 1.6;
}
/* STATS */
#about-vksolar .about-stats {
    display: flex;
    gap: 18px;
    margin: 15px 0;
}
#about-vksolar .about-stat {
    flex: 1;
    background: #e8f5e9;
    padding: 14px;
    text-align: center;
    border-radius: 12px;
    border: 1px solid #c8e6c9;
}
#about-vksolar .stat-num {
    font-size: 28px;
    font-weight: 800;
    color: #1b5e20;
}
#about-vksolar .stat-label {
    font-size: 13px;
    opacity: 0.9;
}
/* RIGHT IMAGE */
#about-vksolar .about-image img {
    width: 100%;
    max-width: 420px;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(0,0,0,0.18);
}
/* Email footer */
#about-vksolar .about-contact-email {
    margin-top: 12px;
    font-weight: 600;
    color: #2e7d32;
}
/* ==== RESPONSIVE ==== */
@media(max-width:900px){
    #about-vksolar .about-grid {
        flex-direction: column;
    }
    #about-vksolar .about-list-grid {
        grid-template-columns: 1fr;
    }
}
/* ==== PRINT ==== */
@media print {
    /* About section basic print setup */
    #about-vksolar {
        padding: 7mm;
        background: #ffffff;
    }
    #about-vksolar .about-card {
        max-width: 100%;
        padding: 6mm 7mm;
        border-radius: 10px;
        box-shadow: none;
    }
    /* ⭐ Force side-by-side layout on print, even if mobile rule hai */
    #about-vksolar .about-grid {
        display: flex !important;
        flex-direction: row !important;
        align-items: flex-start;
        gap: 6mm;
    }
    #about-vksolar .about-info {
        flex: 1 1 55%;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #about-vksolar .about-image {
        flex: 1 1 45%;
        text-align: center;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #about-vksolar .about-image img {
        max-width: 100%;
        max-height: 100%;   /* zarurat ho to 55mm/50mm kar sakte ho */
        height: auto;
    }
    /* Text size thoda compact so dono column ek page pe aa jayein */
    #about-vksolar .about-list {
        font-size: 12pt;
    }
    #about-vksolar .about-stats .stat-num {
        font-size: 16pt;
    }
}
/* ========== COMPANY + CUSTOMER DETAILS (SCREEN) ========== */
#company-customer {
    padding: 40px 20px;
    background: #f5f8f5;
}
#company-customer .details-card {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    padding: 24px 26px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}
#company-customer .details-grid {
    display: flex;
    gap: 24px;
    align-items: stretch;
}
#company-customer .details-panel {
    flex: 1 1 50%;
    background: #ffffff;
    border-radius: 18px;
    border-left: 5px solid #2e7d32;
    padding: 18px 20px 20px;
    font-size: 14px;
    line-height: 1.7;
    display: flex;
    flex-direction: column;
}
#company-customer .details-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px;
}
#company-customer .details-heading {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}
#company-customer .details-subtitle {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 4px;
}
#company-customer .details-block {
    margin-bottom: 10px;
}
#company-customer hr {
    border: 0;
    border-top: 1px solid #e0e0e0;
    margin: 10px 0;
}
/* responsive: stack on small screens */
@media (max-width: 900px) {
    #company-customer .details-grid {
        flex-direction: column;
    }
}
/* ========== PRINT (A4 LANDSCAPE, SIDE BY SIDE, FULL HEIGHT, BIG FONT) ========== */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
    #company-customer {
        padding: 4mm 4mm;
        background: #ffffff;
    }
    #company-customer .details-card {
        max-width: 100%;
        padding: 4mm 5mm;
        border-radius: 0;
        box-shadow: none;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    /* force side-by-side in print */
    #company-customer .details-grid {
        display: flex !important;
        flex-direction: row !important;
        gap: 4mm;
        align-items: stretch;
        height: 100%;
    }
    /* make both panels tall – almost full page height */
    #company-customer .details-panel {
        min-height: 170mm;        /* adjust if needed */
        font-size: 12pt;          /* big readable font */
        line-height: 1.5;
        border-radius: 4mm;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    #company-customer .details-title {
        font-size: 16pt;
        margin-bottom: 3mm;
    }
    #company-customer .details-heading {
        font-size: 13pt;
        margin-bottom: 2mm;
    }
    #company-customer .details-subtitle {
        font-size: 12.5pt;
        margin-bottom: 1.5mm;
    }
    #company-customer p {
        margin: 0 0 2mm;
    }
    #company-customer hr {
        margin: 2mm 0;
    }
}
</style>
</head>
<body style="overflow-x: hidden;">
     <button class="print-btn" onclick="downloadPDF()">Download PDF</button>
     <button class="back-btn" onclick="goBackToForm()">Back to Form</button>
     
    <section id="cover-page" class="newcontainer">
        <img src="firstimage.png" class="cover-page" style="border-radius: 5rem;width: 100%; margin-left:0px;" alt="">
    </section>
    <!-- CUSTOMER COMPANY DETAILS -->
 
 <section id="company-customer" class="newcontainer print-keep-together">
    <div class="details-card">
        <div class="details-grid">
            <!-- LEFT: COMPANY DETAILS -->
            <div class="details-panel">
                <h2 class="details-title">Company Details</h2>
                <div class="details-block">
                    <div class="details-heading">VK SOLAR ENERGY</div>
                    <p>
                        Authorized Channel Partner :
                        <strong>KIRLOSKAR SOLAR TECHNOLOGY PVT. LTD.</strong><br>
                        Registered &amp; Approved by <strong>MSEDCL, MSME</strong><br>
                        GSTIN No: <strong>27CJXPK1402Q1ZK</strong>
                    </p>
                </div>
                <hr>
                <div class="details-block">
                    <div class="details-subtitle">Office Address</div>
                    <p>
                        NEAR DR. A.V. JOSHI CLINIC, KHADGAON ROAD, KOHALE LAYOUT,<br>
                        WADI, NAGPUR – 440023
                    </p>
                </div>
                <hr>
                <div class="details-block">
                    <div class="details-subtitle">Contact Information</div>
                    <p>
                        <strong>Phone:</strong> 9075305275 / 9657135476<br>
                        <strong>Email:</strong> vksolarenergy1989@gmail.com
                    </p>
                </div>
            </div>
            <!-- RIGHT: CUSTOMER DETAILS + SYSTEM INFO -->
            <div class="details-panel">
                <h2 class="details-title">Customer Details</h2>
                <div class="details-block">
                    <div class="details-heading"><?php echo htmlspecialchars($customer_name); ?></div>
                    <p>
                        <strong>Phone :</strong> <?php echo htmlspecialchars($contact); ?><br>
                        <strong>Email :</strong> <?php echo htmlspecialchars($email); ?><br>
                        <strong>Property :</strong> <?php echo htmlspecialchars($property_type); ?><br>
                        <strong>Roof Type :</strong> <?php echo htmlspecialchars($roof_type); ?><br>
                        <strong>Monthly Bill :</strong> ₹<?php echo number_format($current_monthly_bill, 0); ?>
                    </p>
                </div>
                <hr>
                <div class="details-block">
                    <div class="details-subtitle">System Information</div>
                    <p>
                        <strong>System Size :</strong> <?php echo htmlspecialchars($system_size); ?> kW<br>
                        <strong>System Type :</strong> <?php echo htmlspecialchars(ucfirst($system_type)); ?><br>
                        <strong>Panel Company :</strong> <?php echo htmlspecialchars($panel_company); ?><br>
                        <strong>Inverter Company :</strong> <?php echo htmlspecialchars($inverter_company); ?><br>
                        <strong>Monthly Savings :</strong> ₹<?php echo number_format($estimated_monthly_savings, 0); ?><br>
                        <strong>Payback Period :</strong> <?php echo htmlspecialchars($payback_period); ?> years
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
    
<section id="about-vksolar" class="newcontainer print-keep-together">
    <div class="about-card">
        <div class="about-grid">
            <!-- LEFT: TEXT -->
            <div class="about-info">
                <h2 class="section-title">About <span class="accent">VK Solar Energy</span></h2>
                <div class="about-list-grid">
                    <ul class="about-list">
                        <li><strong>Authorized Channel Partner</strong> of Kirloskar Solar Technology Pvt. Ltd.</li>
                        <li><strong>Registered &amp; Approved</strong> by MSEDCL &amp; MSME</li>
                        <li><strong>GSTIN:</strong> 27CJXPK1402Q1ZK</li>
                    </ul>
                    <ul class="about-list">
                        <li>Expert team for <strong>SLD, Design Layout &amp; DPR Preparation</strong></li>
                        <li>End-to-end EPC with <strong>online monitoring &amp; commissioning</strong></li>
                        <li>Strong vendor network for <strong>premium components &amp; support</strong></li>
                    </ul>
                </div>
                <div class="about-stats">
                    <div class="about-stat">
                        <div class="stat-num">245+</div>
                        <div class="stat-label">Customers Served</div>
                    </div>
                    <div class="about-stat">
                        <div class="stat-num">198+</div>
                        <div class="stat-label">Projects Completed</div>
                    </div>
                    <div class="about-stat">
                        <div class="stat-num">94%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                    </div>
                </div>
                <div class="about-contact-email">
                    vksolarenergy1989@gmail.com
                </div>
            </div>
            <!-- RIGHT: IMAGE (same Off-Grid / On-Grid / Hybrid image) -->
            <div class="about-image">
                <img src="solarimage.png" alt="Off-Grid, On-Grid &amp; Hybrid Solar System" />
            </div>
        </div>
    </div>
</section>

<!-- ========== 1. MATERIALS DETAILS ========== -->
<section id="materials-details" class="newcontainer print-keep-together">
    <div class="materials-card">
        <div class="page-title">SOLAR SYSTEM MATERIALS DETAILS</div>
        <div class="materials-table-wrapper">
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
                        <td>24V, <?php echo $panel_wattage; ?>Wp</td>
                        <td><?php echo $panel_count; ?></td>
                        <td><?php echo strtoupper($panel_company); ?></td>
                    </tr>
                    <tr>
                        <td>Solar Grid Inverter</td>
                        <td>1 PH, <?php echo ceil($system_size); ?> KW</td>
                        <td><?php echo $inverter_quantity; ?></td>
                        <td><?php echo strtoupper($inverter_company); ?></td>
                    </tr>
                    <tr>
                        <td>Online monitoring</td>
                        <td>Lan Cable/WIFI Based</td>
                        <td>01</td>
                        <td><?php echo strtoupper($inverter_company); ?></td>
                    </tr>
                    <tr>
                        <td>Fabricated Structure Panel mounting</td>
                        <td>Modified Rooftop structure GI/AL Purlin Height-8 TO 6 Feet</td>
                        <td><?php echo number_format($actual_system_size, 1); ?> kW</td>
                        <td>FURTUNE/APPOLO</td>
                    </tr>
                    <tr>
                        <td>Solar Cables – For AC purpose &amp; Accessories</td>
                        <td>2 core, 4 sq mm insulated wire</td>
                        <td>As required</td>
                        <td>Polycab / RR</td>
                    </tr>
                    <tr>
                        <td>Solar Cables – For DC purpose &amp; accessories</td>
                        <td>1 core, 4 sq mm insulated wire</td>
                        <td>As required</td>
                        <td>RR / POLYCAB</td>
                    </tr>
                    <tr>
                        <td>AC side Breaker with ACDB</td>
                        <td>Input terminal with 20A, Enclosure with IP65 protection</td>
                        <td>01</td>
                        <td>SPD- HAVELS / ABB MCB-C&amp;S / L&amp;T</td>
                    </tr>
                    <tr>
                        <td>DC side Breaker with DCDB</td>
                        <td>Positive terminal with 20A, Enclosure with IP65 protection</td>
                        <td>01</td>
                        <td>D-ELMEX Fuse- ELMEX</td>
                    </tr>
                    <tr>
                        <td>Net meter &amp; Gen Meter</td>
                        <td><?php echo $meter_type; ?></td>
                        <td>As required</td>
                        <td>HPL OR SECURE MSEDCL Approved</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="specifications">
            <h2 class="spec-title">System Specifications</h2>
            <div class="spec-grid">
                <div class="spec-item">
                    <strong>Total System Capacity</strong>
                    <span><?php echo number_format($actual_system_size, 2); ?> kWp (<?php echo $panel_count; ?> x <?php echo $panel_wattage; ?>Wp modules)</span>
                </div>
                <div class="spec-item">
                    <strong>Inverter Type</strong>
                    <span><?php echo ucfirst($inverter_type); ?> Inverter</span>
                </div>
                <div class="spec-item">
                    <strong>Inverter Capacity</strong>
                    <span><?php echo $inverter_capacity; ?> (<?php echo $inverter_quantity; ?> units)</span>
                </div>
                <div class="spec-item">
                    <strong>Module Technology</strong>
                    <span>Bificial Half-Cut Solar Cells</span>
                </div>
                <div class="spec-item">
                    <strong>Property Type</strong>
                    <span><?php echo ucfirst($property_type); ?> - <?php echo ucfirst($roof_type); ?> Roof</span>
                </div>
                <div class="spec-item">
                    <strong>Total Investment</strong>
                    <span>₹<?php echo number_format($investment, 0); ?> (After Subsidy: ₹<?php echo number_format($investment - $subsidy_amount, 0); ?>)</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== 2. COSTING DETAILS + TERMS ========== -->
<section id="costing-details" class="newcontainer print-keep-together">
    <div class="costing-card">
        <div class="page-title">SOLAR SYSTEM COSTING DETAILS</div>
        <div class="materials-table-wrapper">
            <table class="materials-table costing-table">
                <thead>
                    <tr>
                        <th>Sr</th>
                        <th>Description</th>
                        <th>Capacity</th>
                        <th>Unit</th>
                        <th></th>
                        <th>Rate (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Design, Supply, Installation &amp; Commissioning of Grid Tied solar PV system</td>
                        <td><?php echo number_format($actual_system_size, 1); ?> kW</td>
                        <td>kWp</td>
                        <td></td>
                        <td>₹<?php echo number_format($investment, 0); ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Load Extension Charges</td>
                        <td>EXTRA</td>
                        <td>ACTUAL</td>
                        <td></td>
                        <td>As Actual</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sub Total</td>
                        <td>₹<?php echo number_format($investment, 0); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>GST @ 8.5%</td>
                        <td>₹<?php echo number_format($investment * 0.085, 0); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Subsidy/Discount %</td>
                        <td>₹<?php echo number_format($subsidy_amount, 0); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Grand Total</td>
                        <td>₹<?php echo number_format($investment - $subsidy_amount, 0); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h3 class="section-subheading"><span class="accent">Terms &amp; Conditions</span></h3>
        <ul class="terms-list">
            <li><strong>Load Extension:</strong> If required will be charged extra and payment as actual will be on customer scope.</li>
            <li><strong>Taxes:</strong> GST @ 13.8% on System.</li>
            <li><strong>Validity:</strong> Above rates are valid for 15 days from the date of offer and subject to our written confirmation thereafter.</li>
            <li><strong>Delivery, Installation &amp; Commissioning:</strong> Within 5 to 6 weeks from the date of confirmed Purchase Order and advance payment in favor of OM SAI RAM ENTERPRISES.</li>
            <li><strong>Payment Terms:</strong> Advance: 30% with order | 60% at the time of dispatch of Inverters | 10% after commissioning.</li>
            <li><strong>Cables, Wire and Accessories:</strong> Included. <strong>Transportation:</strong> Included.</li>
            <li><strong>Monitoring:</strong> Remote monitoring system included in the cost.</li>
            <li><strong>Civil work and Fabrication:</strong> Civil work and fabrication work for solar system required is in our scope.</li>
            <li><strong>Net Meter:</strong> Net meter included in the cost. <strong>Lightning Arrester:</strong> Included in the cost.</li>
            <li><strong>Metering Cubicle CT &amp; PT:</strong> Upgradation or replacement of Metering Cubical CT PT and accessories will be in client scope.</li>
            <li><strong>On Grid Failure:</strong> The plant will cease to feed supply to the grid until the grid is restored.</li>
            <li><strong>Interruption in Work:</strong> The Customer must not interrupt the works, and/or shall abstain from any act or omission which can reasonably be expected to delay the works.</li>
            <li><strong>Warranty:</strong> 10 years warranty for PV Inverters &amp; 1 year for Balance of System.</li>
            <li><strong>For Loan Purpose Only:</strong> Transit Insurance is in our scope.</li>
        </ul>
    </div>
</section>

<!-- ========== 3. BANK DETAILS & SCOPE OF WORK ========== -->
<section id="bank-details" class="newcontainer print-keep-together">
    <div class="bank-card">
        <div class="page-title">BANK DETAILS &amp; SCOPE OF WORK</div>
        <div class="bank-grid">
            <!-- BOX 1: Bank Details -->
            <div class="bank-box">
                <h3 class="bank-box-title">Bank Details</h3>
                <table class="bank-mini-table">
                    <tr>
                        <td class="label">Name</td>
                        <td class="value">VK SOLAR ENERGY</td>
                    </tr>
                    <tr>
                        <td class="label">Bank Name</td>
                        <td class="value">HDFC BANK DATAWAIDINGP</td>
                    </tr>
                    <tr>
                        <td class="label">Account No</td>
                        <td class="value">50200065621522</td>
                    </tr>
                    <tr>
                        <td class="label">IFSC Code</td>
                        <td class="value">HDFC0004224</td>
                    </tr>
                </table>
            </div>
            <!-- BOX 2: Cost Breakdown -->
            <div class="bank-box">
                <h3 class="bank-box-title">Cost Breakdown</h3>
                <table class="bank-mini-table">
                    <tr>
                        <td class="label">System Cost</td>
                        <td class="value">₹<?php echo number_format($investment, 0); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Subsidy</td>
                        <td class="value">₹<?php echo number_format($subsidy_amount, 0); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Final Cost</td>
                        <td class="value">₹<?php echo number_format($investment - $subsidy_amount, 0); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Monthly Savings</td>
                        <td class="value">₹<?php echo number_format($estimated_monthly_savings, 0); ?></td>
                    </tr>
                </table>
            </div>
            <!-- BOX 3: Scope of Work -->
            <div class="bank-box">
                <h3 class="bank-box-title">Scope of Work</h3>
                <ul class="scope-list">
                    <li>Licensing for Net metering approval and commissioning</li>
                    <li>Designing of Solar PV Plant</li>
                    <li>Supply and installation of module mounting structure</li>
                    <li>Installation of PV modules</li>
                    <li>Supply and installation of inverters, distribution boards, energy meters etc.</li>
                    <li>Supply and installation of associated cables and electrical works</li>
                    <li>Commissioning and trial run-out of solar plant</li>
                </ul>
            </div>
            <!-- BOX 4: Exclusions -->
            <div class="bank-box">
                <h3 class="bank-box-title">Exclusions</h3>
                <ul class="exclusion-list">
                    <li>Materials, components, tools, design or software provided by client</li>
                    <li>Negligence or wilful misconduct of client</li>
                    <li>Improper service work or alterations by third party</li>
                    <li>Any trial or experiment without written consent</li>
                    <li>Damages due to manual tampering or natural calamity</li>
                </ul>
            </div>
        </div>
    </div>
</section>
    
    <section id="description" class="newcontainer">
        <div class="page-title">OUR VALUE ADDED COUSTOMERS</div>
        <table class="materials-table">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>EPC Client</th>
                    <th>Size of System (KW)</th>
                </tr>
            </thead>
            <tbody>
                 <tbody>
              <tr><td>1</td><td>TANIYA INDUSTRIES SAONER, NAGPUR</td><td>348 WP</td></tr>
              <tr><td>2</td><td>JMD ENGINEERING, MIDC HINGNA, NAGPUR</td><td>60 KW</td></tr>
              <tr><td>3</td><td>ZILHA PARISHAD, GADCHIROLI</td><td>60 KW</td></tr>
              <tr><td>4</td><td>YOGESHWAR STONE CRUSHER, AMBAD JALNA</td><td>50 KW</td></tr>
              <tr><td>5</td><td>ISHWARI STONE CRUSHER, ABMAD, JALNA</td><td>50 KW</td></tr>
              <tr><td>6</td><td>HOTEL VRUNDAWAN AMBAD, JALNA</td><td>20 KW</td></tr>
              <tr><td>7</td><td>HOTEL RAGHUVIR RESTAURENT, NAGPUR</td><td>15 KW</td></tr>
              <tr><td>8</td><td>VIDARBH LIQUAR CORPORATION</td><td>50 KW</td></tr>
              <tr><td>9</td><td>MR. PRASHANT RASEKAR SIR SAONER, NAGPUR</td><td>18 KW</td></tr>
              <tr><td>10</td><td>MR. SIDDHARTH PRASAD SIR</td><td>12 KW</td></tr>
              <tr><td>11</td><td>MR. PRANAY GEDAM SIR, SAHKAR NAGAR</td><td>18 KW</td></tr>
              <tr><td>12</td><td>MR. MARAIN SIR, SADAR NAGPUR</td><td>10 KW</td></tr>
              <tr><td>13</td><td>SWAMI SAMARTH HOSPITAL, TRIMURTI NAGAR NAGPUR</td><td>18 KW</td></tr>
              <tr><td>14</td><td>HITUL PATEL SIR, SAONER NAGPUR</td><td>10 KW</td></tr>
              <tr><td>15</td><td>MR. INDRANARAYAN TIWARI, GOREWADA, NAGPUR</td><td>6 KW</td></tr>
              <tr><td>16</td><td>MR. DEEPAK BANSOD SIR, SHANKAR NAGAR NAGPUR</td><td>5 KW</td></tr>
              <tr><td>17</td><td>YOGESH CHAKRADHARE, MANISH NAGAR NAGPUR</td><td>5 KW</td></tr>
              <tr><td>18</td><td>SADANAND SHETTE, MEDICAL SQUARE NAGPUR</td><td>4 KW</td></tr>
              <tr><td>19</td><td>MR. PRADIP PATIL SIR, JAITALA, NAGPUR</td><td>5 KW</td></tr>
              <tr><td>20</td><td>MR. AYAZ AHMED, GOREWADA, NAGPUR</td><td>3 KW</td></tr>
              <tr><td>21</td><td>MR. SHASHANK KAMBLE, SUBODH NAGAR, NAGPUR</td><td>5 KW</td></tr>
              <tr><td>22</td><td>RAHUL DHOBLE, WADHAMANA, NAGPUR</td><td>6 KW</td></tr>
            </tbody>
            </tbody>
        </table>
    </section>

<section id="roi-savings" class="newcontainer print-keep-together">
    <div class="roi-card">
        <h2 class="roi-heading">ROI & Savings Projection</h2>
        <p class="roi-subtitle">
            The following projection is an indicative estimate based on expected solar generation and current electricity tariff.
            Actual savings may vary as per site conditions, usage pattern and tariff revisions.
        </p>
        <!-- Top summary badges -->
        <div class="roi-summary">
            <div class="roi-badge">
                <span class="label">System Size</span>
                <span class="value"><?php echo $system_size; ?> kWp</span>
            </div>
            <div class="roi-badge">
                <span class="label">Approx. Investment</span>
                <span class="value">₹ <?php echo number_format($investment, 0); ?></span>
            </div>
            <div class="roi-badge">
                <span class="label">Estimated Payback</span>
                <span class="value">~ <?php echo $payback_period; ?> Years</span>
            </div>
        </div>
        <!-- Table -->
        <div class="roi-table-wrapper">
            <table class="roi-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th style="width: 10%;">Year</th>
                        <th style="width: 25%;">Estimated Generation (kWh)</th>
                        <th style="width: 25%;">Estimated Savings / Year (₹)</th>
                        <th style="width: 25%;">Cumulative Savings (₹)</th>
                        <th style="width: 15%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?php echo number_format($system_size * 1460, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings, 0); ?></td>
                        <td>System commissioned</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><?php echo number_format($system_size * 1460 * 0.98, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 0.98, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 1.98, 0); ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><?php echo number_format($system_size * 1460 * 0.96, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 0.96, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 2.94, 0); ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr class="roi-payback-row">
                        <td>4</td>
                        <td><?php echo number_format($system_size * 1460 * 0.94, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 0.94, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 3.88, 0); ?></td>
                        <td><strong>Approx. Payback Achieved</strong></td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td><?php echo number_format($system_size * 1460 * 0.85, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 0.85, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 8.5, 0); ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>25</td>
                        <td><?php echo number_format($system_size * 1460 * 0.80, 0); ?></td>
                        <td>₹ <?php echo number_format($estimated_yearly_savings * 0.80, 0); ?></td>
                        <td>₹ <?php echo number_format($savings_25_years, 0); ?></td>
                        <td>End of panel performance warranty</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="roi-footer-notes">
            <p><strong>Assumptions:</strong></p>
            <ul>
                <li>Average specific generation: 1460 units/kWp/year (location dependent).</li>
                <li>Average grid tariff considered: ₹ <?php echo number_format($current_monthly_bill / 500, 2); ?> per kWh with periodic increase.</li>
                <li>Losses due to temperature, wiring and system degradation have been factored in.</li>
            </ul>
        </div>
    </div>
</section>

<section id="govt-subsidy" class="newcontainer print-keep-together">
    <div class="subsidy-card">
        <h2 class="subsidy-heading">Government Scheme & Subsidy Eligibility</h2>
        <p class="subsidy-subtitle">
            The proposed solar rooftop system may be eligible for central / state government incentives,
            subject to prevailing <strong>MNRE</strong> and <strong>DISCOM</strong> guidelines at the time of application.
        </p>
        <!-- Summary badges -->
        <div class="subsidy-summary">
            <div class="subsidy-badge">
                <span class="label">Scheme Name</span>
                <span class="value">PM Surya Ghar – Rooftop Solar</span>
            </div>
            <div class="subsidy-badge">
                <span class="label">Consumer Category</span>
                <span class="value"><?php echo ucfirst($property_type); ?> Rooftop</span>
            </div>
            <div class="subsidy-badge">
                <span class="label">Subsidy Amount</span>
                <span class="value">₹<?php echo number_format($subsidy_amount, 0); ?></span>
            </div>
        </div>
        <!-- Capacity vs subsidy slab -->
        <div class="subsidy-table-wrapper">
            <table class="subsidy-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th style="width: 20%;">Slab</th>
                        <th style="width: 25%;">System Capacity</th>
                        <th style="width: 25%;">Subsidy*</th>
                        <th style="width: 30%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Up to 3 kW</td>
                        <td>Up to 40% on benchmark cost</td>
                        <td>For eligible residential consumers</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Above 3 kW to 10 kW</td>
                        <td>Up to 20% on additional capacity</td>
                        <td>As per scheme &amp; DISCOM policy</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Above 10 kW</td>
                        <td>As per latest notification</td>
                        <td>Subject to technical feasibility</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Checklist + notes -->
        <div class="subsidy-bottom">
            <div class="subsidy-column">
                <h3 class="subsidy-small-title">Basic Eligibility Checklist</h3>
                <ul class="subsidy-list">
                    <li>Consumer must have an active electricity connection in their name.</li>
                    <li>Rooftop / site should be technically suitable for solar installation.</li>
                    <li>System capacity should be within DISCOM-approved limits.</li>
                    <li>Consumer must apply through the official portal / DISCOM process.</li>
                </ul>
            </div>
            <div class="subsidy-column">
                <h3 class="subsidy-small-title">Documents Generally Required</h3>
                <ul class="subsidy-list">
                    <li>Latest electricity bill copy.</li>
                    <li>Identity &amp; address proof of consumer.</li>
                    <li>Property ownership / NOC (if applicable).</li>
                    <li>Signed application &amp; vendor details as per DISCOM format.</li>
                </ul>
            </div>
        </div>
        <p class="subsidy-note">
            *The above subsidy values are indicative placeholders. Actual subsidy amount and eligibility
            will depend on the latest government notifications and DISCOM regulations at the time of
            application. Our team will assist you in the complete subsidy application process.
        </p>
    </div>
</section>

    <section id="partner-benefits" class="newcontainer print-keep-together">
    <div class="benefits-inner">
        <h2 class="benefits-heading">
            VK Solar Energy partner program
            <span class="benefits-highlight">benefits</span>
        </h2>
        <p class="benefits-subtitle">
            Partnering with VK Solar Energy comes with multiple benefits ensuring guaranteed
            business success. We take
            <span class="text-highlight">
                immense pride in empowering our EPC partners.
            </span>
        </p>
        <div class="benefits-grid">
            <!-- 1 -->
            <div class="benefit-card">
                <div class="benefit-icon">💰</div>
                <div class="benefit-content">
                    <h3>Easy Solar Loans</h3>
                    <p>
                        Easy solar loans for your customers &amp; supply chain financing for you,
                        helping close more projects.
                    </p>
                </div>
            </div>
            <!-- 2 -->
            <div class="benefit-card">
                <div class="benefit-icon">⏱️</div>
                <div class="benefit-content">
                    <h3>Timely Payment</h3>
                    <p>
                        We ensure that payments reach directly to your bank on time, securing your cash flows.
                    </p>
                </div>
            </div>
            <!-- 3 -->
            <div class="benefit-card">
                <div class="benefit-icon">📱</div>
                <div class="benefit-content">
                    <h3>Digital Platform</h3>
                    <p>
                        End-to-end digital platform makes every step for you &amp; your customer
                        hassle-free, building trust &amp; confidence.
                    </p>
                </div>
            </div>
            <!-- 4 -->
            <div class="benefit-card">
                <div class="benefit-icon">💼</div>
                <div class="benefit-content">
                    <h3>Seamless Procurement</h3>
                    <p>
                        Our ecommerce platform is your one-stop shop for all solar hardware needs
                        at best price &amp; wide range of options.
                    </p>
                </div>
            </div>
            <!-- 5 -->
            <div class="benefit-card">
                <div class="benefit-icon">🧩</div>
                <div class="benefit-content">
                    <h3>Design Support</h3>
                    <p>
                        VK Solar Energy design experts help you with technical support, enabling
                        you to better serve your customer.
                    </p>
                </div>
            </div>
            <!-- 6 -->
            <div class="benefit-card">
                <div class="benefit-icon">📊</div>
                <div class="benefit-content">
                    <h3>Asset Monitoring</h3>
                    <p>
                        AeROC monitoring portal is a centralized platform for all your solar
                        assets at one place.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
    
     <section id="description" class="newcontainer print-keep-together" > 
     <div class="page-title">OUR QUALITY SYSTEM HIGHLIGHTS</div>
        <div class="slide" style="background-color:#f1f8e9;">
                <div class="left">
                <div>
                    <div class="stats">
                        <div class="stat">
                            <div class="sub">High-Efficiency Solar Panels (Mono PERC / Half-Cut)</div>
                        </div>
                        <div class="stat">
                            <div class="sub">Net Metering Compatible (as per local DISCOM guidelines)</div>
                        </div>
                        <div class="stat">
                            <div class="sub">Advanced MPPT-Based Solar Inverter</div>
                        </div>
                        <div class="stat">
                            <div class="sub">Weather-Resistant Anodized Aluminium Mounting Structure</div>
                        </div>
                         <div class="stat">
                            <div class="sub">Comprehensive Safety & Protection System</div>
                        </div>
                         <div class="stat">
                            <div class="sub">25 Years Panel Performance Warranty & 5-10 Years Inverter Warranty</div>
                        </div>
                    </div>
                </div>
                 <div class="stat-img">
                  <img src="solarimage1.png" alt="Solar Rooftop System" />
                </div>
                </div>
        </div>
    </section>
    
    <section id="vk-partner" class="newcontainer print-keep-together">
    <div class="vk-hero-card">
        <!-- Left: Image -->
        <div class="vk-hero-image">
            <img src="solarimage3.png" alt="VK Solar Energy Engineer">
            <div class="vk-hero-tag">
                🌞 95% Efficiency
            </div>
        </div>
        <!-- Right: Content -->
        <div class="vk-hero-content">
            <p class="vk-hero-eyebrow">About VK Solar Energy</p>
            <h2 class="vk-hero-title">
                Authorized Channel Partner of Kirloskar
                Solar Technology Private Limited – Your
                Trusted Energy Solution in Maharashtra
            </h2>
            <p class="vk-hero-text">
                With <strong>10+ years of expertise</strong>, VK Solar Energy delivers
                <strong>cutting-edge solar solutions</strong>, empowering homes and businesses
                with government schemes, financing, and world-class warranties.
            </p>
            <ul class="vk-hero-list">
                <li>🏛 PM Surya Ghar Scheme Benefits</li>
                <li>⭐ High Efficiency Solar Panels</li>
                <li>⚙️ Advanced Multi-Bus Bar Technology</li>
                <li>₹ 90% Loan & EMI Options</li>
            </ul>
        </div>
    </div>
</section>

<section id="partner-contact" class="newcontainer print-keep-together">
    <div class="partner-card">
        <h2 class="partner-heading">Join Our Solar Partner Network</h2>
         <p class="partner-subtitle">
            I have shared the detailed quotation above. I would appreciate it if you could update me on your decision once you have reviewed it.
        </p>
        <div class="partner-cols">
            <!-- Left: Contact Information -->
            <div class="partner-box">
                <h3 class="partner-box-title">Contact Information</h3>
                <ul class="partner-list">
                    <li>📧 vksolarenergy1989@gmail.com</li>
                    <li>📞 +91  9075305275 / 9657135476</li>
                    <li>📍 NEAR DR. A.V. JOSHI CLINIC, KHADGAON ROAD, KOHALE LAYOUT,<br>
                        WADI, NAGPUR – 440023</li>
                </ul>
            </div>
            <!-- Right: Generated by -->
            <div class="partner-box">
                <h3 class="partner-box-title">Generated by</h3>
                <p><span class="label">Vendor Name:</span> <span><?php echo htmlspecialchars($vendor_name ?: 'Not specified'); ?></span></p>
                <p><span class="label">Vendor Contact:</span> <span><?php echo htmlspecialchars($vendor_contact ?: 'Not specified'); ?></span></p>
                <p><span class="label">Vendor Email:</span> <span><?php echo htmlspecialchars($vendor_email ?: 'Not specified'); ?></span></p>
            </div>
        </div>
    </div>
    <div class="partner-card">
        <h2 class="partner-heading">Provide Your Response</h2>
        <p class="partner-subtitle">
            Please share your decision regarding this quotation. Your response helps us proceed quickly.
        </p>
        <div class="response-buttons">
            <a href="accept_quotation.html" class="response-btn accept">
                ✔ Accept Quotation
            </a>
            <a href="reject_quotation.html" class="response-btn reject">
                ✖ Reject Quotation
            </a>
            <a href="review_quotation.html" class="response-btn review">
                ⏳ Under Review
            </a>
        </div>
    </div>
</section>

<script>
function goBackToForm() {
    // Back to form par session clear nahi karenge, taki user apna data edit kar sake
    window.location.href = 'quotation_generator.php';
}

function downloadPDF() {
    // PDF download karne ke baad session clear karenge
    window.print();
    
    // PDF download hone ke baad fresh form dikhane ke liye
    setTimeout(function() {
        // Session clear karne ke liye redirect
        window.location.href = 'quotation_generator.php?clear=1';
    }, 1000);
}
</script>
</body>
</html>