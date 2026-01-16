<?php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'create');

// Agar clear parameter aaya hai toh session clear karo
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    unset($_SESSION['quotation_data']);
    unset($_SESSION['form_submitted']);
    header('Location: quotation_generator.php');
    exit;
}

// Check if we have previous form data in session
$previous_data = $_SESSION['quotation_data'] ?? null;
$form_submitted = $_SESSION['form_submitted'] ?? false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form data collect karna
    $quotation_data = [
        'customer_name' => $_POST['firstName'] . ' ' . $_POST['lastName'],
        'contact' => $_POST['phone'],
        'email' => $_POST['email'],
        'system_size' => $_POST['systemSize'],
        'panel_company' => $_POST['panelCompany'],
        'panel_model' => $_POST['panelModel'],
        'inverter_type' => $_POST['inverterType'],
        'inverter_company' => $_POST['inverterCompany'],
        'inverter_capacity' => $_POST['inverterCapacity'],
        'system_type' => $_POST['systemType'],
        'meter_type' => $_POST['meterType'],
        'current_monthly_bill' => $_POST['electricityBill'],
        'investment' => $_POST['totalAmount'],
        'subsidy_amount' => $_POST['subsidyAmount'],
        // Vendor details
        'vendor_name' => $_POST['vendorName'],
        'vendor_contact' => $_POST['vendorContact'],
        'vendor_email' => $_POST['vendorEmail'],
        'vendor_address' => $_POST['vendorAddress'],
        // Additional fields
        'property_type' => $_POST['propertyType'],
        'roof_type' => $_POST['roofType']
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
    $_SESSION['form_submitted'] = true;
    
    // Quotation page redirect karna
    header('Location: generatedcotation.php');
    exit;
}

