<?php

require('fpdf_protection.php');

$pdf = new FPDF_Protection();
$pdf->SetProtection(['print']);
$pdf->AddPage();
$pdf->SetFont('Arial');
$pdf->Write(10, 'You can print me but not copy my text.');
$pdf->Output();
?>
