<?php
/****************************************************************************
* Logiciel : classe FPDF                                                    *
* Version :  1.01                                                           *
* Date :     03/10/2001                                                     *
* Licence :  Freeware                                                       *
* Auteur :   Olivier PLATHEY                                                *
*                                                                           *
* Vous pouvez utiliser et modifier ce logiciel comme bon vous semble.       *
****************************************************************************/
define('FPDF_VERSION','1.01');

class FPDF
{
//Private properties
var $page;              //current page number
var $n;                 //current number of objects
var $offset;            //stream offset
var $offsets;           //array of object offsets
var $buffer;            //buffer holding in-memory PDF
var $w,$h;              //dimensions of page in points
var $lMargin;           //left margin in user unit
var $tMargin;           //top margin in user unit
var $x,$y;              //current position in user unit for cell positionning
var $lasth;             //height of last cell printed
var $k;                 //scale factor (number of points in user unit)
var $fontnames;         //array of Postscript (Type1) font names
var $fonts;             //array of used fonts
var $FontFamily;        //current font family
var $FontStyle;         //current font style
var $FontSizePt;        //current font size in points
var $FontSize;          //current font size in user unit
var $AutoPageBreak;     //automatic page breaking
var $PageBreakTrigger;  //threshold used to trigger page breaks
var $InFooter;          //flag set when processing footer
var $DocOpen;           //flag indicating whether doc is open or closed

/****************************************************************************
*                                                                           *
*                              Public methods                               *
*                                                                           *
****************************************************************************/
function FPDF($orientation='P',$unit='mm')
{
	//Initialization of properties
	$this->page=0;
	$this->n=1;
	$this->buffer='';
	$this->fonts=array();
	$this->InFooter=false;
	$this->DocOpen=false;
	$this->FontStyle='';
	$this->FontSizePt=12;
	//Font names
	$this->fontnames['courier']='Courier';
	$this->fontnames['courierB']='Courier-Bold';
	$this->fontnames['courierI']='Courier-Oblique';
	$this->fontnames['courierBI']='Courier-BoldOblique';
	$this->fontnames['helvetica']='Helvetica';
	$this->fontnames['helveticaB']='Helvetica-Bold';
	$this->fontnames['helveticaI']='Helvetica-Oblique';
	$this->fontnames['helveticaBI']='Helvetica-BoldOblique';
	$this->fontnames['times']='Times-Roman';
	$this->fontnames['timesB']='Times-Bold';
	$this->fontnames['timesI']='Times-Italic';
	$this->fontnames['timesBI']='Times-BoldItalic';
	$this->fontnames['symbol']='Symbol';
	$this->fontnames['symbolB']='Symbol';
	$this->fontnames['symbolI']='Symbol';
	$this->fontnames['symbolBI']='Symbol';
	$this->fontnames['zapfdingbats']='ZapfDingbats';
	$this->fontnames['zapfdingbatsB']='ZapfDingbats';
	$this->fontnames['zapfdingbatsI']='ZapfDingbats';
	$this->fontnames['zapfdingbatsBI']='ZapfDingbats';
	//Page orientation (A4 format)
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$this->w=595.3;
		$this->h=841.9;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$this->w=841.9;
		$this->h=595.3;
	}
	else
		$this->Error('Incorrect orientation : '.$orientation);
	//Scale factor
	if($unit=='pt')
		$this->k=1;
	elseif($unit=='mm')
		$this->k=72/25.4;
	elseif($unit=='cm')
		$this->k=72/2.54;
	elseif($unit=='in')
		$this->k=72;
	else
		$this->Error('Incorrect unit : '.$unit);
	//Margins (1 cm)
	$margin=(double)sprintf('%.2f',28.35/$this->k);
	$this->SetMargins($margin,$margin);
	//Automatic page breaks
	$this->SetAutoPageBreak(true,2*$margin);
}

function SetMargins($left,$top)
{
	//Set left and top margins
	$this->lMargin=$left;
	$this->tMargin=$top;
}

function SetAutoPageBreak($auto,$margin=0)
{
	//Set auto page break mode and triggering margin
	$this->AutoPageBreak=$auto;
	$this->PageBreakTrigger=$this->h/$this->k-$margin;
}

function Error($msg)
{
	//Fatal error
	die($msg);
}

