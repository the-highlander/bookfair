<?php

namespace Bookfair;

use TCPDF;
use DateTime;
use URL;
use Str;

class SalesDataCaptureForm extends TCPDF {

    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_prevSection;
    private $_curSection = "";
    private $_measure = 'table';
    private $_fair;
    private $_allocations;
    private $_lineStyle;
    private $_colhead = array(
        "offer" => array(
            "table" => "Num. Full Tables",
            "box" => "Boxes On Table",
            "percent" => "% On Table"),
        "reserve" => array(
            "table" => "Boxes Under Table",
            "box" => "Boxes Under Table",
            "percent" => "% Under Table"));

    public function __construct($bookfair, $allocations) {
        // Always generate this form using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->_fair = & $bookfair;
        $this->_allocations = & $allocations;
        $this->_startDate = new DateTime($bookfair->start_date);
        $this->SetAutoPageBreak(false);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render();
    }

    public function Header() {
        //URL::to('user/profile'); 
        $image_file = URL::asset('bookfair/img/logo.gif');
        $this->Image($image_file, 10, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(91, 43, 0, 45);
        $hdr1 = $this->_fair->season . " Book Fair, "
                . $this->_startDate->format('F Y')
                . "\n" . "Stock Tally Sheet";
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
        $this->SetFont('helvetica', 'B', 18);
        $this->setCellPaddings(0, 0, 0, 2);
        $this->Cell(0, 15, $this->_curSection, 0, 1, 'L', false, '', 0, false, 'T', 'B');
        $this->setCellPaddings(0, 0, 0, 0);
        $this->SetTextColor(0, 0, 0, 0);
        $this->SetFillColor(91, 43, 0, 45);
        $this->SetFont('helvetica', '', 11);
        $bdr = array('LTRB' => $this->_lineStyle);
        $this->Cell(40, 7, '', 0, 0, 'C', false, '', 0, false, 'T', 'B');
        $this->Cell(30, 7, 'Start of Fair', $bdr, 0, 'C', true, '', 0, false, 'T', 'C');
        $this->Cell(30, 7, 'Friday Close', $bdr, 0, 'C', true, '', 0, false, 'T', 'C');
        $this->Cell(30, 7, 'Saturday Close', $bdr, 0, 'C', true, '', 0, false, 'T', 'C');
        $this->Cell(30, 7, 'Start Bag Sale', $bdr, 0, 'C', true, '', 0, false, 'T', 'C');
        $this->Cell(30, 7, 'End of Fair', $bdr, 0, 'C', true, '', 0, false, 'TC', 'C');
        $this->Ln();
        $this->MultiCell(25, 16, "\n" . "\n" . ' Category', $bdr, 'L', true, 0, '', '', true, 0, false, true, 0, 'M');
        $this->MultiCell(15, 16, "\n" . 'Total Boxes', $bdr, 'C', true, 0, '', '', true, 0, false, true, 0, 'M');
        for ($i = 1; $i <= 5; $i++) {
            $this->MultiCell(15, 16, $this->_colhead["offer"][$this->_measure], $bdr, 'C', true, 0);
            $this->MultiCell(15, 16, $this->_colhead["reserve"][$this->_measure], $bdr, 'C', true, 0);
        }
        $this->Ln();
        $this->SetLineStyle($this->_lineStyle);
        for ($i = 0; $i < 5; $i++) {
            $this->Line(50 + $i * 30, 60, 50 + $i * 30, 282);
        }
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $border = array('T' => $this->_lineStyle);
        $this->Cell(50, 10, 'Page ' . $this->getPageNumGroupAlias() . ' of ' . $this->getPageGroupAlias(), $border, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 10, $this->_prevSection, $border, false, 'R', 0, '', 0, false, 'T', 'M');
    }

    private function fmtDecimal($num, $unit = null) {
      if (is_null($unit)) {
          return floor($num) == $num ? number_format($num, 0) : number_format($num, 2);
      } else {
          return (floor($num) == $num ? number_format($num, 0) : number_format($num, 2)) . " " . Str::plural($unit, $num);
      }
    }
    
    private function showCell($num) {
        $text = '';
        if (!is_null($num)) {
            $text = $num === 0 ? '0' : $this->fmtDecimal($num);
        }
        $this->Cell(13, 8, $text, 1, 0, 'C', false, '', 0, false, 'T', 'C');        
    }
    
    private function formatAllocations($allocate, $packed, $allocations, $children) {
        $tables = 0;
        $groups = 0;
        foreach ($children as $child) {
            if ($child->allocate) {
                $tables += $child->allocations->sum('tables');
            }
            $packed += $child->packed;
        }
        if ($allocate) {
            $groups = count($allocations);
            if ($groups > 0) {
                $tables = $allocations->sum('tables');
            }
        }
        if ($tables > 0) {
            $text = $this->fmtDecimal($tables, 'Table') 
            . (($groups > 1) ? ' in ' . $this->fmtDecimal($groups, 'Table Group') : '');
            // . $this->fmtDecimal($packed, 'box') . ' ' .             
            //TODO: Need delivered not packed, but you won't have this until the
            //      drop sheets have been input. Requirement: Drop sheet data entry
            //      page to match the drop sheet layout. (web accessible)
        }
        return  $text;            
    }
    
    public function Render() {
        $rowcount = 0;
        foreach ($this->_allocations as $row) {
            if ($row->category->section->name <> $this->_curSection) {
                $this->_curSection = $row->category->section->name;
                $this->_measure = $row->measure;
                $this->startPageGroup();
                $this->AddPage();
                $this->SetY(60);
                $this->_prevSection = $this->_curSection;
                $rowcount = 0;
            }
            if ($rowcount == 13) {
                $this->AddPage();
                $this->SetY(60);
                $rowcount = 0;
            }
            // set cell padding
            $this->setCellPaddings(0, 0, 0, 0);
            $this->setCellMargins(0, 1, 0, 1);
            $this->writeHTMLCell(0, 0, '', '', '<span color="#548DD4"><b>' 
                    . $row->name . '</b></span>&nbsp;&nbsp;<span style="font-size:9px;color:#000;">(' 
                    . $this->formatAllocations($row->allocate, $row->packed, $row->allocations, $row->children)
                    . ')</span>', 0, 1, false, true, 'L', false);
            $this->setCellPaddings(1, 1, 1, 1);
            $this->setCellMargins(1, 0, 1, 5);
            $this->Cell(23, 8, $row->label, 0, 0, 'C', false, '', 0, false, 'T', 'C');
            $this->showCell($row->delivered);
            $this->showCell($row->start_display);
            $this->showCell($row->start_reserve);
            $this->showCell($row->fri_end_display);
            $this->showCell($row->fri_end_reserve);
            $this->showCell($row->sat_end_display);
            $this->showCell($row->sat_end_reserve);
            $this->showCell($row->sun_end_display);
            $this->showCell($row->sun_end_reserve);
            $this->showCell($row->end_display);
            $this->showCell($row->end_reserve);
            $this->Ln();
            $rowcount++;
        }
        // Update variables for the final page footer
        $this->_prevSection = $this->_curSection;
    }

}

?>