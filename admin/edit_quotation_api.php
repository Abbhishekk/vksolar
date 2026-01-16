<?php
// edit_quotation_api.php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'edit');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "connect/db.php"; // gives $conn (mysqli) from Database class

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: view_quotations.php");
    exit;
}

function post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

if (!isset($_POST['quotation_id']) || !is_numeric($_POST['quotation_id'])) {
    die("Invalid quotation ID.");
}

$quotationId = (int) $_POST['quotation_id'];

/* 1. Read all form fields */

// Customer
$firstName    = post('firstName');
$lastName     = post('lastName');
$customerName = trim($firstName . ' ' . $lastName);
$email        = post('email');
$phone        = post('phone');
$address      = post('address');

// Preparer details
$preparedBy       = post('prepared_by');
$preparerAddress  = post('preparer_address');
$preparerContact  = post('preparer_contact');
$preparerEmail    = post('preparer_email');

// System configuration
$electricityBill = (float) post('electricityBill', 0); // monthly_bill
$systemType      = post('systemType');                 // system_type
$systemSize      = (float) post('systemSize', 0);      // system_size_kwp

$panelCompany   = post('panelCompany');
$panelModel     = post('panelModel');                  // here watt numeric (300, 325, ...)
$panelQuantity  = (int) post('panelQuantity', 0);

$inverterType      = post('inverterType');
$inverterCompany   = post('inverterCompany');
$inverterCapacity  = post('inverterCapacity');         // numeric string (1,2,3,..)
$inverterQuantity  = (int) post('inverterQuantity', 0);

// Financial
$totalAmount   = (float) post('totalAmount', 0);       // total_cost
$subsidyAmount = (float) post('subsidyAmount', 0);     // subsidy
$propertyType  = post('propertyType');
$meterType     = post('meterType');
$roofType      = post('roofType');

// Checkboxes → tinyint(1)
$batteryBackup     = isset($_POST['battery_backup']) ? 1 : 0;
$smartMonitoring   = isset($_POST['smart_monitoring']) ? 1 : 0;
$annualMaintenance = isset($_POST['annual_maintenance']) ? 1 : 0;

/* 2. Basic validation */

if ($customerName === '' || $phone === '' || $address === '') {
    die("Required fields missing: customer name / phone / address.");
}

/* 3. Calculated fields */

// panel_wattage = numeric model (e.g. "550")
$panelWattage = (int) $panelModel;

// final_cost
$finalCost = $totalAmount - $subsidyAmount;

// Simple savings logic (same pattern as your original require.php top logic)
$estimatedMonthlySavings = $electricityBill * 0.75;  // 75% bill saving assumption
$estimatedYearlySavings  = $estimatedMonthlySavings * 12;

// payback_period (in years) – guard against divide by zero
$paybackPeriod = 0.0;
if ($estimatedYearlySavings > 0) {
    $paybackPeriod = round($totalAmount / $estimatedYearlySavings, 1);
}

// We keep monthly_savings = estimatedMonthlySavings
$monthlySavings = $estimatedMonthlySavings;

/* 4. Escape strings for SQL */

$customerNameEsc   = $conn->real_escape_string($customerName);
$phoneEsc          = $conn->real_escape_string($phone);
$emailEsc          = $conn->real_escape_string($email);
$addressEsc        = $conn->real_escape_string($address);

$preparedByEsc      = $conn->real_escape_string($preparedBy);
$preparerAddressEsc = $conn->real_escape_string($preparerAddress);
$preparerContactEsc = $conn->real_escape_string($preparerContact);
$preparerEmailEsc   = $conn->real_escape_string($preparerEmail);

$roofTypeEsc      = $conn->real_escape_string($roofType);
$propertyTypeEsc  = $conn->real_escape_string($propertyType);
$meterTypeEsc     = $conn->real_escape_string($meterType);

$panelCompanyEsc    = $conn->real_escape_string($panelCompany);
$inverterCompanyEsc = $conn->real_escape_string($inverterCompany);
$inverterTypeEsc    = $conn->real_escape_string($inverterType);
$inverterCapacityEsc= $conn->real_escape_string($inverterCapacity);
$systemTypeEsc      = $conn->real_escape_string($systemType);

/* 5. Build UPDATE query – matches solar_rooftop_quotations columns */

$sql = "
UPDATE solar_rooftop_quotations SET
    customer_name      = '$customerNameEsc',
    customer_phone     = '$phoneEsc',
    customer_email     = '$emailEsc',
    customer_address   = '$addressEsc',

    prepared_by        = '$preparedByEsc',
    preparer_address   = '$preparerAddressEsc',
    preparer_contact   = '$preparerContactEsc',
    preparer_email     = '$preparerEmailEsc',

    roof_type          = '$roofTypeEsc',
    property_type      = '$propertyTypeEsc',
    meter_type         = '$meterTypeEsc',

    monthly_bill       = $electricityBill,

    panel_wattage      = $panelWattage,
    panel_count        = $panelQuantity,
    panel_company      = '$panelCompanyEsc',

    inverter_company   = '$inverterCompanyEsc',
    inverter_capacity  = '$inverterCapacityEsc',
    inverter_type      = '$inverterTypeEsc',
    inverter_count     = $inverterQuantity,

    system_size_kwp    = $systemSize,
    system_type        = '$systemTypeEsc',

    total_cost         = $totalAmount,
    subsidy            = $subsidyAmount,
    final_cost         = $finalCost,
    monthly_savings    = $monthlySavings,
    payback_period     = $paybackPeriod,

    battery_backup     = $batteryBackup,
    smart_monitoring   = $smartMonitoring,
    annual_maintenance = $annualMaintenance
WHERE quotation_id = $quotationId
";

if (!$conn->query($sql)) {
    die("Update failed: " . $conn->error . "<br><pre>$sql</pre>");
}

// Optionally: success message in session
$_SESSION['flash_success'] = "Quotation #$quotationId updated successfully.";

// Redirect back to quotation list
header("Location: view_quotations.php");
exit;
