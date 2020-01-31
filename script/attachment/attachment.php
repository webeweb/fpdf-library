<?php

require('fpdf.php');

class PDF_Attachment extends FPDF {

    protected $files = [];

    protected $n_files;

    protected $open_attachment_pane = false;

    function Attach($file, $name = '', $desc = '', $isUTF8 = false) {
        if ($name == '') {
            $p = strrpos($file, '/');
            if ($p === false)
                $p = strrpos($file, '\\');
            if ($p !== false)
                $name = substr($file, $p + 1);
            else
                $name = $file;
        }
        if (!$isUTF8)
            $desc = utf8_encode($desc);
        $this->files[] = ['file' => $file, 'name' => $name, 'desc' => $desc];
    }

    function OpenAttachmentPane() {
        $this->open_attachment_pane = true;
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (!empty($this->files))
            $this->_put('/Names <</EmbeddedFiles ' . $this->n_files . ' 0 R>>');
        if ($this->open_attachment_pane)
            $this->_put('/PageMode /UseAttachments');
    }

    function _putfiles() {
        $s = '';
        foreach ($this->files as $i => $info) {
            $file = $info['file'];
            $name = $info['name'];
            $desc = $info['desc'];

            $fc = file_get_contents($file);
            if ($fc === false)
                $this->Error('Cannot open file: ' . $file);
            $md = @date('YmdHis', filemtime($file));

            $this->_newobj();
            $s .= $this->_textstring(sprintf('%03d', $i)) . ' ' . $this->n . ' 0 R ';
            $this->_put('<<');
            $this->_put('/Type /Filespec');
            $this->_put('/F (' . $this->_escape($name) . ')');
            $this->_put('/UF ' . $this->_textstring(utf8_encode($name)));
            $this->_put('/EF <</F ' . ($this->n + 1) . ' 0 R>>');
            if ($desc)
                $this->_put('/Desc ' . $this->_textstring($desc));
            $this->_put('>>');
            $this->_put('endobj');

            $this->_newobj();
            $this->_put('<<');
            $this->_put('/Type /EmbeddedFile');
            $this->_put('/Length ' . strlen($fc));
            $this->_put("/Params <</ModDate (D:$md)>>");
            $this->_put('>>');
            $this->_putstream($fc);
            $this->_put('endobj');
        }
        $this->_newobj();
        $this->n_files = $this->n;
        $this->_put('<<');
        $this->_put('/Names [' . $s . ']');
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->files))
            $this->_putfiles();
    }
}

?>
