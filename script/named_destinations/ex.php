<?php

require_once('named_destinations.php');

$pdf = new PDF_NamedDestinations();

$pdf->AddPage();
$pdf->SetFont('Arial', '', 14);
$pdf->SetLink('#page-1');
$pdf->Write(10, 'Link to page 2', '#page-2');

$pdf->AddPage();
$pdf->SetLink('#page-2');
$pdf->Write(10, 'Link to page 1', '#page-1');

$pdf->Output();
?>
