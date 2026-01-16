<?php
// admin/add_user.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin','office_staff']);
$auth->checkPermission('reports', 'create');

$title = "undertaking";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require('include/head.php'); ?>
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
</head>
<body>
         <!-- Sidebar -->
  <?php require('include/sidebar.php') ?>
 <div id="main-content">
    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>
    <!-- Professional Header -->
    <header class="professional-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="logo-container">
                        <i class="bi bi-lightning-charge logo-icon"></i>
                        <div class="header-content">
                            <h1>Undertaking/Self-Declaration for Domestic Content Requirement fulfillment</h1>
                            <p>Domestic Content Requirement Declaration</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="status-badge me-3"><i class="bi bi-check-circle-fill"></i> MNRE Approved</span>
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
                <div class="step-text">Preview Declaration</div>
            </div>
            <div class="step" id="step3">
                <div class="step-circle">3</div>
                <div class="step-text">Download/Print</div>
            </div>
        </div>

        <!-- Declaration Form Section -->
        <div id="declarationFormSection">
            <div class="professional-card">
                <div class="card-header">
                    <h5><i class="bi bi-file-earmark-text"></i> Domestic Content Requirement Declaration Form</h5>
                </div>
                <div class="card-body">
                    <form id="declarationForm">
                        <!-- Company & Project Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-building"></i> Company & Project Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="company_name" class="required">Company Name (M/S)</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="system_capacity" class="required">System Capacity (KW)</label>
                                    <input type="number" step="0.1" class="form-control" id="system_capacity" name="system_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="consumer_name" class="required">Consumer Name</label>
                                    <input type="text" class="form-control" id="consumer_name" name="consumer_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="project_address" class="required">Project Address</label>
                                    <textarea class="form-control" id="project_address" name="project_address" rows="2" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="application_number" class="required">Application Number</label>
                                    <input type="text" class="form-control" id="application_number" name="application_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="application_date" class="required">Application Date</label>
                                    <input type="date" class="form-control" id="application_date" name="application_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="discom_name" class="required">DISCOM Name</label>
                                    <input type="text" class="form-control" id="discom_name" name="discom_name" required>
                                </div>
                            </div>
                        </div>

                        <!-- PV Module Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-sun"></i> PV Module Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="pv_module_capacity" class="required">PV Module Capacity (W)</label>
                                    <input type="number" step="0.1" class="form-control" id="pv_module_capacity" name="pv_module_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="pv_module_count" class="required">Number of PV Modules</label>
                                    <input type="number" class="form-control" id="pv_module_count" name="pv_module_count" required>
                                </div>
                                <div class="form-group">
                                    <label for="pv_module_make" class="required">PV Module Make</label>
                                    <input type="text" class="form-control" id="pv_module_make" name="pv_module_make" required>
                                </div>
                                <div class="form-group">
                                    <label for="cell_manufacturer" class="required">Cell Manufacturer Name</label>
                                    <input type="text" class="form-control" id="cell_manufacturer" name="cell_manufacturer" required>
                                </div>
                                <div class="form-group">
                                    <label for="cell_gst_invoice" class="required">Cell GST Invoice Number</label>
                                    <input type="text" class="form-control" id="cell_gst_invoice" name="cell_gst_invoice" required>
                                </div>
                            </div>
                        </div>

                        <!-- Declaration Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-check"></i> Declaration Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="declarant_name" class="required">Declarant Name</label>
                                    <input type="text" class="form-control" id="declarant_name" name="declarant_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="declarant_designation" class="required">Declarant Designation</label>
                                    <input type="text" class="form-control" id="declarant_designation" name="declarant_designation" required>
                                </div>
                                <div class="form-group">
                                    <label for="declarant_phone" class="required">Phone Number</label>
                                    <input type="tel" class="form-control" id="declarant_phone" name="declarant_phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="declarant_email" class="required">Email Address</label>
                                    <input type="email" class="form-control" id="declarant_email" name="declarant_email" required>
                                </div>
                                <div class="form-group">
                                    <label for="declaration_date" class="required">Declaration Date</label>
                                    <input type="date" class="form-control" id="declaration_date" name="declaration_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-primary btn-professional" id="generateDeclarationBtn">
                                <i class="bi bi-eye"></i> Generate Declaration Preview
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-professional">
                                <i class="bi bi-arrow-clockwise"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Declaration Preview Section -->
        <div id="declarationPreviewSection" class="hidden">
            <div class="preview-controls">
                <h3 class="preview-title"><i class="bi bi-file-text"></i> DCR Declaration Preview</h3>
                <div class="action-buttons">
                    <button class="btn btn-success btn-professional" id="downloadDeclarationPdfBtn">
                        <i class="bi bi-download"></i> Download as PDF
                    </button>
                    <button class="btn btn-primary btn-professional" id="printDeclarationBtn">
                        <i class="bi bi-printer"></i> Print Declaration
                    </button>
                    <button class="btn btn-outline-secondary btn-professional" id="backToDeclarationFormBtn">
                        <i class="bi bi-arrow-left"></i> Back to Form
                    </button>
                </div>
            </div>

            <div id="declarationPreview">
                <!-- Declaration content will be generated here -->
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('application_date').value = today;
            document.getElementById('declaration_date').value = today;
            
            // Form events
            document.getElementById('generateDeclarationBtn').addEventListener('click', generateDeclaration);
            document.getElementById('backToDeclarationFormBtn').addEventListener('click', function() {
                showFormSection();
            });
            document.getElementById('downloadDeclarationPdfBtn').addEventListener('click', function() {
                downloadPDF();
            });
            document.getElementById('printDeclarationBtn').addEventListener('click', function() {
                printDocument();
            });
            
            // Update step indicator
            updateStepIndicator(1);
        });
        
        function showFormSection() {
            document.getElementById('declarationFormSection').classList.remove('hidden');
            document.getElementById('declarationPreviewSection').classList.add('hidden');
            updateStepIndicator(1);
        }
        
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
        
        function generateDeclaration() {
            // Get form values
            const form = document.getElementById('declarationForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate form
            if (!data.company_name || !data.system_capacity || !data.consumer_name || !data.project_address) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Format dates
            const applicationDate = new Date(data.application_date);
            const formattedApplicationDate = applicationDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            const declarationDate = new Date(data.declaration_date);
            const formattedDeclarationDate = declarationDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Generate declaration HTML
            const declarationHTML = createDeclarationHTML(data, formattedApplicationDate, formattedDeclarationDate);
            
            // Display declaration
            document.getElementById('declarationPreview').innerHTML = declarationHTML;
            
            // Show preview section and hide form
            document.getElementById('declarationFormSection').classList.add('hidden');
            document.getElementById('declarationPreviewSection').classList.remove('hidden');
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function createDeclarationHTML(data, formattedApplicationDate, formattedDeclarationDate) {
            return `
                <div class="pdf-optimized">
                    <div class="declaration-header">
                        <h2>Undertaking/Self-Declaration for Domestic Content Requirement fulfillment</h2>
                        <p>(On a plain Paper)</p>
                    </div>

                    <div class="declaration-content">
                        <div class="declaration-section">
                            <p>1. This is to certify that M/S <span class="highlight">${data.company_name}</span> has installed <span class="highlight">${data.system_capacity} KW</span> Grid Connected Rooftop Solar Plant for <span class="highlight">${data.consumer_name}</span> at <span class="highlight">${data.project_address}</span> under application number <span class="highlight">${data.application_number}</span> dated <span class="highlight">${formattedApplicationDate}</span> under <span class="highlight">${data.discom_name}</span>.</p>
                        </div>
                        
                        <div class="declaration-section">
                            <p>2. It is hereby undertaken that the PV modules installed for the above-mentioned project are domestically manufactured using domestic manufactured solar cells. The details of installed PV Modules are as follows:</p>
                            
                            <div class="pv-module-details">
                                <div class="pv-module-item">
                                    <div class="pv-module-label">PV Module Capacity:</div>
                                    <div class="pv-module-value">${data.pv_module_capacity} W</div>
                                </div>
                                <div class="pv-module-item">
                                    <div class="pv-module-label">Number of PV Modules:</div>
                                    <div class="pv-module-value">${data.pv_module_count}</div>
                                </div>
                                <div class="pv-module-item">
                                    <div class="pv-module-label">PV Module Make:</div>
                                    <div class="pv-module-value">${data.pv_module_make}</div>
                                </div>
                                <div class="pv-module-item">
                                    <div class="pv-module-label">Cell manufacturer's name:</div>
                                    <div class="pv-module-value">${data.cell_manufacturer}</div>
                                </div>
                                <div class="pv-module-item">
                                    <div class="pv-module-label">Cell GST invoice No:</div>
                                    <div class="pv-module-value">${data.cell_gst_invoice}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="declaration-section">
                            <p>3. The above undertaking is based on the certificate issued by PV Module manufacturer/supplier while supplying the above mentioned order.</p>
                        </div>
                        
                        <div class="declaration-section">
                            <p>4. I, <span class="highlight">${data.declarant_name}</span> on behalf of M/S <span class="highlight">${data.company_name}</span> further declare that the information given above is true and correct and nothing has been concealed therein. If anything is found incorrect at any stage, then REC/ MNRE may take any appropriate action against my company for wrong declaration. Supporting documents and proof of the above information will be provided as and when requested by MNRE.</p>
                        </div>
                        
                        <div class="signature-area">
                            <p>(Signature With official Seal)</p>
                            
                            <div style="margin-top: 30px;">
                                <p>For M/S <span class="highlight">${data.company_name}</span></p>
                                <p>Name: <span class="highlight">${data.declarant_name}</span></p>
                                <p>Designation: <span class="highlight">${data.declarant_designation}</span></p>
                                <p>Phone: <span class="highlight">${data.declarant_phone}</span></p>
                                <p>Email: <span class="highlight">${data.declarant_email}</span></p>
                                <p>Date: <span class="highlight">${formattedDeclarationDate}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function downloadPDF() {
            const element = document.getElementById('declarationPreview');
            const filename = `DCR_Declaration_${document.getElementById('application_number').value}.pdf`;
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options - optimized to remove extra spaces
            const opt = {
                margin: [10, 10, 10, 10],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#FFFFFF',
                    scrollX: 0,
                    scrollY: 0,
                    width: element.scrollWidth,
                    height: element.scrollHeight
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait',
                    compress: true
                }
            };
            
            // Generate PDF directly from the element
            html2pdf().set(opt).from(element).save();
        }
        
        function printDocument() {
            // Update step indicator
            updateStepIndicator(3);
            
            // Print the document directly
            window.print();
        }
    </script>
</body> 
</html>
