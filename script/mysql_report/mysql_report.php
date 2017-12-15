<?php

# Copyright (c) Bouncing Ltd 2003-2016
# Author Philip Clarke nod@bouncing.org
# Released under the CC Attribution 4.0 licence https://creativecommons.org/licenses/by/4.0/
# You may do with it as you please just keep the credits. If you change something note it down for your own good
# This Version released 12/11/2016 (keep in as helps with bug fixes)


// Include the FPDF library. Alter for your system
require('fpdf.php');

class PDF extends FPDF {

protected $tablewidths;
protected $headerset;
protected $footerset;

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

function Header()
{
	static $maxY, $fullwidth;
    
	// Check if header for this page already exists
	if( !isset($this->headerset[$this->page]) ) {

		foreach($this->tablewidths as $width) {
			$fullwidth += $width;
		}
		$this->SetY(($this->tMargin) - ($this->FontSizePt/$this->k)*2);
		$this->cellFontSize = $this->FontSizePt ;
		$this->SetFont('Arial','',( ( $this->titleFontSize) ? $this->titleFontSize : $this->FontSizePt ));
		$this->Cell(0,$this->FontSizePt,$this->titleText,0,1,'C');
		$l = ($this->lMargin);
		$this->SetFont('Arial','',$this->cellFontSize);
		foreach($this->colTitles as $col => $txt) {
			$this->SetXY($l,($this->tMargin));
			$this->MultiCell($this->tablewidths[$col], $this->FontSizePt,$txt);
			$l += $this->tablewidths[$col] ;
			$maxY = ($maxY < $this->getY()) ? $this->getY() : $maxY ;
		}
		$this->SetXY($this->lMargin,$this->tMargin);
		$this->setFillColor(200,200,200);
		$l = ($this->lMargin);
		foreach($this->colTitles as $col => $txt) {
			$this->SetXY($l,$this->tMargin);
			$this->cell($this->tablewidths[$col],$maxY-($this->tMargin),'',1,0,'L',1);
			$this->SetXY($l,$this->tMargin);
			$this->MultiCell($this->tablewidths[$col],$this->FontSizePt,$txt,0,'C');
			$l += $this->tablewidths[$col];
		}
		$this->setFillColor(255,255,255);
		// set headerset
		$this->headerset[$this->page] = 1;
	}

	$this->SetY($maxY);
}

function Footer() {
	// Check if footer for this page already exists
	if( !isset($this->footerset[$this->page]) ) {
		$this->SetY(-15);
		//Page number
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		// set footerset
		$this->footerset[$this->page] = 1;
	}
}

function morepagestable($lineheight=8) {

    static $fullwidth, $tmpheight, $maxpage;
    
    unset($fullwidth);
    
    // some things to set and 'remember'
	$l = $this->lMargin;
	$startheight = $h = $this->GetY();
	$startpage = $currpage = $this->page;

	// calculate the whole width
	foreach($this->tablewidths as $width) {
		$fullwidth += $width;
	}

	// Now let's start to write the table
	$row = 0;
    
    // fix for empty resultset
    if( mysqli_num_rows($this->results)==0 ){
                if($this->page > $maxpage)
                    $maxpage = $this->page;
                $this->page = $currpage;
                $this->SetXY($l,$h);
                $this->MultiCell($fullwidth,$lineheight,'No Rows returned',0,'L');
    }
    
    while($data=mysqli_fetch_row($this->results)) {
		$this->page = $currpage;
		// write the horizontal borders
		$this->Line($l,$h,$fullwidth+$l,$h);
		// write the content and remember the height of the highest col
		foreach($data as $col => $txt) {

			$this->page = $currpage;
			$this->SetXY($l,$h);
			$this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$this->colAlign[$col]);

			$l += $this->tablewidths[$col];

			if(!isset($tmpheight[$row.'-'.$this->page]) || ($tmpheight[$row.'-'.$this->page] < $this->GetY() ) ) {
				$tmpheight[$row.'-'.$this->page] = $this->GetY();
			}
			if($this->page > $maxpage)
				$maxpage = $this->page;
			unset($data[$col]);
		}
		// get the height we were in the last used page
		$h = $tmpheight[$row.'-'.$maxpage];
		// set the "pointer" to the left margin
		$l = $this->lMargin;
		// set the $currpage to the last page
		$currpage = $maxpage;
		unset($data[$row]);
		$row++ ;
	}
    

    // draw the borders
	// we start adding a horizontal line on the last page
 	$this->page = $maxpage;
	$this->Line($l,$h,$fullwidth+$l,$h);

    // now we start at the top of the document and walk down
	for($i = $startpage; $i <= $maxpage; $i++) {
		$this->page = $i;
		$l = $this->lMargin;
		$t = ($i == $startpage) ? $startheight : $this->tMargin;
		$lh = ($i == $maxpage) ? $h : $this->h-$this->bMargin;
		$this->Line($l,$t,$l,$lh);
		foreach($this->tablewidths as $width) {
			$l += $width;
			$this->Line($l,$t,$l,$lh);
		}
	}
	// set it to the last page, if not it'll cause some problems
	$this->page = $maxpage;
}