// Extract name parts if we have previous data
$first_name = '';
$last_name = '';
if ($previous_data && isset($previous_data['customer_name']) && $form_submitted) {
    $name_parts = explode(' ', $previous_data['customer_name']);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';
} else {
    // Agar form submit nahi hua hai, toh empty form dikhao
    $previous_data = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VK Solar - Quote Generator</title>
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

        /* Clear form button */
        .clear-btn {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .clear-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
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

        .form-notice {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">VK</div>
                <h1>VK Solar Quote Generator</h1>
            </div>
            <p>Fill out the form below to get your personalized solar system quote</p>
            <?php if ($form_submitted && $previous_data): ?>
           
            <?php endif; ?>
        </header>
        
        <form method="POST" action="quotation_generator.php" class="form-container" id="quotationForm">
            <div class="form-section">
                <h2>Customer Details</h2>
                
                <div class="form-group">
                    <label for="firstName" class="required">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter first name" required 
                           value="<?php echo ($form_submitted && isset($previous_data['customer_name'])) ? htmlspecialchars($first_name) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="lastName" class="required">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter last name" required
                           value="<?php echo ($form_submitted && isset($previous_data['customer_name'])) ? htmlspecialchars($last_name) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address"
                           value="<?php echo ($form_submitted && isset($previous_data['email'])) ? htmlspecialchars($previous_data['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required
                           value="<?php echo ($form_submitted && isset($previous_data['contact'])) ? htmlspecialchars($previous_data['contact']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address" class="required">Address</label>
                    <input type="text" id="address" name="address" placeholder="Enter complete address" required>
                </div>
                
                <!-- Vendor Details Section -->
                <h2 style="margin-top: 30px;">Vendor Details</h2>
                
                <div class="form-group">
                    <label for="vendorName">Vendor Name</label>
                    <input type="text" id="vendorName" name="vendorName" placeholder="Enter vendor name"
                           value="<?php echo ($form_submitted && isset($previous_data['vendor_name'])) ? htmlspecialchars($previous_data['vendor_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="vendorContact">Vendor Contact</label>
                    <input type="tel" id="vendorContact" name="vendorContact" placeholder="Enter vendor contact number"
                           value="<?php echo ($form_submitted && isset($previous_data['vendor_contact'])) ? htmlspecialchars($previous_data['vendor_contact']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="vendorEmail">Vendor Email</label>
                    <input type="email" id="vendorEmail" name="vendorEmail" placeholder="Enter vendor email"
                           value="<?php echo ($form_submitted && isset($previous_data['vendor_email'])) ? htmlspecialchars($previous_data['vendor_email']) : ''; ?>">
                </div>
                
                
            </div>
            
            <div class="form-section">
                <h2>System Configuration</h2>
                
                <div class="form-group">
                    <label for="electricityBill" class="required">Average Monthly Electricity Bill (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="electricityBill" name="electricityBill" placeholder="5000" min="0" required
                               value="<?php echo ($form_submitted && isset($previous_data['current_monthly_bill'])) ? htmlspecialchars($previous_data['current_monthly_bill']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="systemType" class="required">System Type</label>
                    <select id="systemType" name="systemType" required>
                        <option value="">Select System Type</option>
                        <option value="on-grid" <?php echo ($form_submitted && ($previous_data['system_type'] ?? '') === 'on-grid') ? 'selected' : ''; ?>>On-Grid System</option>
                        <option value="off-grid" <?php echo ($form_submitted && ($previous_data['system_type'] ?? '') === 'off-grid') ? 'selected' : ''; ?>>Off-Grid System</option>
                        <option value="hybrid" <?php echo ($form_submitted && ($previous_data['system_type'] ?? '') === 'hybrid') ? 'selected' : ''; ?>>Hybrid System</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="systemSize" class="required">System Size (kW)</label>
                    <div class="system-size-input">
                        <input type="number" id="systemSize" name="systemSize" placeholder="Enter system size" min="1" max="100" step="0.5" required
                               value="<?php echo ($form_submitted && isset($previous_data['system_size'])) ? htmlspecialchars($previous_data['system_size']) : ''; ?>">
                        <span>kW</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="panelCompany" class="required">Panel Company</label>
                    <select id="panelCompany" name="panelCompany" required>
                        <option value="">Select Panel Company</option>
                        <option value="sunpower" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'sunpower') ? 'selected' : ''; ?>>SunPower</option>
                        <option value="lg" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'lg') ? 'selected' : ''; ?>>LG</option>
                        <option value="panasonic" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'panasonic') ? 'selected' : ''; ?>>Panasonic</option>
                        <option value="canadian" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'canadian') ? 'selected' : ''; ?>>Canadian Solar</option>
                        <option value="jinko" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'jinko') ? 'selected' : ''; ?>>Jinko Solar</option>
                        <option value="vikram" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'vikram') ? 'selected' : ''; ?>>Vikram Solar</option>
                        <option value="waaree" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'waaree') ? 'selected' : ''; ?>>Waaree</option>
                        <option value="adani" <?php echo ($form_submitted && ($previous_data['panel_company'] ?? '') === 'adani') ? 'selected' : ''; ?>>Adani Solar</option>
                    </select>
                </div>
                
                <!-- NEW: Panel Model Selection -->
                <div class="form-group">
                    <label for="panelModel" class="required">Panel Model (Watt)</label>
                    <select id="panelModel" name="panelModel" required>
                        <option value="">Select Panel Model</option>
                        <option value="300W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '300W') ? 'selected' : ''; ?>>300W</option>
                        <option value="325W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '325W') ? 'selected' : ''; ?>>325W</option>
                        <option value="350W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '350W') ? 'selected' : ''; ?>>350W</option>
                        <option value="375W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '375W') ? 'selected' : ''; ?>>375W</option>
                        <option value="400W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '400W') ? 'selected' : ''; ?>>400W</option>
                        <option value="425W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '425W') ? 'selected' : ''; ?>>425W</option>
                        <option value="450W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '450W') ? 'selected' : ''; ?>>450W</option>
                        <option value="475W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '475W') ? 'selected' : ''; ?>>475W</option>
                        <option value="500W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '500W') ? 'selected' : ''; ?>>500W</option>
                        <option value="525W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '525W') ? 'selected' : ''; ?>>525W</option>
                        <option value="550W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '550W') ? 'selected' : ''; ?>>550W</option>
                        <option value="575W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '575W') ? 'selected' : ''; ?>>575W</option>
                        <option value="600W" <?php echo ($form_submitted && ($previous_data['panel_model'] ?? '') === '600W') ? 'selected' : ''; ?>>600W</option>
                    </select>
                </div>
                
                <!-- NEW: Inverter Type Selection -->
                <div class="form-group">
                    <label for="inverterType" class="required">Inverter Type</label>
                    <select id="inverterType" name="inverterType" required>
                        <option value="">Select Inverter Type</option>
                        <option value="string-inverter" <?php echo ($form_submitted && ($previous_data['inverter_type'] ?? '') === 'string-inverter') ? 'selected' : ''; ?>>String Inverter</option>
                        <option value="micro-inverter" <?php echo ($form_submitted && ($previous_data['inverter_type'] ?? '') === 'micro-inverter') ? 'selected' : ''; ?>>Micro Inverter</option>
                        <option value="central-inverter" <?php echo ($form_submitted && ($previous_data['inverter_type'] ?? '') === 'central-inverter') ? 'selected' : ''; ?>>Central Inverter</option>
                        <option value="hybrid-inverter" <?php echo ($form_submitted && ($previous_data['inverter_type'] ?? '') === 'hybrid-inverter') ? 'selected' : ''; ?>>Hybrid Inverter</option>
                        <option value="off-grid-inverter" <?php echo ($form_submitted && ($previous_data['inverter_type'] ?? '') === 'off-grid-inverter') ? 'selected' : ''; ?>>Off-Grid Inverter</option>
                    </select>
                </div>
                
                <!-- NEW: Inverter Company Selection -->
                <div class="form-group">
                    <label for="inverterCompany" class="required">Inverter Company</label>
                    <select id="inverterCompany" name="inverterCompany" required>
                        <option value="">Select Inverter Company</option>
                        <option value="solar-edge" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'solar-edge') ? 'selected' : ''; ?>>SolarEdge</option>
                        <option value="enphase" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'enphase') ? 'selected' : ''; ?>>Enphase</option>
                        <option value="huawei" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'huawei') ? 'selected' : ''; ?>>Huawei</option>
                        <option value="fronius" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'fronius') ? 'selected' : ''; ?>>Fronius</option>
                        <option value="sma" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'sma') ? 'selected' : ''; ?>>SMA</option>
                        <option value="delta" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'delta') ? 'selected' : ''; ?>>Delta</option>
                        <option value="luminous" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'luminous') ? 'selected' : ''; ?>>Luminous</option>
                        <option value="microtek" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'microtek') ? 'selected' : ''; ?>>Microtek</option>
                        <option value="su-kam" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'su-kam') ? 'selected' : ''; ?>>Su-Kam</option>
                        <option value="exide" <?php echo ($form_submitted && ($previous_data['inverter_company'] ?? '') === 'exide') ? 'selected' : ''; ?>>Exide</option>
                    </select>
                </div>
                
                <!-- NEW: Inverter Capacity -->
                <div class="form-group">
                    <label for="inverterCapacity" class="required">Inverter Capacity (kVA)</label>
                    <select id="inverterCapacity" name="inverterCapacity" required>
                        <option value="">Select Inverter Capacity</option>
                        <option value="1kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '1kVA') ? 'selected' : ''; ?>>1 kVA</option>
                        <option value="2kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '2kVA') ? 'selected' : ''; ?>>2 kVA</option>
                        <option value="3kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '3kVA') ? 'selected' : ''; ?>>3 kVA</option>
                        <option value="5kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '5kVA') ? 'selected' : ''; ?>>5 kVA</option>
                        <option value="7.5kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '7.5kVA') ? 'selected' : ''; ?>>7.5 kVA</option>
                        <option value="10kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '10kVA') ? 'selected' : ''; ?>>10 kVA</option>
                        <option value="15kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '15kVA') ? 'selected' : ''; ?>>15 kVA</option>
                        <option value="20kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '20kVA') ? 'selected' : ''; ?>>20 kVA</option>
                        <option value="25kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '25kVA') ? 'selected' : ''; ?>>25 kVA</option>
                        <option value="30kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '30kVA') ? 'selected' : ''; ?>>30 kVA</option>
                        <option value="50kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '50kVA') ? 'selected' : ''; ?>>50 kVA</option>
                        <option value="100kVA" <?php echo ($form_submitted && ($previous_data['inverter_capacity'] ?? '') === '100kVA') ? 'selected' : ''; ?>>100 kVA</option>
                    </select>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Financial Details</h2>
                
                <div class="form-group">
                    <label for="totalAmount" class="required">Total System Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="totalAmount" name="totalAmount" placeholder="Enter total amount" min="0" required
                               value="<?php echo ($form_submitted && isset($previous_data['investment'])) ? htmlspecialchars($previous_data['investment']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subsidyAmount">Subsidy Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="subsidyAmount" name="subsidyAmount" placeholder="Enter subsidy amount" min="0"
                               value="<?php echo ($form_submitted && isset($previous_data['subsidy_amount'])) ? htmlspecialchars($previous_data['subsidy_amount']) : ''; ?>">
                    </div>
                    <small style="color: var(--vk-green); margin-top: 5px; display: block;">
                        Enter any government or utility subsidies you're eligible for
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="propertyType" class="required">Property Type</label>
                    <select id="propertyType" name="propertyType" required>
                        <option value="">Select property type</option>
                        <option value="residential" <?php echo ($form_submitted && ($previous_data['property_type'] ?? '') === 'residential') ? 'selected' : ''; ?>>Residential</option>
                        <option value="commercial" <?php echo ($form_submitted && ($previous_data['property_type'] ?? '') === 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                        <option value="industrial" <?php echo ($form_submitted && ($previous_data['property_type'] ?? '') === 'industrial') ? 'selected' : ''; ?>>Industrial</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="meterType" class="required">Meter Type</label>
                    <select id="meterType" name="meterType" required>
                        <option value="">Select Meter Type</option>
                        <option value="single" <?php echo ($form_submitted && ($previous_data['meter_type'] ?? '') === 'single') ? 'selected' : ''; ?>>Single Phase</option>
                        <option value="three" <?php echo ($form_submitted && ($previous_data['meter_type'] ?? '') === 'three') ? 'selected' : ''; ?>>Three Phase</option>
                        <option value="smart" <?php echo ($form_submitted && ($previous_data['meter_type'] ?? '') === 'smart') ? 'selected' : ''; ?>>Smart Meter</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="roofType" class="required">Roof Type</label>
                    <select id="roofType" name="roofType" required>
                        <option value="">Select roof type</option>
                        <option value="tiled" <?php echo ($form_submitted && ($previous_data['roof_type'] ?? '') === 'tiled') ? 'selected' : ''; ?>>Tiled Roof</option>
                        <option value="metal" <?php echo ($form_submitted && ($previous_data['roof_type'] ?? '') === 'metal') ? 'selected' : ''; ?>>Metal Roof</option>
                        <option value="flat" <?php echo ($form_submitted && ($previous_data['roof_type'] ?? '') === 'flat') ? 'selected' : ''; ?>>Flat Roof</option>
                        <option value="shingle" <?php echo ($form_submitted && ($previous_data['roof_type'] ?? '') === 'shingle') ? 'selected' : ''; ?>>Asphalt Shingle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Additional Components</label>
                    <div class="checkbox-group">
                        <div class="checkbox-option">
                            <input type="checkbox" id="battery" name="battery" value="battery">
                            <label for="battery">Battery Backup System</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="monitoring" name="monitoring" value="monitoring">
                            <label for="monitoring">Smart Monitoring System</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="maintenance" name="maintenance" value="maintenance">
                            <label for="maintenance">Annual Maintenance Package</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="calculateBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                    Generate Quotation
                </button>

                <?php if ($form_submitted && $previous_data): ?>
                <button type="button" class="clear-btn" onclick="clearForm()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                    </svg>
                    Clear Form & Start Fresh
                </button>
                <?php endif; ?>
            </div>
        </form>
        
        <div class="footer">
            <p>© 2023 VK Solar. All rights reserved. | Sustainable Energy Solutions</p>
        </div>
    </div>

    <script>
        document.getElementById('calculateBtn').addEventListener('click', function(e) {
            // Form validation
            const requiredFields = [
                'firstName', 'lastName', 'phone', 'address', 
                'electricityBill', 'systemType', 'systemSize', 'panelCompany',
                'propertyType', 'meterType', 'panelModel', 'totalAmount',
                'inverterType', 'inverterCompany', 'inverterCapacity'
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

        function clearForm() {
            if (confirm('Are you sure you want to clear all form data and start fresh?')) {
                // Clear all form fields
                document.getElementById('quotationForm').reset();
                
                // Clear session data by redirecting to same page without session
                window.location.href = 'quotation_generator.php?clear=1';
            }
        }

        // Check if we should clear session
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('clear') === '1') {
            // Session will be cleared on server side
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>