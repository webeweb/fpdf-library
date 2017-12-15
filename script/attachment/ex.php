<?php
require('attachment.php');

$pdf = new PDF_Attachment();
$pdf->Attach('attached.txt');
$pdf->OpenAttachmentPane();
$pdf->AddPage();
$pdf->SetFont('Arial','',14);
$pdf->Write(5,'This PDF contains an attached file.');
$pdf->Output();
?>
