<?php
// admin/add_user.php
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requireAnyRole(['super_admin', 'admin']);
$auth->checkPermission('user_management', 'create');

$title = "add_user";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require('include/head.php'); ?>
  <title>Add User - VK Solar</title>
 
      <style>
      /* VK Solar Energy - Green Theme Custom CSS */
      :root {
        --primary: #2e8b57; /* Forest Green */
        --primary-light: #3cb371; /* Medium Sea Green */
        --primary-dark: #1e6b47; /* Darker Green */
        --secondary: #f5f5f5; /* Off-white */
        --accent: #87ceeb; /* Sky Blue */
        --accent-light: #b0e2ff; /* Light Sky Blue */
        --earth: #d2b48c; /* Tan/Earth tone */
        --earth-light: #f0e6d6; /* Light Beige */
        --dark: #2c3e50; /* Dark Blue-Gray */
        --light: #f8f9fa; /* Light Gray */
        --success: #10b981;
        --border-radius: 8px;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
          0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --transition: all 0.3s ease;
        --gradient-primary: linear-gradient(
          135deg,
          var(--primary) 0%,
          var(--primary-light) 100%
        );
        --gradient-secondary: linear-gradient(
          135deg,
          var(--earth-light) 0%,
          var(--secondary) 100%
        );
        --gradient-hero: linear-gradient(
          135deg,
          rgba(46, 139, 87, 0.85) 0%,
          rgba(60, 179, 113, 0.85) 100%
        );
        --gradient-nature: linear-gradient(
          135deg,
          var(--primary) 0%,
          var(--accent) 100%
        );
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
      }

      body {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        color: var(--dark);
        line-height: 1.6;
        min-height: 100vh;
        padding: 20px;
      }

      .container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
      }

      header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid rgba(46, 139, 87, 0.1);
      }

      .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 10px;
        flex-wrap: wrap;
      }

      .logo i {
        font-size: 2.5rem;
        color: var(--primary);
      }

      h1 {
        font-size: 2.2rem;
        color: var(--primary);
        margin-bottom: 5px;
        line-height: 1.2;
      }

      .subtitle {
        color: var(--dark);
        font-size: 1.1rem;
      }

      .app-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
      }

      .form-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 30px;
        transition: var(--transition);
        border: 1px solid rgba(46, 139, 87, 0.1);
      }

      .preview-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 30px;
        display: none;
        flex-direction: column;
        border: 1px solid rgba(46, 139, 87, 0.1);
      }

      .preview-container.active {
        display: flex;
      }

      .form-container.hidden {
        display: none;
      }

      .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.4rem;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-light);
      }

      .section-title i {
        font-size: 1.2rem;
      }

      .form-section {
        margin-bottom: 30px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
      }

      input,
      select,
      textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--primary-light);
        border-radius: var(--border-radius);
        font-size: 1rem;
        transition: var(--transition);
      }

      input:focus,
      select:focus,
      textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
      }

      .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .checkbox-group input {
        width: auto;
      }

      .form-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
      }

      .form-row .form-group {
        flex: 1 1 300px;
      }

      .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
      }

      .btn:hover {
        background: linear-gradient(
          135deg,
          var(--primary-light) 0%,
          var(--primary) 100%
        );
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(46, 139, 87, 0.4);
      }

      .btn-secondary {
        background: var(--earth);
      }

      .btn-secondary:hover {
        background: #c19a6b;
      }

      .btn-accent {
        background: var(--accent);
      }

      .btn-accent:hover {
        background: #6cb4d9;
      }

      .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
      }

      .preview-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--primary-light);
      }

      .preview-content {
        flex: 1;
        background: #f8fafc;
        border-radius: var(--border-radius);
        padding: 25px;
        overflow-y: auto;
        max-height: 700px;
      }

      /* Full Screen PDF Preview Styles */
      .fullscreen-pdf-preview {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 9999;
        overflow: auto;
        padding: 0;
        margin: 0;
      }

      .fullscreen-pdf-preview .pdf-page {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        margin: 0;
        box-shadow: none;
        border: none;
        border-radius: 0;
        page-break-after: always;
      }

      .fullscreen-pdf-preview .pdf-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--primary);
        padding-bottom: 20px;
      }

      .fullscreen-pdf-preview .pdf-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
      }

      .fullscreen-pdf-preview .pdf-table th,
      .fullscreen-pdf-preview .pdf-table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
      }

      .fullscreen-pdf-preview .pdf-table th {
        background-color: rgba(46, 139, 87, 0.1);
        font-weight: 600;
        color: var(--primary-dark);
      }

      .fullscreen-pdf-preview .signature-area {
        display: flex;
        justify-content: space-between;
        margin-top: 80px;
        flex-wrap: wrap;
        gap: 20px;
      }

      .fullscreen-pdf-preview .signature-box {
        width: 45%;
        text-align: center;
      }

      .fullscreen-pdf-preview .signature-line {
        border-bottom: 1px solid var(--primary);
        height: 30px;
        margin-bottom: 5px;
      }

      .fullscreen-pdf-preview .declaration {
        background-color: rgba(46, 139, 87, 0.05);
        padding: 15px;
        border-radius: 4px;
        margin-top: 15px;
        font-size: 0.9rem;
        border-left: 4px solid var(--primary);
      }

      .pdf-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        gap: 10px;
        z-index: 10000;
      }

      .pdf-controls .btn {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      }

      .close-fullscreen {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
        background: var(--primary);
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      }

      .close-fullscreen:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
      }

      .status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        padding: 15px 20px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        border: 1px solid rgba(46, 139, 87, 0.1);
        flex-wrap: wrap;
        gap: 15px;
      }

      .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 200px;
      }

      .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--earth-light);
        color: var(--primary-dark);
      }

      .status-icon.active {
        background: var(--primary);
        color: white;
      }

      .status-text {
        font-weight: 600;
      }

      .status-text small {
        display: block;
        font-weight: normal;
        color: var(--dark);
        font-size: 0.8rem;
      }

      .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--dark);
      }

      .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: var(--primary-light);
      }

      .required::after {
        content: " *";
        color: #e11d48;
      }

      .form-hint {
        font-size: 0.85rem;
        color: var(--primary-dark);
        margin-top: 5px;
      }

      .tab-container {
        display: flex;
        border-bottom: 1px solid var(--primary-light);
        margin-bottom: 20px;
        flex-wrap: wrap;
      }

      .tab {
        padding: 12px 20px;
        cursor: pointer;
        font-weight: 600;
        color: var(--primary-dark);
        border-bottom: 3px solid transparent;
        transition: var(--transition);
        flex: 1;
        text-align: center;
        min-width: 120px;
      }

      .tab.active {
        color: var(--dark);
        border-bottom: 3px solid var(--primary);
      }

      .tab-content {
        display: none;
      }

      .tab-content.active {
        display: block;
      }

      .back-to-form {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
      }

      /* Floating Buttons */
      .floating-btn {
        position: fixed;
        right: 20px;
        z-index: 1000;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      }

      /* WhatsApp Button */
      .whatsapp-btn {
        bottom: 80px;
        background: #25d366;
      }
      .whatsapp-btn:hover {
        background: #20b954;
        transform: translateY(-3px);
      }

      /* Back to Top Button */
      .back-to-top {
        bottom: 20px;
        background: var(--primary);
      }
      .back-to-top:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
      }

      /* Responsive Table Styles */
      .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
      }

      /* Mobile-specific styles */
      @media (max-width: 768px) {
        body {
          padding: 10px;
        }

        header {
          padding: 15px;
          margin-bottom: 20px;
        }

        h1 {
          font-size: 1.8rem;
        }

        .logo {
          flex-direction: column;
          gap: 10px;
        }

        .logo i {
          font-size: 2rem;
        }

        .form-container,
        .preview-container {
          padding: 20px;
        }

        .status-bar {
          flex-direction: column;
          align-items: flex-start;
        }

        .status-item {
          width: 100%;
        }

        .form-actions {
          flex-direction: column;
        }

        .btn {
          width: 100%;
          justify-content: center;
        }

        .pdf-page {
          padding: 20px;
          min-height: auto;
        }

        .pdf-table {
          font-size: 0.8rem;
        }

        .pdf-table th,
        .pdf-table td {
          padding: 6px;
        }

        .signature-area {
          flex-direction: column;
          gap: 30px;
          margin-top: 40px;
        }

        .signature-box {
          width: 100%;
        }

        .declaration {
          font-size: 0.8rem;
          padding: 10px;
        }

        .pdf-header h1 {
          font-size: 1.5rem;
        }

        .tab-container {
          flex-direction: column;
        }

        .tab {
          width: 100%;
          text-align: left;
        }

        .floating-btn {
          right: 15px;
          width: 45px;
          height: 45px;
          font-size: 18px;
        }

        .whatsapp-btn {
          bottom: 70px;
        }

        .back-to-form {
          justify-content: center;
        }

        .pdf-controls {
          bottom: 10px;
          right: 10px;
          flex-direction: column;
        }

        .close-fullscreen {
          top: 10px;
          right: 10px;
          width: 40px;
          height: 40px;
          font-size: 18px;
        }
      }

      @media (max-width: 480px) {
        body {
          padding: 5px;
        }

        header {
          padding: 10px;
        }

        h1 {
          font-size: 1.5rem;
        }

        .subtitle {
          font-size: 0.9rem;
        }

        .form-container,
        .preview-container {
          padding: 15px;
        }

        .pdf-page {
          padding: 15px;
        }

        .pdf-table {
          font-size: 0.7rem;
        }

        .pdf-table th,
        .pdf-table td {
          padding: 4px;
        }

        .pdf-header h1 {
          font-size: 1.3rem;
        }

        .section-title {
          font-size: 1.2rem;
        }

        .form-row .form-group {
          flex: 1 1 100%;
        }
      }

      /* Print-specific styles */
      @media print {
        body {
          background: white !important;
          padding: 0 !important;
          margin: 0 !important;
        }

        .container {
          max-width: 100% !important;
          margin: 0 !important;
          box-shadow: none !important;
        }

        header,
        .status-bar,
        .form-container,
        .preview-container,
        .floating-btn,
        .back-to-form,
        .form-actions,
        .section-title,
        .pdf-controls,
        .close-fullscreen {
          display: none !important;
        }

        .preview-container.active {
          display: block !important;
          box-shadow: none !important;
          border: none !important;
          padding: 0 !important;
        }

        .preview-content {
          background: white !important;
          padding: 0 !important;
          max-height: none !important;
          overflow: visible !important;
        }

        .pdf-page {
          box-shadow: none !important;
          border: none !important;
          padding: 20px !important;
          margin-bottom: 0 !important;
          min-height: auto !important;
          page-break-after: always;
        }

        .pdf-page:last-child {
          page-break-after: auto;
        }

        .pdf-header {
          margin-bottom: 20px !important;
          padding-bottom: 15px !important;
        }

        .pdf-table {
          font-size: 12px !important;
        }

        .pdf-table th,
        .pdf-table td {
          padding: 8px !important;
        }

        .declaration {
          font-size: 12px !important;
          padding: 12px !important;
        }

        .signature-area {
          margin-top: 50px !important;
        }
      }

      /* PDF Optimization Styles */
      .pdf-optimized {
        width: 100%;
        padding: 0;
        margin: 0;
      }

      .pdf-optimized .pdf-page {
        width: 100%;
        padding: 15px;
        margin: 0;
        box-shadow: none;
        border: none;
        page-break-after: always;
      }

      .pdf-optimized .pdf-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
      }

      .pdf-optimized .pdf-table {
        margin-bottom: 15px;
      }

      .pdf-optimized .pdf-table th,
      .pdf-optimized .pdf-table td {
        padding: 8px;
      }

      .pdf-optimized .signature-area {
        margin-top: 50px;
      }
    </style>
