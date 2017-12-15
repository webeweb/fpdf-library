<?php
require('morepagestable.php');

function GenerateWord()
{
	// Get a random word
	$nb = rand(3,10);
	$w = '';
	for($i=1;$i<=$nb;$i++)
		$w .= chr(rand(ord('a'),ord('z')));
	return $w;
}

function GenerateSentence($words=500)
{
	// Get a random sentence
	$nb = rand(20,$words);
	$s = '';
	for($i=1;$i<=$nb;$i++)
		$s .= GenerateWord().' ';
	return substr($s,0,-1);
}

$pdf = new PDF('P','pt');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->MultiCell(0,20,'Example to build a table over more than one page');
$pdf->SetFont('Arial','',6);
$pdf->tablewidths = array(90, 90, 90, 90, 90, 90);
for($i=0;$i<4;$i++) {
	$data[] = array(GenerateSentence(), GenerateSentence(), GenerateSentence(), GenerateSentence(), GenerateSentence(), GenerateSentence());
}
$pdf->morepagestable($data);
$pdf->Output();
?>
