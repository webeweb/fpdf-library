<?php
require('linegraph.php');

$pdf = new PDF_LineGraph();
$pdf->SetFont('Arial','',10);
$data = array(
	'Group 1' => array(
		'08-02' => 2.7,
		'08-23' => 3.0,
		'09-13' => 3.3928571,
		'10-04' => 3.2903226,
		'10-25' => 3.1
	),
	'Group 2' => array(
		'08-02' => 2.5,
		'08-23' => 2.0,
		'09-13' => 3.1785714,
		'10-04' => 2.9677419,
		'10-25' => 3.33333
	)
);
$colors = array(
	'Group 1' => array(114,171,237),
	'Group 2' => array(163,36,153)
);

$pdf->AddPage();
// Display options: all (horizontal and vertical lines, 4 bounding boxes)
// Colors: fixed
// Max ordinate: 6
// Number of divisions: 3
$pdf->LineGraph(190,100,$data,'VHkBvBgBdB',$colors,6,3);

$pdf->AddPage();
// Display options: horizontal lines, bounding box around the abscissa values
// Colors: random
// Max ordinate: auto
// Number of divisions: default
$pdf->LineGraph(190,100,$data,'HvB');

$pdf->AddPage();
// Display options: vertical lines, bounding box around the legend
// Colors: random
// Max ordinate: auto
// Number of divisions: default
$pdf->LineGraph(190,100,$data,'VkB');

$pdf->AddPage();
// Display options: horizontal lines, bounding boxes around the plotting area and the entire area
// Colors: random
// Max ordinate: 20
// Number of divisions: 10
$pdf->LineGraph(190,100,$data,'HgBdB',null,20,10);

$pdf->Output();
?>
