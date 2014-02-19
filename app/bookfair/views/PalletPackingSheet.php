<?php

namespace Bookfair;

use Auth;
use DateTime;
use TCPDF;
use URL;

/*
 * Produces the packing sheets used to track the number of boxes in each category
 * that have been packed onto pallets at the Warehouse. Sheets are divided into
 * Sections and grouped according to Pallet Assignments, and show a number of 
 * tickboxes equal to the allocated quota for the current bookfair.
 */

class PalletPackingSheet extends TCPDF {

    //TODO: New Page on Pallet ID change. 
    //TODO: Run sections within the same Pallet ID together on one page.
    //TOOD: Redo using writeCell and MultiCell for better pagination control
    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_pageLen = 36;
    private $_lineStyle;
    private $_fair;
    private $_prevSection;
    private $_curSection = null;
    private $_box = '';
    private $_box5 = '';
    private $_box20 = '';

    public function Header() {
        $hdr1 = $this->_fair->season . " Book Fair, "
                . $this->_startDate->format('F Y')
                . "\n" . "Pallet Tally Sheet";
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
        $this->Ln(1);
        $this->SetY(15);
    }

    private function initStrings() {
        $this->_box = $this->unichr(9633);
        $this->_box5 = str_repeat($this->unichr(9633), 5);
        $this->_box20 = $this->_box5 . ' ' . $this->_box5 . ' ' . $this->_box5 . ' ' . $this->_box5;
    }

    public function __construct($bookfair) {
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->SetCreator(PDF_CREATOR);
        $this->SetTitle('Pallet Packing Sheet');
        $this->SetSubject('Warehouse Stock Control');
        $this->SetAuthor(Auth::user()->name());
        $this->initStrings();
        $this->setMargins(8, PDF_MARGIN_TOP, 8);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->_fair = & $bookfair;
        $this->_startDate = new DateTime($bookfair->start_date);
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render($bookfair->targets);
    }

    private function table_open($section) {
        $html = '<table cellpadding="3" cellspacing="0">';
        $html .= '<thead><tr nobr="true"><td class="heading" colspan="6">' . $section . '</td></tr></thead>';
        return $html;
    }

    private function table_row(array $cell) {
        if (count($cell) === 1) {
            $row = $cell[0][0] . '</tr><tr>' . $cell[0][1];
        } else {
            $row = $cell[0][0] . $cell[1][0] . '</tr><tr>' . $cell[0][1] . $cell[1][1];
        }
        return '<tr nobr="true">' . $row . '</tr>';
    }

    private function heading($cat) {
        $hdr = '<td width="22mm" align="center">'
                . '<span class="label">' . htmlentities($cat->label) . '</span><br />'
                . '<span class="target">[Target: ' . $cat->target . ']</span></td>'
                . '<td class="category" width="75mm">'
                . '<span class="category">' . htmlentities($cat->name) . '</span></td>';
        return $hdr;
    }

    private function tickboxes($count) {
        $ticks = '';
        $c = 0;
        while ($c < $count) {
            if (($count - $c) >= 20) {
                $ticks .= $this->_box20 . '<br />';
                $c +=20;
            } else {
                if (($count - $c) >= 5) {
                    $ticks .= $this->_box5 . ' ';
                    $c += 5;
                } else {
                    $ticks .= $this->_box;
                    $c++;
                }
            }
        }
        return '<td colspan="2" class="counter">'
                . '<p class="counter">' . $ticks . '</p></td>';
    }

    public function Render($targets) {
        $this->SetFont('dejavusans', '', 12, '', false);
        $this->SetCellPadding(0);
        $tagvs = array(
            'p' => array(
                0 => array(
                    'h' => 0,
                    'n' => 0),
                1 => array(
                    'h' => 0,
                    'n' => 0)
            )
        );
        $this->setHtmlVSpace($tagvs);
        $this->setCellHeightRatio(1.25);
        $this->AddPage();
        $this->setY(20);
        $col = 0;
        $html = '';
        $cell = array();
        foreach ($targets as $row) {
            if ($row->section->name <> $this->_curSection) {
                if ($col > 0) {
                    $html .= $this->table_row($cell);
                }
                if (!is_null($this->_curSection)) {
                    $html .= '</table>';
                    $this->writeHTML($html, true, false, false, false, '');
                    $this->AddPage();
                    $this->setY(20);
                }
                $this->_curSection = $row->section->name;
                $html = $this->styleSheet()
                        . $this->table_open($row->section->name);
                $col = 0;
            }
            $cell[$col] = array($this->heading($row), $this->tickboxes($row->target));
            if ($col === 1) {
                $col = 0;
                $html .= $this->table_row($cell);
                $cell = array();
            } else {
                $col++;
            }
        }
        $html .= '</table>';
        $this->writeHTML($html, true, false, false, false, '');
    }

    private function styleSheet() {
        $style = <<<EOD
<style>
    table {
        border-top: 0.5px solid black;
        border-left: 0.5px solid black;
    }
    td.heading {
        color: white;
        background-color: black;
        font-size: 18pt; 
        font-weight: bold;
        border-right: 0.5px solid black;
        border-bottom: 0.5px solid black;
    }
    td.category {
         border-right: 0.5px solid black;
    }
    td.counter {
         border-right: 0.5px solid black;
         border-bottom: 0.5px solid black;
    }
    span.label {
         font-weight: bold; 
         font-size: 14pt; 
    }
    span.category {
         font-weight: normal; 
         font-size: 12pt; 
    }
    span.target {
         font-weight: normal; 
         font-size: 8pt; 
    }
    p.counter {
        letter-spacing: -0.254mm; 
        font-weight: normal; 
        font-size: 14pt;
    }
</style>
                
EOD;
        return $style;
    }

}
