<?php
namespace Bookfair;

use Auth;
use DateTime;
use TCPDF;

/*
 * Produces the packing sheets used to track the number of boxes in each category
 * that have been packed onto pallets at the Warehouse. Sheets are divided into
 * Sections and grouped according to Pallet Assignments, and show a number of 
 * tickboxes equal to the allocated quota for the current bookfair.
 */
class PalletPackingSheet extends TCPDF {

    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_pageLen = 36;
    private $_BoxesWidth = 0;
    private $_LabelRowHeight = 6;
    private $_LabelWidth = 24;
    private $_TitleWidth = 170;
    private $_LeftMargin = 8;
    private $_RightMargin = 8;
    private $_TicksPerLine = 25;
    private $_TicksPerGroup = 5;
    private $_LinesPerCell = 2;
    private $_TicksCellHeight = 14;
    private $_TicksPerCell = 50; // LinesPerCell * TicksPerLine
    private $_lineStyle;
    private $_fair;
    private $_prevSection;
    private $_curSection = null;
    private $_box = '';
    private $_box5 = '';
    private $_box20 = '';

    public function Header() {
        $this->SetFont('dejavusans', 'B', 22, '', false);
        $hdr1 = $this->_header['pallet'] . ' Pallet';
        $this->MultiCell(100, 12, $hdr1, 0, 'L', false, 0);
        $this->SetFont('dejavusans', 'N', 12, '', false);
        $hdr2 = 'Packing Sheet' . "\n" . $this->_fair->season . ' Book Fair';
        $this->MultiCell(0, 12, $hdr2, 0, 'R', false, 1);
        $this->SetFont('dejavusans', 'B', 16, '', false);
        $this->SetFillColor(91, 43, 0, 45);
        $this->SetTextColor(0, 0, 0, 0);
        $this->SetCellPaddings(1, 1, 0, 2);
        $bdr = 1;
        $this->MultiCell(0, 0, $this->_header['section'], 1, 'L', true, 1);
        $this->SetTextColor(255, 255, 255, 0);
    }

    public function Footer() {
        $cur_y = $this->y;
        $this->SetFont('helvetica', 'N', 10);
        $pagenumtxt = $this->_footer['pallet'] . ' Pallet ';
        if (empty($this->pagegroups)) {
            $pagenumtxt .= 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages();
        } else {
            $pagenumtxt .= 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias();
        }
        $this->SetY($cur_y);
        $this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
        $this->Cell(0, 0, $this->_startDate->format('F Y'), 0, 0, 'R', false, '', 0, false, 'T', 'M');
        //$this->Cell(0, 0, $this->_fair->footer['tables'] . ' ' . Str::plural('Table', $this->_footer['tables']) . ' In Group', 0, 0, 'R', false, '', 0, false, 'T', 'M');
    }

    public function __construct($bookfair) {
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->SetCreator(PDF_CREATOR);
        $this->SetTitle('Pallet Packing Sheet');
        $this->SetSubject('Warehouse Stock Control');
        $this->SetAuthor(Auth::user()->name());
        $this->setMargins($this->_LeftMargin, PDF_MARGIN_TOP, $this->_RightMargin);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->_fair = & $bookfair;
        $this->_startDate = new DateTime($bookfair->start_date);
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render($bookfair->targets);
    }

    public function newPage($pallet, $section) {
        if (!isset($this->_header)) {
            $this->_header = array(
                'pallet'=>$pallet->name,
                'section'=>$section->name);
            $this->startPageGroup();
            $this->_footer = array('pallet'=>$pallet->name);
        } else {
           $this->Line($this->_LeftMargin, $this->getY(), 202, $this->getY());
           $this->_header['pallet'] = $pallet->name;
           $this->_header['section'] = $section->name;
           if ($this->_header['pallet'] <> $this->_footer['pallet']) {
             $this->startPageGroup();
           }
        }
        $this->AddPage();
        $this->_footer['pallet'] = $pallet->name;
    }    