function Open()
{
	//Begin document
	$this->_begindoc();
	$this->DocOpen=true;
}

function Close()
{
	//Terminate document
	if($page=$this->page==0)
		$this->Error('Document contains no page');
	//Page footer
	$this->InFooter=true;
	$this->Footer();
	$this->InFooter=false;
	//Close page
	$this->_endpage();
	//Close document
	$this->_enddoc();
	$this->DocOpen=false;
}

function AddPage()
{
	//Start a new page
	$page=$this->page;
	if($page>0)
	{
		//Remember font
		$family=$this->FontFamily;
		$style=$this->FontStyle;
		$size=$this->FontSizePt;
		//Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
		//Close page
		$this->_endpage();
	}
	//Start new page
	$this->_beginpage();
	//Set line width to 1 point
	$this->SetLineWidth(sprintf('%.2f',1/$this->k));
	//Set line cap style to square
	$this->_out('2 J');
	//Page header
	$this->Header();
	//Restore font
	if($page>0 and $family!='')
		$this->SetFont($family,$style,$size);
}

function Header()
{
	//To be implemented in your own inherited class
}

function Footer()
{
	//To be implemented in your own inherited class
}

function PageNo()
{
	//Get current page number
	return $this->page;
}

function SetLineWidth($width)
{
	//Set line width
	$this->_out($width.' w');
}

function Line($x1,$y1,$x2,$y2)
{
	//Draw a line
	$this->_out($x1.' -'.$y1.' m '.$x2.' -'.$y2.' l S');
}

function Rect($x,$y,$w,$h)
{
	//Draw a rectangle
	$this->_out($x.' -'.$y.' '.$w.' -'.$h.' re S');
}

function SetFont($family,$style='',$size=0)
{
	//Select a font; size given in points
	if(!$this->_setfont($family,$style,$size))
		$this->Error('Incorrect font family or style : '.$family.' '.$style);
}

function SetFontSize($size)
{
	//Set font size in points
	$this->_setfontsize($size);
}

function Text($x,$y,$txt)
{
	//Output a string
	$txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
	$this->_out('BT '.$x.' -'.$y.' Td ('.$txt.') Tj ET');
}

function Cell($w,$h=0,$txt='',$border=0,$ln=0)
{
	//Output a cell
	if($this->y+$h>$this->PageBreakTrigger && $this->AutoPageBreak && !$this->InFooter)
		$this->AddPage();
	if($border==1)
		$this->_out($this->x.' -'.$this->y.' '.$w.' -'.$h.' re S');
	if($txt!='')
	{
		$txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
		$this->_out('BT '.($this->x+.15*$this->FontSize).' -'.($this->y+.5*$h+.3*$this->FontSize).' Td ('.$txt.') Tj ET');
	}
	$this->lasth=$h;
	if($ln==1)
	{
		//Go to next line
		$this->x=$this->lMargin;
		$this->y+=$h;
	}
	else
		$this->x+=$w;
}

function Ln($h='')
{
	//Line feed; default value is last cell height
	$this->x=$this->lMargin;
	if(is_string($h))
		$this->y+=$this->lasth;
	else
		$this->y+=$h;
}

function GetY()
{
	//Get y position
	return $this->y;
}

function SetY($y)
{
	//Set y position
	$this->x=$this->lMargin;
	if($y>=0)
		$this->y=$y;
	else
		$this->y=(double)sprintf('%.2f',$this->h/$this->k)+$y;
}

function Output($file='')
{
	//Output PDF to file or browser
	if($this->DocOpen)
		$this->Close();
	if($file=='')
	{
		Header('Content-Type: application/pdf');
		Header('Content-Length: '.strlen($this->buffer));
		Header('Expires: 0');
		echo $this->buffer;
	}
	else
	{
		$f=fopen($file,'wb');
		if(!$f)
			$this->Error('Unable to create output file : '.$file);
		fwrite($f,$this->buffer,strlen($this->buffer));
		fclose($f);
	}
}

/****************************************************************************
*                                                                           *
*                              Private methods                              *
*                                                                           *
****************************************************************************/
function _begindoc()
{
	$this->_out('%PDF-1.3');
}

