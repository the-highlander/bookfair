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

    public function __construct($bookfair, $allocations) {
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
        $this->Render($allocations);        
    }
    
    public function Header() {
        $this->SetFont('helvetica', 'N', 10);
        $this->Cell(0, 0, $this->_header['section'], 0, 0, 'L', false, '', 0, false, 'T', 'T');
        $this->Cell(0, 0, 'Table Group ' . $this->_header['group'], 0, 1, 'R', false, '', 0, false, 'T', 'T');
    }

    public function Footer() {
        $cur_y = $this->y;
        $this->SetFont('helvetica', 'N', 10);
        if (empty($this->pagegroups)) {
            $pagenumtxt = 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages();
        } else {
            $pagenumtxt = 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias();
        }
        $pagenumtxt .= ' for Table Group ' . $this->_footer['group'];
        $this->SetY($cur_y);
        $this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
        $this->Cell(0, 0, $this->_startDate->format('F Y'), 0, 0, 'R', false, '', 0, false, 'T', 'M');
    }

    public function boxAndLines() {
        $this->SetDrawColor(84, 141, 212);
        $this->Line(10, 135, 200, 135);
        $this->Line(10, 286, 200, 286);
        $this->RoundedRect(20, 150, 40, 25, 1, '1111');
        $this->RoundedRect(20, 190, 40, 25, 1, '1111');
        $this->RoundedRect(20, 230, 40, 25, 1, '1111');
        $this->RoundedRect(150, 150, 40, 25, 1, '1111');
        $this->RoundedRect(150, 190, 40, 25, 1, '1111');
        $this->RoundedRect(150, 230, 40, 25, 1, '1111');
        $this->SetTextColor(84, 141, 212);
        $this->SetFont('helvetica', 'N', 12);
        $this->Text(30, 142, 'Planned');
        $this->Text(163, 142, 'Actual');
        $this->SetFont('helvetica', 'N', 48);
        $this->Text(33, 172, '+');
        $this->Text(163, 172, '+');
        $this->Text(33, 212, '=');
        $this->Text(163, 212, '=');
        $this->SetTextColor(0, 0, 0);
    }
    
    public function newPage($tablegroup, $section) {
        if (!isset($this->_header)) {
            $this->_header = array(
                'group'=>is_null($tablegroup) ? "" : $tablegroup->name,
                'section'=>$section);
            $this->startPageGroup();
            $this->_footer = array(
                'group'=>is_null($tablegroup) ? "" : $tablegroup->name,
                'tables'=>is_null($tablegroup) ? 0  : $tablegroup->tables);
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
    
    private function tableCount ($tables) {            
        $this->SetFont('helvetica', 'BI', 36);
        $this->SetAbsXY(10, 115);
        $txt = $this->fmtDecimal($tables, 'Table');
        $this->Cell(0, 0, $txt, 0, 0, 'C', false, '', 0, false, 'T', 'T');
    }

    private function boxCount($x, $y, $boxes) {
        $this->SetAbsXY($x, $y);
        $this->SetFont('helvetica', 'B', 56);
        $this->Cell(40, 25, $this->fmtDecimal($boxes), 0, 0, 'C', false, '', 0, false, 'C', 'M');
    }            

    private function boxCaption($x, $y, $text) {
        $this->SetAbsXY($x, $y);
        $this->SetFont('helvetica', 'I', 36);
        $this->Cell(80, 25, $text, 0, 1, 'C', false, '', 0, false, 'C', 'M');
    }            

    public function Render($allocations) {
        foreach ($allocations as $allocation) {
            if ($allocation->tables > 0) {
                $this->newPage($allocation->tablegroup, $allocation->stats->category->section->name);
                $this->category($allocation->stats->label, $allocation->stats->name);
                $this->tableCount($allocation->tables);
                $this->boxCount(20, 162, $allocation->display);
                $this->boxCaption(65, 162, 'On Table');
                $this->boxCount(20, 202, $allocation->reserve);
                $this->boxCaption(65, 202, 'Under Table');
                $this->boxCount(20, 242, $allocation->display + $allocation->reserve);
                $this->boxCaption(65, 242, 'Total');
            }
        }
            
    }

}
