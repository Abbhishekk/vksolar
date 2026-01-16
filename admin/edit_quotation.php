<?php
// edit_quotation.php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'edit');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "connect/db.php"; // $conn from Database class

if (!isset($_GET['quote_id']) || !is_numeric($_GET['quote_id'])) {
    die("Invalid quotation ID.");
}

$quoteId = (int) $_GET['quote_id'];

$sql = "SELECT * FROM solar_rooftop_quotations WHERE quotation_id = $quoteId";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("Quotation not found.");
}

$row = $result->fetch_assoc();

// Split name into first/last roughly
$fullName  = $row['customer_name'];
$parts     = explode(' ', $fullName, 2);
$firstName = $parts[0] ?? '';
$lastName  = $parts[1] ?? '';

function checkedFlag($val) {
    return ($val ? 'checked' : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quotation - VK Solar</title>
    <?php require('include/head.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        /* EXACT same CSS as your form page (copy/paste from your code) */
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
        .form-group { margin-bottom: 20px; }
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
        .system-size-input input { flex: 1; }
        .system-size-input span {
            font-weight: 600;
            color: var(--vk-green);
        }
        .financial-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .financial-group { position: relative; }
        .financial-group input { padding-left: 40px; }
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
        .btn:active { transform: translateY(0); }
        @media (max-width: 768px) {
            .form-container { flex-direction: column; }
            .form-section { width: 100%; }
            .financial-inputs { grid-template-columns: 1fr; }
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--vk-text);
            font-size: 14px;
            opacity: 0.7;
        }
        .quantity-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-input input { flex: 1; }
        .quantity-input span {
            font-weight: 600;
            color: var(--vk-green);
            min-width: 50px;
        }
    </style>
</head>
<body>
<?php require('include/sidebar.php'); ?>

<div id="main-content">
    <?php require('include/navbar.php'); ?>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">VK</div>
                <h1>Edit Solar Quotation</h1>
            </div>
            <p>Update the details below and save the quotation</p>
        </header>
        
        <form method="POST" action="edit_quotation_api" class="form-container">
            <input type="hidden" name="quotation_id" value="<?php echo htmlspecialchars($quoteId); ?>">

            <div class="form-section">
                <h2>Customer Details</h2>
                
                <div class="form-group">
                    <label for="firstName" class="required">First Name</label>
                    <input type="text" id="firstName" name="firstName"
                           value="<?php echo htmlspecialchars($firstName); ?>"
                           placeholder="Enter first name" required>
                </div>
                
                <div class="form-group">
                    <label for="lastName" class="required">Last Name</label>
                    <input type="text" id="lastName" name="lastName"
                           value="<?php echo htmlspecialchars($lastName); ?>"
                           placeholder="Enter last name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($row['customer_email']); ?>"
                           placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?php echo htmlspecialchars($row['customer_phone']); ?>"
                           placeholder="Enter phone number" required>
                </div>
                
                <div class="form-group">
                    <label for="address" class="required">Address</label>
                    <input type="text" id="address" name="address"
                           value="<?php echo htmlspecialchars($row['customer_address']); ?>"
                           placeholder="Enter complete address" required>
                </div>
                
                <!-- Quotation Preparer Details -->
                <h2 style="margin-top: 30px;">Quotation Preparer Details</h2>
                
                <div class="mb-3">
                    <label class="form-label">Quotation Prepared By</label>
                    <input type="text" name="prepared_by" class="form-control"
                           value="<?php echo htmlspecialchars($row['prepared_by']); ?>"
                           placeholder="Enter name">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Address</label>
                    <input type="text" name="preparer_address" class="form-control"
                           value="<?php echo htmlspecialchars($row['preparer_address']); ?>"
                           placeholder="Enter address">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Contact</label>
                    <input type="text" name="preparer_contact" class="form-control"
                           value="<?php echo htmlspecialchars($row['preparer_contact']); ?>"
                           placeholder="Enter contact number">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Preparer Email</label>
                    <input type="email" name="preparer_email" class="form-control"
                           value="<?php echo htmlspecialchars($row['preparer_email']); ?>"
                           placeholder="Enter email">
                </div>
            </div>
            
            <div class="form-section">
                <h2>System Configuration</h2>
                
                <div class="form-group">
                    <label for="electricityBill" class="required">Average Monthly Electricity Bill (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="electricityBill" name="electricityBill"
                               value="<?php echo htmlspecialchars($row['monthly_bill']); ?>"
                               placeholder="5000" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="systemType" class="required">System Type</label>
                    <select id="systemType" name="systemType" required>
                        <option value="">Select System Type</option>
                        <option value="on-grid"  <?php echo ($row['system_type']=='on-grid'?'selected':''); ?>>On-Grid System</option>
                        <option value="off-grid" <?php echo ($row['system_type']=='off-grid'?'selected':''); ?>>Off-Grid System</option>
                        <option value="hybrid"   <?php echo ($row['system_type']=='hybrid'?'selected':''); ?>>Hybrid System</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="systemSize" class="required">System Size (kW)</label>
                    <div class="system-size-input">
                        <input type="number" id="systemSize" name="systemSize"
                               value="<?php echo htmlspecialchars($row['system_size_kwp']); ?>"
                               placeholder="Enter system size" min="1" max="100" step="0.5" required>
                        <span>kW</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="panelCompany" class="required">Panel Company</label>
                    <select id="panelCompany" name="panelCompany" required>
                        <option value="">Select Panel Company</option>
                        <?php
                        $panelCompanies = ['sunpower','lg','panasonic','canadian','jinko','vikram','waaree','adani'];
                        foreach ($panelCompanies as $pc) {
                            $sel = ($row['panel_company'] == $pc) ? 'selected' : '';
                            echo "<option value=\"$pc\" $sel>" . ucfirst($pc) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="panelModel" class="required">Panel Model (Watt)</label>
                    <div class="system-size-input">
                    <select id="panelModel" name="panelModel" required>
                        <option value="">Select Panel Model</option>
                        <?php
                        $panelModels = ["300","325","350","375","400","425","450","475","500","525","550","575","600"];
                        foreach ($panelModels as $m) {
                            $sel = ($row['panel_wattage'] == $m) ? 'selected' : '';
                            echo "<option value=\"$m\" $sel>$m</option>";
                        }
                        ?>
                    </select>
                     <span>W</span>
                     </div>
                </div>
                
                <div class="form-group">
                    <label for="panelQuantity" class="required">Panel Quantity</label>
                    <div class="quantity-input">
                        <input type="number" id="panelQuantity" name="panelQuantity"
                               value="<?php echo htmlspecialchars($row['panel_count']); ?>"
                               placeholder="Enter number of panels" min="1" max="1000" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="inverterType" class="required">Inverter Type</label>
                    <select id="inverterType" name="inverterType" required>
                        <option value="">Select Inverter Type</option>
                        <?php
                        $invTypes = [
                            'string-inverter'  => 'String Inverter',
                            'micro-inverter'   => 'Micro Inverter',
                            'central-inverter' => 'Central Inverter',
                            'hybrid-inverter'  => 'Hybrid Inverter',
                            'off-grid-inverter'=> 'Off-Grid Inverter'
                        ];
                        foreach ($invTypes as $val=>$label) {
                            $sel = ($row['inverter_type'] == $val) ? 'selected' : '';
                            echo "<option value=\"$val\" $sel>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="inverterCompany" class="required">Inverter Company</label>
                    <select id="inverterCompany" name="inverterCompany" required>
                        <option value="">Select Inverter Company</option>
                        <?php
                        $invCompanies = ['solar-edge','enphase','huawei','fronius','sma','delta','luminous','microtek','su-kam','exide'];
                        foreach ($invCompanies as $ic) {
                            $sel = ($row['inverter_company'] == $ic) ? 'selected' : '';
                            echo "<option value=\"$ic\" $sel>" . ucfirst($ic) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="inverterCapacity" class="required">Inverter Capacity (kVA)</label>
                    <div class="system-size-input">
                    <select id="inverterCapacity" name="inverterCapacity" required>
                        <option value="">Select Inverter Capacity</option>
                        <?php
                        $invCaps = ["1","2","3","5","7.5","10","15","20","25","30","50","100"];
                        foreach ($invCaps as $cap) {
                            $sel = ($row['inverter_capacity'] == $cap) ? 'selected' : '';
                            echo "<option value=\"$cap\" $sel>$cap</option>";
                        }
                        ?>
                    </select>
                    <span>kVA</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="inverterQuantity" class="required">Inverter Quantity</label>
                    <div class="quantity-input">
                        <input type="number" id="inverterQuantity" name="inverterQuantity"
                               value="<?php echo htmlspecialchars($row['inverter_count']); ?>"
                               placeholder="Enter number of inverters" min="1" max="50" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Financial Details</h2>
                
                <div class="form-group">
                    <label for="totalAmount" class="required">Total System Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="totalAmount" name="totalAmount"
                               value="<?php echo htmlspecialchars($row['total_cost']); ?>"
                               placeholder="Enter total amount" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subsidyAmount">Subsidy Amount (₹)</label>
                    <div class="financial-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="subsidyAmount" name="subsidyAmount"
                               value="<?php echo htmlspecialchars($row['subsidy']); ?>"
                               placeholder="Enter subsidy amount" min="0">
                    </div>
                    <small style="color: var(--vk-green); margin-top: 5px; display: block;">
                        Enter any government or utility subsidies you're eligible for
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="propertyType" class="required">Property Type</label>
                    <select id="propertyType" name="propertyType" required>
                        <option value="">Select property type</option>
                        <option value="residential" <?php echo ($row['property_type']=='residential'?'selected':''); ?>>Residential</option>
                        <option value="commercial"  <?php echo ($row['property_type']=='commercial'?'selected':''); ?>>Commercial</option>
                        <option value="industrial"  <?php echo ($row['property_type']=='industrial'?'selected':''); ?>>Industrial</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="meterType" class="required">Meter Type</label>
                    <select id="meterType" name="meterType" required>
                        <option value="">Select Meter Type</option>
                        <option value="single" <?php echo ($row['meter_type']=='single'?'selected':''); ?>>Single Phase</option>
                        <option value="three"  <?php echo ($row['meter_type']=='three'?'selected':''); ?>>Three Phase</option>
                        <option value="smart"  <?php echo ($row['meter_type']=='smart'?'selected':''); ?>>Smart Meter</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="roofType" class="required">Roof Type</label>
                    <select id="roofType" name="roofType" required>
                        <option value="">Select roof type</option>
                        <option value="tiled"   <?php echo ($row['roof_type']=='tiled'?'selected':''); ?>>Tiled Roof</option>
                        <option value="metal"   <?php echo ($row['roof_type']=='metal'?'selected':''); ?>>Metal Roof</option>
                        <option value="flat"    <?php echo ($row['roof_type']=='flat'?'selected':''); ?>>Flat Roof</option>
                        <option value="shingle" <?php echo ($row['roof_type']=='shingle'?'selected':''); ?>>Asphalt Shingle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Additional Components</label>
                    <div class="checkbox-group">
                        <div class="checkbox-option">
                           <input type="checkbox" id="battery_backup" name="battery_backup" value="1"
                            <?php echo ($row['battery_backup'] == 1) ? 'checked' : ''; ?>>
                            <label for="battery">Battery Backup System</label>
                        </div>
                        <div class="checkbox-option">
                           <input type="checkbox" id="smart_monitoring" name="smart_monitoring" value="1"
                            <?php echo ($row['smart_monitoring'] == 1) ? 'checked' : ''; ?>>
                            <label for="monitoring">Smart Monitoring System</label>
                        </div>
                        <div class="checkbox-option">
                           <input type="checkbox" id="annual_maintenance" name="annual_maintenance" value="1"
                            <?php echo ($row['annual_maintenance'] == 1) ? 'checked' : ''; ?>>
                            <label for="maintenance">Annual Maintenance Package</label>
                        </div>
                    </div>
                </div>
                
                <input type="submit" name="submit" class="btn" id="saveBtn" value="Update Quotation">
            </div>
        </form>
        
        <div class="footer">
            <p>© 2023 VK Solar. All rights reserved. | Sustainable Energy Solutions</p>
        </div>
    </div>
</div>

<script>
    // optional: same JS validation and auto panel calc as create form
    document.getElementById('saveBtn').addEventListener('click', function(e) {
        const requiredFields = [
            'firstName', 'lastName', 'phone', 'address',
            'electricityBill', 'systemType', 'systemSize', 'panelCompany',
            'propertyType', 'meterType', 'panelModel', 'panelQuantity',
            'totalAmount', 'inverterType', 'inverterCompany',
            'inverterCapacity', 'inverterQuantity'
        ];
        let isValid = true;
        requiredFields.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value) {
                isValid = false;
                el.style.borderColor = '#e74c3c';
            } else if (el) {
                el.style.borderColor = '';
            }
        });
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields marked with *');
        }
    });

    document.getElementById('systemSize').addEventListener('change', calculatePanelQuantity);
    document.getElementById('panelModel').addEventListener('change', calculatePanelQuantity);

    function calculatePanelQuantity() {
        const systemSize = parseFloat(document.getElementById('systemSize').value);
        const panelModel = document.getElementById('panelModel').value;
        if (systemSize && panelModel) {
            const panelWattage = parseInt(panelModel.replace('W', ''));
            if (panelWattage > 0) {
                const qty = Math.ceil((systemSize * 1000) / panelWattage);
                document.getElementById('panelQuantity').value = qty;
            }
        }
    }
</script>
</body>
</html>
