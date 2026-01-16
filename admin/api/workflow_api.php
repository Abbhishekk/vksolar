<?php
// admin/api/workflow_api.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once __DIR__ . '/../connect/db1.php';
require_once __DIR__ . '/../connect/fun.php';

// Temporary logger for debugging
function logDebug($msg) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
}
function jsonError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function jsonSuccess($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$connect = new connect();
$db = $connect->dbconnect();
$fun = new fun($db);
// print_r($_POST);
// Always accept action from either GET or POST
$action = $_REQUEST['action'] ?? '';

// Log minimal meta
logDebug("REQ_METHOD={$_SERVER['REQUEST_METHOD']} ACTION=" . ($action ?: 'NONE'));
logDebug("CONTENT_TYPE=" . ($_SERVER['CONTENT_TYPE'] ?? ''));
logDebug("CONTENT_LENGTH=" . ($_SERVER['CONTENT_LENGTH'] ?? ''));
logDebug("RAW_LEN=" . strlen($raw = file_get_contents('php://input')));
logDebug("REQUEST_KEYS: " . implode(',', array_keys($_REQUEST)));
logDebug("POST_KEYS: " . implode(',', array_keys($_POST)));
logDebug("FILES_KEYS: " . implode(',', array_keys($_FILES)));
logDebug("REQUEST_PREVIEW: " . print_r($_REQUEST, true));

if (!$action) {
    jsonError("Missing action parameter");
}

switch ($action) {
    case 'save_step_data':
        $step = $_REQUEST['step'] ?? '';
        $clientId = $_REQUEST['client_id'] ?? '';

        if ($step === '') jsonError("Step parameter is required");

        // normalize client id
        if ($clientId === '' || $clientId === '0' || intval($clientId) === 0) {
            $clientId = 0;
        } else {
            $clientId = intval($clientId);
        }

        try {
            if ($step == '1') {
                $name = trim($_REQUEST['name'] ?? '');
                $consumer_number = trim($_REQUEST['consumer_number'] ?? '');
                $billing_unit = trim($_REQUEST['billing_unit'] ?? '');
                $location_url = trim($_REQUEST['location_url'] ?? '');

                if ($name === '' || $consumer_number === '') {
                    throw new Exception("Name and Consumer Number are required");
                }

                $dataToSave = [
                    'name' => $name,
                    'consumer_number' => $consumer_number,
                    'billing_unit' => $billing_unit,
                    'location' => $location_url
                ];

                if ($clientId === 0) {
                    $res = $fun->addClient($dataToSave);
                    if (!$res) throw new Exception("Failed to create client: " . ($db->error ?? 'unknown'));
                    $newId = $db->insert_id;
                    logDebug("Created client id={$newId}");
                    jsonSuccess(['client_id' => $newId, 'message' => 'Client created']);
                } else {
                    $res = $fun->updateClient($clientId, $dataToSave);
                    if (!$res) throw new Exception("Failed to update client: " . ($db->error ?? 'unknown'));
                    logDebug("Updated client id={$clientId}");
                    jsonSuccess(['client_id' => $clientId, 'message' => 'Client updated']);
                }
            } else if ($step == '9') {
                // Handle file uploads for step 9
                if ($clientId === 0) jsonError("client_id required for step 9");
                
                $uploadDir = __DIR__ . "/../uploads/clients/{$clientId}/fitting_photos/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $photoTypes = ['solar_panel_photo', 'inverter_photo', 'geotag_photo'];
                $uploadedFiles = [];
                
                foreach ($photoTypes as $photoType) {
                    if (isset($_FILES[$photoType]) && $_FILES[$photoType]['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES[$photoType];
                        $fileName = $photoType . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filePath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($file['tmp_name'], $filePath)) {
                            // Delete old file if exists
                            $stmt = $db->prepare("SELECT file_path FROM client_documents WHERE client_id = ? AND document_type = ?");
                            $stmt->bind_param("is", $clientId, $photoType);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $oldPath = __DIR__ . '/../' . $row['file_path'];
                                if (file_exists($oldPath)) unlink($oldPath);
                                
                                // Update existing record
                                $stmt = $db->prepare("UPDATE client_documents SET file_path = ?, file_name = ?, original_filename = ? WHERE client_id = ? AND document_type = ?");
                                $relativePath = "uploads/clients/{$clientId}/fitting_photos/{$fileName}";
                                $stmt->bind_param("sssis", $relativePath, $fileName, $file['name'], $clientId, $photoType);
                                $stmt->execute();
                            } else {
                                // Insert new record
                                $stmt = $db->prepare("INSERT INTO client_documents (client_id, document_type, file_path, file_name, original_filename) VALUES (?, ?, ?, ?, ?)");
                                $relativePath = "uploads/clients/{$clientId}/fitting_photos/{$fileName}";
                                $stmt->bind_param("issss", $clientId, $photoType, $relativePath, $fileName, $file['name']);
                                $stmt->execute();
                            }
                            $uploadedFiles[] = $photoType;
                        }
                    }
                }
                
                if (empty($uploadedFiles)) {
                    jsonError("No photos were uploaded");
                } else {
                    jsonSuccess(['message' => 'Photos uploaded: ' . implode(', ', $uploadedFiles)]);
                }
            } else if ($step == '12') {
                // Handle step 12 - meter data + photo
                if ($clientId === 0) jsonError("client_id required for step 12");
                
                // Handle form fields first
                $allowed = ['meter_number', 'meter_installation_date'];
                $update = [];
                foreach ($allowed as $f) {
                    if (isset($_REQUEST[$f])) $update[$f] = $_REQUEST[$f];
                }
                
                if (!empty($update)) {
                    $res = $fun->updateClient($clientId, $update);
                    if (!$res) throw new Exception("Failed to update client: " . ($db->error ?? 'unknown'));
                }
                
                // Handle file upload
                if (isset($_FILES['meter_installation_photo']) && $_FILES['meter_installation_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . "/../uploads/clients/{$clientId}/meter_photos/";
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $file = $_FILES['meter_installation_photo'];
                    $fileName = 'meter_photo_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $filePath)) {
                        // Delete old file if exists
                        $stmt = $db->prepare("SELECT file_path FROM client_documents WHERE client_id = ? AND document_type = 'meter_photo'");
                        $stmt->bind_param("i", $clientId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $oldPath = __DIR__ . '/../' . $row['file_path'];
                            if (file_exists($oldPath)) unlink($oldPath);
                            
                            // Update existing record
                            $stmt = $db->prepare("UPDATE client_documents SET file_path = ?, file_name = ?, original_filename = ? WHERE client_id = ? AND document_type = 'meter_photo'");
                            $relativePath = "uploads/clients/{$clientId}/meter_photos/{$fileName}";
                            $stmt->bind_param("sssi", $relativePath, $fileName, $file['name'], $clientId);
                            $stmt->execute();
                        } else {
                            // Insert new record
                            $stmt = $db->prepare("INSERT INTO client_documents (client_id, document_type, file_path, file_name, original_filename) VALUES (?, 'meter_photo', ?, ?, ?)");
                            $relativePath = "uploads/clients/{$clientId}/meter_photos/{$fileName}";
                            $stmt->bind_param("isss", $clientId, $relativePath, $fileName, $file['name']);
                            $stmt->execute();
                        }
                    }
                }
                
                jsonSuccess(['client_id' => $clientId, 'message' => 'Step 12 saved']);
            } else {
                // generic safe update for other steps (whitelisted fields)
                $allowed = [
                    'adhar','mobile','email','district','block','taluka','village','pincode',
                    'mahadiscom_email','mahadiscom_email_password','mahadiscom_mobile',
                    'mahadiscom_user_id','mahadiscom_password','email_password',
                    'name_change_require','application_no_name_change',
                    'pm_suryaghar_registration','pm_suryaghar_app_id','pm_registration_date',
                    'load_change_application_number','rooftop_solar_application_number','kilo_watt','load_change_status',
                    'bank_loan_status','bank_name','account_number','ifsc_code','loan_amount',
                    'jan_samartha_application_no','first_installment_amount','second_installment_amount','remaining_amount',
                    'inverter_company_name','inverter_capacity','inverter_serial_number','dcr_certificate_number','number_of_panels','solar_type',
                    'company_name','wattage','panel_serial_numbers',
                    'rts_portal_status','meter_number','meter_installation_date',
                    'pm_redeem_status','subsidy_amount','subsidy_redeem_date',
                    'reference_name','reference_contact','estimate_amount'
                ];
                $update = [];
                foreach ($allowed as $f) {
                    if (isset($_REQUEST[$f])) $update[$f] = $_REQUEST[$f];
                }
                if (empty($update)) jsonError("No data provided for step {$step}");
                if ($clientId === 0) jsonError("client_id required for updating step {$step}");

                $res = $fun->updateClient($clientId, $update);
                if (!$res) throw new Exception("Failed to update client: " . ($db->error ?? 'unknown'));
                logDebug("Updated client {$clientId} for step {$step}");
                jsonSuccess(['client_id' => $clientId, 'message' => 'Step saved']);
            }
        } catch (Exception $e) {
            logDebug("ERROR save_step_data: " . $e->getMessage());
            jsonError("Failed to save step data: " . $e->getMessage());
        }
        break;

    case 'get_incomplete_clients':
        $step = intval($_REQUEST['step'] ?? 0);
        if ($step <= 0) jsonError("Valid step required");
        
        // Step-specific queries to get incomplete clients
        $sql = '';
        switch ($step) {
            case 1:
                $sql = "SELECT id, name FROM clients WHERE (name IS NULL OR name = '') OR (consumer_number IS NULL OR consumer_number = '') ORDER BY id DESC LIMIT 500";
                break;
            case 2:
                $sql = "SELECT id, name FROM clients WHERE (mobile IS NULL OR mobile = '') OR (adhar IS NULL OR adhar = '' OR adhar = 0) ORDER BY id DESC LIMIT 500";
                break;
            case 3:
                $sql = "SELECT id, name FROM clients WHERE (mahadiscom_email IS NULL OR mahadiscom_email = '') OR (mahadiscom_email_password IS NULL OR mahadiscom_email_password = '') OR (mahadiscom_mobile IS NULL OR mahadiscom_mobile = '') ORDER BY id DESC LIMIT 500";
                break;
            case 4:
                $sql = "SELECT id, name FROM clients WHERE (mahadiscom_user_id IS NULL OR mahadiscom_user_id = '') OR (mahadiscom_password IS NULL OR mahadiscom_password = '') ORDER BY id DESC LIMIT 500";
                break;
            case 5:
                $sql = "SELECT id, name FROM clients WHERE (name_change_require IS NULL OR name_change_require = '') ORDER BY id DESC LIMIT 500";
                break;
            case 6:
                $sql = "SELECT id, name FROM clients WHERE (pm_suryaghar_registration IS NULL OR pm_suryaghar_registration = '') OR (pm_suryaghar_registration = 'yes' AND (pm_suryaghar_app_id IS NULL OR pm_suryaghar_app_id = '' OR pm_registration_date IS NULL OR pm_registration_date = '0000-00-00')) ORDER BY id DESC LIMIT 500";
                break;
            case 7:
                $sql = "SELECT id, name FROM clients WHERE (load_change_application_number IS NULL OR load_change_application_number = '') OR (rooftop_solar_application_number IS NULL OR rooftop_solar_application_number = '') ORDER BY id DESC LIMIT 500";
                break;
            case 8:
                $sql = "SELECT id, name FROM clients WHERE (bank_loan_status IS NULL OR bank_loan_status = '') OR (bank_loan_status = 'yes' AND (bank_name IS NULL OR bank_name = '' OR account_number IS NULL OR account_number = '' OR loan_amount IS NULL OR loan_amount = 0)) ORDER BY id DESC LIMIT 500";
                break;
            case 9:
                $sql = "SELECT DISTINCT c.id, c.name FROM clients c LEFT JOIN client_documents sp ON sp.client_id = c.id AND sp.document_type = 'solar_panel_photo' LEFT JOIN client_documents ip ON ip.client_id = c.id AND ip.document_type = 'inverter_photo' LEFT JOIN client_documents gp ON gp.client_id = c.id AND gp.document_type = 'geotag_photo' WHERE sp.id IS NULL OR ip.id IS NULL OR gp.id IS NULL ORDER BY c.id DESC LIMIT 500";
                break;
            case 10:
                $sql = "SELECT DISTINCT c.id, c.name FROM clients c LEFT JOIN client_documents da ON da.client_id = c.id AND da.document_type = 'aadhar' LEFT JOIN client_documents dp ON dp.client_id = c.id AND dp.document_type = 'pan_card' LEFT JOIN client_documents eb ON eb.client_id = c.id AND eb.document_type = 'electric_bill' LEFT JOIN client_documents bp ON bp.client_id = c.id AND bp.document_type = 'bank_passbook' WHERE (c.inverter_company_name IS NULL OR c.inverter_company_name = '' OR c.inverter_serial_number IS NULL OR c.inverter_serial_number = '' OR c.dcr_certificate_number IS NULL OR c.dcr_certificate_number = '' OR c.number_of_panels IS NULL OR c.number_of_panels = 0) OR da.id IS NULL OR dp.id IS NULL OR eb.id IS NULL OR bp.id IS NULL ORDER BY c.id DESC LIMIT 500";
                break;
            case 11:
                $sql = "SELECT id, name FROM clients WHERE rts_portal_status IS NULL OR rts_portal_status = ''  ORDER BY id DESC LIMIT 500";
                break;
            case 12:
                $sql = "SELECT DISTINCT c.id, c.name FROM clients c LEFT JOIN client_documents mp ON mp.client_id = c.id AND mp.document_type = 'meter_photo' WHERE (c.meter_number IS NULL OR c.meter_number = '') OR mp.id IS NULL ORDER BY c.id DESC LIMIT 500";
                break;
            case 13:
                $sql = "SELECT id, name FROM clients WHERE (pm_redeem_status IS NULL OR pm_redeem_status = '' ) OR (pm_redeem_status = 'yes' AND (subsidy_amount IS NULL OR subsidy_amount = 0 OR subsidy_redeem_date IS NULL OR subsidy_redeem_date = '0000-00-00')) ORDER BY id DESC LIMIT 500";
                break;
            case 14:
                $sql = "SELECT id, name FROM clients WHERE reference_name IS NULL OR reference_name = '' OR reference_contact IS NULL OR reference_contact = '' ORDER BY id DESC LIMIT 500";
                break;
            default:
                $sql = "SELECT id, name FROM clients ORDER BY id DESC LIMIT 500";
                break;
        }
        
        $res = $db->query($sql);
        if (!$res) jsonError("DB error: " . $db->error);
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        jsonSuccess(['data' => $rows]);
        break;

    case 'delete_client':
        $clientId = intval($_REQUEST['client_id'] ?? 0);
        if ($clientId <= 0) jsonError("Invalid client_id");
        try {
            $stmt = $db->prepare("DELETE FROM solar_panels WHERE client_id = ?");
            $stmt->bind_param("i",$clientId); $stmt->execute(); $stmt->close();
            $stmt = $db->prepare("DELETE FROM client_documents WHERE client_id = ?");
            $stmt->bind_param("i",$clientId); $stmt->execute(); $stmt->close();
            $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->bind_param("i",$clientId); $stmt->execute(); $stmt->close();

            $uploadDir = __DIR__ . "/../uploads/clients/{$clientId}";
            if (is_dir($uploadDir)) {
                $it = new RecursiveDirectoryIterator($uploadDir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if ($file->isDir()) @rmdir($file->getRealPath()); else @unlink($file->getRealPath());
                }
                @rmdir($uploadDir);
            }
            jsonSuccess(['message' => 'Client deleted']);
        } catch (Exception $e) {
            jsonError("Delete failed: " . $e->getMessage());
        }
        break;

    default:
        jsonError("Unknown action: {$action}");
}
