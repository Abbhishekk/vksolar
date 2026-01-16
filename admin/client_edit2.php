<?php
include "include/auth_session.php";
include "connect/db1.php";
include "connect/fun.php";

$connect = new connect();
$fun = new fun($connect->dbconnect());

// Get client ID from URL
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no client ID
if($client_id <= 0) {
    header("Location: view_clients.php");
    exit;
}

// Load client data
$client_data = [];
$result = $fun->fetchClientById($client_id);
if($result && mysqli_num_rows($result) > 0) {
    $client_data = mysqli_fetch_assoc($result);
} else {
    // Client not found
    header("Location: view_clients.php?error=client_not_found");
    exit;
}

// Get current step from URL or default to 1
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Helper function to check if client has a document
function hasClientDocument($db, $clientId, $documentType) {
    $sql = "SELECT COUNT(*) as count FROM client_documents WHERE client_id = ? AND document_type = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $clientId, $documentType);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return ($row['count'] > 0);
}

// Helper function to get document file path
function getClientDocumentPath($db, $clientId, $documentType) {
    $sql = "SELECT file_path FROM client_documents WHERE client_id = ? AND document_type = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $clientId, $documentType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['file_path'];
    }
    return null;
}

// Helper function to convert server path to web URL
function getDocumentWebUrl($filePath) {
    if (!$filePath) return null;
    
    // Convert server path to web URL
    // Example: ../../uploads/clients/8/fitting_photos/solar_panel_photo.jpg
    // Becomes: ../uploads/clients/8/fitting_photos/solar_panel_photo.jpg
    $webPath = str_replace('../../', '../', $filePath);
    return $webPath;
}

