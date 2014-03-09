<?php

namespace Bookfair;
use TCPDF;
use DateTime;
use URL;

class SalesDetail extends TCPDF {

    private $_orientation = 'l'; 
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;
    private $_bookfair;
    private $_logo;
    private $_prevSection;
    private $_startDate;
    private $_curSection = "";

    public function __construct($data) {
        // Always generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, false, 'ISO-8859-1', false);
        $this->setMargins(10, 10, 10, 10);
        $this->SetAutoPageBreak(true);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->_logo = URL::asset('bookfair/img/logo.gif');
        // Transform the data 
        $this->_bookfair = $data;
        $this->_startDate = new DateTime($this->_bookfair->start_date); 
        $this->Render();
    }

   private function setStyle($style) {
        switch ($style) {
            case "normal": 
                $this->SetFillColor(210, 234, 241);
                $this->SetTextColor(0);
                $this->SetDrawColor(255);
                $this->SetFont('helvetica', '', 8);
                break;
            case "title":
                //TODO: Need to pick colours
                $this->SetFillColor(75, 172, 198);
                $this->SetTextColor(255);
                $this->SetDrawColor(255);
                $this->SetLineWidth(0.3);
                $this->SetFont('helvetica', 'B', 8);
                break;
        }
    }

    public function Header() {        
        $image_file = URL::asset('bookfair/img/logo.gif');
        $this->Image($this->_logo, 10, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(91, 43, 0, 45);
        $hdr1 = "Stock Sold by Category" . "\n" 
            . $this->_bookfair->season . " Book Fair, " 
            . $this->_startDate->format('F Y');            
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
        $this->SetFont('helvetica', 'B', 18);
        $this->setCellPaddings(0, 0, 0, 2);
        $this->Cell(0, 10, $this->_curSection, 0, 1, 'L', false, '', 0, false, 'T', 'B');
        $this->setCellPaddings(0, 0, 0, 0);
        $this->SetTextColor(0, 0, 0, 0);
        $this->SetFillColor(91, 43, 0, 45);
        $this->SetFont('helvetica', '', 9);

        $w = 14;
        $h = 20;
        $this->setCellHeightRatio(1);        
        $this->setStyle("title");       
        $this->MultiCell(44, $h, 'Category', 'LTRB', 'C', true, 0);
        $this->MultiCell(11, $h, 'Label', 'LTRB', 'C', true, 0);
        $this->MultiCell(12, $h, 'Total Boxes', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Share of Bookfair', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Friday Boxes Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Saturday Boxes Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Sunday Boxes Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Bag Sale Boxes Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Total Boxes Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Total Boxes Unsold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Friday % Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Saturday % Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Sunday % Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Bag Sale % Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Total % Sold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Total % Unsold', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Sales Rank', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Waste Rank', 'LTRB', 'C', true, 1);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $border = array('T' => $this->_lineStyle);
        $this->Cell(50, 10, 'Page ' . $this->getPageNumGroupAlias() . ' of ' . $this->getPageGroupAlias(), $border, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 10, $this->_prevSection , $border, false, 'R', 0, '', 0, false, 'T', 'M');
    }    

    public function RenderStats($section) {
        $rowcount = 0;
        $fill = 0;
        $w = 14;
        $this->setStyle('normal');
        $soldranks = explode(',', $section->soldranks);
        $unsoldranks = explode(',', $section->unsoldranks);
        foreach ($section->sales as $category) {
            $rowcount++;
            if ($fill) { 
                $this->SetFillColor(210, 234, 241);
            } else {
                $this->SetFillColor(255, 255, 255);
            }
            $this->Cell(44, 6, $category->name, 'LTBR', 0, 'L', true);
            $this->Cell(11, 6, $category->label, 'LTBR', 0, 'L', true);
            $this->Cell(12, 6, number_format($category->total_stock, 0), 'LTBR', 0, 'R', 1);
            $this->Cell($w, 6, round(($category->total_stock / $this->_bookfair->total_stock[0]->stock * 100),1) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->fri_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->sat_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->sun_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->bag_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->total_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($category->total_unsold, 0), 'LTBR', 0, 'R', true);
            if ($category->total_stock > 0) {
                $this->Cell($w, 6, round(($category->fri_sold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($category->sat_sold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($category->sun_sold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($category->bag_sold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($category->total_sold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($category->total_unsold / $category->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            } else {
                for ($i=0; $i<6; $i++) {
                    $this->Cell($w, 6, ' ', 'LTBR', 0, 'C', true);
                }
            }
            $this->Cell($w, 6, array_search($category->total_sold, $soldranks)+1, 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, array_search($category->total_unsold, $unsoldranks)+1, 'LTBR', 0, 'R', true);
            $this->Ln();
            $fill = !$fill;
            if ($rowcount == 25) {
                $this->AddPage();
                $this->SetY(40);
                $rowcount = 0;
            }
        }
        // Total line
        $this->setStyle("title");       
        $this->Cell(55, 6, 'Totals', 'LTBR', 0, 'L', true);
        $this->Cell(12, 6, number_format($section->total_stock, 0), 'LTBR', 0, 'R', 1);
        $this->Cell($w, 6, round(($section->total_stock / $this->_bookfair->total_stock[0]->stock * 100),1) . '%', 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->fri_sold, 0), 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->sat_sold, 0), 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->sun_sold, 0), 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->bag_sold, 0), 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->total_sold, 0), 'LTBR', 0, 'R', true);
        $this->Cell($w, 6, number_format($section->total_unsold, 0), 'LTBR', 0, 'R', true);
        if ($category->total_stock > 0) {
            $this->Cell($w, 6, round(($section->fri_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($section->sat_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($section->sun_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($section->bag_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($section->total_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($section->total_unsold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
        } else {
            for ($i=0; $i<6; $i++) {
                $this->Cell($w, 6, ' ', 'LTBR', 0, 'C', true);
            }
        }
        $this->Cell(2 * $w, 6, ' ', 'LTBR', 0, 'R', true);
    }

    public function Render() {
        foreach ($this->_bookfair->sections as $section) {
            if ($section->name <> $this->_curSection) {
                $this->_curSection = $section->name;
                $this->startPageGroup();
                $this->AddPage();
                $this->SetY(40);
                $this->_prevSection = $this->_curSection;
            }
            $this->RenderStats($section);
        }

    }

}
?>