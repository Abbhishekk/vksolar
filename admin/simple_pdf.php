<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'vendor/autoload.php';
    
    $firstName = $_POST['firstName'] ?? 'Test';
    $lastName = $_POST['lastName'] ?? 'User';
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Solar Quotation</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { background: #4caf50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>VK Solar Energy</h1>
            <h2>Solar Quotation for ' . $firstName . ' ' . $lastName . '</h2>
        </div>
        <div class="content">
            <p>Customer: ' . $firstName . ' ' . $lastName . '</p>
            <p>System Size: ' . ($_POST['systemSize'] ?? '5') . ' kW</p>
            <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    exit;
}
echo 'Method not allowed';
?>