    private function tickboxes($boxes, $checks = 0) {
        $cell = array();
        $ticks = '';
        for ($i = 1; $i <= $boxes; $i++) {
            $ticks .= ($i > $checks) ? $this->unichr(9744) : $this->unichr(9745);
            //$ticks .= ($i > $checks) ? $this->unichr(9633) : $this->unichr(9632);
            if ($i%$this->_TicksPerLine === 0 && $i < $boxes) {
                $ticks .= "\n";
                if ($i%$this->_TicksPerCell === 0 && $i < $boxes) {
                    $cell[] = $ticks;
                    $ticks = '';
                }
            } else {
                if ($i%$this->_TicksPerGroup === 0) { $ticks .= '  '; }
            }
        }
        if (!$ticks == '') {
            $cell[] = $ticks;
        }
        return $cell;
    }
          
    public function printCheckboxes($text, $h = 14) {
        $this->SetCellPaddings(2, 0, 0, 0);
        $this->SetFontSize(20);
        $this->SetFontSpacing(-0.8);
        $this->SetCellHeightRatio(0.85);
        $this->MultiCell($this->_BoxesWidth, $h, $text, 'LR', 'L', false, 1, '', '', true, 0, false, false, 0, 'M', false);
        $this->SetFontSpacing(0);
        $this->SetCellHeightRatio(1.25);
    }    
    
    public function rowheight($boxes) {
        // Typical row has 10mm for Heading, 6mm per row of checkboxes and 
        // 4mm whitespace at the bottom of the box.
        return 4 + $this->_LabelRowHeight * (1 + ceil($boxes / $this->_TicksPerLine));
    }  

    private function printTarget($target) {
        $c = ($target->target % $this->_TicksPerCell);
        $h = (($target->target === 0) || $c === 0 || ($c > $this->_TicksPerLine)) ? $this->_TicksCellHeight : ($this->_TicksCellHeight / 2);
        $text = 'Target: ' . $target->target;
        $this->SetCellHeightRatio(0.85);
        $this->SetCellPaddings(2, 2, 2, 2);
        $this->SetFontSize(8);
        $this->MultiCell($this->_BoxesWidth, $h, $text, 'LR', 'R', false, 1, '', '', true, 0, false, true, $h, 'B', false);
        $this->SetCellHeightRatio(1.25);
    }
    
    
    private function printCategory($target) {
// MultiCell($w,$h,$txt,$border = 0,$align = 'J',$fill = false,$ln = 1,$x = '',$y = '',$reseth = true,$stretch = 0,$ishtml = false,$autopadding = true,$maxh = 0,$valign = 'T',$fitcell = false )
        $h = $this->_LabelRowHeight;
        $this->SetCellPaddings(2, 2, 0, 3);
        $this->SetFont('dejavusans', 'B', 12, '', false);
        $this->MultiCell($this->_LabelWidth, $h, $target->label, 'L', 'L', false, 0, '', '', true, 0, false, true, 0, 'T', false);
        $this->SetCellPaddings(0, 2, 0, 3);
        $this->SetFont('dejavusans', 'N', 12, '', false);
        $this->MultiCell($this->_TitleWidth, $h, $target->name, 'R', 'L', false, 1, '', '', true, 0, false, true, 0, 'T', false);
         
        $boxes = $this->tickboxes($target->target, $target->packed);        
        foreach ($boxes as $line) {
            $this->printCheckboxes($line);
        }
        $this->printTarget($target);
        $this->Line($this->_LeftMargin, $this->getY(), 202, $this->getY());       
    }
    
    public function Render($targets) {
        $this->SetFont('dejavusans', '', 12, '', false);
        $pallet = 0; $section = 0;
        foreach($targets as $target) {
            if (isset($target->pallet)) {
                if ($target->pallet_id <> $pallet || $target->category->section_id <> $section) {
                    $this->newPage($target->pallet, $target->category->section);
                    $pallet = $target->pallet_id;
                    $section = $target->category->section_id;
                } else {
                    $height = $this->rowheight($target->target);
                    if (($this->getY() + $height) > 255) {
                        $this->AddPage();
                    }                
                }        
                $this->printCategory($target);
            }
        }
    }  

}
