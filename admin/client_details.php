<?php
include "include/auth_session.php";
include "connect/db1.php";
include "connect/fun.php";

$connect = new connect();
$fun = new fun($connect->dbconnect());

// Get client ID from URL
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($client_id <= 0) {
    header("Location: view_clients.php");
    exit;
}

// Fetch client data
$client_data = [];
$result = $fun->fetchClientById($client_id);
if($result && mysqli_num_rows($result) > 0) {
    $client_data = mysqli_fetch_assoc($result);
} else {
    header("Location: view_clients.php");
    exit;
}

// Fetch client documents
function getClientDocuments($db, $clientId) {
    $sql = "SELECT document_type, file_path, file_name FROM client_documents WHERE client_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $documents = [];
    while($row = $result->fetch_assoc()) {
        $documents[$row['document_type']] = $row;
    }
    return $documents;
}

$client_documents = getClientDocuments($connect->dbconnect(), $client_id);

// Fetch solar panels
function getSolarPanels($db, $clientId) {
    $sql = "SELECT panel_number,  company_name, wattage,serial_number FROM solar_panels WHERE client_id = ? ORDER BY panel_number";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $panels = [];
    while($row = $result->fetch_assoc()) {
        $panels[] = $row;
    }
    return $panels;
}

$solar_panels = getSolarPanels($connect->dbconnect(), $client_id);

// Helper function to format value display
function formatValue($value) {
    if ($value === null || $value === '') {
        return '<span class="text-muted">Not provided</span>';
    }
    return htmlspecialchars($value);
}

// Helper function to format date
function formatDate($date) {
    if (!$date || $date == '0000-00-00') {
        return '<span class="text-muted">Not provided</span>';
    }
    return date('d M Y', strtotime($date));
}

// Helper function to format amount
function formatAmount($amount) {
    if (!$amount || $amount == 0) {
        return '<span class="text-muted">Not provided</span>';
    }
    return '₹' . number_format($amount, 2);
}

