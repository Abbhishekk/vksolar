<?php
// process_quotation.php
session_start();
require_once 'connect/auth_middleware.php';
$auth->requireAuth();
$auth->requirePermission('quotation_management', 'create');

print_r($_POST);
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<title>Quotation Submission Results</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #2e8b57; text-align: center; }";
echo "h2 { color: #1e6b47; border-bottom: 2px solid #2e8b57; padding-bottom: 10px; }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".field { margin: 10px 0; padding: 8px; background: #f9f9f9; border-radius: 5px; }";
echo ".field strong { color: #2e8b57; }";
echo ".back-btn { display: inline-block; padding: 10px 20px; background: #2e8b57; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }";
echo ".back-btn:hover { background: #1e6b47; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>Quotation Submission Results</h1>";
echo "<p>All form data received and displayed below:</p>";

// Display POST data
echo "<div class='section'>";
echo "<h2>Customer Details</h2>";

echo "<div class='field'><strong>First Name:</strong> " . (isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Last Name:</strong> " . (isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Email:</strong> " . (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Phone:</strong> " . (isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Address:</strong> " . (isset($_POST['address']) ? htmlspecialchars($_POST['address']) : 'Not provided') . "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>Property Details</h2>";
echo "<div class='field'><strong>Property Type:</strong> " . (isset($_POST['propertyType']) ? htmlspecialchars($_POST['propertyType']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Meter Type:</strong> " . (isset($_POST['meterType']) ? htmlspecialchars($_POST['meterType']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Roof Type:</strong> " . (isset($_POST['roofType']) ? htmlspecialchars($_POST['roofType']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Roof Area:</strong> " . (isset($_POST['roofArea']) ? htmlspecialchars($_POST['roofArea']) : 'Not provided') . " sq. ft.</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>System Configuration</h2>";
echo "<div class='field'><strong>Monthly Bill:</strong> ₹" . (isset($_POST['monthlyBill']) ? htmlspecialchars($_POST['monthlyBill']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>System Type:</strong> " . (isset($_POST['systemType']) ? htmlspecialchars($_POST['systemType']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>System Size:</strong> " . (isset($_POST['systemSize']) ? htmlspecialchars($_POST['systemSize']) : 'Not provided') . " kW</div>";
echo "<div class='field'><strong>Panel Company:</strong> " . (isset($_POST['panelCompany']) ? htmlspecialchars($_POST['panelCompany']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Inverter Company:</strong> " . (isset($_POST['inverterCompany']) ? htmlspecialchars($_POST['inverterCompany']) : 'Not provided') . "</div>";
echo "<div class='field'><strong>Panel Model:</strong> " . (isset($_POST['panelModel']) ? htmlspecialchars($_POST['panelModel']) : 'Not provided') . " Wp</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>Additional Components</h2>";
echo "<div class='field'><strong>Battery Backup:</strong> " . (isset($_POST['batteryBackup']) ? 'Yes' : 'No') . "</div>";
echo "<div class='field'><strong>Monitoring System:</strong> " . (isset($_POST['monitoringSystem']) ? 'Yes' : 'No') . "</div>";
echo "<div class='field'><strong>Maintenance Package:</strong> " . (isset($_POST['maintenancePackage']) ? 'Yes' : 'No') . "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>Raw POST Data</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "</div>";

echo "<a href='quotation_generator.php' class='back-btn'>← Back to Quotation Generator</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>