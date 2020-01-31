<?php

require('fpdf.php');

class PDF_Layer extends FPDF {

    protected $current_layer = -1;

    protected $layers = [];

    protected $open_layer_pane = false;

    function AddLayer($name, $isUTF8 = false, $visible = true) {
        if (!$isUTF8)
            $name = utf8_encode($name);
        $this->layers[] = ['name' => $name, 'visible' => $visible];
        return count($this->layers) - 1;
    }

    function BeginLayer($id) {
        $this->EndLayer();
        $this->_out('/OC /OC' . $id . ' BDC');
        $this->current_layer = $id;
    }

    function EndLayer() {
        if ($this->current_layer >= 0) {
            $this->_out('EMC');
            $this->current_layer = -1;
        }
    }

    function OpenLayerPane() {
        $this->open_layer_pane = true;
    }

    function _enddoc() {
        if ($this->PDFVersion < '1.5')
            $this->PDFVersion = '1.5';
        parent::_enddoc();
    }

    function _endpage() {
        $this->EndLayer();
        parent::_endpage();
    }

    function _putcatalog() {
        parent::_putcatalog();
        $l     = '';
        $l_off = '';
        foreach ($this->layers as $layer) {
            $l .= $layer['n'] . ' 0 R ';
            if (!$layer['visible'])
                $l_off .= $layer['n'] . ' 0 R ';
        }
        $this->_put("/OCProperties <</OCGs [$l] /D <</OFF [$l_off] /Order [$l]>>>>");
        if ($this->open_layer_pane)
            $this->_put('/PageMode /UseOC');
    }

    function _putlayers() {
        foreach ($this->layers as $id => $layer) {
            $this->_newobj();
            $this->layers[$id]['n'] = $this->n;
            $this->_put('<</Type /OCG /Name ' . $this->_textstring($layer['name']) . '>>');
            $this->_put('endobj');
        }
    }

    function _putresourcedict() {
        parent::_putresourcedict();
        $this->_put('/Properties <<');
        foreach ($this->layers as $id => $layer)
            $this->_put('/OC' . $id . ' ' . $layer['n'] . ' 0 R');
        $this->_put('>>');
    }

    function _putresources() {
        $this->_putlayers();
        parent::_putresources();
    }
}

?>