// Helper function to get document web URL
function getDocumentWebUrl($filePath) {
    if (!$filePath) return null;
    return str_replace('../../', '../', $filePath);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Details - Solar Quick</title>
    <?php include "include/head.php"; ?>
    <style>
        .info-card {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-row {
            border-bottom: 1px solid #f8f9fa;
            padding: 12px 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .document-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .status-complete {
            background-color: #28a745;
            color: white;
        }
        .status-incomplete {
            background-color: #6c757d;
            color: white;
        }
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- ======= Sidebar ======= -->
    <?php include "include/sidebar.php"; ?>
    <!-- End Sidebar-->
    
    <!-- Main Content -->
    <div id="main-content">
    

        <!-- Fixed Header -->
        <?php require('include/navbar.php') ?>
    <!-- End Sidebar-->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Client Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="view_clients.php">View Clients</a></li>
                    <li class="breadcrumb-item active">Client Details</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <!-- Back Button -->
            <div class="back-btn">
                <a href="view_clients.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Client List
                </a>
                <a href="workflow.php?client_id=<?php echo $client_id; ?>&step=1&edit_mode=1" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Client
                </a>
            </div>

            <!-- Client Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card info-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="card-title"><?php echo htmlspecialchars($client_data['name']); ?></h3>
                                    <p class="card-text">
                                        <strong>Consumer Number:</strong> <?php echo formatValue($client_data['consumer_number']); ?><br>
                                        <strong>Client ID:</strong> #<?php echo $client_id; ?><br>
                                        <strong>Registered:</strong> <?php echo date('d M Y, h:i A', strtotime($client_data['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="status-badge <?php echo ($client_data['step14_completed'] ?? false) ? 'status-complete' : 'status-incomplete'; ?>">
                                        <?php echo ($client_data['step14_completed'] ?? false) ? 'Complete' : 'In Progress'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Basic Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 1: Basic Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo formatValue($client_data['name']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Consumer Number</div>
                                    <div class="info-value"><?php echo formatValue($client_data['consumer_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Billing Unit</div>
                                    <div class="info-value"><?php echo formatValue($client_data['billing_unit']); ?></div>
                                </div>
                                <div class="col-md-12 info-row">
                                    <div class="info-label">Location URL</div>
                                    <div class="info-value">
                                        <?php if(!empty($client_data['location'])): ?>
                                            <a href="<?php echo htmlspecialchars($client_data['location']); ?>" target="_blank">View Location</a>
                                        <?php else: ?>
                                            <?php echo formatValue($client_data['location']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Communication & Address -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 2: Communication & Address</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Mobile</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mobile']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo formatValue($client_data['email']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">District</div>
                                    <div class="info-value"><?php echo formatValue($client_data['district']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Block</div>
                                    <div class="info-value"><?php echo formatValue($client_data['block']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Taluka</div>
                                    <div class="info-value"><?php echo formatValue($client_data['taluka']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Village</div>
                                    <div class="info-value"><?php echo formatValue($client_data['village']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: MAHADISCOM Email & Mobile Update -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 3: MAHADISCOM Email & Mobile Update</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">MAHADISCOM Email</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mahadiscom_email']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Email Password</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mahadiscom_email_password']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">MAHADISCOM Mobile</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mahadiscom_mobile']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: MAHADISCOM Registration -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 4: MAHADISCOM Registration</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 info-row">
                                    <div class="info-label">MAHADISCOM User ID</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mahadiscom_user_id']); ?></div>
                                </div>
                                <div class="col-md-6 info-row">
                                    <div class="info-label">MAHADISCOM Password</div>
                                    <div class="info-value"><?php echo formatValue($client_data['mahadiscom_password']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Name Change Require -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 5: Name Change Require</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Name Change Required</div>
                                    <div class="info-value">
                                        <?php 
                                        $nameChange = $client_data['name_change_require'] ?? 'no';
                                        echo $nameChange === 'yes' ? 'Yes' : 'No';
                                        ?>
                                    </div>
                                </div>
                                <?php if($nameChange === 'yes'): ?>
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Application Number</div>
                                    <div class="info-value"><?php echo formatValue($client_data['application_no_name_change']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: PM Suryaghar Registration -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 6: PM Suryaghar Registration</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">PM Suryaghar Registration</div>
                                    <div class="info-value">
                                        <?php 
                                        $pmReg = $client_data['pm_suryaghar_registration'] ?? 'no';
                                        echo $pmReg === 'yes' ? 'Yes' : 'No';
                                        ?>
                                    </div>
                                </div>
                                <?php if($pmReg === 'yes'): ?>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Application ID</div>
                                    <div class="info-value"><?php echo formatValue($client_data['pm_suryaghar_app_id']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Registration Date</div>
                                    <div class="info-value"><?php echo formatDate($client_data['pm_registration_date']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 7: MAHADISCOM Sanction Load -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 7: MAHADISCOM Sanction Load</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Load Change Application No.</div>
                                    <div class="info-value"><?php echo formatValue($client_data['load_change_application_number']); ?></div>
                                </div>
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Rooftop Solar Application No.</div>
                                    <div class="info-value"><?php echo formatValue($client_data['rooftop_solar_application_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Kilowatt</div>
                                    <div class="info-value"><?php echo formatValue($client_data['kilo_watt']); ?> kW</div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Load Change Status</div>
                                    <div class="info-value"><?php echo formatValue($client_data['load_change_status']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 8: Bank Loan -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 8: Bank Loan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Bank Loan Status</div>
                                    <div class="info-value">
                                        <?php 
                                        $bankLoan = $client_data['bank_loan_status'] ?? 'no';
                                        echo $bankLoan === 'yes' ? 'Yes' : 'No';
                                        ?>
                                    </div>
                                </div>
                                <?php if($bankLoan === 'yes'): ?>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Bank Name</div>
                                    <div class="info-value"><?php echo formatValue($client_data['bank_name']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Account Number</div>
                                    <div class="info-value"><?php echo formatValue($client_data['account_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">IFSC Code</div>
                                    <div class="info-value"><?php echo formatValue($client_data['ifsc_code']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Jan Samartha App No.</div>
                                    <div class="info-value"><?php echo formatValue($client_data['jan_samartha_application_no']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Loan Amount</div>
                                    <div class="info-value"><?php echo formatAmount($client_data['loan_amount']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 9: Fitting Photos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 9: Fitting Photos</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Inverter Company</div>
                                    <div class="info-value"><?php echo formatValue($client_data['inverter_company_name']); ?></div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Uploaded Photos</h6>
                                    <div class="row">
                                        <?php
                                        $fittingPhotos = ['solar_panel_photo', 'inverter_photo', 'geotag_photo'];
                                        foreach($fittingPhotos as $photoType):
                                            if(isset($client_documents[$photoType])):
                                                $doc = $client_documents[$photoType];
                                                $docUrl = getDocumentWebUrl($doc['file_path']);
                                                $isImage = $docUrl && (strpos($docUrl, '.jpg') !== false || strpos($docUrl, '.jpeg') !== false || strpos($docUrl, '.png') !== false);
                                        ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-center">
                                                <?php if($isImage): ?>
                                                    <img src="<?php echo $docUrl; ?>" alt="<?php echo $photoType; ?>" class="document-thumb">
                                                <?php else: ?>
                                                    <div class="document-thumb bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-file-earmark-image text-muted" style="font-size: 24px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <small class="text-muted"><?php echo ucwords(str_replace('_', ' ', $photoType)); ?></small><br>
                                                    <a href="<?php echo $docUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 10: PM SuryaGhar Document Upload -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 10: PM SuryaGhar Document Upload</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Inverter Company</div>
                                    <div class="info-value"><?php echo formatValue($client_data['inverter_company_name']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Inverter Serial No.</div>
                                    <div class="info-value"><?php echo formatValue($client_data['inverter_serial_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">DCR Certificate No.</div>
                                    <div class="info-value"><?php echo formatValue($client_data['dcr_certificate_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Number of Panels</div>
                                    <div class="info-value"><?php echo formatValue($client_data['number_of_panels']); ?></div>
                                </div>
                            </div>

                            <!-- Solar Panels -->
                            <?php if(!empty($solar_panels)): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Solar Panel Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Panel No.</th>
                                                    <th>Serial Number</th>
                                                    <th>Wattage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($solar_panels as $panel): ?>
                                                <tr>
                                                    <td><?php echo $panel['panel_number']; ?></td>
                                                    <td><?php echo formatValue($panel['serial_number']); ?></td>
                                                    <td><?php echo formatValue($panel['wattage']); ?>W</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- PM Documents -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Uploaded Documents</h6>
                                    <div class="row">
                                        <?php
                                        $pmDocuments = [
                                            'aadhar' => 'Aadhar Card',
                                            'pan_card' => 'PAN Card',
                                            'electric_bill' => 'Electricity Bill',
                                            'bank_passbook' => 'Bank Passbook',
                                            'model_agreement' => 'Model Agreement',
                                            'dcr_certificate' => 'DCR Certificate',
                                            'bank_statement' => 'Bank Statement',
                                            'salary_slip' => 'Salary Slip',
                                            'it_return' => 'IT Return',
                                            'gumasta' => 'Gumasta License'
                                        ];
                                        
                                        foreach($pmDocuments as $docType => $docLabel):
                                            if(isset($client_documents[$docType])):
                                                $doc = $client_documents[$docType];
                                                $docUrl = getDocumentWebUrl($doc['file_path']);
                                                $isImage = $docUrl && (strpos($docUrl, '.jpg') !== false || strpos($docUrl, '.jpeg') !== false || strpos($docUrl, '.png') !== false);
                                        ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-center">
                                                <?php if($isImage): ?>
                                                    <img src="<?php echo $docUrl; ?>" alt="<?php echo $docLabel; ?>" class="document-thumb">
                                                <?php else: ?>
                                                    <div class="document-thumb bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 24px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <small class="text-muted"><?php echo $docLabel; ?></small><br>
                                                    <a href="<?php echo $docUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 11: RTS Portal Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 11: RTS Portal Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 info-row">
                                    <div class="info-label">RTS Portal Documents Updated</div>
                                    <div class="info-value">
                                        <?php 
                                        $rtsStatus = $client_data['rts_portal_status'] ?? 'no';
                                        echo $rtsStatus === 'yes' ? 'Yes' : 'No';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 12: Meter Installation Photo -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 12: Meter Installation Photo</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Meter Number</div>
                                    <div class="info-value"><?php echo formatValue($client_data['meter_number']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Installation Date</div>
                                    <div class="info-value"><?php echo formatDate($client_data['meter_installation_date']); ?></div>
                                </div>
                            </div>
                            <?php if(isset($client_documents['meter_photo'])): 
                                $meterDoc = $client_documents['meter_photo'];
                                $meterUrl = getDocumentWebUrl($meterDoc['file_path']);
                                $isMeterImage = $meterUrl && (strpos($meterUrl, '.jpg') !== false || strpos($meterUrl, '.jpeg') !== false || strpos($meterUrl, '.png') !== false);
                            ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Meter Installation Photo</h6>
                                    <div class="text-center">
                                        <?php if($isMeterImage): ?>
                                            <img src="<?php echo $meterUrl; ?>" alt="Meter Installation" class="document-thumb">
                                        <?php else: ?>
                                            <div class="document-thumb bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-file-earmark-image text-muted" style="font-size: 24px;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="<?php echo $meterUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Photo</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 13: PM Suryaghar Redeem Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 13: PM Suryaghar Redeem Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Subsidy Redeemed</div>
                                    <div class="info-value">
                                        <?php 
                                        $redeemStatus = $client_data['pm_redeem_status'] ?? 'no';
                                        echo $redeemStatus === 'yes' ? 'Yes' : 'No';
                                        ?>
                                    </div>
                                </div>
                                <?php if($redeemStatus === 'yes'): ?>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Subsidy Amount</div>
                                    <div class="info-value"><?php echo formatAmount($client_data['subsidy_amount']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Redeem Date</div>
                                    <div class="info-value"><?php echo formatDate($client_data['subsidy_redeem_date']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if(isset($client_documents['subsidy_redeem'])): 
                                $subsidyDoc = $client_documents['subsidy_redeem'];
                                $subsidyUrl = getDocumentWebUrl($subsidyDoc['file_path']);
                                $isSubsidyImage = $subsidyUrl && (strpos($subsidyUrl, '.jpg') !== false || strpos($subsidyUrl, '.jpeg') !== false || strpos($subsidyUrl, '.png') !== false);
                            ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Subsidy Redeem Proof</h6>
                                    <div class="text-center">
                                        <?php if($isSubsidyImage): ?>
                                            <img src="<?php echo $subsidyUrl; ?>" alt="Subsidy Redeem Proof" class="document-thumb">
                                        <?php else: ?>
                                            <div class="document-thumb bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 24px;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="<?php echo $subsidyUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Document</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 14: Reference -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Step 14: Reference</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Reference Name</div>
                                    <div class="info-value"><?php echo formatValue($client_data['reference_name']); ?></div>
                                </div>
                                <div class="col-md-6 info-row">
                                    <div class="info-label">Reference Contact</div>
                                    <div class="info-value"><?php echo formatValue($client_data['reference_contact']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Solar Type</div>
                                    <div class="info-value"><?php echo formatValue($client_data['solar_type']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Estimate Amount</div>
                                    <div class="info-value"><?php echo formatAmount($client_data['estimate_amount']); ?></div>
                                </div>
                                <div class="col-md-4 info-row">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($client_data['updated_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
    <!-- ======= Footer ======= -->
    <?php include "include/footer.php"; ?>
    <!-- End Footer -->
</body>
</html>