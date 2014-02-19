<?php

namespace Bookfair;

use TCPDF;
use DateTime;
use URL;

class SalesSummary extends TCPDF {

    private $_orientation = 'l';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;
    private $_bookfair;
    private $_startDate;
    private $_logo;

    public function __construct($bookfair) {
        // Always generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, false, 'ISO-8859-1', false);
        $this->_bookfair = $bookfair;
        $this->_logo = URL::asset('bookfair/img/logo.gif');
        $this->_startDate = new DateTime($this->_bookfair->start_date);
        $this->setMargins(15, 15, 15, 15);
        $this->SetAutoPageBreak(true);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render();
    }

    public function Header() {
        //TODO THIS LINE TAKES A VERY LONG TIME TO RUN!
        $this->Image($this->_logo, 10, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(91, 43, 0, 45);
        $hdr1 = "Stock Sold by Section" . "\n"
                . $this->_bookfair->season . " Book Fair, "
                . $this->_startDate->format('F Y');
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
    }

    private function heading($text, $newPage = false) {
        if ($newPage) {
            $this->AddPage();
            $this->SetY(25);
        }
        $this->SetFont('helvetica', 'B', 14);
        $this->MultiCell(0, 0, $text, 0, 'L', false, 1);
        $this->Ln(3);
    }

    private function setStyle($style) {
        switch ($style) {
            case "normal":
                $this->SetFillColor(210, 234, 241);
                $this->SetTextColor(0);
                $this->SetDrawColor(255);
                $this->SetFont('helvetica', '', 9);
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

    public function Render() {
        $this->heading('Section Summary', true);
        $w = 14.4;
        $h = 13;
        $this->setStyle("title");
        $this->MultiCell(38, $h, 'Section', 'LTRB', 'C', 1, 0);
        $this->MultiCell($w, $h, 'Total Boxes', 'LTRB', 'C', true, 0);
        $this->MultiCell($w, $h, 'Share of All Boxes', 'LTRB', 'C', true, 0);
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
        $this->MultiCell($w, $h, 'Total % Unsold', 'LTRB', 'C', true, 1);
        //When adding ranks back in, change preceding line last param back to 0 to prevent line feed
        //$this->MultiCell($w, $h, 'Sales Rank', 'LTRB', 'C', true, 0);
        //$this->MultiCell($w, $h, 'Waste Rank', 'LTRB', 'C', 1, 1);
        $this->setStyle("normal");
        $fill = 0;
        foreach ($this->_bookfair->sales_summary as $section) {
            $this->Cell(38, 6, $section->section_name, 'LTBR', 0, 'L', $fill);
            $this->Cell($w, 6, number_format($section->total_stock, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, round(($section->total_stock / $this->_bookfair->total_stock[0]->stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->fri_sold, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->sat_sold, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->sun_sold, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->bag_sold, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->total_sold, 0), 'LTBR', 0, 'R', $fill);
            $this->Cell($w, 6, number_format($section->total_unsold, 0), 'LTBR', 0, 'R', $fill);
            if ($section->total_stock > 0) {
                $this->Cell($w, 6, round(($section->fri_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
                $this->Cell($w, 6, round(($section->sat_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
                $this->Cell($w, 6, round(($section->sun_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
                $this->Cell($w, 6, round(($section->bag_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
                $this->Cell($w, 6, round(($section->total_sold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
                $this->Cell($w, 6, round(($section->total_unsold / $section->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', $fill);
            } else {
                for ($i = 0; $i < 6; $i++) {
                    $this->Cell($w, 6, ' ', 'LTBR', 0, 'C', $fill);
                }
            }
            $this->Ln();
            $fill = !$fill;
        }
        // Total line
        $this->setStyle("title");
        foreach ($this->_bookfair->sales_totals AS $total) { // There is only 1
            $this->Cell(38, 6, 'Total', 'LTBR', 0, 'L', true);
            $this->Cell($w, 6, number_format($total->total_stock, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, round(($total->total_stock / $this->_bookfair->total_stock[0]->stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->fri_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->sat_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->sun_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->bag_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->total_sold, 0), 'LTBR', 0, 'R', true);
            $this->Cell($w, 6, number_format($total->total_unsold, 0), 'LTBR', 0, 'R', true);
            if ($total->total_stock > 0) {
                $this->Cell($w, 6, round(($total->fri_sold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($total->sat_sold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($total->sun_sold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($total->bag_sold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($total->total_sold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
                $this->Cell($w, 6, round(($total->total_unsold / $total->total_stock * 100), 0) . '%', 'LTBR', 0, 'R', true);
            } else {
                for ($i = 0; $i < 6; $i++) {
                    $this->Cell($w, 6, ' ', 'LTBR', 0, 'C', true);
                }
            }
            $this->Ln();
        }
    }

}

?>