</head>
<body>
  <!-- Sidebar -->
  <?php require('include/sidebar.php') ?>

  <!-- Main Content -->
  <div id="main-content">
    <div class="sidebar-overlay"></div>

    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>

    <!-- Main Content -->
    <main>
      <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="mb-1">User Management</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="view_users">Users</a></li>
                <li class="breadcrumb-item active">Add User</li>
              </ol>
            </nav>
          </div>
          <a href="view_users" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
          </a>
        </div>

        <!-- Message Alert -->
        <div id="messageAlert" class="alert d-none">
          <i id="messageIcon" class="bi me-2"></i>
          <span id="messageText"></span>
        </div>
            <a
      href="https://wa.me/919657135476"
      class="floating-btn whatsapp-btn"
      target="_blank"
      aria-label="Chat on WhatsApp"
    >
      <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Back to Top -->
    <a href="#" class="floating-btn back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <div class="container">
      <header>
        <div class="logo">
          <i class="fas fa-solar-panel"></i>
          <div>
            <h1>Solar Power Plant Work Completion Report</h1>
            <div class="subtitle">
              Maharashtra State Electricity Distribution Co. Ltd.
            </div>
          </div>
        </div>
      </header>

      <div class="status-bar">
        <div class="status-item">
          <div class="status-icon active" id="form-status">
            <i class="fas fa-edit"></i>
          </div>
          <div class="status-text">
            Form Completion
            <small>Fill in all required details</small>
          </div>
        </div>
        <div class="status-item">
          <div class="status-icon" id="preview-status">
            <i class="fas fa-eye"></i>
          </div>
          <div class="status-text">
            Preview & Review
            <small>Check all information</small>
          </div>
        </div>
        <div class="status-item">
          <div class="status-icon" id="print-status">
            <i class="fas fa-print"></i>
          </div>
          <div class="status-text">
            Print & Submit
            <small>Finalize document</small>
          </div>
        </div>
      </div>

      <div class="app-container">
        <div class="form-container" id="form-container">
          <div class="section-title">
            <i class="fas fa-pen-to-square"></i>
            <h2>Report Details</h2>
          </div>

          <div class="tab-container">
            <div class="tab active" data-tab="basic">Basic Info</div>
            <div class="tab" data-tab="system">System Details</div>
            <div class="tab" data-tab="additional">Additional Info</div>
          </div>

          <form id="solar-report-form">
            <div id="basic" class="tab-content active">
              <div class="form-section">
                <div class="form-row">
                  <div class="form-group">
                    <label for="consumer_name" class="required"
                      >Consumer Name</label
                    >
                    <input
                      type="text"
                      id="consumer_name"
                      name="consumer_name"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="consumer_number" class="required"
                      >Consumer Number</label
                    >
                    <input
                      type="text"
                      id="consumer_number"
                      name="consumer_number"
                      required
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="address" class="required">Complete Address</label>
                  <textarea
                    id="address"
                    name="address"
                    rows="3"
                    required
                  ></textarea>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="category" class="required">Category</label>
                    <select id="category" name="category" required>
                      <option value="">Select Category</option>
                      <option value="PVT">Private</option>
                      <option value="GOVT">Government</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="sanction_number" class="required"
                      >Sanction Number</label
                    >
                    <input
                      type="text"
                      id="sanction_number"
                      name="sanction_number"
                      required
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="sanctioned_capacity" class="required"
                      >Sanctioned Capacity (KW)</label
                    >
                    <input
                      type="number"
                      id="sanctioned_capacity"
                      name="sanctioned_capacity"
                      step="0.01"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="system_capacity" class="required"
                      >System Capacity (KW)</label
                    >
                    <input
                      type="number"
                      id="system_capacity"
                      name="system_capacity"
                      step="0.01"
                      required
                    />
                  </div>
                </div>
              </div>
            </div>

            <div id="system" class="tab-content">
              <div class="form-section">
                <div class="form-row">
                  <div class="form-group">
                    <label for="module_make" class="required"
                      >Module Make</label
                    >
                    <input
                      type="text"
                      id="module_make"
                      name="module_make"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="almm_model" class="required"
                      >ALMM Model Number</label
                    >
                    <input
                      type="text"
                      id="almm_model"
                      name="almm_model"
                      required
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="wattage_per_module" class="required"
                      >Wattage per Module (W)</label
                    >
                    <input
                      type="number"
                      id="wattage_per_module"
                      name="wattage_per_module"
                      step="0.01"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="number_of_modules" class="required"
                      >Number of Modules</label
                    >
                    <input
                      type="number"
                      id="number_of_modules"
                      name="number_of_modules"
                      required
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="total_capacity" class="required"
                      >Total Capacity (KWP)</label
                    >
                    <input
                      type="number"
                      id="total_capacity"
                      name="total_capacity"
                      step="0.01"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="inverter_capacity" class="required"
                      >Inverter Capacity (KW)</label
                    >
                    <input
                      type="number"
                      id="inverter_capacity"
                      name="inverter_capacity"
                      step="0.01"
                      required
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="inverter_model" class="required"
                      >Inverter Model</label
                    >
                    <input
                      type="text"
                      id="inverter_model"
                      name="inverter_model"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="manufacturing_year" class="required"
                      >Year of Manufacturing</label
                    >
                    <input
                      type="number"
                      id="manufacturing_year"
                      name="manufacturing_year"
                      min="2000"
                      max="2030"
                      value="2024"
                      required
                    />
                  </div>
                </div>
              </div>
            </div>

            <div id="additional" class="tab-content">
              <div class="form-section">
                <div class="form-group">
                  <label for="vendor_name" class="required">Vendor Name</label>
                  <input
                    type="text"
                    id="vendor_name"
                    name="vendor_name"
                    required
                  />
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="mobile_number" class="required"
                      >Mobile Number</label
                    >
                    <input
                      type="tel"
                      id="mobile_number"
                      name="mobile_number"
                      value="9529750282"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      value="officeomsairament2017@gmail.com"
                      required
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="installation_date" class="required"
                    >Installation Date</label
                  >
                  <input
                    type="date"
                    id="installation_date"
                    name="installation_date"
                    required
                  />
                </div>

                <div class="form-group">
                  <label for="aadhar_number" class="required"
                    >Aadhar Number</label
                  >
                  <input
                    type="text"
                    id="aadhar_number"
                    name="aadhar_number"
                    required
                  />
                  <div class="form-hint">
                    Upload a self-attested copy in the final document
                  </div>
                </div>

                <div class="checkbox-group">
                  <input
                    type="checkbox"
                    id="terms_agreement"
                    name="terms_agreement"
                    required
                  />
                  <label for="terms_agreement" class="required"
                    >I certify that all information provided is accurate and
                    complete</label
                  >
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" id="prev-tab" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Previous
              </button>
              <button type="button" id="next-tab" class="btn">
                Next <i class="fas fa-arrow-right"></i>
              </button>
              <button type="button" id="generate-pdf" class="btn btn-accent">
                <i class="fas fa-file-pdf"></i> Generate PDF
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Full Screen PDF Preview Container -->
    <div
      id="fullscreen-pdf-container"
      class="fullscreen-pdf-preview"
      style="display: none"
    >
      <button class="close-fullscreen" id="close-fullscreen">
        <i class="fas fa-times"></i>
      </button>

      <div class="pdf-controls">
        <button type="button" id="fullscreen-print-btn" class="btn">
          <i class="fas fa-print"></i>
        </button>
        <button
          type="button"
          id="fullscreen-save-btn"
          class="btn btn-secondary"
        >
          <i class="fas fa-download"></i>
        </button>
      </div>

      <div id="fullscreen-pdf-content" class="pdf-optimized">
        <!-- Full screen PDF content will be inserted here -->
      </div>
    </div>
      
      </div>
    </main>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Tab functionality
        const tabs = document.querySelectorAll(".tab");
        const tabContents = document.querySelectorAll(".tab-content");
        const nextBtn = document.getElementById("next-tab");
        const prevBtn = document.getElementById("prev-tab");
        let currentTab = 0;

        // Initialize tabs
        function showTab(index) {
          tabs.forEach((tab) => tab.classList.remove("active"));
          tabContents.forEach((content) => content.classList.remove("active"));

          tabs[index].classList.add("active");
          tabContents[index].classList.add("active");

          // Update button states
          prevBtn.disabled = index === 0;
          nextBtn.style.display =
            index === tabs.length - 1 ? "none" : "inline-flex";

          if (index === tabs.length - 1) {
            document.getElementById("generate-pdf").style.display =
              "inline-flex";
          } else {
            document.getElementById("generate-pdf").style.display = "none";
          }

          currentTab = index;
        }

        // Tab click events
        tabs.forEach((tab, index) => {
          tab.addEventListener("click", () => showTab(index));
        });

        // Next button
        nextBtn.addEventListener("click", () => {
          if (currentTab < tabs.length - 1) {
            showTab(currentTab + 1);
          }
        });

        // Previous button
        prevBtn.addEventListener("click", () => {
          if (currentTab > 0) {
            showTab(currentTab - 1);
          }
        });

        // Initialize first tab
        showTab(0);

        // Generate PDF (directly opens in full screen)
        document
          .getElementById("generate-pdf")
          .addEventListener("click", function () {
            const form = document.getElementById("solar-report-form");

            // Validate form
            let isValid = true;
            const requiredFields = form.querySelectorAll("[required]");
            requiredFields.forEach((field) => {
              if (!field.value) {
                isValid = false;
                field.style.borderColor = "#e11d48";
              } else {
                field.style.borderColor = "";
              }
            });

            if (!isValid) {
              alert(
                "Please fill in all required fields before generating the PDF."
              );
              return;
            }

            // Update PDF content with form data
            updatePDFContent();

            // Show full screen PDF preview
            document.getElementById("fullscreen-pdf-container").style.display =
              "block";
            document.body.style.overflow = "hidden";

            // Update status bar
            document.getElementById("form-status").classList.remove("active");
            document.getElementById("preview-status").classList.add("active");
            document.getElementById("print-status").classList.add("active");
          });

        // Function to update PDF content with form data
        function updatePDFContent() {
          const formData = new FormData(
            document.getElementById("solar-report-form")
          );
          const fullscreenContent = document.getElementById(
            "fullscreen-pdf-content"
          );

          // Create optimized PDF content with ALL pages from original code
          let pdfHTML = `
                    <!-- Page 1 -->
                    <div class="pdf-page">
                        <div class="pdf-header">
                            <h1>Work Completion Report for Solar Power Plant</h1>
                            <div>Maharashtra State Electricity Distribution Co. Ltd.</div>
                        </div>
                        
                        <h2>System Details</h2>
                        <div class="table-responsive">
                            <table class="pdf-table">
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Component</th>
                                    <th>Observation</th>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>Name</td>
                                    <td>${
                                      document.getElementById("consumer_name")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Consumer number</td>
                                    <td>${
                                      document.getElementById("consumer_number")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Site/Location with Complete Address</td>
                                    <td>${
                                      document.getElementById("address").value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Category: Govt/Private Sector</td>
                                    <td>${
                                      document.getElementById("category").value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Sanction number</td>
                                    <td>${
                                      document.getElementById("sanction_number")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Sanctioned Capacity of solar PV system (KW) installed</td>
                                    <td>${
                                      document.getElementById(
                                        "sanctioned_capacity"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>Capacity of solar PV system (KW)</td>
                                    <td>${
                                      document.getElementById("system_capacity")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Specification of the Modules</td>
                                    <td>${
                                      document.getElementById(
                                        "wattage_per_module"
                                      ).value
                                    }W per module, ${
            document.getElementById("number_of_modules").value
          } modules</td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>Make of Module</td>
                                    <td>${
                                      document.getElementById("module_make")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>ALMM Model Number</td>
                                    <td>${
                                      document.getElementById("almm_model")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td>Wattage per module</td>
                                    <td>${
                                      document.getElementById(
                                        "wattage_per_module"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td>No. of Module</td>
                                    <td>${
                                      document.getElementById(
                                        "number_of_modules"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td>Total Capacity (KWP)</td>
                                    <td>${
                                      document.getElementById("total_capacity")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>14</td>
                                    <td>Warrantee Details (Product + Performance)</td>
                                    <td>25 YEAR</td>
                                </tr>
                                <tr>
                                    <td>15</td>
                                    <td>PCU</td>
                                    <td>PCU Details</td>
                                </tr>
                                <tr>
                                    <td>16</td>
                                    <td>Make & Model number of Inverter</td>
                                    <td>${
                                      document.getElementById("inverter_model")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>17</td>
                                    <td>Rating</td>
                                    <td>65-500 D.C.V</td>
                                </tr>
                                <tr>
                                    <td>18</td>
                                    <td>Type of charge controller/ MPPT</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>19</td>
                                    <td>Capacity of Inverter</td>
                                    <td>${
                                      document.getElementById(
                                        "inverter_capacity"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>20</td>
                                    <td>HPD</td>
                                    <td>YES</td>
                                </tr>
                                <tr>
                                    <td>21</td>
                                    <td>Year of manufacturing</td>
                                    <td>${
                                      document.getElementById(
                                        "manufacturing_year"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>22</td>
                                    <td>Earthling and Protections</td>
                                    <td>Earthling Details</td>
                                </tr>
                                <tr>
                                    <td>23</td>
                                    <td>No of Separate Earthings with earth Resistance</td>
                                    <td>3</td>
                                </tr>
                                <tr>
                                    <td>24</td>
                                    <td>It is certified that the Earth Resistance measure in presence of Licensed Electrical Contractor/Supervisor and found in order i.e. < 5 Ohms as per MNRE OM Dtd. 07.06.24 for CFA Component.</td>
                                    <td>Certified</td>
                                </tr>
                                <tr>
                                    <td>25</td>
                                    <td>Lightening Arrester (LA)</td>
                                    <td>0.5 Ohms</td>
                                </tr>
                                <tr>
                                    <td>26</td>
                                    <td>DC</td>
                                    <td>0.70 Ohms</td>
                                </tr>
                                <tr>
                                    <td>27</td>
                                    <td>AC</td>
                                    <td>0.9 Ohms</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="declaration">
                            <p>We ${
                              document.getElementById("vendor_name").value
                            } & ${
            document.getElementById("consumer_name").value
          } bearing Consumer Number ${
            document.getElementById("consumer_number").value
          } Ensured structural stability of installed solar power plant and obtained requisite permissions from the concerned authority. If in future, by virtue of any means due to collapsing or damage to installed solar power plant, MSEDCL will not be held responsible for any loss to property or human life, if any.</p>
                            <p>This is to Certified above Installed Solar PV System is working properly with electrical safety & Islanding switch in case of any presence of backup inverter an arrangement should be made in such way the backup inverter supply should never be synchronized with solar inverter to avoid any electrical accident due to back feeding. We will be held responsible for non-working of islanding mechanism and back feed to the de-energized grid.</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature [Vendor]</div>
                            </div>
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature [Consumer]</div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 2 -->
                    <div class="pdf-page">
                        <div class="pdf-header">
                            <h1>Guarantee Certificate & Identity Details</h1>
                            <div>Maharashtra State Electricity Distribution Co. Ltd.</div>
                        </div>
                        
                        <h2>Guarantee Certificate</h2>
                        <div class="declaration">
                            <p>The undersigned will provide the services to the consumers for repairs/maintenance of the RTS plant free of cost for 5 years of the comprehensive Maintenance Contract (CMC) period from the date of commissioning of the plant. Non performing/under-performing system component will be replaced/repaired free of cost in the CMC period.</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature [Vendor]</div>
                                <div>Stamp & Seal</div>
                            </div>
                        </div>
                        
                        <h2>Identity Details of Consumer</h2>
                        <table class="pdf-table">
                            <tr>
                                <td>Aadhar Number:</td>
                                <td>${
                                  document.getElementById("aadhar_number").value
                                }</td>
                            </tr>
                        </table>
                        
                        <div class="declaration">
                            <p>Upload Xerox of AADHAR CARD HERE</p>
                            <p>SHOULD BE SELF ATTESTED BY CONSUMER</p>
                        </div>
                    </div>

                    <!-- Page 3 -->
                    <div class="pdf-page">
                        <div class="pdf-header">
                            <h1>Renewable Energy Generating System</h1>
                            <div>Annexure-I (Commissioning Report for RE System)</div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="pdf-table">
                                <tr>
                                    <th>S No.</th>
                                    <th>Particulars</th>
                                    <th>As Commissioned</th>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>Name of the Consumer</td>
                                    <td>${
                                      document.getElementById("consumer_name")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>CONSUMER NO.</td>
                                    <td>${
                                      document.getElementById("consumer_number")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Mobile Number</td>
                                    <td>${
                                      document.getElementById("mobile_number")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>E-mail</td>
                                    <td>${
                                      document.getElementById("email").value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Address of INSTALLATION</td>
                                    <td>${
                                      document.getElementById("address").value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>RE Arrangement Type</td>
                                    <td>Net Metering Arrangement</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>RE Source</td>
                                    <td>SOLAR</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Sanctioned Capacity(KW)</td>
                                    <td>${
                                      document.getElementById(
                                        "sanctioned_capacity"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>Capacity Type</td>
                                    <td>ROOFTOP</td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>Project Model</td>
                                    <td>CAPEX</td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td>RE Installed Capacity(Rooftop)(KW)</td>
                                    <td>${
                                      document.getElementById("system_capacity")
                                        .value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td>RE Installed Capacity (Rooftop + Ground) (KW)</td>
                                    <td>NA</td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td>RE Installed Capacity(Ground)(KW)</td>
                                    <td>NA</td>
                                </tr>
                                <tr>
                                    <td>14</td>
                                    <td>Installation date</td>
                                    <td>${
                                      document.getElementById(
                                        "installation_date"
                                      ).value
                                    }</td>
                                </tr>
                                <tr>
                                    <td>15</td>
                                    <td>Solar PV Details</td>
                                    <td>
                                        <div>Inverter Capacity(KW): ${
                                          document.getElementById(
                                            "inverter_capacity"
                                          ).value
                                        } KW</div>
                                        <div>Inverter Make: ${
                                          document.getElementById(
                                            "inverter_model"
                                          ).value
                                        }</div>
                                        <div>No. of PV Modules: ${
                                          document.getElementById(
                                            "number_of_modules"
                                          ).value
                                        }</div>
                                        <div>Module Capacity (KW): ${
                                          document.getElementById(
                                            "total_capacity"
                                          ).value
                                        } KW</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <h2>Proforma-A</h2>
                        <h3>COMMISSIONING REPORT (PROVISIONAL) FOR GRID CONNECTED SOLAR PHOTOVOLTAIC POWER PLANT (with Net-metering facility)</h3>
                        
                        <div class="declaration">
                            <p>Certified that a Grid Connected SPV Power Plant of ${
                              document.getElementById("system_capacity").value
                            } KW p capacity has been installed at District NAGPUR of MAHARASHTRA which has been installed by M/S ${
            document.getElementById("vendor_name").value
          } on ${
            document.getElementById("installation_date").value
          }. The system is as per BIS/MNRE specifications. The system has been checked for its performance and found in order for further commissioning.</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature of the beneficiary</div>
                            </div>
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature of the agency with name, seal and date</div>
                            </div>
                        </div>
                        
                        <div class="declaration" style="margin-top: 20px;">
                            <p>The above RTS installation has been inspected by me for Pre-Commissioning Testing of Roof Top Solar Connection on dt ${new Date().toLocaleDateString()} as per guidelines issued by the office of The Chief Engineer vide letter no 21653 on dt. 18.08.2022 and found in order for commissioning.</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>Signature of the MSEDCL Officer</div>
                                <div>Name, Designation, Date and seal</div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 4 -->
                    <div class="pdf-page">
                        <div class="pdf-header">
                            <h1>Undertaking/Self-Declaration</h1>
                            <div>For Domestic Content Requirement fulfillment</div>
                        </div>
                        
                        <div class="declaration">
                            <p>1. This is to certify that M/S ${
                              document.getElementById("vendor_name").value
                            } has installed ${
            document.getElementById("system_capacity").value
          } KW [Capacity] Grid Connected Rooftop Solar Plant for ${
            document.getElementById("consumer_name").value
          } at ${
            document.getElementById("address").value
          } under application number ${
            document.getElementById("sanction_number").value
          } dated ${
            document.getElementById("installation_date").value
          } under MSEDCL.</p>
                            
                            <p>2. It is hereby undertaken that the PV modules installed for the above-mentioned project are domestically manufactured using domestic manufactured solar cells. The details of installed PV Modules are follows:</p>
                            
                            <div class="table-responsive">
                                <table class="pdf-table">
                                    <tr>
                                        <td>1. PV Module Capacity:</td>
                                        <td>${
                                          document.getElementById(
                                            "total_capacity"
                                          ).value
                                        } KW</td>
                                    </tr>
                                    <tr>
                                        <td>2. Number of PV Modules:</td>
                                        <td>${
                                          document.getElementById(
                                            "number_of_modules"
                                          ).value
                                        }</td>
                                    </tr>
                                    <tr>
                                        <td>3. Sr No of PV Module</td>
                                        <td>${
                                          document.getElementById("almm_model")
                                            .value
                                        }</td>
                                    </tr>
                                    <tr>
                                        <td>4. PV Module Make:</td>
                                        <td>${
                                          document.getElementById("module_make")
                                            .value
                                        }</td>
                                    </tr>
                                    <tr>
                                        <td>5. Cell manufacturer's name</td>
                                        <td>${
                                          document.getElementById("module_make")
                                            .value
                                        }</td>
                                    </tr>
                                    <tr>
                                        <td>6. Cell GST invoice No</td>
                                        <td>GST/${
                                          document.getElementById(
                                            "consumer_number"
                                          ).value
                                        }</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p>3. The above undertaking is based on the certificate issued by PV Module manufacturer/supplier while supplying the above mentioned order.</p>
                            
                            <p>4. I, Authorized Signatory on behalf of M/S ${
                              document.getElementById("vendor_name").value
                            } further declare that the information given above is true and correct and nothing has been concealed therein. If anything is found incorrect at any stage, then REC/ MNRE may take any appropriate action against my company for wrong declaration. Supporting documents and proof of the above information will be provided as and when requested by MNRE.</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div>(Signature With official Seal)</div>
                                <div>For M/S ${
                                  document.getElementById("vendor_name").value
                                }</div>
                                <div>Name: Authorized Signatory</div>
                                <div>Designation: Project Manager</div>
                                <div>Phone: ${
                                  document.getElementById("mobile_number").value
                                }</div>
                                <div>Email: ${
                                  document.getElementById("email").value
                                }</div>
                            </div>
                        </div>
                    </div>
                `;

          fullscreenContent.innerHTML = pdfHTML;
        }

        // Close full screen functionality
        document
          .getElementById("close-fullscreen")
          .addEventListener("click", function () {
            document.getElementById("fullscreen-pdf-container").style.display =
              "none";
            document.body.style.overflow = "auto";
          });

        // Full screen print functionality
        document
          .getElementById("fullscreen-print-btn")
          .addEventListener("click", function () {
            window.print();
          });

        // Full screen save functionality
        document
          .getElementById("fullscreen-save-btn")
          .addEventListener("click", function () {
            const element = document.getElementById("fullscreen-pdf-content");
            const consumerName =
              document.getElementById("consumer_name").value || "Solar_Report";

            const options = {
              margin: 5,
              filename: `${consumerName}_Solar_Report.pdf`,
              image: { type: "jpeg", quality: 0.98 },
              html2canvas: {
                scale: 2,
                useCORS: true,
                scrollX: 0,
                scrollY: 0,
                windowWidth: document.documentElement.offsetWidth,
                windowHeight: document.documentElement.offsetHeight,
              },
              jsPDF: {
                unit: "mm",
                format: "a4",
                orientation: "portrait",
              },
            };

            // Show loading message
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            html2pdf()
              .set(options)
              .from(element)
              .save()
              .finally(() => {
                // Restore button state
                this.innerHTML = '<i class="fas fa-download"></i>';
                this.disabled = false;
              });
          });

        // Back to top functionality
        document
          .querySelector(".back-to-top")
          .addEventListener("click", function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: "smooth" });
          });

        // Add some sample data for demo purposes
        function populateSampleData() {
          document.getElementById("consumer_name").value = "Rajesh Kumar";
          document.getElementById("consumer_number").value = "CN-7845-2024";
          document.getElementById("address").value =
            "123 Solar Avenue, Green Park, Nagpur, Maharashtra - 440001";
          document.getElementById("category").value = "PVT";
          document.getElementById("sanction_number").value = "SN-2024-5678";
          document.getElementById("sanctioned_capacity").value = "5.0";
          document.getElementById("system_capacity").value = "5.0";
          document.getElementById("module_make").value = "SolarTech Pro";
          document.getElementById("almm_model").value = "STP-350M";
          document.getElementById("wattage_per_module").value = "350";
          document.getElementById("number_of_modules").value = "15";
          document.getElementById("total_capacity").value = "5.25";
          document.getElementById("inverter_capacity").value = "5.0";
          document.getElementById("inverter_model").value = "SunPower 5KW";
          document.getElementById("vendor_name").value =
            "Green Energy Solutions";
          document.getElementById("installation_date").value = "2024-06-15";
          document.getElementById("aadhar_number").value = "XXXX-XXXX-7890";
          document.getElementById("terms_agreement").checked = true;
        }

        // Uncomment the line below to auto-populate form with sample data for demo
        populateSampleData();
      });
    </script>
</body>
</html>