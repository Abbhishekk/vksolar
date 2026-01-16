<?php 
include "../../connect/db.php";
include "../../connect/fun.php";
include 'include/auth_session.php';

$connect = new connect();
$fun = new fun($connect->dbconnect());

$id = $_GET['id'] ?? 0;
$client = null;
$existingPanels = [];

// Fetch client data
if($id) {
    $result = $fun->fetchClientById($id);
    if(mysqli_num_rows($result) > 0) {
        $client = mysqli_fetch_assoc($result);
        
        // Fetch existing panels
        $panelsResult = $fun->getClientPanels($id);
        while($panel = mysqli_fetch_assoc($panelsResult)) {
            $existingPanels[] = $panel;
        }
    }
}

$message = '';

// Handle form submission
if($_POST && $client){
    // File upload handling for client photos
    if($_FILES){
        $upload_dir = "uploads/";
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Handle geo tagged photo upload
        if($_FILES['geo_tagged_photo']['name']){
            $geo_photo_name = time() . '_' . basename($_FILES['geo_tagged_photo']['name']);
            $geo_photo_path = $upload_dir . $geo_photo_name;
            if(move_uploaded_file($_FILES['geo_tagged_photo']['tmp_name'], $geo_photo_path)){
                $_POST['geo_tagged_photo'] = $geo_photo_path;
            }
        } else {
            // Keep existing photo if not uploaded new one
            $_POST['geo_tagged_photo'] = $client['geo_tagged_photo'];
        }
    }

    // Update client basic information
    if($fun->updateClient($id, $_POST)) {
        
        // Handle existing panels update
        if(isset($_POST['existing_panels']) && is_array($_POST['existing_panels'])) {
            foreach($_POST['existing_panels'] as $panelId => $panelData) {
                $updateData = [
                    'serial_number' => $panelData['serial_number'],
                    'wattage' => $panelData['wattage'],
                    'manufacturer' => $panelData['manufacturer'],
                    'installation_date' => $panelData['installation_date'],
                    'photo_path' => $existingPanels[$panelId]['photo_path'] // Keep existing by default
                ];
                
                // Handle photo update for existing panels
                if(isset($_FILES["existing_panel_photo_{$panelId}"]) && $_FILES["existing_panel_photo_{$panelId}"]['name']) {
                    $panel_photo_name = time() . '_existing_' . $panelId . '_' . basename($_FILES["existing_panel_photo_{$panelId}"]['name']);
                    $panel_photo_path = $upload_dir . $panel_photo_name;
                    if(move_uploaded_file($_FILES["existing_panel_photo_{$panelId}"]['tmp_name'], $panel_photo_path)) {
                        $updateData['photo_path'] = $panel_photo_path;
                    }
                }
                
                $fun->updateSolarPanel($panelId, $updateData);
            }
        }
        
        // Handle new panels addition
        if(isset($_POST['new_panels']) && is_array($_POST['new_panels'])) {
            $newPanelsData = [];
            $nextPanelNumber = $fun->getNextPanelNumber($id);
            
            foreach($_POST['new_panels'] as $index => $panel) {
                $panelData = [
                    'panel_number' => $nextPanelNumber,
                    'serial_number' => $panel['serial_number'],
                    'wattage' => $panel['wattage'],
                    'manufacturer' => $panel['manufacturer'],
                    'installation_date' => $panel['installation_date'],
                    'photo_path' => ''
                ];
                
                // Handle new panel photo upload
                if(isset($_FILES["new_panel_photo_{$index}"]) && $_FILES["new_panel_photo_{$index}"]['name']) {
                    $panel_photo_name = time() . '_new_' . $index . '_' . basename($_FILES["new_panel_photo_{$index}"]['name']);
                    $panel_photo_path = $upload_dir . $panel_photo_name;
                    if(move_uploaded_file($_FILES["new_panel_photo_{$index}"]['tmp_name'], $panel_photo_path)) {
                        $panelData['photo_path'] = $panel_photo_path;
                    }
                }
                
                $newPanelsData[] = $panelData;
                $nextPanelNumber++;
            }
            
            // Save new panels to database
            if(!empty($newPanelsData)) {
                $fun->addSolarPanels($id, $newPanelsData);
            }
        }
        
        $message = "Client updated successfully!";
        header("Location: view_clients.php?msg=" . urlencode($message));
        exit();
    } else {
        $message = "Error updating client!";
    }
}

