<?php
// Simple test to check if DomPDF is working
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

$html = '<html><body><h1>Test PDF</h1><p>This is a test PDF generation.</p></body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('test.pdf', array('Attachment' => true));
?>