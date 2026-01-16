<?php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'create');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form data collect karna
    $quotation_data = [
        'customer_name' => $_POST['firstName'] . ' ' . $_POST['lastName'],
        'contact' => $_POST['phone'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'system_size' => $_POST['systemSize'],
        'panel_company' => $_POST['panelCompany'],
        'panel_model' => $_POST['panelModel'],
        'panel_quantity' => $_POST['panelQuantity'],
        'inverter_type' => $_POST['inverterType'],
        'inverter_company' => $_POST['inverterCompany'],
        'inverter_capacity' => $_POST['inverterCapacity'],
        'inverter_quantity' => $_POST['inverterQuantity'],
        'system_type' => $_POST['systemType'],
        'meter_type' => $_POST['meterType'],
        'current_monthly_bill' => $_POST['electricityBill'],
        'investment' => $_POST['totalAmount'],
        'subsidy_amount' => $_POST['subsidyAmount'],
        'property_type' => $_POST['propertyType'],
        'roof_type' => $_POST['roofType'],
        // Vendor details
        'vendor_name' => $_POST['vendorName'],
        'vendor_contact' => $_POST['vendorContact'],
        'vendor_email' => $_POST['vendorEmail'],
        
        // Additional components
        'battery' => isset($_POST['battery']) ? 'Yes' : 'No',
        'monitoring' => isset($_POST['monitoring']) ? 'Yes' : 'No',
        'maintenance' => isset($_POST['maintenance']) ? 'Yes' : 'No'
    ];
    
    // Additional calculations
    $estimated_monthly_savings = $quotation_data['current_monthly_bill'] * 0.75;
    $estimated_yearly_savings = $estimated_monthly_savings * 12;
    
    // Payback period calculation
    $payback_period = $quotation_data['investment'] / $estimated_yearly_savings;
    
    $savings_25_years = $estimated_yearly_savings * 25;
    
    // Add calculated values
    $quotation_data['estimated_monthly_savings'] = $estimated_monthly_savings;
    $quotation_data['estimated_yearly_savings'] = $estimated_yearly_savings;
    $quotation_data['payback_period'] = round($payback_period, 1);
    $quotation_data['savings_25_years'] = $savings_25_years;
    
    // Session mein store karna
    $_SESSION['quotation_data'] = $quotation_data;

    // JavaScript redirect use karna (100% working)
    echo '<script type="text/javascript">
        window.location.href = "merged_template";
    </script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VK Solar - Quote Generator</title>
      <?php require('include/head.php'); ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Animate.css for animations -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Your existing CSS styles */
        :root {
            --vk-green: #00a651;
            --vk-green-dark: #008c45;
            --vk-green-light: #6bc9a5;
            --vk-gray: #f5f5f5;
            --vk-gray-dark: #e0e0e0;
            --vk-text: #333333;
            --vk-white: #ffffff;
            --vk-shadow: rgba(0, 166, 81, 0.2);
            --vk-orange: #ff9800;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--vk-gray);
            color: var(--vk-text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background-color: var(--vk-white);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--vk-green), var(--vk-green-light));
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background-color: var(--vk-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        header h1 {
            color: var(--vk-green);
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }
        
        .form-section {
            flex: 1;
            min-width: 300px;
            background-color: var(--vk-white);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .form-section:hover {
            transform: translateY(-5px);
        }
        
        .form-section h2 {
            color: var(--vk-green);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--vk-gray-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2::before {
            content: '';
            width: 8px;
            height: 25px;
            background-color: var(--vk-green);
            border-radius: 4px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--vk-text);
        }
        
        .required::after {
            content: ' *';
            color: #e74c3c;
        }
        
        input, select {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--vk-gray-dark);
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--vk-green);
            box-shadow: 0 0 0 3px var(--vk-shadow);
        }
        
        .system-size-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .system-size-input input {
            flex: 1;
        }
        
        .system-size-input span {
            font-weight: 600;
            color: var(--vk-green);
        }
        
        .financial-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .financial-group {
            position: relative;
        }
        
        .financial-group input {
            padding-left: 40px;
        }
        
        .currency-symbol {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--vk-green);
            font-weight: 600;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .checkbox-option:hover {
            background-color: rgba(0, 166, 81, 0.05);
        }
        
        .checkbox-option input {
            width: 20px;
            height: 20px;
            accent-color: var(--vk-green);
        }
        
        .btn {
            background: linear-gradient(to right, var(--vk-green), var(--vk-green-dark));
            color: white;
            border: none;
            padding: 16px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px var(--vk-shadow);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
            }
            
            .form-section {
                width: 100%;
            }
            
            .financial-inputs {
                grid-template-columns: 1fr;
            }
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--vk-text);
            font-size: 14px;
            opacity: 0.7;
        }
        
        /* NEW: Quantity input styling */
        .quantity-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-input input {
            flex: 1;
        }
        
        .quantity-input span {
            font-weight: 600;
            color: var(--vk-green);
            min-width: 50px;
        }
    </style>
