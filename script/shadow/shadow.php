<?php

require('fpdf.php');

class PDF_Shadow extends FPDF {

    function ShadowCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $color = 'G', $distance = 0.5) {
        if ($color == 'G')
            $ShadowColor = 100;
        else if ($color == 'B')
            $ShadowColor = 0;
        else
            $ShadowColor = $color;
        $TextColor = $this->TextColor;
        $x         = $this->x;
        $this->SetTextColor($ShadowColor);
        $this->Cell($w, $h, $txt, $border, 0, $align, $fill, $link);
        $this->TextColor = $TextColor;
        $this->x         = $x;
        $this->y         += $distance;
        $this->Cell($w, $h, $txt, 0, $ln, $align);
    }
}

?>
