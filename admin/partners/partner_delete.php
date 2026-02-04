<?php
// admin/partners/partner_delete.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);
$auth->requirePermission('partner_management', 'delete');

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    $_SESSION['error'] = 'Invalid partner ID.';
    header('Location: partners.php');
    exit;
}

// OPTIONAL: fetch for confirmation / logging
$stmt = $conn->prepare("
    SELECT company_name, type 
    FROM vendors_retailers 
    WHERE id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$partner = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$partner) {
    $_SESSION['error'] = 'Partner not found.';
    header('Location: partners.php');
    exit;
}

// DELETE
$del = $conn->prepare("DELETE FROM vendors_retailers WHERE id = ?");
$del->bind_param('i', $id);

if ($del->execute()) {
    $_SESSION['success'] = $partner['company_name'] . ' deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete partner.';
}

$del->close();

header('Location: partners.php');
exit;