</head>
<body>
      <!-- Sidebar -->
  <?php require('include/sidebar.php') ?>

  <!-- Main Content -->
  <div id="main-content">
 

    <!-- Fixed Header -->
    <?php require('include/navbar.php') ?>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">VK</div>
                <h1>VK Solar Quote Generator</h1>
            </div>
            <p>Fill out the form below to get your personalized solar system quote</p>
        </header>
        
        <form method="POST" action="save_quotation_api" class="form-container">
            <div class="form-section">
                <h2>Customer Details</h2>
                
                <div class="form-group">
                    <label for="firstName" class="required">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter first name" required>
                </div>
                
                <div class="form-group">
                    <label for="lastName" class="required">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter last name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
                </div>
                
                <div class="form-group">
                    <label for="address" class="required">Address</label>
                    <input type="text" id="address" name="address" placeholder="Enter complete address" required>
                </div>
                
                <!-- Vendor Details Section -->
                <h2 style="margin-top: 30px;">Quotation Preparer Details</h2>
                
                <div class="mb-3">
                    <label class="form-label">Quotation Prepared By</label>
                    <input type="text" name="prepared_by" class="form-control" placeholder="Enter name">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Address</label>
                    <input type="text" name="preparer_address" class="form-control" placeholder="Enter address">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Contact</label>
                    <input type="text" name="preparer_contact" class="form-control" placeholder="Enter contact number">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Email</label>
                    <input type="email" name="preparer_email" class="form-control" placeholder="Enter email">
                </div>

                
               
            </div>
            
            <div class="form-section">
                <h2>System Configuration</h2>
                
                <div class="form-group">
                    <label for="electricityBill" class="required">Average Monthly Electricity Bill (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="electricityBill" name="electricityBill" placeholder="5000" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="systemType" class="required">System Type</label>
                    <select id="systemType" name="systemType" required>
                        <option value="">Select System Type</option>
                        <option value="on-grid">On-Grid System</option>
                        <option value="off-grid">Off-Grid System</option>
                        <option value="hybrid">Hybrid System</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="systemSize" class="required">System Size (kW)</label>
                    <div class="system-size-input">
                        <input type="number" id="systemSize" name="systemSize" placeholder="Enter system size" min="1"  step="0.5" required>
                        <span>kW</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="panelCompany" class="required">Panel Company</label>
                    <input 
                        type="text"
                        id="panelCompany"
                        name="panelCompany"
                        class="form-control"
                        placeholder="ENTER PANEL COMPANY"
                        required
                        style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();"
                    >
                </div>

                
                <!-- Panel Model Selection -->
                <div class="form-group">
                    <label for="panelModel" class="required">Panel Model (Watt)</label>
                    <input
                        type="text"
                        id="panelModel"
                        name="panelModel"
                        class="form-control"
                        placeholder="ENTER WATT (e.g. 550)"
                        required
                        inputmode="numeric"
                        oninput="
                            let v = this.value.replace(/[^0-9]/g,'');
                            this.value = v ? v + 'W' : '';
                        "
                    >
                </div>

                
                <!-- NEW: Panel Quantity -->
                <div class="form-group">
                    <label for="panelQuantity" class="required">Panel Quantity</label>
                    <div class="quantity-input">
                        <input type="number" id="panelQuantity" name="panelQuantity" placeholder="Enter number of panels" min="1" max="1000" required>
                        
                    </div>
                    
                </div>
                
                <!-- Inverter Type Selection -->
                <div class="form-group">
                    <label for="inverterType" class="required">Inverter Type</label>
                    <select id="inverterType" name="inverterType" required>
                        <option value="">Select Inverter Type</option>
                        <option value="string-inverter">String Inverter</option>
                        <option value="micro-inverter">Micro Inverter</option>
                        <option value="central-inverter">Central Inverter</option>
                        <option value="hybrid-inverter">Hybrid Inverter</option>
                        <option value="off-grid-inverter">Off-Grid Inverter</option>
                    </select>
                </div>
                
                <!-- Inverter Company Selection -->
                <div class="form-group">
                    <label for="inverterCompany" class="required">Inverter Company</label>
                    <input 
                        type="text"
                        id="inverterCompany"
                        name="inverterCompany"
                        class="form-control"
                        placeholder="ENTER INVERTER COMPANY"
                        required
                        style="text-transform: uppercase;"
                        oninput="this.value = this.value.replace(/[^A-Za-z\- ]/g,'').toUpperCase();"
                    >

                </div>
                
                <!-- Inverter Capacity -->
                <div class="form-group">
                    <label for="inverterCapacity" class="required">Inverter Capacity (kVA)</label>
                    <select id="inverterCapacity" name="inverterCapacity" required>
                        <option value="">Select Inverter Capacity</option>
                        <option value="1">1 kVA</option>
                        <option value="2">2 kVA</option>
                        <option value="3">3 kVA</option>
                        <option value="5">5 kVA</option>
                        <option value="7">7.5 kVA</option>
                        <option value="10">10 kVA</option>
                        <option value="15">15 kVA</option>
                        <option value="20">20 kVA</option>
                        <option value="25">25 kVA</option>
                        <option value="30">30 kVA</option>
                        <option value="50">50 kVA</option>
                        <option value="100">100 kVA</option>
                    </select>
                </div>
                
                <!-- NEW: Inverter Quantity -->
                <div class="form-group">
                    <label for="inverterQuantity" class="required">Inverter Quantity</label>
                    <div class="quantity-input">
                        <input type="number" id="inverterQuantity" name="inverterQuantity" placeholder="Enter number of inverters" min="1" max="50" required>
                        
                    </div>
                    
                </div>
            </div>
            
            <div class="form-section">
                <h2>Financial Details</h2>
                
                <div class="form-group">
                    <label for="totalAmount" class="required">Total System Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="totalAmount" name="totalAmount" placeholder="Enter total amount" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subsidyAmount">Subsidy Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="subsidyAmount" name="subsidyAmount" placeholder="Enter subsidy amount" min="0">
                    </div>
                    <small style="color: var(--vk-green); margin-top: 5px; display: block;">
                        Enter any government or utility subsidies you're eligible for
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="propertyType" class="required">Property Type</label>
                    <select id="propertyType" name="propertyType" required>
                        <option value="">Select property type</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="industrial">Industrial</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="meterType" class="required">Meter Type</label>
                    <select id="meterType" name="meterType" required>
                        <option value="">Select Meter Type</option>
                        <option value="single">Single Phase</option>
                        <option value="three">Three Phase</option>
                        <option value="smart">Smart Meter</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="roofType" class="required">Roof Type</label>
                    <select id="roofType" name="roofType" required>
                        <option value="">Select roof type</option>
                        <option value="tiled">Tiled Roof</option>
                        <option value="metal">Metal Roof</option>
                        <option value="flat">Flat Roof</option>
                        <option value="shingle">Asphalt Shingle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Additional Components</label>
                    <div class="checkbox-group">
                        <div class="checkbox-option">
                           <input type="checkbox" id="battery_backup" name="battery_backup" value="1">
                            <label for="battery">Battery Backup System</label>
                        </div>
                        <div class="checkbox-option">
                           <input type="checkbox" id="smart_monitoring" name="smart_monitoring" value="1">
                            <label for="monitoring">Smart Monitoring System</label>
                        </div>
                        <div class="checkbox-option">
                           <input type="checkbox" id="annual_maintenance" name="annual_maintenance" value="1">
                            <label for="maintenance">Annual Maintenance Package</label>
                        </div>
                    </div>
                </div>
                
                <input type="submit" name="submit" class="btn" id="calculateBtn" value="save Quotation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                
       
            </div>
        </form>
        
        <div class="footer">
            <p>© 2023 VK Solar. All rights reserved. | Sustainable Energy Solutions</p>
        </div>
    </div>
</div>
    <script>
        document.getElementById('calculateBtn').addEventListener('click', function(e) {
            // Form validation
            const requiredFields = [
                'firstName', 'lastName', 'phone', 'address', 
                'electricityBill', 'systemType', 'systemSize', 'panelCompany',
                'propertyType', 'meterType', 'panelModel', 'panelQuantity', // UPDATED: Added panelQuantity
                'totalAmount', 'inverterType', 'inverterCompany', 'inverterCapacity',
                'inverterQuantity' // UPDATED: Added inverterQuantity
            ];
            
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields marked with *');
                return;
            }
        });

        // NEW: Auto-calculate panel quantity based on system size and panel model
        document.getElementById('systemSize').addEventListener('change', calculatePanelQuantity);
        document.getElementById('panelModel').addEventListener('change', calculatePanelQuantity);

        function calculatePanelQuantity() {
            const systemSize = parseFloat(document.getElementById('systemSize').value);
            const panelModel = document.getElementById('panelModel').value;
            
            if (systemSize && panelModel) {
                // Extract wattage from panel model (e.g., "400W" -> 400)
                const panelWattage = parseInt(panelModel.replace('W', ''));
                
                if (panelWattage > 0) {
                    // Calculate number of panels needed
                    const calculatedQuantity = Math.ceil((systemSize * 1000) / panelWattage);
                    document.getElementById('panelQuantity').value = calculatedQuantity;
                }
            }
        }
    </script>
</body>
</html>