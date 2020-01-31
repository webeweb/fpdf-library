<?php

require('fancyrow.php');

$pdf = new PDF_FancyRow();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->Write(12, 'Please fill in your name, company and email below:');
$pdf->Ln(20);
$widths  = [5, 40, 5, 40, 5, 40];
$border  = ['', 'LBR', '', 'LBR', '', 'LBR'];
$caption = ['', 'Name', '', 'Company', '', 'Email'];
$align   = ['', 'C', '', 'C', '', 'C'];
$style   = ['', 'I', '', 'I', '', 'I'];
$empty   = ['', '', '', '', '', ''];
$pdf->SetWidths($widths);
$pdf->FancyRow($empty, $border);
$pdf->FancyRow($caption, $empty, $align, $style);
$pdf->Output();
?>
