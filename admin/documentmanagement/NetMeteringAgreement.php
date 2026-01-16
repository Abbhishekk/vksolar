<?php
// admin/add_user.php
require_once __DIR__ . '/../connect/db.php';
require_once __DIR__ . '/../connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin','office_staff']);
$auth->checkPermission('reports', 'create');
$auth->requirePermission('reports', 'create');

$title = "NetMeteringAgreement";
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <?php require_once __DIR__ . '/../include/head3.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Net Metering Agreement Generator | MSEDCL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #3BAF6E;    /* requested main color */
            --secondary-color: #2F8E58;  /* darker green for gradients */
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
            margin-top: 20px;
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
            /*margin-bottom: 40px;*/
            /*padding-bottom: 20px;*/
            /*border-bottom: 2px solid var(--primary-color);*/
        }
        
        .agreement-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 24pt;
        }
        
        .agreement-header p {
            color: #666;
            margin-bottom: 5px;
        }
        
        .agreement-content {
            line-height: 1.7;
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
            margin-top: 30px;
            padding-top: 30px;
            
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 250px;
            margin-bottom: 8px;
            height: 25px;
        }
        
        .highlight {
            background-color: #e8f9ef; /* soft green highlight to match theme */
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
            color: var(--dark-color);
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
            margin-bottom: 15px;
            text-align: justify;
        }
        
        .agreement-content .clause p {
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

/* ================= STAMP PAPER PRINT CONTROL ================= */
@media print {

    body {
        margin: 0;
        padding: 0;
        background: white;
    }

    #agreementPreview {
        margin: 0;
        padding: 0;
        box-shadow: none;
    }

    /* ===== STAMP PAPER PAGES (1–4) ===== */
    .page-1,
    .page-2,
    .page-3,
    .page-4 {
        page-break-before: always;
        page-break-after: auto;

        min-height: 297mm;
        width: 210mm;

        box-sizing: border-box;

        padding-top: 5in;
        padding-bottom: 1in;
        padding-left: 1in;
        padding-right: 1in;

        overflow: visible;
    }

    /* 🔑 CRITICAL FIX: DO NOT BREAK BEFORE FIRST PAGE */
    .page-1 {
        page-break-before: auto !important;
    }

    /* ===== NORMAL PAGES ===== */
    .page-5,.page-6 {
        page-break-before: always;
        padding: 1in;
    }

    /* Hide UI */
    .professional-header,
    .preview-controls,
    .step-indicator {
        display: none !important;
    }
    @page {
        size: A4;
        margin: 0;
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
                        <i class="bi bi-lightning-charge logo-icon"></i>
                        <div class="header-content">
                            <h1>Net Metering Agreement Generator</h1>
                            <p>Create professional net metering agreements for MSEDCL</p>
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
                    <h5><i class="bi bi-file-earmark-text"></i> Net Metering Agreement Form</h5>
                </div>
                <div class="card-body">
                    <form id="agreementForm">
                        <!-- Consumer Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-vcard"></i> Consumer Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="consumer_name" class="required">Consumer Name</label>
                                    <input type="text" class="form-control" id="consumer_name" name="consumer_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="consumer_number" class="required">Consumer Number</label>
                                    <input type="text" class="form-control" id="consumer_number" name="consumer_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="required">Premises Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="location" class="required">Location/City</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                            </div>
                        </div>

                        <!-- System Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-sun"></i> System Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="system_capacity" class="required">Solar PV System Capacity (kW)</label>
                                    <input type="number" step="0.1" class="form-control" id="system_capacity" name="system_capacity" required>
                                </div>
                                <div class="form-group">
                                    <label for="vendor_name" class="required">Vendor/Installer Name</label>
                                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="agreement_date" class="required">Agreement Date</label>
                                    <input type="date" class="form-control" id="agreement_date" name="agreement_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="msedcl_representative" class="required">MSEDCL Representative Name</label>
                                    <input type="text" class="form-control" id="msedcl_representative" name="msedcl_representative" required>
                                </div>
                                <div class="form-group">
                                    <label for="msedcl_designation" class="required">MSEDCL Representative Designation</label>
                                    <input type="text" class="form-control" id="msedcl_designation" name="msedcl_designation" required>
                                </div>
                            </div>
                        </div>

                        <!-- Witness Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-check"></i> Witness Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="witness1_vendor" class="required">Witness 1 (Vendor)</label>
                                    <input type="text" class="form-control" id="witness1_vendor" name="witness1_vendor" required>
                                </div>
                                <div class="form-group">
                                    <label for="witness1_msedcl" class="required">Witness 1 (MSEDCL)</label>
                                    <input type="text" class="form-control" id="witness1_msedcl" name="witness1_msedcl" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
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
                <h3 class="preview-title"><i class="bi bi-file-text"></i> Net Metering Agreement Preview</h3>
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
            
            
             fetch("save_net_metering", {
                    method: "POST",
                    body: new FormData(document.getElementById('agreementForm'))
                });

            // Get form values
            const form = document.getElementById('agreementForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate form
            if (!data.consumer_name || !data.consumer_number || !data.address || !data.system_capacity) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Format date
            const agreementDate = new Date(data.agreement_date);
            const formattedDate = agreementDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Generate agreement HTML
            const agreementHTML = createAgreementHTML(data, formattedDate);
            
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
        
        function createAgreementHTML(data, formattedDate) {
            return `
                <div class="pdf-optimized">
                <div id="agreementPreview" >
                <!--first page 1 starts here -->
                <div class="page-1">
                    <div class="agreement-header" style="">
                        <h6>Annexure - 3</h6>
                    </div>
                    <div class="agreement-header">
                        <h5> <b> Net Metering Connection Agreement </b> </h5>
                    </div>

                    <div class="agreement-content">
                        <p>This Agreement is made and entered into at <span class="highlight">${data.location}</span> on this <span class="highlight">${formattedDate}</span> between the Eligible Consumer <span class="highlight">${data.consumer_name}</span> having premises at <span class="highlight">${data.address}</span> and Consumer No. <span class="highlight">${data.consumer_number}</span> as the first Party,</p>
                        
                        <p>AND</p>
                        
                        <p>MSEDCL __________ (Hereinafter referred to as 'the Licensee') and having its Registered Office at (address) ____________________________ as second party of this Agreement</p>
                        
                        <p>Whereas, the Eligible Consumer has applied to the Licensee for approval of a Net Metering Arrangement under the provisions of the Maharashtra Electricity Regulatory Commission (Net Metering for Roof-top Solar Photo Voltaic Systems) Regulations, 2015 ('the Net Metering Regulations') and sought its connectivity to the Licensee's Distribution Network;</p>
                        
                        <p>And whereas, the Licensee has agreed to provide Network connectivity to the Eligible Consumer for injection of electricity generated from its Roof-top Solar PV System of <span class="highlight">${data.system_capacity} kilowatt</span>;</p>
                    </div>
                </div>
                <div class="page-2" style="">      
                        
                        <div class="agreement-content">
                            <p>Both Parties hereby agree as follows:-</p>
                            <div class="">1. Eligibility</div>
                            <p>The Roof-top Solar PV System meets the applicable norms for being integrated into the Distribution Network, and that the Eligible Consumer shall maintain the System accordingly for the duration of this Agreement.</p>

                            <div class="">2. Technical and Inter-connection Requirements</div>
                            <p>2.1 &nbsp The metering arrangement and the inter-connection of the Roof-top Solar PV System with the Network of the Licensee shall be as per the provisions of the Net Metering Regulations and the technical standards and norms specified by the Central Electricity Authority for connectivity of distributed generation resources and for the installation and operation of meters.</p>
                            <p>2.2 &nbsp The Eligible Consumer agrees, that he shall install, prior to connection of the Renewable Energy Generating System to the Network of the MSEDCL, an isolation device (both automatic and in built within inverter and external manual relays); and the MSEDCL shall have access to it if required for the repair and maintenance of the Distribution Network.</p>
                            
                        </div>
                </div>      
                <div class="page-3" style="">     
                        <div class="agreement-content">
                            <p>2.3 &nbsp The MSEDCL shall specify the interface/inter-connection point and metering point.</p>
                            <p>2.4 &nbsp The Eligible Consumer shall furnish all relevant data, such as voltage, frequency, circuit breaker, isolator position in his System, as and when required by the Licensee.</p>
                            <div class="">3. Safety</div>
                            <p>3.1 &nbsp The equipment connected to the Licensee's Distribution System shall be compliant with relevant International (IEEE/IEC) or Indian Standards (BIS), as the case may be, and the installation of electrical equipment shall comply with the requirements specified by the Central Electricity Authority regarding safety and electricity supply.</p>
                            <p>3.2 &nbsp The design, installation, maintenance and operation of the Roof-top Solar PV System shall be undertaken in a manner conducive to the safety of the Roof-top Solar PV System as well as the Licensee's Network.</p>
                            <p>3.3 &nbsp If, at any time, the Licensee determines that the Eligible Consumer's Roof-top Solar PV System is causing or may cause damage to and/or results in the Licensee's other consumers or its assets, the Eligible Consumer shall disconnect the Roof-top Solar PV System from the distribution Network upon direction from the Licensee, and shall undertake corrective measures at his own expense prior to re-connection.</p>
                        </div>
                </div>      
                <div class="page-4" style="">
                        
                        <p>3.4 &nbsp The Licensee shall not be responsible for any accident resulting in injury to human beings or animals or damage to property that may occur due to back- feeding from the Roof-top Solar PV System when the grid supply is off. The Licensee may disconnect the installation at any time in the event of such exigencies to prevent such accident.</p>
                        <div class="agreement-content">
                            <div class="">4. Other Clearances and Approvals</div>
                            <p>The Eligible Consumer shall obtain any statutory approvals and clearances that may be required, such as from the Electrical Inspector or the municipal or other authorities, before connecting the Roof-top Solar PV System to the distribution Network.</p>
                        </div>

                        <div class="agreement-content">
                            <div class="">5. Period of Agreement, and Termination</div>
                            <p>This Agreement shall be for a period for 20 years, but may be terminated prematurely</p>
                            <p>(a) By mutual consent; or</p>
                            <p>(b) By the Eligible Consumer, by giving 30 days' notice to the Licensee;</p>
                            <p>(c) By the Licensee, by giving 30 days' notice, if the Eligible Consumer breaches any terms of this Agreement or the provisions of the Net Metering Regulations and does not remedy such breach within 30 days, or such other reasonable period as may be provided, of receiving notice of such breach, or for any other valid reason communicated by the Licensee in writing.</p>
                        </div>
                        
                </div>      
                <div class="page-5" >
                        <div class="agreement-content">
                            <div class="">6. Access and Disconnection</div>
                            <p>The Eligible Consumer shall provide access to the Licensee to the metering equipment and disconnecting devices of Roof-top Solar PV System, both automatic and manual, by the Eligible Consumer.</p>
                            <p>If, in an emergent or outage situation, the Licensee cannot access the disconnecting devices of the Roof-top Solar PV System, both automatic and manual, it may disconnect power supply to the premises.</p>
                            <p>Upon termination of this Agreement under Clause 5, the Eligible Consumer shall disconnect the Roof-top Solar PV System forthwith from the Network of the Licensee.</p>
                        </div>
                        <div class="agreement-content">
                            <div class="">7. Liabilities</div>
                            <p>The Parties shall indemnify each other for damages or adverse effects of either Party's negligence or misconduct during the installation of the Roof-top Solar PV System, connectivity with the distribution Network and operation of the System.</p>
                            <p>The Parties shall not be liable to each other for any loss of profits or revenues, business interruption losses, loss of contract or goodwill, or for indirect, consequential, incidental or special damages including, but not limited to, punitive or exemplary damages, whether any of these liabilities, losses or damages arise in contract, or otherwise.</p>
                        </div>

                        <div class="agreement-content">
                            <div class="">8. Commercial Settlement</div>
                            <p>The commercial settlements under this Agreement shall be in accordance with the Net Metering Regulations.</p>
                            <p>The Licensee shall not be liable to compensate the Eligible Consumer if his Rooftop Solar PV System is unable to inject surplus power generated into the Licensee's Network on account of failure of power supply in the grid/Network.</p>
                            <p>The existing metering System, if not in accordance with the Net Metering Regulations, shall be replaced by a bi-directional meter (whole current/CT operated) or a pair of meters (as per the definition of 'Net Meter' in the Regulations), and a separate generation meter may be provided to measure Solar power generation. The bi-directional meter (whole current/CT operated) or pair of meters shall be installed at the interconnection point to the Licensee's Network for recording export and import of energy.</p>
                        </div>
                    </div>
                    <div class="page-6">
                        <div class="agreement-content">
                
                            <p>The uni-directional and bi-directional or pair of meters shall be fixed in separate meter boxes in the same proximity.</p>
                            <p>The Licensee shall issue monthly electricity bill for the net metered energy on the scheduled date of meter reading. If the exported energy exceeds the imported energy, the Licensee shall show the net energy exported as credited Units of electricity as specified in the Net Metering Regulations, 2015. If the exported energy is less than the imported energy, the Eligible Consumer shall pay the Distribution Licensee for the net energy imported at the prevailing tariff approved by the Commission for the consumer category to which he belongs.</p>
                 
                            <div class="">9. Connection Costs</div>
                            <p>The Eligible Consumer shall bear all costs related to the setting up of the Roof-top Solar PV System, excluding the Net Metering Arrangement costs.</p>
                        </div>

                        <div class="agreement-content">
                            <div class="">10. Dispute Resolution</div>
                            <p>Any dispute arising under this Agreement shall be resolved promptly, in good faith and in an equitable manner by both the Parties.</p>
                            <p>The Eligible Consumer shall have recourse to the concerned Consumer Grievance Redressal Forum constituted under the relevant Regulations in respect of any grievance regarding billing which has not been redressed by the Licensee.</p>
                        </div>

                        <div class="signature-area">
                            <p>In the witness where of <span class="highlight">${data.vendor_name}</span> for and on behalf of Eligible Consumer and <span class="highlight">${data.msedcl_representative}</span> for and on behalf of MSEDCL agree to this agreement.</p>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                                <div>
                                    <p><span class="highlight">${data.consumer_name}</span></p>
                                    <div class="signature-line mt-0"></div>
                                    <p>For Eligible Consumer</p>
                                </div>
                                <div>
                                    <p><span class="highlight">${data.msedcl_representative}</span></p>
                                    <div class="signature-line mt-0"></div>
                                    <p>${data.msedcl_designation}, MSEDCL</p>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                                <div>
                                    <p>Witness 1 (VENDOR):<br> <span class="highlight">${data.witness1_vendor}</span></p>
                                    <div class="signature-line mt-0"></div>
                                </div>
                                <div>
                                    <p>Witness 1 (MSEDCL): <br><span class="highlight">${data.witness1_msedcl}</span></p>
                                    <div class="signature-line mt-0"></div>
                                </div>
                            </div>
                        </div>
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
            const element = document.getElementById('agreementPreview');
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options - optimized to remove extra spaces
            const opt = {
                margin: [5, 5, 5, 5], // Minimal margins
                filename: `Net_Metering_Agreement_${document.getElementById('consumer_number').value}.pdf`,
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
        
        function printAgreement() {
            // Update step indicator
            updateStepIndicator(3);
            
            // Print the agreement directly
            window.print();
        }
    </script>
     <script>
document.addEventListener('DOMContentLoaded', function () {
    // console.log(netMeteringData);
    if (typeof netMeteringData === 'undefined') return;

    Object.keys(netMeteringData).forEach(key => {
        const el = document.getElementById(key);
        // console.log(el);
        if (el && netMeteringData[key] !== null && netMeteringData[key] !== '') {
            el.value = netMeteringData[key];
        }
    });
});
</script>
</body>
</html>
