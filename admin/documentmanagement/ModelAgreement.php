<?php
// admin/add_user.php
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin','office_staff']);
$auth->checkPermission('reports', 'create');
$auth->requirePermission('reports', 'create');

$title = "model_agreement";
?><!DOCTYPE html>
<html lang="en">
<head>
   <?php require_once __DIR__ . '/../include/head3.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Installation Agreement Generator | MSEDCL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #3BAF6E;    /* main color as requested */
            --secondary-color: #2F8E58;  /* darker green for gradient */
            --accent-color: #58D68D;     /* lighter green accent */
            --light-color: #f2fbf6;      /* soft greenish background */
            --dark-color: #114f2b;       /* dark green for headings/text */
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
            background: linear-gradient(135deg, #f2fbf6 0%, #e6f7ed 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .professional-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(47, 142, 88, 0.12);
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
            opacity: 0.08;
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
            opacity: 0.95;
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
            box-shadow: 0 6px 15px rgba(17, 79, 43, 0.05);
            margin-bottom: 25px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .professional-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(47, 142, 88, 0.08);
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
            color: var(--dark-color);
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
            box-shadow: 0 0 0 0.2rem rgba(59, 175, 110, 0.15);
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
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 175, 110, 0.28);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #34ce57);
            border: none;
            color: #fff;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.28);
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
            box-shadow: 0 4px 12px rgba(17, 79, 43, 0.04);
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
        
        /* Agreement Preview Styling */
        #agreementPreview {
            background-color: white;
            padding: 50px;
            border-radius: var(--border-radius);
            box-shadow: 0 6px 15px rgba(17, 79, 43, 0.04);
            margin-top: 10px;
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            font-size: 12pt;
            color: #333;
        }
        
        .agreement-header {
            text-align: center;
            margin-bottom: 40px;
           
        }
        
        .agreement-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 24pt;
        }
        
        .agreement-content {
            line-height: 1.3;
        }
        
        .clause {
            margin-bottom: 25px;
        }
        
        .clause-title {
            font-weight: bold;
            margin-bottom: 12px;
            color: var(--dark-color);
            font-size: 13pt;
            border-left: 3px solid var(--accent-color);
            padding-left: 10px;
        }
        
        .signature-area {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 250px;
            margin-bottom: 8px;
            height: 25px;
        }
        
        .highlight {
            background-color: #fff9c4;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .agreement-footer {
            margin-top: 40px;
            font-size: 10pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .agreement-content p {
            margin-bottom: 10px;
            text-align: justify;
        }
        
        .agreement-content .clause p {
            margin-bottom: 5px;
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
        
        @media print {
            body * {
                visibility: hidden;
            }
            #agreementPreview, #agreementPreview * {
                visibility: visible;
            }
            #agreementPreview {
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
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .agreement-content ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        
        .agreement-content li {
            margin-bottom: 8px;
        }
        /* ======================================================
   A4 MULTI-PAGE PRINT SUPPORT (ADD-ON ONLY)
   DOES NOT MODIFY EXISTING STYLES
====================================================== */
@media print {

    /* A4 page setup */
    @page {
        size: A4;
        margin: 20mm 15mm 20mm 15mm;
    }

    /* Agreement container stays clean */
    #agreementPreview {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 auto !important;
        box-shadow: none !important;
    }

    /* Page wrappers */
    .page-1,
    .page-2,
    .page-3 {
        width: 100%;
        min-height: 297mm;
        page-break-after: always;
        break-after: page;
        position: relative;
    }
    .page-1,
    .page-2,
    .page-3 {
        padding-left: 15px !important;
        padding-right: 15px !important;
        box-sizing: border-box;
        padding-top:20px !important;
    }

    .page-3 {
        page-break-after: auto;
    }

    /* Prevent ugly breaks */
    p,
    ul,
    li,
    .clause,
    .signature-area {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* Page numbers (bottom-right) */
    .page-1::after,
    .page-2::after,
    .page-3::after {
        position: absolute;
        bottom: 10mm;
        right: 15mm;
        font-size: 10pt;
        color: #444;
        counter-increment: page;
        content: "Page " counter(page);
    }

    body {
        counter-reset: page;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Footer positioning (if present) */
    .agreement-footer {
        position: fixed;
        bottom: 10mm;
        left: 0;
        right: 0;
        text-align: center;
        display:none;
    }

    /* Hide non-agreement UI */
    .step-indicator,
    .form-actions,
    .preview-controls,
    .professional-header {
        display: none !important;
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
                            <h1>Solar Installation Agreement Generator</h1>
                            <p>Create professional solar installation agreements for Rooftop Solar Programme</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
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
                    <div class="step-text">Preview Agreement</div>
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
                        <h5><i class="bi bi-file-earmark-text"></i> Solar Installation Agreement Form</h5>
                    </div>
                    <div class="card-body">
                        <form id="agreementForm">
                            <!-- Applicant Information -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="bi bi-person-vcard"></i> Applicant Information
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="applicant_name" class="required">Applicant Name</label>
                                        <input type="text" class="form-control" id="applicant_name" name="applicant_name" >
                                    </div>
                                    <div class="form-group">
                                        <label for="consumer_number" class="required">Consumer Number</label>
                                        <input type="text" class="form-control" id="consumer_number" name="consumer_number" >
                                    </div>
                                    <div class="form-group">
                                        <label for="applicant_address" class="required">Applicant Address</label>
                                        <textarea class="form-control" id="applicant_address" name="applicant_address" rows="2" ></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="agreement_date" class="required">Agreement Date</label>
                                        <input type="date" class="form-control" id="agreement_date" name="agreement_date" >
                                    </div>
                                </div>
                            </div>
    
                            <!-- Vendor Information -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="bi bi-building"></i> Vendor Information
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="vendor_name" class="required">Vendor Name</label>
                                        <input type="text" class="form-control" id="vendor_name" name="vendor_name" >
                                    </div>
                                    <div class="form-group">
                                        <label for="vendor_address" class="required">Vendor Address</label>
                                        <textarea class="form-control" id="vendor_address" name="vendor_address" rows="2" ></textarea>
                                    </div>
                                </div>
                            </div>
    
                            <!-- System Details -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="bi bi-gear"></i> System Details
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="system_capacity" class="required">System Capacity (KWp)</label>
                                        <input type="number" step="0.1" class="form-control" id="system_capacity" name="system_capacity" >
                                    </div>
                                    <div class="form-group">
                                        <label for="pv_module_make" class="required">Solar Panel Make & Model</label>
                                        <input type="text" class="form-control" id="pv_module_make" name="pv_module_make" >
                                    </div>
                                    <div class="form-group">
                                        <label for="pv_module_capacity" class="required">Panel Wattage</label>
                                        <input type="text" class="form-control" id="pv_module_capacity" name="pv_module_capacity" >
                                    </div>
                                    <div class="form-group">
                                        <label for="panel_efficiency" class="required">Panel Efficiency (%)</label>
                                        <input type="text" class="form-control" id="panel_efficiency" name="panel_efficiency" >
                                    </div>
                                    <div class="form-group">
                                        <label for="inverter_company_name" class="required">Inverter Make</label>
                                        <input type="text" class="form-control" id="inverter_company_name" name="inverter_company_name" >
                                    </div>
                                    <div class="form-group">
                                        <label for="inverter_capacity" class="required">Inverter Capacity (KW)</label>
                                        <input type="text" class="form-control" id="inverter_capacity" name="inverter_capacity" >
                                    </div>
                                </div>
                            </div>
    
                            <!-- Financial Details -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="bi bi-currency-rupee"></i> Financial Details
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="system_cost" class="required">Total System Cost (₹)</label>
                                        <input type="number" class="form-control" id="system_cost" name="system_cost" >
                                    </div>
                                    <div class="form-group">
                                        <label for="advance_percentage" class="required">Advance Payment (%)</label>
                                        <input type="number" class="form-control" id="advance_percentage" name="advance_percentage" value="10" >
                                    </div>
                                    <div class="form-group">
                                        <label for="dispatch_percentage" class="required">Payment Before Dispatch (%)</label>
                                        <input type="number" class="form-control" id="dispatch_percentage" name="dispatch_percentage" value="80" >
                                    </div>
                                    <div class="form-group">
                                        <label for="completion_percentage" class="required">Payment After Commissioning (%)</label>
                                        <input type="number" class="form-control" id="completion_percentage" name="completion_percentage" value="10" >
                                    </div>
                                </div>
                            </div>
    
                            <div class="form-actions pb-3">
                                <button type="button" class="btn btn-primary btn-professional" id="generateBtn">
                                    <i class="bi bi-eye"></i> Generate Agreement Preview
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-professional">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    
            <!-- Agreement Preview Section -->
            <div id="previewSection" class="hidden">
                <div class="preview-controls">
                    <h3 class="preview-title"><i class="bi bi-file-text"></i> Agreement Preview</h3>
                    <div class="action-buttons">
                        <button class="btn btn-success btn-professional" id="downloadPdfBtn">
                            <i class="bi bi-download"></i> Download as PDF
                        </button>
                        <button class="btn btn-primary btn-professional" id="printBtn">
                            <i class="bi bi-printer"></i> Print Agreement
                        </button>
                        <button class="btn btn-outline-secondary btn-professional" id="backToFormBtn">
                            <i class="bi bi-arrow-left"></i> Back to Form
                        </button>
                    </div>
                </div>
    
                <div id="agreementPreview">
                    <!-- Agreement content will be generated here -->
                </div>
            </div>
        </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('agreement_date').valueAsDate = new Date();
            
            // Event listeners
            document.getElementById('generateBtn').addEventListener('click', generateAgreement);
            document.getElementById('showFormBtn').addEventListener('click', showForm);
            document.getElementById('backToFormBtn').addEventListener('click', showForm);
            document.getElementById('downloadPdfBtn').addEventListener('click', downloadPDF);
            document.getElementById('printBtn').addEventListener('click', printAgreement);
            
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
        
        function generateAgreement() {
            
            fetch("save_model_agreement", {
                    method: "POST",
                    body: new FormData(document.getElementById('agreementForm'))
                });

            // Get form values
            const form = document.getElementById('agreementForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            // console.log(data);
            // Validate form
            if (!data.advance_percentage || !data.agreement_date || !data.applicant_address || 
                !data.applicant_name || !data.completion_percentage || !data.consumer_number || 
                !data.dispatch_percentage || !data.inverter_capacity || !data.inverter_company_name || 
                !data.panel_efficiency || !data.pv_module_capacity || !data.pv_module_make || 
                !data.system_capacity || !data.system_cost || !data.vendor_address || !data.vendor_name) {
                alert('Please fill in all required fields');
                return;
            }
            
            
            // Format date
            const agreementDate = new Date(data.agreement_date);
            const formattedDate = agreementDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Calculate payment amounts
            const systemCost = parseFloat(data.system_cost);
            const advanceAmount = (systemCost * parseFloat(data.advance_percentage)) / 100;
            const dispatchAmount = (systemCost * parseFloat(data.dispatch_percentage)) / 100;
            const completionAmount = (systemCost * parseFloat(data.completion_percentage)) / 100;
            
            // Generate agreement HTML
            const agreementHTML = createAgreementHTML(data, formattedDate, advanceAmount, dispatchAmount, completionAmount);
            
            // Display agreement
            document.getElementById('agreementPreview').innerHTML = agreementHTML;
            
            // Show preview section and hide form
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function createAgreementHTML(data, formattedDate,advanceAmount, dispatchAmount, completionAmount) {
        
            return `
            <div id="agreementPreview" >
            <!--first page 1 starts here -->
            <div class="page-1">
                <div class="agreement-header mb-2" style="margin-top:410px !important;">
                    <h4>Model Agreement</h4>
                    <p class="mb-0">Agreement Between CFA Applicant and the registered/empaneled Vendor for installation of</p>
                    <p class="mb-0"> rooftop solar system in residential house of the Applicant under simplified procedure of<br> Rooftop Solar Programme Ph-II</p>
                </div>
                <div class="agreement-content mt-0">
                    <p style="margin-bottom:0px; !important; ">This agreement is executed on <span class="highlight">${formattedDate}</span> for design, Installation, commissioning and five years comprehensive maintenance of rooftop solar system to be installed under simplified procedure of Rooftop Solar Programme Ph-II.</p>
                    
                    <p style="text-align: center !important; margin-top:0px; !important; "><strong>Between</strong></p>
                    
                    <p style="margin-bottom:0px; !important; "><span class="highlight">${data.applicant_name}</span> residential electricity connection with consumer number <span class="highlight">${data.consumer_number}</span> from Maharashtra State Electricity Distribution Company Limited (MSEDCL) <span class="highlight">${data.applicant_address}</span> (hereinafter referred as Applicant).</p>
                    
                    <p style="text-align: center !important;"><strong>And</strong></p>
                    
                    <p><span class="highlight">${data.vendor_name}</span> is registered/ empanelled vendor with the Maharashtra State Electricity Distribution Company Limited (MSEDCL) (hereinafter referred as DISCOM) and is having registered/functional office <span class="highlight">${data.vendor_address}</span>. Both Applicant and the Vendor are jointly referred as Parties.</p>
                    
                    <p><strong>Whereas</strong></p>
                    
                    <ul>
                        <li>The Applicant intends to install rooftop solar system under simplified procedure of Rooftop Solar Programme Ph-II of the MNRE.</li>
                        <li>The Vendor is registered/empanelled vendor with DISCOM for installation of rooftop solar under MNRE Schemes. The Vendor satisfies all the existing regulation pertaining to electrical safety and license in the respective state and it is not debarred or blacklisted from undertaking any such installations by any state/central Government agency.</li>
                        <li>Both the parties are mutually agreed and understand their roles and responsibilities and have no liability to any other agency/firm/stakeholder especially to DISCOM and MNRE.</li>
                    </ul>
                 </div>
                 <!--first page 1 end here -->   
                 <!--first page 2 starts here -->
            <div class="page-2" >
                    <div class="clause" style="margin-top:10px; !important">
                        <div class="clause-title">1. GENERAL TERMS:</div>
                        <p><strong>1.1.</strong> The Applicant hereby represents and warrants that the Applicant has the sole legal capacity to enter into this Agreement and authorise the construction, installation and commissioning of the Rooftop Solar System ("RTS System") which is inclusive of Balance of System ("BoS") on the Applicant's premises ("Applicant Site"). The Vendor reserves its right to verify ownership of the Applicant Site and Applicant covenants to co-operate and provide all information and documentation required by the Vendor for the same.</p>
                        <p><strong>1.2.</strong> Vendor may propose changes to the scope, nature and or schedule of the services being performed under this Agreement. All proposed changes must be mutually agreed between the Parties. If Parties fail to agree on the variation proposed, either Party may terminate this Agreement by serving notice as per Clause 13.</p>
                        <p><strong>1.3.</strong> The Applicant understands and agrees that future changes in load, electricity usage patterns and/or electricity tariffs may affect the economics of the RTS System and these factors have not been and cannot be considered in any analysis or quotation provided by Vendor or its Authorized Persons (defined below).</p>
                    </div>
                    
                    <div class="clause">
                        <div class="clause-title">2. RTS System</div>
                        <p><strong>2.1.</strong> <strong>Total capacity of RTS System</strong> will be <strong>minimum ${data.system_capacity} KWp.</strong></p>
                        <p><strong>2.2.</strong> The Solar modules, inverters and Bos will confirm to minimum specifications and DCR requirement of MNRE.</p>
                        <p><strong>2.3.</strong> Solar modules of <span class="highlight">${data.panel_make}</span> WATT capacity each and <span class="highlight">${data.panel_efficiency}%</span> efficiency will be procured and installed by the Vendor</p>
                        <p><strong>2.4.</strong> Solar inverter of <span class="highlight">${data.inverter_make}</span> make, rated <span class="highlight">${data.inverter_capacity} KW</span> output capacity will be procured and installed by the Vendor.</p>
                        <p><strong>2.5.</strong> <strong>Module mounting structure has to withstand minimum wind load pressure as specified by MNRE.</strong></p>
                        <p><strong>2.6.</strong> Other BoS installations shall be as per best industry practice with all safety and protection gears installed by the vendor.</p>
                    </div>
                    
                    <div class="clause">
                        <div class="clause-title">PRICE AND PAYMENT TERMS</div>
                        <p>The cost of RTS System will be Rs <span class="highlight">${data.system_cost}/-</span> to be decided mutually). The Applicant shall pay the al cost to the Vendor as under:</p>
                        <p>${data.advance_percentage}% (₹${advanceAmount.toFixed(2)}) as an advance on confirmation of the order,</p>
                        <p>${data.dispatch_percentage}% (₹${dispatchAmount.toFixed(2)}) against Performa Invoice (PI) before dispatch of solar panels, inverters and other BoS items to be livered;</p>
                        <p>${data.completion_percentage}% (₹${completionAmount.toFixed(2)}) after installation and commissioning of the RTS System.</p>
                       
                    </div>
                  <!--first page 2 end here -->   
                 <!--first page 3 starts here -->
            <div class="page-3">
            
                    <div class="clause" style="margin-top:20px; !important">
                     <p>The order value and payment terms are fixed and will not be subject to any adjustment except as proved in writing by Vendor. The payment shall be made only through bankers' cheque / NEFT/RTGS/online payment portal as intimated by Vendor. No cash payments shall be accepted by Vendor or Authorized Person.</p>
                        <div class="clause-title">REPRESENTATIONS MADE BY THE APPLICANT:</div>
                        <p><strong>1.</strong> Any timeline or schedule shared by Vendor for the provision of services and delivery of the RTS system is only an estimate and Vendor will not be liable for any delay that is not attributable to Vendor.</p>
                        <p><strong>2.</strong> All information disclosed by the Applicant to Vendor in connection with the supply of the RTS system (or any part thereof), services and generation estimation (including, without limitation, the load and power bill) are true and accurate, and acknowledges that Vendor has relied on the information produced by the Applicant to customise the RTS System layout and BoS design for the purposes of this agreement:</p>
                        <p><strong>3.</strong> All descriptive specifications, illustrations, drawings, data, dimensions, quotation, fact sheets, price etc. and any advertising material circulated/published/provided by Vendor are approximate only,</p>
                        <p><strong>4.</strong> Any drawings, pre-feasibility report, specifications and plans composed by Vendor shall require the applicant's approval within 5 (five) days of its receipt by electronic mail to Vendor and if the Applicant ones not respond within this period, the drawings, specifications or plans shall be final and deemed to have been approved by the Applicant;</p>
                        <p><strong>5.</strong> The Applicant shall not use the RTS System or any part thereof, other than in accordance with the product manufacturer's specifications, and covenants that any risk arising from misuse or/and misappropriate use shall be to the account of the Applicant alone.</p>
                    </div>
                    
                    <div class="signature-area">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>${data.applicant_name}</strong></p>
                                <p><strong>(Applicant)</strong></p>
                                <div class="signature-line"></div>
                                <p>Date: ${formattedDate}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>${data.vendor_name}</strong></p>
                                <p><strong>(Vendor)</strong></p>
                                <div class="signature-line"></div>
                                <p>Date: ${formattedDate}</p>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p><strong>Witness 1</strong></p>
                                <div class="signature-line"></div>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Witness 2</strong></p>
                                <div class="signature-line"></div>
                            </div>
                        </div>
                    </div>
                    
                   
                </div>
                 <!--first page 3 end here -->   
                </div>
            `;
        }
        
        function showForm() {
            document.getElementById('formSection').classList.remove('hidden');
            document.getElementById('previewSection').classList.add('hidden');
            updateStepIndicator(1);
        }
        
        function downloadPDF() {
            const element = document.getElementById('agreementPreview');
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options
            const opt = {
                margin: 15,
                filename: `Solar_Installation_Agreement_${document.getElementById('consumer_number').value}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    logging: false
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait' 
                }
            };
            
            // Generate PDF
            html2pdf().set(opt).from(element).save();
        }
        
        function printAgreement() {
            // Update step indicator
            updateStepIndicator(3);
            window.print();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    if (typeof modelAgreementData !== 'object') return;

    Object.keys(modelAgreementData).forEach(key => {
        const field = document.getElementById(key);
        if (field && modelAgreementData[key] !== null && modelAgreementData[key] !== '') {
            field.value = modelAgreementData[key];
        }
    });
});

    </script>
</body>
</html>
