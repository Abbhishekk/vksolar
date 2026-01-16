<?php
// admin/customer_status.php
require_once "connect/auth_middleware.php";
require_once "connect/db.php";
$auth->requirePermission('customer_management', 'view');

// Use the existing database connection
$conn = $database->conn;

// Function to get workflow statistics
function getWorkflowStatistics($conn) {
    $stats = [];
    $totalClients = 0;
    
    // Get total clients count
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM clients");
    if ($totalResult) {
        $totalRow = $totalResult->fetch_assoc();
        $totalClients = $totalRow['total'];
    }
    
    // Step 1: Basic Details - Complete if name and consumer_number are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE name IS NOT NULL AND name != '' AND consumer_number IS NOT NULL AND consumer_number != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['basic'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 2: Communication & Address - Complete if mobile, email, district are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE mobile IS NOT NULL AND mobile != '' AND email IS NOT NULL AND email != '' AND district IS NOT NULL AND district != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['communication'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 3: MAHADISCOM Email & Mobile Update - Complete if all three fields are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE mahadiscom_email IS NOT NULL AND mahadiscom_email != '' AND mahadiscom_email_password IS NOT NULL AND mahadiscom_email_password != '' AND mahadiscom_mobile IS NOT NULL AND mahadiscom_mobile != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['mahadiscom_email_mobile'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 4: MAHADISCOM Registration - Complete if both fields are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE mahadiscom_user_id IS NOT NULL AND mahadiscom_user_id != '' AND mahadiscom_password IS NOT NULL AND mahadiscom_password != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['mahadiscom_registration'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 5: Name Change Require - Complete if name_change_require is set AND if yes, application_no_name_change is filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE name_change_require IS NOT NULL AND (name_change_require = 'no' OR (name_change_require = 'yes' AND application_no_name_change IS NOT NULL AND application_no_name_change != ''))";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['name_change'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 6: PM Suryaghar Registration - Complete if pm_suryaghar_registration is set AND if yes, required fields are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE pm_suryaghar_registration IS NOT NULL AND (pm_suryaghar_registration = 'no' OR (pm_suryaghar_registration = 'yes' AND pm_suryaghar_app_id IS NOT NULL AND pm_suryaghar_app_id != '' AND pm_registration_date IS NOT NULL))";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['pm_registration'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 7: MAHADISCOM Sanction Load - Complete if both application numbers are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE load_change_application_number IS NOT NULL AND load_change_application_number != '' AND rooftop_solar_application_number IS NOT NULL AND rooftop_solar_application_number != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['sanction_load'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 8: Bank Loan - Complete if bank_loan_status is set AND if yes, required fields are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE bank_loan_status IS NOT NULL AND (bank_loan_status = 'no' OR (bank_loan_status = 'yes' AND bank_name IS NOT NULL AND bank_name != '' AND account_number IS NOT NULL AND account_number != ''))";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['bank_loan'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 9: Fitting Photos - Complete if inverter company name is filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE inverter_company_name IS NOT NULL AND inverter_company_name != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['fitting_photos'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 10: PM SuryaGhar Document Upload - Complete if dcr certificate number is filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE dcr_certificate_number IS NOT NULL AND dcr_certificate_number != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['pm_documents'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 11: RTS Portal Status - Complete if rts_portal_status is set
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE rts_portal_status IS NOT NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['rts_status'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 12: Meter Installation Photo - Complete if meter number is filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE meter_number IS NOT NULL AND meter_number != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['meter_photos'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 13: PM Suryaghar Redeem Status - Complete if pm_redeem_status is set AND if yes, subsidy details are filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE pm_redeem_status IS NOT NULL AND (pm_redeem_status = 'no' OR (pm_redeem_status = 'yes' AND subsidy_amount IS NOT NULL AND subsidy_amount > 0 AND subsidy_redeem_date IS NOT NULL))";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['pm_redeem'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    // Step 14: Reference - Complete if reference name is filled
    $sql = "SELECT COUNT(*) as complete FROM clients WHERE reference_name IS NOT NULL AND reference_name != ''";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['reference'] = [
        'complete' => $row['complete'] ?? 0,
        'incomplete' => $totalClients - ($row['complete'] ?? 0)
    ];
    
    return $stats;
}

// Get workflow statistics
$workflowStats = getWorkflowStatistics($conn);
$totalClients = $conn->query("SELECT COUNT(*) as total FROM clients")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Workflow Status - VK Solar Energy</title>
<?php require('include/head.php'); ?>

    <style>
        .workflow-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .complete-count {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2em;
        }
        .incomplete-count {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.2em;
        }
        .issue-count {
            background-color: #ffc107;
            color: #856404;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .stats-row {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 0;
        }
        .total-clients-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
  
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php include "include/sidebar.php"; ?>
    <!-- End Sidebar--> <!-- Main Content -->
  <div id="main-content"> 

    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Customer Workflow Status</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Workflow Status</li>
                </ol>
            </nav>
        </div>

        <!-- Total Clients Card -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="total-clients-card">
                    <div class="card-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="card-title">TOTAL CLIENTS</div>
                    <div class="card-value"><?php echo $totalClients; ?></div>
                    <div class="card-change">
                        <i class="bi bi-arrow-up"></i> All clients workflow status
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Dashboard Section -->
        <div class="workflow-section">
            <div class="row">
                <div class="col-12">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-solar-panel text-warning"></i>
                        Customer Workflow Dashboard (14 Steps)
                    </h4>
                </div>
            </div>

            <div class="row">
                <?php
                // Define the 14-step workflow structure
                $workflowSteps = [
                    1 => ['id' => 'basic', 'title' => 'Basic Details'],
                    2 => ['id' => 'communication', 'title' => 'Communication & Address'],
                    3 => ['id' => 'mahadiscom_email_mobile', 'title' => 'MAHADISCOM Email & Mobile Update'],
                    4 => ['id' => 'mahadiscom_registration', 'title' => 'MAHADISCOM Registration'],
                    5 => ['id' => 'name_change', 'title' => 'Name Change Require'],
                    6 => ['id' => 'pm_registration', 'title' => 'PM Suryaghar Portal Registration'],
                    7 => ['id' => 'sanction_load', 'title' => 'MAHADISCOM Sanction Load'],
                    8 => ['id' => 'bank_loan', 'title' => 'Bank Loan'],
                    9 => ['id' => 'fitting_photos', 'title' => 'Fitting Photos'],
                    10 => ['id' => 'pm_documents', 'title' => 'PM SuryaGhar Document Upload'],
                    11 => ['id' => 'rts_status', 'title' => 'RTS Portal Status'],
                    12 => ['id' => 'meter_photos', 'title' => 'Meter Installation Photo'],
                    13 => ['id' => 'pm_redeem', 'title' => 'PM Suryaghar Redeem Status'],
                    14 => ['id' => 'reference', 'title' => 'Reference']
                ];

                foreach($workflowSteps as $step => $stepData):
                    $complete = $workflowStats[$stepData['id']]['complete'] ?? 0;
                    $incomplete = $workflowStats[$stepData['id']]['incomplete'] ?? 0;
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card dashboard-card" onclick="navigateToStep(<?php echo $step; ?>)" style="cursor: pointer;">
                        <div class="card-header text-white"><?php echo $step . '. ' . $stepData['title']; ?></div>
                        <div class="card-body">
                            <div class="row stats-row">
                                <div class="col-6 text-center">
                                    <div class="complete-count" id="<?php echo $stepData['id']; ?>Complete">
                                        <?php echo $complete; ?>
                                    </div>
                                    <small class="text-muted">Complete</small>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="incomplete-count" id="<?php echo $stepData['id']; ?>Incomplete">
                                        <?php echo $incomplete; ?>
                                    </div>
                                    <small class="text-muted">Incomplete</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                            <span>Progress:</span>
                            <span class="issue-count">
                                <?php 
                                $total = $complete + $incomplete;
                                $percentage = $total > 0 ? round(($complete / $total) * 100) : 0;
                                echo $percentage . '%';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- End Workflow Section -->
    <!-- ======= Footer ======= -->
    <?php include "include/footer.php"; ?>
    </main>
</div>


    <script>
        function navigateToStep(stepNumber) {
            console.log('Navigating to step:', stepNumber);
            window.location.href = 'workflow.php?step=' + stepNumber;
        }

        // Add click event to all dashboard cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach((card, index) => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const step = index + 1;
                    console.log('Card clicked, step:', step);
                    window.location.href = 'workflow.php?step=' + step;
                });
                
                // Visual feedback
                card.style.cursor = 'pointer';
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.08)';
                });
            });
        });
    </script>
</body>
</html>