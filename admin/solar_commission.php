<?php
// admin/add_user.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin','office_staff']);
$auth->checkPermission('reports', 'create');

$title = "solar_commission";
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <?php require('include/head.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Commissioning Report Generator | MSEDCL</title>
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
        
        /* Commissioning Report Styling */
        #commissioningPreview {
            background-color: white;
            padding: 50px;
            border-radius: var(--border-radius);
            box-shadow: 0 6px 15px rgba(17, 79, 43, 0.04);
            margin-top: 20px;
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            font-size: 12pt;
            color: #333;
        }
        
        .commissioning-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .commissioning-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 24pt;
        }
        
        .commissioning-content {
            line-height: 1.7;
        }
        
        .commissioning-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .commissioning-table th, 
        .commissioning-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }
        
        .commissioning-table th {
            background-color: #f0f0f0;
            font-weight: bold;
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
        
        .commissioning-footer {
            margin-top: 40px;
            font-size: 10pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .commissioning-content p {
            margin-bottom: 15px;
            text-align: justify;
        }
        
        .commissioning-content .clause p {
            margin-bottom: 10px;
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
            #commissioningPreview, #commissioningPreview * {
                visibility: visible;
            }
            #commissioningPreview {
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
        
        .commissioning-content ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        
        .commissioning-content li {
            margin-bottom: 8px;
        }
    </style>
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
                        <i class="bi bi-sun-fill logo-icon"></i>
                        <div class="header-content">
                            <h1>Solar completion Report Generator</h1>
                            <p>Create professional solar completion reports for Rooftop Solar Programme</p>
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
                    <h5><i class="bi bi-file-earmark-text"></i> Solar completion Report Form</h5>
                </div>
                <div class="card-body">
                    <form id="commissioningForm">
                        <!-- Consumer Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-vcard"></i> Consumer Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="consumer_name" class="required">Consumer Name</label>
                                    <input type="text" class="form-control" id="consumer_name" name="consumer_name" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="consumer_number" class="required">Consumer Number</label>
                                    <input type="text" class="form-control" id="consumer_number" name="consumer_number" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="mobile_number" class="required">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile_number" name="mobile_number" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="required">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="installation_address" class="required">Installation Address</label>
                                    <textarea class="form-control" id="installation_address" name="installation_address" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- System Configuration -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-gear"></i> System Configuration
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="re_arrangement_type" class="required">RE Arrangement Type</label>
                                    <input type="text" class="form-control" id="re_arrangement_type" name="re_arrangement_type" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="re_source" class="required">RE Source</label>
                                    <input type="text" class="form-control" id="re_source" name="re_source" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="sanctioned_capacity" class="required">Sanctioned Capacity (KW)</label>
                                    <input type="number" step="0.1" class="form-control" id="sanctioned_capacity" name="sanctioned_capacity" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="capacity_type" class="required">Capacity Type</label>
                                    <input type="text" class="form-control" id="capacity_type" name="capacity_type" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="project_model" class="required">Project Model</label>
                                    <input type="text" class="form-control" id="project_model" name="project_model" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="installation_date" class="required">Installation Date</label>
                                    <input type="date" class="form-control" id="installation_date" name="installation_date" value="" required>
                                </div>
                            </div>
                        </div>

                        <!-- Solar PV Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-sun"></i> Solar PV Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="inverter_capacity" class="required">Inverter Capacity (KW)</label>
                                    <input type="number" step="0.1" class="form-control" id="inverter_capacity" name="inverter_capacity" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="inverter_make" class="required">Inverter Make</label>
                                    <input type="text" class="form-control" id="inverter_make" name="inverter_make" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="number_of_modules" class="required">Number of PV Modules</label>
                                    <input type="number" class="form-control" id="number_of_modules" name="number_of_modules" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="module_capacity" class="required">Module Capacity (Watt)</label>
                                    <input type="number" class="form-control" id="module_capacity" name="module_capacity" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="module_make" class="required">Module Make</label>
                                    <input type="text" class="form-control" id="module_make" name="module_make" value="" required>
                                </div>
                            </div>
                        </div>

                        <!-- Installation Company -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-building"></i> Installation Company Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="company_name" class="required">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="rep_name" class="required">Representative Name</label>
                                    <input type="text" class="form-control" id="rep_name" name="rep_name" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_phone" class="required">Phone</label>
                                    <input type="tel" class="form-control" id="company_phone" name="company_phone" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_email" class="required">Email</label>
                                    <input type="email" class="form-control" id="company_email" name="company_email" value="" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions pb-3">
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

        <!-- Commissioning Report Preview Section -->
        <div id="previewSection" class="hidden">
            <div class="preview-controls">
                <h3 class="preview-title"><i class="bi bi-file-text"></i> Commissioning Report Preview</h3>
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

            <div id="commissioningPreview">
                <!-- Commissioning report content will be generated here -->
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('installation_date').valueAsDate = new Date();
            
            // Event listeners
            document.getElementById('generateBtn').addEventListener('click', generateCommissioningReport);
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
        
        function generateCommissioningReport() {
            // Get form values
            const form = document.getElementById('commissioningForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate form
            if (!data.consumer_name || !data.consumer_number || !data.installation_address || 
                !data.installation_date || !data.company_name || !data.rep_name || 
                !data.inverter_capacity || !data.inverter_make || !data.number_of_modules || 
                !data.module_capacity || !data.module_make) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Format date
            const installationDate = new Date(data.installation_date);
            const formattedDate = installationDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Calculate total capacity
            const totalCapacity = (parseInt(data.number_of_modules) * parseInt(data.module_capacity));
            
            // Generate commissioning report HTML
            const reportHTML = createCommissioningReportHTML(data, formattedDate, totalCapacity);
            
            // Display report
            document.getElementById('commissioningPreview').innerHTML = reportHTML;
            
            // Show preview section and hide form
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function createCommissioningReportHTML(data, formattedDate, totalCapacity) {
            return `
                <div class="commissioning-header">
                    <h2>Renewable Energy Generating System</h2>
                    <h3>Annexure-l</h3>
                    <p><strong>(Commissioning Report for RE System)</strong></p>
                </div>

                <div class="commissioning-content">
                    <table class="commissioning-table">
                        <tr>
                            <td width="10%"><strong>SNo.</strong></td>
                            <td width="40%"><strong>Particulars</strong></td>
                            <td width="50%"><strong>As Commissioned</strong></td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Name of the Consumer</td>
                            <td>${data.consumer_name}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Consumer Number</td>
                            <td>${data.consumer_number}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Mobile Number</td>
                            <td>${data.mobile_number}</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>E-mail</td>
                            <td>${data.email}</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Address of installation</td>
                            <td>${data.installation_address}</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>RE Armingement Type</td>
                            <td>${data.re_arrangement_type}</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>RE Source</td>
                            <td>${data.re_source}</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Sanctioned Capacity(KW)</td>
                            <td>${data.sanctioned_capacity} KW</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Capacity Type</td>
                            <td>${data.capacity_type}</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Project Model</td>
                            <td>${data.project_model}</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>RE installed Capacity (Rooftop) (KW)</td>
                            <td>${totalCapacity} Watt</td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>RE installed Capacity(Rooftop + Ground) (KW)</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>RE installed Capacity(Ground)(KW)</td>
                            <td>--</td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>Installation date</td>
                            <td>${formattedDate}</td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>SolarPV Details :-</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>Inverter Capacity(KW)</td>
                            <td>${data.inverter_capacity} KW</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Inverter Make</td>
                            <td>${data.inverter_make}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>No of PV Modules</td>
                            <td>${data.number_of_modules} Nos.</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Module Capacity (KW)</td>
                            <td>${data.module_capacity} Watt</td>
                        </tr>
                    </table>

                    <div style="margin-top: 30px;">
                        <h3>Proforma-A</h3>
                        <h4>COMMISSIONING REPORT (PROVISIONAL) FOR GRID CONNECTED SOLAR PHOTOVOLTAIC POWER PLANT (with Net-metering facility)</h4>
                        
                        <p>Certified that a Grid Connected SPV Power Plant of <strong>${(totalCapacity/1000).toFixed(1)}</strong> KWp capacity has been installed at the site <strong>${data.installation_address}</strong>. District <strong>NAGPUR</strong> of <strong>MAHARASHTRA</strong> which has been installed by M/S <strong>${data.company_name}</strong> on ${formattedDate}. The system is as per BIS/MNRE specifications. The system has been checked for its performance and found in order for further commissioning.</p>

                        <div class="signature-area">
                            <p><strong>Signature of the beneficiary</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Signature of the agency with name, seal and date</strong></p>
                        </div>

                        <div style="margin-top: 50px;">
                            <p>We <strong>${data.rep_name} & ${data.company_name}</strong> (${data.consumer_name}) bearing Consumer Number <strong>${data.consumer_number}</strong></p>
                            
                            <p>Ensured structural stability of installed solar power plant and obtained requisite permissions from the concerned authority. If in future, by virtue of any means due to collapsing or damage to installed solar power plant, MSEDCL will not be held responsible for any loss to property or human life, if any.</p>
                            
                            <p>This is to Certified above Installed Solar PV System is working properly with electrical safety & Islanding switch in case of any presence of backup inverter an arrangement should be made in such way the backup inverter supply should never be synchronized with solar inverter to avoid any electrical accident due to back feeding. We will be held responsible for non-working of islanding mechanism and back feed to the de-energized grid.</p>
                            
                            <div class="signature-area">
                                <p><strong>Signature (Vendor)</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Signature(Consumer)</strong></p>
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
            const element = document.getElementById('commissioningPreview');
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options
            const opt = {
                margin: 15,
                filename: `Solar_Commissioning_Report_${document.getElementById('consumer_number').value}.pdf`,
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
        
        function printReport() {
            // Update step indicator
            updateStepIndicator(3);
            window.print();
        }
    </script>
</body>
</html>
