<?php
// admin/api/workflow_api.php
// API endpoint for workflow steps
header('Content-Type: application/json; charset=utf-8');

// include auth if needed
require_once __DIR__ . '/../connect/auth_middleware.php';
require_once __DIR__ . '/../connect/db1.php';
require_once __DIR__ . '/../connect/fun.php';

// Simple debug logger (appends)
function logDebug($msg) {
    $f = __DIR__ . '/debug.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    @file_put_contents($f, $line, FILE_APPEND);
}

// respond helper
function sendJson($success, $message = '', $data = []) {
    $resp = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($resp);
    exit;
}

// Establish DB connection using your connect class (db1.php)
$connect = new connect();
$db = $connect->dbconnect(); // could be PDO or mysqli
$fun = new fun($db);

// Try to read action param (GET preferred)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

logDebug("API called; action=" . var_export($action, true));

// Accept raw JSON body as well (if client posts JSON)
$rawBody = file_get_contents('php://input');
if ($rawBody && !empty($rawBody) && empty($_POST)) {
    // attempt parse JSON into $_POST-like array
    $json = json_decode($rawBody, true);
    if (is_array($json)) {
        foreach ($json as $k => $v) {
            $_POST[$k] = $v;
        }
    }
}

// Helper: normalized fetch for POST/GET
function getReq($key, $default = '') {
    if (isset($_POST[$key])) return $_POST[$key];
    if (isset($_GET[$key])) return $_GET[$key];
    return $default;
}

// Database-agnostic execute helper (prepared)
function db_insert_return_id($db, $table, $data) {
    // supports PDO and mysqli
    if ($db instanceof PDO || (isset($db) && get_class($db) === 'PDO')) {
        $cols = array_keys($data);
        $placeholders = array_map(function($c){ return ':' . $c; }, $cols);
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    } else {
        // assume mysqli-like (object)
        if (method_exists($db, 'real_escape_string')) {
            $cols = array_keys($data);
            $vals = array_map(function($v) use ($db){ return "'" . $db->real_escape_string($v) . "'"; }, array_values($data));
            $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ")";
            if ($db->query($sql)) {
                return $db->insert_id;
            }
            return false;
        } else {
            // Unknown DB type
            return false;
        }
    }
}

function db_update($db, $table, $data, $whereClause, $whereParams = []) {
    if ($db instanceof PDO || (isset($db) && get_class($db) === 'PDO')) {
        $cols = array_keys($data);
        $setParts = array_map(function($c){ return "`$c` = :$c"; }, $cols);
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE $whereClause";
        $stmt = $db->prepare($sql);
        foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
        foreach ($whereParams as $k => $v) $stmt->bindValue($k, $v);
        return $stmt->execute();
    } else {
        if (method_exists($db, 'real_escape_string')) {
            $set = [];
            foreach ($data as $k => $v) {
                $set[] = "`$k` = '" . $db->real_escape_string($v) . "'";
            }
            $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE $whereClause";
            return $db->query($sql);
        } else {
            return false;
        }
    }
}

// handle actions
switch ($action) {
    case 'save_step_data':
        try {
            $step = (string) getReq('step', '');
            $clientId = (string) getReq('client_id', 'new');

            logDebug("save_step_data called. step={$step}, client_id={$clientId}");
            logDebug("POST raw: " . print_r($_POST, true));
            logDebug("FILES raw: " . print_r($_FILES, true));

            if ($step === '') {
                sendJson(false, "Step parameter is required");
            }

            // route by step
            if ($step === '1' || $step === '01') {
                // Basic details expected: name, consumer_number
                $name = trim(getReq('name', ''));
                $consumer_number = trim(getReq('consumer_number', ''));
                $mobile = trim(getReq('mobile', ''));
                $address = trim(getReq('address', ''));

                logDebug("Step1 data captured: name=" . $name . ", consumer_number=" . $consumer_number);

                if ($name === '' || $consumer_number === '') {
                    sendJson(false, "Name and Consumer Number are required");
                }

                // Prepare insert/update data
                $now = date('Y-m-d H:i:s');
                $data = [
                    'name' => $name,
                    'consumer_number' => $consumer_number,
                    'mobile' => $mobile,
                    'address' => $address,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                // If your fun.php provides addClient/updateClient methods, prefer those.
                // Fallback: try to insert into 'clients' table (adjust table name if different).
                $newClientId = null;
                if (method_exists($fun, 'addClient')) {
                    // try to call your helper if present
                    logDebug("Calling fun->addClient()");
                    $res = $fun->addClient($data); // adapt if your signature differs
                    if ($res === false || $res === 0) {
                        // attempt fallback DB insert
                        logDebug("fun->addClient returned false/0, falling back to direct insert");
                    } else {
                        $newClientId = $res;
                    }
                }

                if ($newClientId === null) {
                    // fallback direct DB insert/update - assumes table name 'clients' with primary key 'id'
                    if ($clientId === '' || $clientId === 'new' || $clientId === '0') {
                        // insert
                        $insertData = $data;
                        // Remove updated_at if you prefer; depends on table
                        $id = db_insert_return_id($db, 'clients', $insertData);
                        if ($id === false) {
                            logDebug("Direct insert failed. Attempting to provide DB error.");
                            sendJson(false, "Failed to create client (DB insert failed)");
                        }
                        $newClientId = $id;
                    } else {
                        // update existing
                        $updateData = $data;
                        // Remove created_at for update
                        unset($updateData['created_at']);
                        $ok = db_update($db, 'clients', $updateData, "id = " . (int)$clientId);
                        if (!$ok) {
                            sendJson(false, "Failed to update client (DB update failed)");
                        }
                        $newClientId = (int)$clientId;
                    }
                }

                // success
                logDebug("Step1 save successful; client_id=" . $newClientId);
                sendJson(true, "Saved Step 1", ['client_id' => $newClientId]);
            }

            // add additional steps here (step 2, step 3...) using similar patterns
            sendJson(false, "Unhandled step: " . $step);

        } catch (Exception $ex) {
            logDebug("Exception in save_step_data: " . $ex->getMessage() . "\n" . $ex->getTraceAsString());
            sendJson(false, "Server error: " . $ex->getMessage());
        }
        break;

    default:
        sendJson(false, "No or unknown action specified");
        break;
}
