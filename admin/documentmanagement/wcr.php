<?php
// admin/add_user.php
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin','office_staff']);
$auth->checkPermission('reports', 'create');
$auth->requirePermission('reports', 'create');

$title = "wcr";
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <?php require_once __DIR__ . '/../include/head3.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate/Undertaking Generator | Solar Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            max-width: 1200px;
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
        
        /* Report Preview Styling - Black and White for PDF */
        #reportPreview {
            background-color: white;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            margin-top: 10px;
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            font-size: 12pt;
            color: #000 !important;
        }
        
        .report-header {
            text-align: center;
            /*margin-bottom: 30px;*/
            /*padding-bottom: 20px;*/
            /*border-bottom: 2px solid #000;*/
        }
        
        .report-header h2 {
            color: #000 !important;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 20pt;
        }
        
        .report-header p {
            color: #000 !important;
            margin-bottom: 5px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            color: #000 !important;
        }
        
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            color: #000 !important;
        }
        
        .report-table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            color: #000 !important;
        }
        
        .certification-box {
            background-color: #f9f9f9;
            border: 1px solid #000;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
            color: #000 !important;
        }
        
        .signature-area {

        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin-bottom: 8px;
            height: 25px;
        }
        
        .highlight {
            background-color: #f0f0f0 !important;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
            color: #000 !important;
        }
        
        .report-footer {
            margin-top: 40px;
            font-size: 10pt;
            color: #000 !important;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 15px;
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
        
        /* Page break for second page - FIXED */
        .pdf-page-break {
            
            margin-top: 0;
            padding-top: 0;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            #reportPreview, #reportPreview * {
                visibility: visible;
            }
            #reportPreview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                padding: 20px;
                margin: 0;
                max-width: 100%;
            }
            .preview-controls, .professional-header {
                display: none;
            }
            /* Ensure no blank pages */
            .pdf-page-break {
             
                margin-top: 0;
                padding-top: 0;
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
            
            .report-table {
                font-size: 8px;
                
            }
            
            .report-table th, .report-table td {
                
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
        /* ===== PDF / PRINT CONTROL ===== */
@media print {

    .pdf-page {
        page-break-after: always;
        break-after: page;
    }

    .page-1 {
        page-break-after: always;
    }

    .page-2 {
        page-break-before: always;
    }

    /* Prevent breaking important blocks */
    table, tr, td, th {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .certification-box,
    .signature-area {
      
    }

    /* Remove extra margins */
    body {
        margin: 0;
        padding: 0;
    }
    @media print {
    .no-print {
        display: none !important;
    }
}
}

    </style>
</head>
<body>
         <!-- Sidebar -->
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
    <!-- Professional Header -->
    <header class="professional-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="logo-container">
                        <i class="bi bi-sun-fill logo-icon"></i>
                        <div class="header-content">
                            <h1>Solar Plant Work Completion Report</h1>
                            <p>Generate Work Completion Report and Guarantee Certificate</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="status-badge me-3"><i class="bi bi-check-circle-fill"></i> Active</span>
                    <button class="btn btn-light btn-professional" id="showFormBtn">
                        <i class="bi bi-pencil-square"></i> Edit Form
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-circle">1</div>
                <div class="step-text">Enter Details</div>
            </div>
            <div class="step" id="step2">
                <div class="step-circle">2</div>
                <div class="step-text">Preview Report</div>
            </div>
            <div class="step" id="step3">
                <div class="step-circle">3</div>
                <div class="step-text">Download/Print</div>
            </div>
        </div>

        <!-- Form Section -->
        <div id="formSection">
            <div class="professional-card">
                <div class="card-header">
                    <h5><i class="bi bi-clipboard-data"></i> Work Completion Report Form</h5>
                </div>
                <div class="card-body">
                    <form id="reportForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-vcard"></i> Basic Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name" class="required">Name of Customer</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="consumer_number" class="required">Consumer Number</label>
                                    <input type="text" class="form-control" id="consumer_number" name="consumer_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="required">Site/Location with Complete Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="category" class="required">Category</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Govt">Government</option>
                                        <option value="Private">Private Sector</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="sanction_number" class="required">Sanction Number</label>
                                    <input type="text" class="form-control" id="sanction_number" name="sanction_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="sanctioned_capacity" class="required">Sanctioned Capacity (KW)</label>
                                    <input type="number" step="0.1" class="form-control" id="sanctioned_capacity" name="sanctioned_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="installed_capacity" class="required">Installed Capacity (KW)</label>
                                    <input type="number" step="0.1" class="form-control" id="installed_capacity" name="installed_capacity" required>
                                </div>
                            </div>
                        </div>

                        <!-- Module Specifications -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-sun"></i> Specification of the Modules (Pannel)
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="module_make" class="required">Make of Module</label>
                                    <input type="text" class="form-control" id="module_make" name="module_make" required>
                                </div>
                                <div class="form-group">
                                    <label for="almm_model" class="required">ALMM Model Number</label>
                                    <input type="text" class="form-control" id="almm_model" name="almm_model" required>
                                </div>
                                <div class="form-group">
                                    <label for="wattage_per_module" class="required">Wattage per Module</label>
                                    <input type="number" class="form-control" id="wattage_per_module" name="wattage_per_module" required>
                                </div>
                                <div class="form-group">
                                    <label for="number_of_modules" class="required">Number of Modules</label>
                                    <input type="number" class="form-control" id="number_of_modules" name="number_of_modules" required>
                                </div>
                                <div class="form-group">
                                    <label for="total_capacity" class="required">Total Capacity (KWP)</label>
                                    <input type="number" step="0.1" class="form-control" id="total_capacity" name="total_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="warranty_details" class="required">Warranty Details (Product + Performance)</label>
                                    <textarea class="form-control" id="warranty_details" name="warranty_details" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- PCU Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-lightning-charge"></i> PCU Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="inverter_make" class="required">Inverter Make</label>
                                    <input type="text" class="form-control" id="inverter_make" name="inverter_make" required>
                                </div>
                                <div class="form-group">
                                    <label for="inverter_capacity" class="required">Inverter Capacity</label>
                                    <input type="text" class="form-control" id="inverter_capacity" name="inverter_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="inverter_serial_no" class="required">Serial Number of Inverter</label>
                                    <input type="text" class="form-control" id="inverter_serial_no" name="inverter_serial_no" required>
                                </div>
                                <div class="form-group">
                                    <label for="inverter_rating" class="required">Rating</label>
                                    <input type="text" class="form-control" id="inverter_rating" name="inverter_rating" required>
                                </div>
                                <div class="form-group">
                                    <label for="charge_controller" class="required">Type of Charge Controller/MPPT</label>
                                    <input type="text" class="form-control" id="charge_controller" name="charge_controller" required>
                                </div>
                                <div class="form-group">
                                    <label for="hpd">HPD</label>
                                    <input type="text" class="form-control" id="hpd" name="hpd">
                                </div>
                                <div class="form-group">
                                    <label for="manufacturing_year" class="required">Year of Manufacturing</label>
                                    <input type="number" class="form-control" id="manufacturing_year" name="manufacturing_year" min="2000" max="2030" required>
                                </div>
                            </div>
                        </div>

                        <!-- Earthing and Protections -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-shield-check"></i> Earthing and Protections
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="earthings_count" class="required">Number of Separate Earthings with Earth Resistance</label>
                                    <input type="text" class="form-control" id="earthings_count" name="earthings_count" value="3 " required readonly>
                                </div>
                                <div class="form-group">
                                    <label for="lightening_arrester" class="required">Lightening Arrester</label>
                                    <select class="form-control" id="lightening_arrester" name="lightening_arrester" required readonly>
                                        <option value="">Select Option</option>
                                        <option value="Installed" selected>Installed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Vendor and Consumer Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-building"></i> Vendor and Consumer Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="vendor_name" class="required">Vendor Name</label>
                                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="adhar" class="required">Aadhar Number</label>
                                    <input type="text" class="form-control" id="adhar" name="adhar" required>
                                    
                                </div>
                                <div class="form-group">
                                    <label for="report_date" class="required">Report Date</label>
                                    <input type="date" class="form-control" id="report_date" name="report_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-primary btn-professional" id="generateBtn">
                                <i class="bi bi-eye"></i> Generate Report Preview
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-professional">
                                <i class="bi bi-arrow-clockwise"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Preview Section -->
        <div id="previewSection" class="hidden">
            <div class="preview-controls">
                <h3 class="preview-title"><i class="bi bi-file-text"></i> Work Completion Report Preview</h3>
                <div class="action-buttons">
                    <button class="btn btn-success btn-professional" id="downloadPdfBtn">
                        <i class="bi bi-download"></i> Download as PDF
                    </button>
                    <button class="btn btn-primary btn-professional" id="printBtn">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                    <button class="btn btn-outline-secondary btn-professional" id="backToFormBtn">
                        <i class="bi bi-arrow-left"></i> Back to Form
                    </button>
                </div>
            </div>

            <div id="reportPreview">
                <!-- Report content will be generated here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('report_date').valueAsDate = new Date();
            
            // Set default manufacturing year to current year
            document.getElementById('manufacturing_year').value = new Date().getFullYear();
            
            // Event listeners
            document.getElementById('generateBtn').addEventListener('click', generateReport);
            document.getElementById('showFormBtn').addEventListener('click', showForm);
            document.getElementById('backToFormBtn').addEventListener('click', showForm);
            document.getElementById('downloadPdfBtn').addEventListener('click', downloadPDF);
            document.getElementById('printBtn').addEventListener('click', printReport);
            
            // Update step indicator
            updateStepIndicator(1);
        });
        
        function updateStepIndicator(step) {
            // Reset all steps
            document.querySelectorAll('.step').forEach(el => {
                el.classList.remove('active', 'completed');
            });
            
            // Set active and completed steps
            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById(`step${i}`);
                if (i < step) {
                    stepEl.classList.add('completed');
                } else if (i === step) {
                    stepEl.classList.add('active');
                }
            }
        }
        
        function generateReport() {
            fetch("save_wcr", {
                method: "POST",
                body: new FormData(document.getElementById('reportForm'))
            });

            // Get form values
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate form
            if (!data.name || !data.consumer_number || !data.address || !data.sanction_number || 
                !data.sanctioned_capacity || !data.installed_capacity) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Format date
            const reportDate = new Date(data.report_date);
            const formattedDate = reportDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Generate report HTML
            const reportHTML = createReportHTML(data, formattedDate);
            
            // Display report
            document.getElementById('reportPreview').innerHTML = reportHTML;
            
            // Show preview section and hide form
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function createReportHTML(data, formattedDate) {
            return `
                <div class="report-content">
                    <!-- First Page -->
                    <div class="pdf-page page-1">
                    <div class="report-header"  >
                        <h4>Work Completion Report for Solar Power Plant</h4>
                    </div>
                    <table class="report-table" style="font-size:13px; " cellpadding="4px">
                        <tr>
                            <th style="text-align:center">Sr.No</th>
                            <th style="text-align:center">Component</th>
                            <th style="text-align:center">Observation</th>
                        </tr>
                        <tr>
                            <th>1</th>
                            <th>Name</th>
                            <td>${data.name}</td>
                        </tr>
                        <tr>
                            <th>2</th>
                            <th>Consumer Number</th>
                            <td>${data.consumer_number}</td>
                        </tr>
                        <tr>
                            <th>3</th>
                            <th>Site/Location With Complete Address</th>
                            <td>${data.address}</td>
                        </tr>
                        <tr>
                            <th>4</th>
                            <th>Category: Govt/Private Sector</th>
                            <td>${data.category}</td>
                        </tr>
                        <tr>
                            <th>5</th>
                            <th>Sanction Number</th>
                            <td>${data.sanction_number}</td>
                        </tr>
                        <tr>
                            <th rowspan="2">6</th>
                            <th>Sanctioned Capacity of Solar PV System (KW) Installed</th>
                            <td>${data.sanctioned_capacity} KW</td>
                        </tr>
                        <tr>
                            <th>Capacity of Solar PV System (KW)</th>
                            <td>${data.installed_capacity} KW</td>
                        </tr>
                        <tr>
                            <th rowspan="7" >7</th>
                            <th colspan="2" style="text-align:center">Specification of the Modules</th>
                        </tr>
                        <tr>
                            <th>Make of Module</th>
                            <td>${data.module_make}</td>
                        </tr>
                        <tr>
                            <th>ALMM Model Number</th>
                            <td>${data.almm_model}</td>
                        </tr>
                        <tr>
                            <th>Wattage per Module</th>
                            <td>${data.wattage_per_module} WP</td>
                        </tr>
                        <tr>
                            <th>No. of Module</th>
                            <td>${data.number_of_modules}</td>
                        </tr>
                        <tr>
                            <th>Total Capacity (KWP)</th>
                            <td>${data.total_capacity} KWP</td>
                        </tr>
                        <tr>
                            <th>Warranty Details (Product + Performance)</th>
                            <td>${data.warranty_details}</td>
                        </tr>
                        <tr>
                            <th rowspan="7" >8</th>
                            <th colspan="2" style="text-align:center">PCU</th>
                        </tr>
                        <tr>
                            <th>Make & Model Number of Inverter</th>
                            <td>${data.inverter_make_model}</td>
                        </tr>
                        <tr>
                            <th>Rating</th>
                            <td>${data.inverter_rating}</td>
                        </tr>
                        <tr>
                            <th>Type of Charge Controller/MPPT</th>
                            <td>${data.charge_controller}</td>
                        </tr>
                        <tr>
                            <th>Capacity of Inverter</th>
                            <td>${data.inverter_capacity}</td>
                        </tr>
                        <tr>
                            <th>HPD</th>
                            <td>${data.hpd || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Year of Manufacturing</th>
                            <td>${data.manufacturing_year}</td>
                        </tr>
                        <tr>
                            <th rowspan="6">9</th>
                            <th colspan="2" style="text-align:center">Earthing and Protections</th>
                        </tr>
                        <tr>
                            <th>No of Separate Earthings with Earth Resistance</th>
                            <td>${data.earthings_count}</td>
                        </tr>
                        <tr>
                            <th colspan="2"> 
                            It is certified that the Earth Resistance measure in presence of Licensed Electrical Contractor/Supervisor and found in order i.e. < 5 Ohms as per MNRE OM Dtd. 07.06.24 for CFA Component.
                            </th>
                        </tr>
                        <tr>
                            <th>Lightening Arrester</th>
                            <td>${data.lightening_arrester}/0.5 Ohms</td>
                        </tr>
                        <tr>
                            <th>DC</th>
                            <td>0.7 Ohms</td>
                        </tr>
                        <tr>
                            <th>AC</th>
                            <td>0.9 Ohms</td>
                        </tr>
                    </table>
                </div>
                   <!-- Second Page - Guarantee Certificate -->
                <div class="pdf-page page-2"st>
                    <div class="report-header no-print"  >
                        <h4 style="color:white;">Work Completion Report for Solar Power Plant</h4>
                    </div>
                    <div class="certification-box">
                        <p>We <span class="highlight">${data.vendor_name}</span> & <span class="highlight">${data.name}</span> bearing Consumer Number <span class="highlight">${data.consumer_number}</span> Ensured structural stability of installed solar power plant and obtained requisite permissions from the concerned authority. If in future, by virtue of any means due to collapsing or damage to installed solar power plant, MSEDCL will not be held responsible for any loss to property or human life, if any.</p>
                        <p>This is to Certified above Installed Solar PV System is working properly with electrical safety & Islanding switch in case of any presence of backup inverter an arrangement should be made in such way the backup inverter supply should never be synchronized with solar inverter to avoid any electrical accident due to back feeding. We will be held responsible for non-working of islanding mechanism and back feed to the de-energized grid.</p>
                    </div>

                    <div class="signature-area">
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>Signature [Vendor]</strong></p>
                                <div>
                                     <img src="https://vksolarenergy.com/admin/documentmanagement/img/sign.png" style="width: 50%; height : 50px;object-fit: contain;" >
                                </div>
                                <p>Name: ${data.vendor_name}</p>
                                
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Signature [Consumer]</strong></p>
                                <div>
                                <img  src="https://vksolarenergy.com/${wcrData.client_signature}" style="width: 50%; height : 50px; object-fit: contain;" >
                            </div>
                                <p>Name: ${data.name}${data.client_signature}</p>
                               
                            </div>
                        </div>
                    </div>
                        <div class="report-header">
                            <h2>Guarantee Certificate Undertaking</h2>
                            <p>To be submitted by VENDOR</p>
                        </div>

                        <div class="certification-box">
                            <p>The undersigned will provide the services to the consumers for repairs/maintenance of the RTS plant free of cost for 5 years of the comprehensive Maintenance Contract (CMC) period from the date of commissioning of the plant. Non performing/under-performing system component will be replaced/repaired free of cost in the CMC period.</p>
                        </div>

                        <div class="signature-area">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>Signature [Vendor]</strong></p>
                                    <div>
                                     <img src="https://vksolarenergy.com/admin/documentmanagement/img/sign.png" style="width: 30%; height : 50px; object-fit: contain;" >
                                     </div>
                                    <p>Name: ${data.vendor_name}</p>
                                    </div>
                                <div class="col-sm-6">
                                    <p><strong>Stamp & Seal</strong></p>
                                    <div>
                                     <img src="https://vksolarenergy.com/admin/documentmanagement/img/stamp.png" style="width: 30%; height : 50px; object-fit: contain;" >
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pdf-page page-3">
                        <div class="report-header no-print"  >
                        <h4 style="color:white;">Work Completion Report for Solar Power Plant</h4>
                        <h4 style="color:white;">Work Completion Report for Solar Power Plant</h4>
                        <h4 style="color:white;">Work Completion Report for Solar Power Plant</h4>
                        </div>
                        <div class="certification-box mt-4 pt-4">
                            <h5>Identity Details of Consumer:</h5>
                            <p><strong>Aadhar Number:</strong> ${data.adhar}</p>
                            <p><strong>Upload Xerox of AADHAR CARD HERE</strong></p>
                            <div>
                                <img crossorigin="anonymous" src="https://vksolarenergy.com/${wcrData.adhar_path}" style="max-height: 320px; width: 100%; object-fit: contain;" >
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function showForm() {
            document.getElementById('formSection').classList.remove('hidden');
            document.getElementById('previewSection').classList.add('hidden');
            updateStepIndicator(1);
        }
        
        function downloadPDF() {
            const element = document.getElementById('reportPreview');
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options - optimized for black and white and no blank pages
            const opt = {
                margin: [10, 10, 10, 10], // Equal margins on all sides
                filename: `Work_Completion_Report_${document.getElementById('consumer_number').value}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#FFFFFF'
                    },
            
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    }
            };
            
            // Generate PDF directly from the element
            html2pdf().set(opt).from(element).save();
        }
        
        function printReport() {
            // Update step indicator
            updateStepIndicator(3);
            
            // Print the report directly
            window.print();
        }
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    // console.log(wcrData);
    if (typeof wcrData === 'undefined') return;

    Object.keys(wcrData).forEach(key => {
        const el = document.getElementById(key);
        // console.log(el);
        if (el && wcrData[key] !== null && wcrData[key] !== '') {
            el.value = wcrData[key];
        }
    });
});
</script>

</body> 
</html>