if(!$client) {
    header("Location: view_clients.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Client - Quick Solar</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <?php include "include/links.php"; ?>

    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #4154f1;
        }
        
        .section-title {
            color: #4154f1;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            font-weight: 500;
            color: #012970;
            margin-bottom: 5px;
        }
        
        .btn-custom {
            margin: 5px;
        }
        
        .panel-photo-preview {
            max-width: 80px;
            max-height: 60px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
        }
        
        .existing-panel {
            background: #e8f5e8;
            border-left: 4px solid #28a745;
        }
        
        .new-panel {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>

<body>

    <!-- ======= Header ======= -->
    <?php include "include/header.php"; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php include "include/sideBar.php"; ?>
    <!-- End Sidebar-->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Client</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="view_clients.php">Clients</a></li>
                    <li class="breadcrumb-item active">Edit Client</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Edit Client Information</h5>
                            
                            <?php if($message): ?>
                                <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" enctype="multipart/form-data">
                                
                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <div class="section-title">Basic Information</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="name">Name of Customer *</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="consumer_number">Consumer Number *</label>
                                            <input type="text" class="form-control" id="consumer_number" name="consumer_number" value="<?php echo htmlspecialchars($client['consumer_number']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_unit">Billing Unit</label>
                                            <input type="text" class="form-control" id="billing_unit" name="billing_unit" value="<?php echo htmlspecialchars($client['billing_unit']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mobile">Customer Mobile Number</label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($client['mobile']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Email & Mahadiscom Section -->
                                <div class="form-section">
                                    <div class="section-title">Email & Mahadiscom Details</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="email">Email ID</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="email_password">Email Password</label>
                                            <input type="text" class="form-control" id="email_password" name="email_password" value="<?php echo htmlspecialchars($client['email_password']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mahadiscom_user_id">Mahadiscom User ID</label>
                                            <input type="text" class="form-control" id="mahadiscom_user_id" name="mahadiscom_user_id" value="<?php echo htmlspecialchars($client['mahadiscom_user_id']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mahadiscom_password">Mahadiscom Password</label>
                                            <input type="text" class="form-control" id="mahadiscom_password" name="mahadiscom_password" value="<?php echo htmlspecialchars($client['mahadiscom_password']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Application Details Section -->
                                <div class="form-section">
                                    <div class="section-title">Application Details</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="application_no_sanction">Application No (Sanction)</label>
                                            <input type="text" class="form-control" id="application_no_sanction" name="application_no_sanction" value="<?php echo htmlspecialchars($client['application_no_sanction']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="application_no_load_change">Application No (Load Change)</label>
                                            <input type="text" class="form-control" id="application_no_load_change" name="application_no_load_change" value="<?php echo htmlspecialchars($client['application_no_load_change']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="application_no_name_change">Application No (Name Change)</label>
                                            <input type="text" class="form-control" id="application_no_name_change" name="application_no_name_change" value="<?php echo htmlspecialchars($client['application_no_name_change']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="kilo_watt">Kilo Watt</label>
                                            <input type="number" step="0.01" class="form-control" id="kilo_watt" name="kilo_watt" value="<?php echo htmlspecialchars($client['kilo_watt']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="load_change_status">Load Change Status</label>
                                            <select class="form-control" id="load_change_status" name="load_change_status">
                                                <option value="Not" <?php echo $client['load_change_status'] == 'Not' ? 'selected' : ''; ?>>Not</option>
                                                <option value="Done" <?php echo $client['load_change_status'] == 'Done' ? 'selected' : ''; ?>>Done</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="pm_suryaghar_app_id">PM Suryaghar Application ID</label>
                                            <input type="text" class="form-control" id="pm_suryaghar_app_id" name="pm_suryaghar_app_id" value="<?php echo htmlspecialchars($client['pm_suryaghar_app_id']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Loan Details Section -->
                                <div class="form-section">
                                    <div class="section-title">Bank Loan Details</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="bank_loan_status">Bank Loan Status</label>
                                            <select class="form-control" id="bank_loan_status" name="bank_loan_status">
                                                <option value="no" <?php echo $client['bank_loan_status'] == 'no' ? 'selected' : ''; ?>>No</option>
                                                <option value="yes" <?php echo $client['bank_loan_status'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_name">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($client['bank_name']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_application_no">Bank Application No</label>
                                            <input type="text" class="form-control" id="bank_application_no" name="bank_application_no" value="<?php echo htmlspecialchars($client['bank_application_no']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="loan_amount">Loan Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="loan_amount" name="loan_amount" value="<?php echo htmlspecialchars($client['loan_amount']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="final_loan_amount">Final Loan Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="final_loan_amount" name="final_loan_amount" value="<?php echo htmlspecialchars($client['final_loan_amount']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_loan_1st_installment">1st Installment Date</label>
                                            <input type="date" class="form-control" id="bank_loan_1st_installment" name="bank_loan_1st_installment" value="<?php echo htmlspecialchars($client['bank_loan_1st_installment']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_loan_2nd_installment">2nd Installment Date</label>
                                            <input type="date" class="form-control" id="bank_loan_2nd_installment" name="bank_loan_2nd_installment" value="<?php echo htmlspecialchars($client['bank_loan_2nd_installment']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="remaining_amount">Remaining Amount</label>
                                            <input type="number" step="0.01" class="form-control" id="remaining_amount" name="remaining_amount" value="<?php echo htmlspecialchars($client['remaining_amount']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Reference & Location Section -->
                                <div class="form-section">
                                    <div class="section-title">Reference & Location Details</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="reference_name">Reference Name</label>
                                            <input type="text" class="form-control" id="reference_name" name="reference_name" value="<?php echo htmlspecialchars($client['reference_name']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="district">District</label>
                                            <input type="text" class="form-control" id="district" name="district" value="<?php echo htmlspecialchars($client['district']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="block">Block</label>
                                            <input type="text" class="form-control" id="block" name="block" value="<?php echo htmlspecialchars($client['block']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="taluka">Taluka</label>
                                            <input type="text" class="form-control" id="taluka" name="taluka" value="<?php echo htmlspecialchars($client['taluka']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="village">Village</label>
                                            <input type="text" class="form-control" id="village" name="village" value="<?php echo htmlspecialchars($client['village']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <textarea class="form-control" id="location" name="location" rows="3"><?php echo htmlspecialchars($client['location']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="geo_tagging_photo">Geo Tagging Photo URL</label>
                                            <input type="url" class="form-control" id="geo_tagging_photo" name="geo_tagging_photo" value="<?php echo htmlspecialchars($client['geo_tagging_photo']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Solar System Details Section -->
                                <div class="form-section">
                                    <div class="section-title">Solar System Details</div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="geo_tagged_photo">Geo Tagged Photo</label>
                                            <input type="file" class="form-control" id="geo_tagged_photo" name="geo_tagged_photo" accept="image/*">
                                            <?php if($client['geo_tagged_photo']): ?>
                                                <div class="mt-2">
                                                    <strong>Current Photo:</strong> 
                                                    <a href="<?php echo htmlspecialchars($client['geo_tagged_photo']); ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="inverter_name">Inverter Name</label>
                                            <input type="text" class="form-control" id="inverter_name" name="inverter_name" value="<?php echo htmlspecialchars($client['inverter_name']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="inverter_serial_number">Inverter Serial Number</label>
                                            <input type="text" class="form-control" id="inverter_serial_number" name="inverter_serial_number" value="<?php echo htmlspecialchars($client['inverter_serial_number']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="solar_type">Solar Type</label>
                                            <select class="form-control" id="solar_type" name="solar_type">
                                                <option value="DCR" <?php echo $client['solar_type'] == 'DCR' ? 'selected' : ''; ?>>DCR</option>
                                                <option value="NON-DCR" <?php echo $client['solar_type'] == 'NON-DCR' ? 'selected' : ''; ?>>NON-DCR</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="estimate_amount">Estimate Amount (₹)</label>
                                            <input type="number" step="0.01" class="form-control" id="estimate_amount" name="estimate_amount" value="<?php echo htmlspecialchars($client['estimate_amount']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Existing Solar Panels Section -->
                                <?php if(!empty($existingPanels)): ?>
                                <div class="form-section">
                                    <div class="section-title">Existing Solar Panels</div>
                                    <div id="existingPanelsContainer">
                                        <?php foreach($existingPanels as $panel): ?>
                                        <div class="card mb-3 existing-panel">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Panel <?php echo $panel['panel_number']; ?> (Existing)</h6>
                                                <a href="delete_panel.php?id=<?php echo $panel['id']; ?>&client_id=<?php echo $id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this panel?')">Delete</a>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Serial Number *</label>
                                                            <input type="text" class="form-control" name="existing_panels[<?php echo $panel['id']; ?>][serial_number]" value="<?php echo htmlspecialchars($panel['serial_number']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Wattage (W) *</label>
                                                            <input type="number" step="0.01" class="form-control" name="existing_panels[<?php echo $panel['id']; ?>][wattage]" value="<?php echo htmlspecialchars($panel['wattage']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Manufacturer</label>
                                                            <input type="text" class="form-control" name="existing_panels[<?php echo $panel['id']; ?>][manufacturer]" value="<?php echo htmlspecialchars($panel['manufacturer']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Installation Date</label>
                                                            <input type="date" class="form-control" name="existing_panels[<?php echo $panel['id']; ?>][installation_date]" value="<?php echo htmlspecialchars($panel['installation_date']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Panel Photo</label>
                                                            <input type="file" class="form-control" name="existing_panel_photo_<?php echo $panel['id']; ?>" accept="image/*">
                                                            <?php if($panel['photo_path']): ?>
                                                                <div class="mt-2">
                                                                    <strong>Current:</strong> 
                                                                    <a href="<?php echo htmlspecialchars($panel['photo_path']); ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Add New Solar Panels Section -->
                                <div class="form-section">
                                    <div class="section-title">Add New Solar Panels</div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-success" onclick="addNewPanel()">
                                            <i class="bi bi-plus-circle"></i> Add New Panel
                                        </button>
                                    </div>
                                    <div id="newPanelsContainer">
                                        <!-- New panels will be added here dynamically -->
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-custom">Update Client</button>
                                    <a href="view_clients.php" class="btn btn-secondary btn-custom">Back to List</a>
                                    <button type="reset" class="btn btn-warning btn-custom">Reset Form</button>
                                    <a href="export_clients.php" class="btn btn-success btn-custom">Export to Excel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ======= Footer ======= -->
    <?php include "include/footer.php"; ?>

    <script>
        let newPanelCount = 0;

        function addNewPanel() {
            newPanelCount++;
            const newPanelHtml = `
                <div class="card mb-3 new-panel" id="newPanel${newPanelCount}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">New Panel ${newPanelCount}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeNewPanel(${newPanelCount})">Remove</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Serial Number *</label>
                                    <input type="text" class="form-control" name="new_panels[${newPanelCount}][serial_number]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Wattage (W) *</label>
                                    <input type="number" step="0.01" class="form-control" name="new_panels[${newPanelCount}][wattage]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Manufacturer</label>
                                    <input type="text" class="form-control" name="new_panels[${newPanelCount}][manufacturer]">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Installation Date</label>
                                    <input type="date" class="form-control" name="new_panels[${newPanelCount}][installation_date]">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Panel Photo</label>
                                    <input type="file" class="form-control" name="new_panel_photo_${newPanelCount}" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('newPanelsContainer').innerHTML += newPanelHtml;
        }

        function removeNewPanel(panelNumber) {
            document.getElementById(`newPanel${panelNumber}`).remove();
        }

        // Bank loan status dynamic fields
        document.getElementById('bank_loan_status').addEventListener('change', function() {
            const bankFields = ['bank_name', 'bank_application_no', 'loan_amount', 'final_loan_amount', 
                              'bank_loan_1st_installment', 'bank_loan_2nd_installment', 'remaining_amount'];
            
            bankFields.forEach(field => {
                const element = document.getElementById(field);
                if(element) {
                    element.disabled = this.value === 'no';
                    if(this.value === 'no') {
                        element.value = '';
                    }
                }
            });
        });

        // Initialize on page load
        document.getElementById('bank_loan_status').dispatchEvent(new Event('change'));
    </script>
</body>
</html>