<?php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'create');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "connect/db.php"; // yahi wala jo bhi tum use karte ho

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Not a POST request";
    exit;
}

// Helper to clean POST
function post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/* 1. READ FORM FIELDS */

// Customer
$firstName  = post('firstName');
$lastName   = post('lastName');
$customerName = trim($firstName . ' ' . $lastName);
$email      = post('email');
$phone      = post('phone');
$address    = post('address');

// Quotation Preparer (User who is generating quotation)
$preparedByName    = post('prepared_by');
$preparedByAddress = post('preparer_address');
$preparedByContact = post('preparer_contact');
$preparedByEmail   = post('preparer_email');

// System
$electricityBill = (float) post('electricityBill', 0);
$systemType      = post('systemType');
$systemSize      = (float) post('systemSize', 0);
$panelCompany    = post('panelCompany');
$panelModel      = post('panelModel');
$panelQuantity   = (int) post('panelQuantity', 0);
$inverterCompany = post('inverterCompany');
$inverterType = post('inverterType');
$inverterCapacity=post('inverterCapacity');
$inverterQuantity=post('inverterQuantity');
$propertyType  = post('propertyType');
$meterType     = post('meterType');
$roofType      = post('roofType');

// Finance
$totalAmount   = (float) post('totalAmount', 0);
$subsidyAmount = (float) post('subsidyAmount', 0);


// Checkboxes
$batteryBackup     = isset($_POST['battery_backup']) ? 1 : 0;
$smartMonitoring   = isset($_POST['smart_monitoring']) ? 1 : 0;
$annualMaintenance = isset($_POST['annual_maintenance']) ? 1 : 0;

// Quick validation
if ($customerName === '' || $phone === '' || $address === '') {
    die("Required fields missing (name / phone / address).");
}

/* 2. CALCULATED FIELDS (simple for now) */

$panelWattage = (int) filter_var($panelModel, FILTER_SANITIZE_NUMBER_INT);
$finalCost      = $totalAmount - $subsidyAmount;
$monthlySavings = 0;
$paybackPeriod  = 0;

$status     = 'sent';
$customerId = 0;  // abhi separate customer table use nahi kar rahe
$tempQuote  = 'TEMP-' . uniqid();

/* 3. INSERT INTO TABLE (simple query, no bind_param headache) */

// NOTE: ye INSERT un columns pe based hai jo pehle wali table design me bataye the.
// Agar tumne table ka naam/columns change kiye hain to yaha bhi change karo.

$customerNameEsc = $conn->real_escape_string($customerName);
$phoneEsc        = $conn->real_escape_string($phone);
$emailEsc        = $conn->real_escape_string($email);
$addressEsc      = $conn->real_escape_string($address);
$propertyTypeEsc = $conn->real_escape_string($propertyType);
$roofTypeEsc     = $conn->real_escape_string($roofType);
$panelCompanyEsc = $conn->real_escape_string($panelCompany);
$inverterCompanyEsc = $conn->real_escape_string($inverterCompany);
$systemTypeEsc   = $conn->real_escape_string($systemType);

$sql = "
INSERT INTO solar_rooftop_quotations
(quote_number, status, customer_id, customer_name, customer_phone, customer_email, customer_address, prepared_by, preparer_address, preparer_contact, preparer_email,
 property_type, roof_type, monthly_bill,
 panel_company, panel_wattage, panel_count,
 inverter_company,inverter_type,inverter_capacity,inverter_count,system_type, system_size_kwp,meter_type,
 total_cost, subsidy, final_cost, monthly_savings, payback_period,battery_backup, smart_monitoring, annual_maintenance)
VALUES
('$tempQuote', '$status', $customerId, '$customerNameEsc', '$phoneEsc', '$emailEsc', '$addressEsc', '$preparedByName', '$preparedByAddress', '$preparedByContact', '$preparedByEmail',
 '$propertyTypeEsc', '$roofTypeEsc', $electricityBill,
 '$panelCompanyEsc', $panelWattage, $panelQuantity,
 '$inverterCompanyEsc','$inverterType','$inverterCapacity','$inverterQuantity', '$systemTypeEsc', $systemSize, '$meterType',
 $totalAmount, $subsidyAmount, $finalCost, $monthlySavings, $paybackPeriod,'$batteryBackup', '$smartMonitoring', '$annualMaintenance')
";
if (!$conn->query($sql)) {
    die("Insert failed: " . $conn->error . "<br>SQL: " . $sql);
}

$quotationId = $conn->insert_id;

/* 4. UPDATE REAL QUOTE NUMBER FORMAT: SOL-NA-YEAR-MONTH-ID */

$cityCode = "NA"; // abhi form me city nahi hai, baad me field add karke yahan use kar sakte ho
$year  = date('Y');
$month = date('m');
$finalQuoteNumber = "SOL-$cityCode-$year-$month-$quotationId";

$updSql = "UPDATE solar_rooftop_quotations SET quote_number = '$finalQuoteNumber' WHERE quotation_id = $quotationId";

if (!$conn->query($updSql)) {
    die("Quote number update failed: " . $conn->error);
}

/* 5. REDIRECT TO VIEW PAGE */

header("Location: view_quotations.php");
exit;
