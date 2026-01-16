<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin','admin','office_staff']);
$auth->requirePermission('quotation_management', 'create');
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
$response = ['status' => false, 'message' => 'Invalid request'];
//   print_r($data);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $quotation_id = $data['quotation_id'] ?? null;
    $client_id    = $data['client_id'];
    $created_by   = $_SESSION['user_id'];

    /* ---------------- MASTER DATA ---------------- */
    $quotation_number = trim($data['quotation_number']);
    $quotation_date   = $data['quotation_date'];
    $validity_date    = $data['validity_date'];

    $customer_name    = $data['customer_name'];
    $customer_address = $data['customer_address'];
    $pin_code         = $data['pin_code'];
    $customer_phone   = $data['customer_phone'];
    $customer_email   = $data['customer_email'];

    $project_location     = $data['project_location'];
    $plant_capacity       = $data['plant_capacity'];
    $system_type          = $data['system_type'];
    $estimated_generation = $data['estimated_generation'];
    $system_description   = $data['system_description'];

    $bank_id = $data['bank_id'];

    $total_amount = $data['total_amount'];
    $subsidy      = $data['subsidy'];
    $final_amount = $data['final_amount'];

    /* ---------------- PRODUCTS ---------------- */
    $products = $data['products']; // array

    $existing = $conn->query("SELECT * FROM bank_quotations WHERE client_id = $client_id ")->fetch_assoc();
    $conn->begin_transaction();

    try {

        /* ========= INSERT OR UPDATE ========= */
        if ($quotation_id) {

            $stmt = $conn->prepare("
                UPDATE bank_quotations SET
                    quotation_number=?,
                    quotation_date=?,
                    validity_date=?,
                    customer_name=?,
                    customer_address=?,
                    pin_code=?,
                    customer_phone=?,
                    customer_email=?,
                    project_location=?,
                    plant_capacity=?,
                    system_type=?,
                    estimated_generation=?,
                    system_description=?,
                    bank_id=?,
                    total_amount=?,
                    subsidy=?,
                    final_amount=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "ssssssssssssiddddi",
                $quotation_number,
                $quotation_date,
                $validity_date,
                $customer_name,
                $customer_address,
                $pin_code,
                $customer_phone,
                $customer_email,
                $project_location,
                $plant_capacity,
                $system_type,
                $estimated_generation,
                $system_description,
                $bank_id,
                $total_amount,
                $subsidy,
                $final_amount,
                $quotation_id
            );

            $stmt->execute();

            // delete old products
            $conn->query("DELETE FROM bank_quotation_products WHERE quotation_id = $quotation_id");

        } else {

            $stmt = $conn->prepare("
                INSERT INTO bank_quotations (
                    client_id, quotation_number, quotation_date, validity_date,
                    customer_name, customer_address, pin_code, customer_phone, customer_email,
                    project_location, plant_capacity, system_type, estimated_generation,
                    system_description, bank_id,
                    total_amount, subsidy, final_amount, created_by
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "issssssssssssssdddi",
                $client_id,
                $quotation_number,
                $quotation_date,
                $validity_date,
                $customer_name,
                $customer_address,
                $pin_code,
                $customer_phone,
                $customer_email,
                $project_location,
                $plant_capacity,
                $system_type,
                $estimated_generation,
                $system_description,
                $bank_id,
                $total_amount,
                $subsidy,
                $final_amount,
                $created_by
            );

            $stmt->execute();
            $quotation_id = $stmt->insert_id;
        }

        /* ========= INSERT PRODUCTS ========= */
        $pstmt = $conn->prepare("
            INSERT INTO bank_quotation_products
            (quotation_id, description, quantity, unit_price, amount)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($products as $p) {
            $amount = $p['quantity'] * $p['unitPrice'];

            $pstmt->bind_param(
                "isidd",
                $quotation_id,
                $p['description'],
                $p['quantity'],
                $p['unitPrice'],
                $amount
            );
            $pstmt->execute();
        }

        $conn->commit();

        $response = [
            'status' => true,
            'quotation_id' => $quotation_id
        ];

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
