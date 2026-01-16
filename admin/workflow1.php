<?php
require_once "connect/auth_middleware1.php";
require_once "connect/db1.php";

authenticate();

$title = 'customer_workflow';

// Get current step from URL or default to 1
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// Database connection
$database = new Database();
$db = $database->getConnection();

// If client_id is provided, load client data
$client_data = [];
if ($client_id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $client_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error loading client data: " . $e->getMessage());
    }
}

// Function to get incomplete clients for a specific step
function getIncompleteClients($db, $step) {
    $sql = "";
    switch($step) {
        case 2: // Communication & Address
            $sql = "SELECT id, name FROM clients WHERE mobile IS NULL OR mobile = '' OR email IS NULL OR email = '' OR district IS NULL OR district = ''";
            break;
        case 3: // MAHADISCOM Email & Mobile Update
            $sql = "SELECT id, name FROM clients WHERE mahadiscom_email IS NULL OR mahadiscom_email = '' OR mahadiscom_email_password IS NULL OR mahadiscom_email_password = '' OR mahadiscom_mobile IS NULL OR mahadiscom_mobile = ''";
            break;
        case 4: // MAHADISCOM Registration
            $sql = "SELECT id, name FROM clients WHERE mahadiscom_user_id IS NULL OR mahadiscom_user_id = '' OR mahadiscom_password IS NULL OR mahadiscom_password = ''";
            break;
        case 5: // Name Change Require
            $sql = "SELECT id, name FROM clients WHERE name_change_require IS NULL OR name_change_require = ''";
            break;
        case 6: // PM Suryaghar Registration
            $sql = "SELECT id, name FROM clients WHERE pm_suryaghar_registration IS NULL OR pm_suryaghar_registration = ''";
            break;
        case 7: // MAHADISCOM Sanction Load
            $sql = "SELECT id, name FROM clients WHERE load_change_application_number IS NULL OR load_change_application_number = '' OR rooftop_solar_application_number IS NULL OR rooftop_solar_application_number = ''";
            break;
        case 8: // Bank Loan
            $sql = "SELECT id, name FROM clients WHERE bank_loan_status IS NULL OR bank_loan_status = ''";
            break;
        case 9: // Fitting Photos
            $sql = "SELECT id, name FROM clients WHERE inverter_company_name IS NULL OR inverter_company_name = ''";
            break;
        case 10: // PM SuryaGhar Document Upload
            $sql = "SELECT c.id, c.name 
                    FROM clients c
                    WHERE 
                        (c.inverter_company_name IS NULL OR c.inverter_company_name = '' OR
                         c.inverter_serial_number IS NULL OR c.inverter_serial_number = '' OR
                         c.dcr_certificate_number IS NULL OR c.dcr_certificate_number = '' OR
                         c.number_of_panels IS NULL OR c.number_of_panels = 0)
                        OR
                        NOT EXISTS (SELECT 1 FROM client_documents WHERE client_id = c.id AND document_type = 'aadhar')
                        OR NOT EXISTS (SELECT 1 FROM client_documents WHERE client_id = c.id AND document_type = 'pan_card')
                        OR NOT EXISTS (SELECT 1 FROM client_documents WHERE client_id = c.id AND document_type = 'electric_bill')
                        OR NOT EXISTS (SELECT 1 FROM client_documents WHERE client_id = c.id AND document_type = 'bank_passbook')";
            break;
        case 11: // RTS Portal Status
            $sql = "SELECT id, name FROM clients WHERE rts_portal_status IS NULL OR rts_portal_status = ''";
            break;
        case 12: // Meter Installation Photo
            $sql = "SELECT id, name FROM clients WHERE meter_number IS NULL OR meter_number = ''";
            break;
        case 13: // PM Suryaghar Redeem Status
            $sql = "SELECT id, name FROM clients WHERE pm_redeem_status IS NULL OR (pm_redeem_status = 'no' OR (pm_redeem_status = 'yes' AND (subsidy_amount IS NULL OR subsidy_amount = 0 OR subsidy_redeem_date IS NULL)))";
            break;
        case 14: // Reference
            $sql = "SELECT id, name FROM clients WHERE reference_name IS NULL OR reference_name = ''";
            break;
        default:
            $sql = "SELECT id, name FROM clients WHERE 1";
    }
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting incomplete clients: " . $e->getMessage());
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow - Solar Quick</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-nav { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .step-item { padding: 10px; margin: 5px 0; cursor: pointer; border-radius: 5px; transition: all 0.3s ease; }
        .step-item.active { background: #007bff; color: white; }
        .step-item.completed { background: #28a745; color: white; }
        .step-item:hover:not(.active) { background: #e9ecef; }
        .client-selector { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
        .step-form-container { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-navigation { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        .progress { height: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include "include/sidebar.php"; ?>
    <?php require('include/navbar.php') ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Client Workflow</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Workflow - Step <?php echo $current_step; ?></li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <!-- Progress Bar -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo ($current_step / 14) * 100; ?>%">
                            Step <?php echo $current_step; ?> of 14
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Step Navigation -->
                <div class="col-md-3">
                    <div class="step-nav">
                        <h5>Workflow Steps</h5>
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
                        ?>
                            <div class="step-item <?php echo $active_class; ?>" 
                                 onclick="navigateToStep(<?php echo $step_num; ?>)">
                                <?php echo $step_num . '. ' . $step_name; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step Content -->
                <div class="col-md-9">
                    <!-- Client Selector (hidden for step 1) -->
                    <?php if($current_step > 1): ?>
                    <div class="client-selector">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Client:</label>
                                <select class="form-select" id="clientSelect" onchange="onClientChange()">
                                    <option value="">-- Select Client --</option>
                                    <option value="new">+ Create New Client</option>
                                    <?php
                                    $incomplete_clients = getIncompleteClients($db, $current_step);
                                    foreach($incomplete_clients as $client):
                                    ?>
                                        <option value="<?php echo $client['id']; ?>" 
                                                <?php echo ($client_id == $client['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['name']); ?> (ID: <?php echo $client['id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <?php if($client_id > 0): ?>
                                        <span class="badge bg-info">Editing: <?php echo htmlspecialchars($client_data['name'] ?? ''); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">No client selected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Step Forms -->
                    <div class="step-form-container">
                        <?php
                        $step_file = "workflow_steps/step{$current_step}.php";
                        if(file_exists($step_file)) {
                            include $step_file;
                        } else {
                            echo "<div class='alert alert-warning'>Step {$current_step} form is under development.</div>";
                        }
                        ?>

                        <!-- Navigation Buttons -->
                        <div class="form-navigation">
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
                                        <button class="btn btn-primary" onclick="saveAndContinue()">
                                            Save & Continue →
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success" onclick="saveAndFinish()">
                                            ✓ Complete Workflow
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

    <script>
        let currentClientId = <?php echo $client_id ?: '0'; ?>;
        let currentStep = <?php echo $current_step; ?>;

        function navigateToStep(step) {
            let url = `workflow.php?step=${step}`;
            if(currentClientId > 0) {
                url += `&client_id=${currentClientId}`;
            }
            window.location.href = url;
        }

        function onClientChange() {
            const clientSelect = document.getElementById('clientSelect');
            const clientId = clientSelect.value;
            
            if(clientId === 'new') {
                window.location.href = 'workflow.php?step=1';
            } else if(clientId) {
                window.location.href = `workflow.php?step=${currentStep}&client_id=${clientId}`;
            }
        }

        function saveAndContinue() {
            if(!validateCurrentStep()) {
                return;
            }
            
            const form = document.getElementById(`step${currentStep}Form`);
            if(!form) {
                alert('Form not found!');
                return;
            }
            
            const formData = new FormData(form);
            formData.append('step', currentStep);
            formData.append('client_id', currentClientId);

            const submitBtn = document.querySelector('.btn-primary');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            submitBtn.disabled = true;

            fetch('api/workflow_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    setTimeout(() => {
                        navigateToStep(currentStep + 1);
                    }, 500);
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                alert('Error saving data: ' + error.message);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        function saveAndFinish() {
            if(!validateCurrentStep()) return;
            
            const form = document.getElementById(`step${currentStep}Form`);
            const formData = new FormData(form);
            formData.append('step', currentStep);
            formData.append('client_id', currentClientId);

            fetch('api/workflow_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Workflow completed successfully!');
                    window.location.href = 'view_clients.php';
                } else {
                    alert('Error: ' + (data.message || 'Please check the form'));
                }
            });
        }

        function validateCurrentStep() {
            const validators = {
                1: validateStep1, 2: validateStep2, 3: validateStep3, 4: validateStep4,
                5: validateStep5, 6: validateStep6, 7: validateStep7, 8: validateStep8,
                9: validateStep9, 10: validateStep10, 11: validateStep11, 12: validateStep12,
                13: validateStep13, 14: validateStep14
            };
            
            return validators[currentStep] ? validators[currentStep]() : true;
        }

        // Validation functions will be defined in respective step files
    </script>
</body>
</html>