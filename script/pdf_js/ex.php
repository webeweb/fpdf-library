<?php

require('pdf_js.php');

class PDF_AutoPrint extends PDF_JavaScript {

    function AutoPrint($printer = '') {
        // Open the print dialog
        if ($printer) {
            $printer = str_replace('\\', '\\\\', $printer);
            $script  = "var pp = getPrintParams();";
            $script  .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script  .= "pp.printerName = '$printer'";
            $script  .= "print(pp);";
        } else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }
}

$pdf = new PDF_AutoPrint();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 20);
$pdf->Text(90, 50, 'Print me!');
$pdf->AutoPrint();
$pdf->Output();
?>
