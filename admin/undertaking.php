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
        
        /* Certificate Preview Styling (adapted from agreement) */
        #certificatePreview {
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
        
        .agreement-header, .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .certificate-header h2, .agreement-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 24pt;
        }
        
        .agreement-content, .certificate-content {
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
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 300px;
            margin-bottom: 8px;
            height: 25px;
        }
        
        .highlight {
            background-color: #fff9c4;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .agreement-footer, .certificate-footer {
            margin-top: 40px;
            font-size: 10pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .agreement-content p, .certificate-content p {
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
            #certificatePreview, #certificatePreview * {
                visibility: visible;
            }
            #certificatePreview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                padding: 20px;
                margin: 0;
                max-width: 100%;
                border: none;
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
            
            .stamp-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stamp-area {
                margin-left: 0;
                margin-top: 20px;
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
                        <i class="bi bi-award-fill logo-icon"></i>
                        <div class="header-content">
                            <h1>Certificate/Undertaking Generator</h1>
                            <p>Create professional installation certificates for solar systems</p>
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
                <div class="step-text">Preview Certificate</div>
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
                    <h5><i class="bi bi-file-earmark-medical"></i> Certificate/Undertaking Details</h5>
                </div>
                <div class="card-body">
                    <form id="certificateForm">
                        <!-- Company Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-building"></i> Company Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="company_name" class="required">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="agent_name" class="required">Name of Agent/Representative</label>
                                    <input type="text" class="form-control" id="agent_name" name="agent_name" required>
                                </div>
                            </div>
                        </div>

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
                                    <label for="consumer_address" class="required">Consumer Address</label>
                                    <textarea class="form-control" id="consumer_address" name="consumer_address" rows="2" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="system_load" class="required">System Load (kW)</label>
                                    <input type="number" step="0.1" class="form-control" id="system_load" name="system_load" required>
                                </div>
                            </div>
                        </div>

                        <!-- Installation Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-gear"></i> Installation Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="installation_date" class="required">Installation Date</label>
                                    <input type="date" class="form-control" id="installation_date" name="installation_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="system_type" class="required">System Type</label>
                                    <select class="form-control" id="system_type" name="system_type" required>
                                        <option value="Solar PV System">Solar PV System</option>
                                        <option value="Rooftop Solar System">Rooftop Solar System</option>
                                        <option value="Grid-Connected Solar System">Grid-Connected Solar System</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="has_backup_inverter">Has Backup Inverter?</label>
                                    <select class="form-control" id="has_backup_inverter" name="has_backup_inverter">
                                        <option value="yes">Yes</option>
                                        <option value="no" selected>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions pb-3">
                            <button type="button" class="btn btn-primary btn-professional" id="generateBtn">
                                <i class="bi bi-eye"></i> Generate Certificate
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-professional">
                                <i class="bi bi-arrow-clockwise"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Certificate Preview Section -->
        <div id="previewSection" class="hidden">
            <div class="preview-controls">
                <h3 class="preview-title"><i class="bi bi-file-text"></i> Certificate Preview</h3>
                <div class="action-buttons">
                    <button class="btn btn-success btn-professional" id="downloadPdfBtn">
                        <i class="bi bi-download"></i> Download as PDF
                    </button>
                    <button class="btn btn-primary btn-professional" id="printBtn">
                        <i class="bi bi-printer"></i> Print Certificate
                    </button>
                    <button class="btn btn-outline-secondary btn-professional" id="backToFormBtn">
                        <i class="bi bi-arrow-left"></i> Back to Form
                    </button>
                </div>
            </div>

            <div id="certificatePreview">
                <!-- Certificate content will be generated here -->
            </div>
        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('installation_date').valueAsDate = new Date();
            
            // Event listeners
            document.getElementById('generateBtn').addEventListener('click', generateCertificate);
            document.getElementById('showFormBtn').addEventListener('click', showForm);
            document.getElementById('backToFormBtn').addEventListener('click', showForm);
            document.getElementById('downloadPdfBtn').addEventListener('click', downloadPDF);
            document.getElementById('printBtn').addEventListener('click', printCertificate);
            
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
        
        function generateCertificate() {
            // Get form values
            const form = document.getElementById('certificateForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate form
            if (!data.company_name || !data.agent_name || !data.consumer_name || 
                !data.consumer_number || !data.consumer_address || !data.system_load || 
                !data.installation_date || !data.system_type) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Format date
            const installationDate = new Date(data.installation_date);
            const formattedDate = installationDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            
            // Generate certificate HTML
            const certificateHTML = createCertificateHTML(data, formattedDate);
            
            // Display certificate
            document.getElementById('certificatePreview').innerHTML = certificateHTML;
            
            // Show preview section and hide form
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function createCertificateHTML(data, formattedDate) {
            const backupInverterText = data.has_backup_inverter === 'yes' 
                ? "in case of any presence of backup inverter an arrangement should be made in such way the backup inverter supply should never be synchronized with solar inverter to avoid any electrical accident due to back feeding"
                : "";
                
            return `
                <div class="certificate-header">
                    <h2>CERTIFICATE / UNDERTAKING</h2>
                </div>

                <div class="certificate-content">
                    <div class="certificate-title">
                        INSTALLATION CERTIFICATION
                    </div>
                    
                    <p>This is to certify that we <span class="highlight">${data.company_name}</span> installed <span class="highlight">${data.system_type}</span> for consumer Name <span class="highlight">${data.consumer_name}</span> Consumer Number <span class="highlight">${data.consumer_number}</span> Address <span class="highlight">${data.consumer_address}</span> of load <span class="highlight">${data.system_load} kW</span> on <span class="highlight">${formattedDate}</span>.</p>
                    
                    <p>The system is working properly with electrical safety & islanding switch ${backupInverterText}. We are held responsible for proper working of islanding system and electrical safety of the system in case of back feed to the de-energized grid.</p>
                    
                    <p>We hereby undertake that the installation complies with all safety standards and regulations as prescribed by the relevant authorities.</p>
                    
                    <div class="stamp-container">
                        <div class="signature-block">
                            <p><strong>Name of Agent/Representative:</strong></p>
                            <div class="signature-line"></div>
                            <p><strong>${data.agent_name}</strong></p>
                            <p><strong>${data.company_name}</strong></p>
                        </div>
                        <div class="stamp-block">
                            <div class="stamp-area">
                                Company Stamp
                            </div>
                        </div>
                    </div>
                    
                    <div class="certificate-footer">
                        <p>Generated on ${new Date().toLocaleDateString()} by Certificate/Undertaking Generator</p>
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
            const element = document.getElementById('certificatePreview');
            
            // Update step indicator
            updateStepIndicator(3);
            
            // PDF options
            const opt = {
                margin: 15,
                filename: `Installation_Certificate_${document.getElementById('consumer_number').value}.pdf`,
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
         
        function printCertificate() { 
            // Update step indicator 
            updateStepIndicator(3); 
            window.print(); 
        } 
    </script> 
</body> 
</html>
