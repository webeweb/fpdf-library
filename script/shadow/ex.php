<?php

require('shadow.php');

$pdf = new PDF_Shadow();
$pdf->SetFont('Arial', '', 30);

$pdf->AddPage();
$pdf->SetTextColor(255, 255, 255);
for ($i = 1; $i < 6; $i++) {
    $Text = sprintf('Gray shadow with %.1F distance', $i / 2);
    $pdf->ShadowCell(0, 40, $Text, 1, 1, 'C', true, '', 'G', $i / 2);
    $pdf->Ln(10);
}

$pdf->AddPage();
$pdf->SetTextColor(0, 0, 255);
for ($i = 1; $i < 6; $i++) {
    $Text = sprintf('Black shadow with %.1F distance', $i / 2);
    $pdf->ShadowCell(0, 40, $Text, 1, 1, 'C', false, '', 'B', $i / 2);
    $pdf->Ln(10);
}

$pdf->Output();
?>
