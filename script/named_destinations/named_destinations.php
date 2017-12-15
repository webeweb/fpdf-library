<?php
require_once('fpdf.php');

class PDF_NamedDestinations extends FPDF
{
    protected $namedDestinations = array();
    protected $n_namedDestinations;

    function SetLink($link, $y = 0, $page = -1)
    {
        if (strpos($link, '#') !== 0) {
            parent::SetLink($link);
        } else {
            // Set destination of internal link
            if ($y == -1)
                $y = $this->y;
            if ($page == -1)
                $page = $this->page;
            $this->namedDestinations[substr($link, 1)] = array($page, $y);
        }
    }

    function _putnamedDestinations()
    {
        $s = array();
        if ($this->DefOrientation == 'P') {
            $hPt = $this->DefPageSize[1] * $this->k;
        } else {
            $hPt = $this->DefPageSize[0] * $this->k;
        }
        
        foreach ($this->namedDestinations as $name => $namedDestinations) {
            $h = isset($this->PageInfo[$namedDestinations[0]]['size']) ? $this->PageInfo[$namedDestinations[0]]['size'][1] : $hPt;
            $this->_newobj();
            $this->_put(sprintf('[%d 0 R /XYZ 0 %.2F null]', 1 + 2 * $namedDestinations[0], $h - $namedDestinations[1] * $this->k));
            $this->_put('endobj');
            
            $s[$name] = $this->_textstring($name) . ' ' . $this->n . ' 0 R';
        }
        $this->_newobj();
        $this->n_namedDestinations = $this->n;
        $this->_put('<<');
        ksort($s);
        $this->_put('/Names [' . join(' ', $s) . ']');
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources()
    {
        parent::_putresources();
        if(!empty($this->namedDestinations))
            $this->_putnamedDestinations();
    }

    function _putcatalog()
    {
        parent::_putcatalog();
        if(!empty($this->namedDestinations))
            $this->_put('/Names <</Dests '.$this->n_namedDestinations.' 0 R>>');
    }

    protected function _putpage($n)
    {
        $this->_newobj();
        $this->_put('<</Type /Page');
        $this->_put('/Parent 1 0 R');
        if(isset($this->PageInfo[$n]['size']))
            $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
        if(isset($this->PageInfo[$n]['rotation']))
            $this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
        $this->_put('/Resources 2 0 R');
        if(isset($this->PageLinks[$n]))
        {
            // Links
            $annots = '/Annots [';
            foreach($this->PageLinks[$n] as $pl)
            {
                $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                if(is_string($pl[4])) {
                    if (strpos($pl[4], '#') === 0) {
                        $annots .= '/A <</S /GoTo /D ' . $this->_textstring(substr($pl[4], 1)) . '>>>>';
                    } else {
                        $annots .= '/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
                    }
                }
                else
                {
                    $l = $this->links[$pl[4]];
                    if(isset($this->PageInfo[$l[0]]['size']))
                        $h = $this->PageInfo[$l[0]]['size'][1];
                    else
                        $h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
                    $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k);
                }
            }
            $this->_put($annots.']');
        }
        if($this->WithAlpha)
            $this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
        $this->_put('/Contents '.($this->n+1).' 0 R>>');
        $this->_put('endobj');
        // Page content
        if(!empty($this->AliasNbPages))
            $this->pages[$n] = str_replace($this->AliasNbPages,$this->page,$this->pages[$n]);
        $this->_putstreamobject($this->pages[$n]);
    }
}