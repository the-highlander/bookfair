<?php

//TODO: Required by March 2014

namespace Bookfair;

use Auth;
use DateTime;
use Str;
use TCPDF;

/*
  Produces the control sheets used when unpacking pallets at the book fair to show how many boxes
  in each category will go on top of the table and how many go underneath.
 */

class AllocationDropSheets extends TCPDF {

    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;
    private $_fair;
    private $_logo;
    private $_startDate;
    private $_header;
    private $_footer;

    public function __construct($bookfair) {
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->_fair = & $bookfair;
        $this->_startDate = new DateTime($bookfair->start_date);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->SetCreator(PDF_CREATOR);        
        $this->SetSubject('Bookfair Set Up Stock Allocation');
        $this->SetAuthor(Auth::user()->name());
        $this->setMargins(10, PDF_MARGIN_TOP, 10);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->SetAutoPageBreak(false);
        $this->SetTitle($this->_startDate->format('F Y') . ' Allocation Drop Sheet');
        $this->Render($bookfair->allocations);        
    }
    
    public function Header() {
        $this->SetFont('helvetica', 'N', 10);
        $this->Cell(0, 0, $this->_header['section'], 0, 0, 'L', false, '', 0, false, 'T', 'T');
        $this->Cell(0, 0, $this->_header['group'], 0, 1, 'R', false, '', 0, false, 'T', 'T');
    }

    public function Footer() {
        $cur_y = $this->y;
        $this->SetFont('helvetica', 'N', 10);
        if (empty($this->pagegroups)) {
            $pagenumtxt = 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages();
        } else {
            $pagenumtxt = 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias();
        }
        $this->SetY($cur_y);
        $this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
        $this->Cell(0, 0, $this->_startDate->format('F Y'), 0, 0, 'R', false, '', 0, false, 'T', 'M');
        //$this->Cell(0, 0, $this->_fair->footer['tables'] . ' ' . Str::plural('Table', $this->_footer['tables']) . ' In Group', 0, 0, 'R', false, '', 0, false, 'T', 'M');
    }

    public function boxAndLines() {
        $this->SetDrawColor(84, 141, 212);
        $this->Line(10, 135, 200, 135);
        $this->Line(10, 286, 200, 286);
        $this->RoundedRect(27, 150, 40, 25, 1, '1111');
        $this->RoundedRect(27, 190, 40, 25, 1, '1111');
        $this->RoundedRect(27, 230, 40, 25, 1, '1111');
    }
    
    public function newPage($tablegroup, $section) {
        if (!isset($this->_header)) {
            $this->_header = array(
                'group'=>is_null($tablegroup) ? "" : $tablegroup->name,
                'section'=>$section);
            $this->startPageGroup();
            $this->_footer = array(
                'group'=>is_null($tablegroup) ? "" : $tablegroup->name,
                'count'=>is_null($tablegroup) ? 0  : $tablegroup->tables);
        } else {
           $this->_header['group'] = is_null($tablegroup) ? "" : $tablegroup->name;
           $this->_header['section'] = $section;
           if ($this->_header['group'] <> $this->_footer['group']) {
             $this->startPageGroup();
           }
        }
        $this->AddPage();            
        $this->boxAndLines();
        $this->_footer['group'] = is_null($tablegroup) ? "" : $tablegroup->name;
        $this->_footer['tables'] = is_null($tablegroup) ? 0 : $tablegroup->tables;
    }

    public function category ($label, $name) {
        $this->SetFont('helvetica', 'B', 96);
        $this->SetAbsXY(10, 25);
        $this->Cell(0, 0, $label, 0, 1, 'C', false, '', 0, false, 'T', 'M');
        $this->SetFont('helvetica', 'B', 36);
        $this->SetAbsXY(10, 68);
        $this->MultiCell(0, 0, $name, 0, 'C', false, 1);
    }

    private function fmtDecimal($num, $unit = null) {
      if (is_null($unit)) {
          return floor($num) == $num ? number_format($num, 0) : number_format($num, 2);
      } else {
          return (floor($num) == $num ? number_format($num, 0) : number_format($num, 2)) . " " . Str::plural($unit, $num);
      }
    }
    
    public function allocation ($tables) {            
        $this->SetFont('helvetica', 'BI', 36);
        $this->SetAbsXY(10, 115);
        $txt = $this->fmtDecimal($tables, 'Table');
        $this->Cell(0, 0, $txt, 0, 0, 'C', false, '', 0, false, 'T', 'T');
    }

    public function Render($allocations) {
        foreach ($allocations as $allocation) {
            $this->newPage($allocation->tablegroup, $allocation->section->name);
            $this->category($allocation->label, $allocation->name);
            $this->allocation($allocation->allocated);
            //todo: These should be stored in the record. allocated display/reserver versus actual start display
            $start_display = floor($allocation->loading * $allocation->allocated);
            $start_reserve = $allocation->packed - $start_display;
            $this->SetFont('helvetica', 'B', 56);
            $this->SetAbsXY(27, 162);
            $this->Cell(40, 25, $start_display, 0, 0, 'C', false, '', 0, false, 'C', 'M');
            $this->SetFont('helvetica', 'I', 48);
            $this->SetAbsXY(77, 162);
            $this->Cell(0, 25, 'On Table', 0, 1, 'L', false, '', 0, false, 'C', 'M');
            $this->SetFont('helvetica', 'B', 56);
            $this->SetAbsXY(27, 202);
            $this->Cell(40, 25, $start_reserve, 0, 0, 'C', false, '', 0, false, 'C', 'M');
            $this->SetFont('helvetica', 'I', 48);
            $this->SetAbsXY(77, 202);
            $this->Cell(0, 25, 'Under Table', 0, 1, 'L', false, '', 0, false, 'C', 'M');
            $this->SetFont('helvetica', 'I', 48);
            $this->SetAbsXY(77, 242);
            $this->Cell(0, 25, 'Total', 0, 1, 'L', false, '', 0, false, 'C', 'M');
            $this->SetTextColor(84, 141, 212);
            $this->Text(40, 172, '+');
            $this->Text(40, 212, '=');
            $this->SetTextColor(0, 0, 0);
        }
            
    }

}
