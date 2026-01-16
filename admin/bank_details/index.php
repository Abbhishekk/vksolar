<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';

$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin', 'office_staff', 'sales_marketing', 'warehouse_staff']);
$auth->requirePermission('bank_details_management', 'view');

$title = "Company Bank Details";

/* =========================
   FETCH BANK DETAILS
========================= */
$result = $conn->query("
    SELECT 
        id,
        bank_name,
        branch_name,
        account_number,
        account_type,
        ifsc_code,
        bank_gst,
        is_active,
        created_at
    FROM company_bank_details
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../include/head2.php'; ?>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        :root {
            --primary-color: #2E8B57;
            --secondary-color: #3CB371;
            --accent-color: #FFD700;
            --light-color: #f8f9fa;
            --dark-color: #1e2a4a;
            --success-color: #28a745;
            --border-radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f8f0 0%, #e0f0e0 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .professional-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .professional-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.1"><path fill="white" d="M50,10 L60,40 L90,40 L65,60 L75,90 L50,70 L25,90 L35,60 L10,40 L40,40 Z"/></svg>');
            background-size: 200px;
            opacity: 0.1;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
        }
        
        .header-content h1 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.8rem;
        }
        
        .header-content p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .main-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .professional-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .professional-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 0 !important;
            font-weight: 600;
            padding: 18px 25px;
            border-bottom: 3px solid var(--accent-color);
        }
        
        .card-header h5 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            border-left-color: var(--accent-color);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .section-title i {
            color: var(--accent-color);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control {
            border-radius: 6px;
            padding: 12px 15px;
            border: 1px solid #d1d5e0;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 139, 87, 0.15);
        }
        
        .btn-professional {
            border-radius: 6px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 139, 87, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #3CB371, #2E8B57);
            border: none;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(60, 179, 113, 0.3);
        }
        
        .btn-outline-secondary {
            border: 1px solid #6c757d;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }
        
        .preview-controls {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .preview-title {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Declaration Preview Styling - Clean without borders */
        #declarationPreview {
            background-color: white;
            padding: 30px;
            margin-top: 20px;
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.5;
            font-size: 12pt;
            color: #000;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .declaration-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        
        .declaration-header h2 {
            color: #000;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 18pt;
        }
        
        .declaration-content {
            line-height: 1.5;
        }
        
        .declaration-section {
            margin-bottom: 15px;
        }
        
        .declaration-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #000;
            font-size: 12pt;
        }
        
        .signature-area {
            margin-top: 40px;
            padding-top: 20px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin-bottom: 5px;
            height: 20px;
        }
        
        .highlight {
            background-color: #f0f0f0;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: 600;
            color: #000;
        }
        
        .hidden {
            display: none;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 150px;
        }
        
        .step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 25px;
            right: -75px;
            width: 150px;
            height: 2px;
            background: #d1d5e0;
        }
        
        .step.active:not(:last-child)::after {
            background: var(--primary-color);
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 10px;
            border: 3px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .step.active .step-circle {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .step.completed .step-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6c757d;
            text-align: center;
        }
        
        .step.active .step-text {
            color: var(--primary-color);
        }
        
        .step.completed .step-text {
            color: var(--primary-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        /* PDF-specific styles */
        .pdf-content {
            margin: 0;
            padding: 0;
        }
        
        .pdf-page {
            page-break-after: always;
            margin: 0;
            padding: 20mm 15mm;
        }
        
        .pdf-page:last-child {
            page-break-after: auto;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            #declarationPreview, #declarationPreview * {
                visibility: visible;
            }
            #declarationPreview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            .preview-controls, .professional-header {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .preview-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .step:not(:last-child)::after {
                display: none;
            }
            
            .step-indicator {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            
            #declarationPreview {
                padding: 15px;
            }
            
            .signature-area {
                flex-direction: column;
                gap: 30px;
            }
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .status-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Remove all extra spacing for PDF */
        .pdf-optimized * {
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        
        .pdf-optimized p {
            margin-bottom: 8px;
        }
        
        .pdf-optimized .declaration-section {
            margin-bottom: 12px;
        }
        
        /* PV Module details styling without table */
        .pv-module-details {
            margin: 15px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .pv-module-item {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }
        
        .pv-module-label {
            font-weight: bold;
            min-width: 180px;
            color: #333;
        }
        
        .pv-module-value {
            color: #000;
            flex: 1;
        }
        
        .pv-module-item:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>

<!-- ===================== SIDEBAR ===================== -->
<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/sidebar.php';
chdir($cwd);
?>

<div id="main-content">

<!-- ===================== NAVBAR ===================== -->
<?php
$cwd = getcwd();
chdir(__DIR__ . '/..');
include 'include/navbar.php';
chdir($cwd);
?>

<header class="professional-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="logo-container">
                    <i class="bi bi-bank logo-icon"></i>
                    <div class="header-content">
                        <h1>Company Bank Details</h1>
                        <p>Manage bank accounts used for quotations & payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($auth->checkPermission('bank_details_management', 'create')): ?>
                <a href="create.php" class="btn btn-light btn-professional">
                    <i class="bi bi-plus-circle"></i> Add New Bank
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<div class="alert alert-primary text-center text-dark fw-bold mb-4">
    Bank Details List
</div>

<div class="main-container">

    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success">Bank details saved successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])) : ?>
        <div class="alert alert-success">Bank details updated successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])) : ?>
        <div class="alert alert-warning">Bank deactivated successfully.</div>
    <?php endif; ?>

    <div class="professional-card">
        <div class="card-header">
            <h5><i class="bi bi-list-ul"></i> Registered Bank Accounts</h5>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Bank Name</th>
                        <th>Branch</th>
                        <th>Account No</th>
                        <th>IFSC</th>
                        <th>Account Type</th>
                        <th>Status</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if ($result && $result->num_rows > 0):
                    $i = 1;
                    while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>

                        <td class="fw-bold"><?= htmlspecialchars($row['bank_name']) ?></td>

                        <td><?= htmlspecialchars($row['branch_name']) ?></td>

                        <!-- Mask Account Number -->
                        <td>
                            ****<?= substr($row['account_number'], -4) ?>
                        </td>

                        <td><?= htmlspecialchars($row['ifsc_code']) ?></td>

                        <td><?= htmlspecialchars($row['account_type']) ?></td>

                        <td class="text-center">
                            <?php if ($row['is_active']) : ?>
                                <span class="badge bg-success">Active</span>
                            <?php else : ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if ($auth->checkPermission('bank_details_management', 'edit')): ?>
                            <a href="edit.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($row['is_active'] && $auth->checkPermission('bank_details_management', 'delete')): ?>
                                <a href="delete.php?id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Deactivate this bank?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No bank records found.
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>

        </div>
    </div>
</div>

</div>
</body>
</html>
