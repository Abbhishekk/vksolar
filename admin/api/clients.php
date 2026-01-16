<?php
// api/clients.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../connect/db.php';


$method = $_SERVER['REQUEST_METHOD'];

// helper: get incomplete clients (mysqli)
function getIncompleteClientsMysqli($conn, $step, $limit = 500, $include_client_id = 0) {
    $sql = '';
    switch ($step) {
        // 1 - Basic Details (name or consumer_number missing)
        case 1:
            $sql = "SELECT id, name FROM clients
                    WHERE (name IS NULL OR name = '')
                       OR (consumer_number IS NULL OR consumer_number = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 2 - Communication & Address
        case 2:
            // treat as incomplete when both mobile and email empty OR any address field empty
            $sql = "SELECT id, name FROM clients
                    WHERE (
                        ((mobile IS NULL OR mobile = '') AND (email IS NULL OR email = ''))
                        OR (district IS NULL OR district = '')
                        OR (block IS NULL OR block = '')
                        OR (taluka IS NULL OR taluka = '')
                        OR (village IS NULL OR village = '')
                    )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 3 - MAHADISCOM Email & Mobile Update
        case 3:
            $sql = "SELECT id, name FROM clients
                    WHERE (mahadiscom_email IS NULL OR mahadiscom_email = '')
                       OR (mahadiscom_email_password IS NULL OR mahadiscom_email_password = '')
                       OR (mahadiscom_mobile IS NULL OR mahadiscom_mobile = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 4 - MAHADISCOM Registration
        case 4:
            $sql = "SELECT id, name FROM clients
                    WHERE (mahadiscom_user_id IS NULL OR mahadiscom_user_id = '')
                       OR (mahadiscom_password IS NULL OR mahadiscom_password = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 5 - Name Change Require
        case 5:
            $sql = "SELECT id, name FROM clients
                    WHERE (name_change_require IS NULL OR name_change_require = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 6 - PM Suryaghar Registration
        case 6:
            // incomplete if registration not set or (registered yes but app id/date missing)
            $sql = "SELECT id, name FROM clients
                    WHERE (pm_suryaghar_registration IS NULL OR pm_suryaghar_registration = '')
                       OR (
                           pm_suryaghar_registration = 'yes'
                           AND (
                               pm_suryaghar_app_id IS NULL OR pm_suryaghar_app_id = ''
                               OR pm_registration_date IS NULL OR pm_registration_date = '0000-00-00'
                           )
                       )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 7 - MAHADISCOM Sanction Load
        case 7:
            $sql = "SELECT id, name FROM clients
                    WHERE (load_change_application_number IS NULL OR load_change_application_number = '')
                       OR (rooftop_solar_application_number IS NULL OR rooftop_solar_application_number = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 8 - Bank Loan
        case 8:
            // include if bank_loan_status not set OR if 'yes' but bank details missing
            $sql = "SELECT id, name FROM clients
                    WHERE (bank_loan_status IS NULL OR bank_loan_status = '')
                       OR (
                           bank_loan_status = 'yes'
                           AND (
                               bank_name IS NULL OR bank_name = ''
                               OR account_number IS NULL OR account_number = ''
                               OR loan_amount IS NULL OR loan_amount = 0
                           )
                       )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 9 - Fitting Photos (inverter info)
        case 9:
            $sql = "SELECT id, name FROM clients
                    WHERE (inverter_company_name IS NULL OR inverter_company_name = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 10 - PM SuryaGhar Document Upload (inverter/system + required docs)
        case 10:
            $sql = "SELECT DISTINCT c.id, c.name
                    FROM clients c
                    LEFT JOIN client_documents da ON da.client_id = c.id AND da.document_type = 'aadhar'
                    LEFT JOIN client_documents dp ON dp.client_id = c.id AND dp.document_type = 'pan_card'
                    LEFT JOIN client_documents eb ON eb.client_id = c.id AND eb.document_type = 'electric_bill'
                    LEFT JOIN client_documents bp ON bp.client_id = c.id AND bp.document_type = 'bank_passbook'
                    WHERE (
                        c.inverter_company_name IS NULL OR c.inverter_company_name = ''
                        OR c.inverter_serial_number IS NULL OR c.inverter_serial_number = ''
                        OR c.dcr_certificate_number IS NULL OR c.dcr_certificate_number = ''
                        OR c.number_of_panels IS NULL OR c.number_of_panels = 0
                    )
                    OR da.id IS NULL OR dp.id IS NULL OR eb.id IS NULL OR bp.id IS NULL
                    ORDER BY c.id DESC
                    LIMIT ?";
            break;

        // 11 - RTS Portal Status
        case 11:
            $sql = "SELECT id, name FROM clients
                    WHERE rts_portal_status IS NULL OR rts_portal_status = '' OR rts_portal_status = 'no'
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 12 - Meter Installation Photo
        case 12:
            // include client if meter_number/date missing OR meter document missing
            $sql = "SELECT DISTINCT c.id, c.name
                    FROM clients c
                    LEFT JOIN client_documents mp ON mp.client_id = c.id AND mp.document_type = 'meter_photo'
                    WHERE (c.meter_number IS NULL OR c.meter_number = '')
                       OR (c.meter_installation_date IS NULL OR c.meter_installation_date = '0000-00-00')
                       OR mp.id IS NULL
                    ORDER BY c.id DESC
                    LIMIT ?";
            break;

        // 13 - PM Suryaghar Redeem Status
        case 13:
            $sql = "SELECT id, name FROM clients
                    WHERE
                      (
                        pm_redeem_status IS NULL
                        OR pm_redeem_status = ''
                        OR pm_redeem_status = 'no'
                      )
                      OR
                      (
                        pm_redeem_status = 'yes'
                        AND (
                          subsidy_amount IS NULL
                          OR subsidy_amount = 0
                          OR subsidy_redeem_date IS NULL
                          OR subsidy_redeem_date = '0000-00-00'
                        )
                      )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 14 - Reference
        case 14:
            $sql = "SELECT id, name FROM clients
                    WHERE reference_name IS NULL OR reference_name = ''
                       OR reference_contact IS NULL OR reference_contact = ''
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // default: return latest clients
        default:
            $sql = "SELECT id, name FROM clients ORDER BY id DESC LIMIT ?";
            break;
    }

    $results = [];

    // prepare statement
    if (!$stmt = $conn->prepare($sql)) {
        error_log('getIncompleteClientsMysqli prepare error (step ' . $step . '): ' . $conn->error . ' -- SQL: ' . $sql);
        return $results;
    }

    // bind limit param
    if (!$stmt->bind_param('i', $limit)) {
        error_log('getIncompleteClientsMysqli bind_param error: ' . $stmt->error);
        $stmt->close();
        return $results;
    }

    if (!$stmt->execute()) {
        error_log('getIncompleteClientsMysqli execute error: ' . $stmt->error);
        $stmt->close();
        return $results;
    }

    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[$row['id']] = $row['name'];
        }
    }
    $stmt->close();

    // If include_client_id requested and not present, fetch that client and prepend it
    if ($include_client_id && !isset($results[$include_client_id])) {
        if ($qr = $conn->prepare("SELECT id, name FROM clients WHERE id = ? LIMIT 1")) {
            $qr->bind_param('i', $include_client_id);
            $qr->execute();
            $r2 = $qr->get_result();
            if ($r2 && $row2 = $r2->fetch_assoc()) {
                // prepend selected client at top
                $results = array($row2['id'] => $row2['name']) + $results;
            }
            $qr->close();
        } else {
            error_log('getIncompleteClientsMysqli include_client prepare error: ' . $conn->error);
        }
    }

    return $results;
}


// Read and validate inputs
$step = isset($_GET['step']) ? (int) $_GET['step'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 500;
$include_client_id = isset($_GET['include_client_id']) ? (int) $_GET['include_client_id'] : 0;

// Sanity limits
if ($limit < 1) $limit = 1;
if ($limit > 5000) $limit = 5000;

// Call your function and convert result into JSON array of {id,name}
try {
    $clientsAssoc = getIncompleteClientsMysqli($conn, $step, $limit, $include_client_id);
    $clients = [];
    foreach ($clientsAssoc as $id => $name) {
        $clients[] = ['id' => (int)$id, 'name' => $name];
    }

    echo json_encode($clients);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
