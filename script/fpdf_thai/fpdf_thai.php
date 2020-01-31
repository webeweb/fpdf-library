<?php
/*******************************************************************************
 * FPDF Thai Positioning Improve                                                *
 *                                                                              *
 * Version:  1.01                                                               *
 * Date:     2009-10-08                                                         *
 * Advisor:  Mr. Wittawas Puntumchinda                                          *
 * Coding:   Mr. Sirichai Fuangfoo                                              *
 * License:  FPDF                                                               *
 *******************************************************************************/

require('fpdf.php');

class FPDF_Thai extends FPDF {

    var $array_th;

    var $checkFill;

    var $curPointX;

    var $pointX;

    var $pointY;

    var $s_error;

    var $s_th;

    var $string_th;

    var $txt_error;

    /****************************************************************************************
     * ������  : Function    �ͧ Class FPDF_TH
     * ��ҧ�ԧ       : Function Cell    �ͧ Class FPDF
     * ��÷ӧҹ  : ��㹡�þ�����ͤ������к�÷Ѵ�ͧ�͡��� PDF
     * �ٺẺ  : Cell (    $w = �������ҧ�ͧCell,
     *                    $h = �����٧�ͧCell,
     *                    $txt = ��ͤ������о����,
     *                    $border = ��˹�����ʴ���鹡�ͺ(0 = ����ʴ�, 1= �ʴ�),
     *                    $ln = ���˹觷������Ѵ仨ҡ����(0 = ���, 1 = ��÷Ѵ�Ѵ�, 2 = ��ҹ��ҧ),
     *                    $align = ���˹觢�ͤ���(L = ����, R = ���, C = ��觡�ҧ, T = ��, B = ��ҧ),
     *                    $fill = ��˹�����ʴ��բͧCell(false = ����ʴ�, true = �ʴ�),
     *                    $link = URL ����ͧ�������ͤ���������§件֧
     *                )
     *****************************************************************************************/
    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $this->checkFill = "";
        $k               = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            //���˹�������ѵ��ѵ
            $x  = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $k));
            }
        }
        //��˹��������ҧ������ҡѺ˹�ҡ�д��
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $this->s_th = '';
        //��˹�����ʴ���鹡�ͺ 4 ��ҹ ����ա�ͺ
        if ($fill || $border == 1) {
            if ($fill)
                $op = ($border == 1) ? 'B' : 'f';
            else
                $op = 'S';
            $this->s_th = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
            if ($op == 'f')
                $this->checkFill = $op;
        }
        //��˹�����ʴ���鹡�ͺ�������
        if (is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if (strpos($border, 'L') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'T') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            if (strpos($border, 'R') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'B') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
        }

        if ($txt !== '') {
            $x = $this->x;
            $y = $this->y;
            //��˹���èѴ��ͤ�������������дѺ
            if (strpos($align, 'R') !== false)
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            else if (strpos($align, 'C') !== false)
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            else
                $dx = $this->cMargin;
            //��˹���èѴ��ͤ�����������Ǵ��
            if (strpos($align, 'T') !== false)
                $dy = $h - (.7 * $this->k * $this->FontSize);
            else if (strpos($align, 'B') !== false)
                $dy = $h - (.3 * $this->k * $this->FontSize);
            else
                $dy = .5 * $h;
            //��˹���âմ������ͤ���
            if ($this->underline) {
                //��˹��ѹ�֡��ҿԡ
                if ($this->ColorFlag)
                    $this->s_th .= ' q ' . $this->TextColor . ' ';
                //�մ������ͤ���0
                $this->s_th .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
                //��˹��׹��ҡ�ҿԡ
                if ($this->ColorFlag)
                    $this->s_th .= ' Q ';
            }
            //��˹���ͤ���������§件֧
            if ($link)
                $this->Link($this->x, $this->y, $this->GetStringWidth($txt), $this->FontSize, $link);
            /*if($s)
                $this->_out($s);
            $s='';*/
            //�Ѵ�ѡ���͡�ҡ��ͤ��� ���е����ŧ������
            $this->array_th  = substr($txt, 0);
            $i               = 0;
            $this->pointY    = ($this->h - ($y + $dy + .3 * $this->FontSize)) * $k;
            $this->curPointX = ($x + $dx) * $k;
            $this->string_th = '';
            $this->txt_error = 0;

            while ($i < strlen($txt)) {
                //��˹����˹觷��о�����ѡ�������
                if (strpos('��������������', $this->array_th[$i]) !== false) {
                    $pX = $x + $dx;
                    if ($i > 0)
                        $pX += .02 * $this->GetStringWidth($this->array_th[$i - 1]);
                    $this->pointX = $pX * $k;
                    //��Ǩ�ͺ�ѡ�� ��Ѻ���˹���зӡ�þ����
                    $this->_checkT($i);

                    if ($this->txt_error == 0)
                        $this->string_th .= $this->array_th[$i];
                    else {
                        $this->txt_error = 0;
                    }
                } else
                    $this->string_th .= $this->array_th[$i];

                //����͹���˹� x 价���Ƿ��о����Ѵ�
                $x = $x + $this->GetStringWidth($this->array_th[$i]);
                $i++;
            }
            $this->TText($this->curPointX, $this->pointY, $this->string_th);
            /*$this->s_th.=$this->s_hidden.$this->s_error;*/
            //$this->s_th.=$this->s_error;
            if ($this->s_th)
                $this->_out($this->s_th);
        } else
            //�Ӥ����ʴ����������բ�ͤ���
            $this->_out($this->s_th);

        $this->lasth = $h;
        //��Ǩ�ͺ����ҧ���˹觢ͧ����Ѵ�
        if ($ln > 0) {
            //��鹺�÷Ѵ����
            $this->y += $h;
            if ($ln == 1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }

    /****************************************************************************************
     * ��ҹ: called by function MultiCell within this class
     * ��ҧ�ԧ: Function Cell    �ͧ Class FPDF
     * ��÷ӧҹ: ��㹡�þ�����ͤ������к�÷Ѵ�ͧ�͡��� PDF
     * �ٺẺ: MCell (    $w = �������ҧ�ͧCell,
     *                    $h = �����٧�ͧCell,
     *                    $txt = ��ͤ������о����,
     *                    $border = ��˹�����ʴ���鹡�ͺ(0 = ����ʴ�, 1= �ʴ�),
     *                    $ln = ���˹觷������Ѵ仨ҡ����(0 = ���, 1 = ��÷Ѵ�Ѵ�, 2 = ��ҹ��ҧ),
     *                    $align = ���˹觢�ͤ���(L = ����, R = ���, C = ��觡�ҧ, T = ��, B = ��ҧ),
     *                    $fill = ��˹�����ʴ��բͧCell(false = ����ʴ�, true = �ʴ�)
     *                    $link = URL ����ͧ�������ͤ���������§件֧
     *                )
     *****************************************************************************************/
    function MCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $this->checkFill = "";
        $k               = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            //���˹�������ѵ��ѵ
            $x  = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $k));
            }
        }
        //��˹��������ҧ������ҡѺ˹�ҡ�д��
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $this->s_th = '';
        //��˹�����ʴ���鹡�ͺ 4 ��ҹ ����ա�ͺ
        if ($fill || $border == 1) {
            if ($fill)
                $op = ($border == 1) ? 'B' : 'f';
            else
                $op = 'S';
            $this->s_th = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
            if ($op == 'f')
                $this->checkFill = $op;
        }
        //��˹�����ʴ���鹡�ͺ�������
        if (is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if (strpos($border, 'L') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'T') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            if (strpos($border, 'R') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'B') !== false)
                $this->s_th .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
        }

        if ($txt !== '') {
            $x = $this->x;
            $y = $this->y;
            //��˹���èѴ��ͤ�������������дѺ
            if (strpos($align, 'R') !== false)
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            else if (strpos($align, 'C') !== false)
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            else
                $dx = $this->cMargin;
            //��˹���èѴ��ͤ�����������Ǵ��
            if (strpos($align, 'T') !== false)
                $dy = $h - (.7 * $this->k * $this->FontSize);
            else if (strpos($align, 'B') !== false)
                $dy = $h - (.3 * $this->k * $this->FontSize);
            else
                $dy = .5 * $h;
            //��˹���âմ������ͤ���
            if ($this->underline) {
                //��˹��ѹ�֡��ҿԡ
                if ($this->ColorFlag)
                    $this->s_th .= 'q ' . $this->TextColor . ' ';
                //�մ������ͤ���0
                $this->s_th .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
                //��˹��׹��ҡ�ҿԡ
                if ($this->ColorFlag)
                    $this->s_th .= ' Q';
            }
            //��˹���ͤ���������§件֧
            if ($link)
                $this->Link($this->x, $this->y, $this->GetStringWidth($txt), $this->FontSize, $link);
            if ($this->s_th)
                $this->_out($this->s_th);
            $this->s_th = '';
            //�Ѵ�ѡ���͡�ҡ��ͤ��� ���е����ŧ������
            $this->array_th = substr($txt, 0);
            $i              = 0;

            while ($i < strlen($txt)) {
                //��˹����˹觷��о�����ѡ�������
                $this->pointX = ($x + $dx + .02 * $this->GetStringWidth($this->array_th[$i - 1])) * $k;
                $this->pointY = ($this->h - ($y + $dy + .3 * $this->FontSize)) * $k;
                //��Ǩ�ͺ�ѡ�� ��Ѻ���˹���зӡ�þ����
                $this->_checkT($i);
                if ($this->txt_error == 0)
                    $this->TText($this->pointX, $this->pointY, $this->array_th[$i]);
                else {
                    $this->txt_error = 0;
                }
                //��Ǩ�ͺ�������Ţ˹��
                if ($this->array_th[$i] == '{' && $this->array_th[$i + 1] == 'n' && $this->array_th[$i + 2] == 'b' && $this->array_th[$i + 3] == '}')
                    $i = $i + 3;
                //����͹���˹� x 价���Ƿ��о����Ѵ�
                $x = $x + $this->GetStringWidth($this->array_th[$i]);
                $i++;
            }
            $this->_out($this->s_th);
        } else
            //�Ӥ����ʴ����������բ�ͤ���
            $this->_out($this->s_th);

        $this->lasth = $h;
        //��Ǩ�ͺ����ҧ���˹觢ͧ����Ѵ�
        if ($ln > 0) {
            //��鹺�÷Ѵ����
            $this->y += $h;
            if ($ln == 1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }

    /****************************************************************************************
     * ������: Function �ͧ Class FPDF_TH
     * ��ҧ�ԧ: Function MultiCell �ͧ Class FPDF
     * ��÷ӧҹ: ��㹡�þ�����ͤ������º�÷Ѵ�ͧ�͡��� PDF
     * �ٺẺ: MultiCell (    $w = �������ҧ�ͧCell,
     *                        $h = �����٧�ͧCell,
     *                        $txt = ��ͤ������о����,
     *                        $border = ��˹�����ʴ���鹡�ͺ(0 = ����ʴ�, 1= �ʴ�)    ,
     *                        $align = ���˹觢�ͤ���(L = ����, R = ���, C = ��觡�ҧ, J = ��Ш��),
     *                        $fill = ��˹�����ʴ��բͧCell(false = ����ʴ�, true = �ʴ�)
     *                    )
     *****************************************************************************************/
    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
        //Output text with automatic or explicit line breaks
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $b = 0;
        if ($border) {
            if ($border == 1) {
                $border = 'LTRB';
                $b      = 'LRT';
                $b2     = 'LR';
            } else {
                $b2 = '';
                if (strpos($border, 'L') !== false)
                    $b2 .= 'L';
                if (strpos($border, 'R') !== false)
                    $b2 .= 'R';
                $b = (strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
            }
        }
        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $ns  = 0;
        $nl  = 1;
        while ($i < $nb) {
            //Get next character
            $c = $s{$i};
            if ($c == "\n") {
                //Explicit line break
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->MCell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
                $ls  = $l;
                $ns++;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                //Automatic line break
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->MCell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                } else {
                    if ($align == 'J') {
                        $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                    }
                    $this->MCell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $ns  = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
            } else
                $i++;
        }
        //Last chunk
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if ($border && strpos($border, 'B') !== false)
            $b .= 'B';
        $this->MCell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    /********************************************************************************
     * ��ҹ: Function    _checkT �ͧ Class FPDF_TH                                    *
     * ��÷ӧҹ: ��㹾�������ѡ�÷���Ǩ�ͺ����                                    *
     * ������ͧ���: $txt_th = ����ѡ�� 1 ��� ����Ǩ�ͺ����                            *
     *                        $s = ����ѡ��Тͧ⤴ PDF                                *
     *********************************************************************************/
    function TText($pX, $pY, $txt_th) {
        //�Ǩ�ͺ������������
        if ($this->ColorFlag)
            $this->s_th .= ' q ' . $this->TextColor . ' ';
        $txt_th2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt_th)));
        //�кص��˹� ��о�������ѡ��
        $this->s_th .= sprintf(' BT %.2F %.2F Td (%s) Tj ET ', $pX, $pY, $txt_th2);
        if ($this->ColorFlag)
            $this->s_th .= ' Q ';
    }

    /********************************************************************************
     * ��ҹ: Function    Cell �ͧ Class FPDF_TH
     * ��÷ӧҹ: ��㹡�õ�Ǩ�ͺ�ѡ�� ��л�Ѻ���˹觡�͹���зӡ�þ����
     * ������ͧ���: $this->array_th = ������ͧ�ѡ�÷��Ѵ�͡�ҡ��ͤ���
     *                        $i = �ӴѺ�Ѩ�غѹ���������зӡ�õ�Ǩ�ͺ
     *                        $s = ����ѡ��Тͧ⤴ PDF
     *********************************************************************************/
    function _checkT($i) {
        $pointY = $this->pointY;
        $pointX = $this->pointX;
        $nb     = strlen($this->array_th);
        //�Ǩ�ͺ����ʴ��Ţͧ����ѡ���˹����к�
        if ($this->_errorTh($this->array_th[$i]) == 1) {
            //��Ǩ�ͺ����ѡ�á�͹˹�ҹ���������к� ��Ѻ���˹�ŧ
            if (($this->_errorTh($this->array_th[$i - 1]) != 2) && ($this->array_th[$i + 1] != "�")) {
                //��ҵ�ǹ��������͡�������ѵ��
                if ($this->array_th[$i] == "�" || $this->array_th[$i] == "�") {
                    $pointY          = $this->pointY - .2 * $this->FontSize * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ�����������������
                else if ($this->array_th[$i] == '�' || $this->array_th[$i] == '�') {
                    $pointY          = $this->pointY - .23 * $this->FontSize * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ���繡��ѹ��
                else {
                    $pointY          = $this->pointY - .17 * $this->FontSize * $this->k;
                    $this->txt_error = 1;
                }
            }

            //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ���繵���ѡ���ҧ��Ǻ�
            if ($this->_errorTh($this->array_th[$i - 1]) == 3) {
                //��ҵ�ǹ��������͡�������ѵ��
                if ($this->array_th[$i] == "�" || $this->array_th[$i] == "�") {
                    $pointX          = $this->pointX - .17 * $this->GetStringWidth($this->array_th[$i - 1]) * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ�����������������
                else if ($this->array_th[$i] == '�' || $this->array_th[$i] == '�') {
                    $pointX          = $this->pointX - .25 * $this->GetStringWidth($this->array_th[$i - 1]) * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ���繡��ѹ��
                else {
                    $pointX          = $this->pointX - .4 * $this->GetStringWidth($this->array_th[$i - 1]) * $this->k;
                    $this->txt_error = 1;
                }
            }

            //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ����ա�繵���ѡ���ҧ��Ǻ�
            if ($i >= 2 && $this->_errorTh($this->array_th[$i - 2]) == 3) {
                //��ҵ�ǹ��������͡�������ѵ��
                if ($this->array_th[$i] == "�" || $this->array_th[$i] == "�") {
                    $pointX          = $this->pointX - .17 * $this->GetStringWidth($this->array_th[$i - 2]) * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ�����������������
                else if ($this->array_th[$i] == '�' || $this->array_th[$i] == '�') {
                    $pointX          = $this->pointX - .25 * $this->GetStringWidth($this->array_th[$i - 2]) * $this->k;
                    $this->txt_error = 1;
                } //��ҵ�ǹ���繡��ѹ��
                else {
                    $pointX          = $this->pointX - .4 * $this->GetStringWidth($this->array_th[$i - 2]) * $this->k;
                    $this->txt_error = 1;
                }
            }
        }
        //����õ�Ǩ�ͺ����ѡ���˹����к�

        //�Ǩ�ͺ����ʴ��Ţͧ����ѡ����к�
        else if ($this->_errorTh($this->array_th[$i]) == 2) {
            //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ���繵���ѡ���ҧ��Ǻ�
            if ($this->_errorTh($this->array_th[$i - 1]) == 3) {
                $pointX          = $this->pointX - .17 * $this->GetStringWidth($this->array_th[$i - 1]) * $this->k;
                $this->txt_error = 1;
            }
            //��ҵ�ǹ���������
            if ($this->array_th[$i] == "�")
                //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ���繵���ѡ���ҧ��Ǻ�
                if ($this->_errorTh($this->array_th[$i - 2]) == 3) {
                    $pointX          = $this->pointX - .17 * $this->GetStringWidth($this->array_th[$i - 2]) * $this->k;
                    $this->txt_error = 1;
                }
        }
        //����õ�Ǩ�ͺ����ѡ����к�

        //�Ǩ�ͺ����ʴ��Ţͧ����ѡ�������ҧ
        else if ($this->_errorTh($this->array_th[$i]) == 6) {
            //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ���繵���ѡ�� �. �Ѻ �.
            if ($this->_errorTh($this->array_th[$i - 1]) == 5) {    //$this->string_th		$this->curPointX
                $this->TText($this->curPointX, $this->pointY, $this->string_th);
                $this->string_th = '';
                $this->curPointX = $this->pointX;

                if ($this->checkFill == 'f')
                    $this->s_th .= ' q ';
                else
                    $this->s_th .= ' q 1 g ';
                //���ҧ����������任Դ���ҹ��ҧ�ͧ����ѡ�� �. �Ѻ �. $s.
                $this->s_th .= sprintf('%.2F %.2F %.2F %.2F re f ', $this->pointX - $this->GetStringWidth($this->array_th[$i - 1]) * $this->k, $this->pointY - .27 * $this->FontSize * $this->k, .9 * $this->GetStringWidth($this->array_th[$i - 1]) * $this->k, .25 * $this->FontSize * $this->k);
                $this->s_th .= ' Q ';

                $this->txt_error = 1;
            } //��Ǩ�ͺ����ѡ�õ�ǡ�͹˹�ҹ�����ѡ��� �. �Ѻ �.
            else if ($this->_errorTh($this->array_th[$i - 1]) == 4) {
                $pointY          = $this->pointY - .25 * $this->FontSize * $this->k;
                $this->txt_error = 1;
            }
            //����õ�Ǩ�ͺ����ѡ�������ҧ
        }
        //����õ�Ǩ�ͺ����ѡ��������ҧ

        if ($this->txt_error == 1)
            $this->TText($pointX, $pointY, $this->array_th[$i]);
    }

    /********************************************************************************
     * ��ҹ: Function    _checkT �ͧ Class FPDF_TH
     * ��÷ӧҹ: ��㹡�õ�Ǩ�ͺ�ѡ�÷���Ҩ�з�����Դ��þ������Դ��Ҵ
     * ������ͧ���: $char_th = ����ѡ�÷�����㹡�����º��º
     *********************************************************************************/
    function _errorTh($char_th) {
        $txt_error = 0;
        //����ѡ�ú�-��
        if (($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�'))
            $txt_error = 1;
        //����ѡ�ú�
        else if (($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�') || ($char_th == '�'))
            $txt_error = 2;
        //����ѡ�á�ҧ-��
        else if (($char_th == '�') || ($char_th == '�') || ($char_th == '�'))
            $txt_error = 3;
        //����ѡ�á�ҧ-��ҧ
        else if (($char_th == '�') || ($char_th == '�'))
            $txt_error = 4;
        //����ѡ�á�ҧ-��ҧ
        else if (($char_th == '�') || ($char_th == '�'))
            $txt_error = 5;
        //����ѡ�������ҧ
        else if (($char_th == '�') || ($char_th == '�'))
            $txt_error = 6;
        else
            $txt_error = 0;
        return $txt_error;
    }
//End of class
}

?>
