<?php
// view_quotation.php  (SINGULAR)
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'view');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "connect/db.php"; // gives $conn (mysqli)

// 1. Validate quotation id
if (!isset($_GET['quote_id']) || !is_numeric($_GET['quote_id'])) {
    die("Invalid quotation ID.");
}

$quotationId = (int) $_GET['quote_id'];

// 2. Fetch record
$sql = "SELECT * FROM solar_rooftop_quotations WHERE quotation_id = $quotationId";
$result = $conn->query($sql);

if (!$result) {
    die("SQL error: " . $conn->error);
}

if ($result->num_rows === 0) {
    die("Quotation not found.");
}

$q = $result->fetch_assoc();

// Small helpers
function yn($v) {
    return $v ? 'Yes' : 'No';
}
function moneyOrDash($v) {
    if ($v === null || $v === '') return '-';
    return '₹ ' . number_format((float)$v, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        View Quotation - <?= htmlspecialchars($q['quote_number']); ?>
    </title>
    <?php require('include/head.php'); ?>

    <style>
        .vk-detail-wrapper {
            max-width: 1200px;
            margin: 20px auto 30px;
        }
        .vk-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 15px;
        }
        .vk-detail-header-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 4px;
        }
        .vk-detail-meta {
            font-size: 13px;
            color: #666;
        }
        .badge-status {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 999px;
            display: inline-block;
        }
        .badge-status.sent          { background:#e3f2fd; color:#1565c0; }
        .badge-status.approved      { background:#e8f5e9; color:#2e7d32; }
        .badge-status.declined      { background:#ffebee; color:#c62828; }
        .badge-status.under_review  { background:#fff8e1; color:#ef6c00; }

        .vk-section-card {
            background:#ffffff;
            border-radius:10px;
            padding:16px 18px;
            margin-bottom:14px;
            box-shadow:0 2px 6px rgba(0,0,0,0.05);
        }
        .vk-section-title {
            font-size:16px;
            font-weight:700;
            margin:0 0 8px;
            border-bottom:1px solid #eee;
            padding-bottom:6px;
        }
        .vk-detail-table {
            width:100%;
            border-collapse:collapse;
            font-size:14px;
        }
        .vk-detail-table td {
            padding:4px 6px;
            vertical-align:top;
        }
        .vk-detail-table td.label {
            width:32%;
            font-weight:600;
            color:#555;
        }
        .vk-detail-table td.value {
            width:68%;
            color:#222;
        }

        .vk-actions-bar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            margin:20px 0 10px;
        }

        @media (max-width: 768px) {
            .vk-detail-wrapper {
                margin:10px auto 20px;
                padding:0 10px;
            }
            .vk-detail-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .vk-actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .vk-actions-bar a {
                width:100%;
                margin-bottom:4px;
            }
        }
    </style>
</head>
<body>

<?php require('include/sidebar.php'); ?>

<div id="main-content">
    <?php require('include/navbar.php'); ?>

    <div class="vk-detail-wrapper">

        <!-- TOP HEADER -->
        <div class="vk-detail-header">
            <div>
                <h1 class="vk-detail-header-title">
                    Quotation Details
                </h1>
                <div class="vk-detail-meta">
                    Quote No: <strong><?= htmlspecialchars($q['quote_number']); ?></strong>
                    &nbsp;|&nbsp;
                    Customer: <strong><?= htmlspecialchars($q['customer_name']); ?></strong>
                    <br>
                    Created: <?= htmlspecialchars($q['created_date']); ?>
                    <?php if (!empty($q['updated_date'])): ?>
                        &nbsp;|&nbsp; Last Updated: <?= htmlspecialchars($q['updated_date']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <?php
                $status = $q['status'] ?? 'sent';
                $statusClass = 'sent';
                if ($status === 'approved')       $statusClass = 'approved';
                elseif ($status === 'declined')   $statusClass = 'declined';
                elseif ($status === 'under_review') $statusClass = 'under_review';
                ?>
                <span class="badge-status <?= $statusClass; ?>">
                    <?= ucfirst(str_replace('_',' ', $status)); ?>
                </span>
            </div>
        </div>

        <!-- CUSTOMER DETAILS -->
        <div class="row ">
            <div class="vk-section-card col-sm-5 me-2">
                <h2 class="vk-section-title">Customer Details</h2>
                <table class="vk-detail-table">
                    <tr>
                        <td class="label">Customer Name</td>
                        <td class="value"><?= htmlspecialchars($q['customer_name']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Phone</td>
                        <td class="value"><?= htmlspecialchars($q['customer_phone']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Email</td>
                        <td class="value"><?= htmlspecialchars($q['customer_email']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Address</td>
                        <td class="value"><?= nl2br(htmlspecialchars($q['customer_address'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Property Type</td>
                        <td class="value"><?= htmlspecialchars(ucfirst($q['property_type'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Roof Type</td>
                        <td class="value"><?= htmlspecialchars(ucfirst($q['roof_type'])); ?></td>
                    </tr>
                    <?php if (!empty($q['roof_area_sqft'])): ?>
                    <tr>
                        <td class="label">Roof Area (sq.ft)</td>
                        <td class="value"><?= htmlspecialchars($q['roof_area_sqft']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Meter Type</td>
                        <td class="value">
                            <?= htmlspecialchars($q['meter_type'] ?: '-'); ?>
                        </td>
                    </tr>
                </table>
            </div>
    
            <!-- QUOTATION PREPARER -->
            <div class="vk-section-card col-sm-5 me-2">
                <h2 class="vk-section-title">Quotation Prepared By</h2>
                <table class="vk-detail-table">
                    <tr>
                        <td class="label">Prepared By</td>
                        <td class="value"><?= htmlspecialchars($q['prepared_by']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Preparer Address</td>
                        <td class="value"><?= nl2br(htmlspecialchars($q['preparer_address'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Preparer Contact</td>
                        <td class="value"><?= htmlspecialchars($q['preparer_contact']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Preparer Email</td>
                        <td class="value"><?= htmlspecialchars($q['preparer_email']); ?></td>
                    </tr>
                </table>
            </div>
     
            <!-- SYSTEM CONFIGURATION -->
            <div class="vk-section-card col-sm-5 me-2">
                <h2 class="vk-section-title">System Configuration</h2>
                <table class="vk-detail-table">
                    <tr>
                        <td class="label">System Type</td>
                        <td class="value"><?= htmlspecialchars(ucfirst($q['system_type'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">System Size</td>
                        <td class="value">
                            <?= htmlspecialchars($q['system_size_kwp']); ?> kWp
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Average Monthly Bill</td>
                        <td class="value">
                            <?= moneyOrDash($q['monthly_bill']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Panel Company</td>
                        <td class="value"><?= htmlspecialchars(ucfirst($q['panel_company'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Panel Wattage</td>
                        <td class="value">
                            <?= htmlspecialchars($q['panel_wattage']); ?> W
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Panel Quantity</td>
                        <td class="value"><?= htmlspecialchars($q['panel_count']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Inverter Company</td>
                        <td class="value"><?= htmlspecialchars(ucfirst($q['inverter_company'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Inverter Type</td>
                        <td class="value"><?= htmlspecialchars($q['inverter_type']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Inverter Capacity</td>
                        <td class="value">
                            <?= htmlspecialchars($q['inverter_capacity']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Inverter Quantity</td>
                        <td class="value"><?= htmlspecialchars($q['inverter_count']); ?></td>
                    </tr>
                </table>
            </div>
    
            <!-- FINANCIAL SUMMARY -->
            <div class="vk-section-card col-sm-5 me-2">
                <h2 class="vk-section-title">Financial Summary</h2>
                <table class="vk-detail-table">
                    <tr>
                        <td class="label">Total Cost</td>
                        <td class="value"><?= moneyOrDash($q['total_cost']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Subsidy</td>
                        <td class="value"><?= moneyOrDash($q['subsidy']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Final Cost</td>
                        <td class="value"><?= moneyOrDash($q['final_cost']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Estimated Monthly Savings</td>
                        <td class="value"><?= moneyOrDash($q['monthly_savings']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Payback Period</td>
                        <td class="value">
                            <?php
                            echo ($q['payback_period'] !== null && $q['payback_period'] !== '')
                                ? htmlspecialchars($q['payback_period']) . " Years"
                                : '-';
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
    
            <!-- ADDITIONAL COMPONENTS -->
            <div class="vk-section-card col-sm-5 me-2">
                <h2 class="vk-section-title">Additional Components</h2>
                <table class="vk-detail-table">
                    <tr>
                        <td class="label">Battery Backup</td>
                        <td class="value"><?= yn($q['battery_backup']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Smart Monitoring</td>
                        <td class="value"><?= yn($q['smart_monitoring']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Annual Maintenance</td>
                        <td class="value"><?= yn($q['annual_maintenance']); ?></td>
                    </tr>
                </table>
            </div>
    
            <!-- ACTION BUTTONS -->
            <div class="vk-actions-bar col-sm-5 me-2">
                <a href="view_quotations.php" class="btn btn-secondary btn-sm">
                    ← Back to Quotations
                </a>
    
                <div>
                    <a href="edit_quotation.php?quote_id=<?= $quotationId; ?>"
                       class="btn btn-warning btn-sm">
                        Edit
                    </a>
                    <a href="delete_quotation.php?quote_id=<?= $quotationId; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this quotation?');">
                        Delete
                    </a>
                   <a href="merged_template.php?quote_id=<?= $row['quotation_id']; ?>" 
                       class="btn btn-success btn-xs" target="_blank">
                        Generate Quotation
                    </a>

                </div>
            </div>
        
        </div>

    </div>
</div>

</body>
</html>
