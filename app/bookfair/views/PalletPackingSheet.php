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
        $this->SetCellPaddings(1, 1, 0, 1);
        $bdr = 1;
        $this->MultiCell(0, 8, $this->_header['section'], $bdr, 'L', true, 1);
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
        $this->setMargins(8, PDF_MARGIN_TOP, 8);
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
           $this->_header['pallet'] = $pallet->name;
           $this->_header['section'] = $section->name;
           if ($this->_header['pallet'] <> $this->_footer['pallet']) {
             $this->startPageGroup();
           }
        }
        $this->AddPage();
        $this->_footer['pallet'] = $pallet->name;
    }    


    private function newtickboxes($boxes, $checks = 0) {
        $ticks = '';
        for ($i = 1; $i <= $boxes; $i++) {
            $ticks .= ($i > $checks) ? $this->unichr(9633) : $this->unichr(9632);
            if ($i%20 == 0 && $i < $boxes) {
                $ticks .= "\n";
            } else {
                if ($i%5 == 0) { $ticks .= '  '; }
            }
        }
        return $ticks;
    }
    
    public function formatCell( $target = null ) {
        if (!is_null($target)) {
            return array(
                'label'   => $target->label,
                'heading' => $target->name, 
                'boxes'   => $target->target,
                'ticks'   => $this->newtickboxes($target->target, $target->packed));
        } else {
            return array(
                'label'   => '',
                'heading' => '',
                'boxes'   => 0,
                'ticks'   => '');                
        }
    }
    
    public function printLabel($text, $ln = 0) {
        $bdr = 'TL'; $ln = 0;
        if (strlen($text) < 6 ) {
            $this->SetFont('dejavusans', 'B', 12, '', false);
        } else {
            $this->SetFont('dejavusans', 'B', 11-(strlen($text)-4), '', false);
        }
        $this->SetCellPaddings(2, 2, 0, 0);
        $this->MultiCell(18, 9, $text, $bdr, 'L', false, $ln, '', '', true, 0, false, false, 16, 'T', false);
        //$this->Cell(18, 10, $text, $bdr, $ln,  'L', false, '', 0, false, 'T', 'M');        
    }
    
    public function printHeading($text, $ln = 0) {
        $bdr = 'TR'; $stretch = 1;
        $this->SetFont('dejavusans', 'N', 12, '', false);
        $this->SetCellPaddings(0, 2, 0, 0);
        //$this->Cell(80, 0, $text, $bdr, $ln,  'L', false, '', 1, false, 'T', 'M');        
        $this->MultiCell(79, 9, $text, $bdr, 'L', false, $ln, '', '', true, $stretch, false, false, 16, 'T', false);
    }
    
    public function printCheckboxes($text, $h, $ln = 0) {
        $bdr = ($ln == 0) ? 'BLR' : 'RB';
        $this->SetCellPaddings(8, 0, 0, 0);
        $this->SetFontSize(13.5);
        $this->SetFontSpacing(-0.5);
        $this->MultiCell(97, $h, $text, $bdr, 'L', false, $ln, '', '', true, 0, false, false, 0, 'M', false);
        $this->SetFontSpacing(0);
    }    
    
    public function rowheight($boxes) {
        // Typical row has 10mm for Heading, 6mm per row of checkboxes and 
        // 4mm whitespace at the bottom of the box.
        return 6 * ceil($boxes /20) + 4;
    }
    
    public function printRow($cols) {        
        if (count($cols) == 1) {
            $cols[] = $this->formatCell();
        }
        $height = $this->rowheight(max($cols[0]['boxes'], $cols[1]['boxes']));
        if (($this->getY() + $height) > 255) {
            $this->AddPage();
        }
        $this->printLabel($cols[0]['label']);
        $this->printHeading($cols[0]['heading']);
        $this->printLabel($cols[1]['label']);
        $this->printHeading($cols[1]['heading'], 1);
        $this->printCheckboxes($cols[0]['ticks'], $height);
        $this->printCheckboxes($cols[1]['ticks'], $height, 1);
        $this->Line(10, $this->getY(), 202, $this->getY());
    }    

    public function Render($targets) {
        $this->SetFont('dejavusans', '', 12, '', false);
        $cells = array(); $col = 0; $pallet = 0; $section = 0;
        foreach($targets as $target) {
            if (isset($target->pallet)) {
                if ($target->pallet_id <> $pallet || $target->category->section_id <> $section) {
                    if ($col > 0) {
                        $this->printRow($cells);
                    }
                    $this->newPage($target->pallet, $target->category->section);
                    $col = 0;
                    $cells = array();                           
                    $pallet = $target->pallet_id;
                    $section = $target->category->section_id;
                }
                $cells[$col] = $this->formatCell($target);
                if ($col == 1) {
                    $this->printRow($cells);
                    $cells = array();                           
                }
                $col = ($col == 1) ? 0 : 1;
            }
        }
    }  

}
