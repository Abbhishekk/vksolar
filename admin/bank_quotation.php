<!DOCTYPE html>
<?php
// admin/pages/add_employee.php
require_once 'connect/auth_middleware.php';


?>
<!DOCTYPE html>
<?php
// admin/pages/add_employee.php
require_once 'connect/auth_middleware.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('include/head.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VK Solar Energy - Solar Quotation Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-green: #2e7d32;
            --secondary-green: #4caf50;
            --light-green: #e8f5e9;
            --dark-green: #1b5e20;
            --accent-green: #66bb6a;
            --text-dark: #1e1e1e;
            --white: #ffffff;
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
            padding: 0px;
        }

        .professional-header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 30px;
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
            justify-content: center;
        }

        .logo-icon {
            font-size: 2.5rem;
            color: #FFD700;
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
            margin: 0 auto;
        }

        .professional-card {
            background: white;
            border-radius: 8px;
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
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border-radius: 0 !important;
            font-weight: 600;
            padding: 18px 25px;
            border-bottom: 3px solid #FFD700;
        }

        .card-header h5 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section {
            background: var(--light-green);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-green);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-left-color: #FFD700;
        }

        .section-title {
            color: var(--primary-green);
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
            color: #FFD700;
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
            color: var(--text-dark);
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
            border-color: var(--primary-green);
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
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
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

        .btn-warning {
            background: linear-gradient(135deg, #ff9800, #ff5722);
            border: none;
            color: white;
        }

        .required::after {
            content: " *";
            color: #e74c3c;
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 40px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 25px;
            right: -20px;
            width: 40px;
            height: 2px;
            background: #d1d5e0;
        }

        .step.active:not(:last-child)::after {
            background: var(--primary-green);
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
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .step.completed .step-circle {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
        }

        .step-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6c757d;
            text-align: center;
        }

        .step.active .step-text {
            color: var(--primary-green);
        }

        .step.completed .step-text {
            color: var(--primary-green);
        }

        .auto-calculate {
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        /* Products Section Styles */
        .product-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }

        .product-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-green);
        }

        .product-item-title {
            font-weight: 600;
            color: var(--primary-green);
            font-size: 1.1em;
        }

        .remove-product {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-product:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .products-total {
            background: var(--light-green);
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-green);
        }

        .products-total h5 {
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        .add-product-btn {
            width: 100%;
            margin-top: 10px;
        }

        /* PREVIEW SECTION STYLES */
        #previewSection {
            display: none;
        }

        .preview-controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .preview-title {
            color: var(--primary-green);
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

        .quotation-output {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Preview container styling to match print preview */
        .preview-container {
            width: 210mm;

            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 5mm;
            font-family: Arial, sans-serif;
            line-height: 1.2;
            color: #000;
            font-size: 11pt;
        }

        .preview-page {
            position: relative;
            min-height: auto;
            margin-bottom: 10mm;
            background: white;

        }

        /* Company Header Styles for Preview */
        .company-header-preview {
            position: relative;
            width: 100%;
            overflow: hidden;
            border-radius: 8px;
        }

        .header-image-preview {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }


        .header-overlay-preview {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7));
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            z-index: 2;
        }

        .company-name-preview {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .company-tagline-preview {
            font-size: 16px;
            margin-bottom: 15px;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .company-badges-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .badge-preview {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .company-details-preview {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }

        .office-info-preview {
            flex: 1;
            min-width: 200px;
        }

        .office-info-preview h4 {
            font-size: 13px;
            margin-bottom: 5px;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 3px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .office-info-preview p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .contact-info-preview {
            flex: 1;
            min-width: 200px;
        }

        .contact-details-preview p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Quotation Details for Preview */
        .quotation-details-preview {
            font-size: 11pt;
            margin-bottom: 15px;
            line-height: 1.3;
            background-color: #e8f5e9 !important;
            padding: 10px 15px;
            border-radius: 5px;
            border-left: 4px solid #2e7d32;
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }

        .quotation-title-preview {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px 0;
            border-bottom: 3px solid #2e7d32;
            border-top: 3px solid #2e7d32;
            color: #1b5e20;
            position: relative;
            z-index: 2;
        }

        .customer-section-preview {
            margin-bottom: 20px;
            font-size: 11pt;
            padding: 12px;
            background-color: #e8f5e9 !important;
            border-radius: 5px;
            border-left: 4px solid #4caf50;
            position: relative;
            z-index: 2;
        }

        .customer-section-preview h4 {
            margin: 0 0 8px 0;
            font-size: 12pt;
            color: #1b5e20;
        }

        .letter-content-preview {
            font-size: 11pt;
            margin-bottom: 20px;
            line-height: 1.5;
            position: relative;
            z-index: 2;
        }

        /* Bill of Materials Table for Preview */
        .bill-table-preview {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
            position: relative;
            z-index: 2;
            background: white;
        }

        .bill-table-preview th {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            background-color: #2e7d32 !important;
            color: white !important;
            font-weight: bold;
        }

        .bill-table-preview td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            background: white;
        }

        .bill-table-preview tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .bill-table-preview tr:last-child {
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }

        /* Terms & Conditions for Preview */
        .terms-section-preview {
            display: flex;
            margin-top: 20px;
            gap: 25px;
            position: relative;
            z-index: 2;
        }

        .terms-column-preview {
            flex: 1;
            font-size: 9pt;
            line-height: 1.3;
            background: white;
            position: relative;
            z-index: 2;
        }

        .terms-column-preview h4 {
            color: #1b5e20;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #4caf50;
            font-size: 10pt;
        }

        .terms-column-preview ol {
            margin: 0;
            padding-left: 15px;
            background-color: #e8f5e9 !important;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .terms-column-preview li {
            margin-bottom: 3px;
        }

        .bank-details-preview {
            font-size: 9pt;
            line-height: 1.3;
            background-color: #e8f5e9 !important;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .bank-details-preview div {
            margin-bottom: 3px;
        }

        /* Print Styles */
        @media print {
            body {
                font-family: Arial, sans-serif;
                line-height: 1.2;
                color: #000;
                background: #fff;
                font-size: 11pt;
                width: 210mm;
                margin: 0 auto;
                padding: 15mm 15mm 0 15mm;
                position: relative;
            }

            @page {
                size: A4;
                margin: 15mm 15mm 0 15mm;
            }

            /* Hide all elements except printable quotation */
            body>*:not(#printableQuotation) {
                display: none !important;
            }

            #printableQuotation {
                display: block !important;
                position: relative;
                width: 100%;
                background: white;
                margin: 0;
                padding: 0;
            }

            /* Page containers */
            .page-container {
                position: relative;
                min-height: 277mm;
                margin-bottom: 10mm;
                background: white;
                page-break-after: always;
            }

            /* Content styling for print */
            .company-header {
                position: relative;
                color: white;
                margin-bottom: 20px;
                border-radius: 8px;
                overflow: hidden;
                min-height: 180px;
                z-index: 2;
                width: 100%;
            }

            .header-image {
                width: 100%;
                height: 180px;
                object-fit: cover;
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
            }

            .header-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7));
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                padding: 20px;
                z-index: 2;
            }

            .company-name {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 5px;
                color: white;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            }

            .company-tagline {
                font-size: 14px;
                margin-bottom: 15px;
                opacity: 0.9;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            }

            .company-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 15px;
            }

            .badge {
                background: rgba(255, 255, 255, 0.2);
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: bold;
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .company-details {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 20px;
                margin-top: 15px;
            }

            .office-info {
                flex: 1;
                min-width: 200px;
            }

            .office-info h4 {
                font-size: 12px;
                margin-bottom: 5px;
                color: #ffffff;
                border-bottom: 1px solid rgba(255, 255, 255, 0.3);
                padding-bottom: 3px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            }

            .office-info p {
                font-size: 11px;
                line-height: 1.4;
                margin-bottom: 5px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            }

            .contact-info {
                flex: 1;
                min-width: 200px;
            }

            .contact-details p {
                font-size: 11px;
                line-height: 1.4;
                margin-bottom: 5px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            }

            /* Quotation Details for print */
            .quotation-details {
                font-size: 10pt;
                margin-bottom: 15px;
                line-height: 1.3;
                background-color: #e8f5e9 !important;
                padding: 8px 12px;
                border-radius: 3px;
                border-left: 4px solid #2e7d32;
                display: flex;
                justify-content: space-between;
                position: relative;
                z-index: 2;
            }

            .quotation-title {
                text-align: center;
                font-size: 16pt;
                font-weight: bold;
                margin: 15px 0;
                padding: 8px 0;
                border-bottom: 2px solid #2e7d32;
                border-top: 2px solid #2e7d32;
                color: #1b5e20;
                position: relative;
                z-index: 2;
            }

            .customer-section {
                margin-bottom: 15px;
                font-size: 10pt;
                padding: 10px;
                background-color: #e8f5e9 !important;
                border-radius: 3px;
                border-left: 4px solid #4caf50;
                position: relative;
                z-index: 2;
            }

            .customer-section h4 {
                margin: 0 0 5px 0;
                font-size: 11pt;
                color: #1b5e20;
            }

            .letter-content {
                font-size: 10pt;
                margin-bottom: 15px;
                line-height: 1.4;
                position: relative;
                z-index: 2;
            }

            /* Bill of Materials Table for print */
            .bill-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 9pt;
                position: relative;
                z-index: 2;
                background: white;
            }

            .bill-table th {
                border: 1px solid #ddd;
                padding: 6px 5px;
                text-align: left;
                background-color: #2e7d32 !important;
                color: white !important;
                font-weight: bold;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .bill-table td {
                border: 1px solid #ddd;
                padding: 6px 5px;
                text-align: left;
                background: white;
            }

            .bill-table tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            .bill-table tr:last-child {
                background-color: #e8f5e9 !important;
                font-weight: bold;
            }

            /* Terms & Conditions for print */
            .terms-section {
                display: flex;
                margin-top: 15px;
                gap: 20px;
                position: relative;
                z-index: 2;
            }

            .terms-column {
                flex: 1;
                font-size: 8pt;
                line-height: 1.2;
                background: white;
                position: relative;
                z-index: 2;
            }

            .terms-column h4 {
                color: #1b5e20;
                margin-bottom: 5px;
                padding-bottom: 3px;
                border-bottom: 1px solid #4caf50;
                font-size: 9pt;
            }

            .terms-column ol {
                margin: 0;
                padding-left: 15px;
                background-color: #e8f5e9 !important;
                padding: 8px 10px;
                border-radius: 3px;
                border: 1px solid #ddd;
            }

            .terms-column li {
                margin-bottom: 2px;
            }

            .bank-details {
                font-size: 8pt;
                line-height: 1.2;
                background-color: #e8f5e9 !important;
                padding: 8px 10px;
                border-radius: 3px;
                border: 1px solid #ddd;
            }

            .bank-details div {
                margin-bottom: 2px;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .step-indicator {
                gap: 20px;
            }

            .step:not(:last-child)::after {
                width: 20px;
                right: -10px;
            }

            .preview-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .preview-container {
                width: 100%;
                min-height: auto;
                padding: 15px;
            }

            .company-details-preview {
                flex-direction: column;
            }

            .terms-section-preview {
                flex-direction: column;
            }
            @media print {
    *:last-child {
        page-break-after: auto !important;
    }
}

        }
    </style>
</head>

<body>
     <?php require('include/sidebar.php'); ?>
     <!-- Main Content -->
  <div id="main-content">
    <div class="sidebar-overlay"></div>

    <!-- Professional Header -->
    <header class="professional-header">
        <div class="container">
            <div class="logo-container">
                <i class="bi bi-sun-fill logo-icon"></i>
                <div class="header-content">
                    <h1>VK Solar Energy - Solar Quotation Generator</h1>
                    <p>Create professional solar quotations in VK Solar Energy style</p>
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
                <div class="step-text">Preview Quotation</div>
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
                    <h5><i class="bi bi-file-earmark-text"></i> VK Solar Energy Quotation Form</h5>
                </div>
                <div class="card-body">
                    <form id="quotationForm">
                        <!-- Customer Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-person-vcard"></i> Customer Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="customer_name" class="required">Customer Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                                        required placeholder="Enter customer name">
                                </div>
                                <div class="form-group">
                                    <label for="customer_address" class="required">Customer Address</label>
                                    <input type="text" class="form-control" id="customer_address"
                                        name="customer_address" required placeholder="Enter customer address">
                                </div>
                                <div class="form-group">
                                    <label for="pin_code" class="required">Pin Code</label>
                                    <input type="text" class="form-control" id="pin_code" name="pin_code" required
                                        placeholder="Enter pin code">
                                </div>
                                <div class="form-group">
                                    <label for="customer_phone" class="required">Customer Phone</label>
                                    <input type="text" class="form-control" id="customer_phone" name="customer_phone"
                                        required placeholder="Enter phone number">
                                </div>
                                <div class="form-group">
                                    <label for="customer_email" class="required">Customer Email</label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email"
                                        required placeholder="Enter email address">
                                </div>
                                <div class="form-group">
                                    <label for="quotation_date" class="required">Quotation Date</label>
                                    <input type="date" class="form-control" id="quotation_date" name="quotation_date"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="quotation_number" class="required">Quotation Number</label>
                                    <input type="text" class="form-control" id="quotation_number"
                                        name="quotation_number" required placeholder="e.g., VKS-2024-001">
                                </div>
                            </div>
                        </div>

                        <!-- Project Details -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-sun"></i> Project Details
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="project_location" class="required">Project Location</label>
                                    <input type="text" class="form-control" id="project_location"
                                        name="project_location" required placeholder="Enter project location">
                                </div>
                                <div class="form-group">
                                    <label for="plant_capacity" class="required">Plant Capacity</label>
                                    <input type="text" class="form-control" id="plant_capacity" name="plant_capacity"
                                        required placeholder="e.g., 3kWp">
                                </div>
                                <div class="form-group">
                                    <label for="system_type" class="required">System Type</label>
                                    <input type="text" class="form-control" id="system_type" name="system_type" required
                                        placeholder="e.g., On Grid">
                                </div>
                                <div class="form-group">
                                    <label for="estimated_generation" class="required">Estimated Annual
                                        Generation</label>
                                    <input type="text" class="form-control" id="estimated_generation"
                                        name="estimated_generation" required placeholder="e.g., 4,500 kWh">
                                </div>
                                <div class="form-group">
                                    <label for="system_description" class="required">System Description</label>
                                    <textarea class="form-control" id="system_description" name="system_description"
                                        rows="4" required placeholder="Enter detailed system description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bi bi-cart-plus"></i> Products & Services
                            </div>
                            <div id="productsContainer">
                                <div class="product-item" id="product-1">
                                    <div class="product-item-header">
                                        <div class="product-item-title">Product/Service #1</div>
                                        <button type="button" class="remove-product" onclick="removeProduct(1)"
                                            disabled>
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="required">Description</label>
                                            <input type="text" class="form-control product-description" data-product="1"
                                                required placeholder="Enter product/service description">
                                        </div>
                                        <div class="form-group">
                                            <label class="required">Quantity</label>
                                            <input type="number" class="form-control product-quantity" data-product="1"
                                                required value="1" min="1" onchange="calculateTotals()">
                                        </div>
                                        <div class="form-group">
                                            <label class="required">Unit Price (₹)</label>
                                            <input type="number" class="form-control product-unit-price"
                                                data-product="1" required value="0" min="0"
                                                onchange="calculateTotals()" placeholder="Enter unit price">
                                        </div>
                                        <div class="form-group">
                                            <label>Amount (₹)</label>
                                            <input type="text" class="form-control product-amount" data-product="1"
                                                readonly value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-warning btn-professional add-product-btn"
                                onclick="addProduct()">
                                <i class="bi bi-plus-circle"></i> Add Another Product/Service
                            </button>

                            <div class="products-total">
                                <h5>Financial Summary</h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="subsidy">Subsidy (₹)</label>
                                        <input type="number" class="form-control" id="subsidy" name="subsidy"
                                            value="0" min="0" onchange="calculateTotals()" placeholder="Enter subsidy amount">
                                    </div>
                                    <div class="form-group">
                                        <label for="validity_date" class="required">Price Validity Until</label>
                                        <input type="date" class="form-control" id="validity_date" name="validity_date"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <div class="auto-calculate">
                                            <strong>Auto-Calculated Totals:</strong>
                                            <div>Total Amount: ₹<span id="total_amount_display">0</span></div>
                                            <div>Subsidy: ₹<span id="subsidy_display">0</span></div>
                                            <div>Final Amount (After Subsidy): ₹<span
                                                    id="final_amount_display">0</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-primary btn-professional" id="generateBtn">
                                <i class="bi bi-eye"></i> Generate Quotation Preview
                            </button>
                            <button type="button" class="btn btn-success btn-professional" id="fillSampleBtn">
                                <i class="bi bi-arrow-clockwise"></i> Fill with Sample Data
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-professional" id="resetBtn">
                                <i class="bi bi-arrow-clockwise"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="previewSection">
            <div class="preview-controls">
                <h3 class="preview-title"><i class="bi bi-file-text"></i> VK Solar Energy Quotation Preview</h3>
                <div class="action-buttons">
                    <button class="btn btn-success btn-professional" id="downloadPdfBtn">
                        <i class="bi bi-download"></i> Download as PDF
                    </button>
                    <button class="btn btn-primary btn-professional" id="printBtn">
                        <i class="bi bi-printer"></i> Print Quotation
                    </button>
                    <button class="btn btn-outline-secondary btn-professional" id="backToFormBtn">
                        <i class="bi bi-arrow-left"></i> Back to Form
                    </button>
                </div>
            </div>

            <div class="quotation-output" id="quotationOutput">
                <!-- Quotation will be generated here -->
            </div>
        </div>
    </div>
</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Static company information
        const COMPANY_INFO = {
            name: "VK SOLAR ENERGY",
            tagline: "Authorized Channel Partner: KIRLOSKAR SOLAR TECHNOLOGY PVT.LTD.",
            badges: ["MSEDCL Approved", "MSME Supplier", "ON-Grid Solar plants", "Solar Energy"],
            headOffice: "NEAR DRA.V. JOSHI CLINIC<br>KHADGAON ROAD KOHALE<br>LAYOUT WADI NAGPUR 440023",
            branchOffices: "AMPAVATI, AURANGABAD, BULDHANA<br>KHAMGAON, SHEGAON, NANDURA",
            phone: "9657135476 / 9075305275",
            email: "vksolarenergy1989@gmail.com",
            gstin: "27CJXPK1402QJZK",
            bankName: "HDFC Bank",
            branchName: "Ghaziabad Main Branch",
            ifsc: "HDFC0001234",
            accountNumber: "107066737268",
            accountType: "Current Account",
            bankGst: "09AABCU9603R1ZX"
        };

        const WARRANTY_INFO = {
            solarPanel: "25 Years from date of commissioning",
            inverter: "7 Years with extended warranty as per OEM or with applicable PAC",
            otherComponents: "Other components are warranted for 1 Year unless specified T&C",
            workmanship: "5 Years Installation Workmanship Warranty",
            exclusions: "Product physical damage due to natural calamity or any other reasons will not be covered under warranty."
        };

        // Global variables
        let productCounter = 1;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            // Set default dates
            const today = new Date();
            document.getElementById('quotation_date').valueAsDate = today;

            const validityDate = new Date();
            validityDate.setMonth(validityDate.getMonth() + 3);
            document.getElementById('validity_date').valueAsDate = validityDate;

            // Set initial calculations
            calculateTotals();

            // Event listeners
            document.getElementById('generateBtn').addEventListener('click', generateQuotation);
            document.getElementById('fillSampleBtn').addEventListener('click', fillFormWithSampleData);
            document.getElementById('backToFormBtn').addEventListener('click', showForm);
            document.getElementById('printBtn').addEventListener('click', printQuotation);
            document.getElementById('downloadPdfBtn').addEventListener('click', downloadPDF);
            document.getElementById('resetBtn').addEventListener('click', resetForm);

            // Update step indicator
            updateStepIndicator(1);
        });

        function updateStepIndicator(step) {
            document.querySelectorAll('.step').forEach(el => {
                el.classList.remove('active', 'completed');
            });

            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById(`step${i}`);
                if (i < step) {
                    stepEl.classList.add('completed');
                } else if (i === step) {
                    stepEl.classList.add('active');
                }
            }
        }

        function addProduct() {
            productCounter++;
            const newProduct = document.createElement('div');
            newProduct.className = 'product-item';
            newProduct.id = `product-${productCounter}`;
            newProduct.innerHTML = `
                <div class="product-item-header">
                    <div class="product-item-title">Product/Service #${productCounter}</div>
                    <button type="button" class="remove-product" onclick="removeProduct(${productCounter})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">Description</label>
                        <input type="text" class="form-control product-description" data-product="${productCounter}" required placeholder="Enter product/service description">
                    </div>
                    <div class="form-group">
                        <label class="required">Quantity</label>
                        <input type="number" class="form-control product-quantity" data-product="${productCounter}" required value="1" min="1" onchange="calculateTotals()">
                    </div>
                    <div class="form-group">
                        <label class="required">Unit Price (₹)</label>
                        <input type="number" class="form-control product-unit-price" data-product="${productCounter}" required value="0" min="0" onchange="calculateTotals()" placeholder="Enter unit price">
                    </div>
                    <div class="form-group">
                        <label>Amount (₹)</label>
                        <input type="text" class="form-control product-amount" data-product="${productCounter}" readonly value="0">
                    </div>
                </div>
            `;
            document.getElementById('productsContainer').appendChild(newProduct);
            calculateTotals();
        }

        function removeProduct(productId) {
            if (productCounter > 1) {
                const productElement = document.getElementById(`product-${productId}`);
                if (productElement) {
                    productElement.remove();
                    calculateTotals();
                }
            }
        }

        function calculateTotals() {
            let totalAmount = 0;

            // Calculate total for each product
            for (let i = 1; i <= productCounter; i++) {
                const productElement = document.getElementById(`product-${i}`);
                if (productElement) {
                    const quantity = parseFloat(productElement.querySelector('.product-quantity').value) || 0;
                    const unitPrice = parseFloat(productElement.querySelector('.product-unit-price').value) || 0;
                    const amount = quantity * unitPrice;

                    productElement.querySelector('.product-amount').value = formatNumber(amount);
                    totalAmount += amount;
                }
            }

            const subsidy = parseFloat(document.getElementById('subsidy').value) || 0;
            const finalAmount = Math.max(0, totalAmount - subsidy);

            document.getElementById('total_amount_display').textContent = formatNumber(totalAmount);
            document.getElementById('subsidy_display').textContent = formatNumber(subsidy);
            document.getElementById('final_amount_display').textContent = formatNumber(finalAmount);

            return { totalAmount, subsidy, finalAmount };
        }

        function formatNumber(num) {
            return num.toLocaleString('en-IN');
        }

        function formatCurrency(amount) {
            return '₹' + amount.toLocaleString('en-IN');
        }

        function fillFormWithSampleData() {
            // Reset to only one product
            while (productCounter > 1) {
                removeProduct(productCounter);
                productCounter--;
            }

            // Update first product
            document.querySelector('.product-description').value = "Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system";
            document.querySelector('.product-quantity').value = "1";
            document.querySelector('.product-unit-price').value = "220000";

            // Fill form fields
            document.getElementById('customer_name').value = 'MADHUKAN MISHBAM';
            document.getElementById('customer_address').value = 'MAETUR AHUANGSTTAJ';
            document.getElementById('pin_code').value = '440014';
            document.getElementById('customer_phone').value = '+91 98765 43212';
            document.getElementById('customer_email').value = 'customer@example.com';
            document.getElementById('quotation_number').value = 'VKS-2024-001';
            document.getElementById('project_location').value = 'MACPUB';
            document.getElementById('plant_capacity').value = '3kWp';
            document.getElementById('system_type').value = 'On Grid';
            document.getElementById('estimated_generation').value = '4,500 kWh';
            document.getElementById('system_description').value = 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.';
            document.getElementById('subsidy').value = '77000';

            const today = new Date();
            document.getElementById('quotation_date').valueAsDate = today;

            const validityDate = new Date();
            validityDate.setMonth(validityDate.getMonth() + 3);
            document.getElementById('validity_date').valueAsDate = validityDate;

            calculateTotals();

            alert('Form filled with sample data!');
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset all form fields?')) {
                // Reset to only one product
                while (productCounter > 1) {
                    removeProduct(productCounter);
                    productCounter--;
                }

                document.getElementById('quotationForm').reset();

                // Reset first product
                document.querySelector('.product-description').value = "";
                document.querySelector('.product-quantity').value = "1";
                document.querySelector('.product-unit-price').value = "0";

                const today = new Date();
                document.getElementById('quotation_date').valueAsDate = today;

                const validityDate = new Date();
                validityDate.setMonth(validityDate.getMonth() + 3);
                document.getElementById('validity_date').valueAsDate = validityDate;

                calculateTotals();
            }
        }

        function generateQuotation() {
            // Collect products data
            const products = [];
            for (let i = 1; i <= productCounter; i++) {
                const productElement = document.getElementById(`product-${i}`);
                if (productElement) {
                    products.push({
                        description: productElement.querySelector('.product-description').value,
                        quantity: parseFloat(productElement.querySelector('.product-quantity').value) || 0,
                        unitPrice: parseFloat(productElement.querySelector('.product-unit-price').value) || 0,
                        amount: parseFloat(productElement.querySelector('.product-amount').value.replace(/,/g, '')) || 0
                    });
                }
            }

            // Collect form data
            const form = document.getElementById('quotationForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Validate required fields
            let isValid = true;
            const requiredFields = ['customer_name', 'customer_address', 'plant_capacity', 'system_description'];

            requiredFields.forEach(field => {
                if (!data[field]) {
                    isValid = false;
                    document.getElementById(field).classList.add('is-invalid');
                } else {
                    document.getElementById(field).classList.remove('is-invalid');
                }
            });

            // Validate products
            products.forEach((product, index) => {
                if (!product.description || product.unitPrice <= 0) {
                    isValid = false;
                    const productElement = document.getElementById(`product-${index + 1}`);
                    if (productElement) {
                        const inputs = productElement.querySelectorAll('.form-control');
                        inputs.forEach(input => input.classList.add('is-invalid'));
                    }
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields marked with * and ensure all products have valid prices');
                return;
            }

            const { totalAmount, subsidy, finalAmount } = calculateTotals();

            const quotationDate = new Date(data.quotation_date);
            const validityDate = new Date(data.validity_date);

            const formattedQuotationDate = quotationDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'short', year: 'numeric'
            });

            const formattedValidityDate = validityDate.toLocaleDateString('en-GB', {
                day: 'numeric', month: 'short', year: 'numeric'
            });

            // Generate the quotation HTML
            const quotationHTML = createQuotationHTML(data, products, formattedQuotationDate, formattedValidityDate, totalAmount, subsidy, finalAmount);

            // Display the quotation
            document.getElementById('quotationOutput').innerHTML = quotationHTML;

            // Show preview section, hide form section
            document.getElementById('formSection').style.display = 'none';
            document.getElementById('previewSection').style.display = 'block';

            updateStepIndicator(2);

            window.scrollTo(0, 0);
        }

        function createQuotationHTML(data, products, formattedQuotationDate, formattedValidityDate, totalAmount, subsidy, finalAmount) {
            // Generate products rows HTML
            let productsRowsHTML = '';
            products.forEach((product, index) => {
                productsRowsHTML += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td>${product.description}</td>
                        <td style="text-align: center;">${product.quantity}</td>
                        <td style="text-align: right;">${formatCurrency(product.unitPrice)}</td>
                        <td style="text-align: right;">${formatCurrency(product.amount)}</td>
                    </tr>
                `;
            });

            // Use a reliable solar energy image
            const headerImageURL = '/admin/img/vk_banner.jpg?w=1200&h=300&fit=crop&crop=center';

            return `
            
                <div class="preview-container" id="printableQuotation">
                    <!-- PAGE 1 -->
                    <div class="preview-page">
                        <!-- Company Header -->
                        <div class="company-header-preview">
                            <img src="${headerImageURL}" alt="VK Solar Energy" class="header-image-preview">
                            
                        </div>
                        
                        <!-- Quotation Details -->
                        <div class="quotation-details-preview">
                            <div><strong>Quotation No:</strong> ${data.quotation_number}</div>
                            <div><strong>Date:</strong> ${formattedQuotationDate}</div>
                            <div><strong>Price Validity:</strong> ${formattedValidityDate}</div>
                        </div>
                        
                        <!-- Quotation Title -->
                        <div class="quotation-title-preview">
                            QUOTATION FOR SOLAR POWER SYSTEM
                        </div>
                        
                        <!-- Customer Information -->
                        <div class="customer-section-preview">
                            <h4>TO:</h4>
                            <div><strong>${data.customer_name}</strong></div>
                            <div>${data.customer_address}</div>
                            <div><strong>Pin Code:</strong> ${data.pin_code}</div>
                            <div><strong>Phone:</strong> ${data.customer_phone}</div>
                            <div><strong>Email:</strong> ${data.customer_email}</div>
                        </div>
                        
                        <!-- Letter Content -->
                        <div class="letter-content-preview">
                            <p>Dear Sir/Madam,</p>
                            <p>We appreciate your interest in <strong style="color: #1b5e20;">${COMPANY_INFO.name}</strong> for your Solar Energy needs. We are pleased to provide you with the following quotation for installation of a Solar System tailored to your requirements of ${data.plant_capacity} ${data.system_type} system.</p>
                        </div>
                        
                        <!-- Bill of Materials Table -->
                        <table class="bill-table-preview">
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">Sr. No</th>
                                    <th style="width: 60%;">Description</th>
                                    <th style="width: 5%; text-align: center;">Qty</th>
                                    <th style="width: 15%; text-align: right;">Unit Price (₹)</th>
                                    <th style="width: 15%; text-align: right;">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${productsRowsHTML}
                                <tr>
                                    <td colspan="3" style="border: none;"></td>
                                    <td style="text-align: right; border-left: 1px solid #ddd;"><strong>Total</strong></td>
                                    <td style="text-align: right;"><strong>${formatCurrency(totalAmount)}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="border: none;"></td>
                                    <td style="text-align: right; border-left: 1px solid #ddd;"><strong>Subsidy</strong></td>
                                    <td style="text-align: right;"><strong>${formatCurrency(subsidy)}</strong></td>
                                </tr>
                                <tr style="background-color: #e8f5e9 !important;">
                                    <td colspan="3" style="border: none;"></td>
                                    <td style="text-align: right; border-left: 1px solid #ddd;"><strong>Final Amount (After Subsidy)</strong></td>
                                    <td style="text-align: right;"><strong>${formatCurrency(finalAmount)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Terms & Conditions and Bank Details -->
                        <div class="terms-section-preview">
                            <!-- Terms & Conditions -->
                            <div class="terms-column-preview">
                                <h4>Terms & Conditions</h4>
                                <ol>
                                    <li>Prices are inclusive of all taxes (GST @ 18%).</li>
                                    <li>Payment Terms: 50% advance, 40% after installation, 10% after commissioning.</li>
                                    <li>Installation includes mounting structure, DC wiring, AC wiring, and net meter installation.</li>
                                    <li>Above prices are valid until ${formattedValidityDate}.</li>
                                    <li>Any government subsidies will be processed and adjusted as per actual realization.</li>
                                    <li>Delivery of equipment will be within 7-10 days from date of order confirmation.</li>
                                    <li>Installation timeline is 3-4 weeks from the date of funding confirmation.</li>
                                    <li>All materials used will be of standard quality and as per specifications mentioned.</li>
                                </ol>
                            </div>
                            
                            <!-- Bank Details -->
                            <div class="terms-column-preview">
                                <h4>Bank Details</h4>
                                <div class="bank-details-preview">
                                    <div><strong>Bank Name:</strong> ${COMPANY_INFO.bankName}</div>
                                    <div><strong>Branch Name:</strong> ${COMPANY_INFO.branchName}</div>
                                    <div><strong>IFSC Code:</strong> ${COMPANY_INFO.ifsc}</div>
                                    <div><strong>Account Number:</strong> ${COMPANY_INFO.accountNumber}</div>
                                    <div><strong>Account Type:</strong> ${COMPANY_INFO.accountType}</div>
                                    <div><strong>GST No:</strong> ${COMPANY_INFO.bankGst}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PAGE 2 -->
                    <div class="preview-page">
                        <!-- Horizontal Line -->
                        <div style="border: none; border-top: 2px solid #2e7d32; margin: 15px 0;"></div>
                        
                        <!-- Project Details -->
                        <div style="margin-bottom: 15px; font-size: 10pt; line-height: 1.4; background-color: #e8f5e9; padding: 10px 12px; border-radius: 3px; border-left: 4px solid #2e7d32; position: relative; z-index: 2;">
                            <h1 style="font-size: 12pt; margin: 0 0 8px 0; color: #1b5e20;">Project Details</h1>
                            <p>${data.system_description}</p>
                        </div>
                        
                        <!-- Horizontal Line -->
                        <div style="border: none; border-top: 2px solid #2e7d32; margin: 15px 0;"></div>
                        
                        <!-- System Specifications -->
                        <div style="border: 1px solid #2e7d32; padding: 10px 12px; margin: 15px 0; font-size: 9pt; line-height: 1.3; background-color: #e8f5e9; border-radius: 3px; position: relative; z-index: 2;">
                            <h4 style="margin: 0 0 8px 0; font-size: 10pt; color: #1b5e20; padding-bottom: 3px; border-bottom: 1px solid #4caf50;">System Specifications:</h4>
                            <div><strong>Project Location:</strong> ${data.project_location}</div>
                            <div><strong>Plant Capacity:</strong> ${data.plant_capacity}</div>
                            <div><strong>System Type:</strong> ${data.system_type}</div>
                            <div><strong>Estimated Annual Generation:</strong> ${data.estimated_generation}</div>
                            <div><strong>Solar Panels:</strong> Mono PERC 540W - Tier 1 Quality</div>
                            <div><strong>Inverter:</strong> Hybrid ${data.plant_capacity.replace('kWp', 'kW')} with Wi-Fi Monitoring</div>
                            <div><strong>Mounting Structure:</strong> Galvanized Iron (GI) with anti-corrosion coating</div>
                        </div>
                        
                        <!-- Horizontal Line -->
                        <div style="border: none; border-top: 2px solid #2e7d32; margin: 15px 0;"></div>
                        
                        <!-- Product Warranty -->
                        <h1 style="font-size: 12pt; margin: 15px 0 10px 0; color: #1b5e20; position: relative; z-index: 2;">Product Warranty</h1>
                        <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9pt; position: relative; z-index: 2; background: white;">
                            <thead>
                                <tr>
                                    <th style="width: 30%; border: 1px solid #ddd; padding: 6px; text-align: left; background-color: #2e7d32; color: white; font-weight: bold;">Component</th>
                                    <th style="width: 70%; border: 1px solid #ddd; padding: 6px; text-align: left; background-color: #2e7d32; color: white; font-weight: bold;">Warranty Period</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">Solar Panels</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">${WARRANTY_INFO.solarPanel}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">Inverter</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">${WARRANTY_INFO.inverter}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">Other Components</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">${WARRANTY_INFO.otherComponents}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">Installation Workmanship</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: left;">${WARRANTY_INFO.workmanship}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Note Box -->
                        <div style="font-size: 8.5pt; margin: 10px 0; line-height: 1.3; background-color: #e8f5e9; padding: 8px 10px; border-radius: 3px; border-left: 3px solid #2e7d32; position: relative; z-index: 2;">
                            <p><strong>Note:</strong> ${WARRANTY_INFO.exclusions}</p>
                            <p><strong>Project Timeline:</strong> 3-4 weeks from the date of funding confirmation</p>
                            <p style="font-style: italic;">(Excluding any delay due to project modifications, scope changes, government approvals, or weather conditions)</p>
                        </div>
                        
                        <!-- Horizontal Line -->
                        <div style="border: none; border-top: 2px solid #2e7d32; margin: 15px 0;"></div>
                        
                        <!-- Signature Area -->
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #2e7d32; font-size: 10pt; position: relative; z-index: 2;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <div style="width: 45%;">
                                    <h4 style="color: #1b5e20; margin-bottom: 8px; font-size: 10pt;">For ${COMPANY_INFO.name}</h4>
                                    <div style="border-bottom: 1px solid #2e7d32; width: 200px; margin: 5px 0 25px 0; height: 20px;"></div>
                                    <div>Authorized Signatory</div>
                                    <div style="margin-top: 25px;">Date: _______________</div>
                                </div>
                                <div style="width: 45%;">
                                    <h4 style="color: #1b5e20; margin-bottom: 8px; font-size: 10pt;">Customer Acceptance</h4>
                                    <div style="border-bottom: 1px solid #2e7d32; width: 200px; margin: 5px 0 25px 0; height: 20px;"></div>
                                    <div>Signature</div>
                                    <div style="margin-top: 25px;">Date: _______________</div>
                                </div>
                            </div>
                            
                            <div style="text-align: center; font-style: italic; font-size: 8pt; margin-top: 15px; color: #666; padding: 8px; background-color: #e8f5e9; border-radius: 3px;">
                                <p>This is a computer generated quotation and does not require a physical signature</p>
                                <p><strong>Contact:</strong> ${COMPANY_INFO.phone} | <strong>Email:</strong> ${COMPANY_INFO.email}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function showForm() {
            document.getElementById('formSection').style.display = 'block';
            document.getElementById('previewSection').style.display = 'none';
            updateStepIndicator(1);
        }

        function printQuotation() {
            updateStepIndicator(3);

            // Get the printable content
           const printableContent = document.querySelector('#printableQuotation').innerHTML;

            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=800,height=600');

            // Write print content with proper styles
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>VK Solar Energy - Solar Quotation</title>
                    <style>#previewSection {
            display: none;
        }
        
        .preview-controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .preview-title {
            color: var(--primary-green);
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
        
        .quotation-output {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        /* Preview container styling to match print preview */
        .preview-container {
    width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 20mm;
    font-family: Arial, sans-serif;
    line-height: 1.2;
    color: #000;
    font-size: 11pt;
}

        
        .preview-page {
            position: relative;
            min-height: 277mm;
            margin-bottom: 10mm;
            background: white;
           
        }.preview-page:last-child {
    page-break-after: auto !important;
}
        
        /* Company Header Styles for Preview */
        .company-header-preview {
    position: relative;
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
}

.header-image-preview {
    width: 100%;
    height: auto;
    object-fit: contain;
    display: block;
}

        .header-overlay-preview {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            z-index: 2;
        }
        
        .company-name-preview {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
            color: white;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        
        .company-tagline-preview {
            font-size: 16px;
            margin-bottom: 15px;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        .company-badges-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .badge-preview {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .company-details-preview {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }
        
        .office-info-preview {
            flex: 1;
            min-width: 200px;
        }
        
        .office-info-preview h4 {
            font-size: 13px;
            margin-bottom: 5px;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 3px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        .office-info-preview p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        .contact-info-preview {
            flex: 1;
            min-width: 200px;
        }
        
        .contact-details-preview p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        
        /* Quotation Details for Preview */
        .quotation-details-preview {
            font-size: 11pt;
            margin-bottom: 15px;
            line-height: 1.3;
            background-color: #e8f5e9 !important;
            padding: 10px 15px;
            border-radius: 5px;
            border-left: 4px solid #2e7d32;
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }
        
        .quotation-title-preview {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px 0;
            border-bottom: 3px solid #2e7d32;
            border-top: 3px solid #2e7d32;
            color: #1b5e20;
            position: relative;
            z-index: 2;
        }
        
        .customer-section-preview {
            margin-bottom: 20px;
            font-size: 11pt;
            padding: 12px;
            background-color: #e8f5e9 !important;
            border-radius: 5px;
            border-left: 4px solid #4caf50;
            position: relative;
            z-index: 2;
        }
        
        .customer-section-preview h4 {
            margin: 0 0 8px 0;
            font-size: 12pt;
            color: #1b5e20;
        }
        
        .letter-content-preview {
            font-size: 11pt;
            margin-bottom: 20px;
            line-height: 1.5;
            position: relative;
            z-index: 2;
        }
        
        /* Bill of Materials Table for Preview */
        .bill-table-preview {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
            position: relative;
            z-index: 2;
            background: white;
        }
        
        .bill-table-preview th {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            background-color: #2e7d32 !important;
            color: white !important;
            font-weight: bold;
        }
        
        .bill-table-preview td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            background: white;
        }
        
        .bill-table-preview tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .bill-table-preview tr:last-child {
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }
        
        /* Terms & Conditions for Preview */
        .terms-section-preview {
            display: flex;
            margin-top: 20px;
            gap: 15px;
            position: relative;
            z-index: 2;
        }
        
        .terms-column-preview {
            flex: 1;
            font-size: 6pt;
            line-height: 1.3;
            background: white;
            position: relative;
            z-index: 2;
        }
        
        .terms-column-preview h4 {
            color: #1b5e20;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #4caf50;
            font-size: 15pt;
        }
        
        .terms-column-preview ol {
            margin: 0;
            padding-left: 15px;
            background-color: #e8f5e9 !important;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .terms-column-preview li {
            margin-bottom: 3px;
        }
        
        .bank-details-preview {
            font-size: 6pt;
            line-height: 1.3;
            background-color: #e8f5e9 !important;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .bank-details-preview div {
            margin-bottom: 3px;
        }
                    </style>
                       
                </head>
                <body>
                    
                        ${printableContent}
                    
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 100);
                        };
                    <\/script>
                </body>
                </html>
            `);

            printWindow.document.close();
        }

        function downloadPDF() {
            updateStepIndicator(3);

            const element = document.querySelector('.preview-container');

            const opt = {
                margin: [15, 15, 0, 15],
                filename: `VK_Solar_Quotation_${document.getElementById('quotation_number').value}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#FFFFFF'
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: { mode: ['css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>