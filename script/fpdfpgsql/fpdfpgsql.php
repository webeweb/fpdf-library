<?php
//Example FPDF script with PostgreSQL
//Ribamar FS - ribafs@dnocs.gov.br

require('fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetTitle('Exemplo de Relat�rio em PDF via PHP');

//Set font and colors
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetFillColor(255, 0, 0);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(128, 0, 0);
$pdf->SetLineWidth(.3);

//Table header
$pdf->Cell(20, 10, 'SIAPE', 1, 0, 'L', 1);
$pdf->Cell(50, 10, 'Nome', 1, 1, 'L', 1);

//Restore font and colors
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(224, 235, 255);
$pdf->SetTextColor(0);

//Connection and query
$str_conexao = 'dbname=contabilidade port=5432 user=postgres password=postgres';
$conexao = pg_connect($str_conexao) or die('A conex�o ao banco de dados falhou!');
$consulta = pg_exec($conexao, 'select * from conveniologin');
$numregs  = pg_numrows($consulta);

//Build table
$fill = false;
$i    = 0;
while ($i < $numregs) {
    $siape = pg_result($consulta, $i, 'siape');
    $nome  = pg_result($consulta, $i, 'nome');
    $pdf->Cell(20, 10, $siape, 1, 0, 'R', $fill);
    $pdf->Cell(50, 10, $nome, 1, 1, 'L', $fill);
    $fill = !$fill;
    $i++;
}

//Add a rectangle, a line, a logo and some text
$pdf->Rect(5, 5, 170, 80);
$pdf->Line(5, 90, 90, 90);
$pdf->Image('mouse.jpg', 185, 5, 10, 0, 'JPG', 'http://www.dnocs.gov.br');
$pdf->SetFillColor(224, 235);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(5, 95);
$pdf->Cell(170, 5, 'PDF gerado via PHP acessando banco de dados - Por Ribamar FS', 1, 1, 'L', 1, 'mailto:ribafs@dnocs.gov.br');

$pdf->Output();
?>