function connect($host='localhost',$username='',$password='',$db=''){
	$this->conn = mysqli_connect($host,  $username,$password) or die( mysqli_error( $this->conn ) );
	mysqli_select_db($this->conn, $db) or die( mysqli_error( $this->conn ) );
	return true;
}

function query($query){
    $query = mysqli_real_escape_string($this->conn, $query);
	$this->results = mysqli_query($this->conn, $query);
	$this->numFields = mysqli_num_fields($this->results);
}

function mysql_report($query, $dump=false, $attr=array()){

//    $dump = true;

    unset($this->tablewidths);
    unset($this->sColWidth);
    unset($this->colTitles);
    
	foreach($attr as $key=>$val){
		$this->$key = $val ;
	}

	$this->query($query);

	// if column widths not set
	if(!isset($this->tablewidths)){
			Header('Content-type: text/plain');

        // starting col width
		$this->sColWidth = (($this->w-$this->lMargin-$this->rMargin))/$this->numFields;
        
		// loop through results header and set initial col widths/ titles/ alignment
		// if a col title is less than the starting col width / reduce that column size
        for($i=0;$i<$this->numFields;$i++){
            $_mysqli_obj = mysqli_fetch_field_direct($this->results, $i) ;
			$stringWidth = $this->getstringwidth( $_mysqli_obj->name ) + 6 ;
            if( ($stringWidth) < $this->sColWidth){
				$colFits[$i] = $stringWidth ;
				// set any column titles less than the start width to the column title width
			}

            $this->colTitles[$i] = $_mysqli_obj->name ;

            switch (  $_mysqli_obj->type ){
				case 'int':
					$this->colAlign[$i] = 'R';
					break;
				default:
					$this->colAlign[$i] = 'L';
			}
		}

        if(!isset($colFits)){
            $colFits = array();
        }

        // loop through the data, any column whose contents is bigger that the col size is
		// resized
    		while( $row=mysqli_fetch_row($this->results) ){
			foreach($colFits as $key=>$val){
				$stringWidth = $this->getstringwidth($row[$key]) + 6 ;
				if( ($stringWidth) > $this->sColWidth ){
					// any col where row is bigger than the start width is now discarded
					unset($colFits[$key]);
				}else{
					// if text is not bigger than the current column width setting enlarge the column
					if( ($stringWidth) > $val ){
						$colFits[$key] = ($stringWidth) ;
					}
				}
			}
            }



        
        if(!isset($totAlreadyFitted)){
            $totAlreadyFitted = 0 ;
        }

        foreach($colFits as $key=>$val){
			// set fitted columns to smallest size
			$this->tablewidths[$key] = $val;
			// to work out how much (if any) space has been freed up
			$totAlreadyFitted += $val;
		}

        
		$surplus = (sizeof($colFits)*$this->sColWidth) - ($totAlreadyFitted);
		for($i=0;$i<$this->numFields;$i++){
			if(!in_array($i,array_keys($colFits))){
				$this->tablewidths[$i] = $this->sColWidth + ($surplus/(($this->numFields)-sizeof($colFits)));
			}
		}

        ksort($this->tablewidths);

        if( !isset($flength) ) 
            $flength = 0 ;
        
		if($dump){
			Header('Content-type: text/plain');
			for($i=0;$i<$this->numFields;$i++){
            $_mysqli_obj = mysqli_fetch_field_direct($this->results, $i) ;
				if(strlen( $_mysqli_obj->name ) > $flength){
					$flength = strlen( $_mysqli_obj->name );
				}
			}
			switch($this->k){
				case 72/25.4:
					$unit = 'millimeters';
					break;
				case 72/2.54:
					$unit = 'centimeters';
					break;
				case 72:
					$unit = 'inches';
					break;
				default:
					$unit = 'points';
			}
            
			print "All measurements in $unit\n\n";
            
			for($i=0;$i<$this->numFields;$i++){
            $_mysqli_obj = mysqli_fetch_field_direct($this->results, $i) ;
				printf("%-{$flength}s : %-10s : %10f\n",
					$_mysqli_obj->name,
					$_mysqli_obj->type,
					$this->tablewidths[$i] );
			}
			print "\n\n";
			print "\$pdf->tablewidths=\n\tarray(\n\t\t";
			for($i=0;$i<$this->numFields;$i++){
            $_mysqli_obj = mysqli_fetch_field_direct($this->results, $i) ;
				($i<($this->numFields-1)) ?
				print $this->tablewidths[$i].", /* ".$_mysqli_obj->name." */\n\t\t":
				print $this->tablewidths[$i]." /* ".$_mysqli_obj->name." */\n\t\t";
			}
			print "\n\t);\n";
			exit;
		}

	} else { // end of if tablewidths not defined

		for($i=0;$i<$this->numFields;$i++){
            $_mysqli_obj = mysqli_fetch_field_direct($this->results, $i) ;
			$this->colTitles[$i] = $_mysqli_obj->name ;
			switch ( $_mysqli_obj->type ){
				case 'int':
					$this->colAlign[$i] = 'R';
					break;
				default:
					$this->colAlign[$i] = 'L';
			}
		}
	}
    
    mysqli_data_seek($this->results,0);
	$this->AliasNbPages();
	$this->SetY($this->tMargin);
	$this->AddPage();
	$this->morepagestable($this->FontSizePt);
}

}



/* ADVICE do not use a PHP closing tag like  ?> */


