<?php

require('fpdf.php');

// Written by Larry Stanbery - 20 May 2004
// Same license as FPDF
// creates "page groups" -- groups of pages with page numbering
// total page numbers are represented by aliases of the form {nbX}

class PDF_PageGroup extends FPDF {

    protected $CurrPageGroup;   // variable indicating whether a new group was requested

    protected $NewPageGroup;     // variable containing the number of pages of the groups

    protected $PageGroups;  // variable containing the alias of the current page group

    // create a new page group; call this before calling AddPage()

    function GroupPageNo() {
        return $this->PageGroups[$this->CurrPageGroup];
    }

    // current page in the group

    function PageGroupAlias() {
        return $this->CurrPageGroup;
    }

    // alias of the current page group -- will be replaced by the total number of pages in this group

    function StartPageGroup() {
        $this->NewPageGroup = true;
    }

    function _beginpage($orientation, $format, $rotation) {
        parent::_beginpage($orientation, $format, $rotation);
        if ($this->NewPageGroup) {
            // start a new group
            $n                        = sizeof($this->PageGroups) + 1;
            $alias                    = "{nb$n}";
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup      = $alias;
            $this->NewPageGroup       = false;
        } else if ($this->CurrPageGroup)
            $this->PageGroups[$this->CurrPageGroup]++;
    }

    function _putpages() {
        $nb = $this->page;
        if (!empty($this->PageGroups)) {
            // do page number replacement
            foreach ($this->PageGroups as $k => $v) {
                for ($n = 1; $n <= $nb; $n++) {
                    $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
                }
            }
        }
        parent::_putpages();
    }
}

?>
