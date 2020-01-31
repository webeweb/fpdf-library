<?php

require('layer.php');

$pdf = new PDF_Layer();

// Define layers
$l1 = $pdf->AddLayer('Layer 1');
$l2 = $pdf->AddLayer('Layer 2');

// Open layer pane in PDF viewer
$pdf->OpenLayerPane();

$pdf->AddPage();
$pdf->SetFont('Arial', '', 15);
$pdf->Write(8, "This line doesn't belong to any layer.\n");

// First layer
$pdf->BeginLayer($l1);
$pdf->Write(8, "This line belongs to Layer 1.\n");
$pdf->EndLayer();

// Second layer
$pdf->BeginLayer($l2);
$pdf->Write(8, "This line belongs to Layer 2.\n");
$pdf->EndLayer();

$pdf->Output();
?>