function _enddoc()
{
	//Fonts
	$nf=$this->n;
	reset($this->fonts);
	while(list($name)=each($this->fonts))
	{
		$this->_newobj();
		$this->_out('<< /Type /Font');
		$this->_out('/Subtype /Type1');
		$this->_out('/BaseFont /'.$name);
		$this->_out('/Encoding /WinAnsiEncoding >>');
		$this->_out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<< /Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$this->page;$i++)
		$kids.=(2+3*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$this->page);
	$this->_out('/MediaBox [0 0 '.$this->w.' '.$this->h.']');
	$this->_out('/Resources << /ProcSet [/PDF /Text]');
	$this->_out('/Font <<');
	for($i=1;$i<=count($this->fonts);$i++)
		$this->_out('/F'.$i.' '.($nf+$i).' 0 R');
	$this->_out('>> >> >>');
	$this->_out('endobj');
	//Info
	$this->_newobj();
	$this->_out('<< /Producer (FPDF '.FPDF_VERSION.')');
	$this->_out('/CreationDate (D:'.date('YmdHis').') >>');
	$this->_out('endobj');
	//Catalog
	$this->_newobj();
	$this->_out('<< /Type /Catalog');
	$this->_out('/Pages 1 0 R >>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<< /Size '.($this->n+1));
	$this->_out('/Root '.$this->n.' 0 R');
	$this->_out('/Info '.($this->n-1).' 0 R >>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
}

function _beginpage()
{
	$this->page++;
	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->lasth=0;
	$this->FontFamily='';
	//Page object
	$this->_newobj();
	$this->_out('<< /Type /Page');
	$this->_out('/Parent 1 0 R');
	$this->_out('/Contents '.($this->n+1).' 0 R >>');
	$this->_out('endobj');
	//Begin of page contents
	$this->_newobj();
	$this->_out('<< /Length '.($this->n+1).' 0 R >>');
	$this->_out('stream');
	$this->offset=strlen($this->buffer);
	//Set transformation matrix
	$this->_out(sprintf('%.6f',$this->k).' 0 0 '.sprintf('%.6f',$this->k).' 0 '.$this->h.' cm');
}

function _endpage()
{
	//End of page contents
	$size=strlen($this->buffer)-$this->offset;
	$this->_out('endstream');
	$this->_out('endobj');
	//Size of page contents stream
	$this->_newobj();
	$this->_out($size);
	$this->_out('endobj');
}

function _newobj()
{
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n]=strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
}

function _setfont($family,$style,$size)
{
	$family=strtolower($family);
	if($family=='')
		$family=$this->FontFamily;
	if($family=='arial')
		$family='helvetica';
	$style=strtoupper($style);
	if($style=='IB')
		$style='BI';
	if($size==0)
		$size=$this->FontSizePt;
	//Test if font already selected
	if($this->FontFamily==$family and $this->FontStyle==$style and $this->FontSizePt==$size)
		return true;
	//Retrieve Type1 font name
	if(!isset($this->fontnames[$family.$style]))
		return false;
	$fontname=$this->fontnames[$family.$style];
	//If the font is used for the first time, record it
	if(!isset($this->fonts[$fontname]))
	{
		$n=count($this->fonts);
		$this->fonts[$fontname]=$n+1;
	}
	//Select it
	$this->FontFamily=$family;
	$this->FontStyle=$style;
	$this->FontSizePt=$size;
	$this->FontSize=(double)sprintf('%.2f',$size/$this->k);
	$this->_out('BT /F'.$this->fonts[$fontname].' '.$this->FontSize.' Tf ET');
	return true;
}

function _setfontsize($size)
{
	//Test if size already selected
	if($this->FontSizePt==$size)
		return;
	//Select it
	$fontname=$this->fontnames[$this->FontFamily.$this->FontStyle];
	$this->FontSizePt=$size;
	$this->FontSize=(double)sprintf('%.2f',$size/$this->k);
	$this->_out('BT /F'.$this->fonts[$fontname].' '.$this->FontSize.' Tf ET');
}

function _out($s)
{
	//Add a line to the document
	$this->buffer.=$s."\n";
}
//End of class
}

//Handle silly IE contype request
if($HTTP_ENV_VARS['HTTP_USER_AGENT']=='contype')
{
	Header('Content-Type: application/pdf');
	exit;
}

?>