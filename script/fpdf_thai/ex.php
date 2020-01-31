<?php

require('fpdf_thai.php');

class PDF extends FPDF_Thai {

    function ColorTable($header, $data) {
        //Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        //Header
        $w = [40, 35, 40, 45];
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        $this->Ln();
        //Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Data
        $fill = 0;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row[2], 'LR', 0, 'C', $fill);
            $this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    function Header() {
        $this->SetFont('AngsanaNew', 'BU', 40);
        $this->Cell(190, 10, '����Է����º�þ�', 0, 0, 'C');
        $this->Ln(20);
    }
}

$pdf = new PDF();
$pdf->AddFont('AngsanaNew', '', 'angsa.php');
$pdf->AddFont('AngsanaNew', 'B', 'angsab.php');
$pdf->AddPage();
$pdf->SetFont('AngsanaNew', '', 12);
//Column titles
$header = ['����', '���ʡ��', '���ʹ��Ե', '��������/��͹'];
//Read file lines
$lines = file('student.txt');
$data  = [];
foreach ($lines as $line)
    $data[] = explode(';', chop($line));
//Output table
$pdf->ColorTable($header, $data);

$pdf->Output();
?>
