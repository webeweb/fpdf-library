<?php
require('fpdf.php');

class PDF extends FPDF {

public $tablewidths;
public $footerset;

function _beginpage($orientation, $size, $rotation) {
	$this->page++;
	if(!isset($this->pages[$this->page])) // solves the problem of overwriting a page if it already exists
		$this->pages[$this->page] = '';
	$this->state = 2;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->FontFamily = '';
	// Check page size and orientation
	if($orientation=='')
		$orientation = $this->DefOrientation;
	else
		$orientation = strtoupper($orientation[0]);
	if($size=='')
		$size = $this->DefPageSize;
	else
		$size = $this->_getpagesize($size);
	if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
	{
		// New size or orientation
		if($orientation=='P')
		{
			$this->w = $size[0];
			$this->h = $size[1];
		}
		else
		{
			$this->w = $size[1];
			$this->h = $size[0];
		}
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		$this->PageBreakTrigger = $this->h-$this->bMargin;
		$this->CurOrientation = $orientation;
		$this->CurPageSize = $size;
	}
	if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
		$this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
	if($rotation!=0)
	{
		if($rotation%90!=0)
			$this->Error('Incorrect rotation value: '.$rotation);
		$this->CurRotation = $rotation;
		$this->PageInfo[$this->page]['rotation'] = $rotation;
	}
}

function Footer() {
	// Check if Footer for this page already exists (do the same for Header())
	if(!isset($this->footerset[$this->page])) {
		$this->SetY(-15);
		// Page number
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		// set footerset
		$this->footerset[$this->page] = true;
	}
}

function morepagestable($datas, $lineheight=8) {
	// some things to set and 'remember'
	$l = $this->lMargin;
	$startheight = $h = $this->GetY();
	$startpage = $currpage = $maxpage = $this->page;

	// calculate the whole width
	$fullwidth = 0;
	foreach($this->tablewidths AS $width) {
		$fullwidth += $width;
	}

	// Now let's start to write the table
	foreach($datas AS $row => $data) {
		$this->page = $currpage;
		// write the horizontal borders
		$this->Line($l,$h,$fullwidth+$l,$h);
		// write the content and remember the height of the highest col
		foreach($data AS $col => $txt) {
			$this->page = $currpage;
			$this->SetXY($l,$h);
			$this->MultiCell($this->tablewidths[$col],$lineheight,$txt);
			$l += $this->tablewidths[$col];

			if(!isset($tmpheight[$row.'-'.$this->page]))
				$tmpheight[$row.'-'.$this->page] = 0;
			if($tmpheight[$row.'-'.$this->page] < $this->GetY()) {
				$tmpheight[$row.'-'.$this->page] = $this->GetY();
			}
			if($this->page > $maxpage)
				$maxpage = $this->page;
		}

		// get the height we were in the last used page
		$h = $tmpheight[$row.'-'.$maxpage];
		// set the "pointer" to the left margin
		$l = $this->lMargin;
		// set the $currpage to the last page
		$currpage = $maxpage;
	}
	// draw the borders
	// we start adding a horizontal line on the last page
	$this->page = $maxpage;
	$this->Line($l,$h,$fullwidth+$l,$h);
	// now we start at the top of the document and walk down
	for($i = $startpage; $i <= $maxpage; $i++) {
		$this->page = $i;
		$l = $this->lMargin;
		$t  = ($i == $startpage) ? $startheight : $this->tMargin;
		$lh = ($i == $maxpage)   ? $h : $this->h-$this->bMargin;
		$this->Line($l,$t,$l,$lh);
		foreach($this->tablewidths AS $width) {
			$l += $width;
			$this->Line($l,$t,$l,$lh);
		}
	}
	// set it to the last page, if not it'll cause some problems
	$this->page = $maxpage;
}
}
?>