// Helper function to check if step is completed

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client - Solar Quick</title>
    <?php require('include/head.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-nav {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .step-item {
            padding: 10px;
            margin: 5px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .step-item.active {
            background: #007bff;
            color: white;
        }
        .step-item.completed {
            background: #28a745;
            color: white;
        }
        .step-item:hover:not(.active) {
            background: #e9ecef;
        }
        .step-form-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .file-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .client-info-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
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
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Client</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="view_clients.php">View Clients</a></li>
                    <li class="breadcrumb-item active">Edit Client - Step <?php echo $current_step; ?></li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <!-- Client Info Header -->
            <div class="client-info-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4><?php echo htmlspecialchars($client_data['name']); ?></h4>
                        <p class="mb-0">Consumer No: <?php echo htmlspecialchars($client_data['consumer_number']); ?></p>
                        <p class="mb-0">Mobile: <?php echo htmlspecialchars($client_data['mobile']); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-0">Client ID: <?php echo $client_id; ?></p>
                        <p class="mb-0">Created: <?php echo date('d M Y', strtotime($client_data['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Progress Bar -->
                <div class="col-12 mb-4">
                    <div class="progress" style="height:100% !important;">
                        <div class="progress-bar" role="progressbar" 
                             style="padding: 10px !important; height 100% !important; margin-top:0px; width: <?php echo ($current_step / 14) * 100; ?>%">
                            Step <?php echo $current_step; ?> of 14
                        </div>
                    </div>
                </div>

                <!-- Step Navigation -->
                <div class="col-md-3">
                    <div class="step-nav">
                        <h5>Edit Steps</h5>
                        <?php
                        $steps = [
                            1 => 'Basic Details',
                            2 => 'Communication & Address',
                            3 => 'MAHADISCOM Email & Mobile Update',
                            4 => 'MAHADISCOM Registration',
                            5 => 'Name Change Require',
                            6 => 'PM Suryaghar Portal Registration',
                            7 => 'MAHADISCOM Sanction Load',
                            8 => 'Bank Loan',
                            9 => 'Fitting Photos',
                            10 => 'PM SuryaGhar Document Upload',
                            11 => 'RTS Portal Status',
                            12 => 'Meter Installation Photo',
                            13 => 'PM Suryaghar Redeem Status',
                            14 => 'Reference'
                        ];
                        
                        foreach($steps as $step_num => $step_name):
                            $active_class = ($step_num == $current_step) ? 'active' : '';
                            $completed_class = isStepCompleted($connect->dbconnect(), $client_id, $step_num) ? 'completed' : '';
                        ?>
                            <div class="step-item <?php echo $active_class . ' ' . $completed_class; ?>" 
                                 onclick="navigateToStep(<?php echo $step_num; ?>)">
                                <?php echo $step_num . '. ' . $step_name; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-3">
                        <a href="view_clients.php" class="btn btn-secondary w-100 mb-2">
                            ← Back to List
                        </a>
                        <button onclick="confirmDelete(<?php echo $client_id; ?>)" 
                                class="btn btn-danger w-100">
                            🗑 Delete Client
                        </button>
                    </div>
                </div>

                <!-- Step Content -->
                <div class="col-md-9">
                    <div class="step-form-container " style="position:sticky;z-index:1050;top:5rem">
                        <?php
                        // Include the appropriate step edit form
                        $step_file = "edit_client_steps/step{$current_step}_edit.php";
                        if(file_exists($step_file)) {
                            include $step_file;
                        } else {
                            // Fallback to regular step file
                            $fallback_file = "workflow_steps/step{$current_step}.php";
                            if(file_exists($fallback_file)) {
                                include $fallback_file;
                            } else {
                                echo "<div class='alert alert-warning'>Step {$current_step} edit form is under development.</div>";
                            }
                        }
                        ?>

                        <!-- Navigation Buttons -->
                        <div class="form-navigation mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if($current_step > 1): ?>
                                        <button class="btn btn-secondary" onclick="navigateToStep(<?php echo $current_step - 1; ?>)">
                                            ← Previous Step
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 text-end">
                                    <?php if($current_step < 14): ?>
                                        <button class="btn btn-primary" onclick="saveStep()">
                                            Save & Continue →
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success" onclick="saveStep()">
                                            ✓ Update Client
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete client <strong><?php echo htmlspecialchars($client_data['name']); ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently delete all client data and uploaded files.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Client</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const clientId = <?php echo $client_id; ?>;
        let currentStep = <?php echo $current_step; ?>;

        function navigateToStep(step) {
            window.location.href = `client_edit2?id=${clientId}&step=${step}`;
        }

        function saveStep() {
            // Validate current step
            if(!validateCurrentStep()) {
                console.log('Validation failed for step', currentStep);
                return;
            }
            
            const form = document.getElementById(`step${currentStep}Form`);
            if(!form) {
                alert('Form not found!');
                return;
            }
            
            const formData = new FormData(form);
            formData.append('step', currentStep);
            formData.append('client_id', clientId);
            formData.append('edit_mode', 'true');

            // Show loading state
            const submitBtn = document.querySelector('.btn-primary, .btn-success');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitBtn.disabled = true;

            const url = `api/workflow_api?action=save_step_data&step=${currentStep}&client_id=${clientId}&edit_mode=true`;
            
            console.log('Saving to URL:', url);
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                if(data.success) {
                    console.log('Data saved successfully');
                    // Show success message
                    showAlert('success', 'Step updated successfully!');
                    
                    // Navigate to next step after short delay
                    setTimeout(() => {
                        if(currentStep < 14) {
                            navigateToStep(currentStep + 1);
                        } else {
                            // If last step, go back to view clients
                            window.location.href = 'view_clients.php?updated=true';
                        }
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error saving data: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        function validateCurrentStep() {
            // Reuse validation from workflow.php or create specific ones
            switch(currentStep) {
                case 1:
                    return validateStep1();
                case 2:
                    return validateStep2();
                case 3:
                    return validateStep3();
                case 9:
                    return validateStep9();
                case 10:
                    return validateStep10();
                case 12:
                    return validateStep12();
                // Add other steps as needed
                default:
                    return true;
            }
        }

        function validateStep1() {
            const name = document.querySelector('input[name="name"]').value.trim();
            const consumerNumber = document.querySelector('input[name="consumer_number"]').value.trim();
            
            if(!name) {
                alert('Please enter customer name');
                return false;
            }
            
            if(!consumerNumber) {
                alert('Please enter consumer number');
                return false;
            }
            
            return true;
        }

        function validateStep2() {
            const mobile = document.querySelector('input[name="mobile"]').value.trim();
            
            if(!mobile) {
                alert('Please enter mobile number');
                return false;
            }
            
            return true;
        }

        function validateStep9() {
            // For step 9, check if at least one photo is being uploaded or already exists
            const photoInputs = ['solar_panel_photo', 'inverter_photo', 'geotag_photo'];
            let hasNewUpload = false;
            
            for(let inputName of photoInputs) {
                const input = document.querySelector(`input[name="${inputName}"]`);
                if(input && input.files && input.files.length > 0) {
                    hasNewUpload = true;
                    break;
                }
            }
            
            if(!hasNewUpload) {
                // Check if photos already exist (look for success badges)
                const existingPhotos = document.querySelectorAll('.badge.bg-success');
                if(existingPhotos.length === 0) {
                    alert('Please upload at least one photo');
                    return false;
                }
            }
            
            return true;
        }

        function validateStep12() {
            // Step 12 is optional - meter number and photo are not required
            return true;
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of the form container
            const formContainer = document.querySelector('.step-form-container');
            formContainer.insertBefore(alertDiv, formContainer.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if(alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        function confirmDelete(clientId) {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
            btn.disabled = true;

            fetch(`api/workflow_api.php?action=delete_client&client_id=${clientId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.href = 'view_clients.php?deleted=true';
                } else {
                    alert('Error deleting client: ' + (data.message || 'Unknown error'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting client');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>

<?php
// Helper function to check if step is completed
function isStepCompleted($db, $clientId, $step) {
    $sql = "SELECT * FROM clients WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    
    if (!$client) return false;
    
    // Basic checks for each step
    switch($step) {
        case 1:
            return !empty($client['name']) && !empty($client['consumer_number']);
        case 2:
            return !empty($client['mobile']) && !empty($client['adhar']);
        case 3:
            return !empty($client['mahadiscom_email']) && !empty($client['mahadiscom_mobile']);
        case 4:
            return !empty($client['mahadiscom_user_id']) && !empty($client['mahadiscom_password']);
        case 5:
            return !empty($client['name_change_require']);
        case 6:
            return !empty($client['pm_suryaghar_registration']);
        case 7:
            return !empty($client['load_change_application_number']) && !empty($client['rooftop_solar_application_number']);
        case 8:
            return !empty($client['bank_loan_status']);
        case 9:
            return hasAllFittingPhotos($db, $clientId);
        case 10:
            return !empty($client['inverter_company_name']) && !empty($client['dcr_certificate_number']);
        case 11:
            return !empty($client['rts_portal_status']);
        case 12:
            return !empty($client['meter_number']) && hasMeterPhoto($db, $clientId);
        case 13:
            return !empty($client['pm_redeem_status']);
        case 14:
            return !empty($client['reference_name']);
        default:
            return false;
    }
}

function hasAllFittingPhotos($db, $clientId) {
    $types = ['solar_panel_photo', 'inverter_photo', 'geotag_photo'];
    foreach($types as $type) {
        $sql = "SELECT COUNT(*) as count FROM client_documents WHERE client_id = ? AND document_type = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("is", $clientId, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if($row['count'] == 0) return false;
    }
    return true;
}

function hasMeterPhoto($db, $clientId) {
    $sql = "SELECT COUNT(*) as count FROM client_documents WHERE client_id = ? AND document_type = 'meter_photo'";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}
?>