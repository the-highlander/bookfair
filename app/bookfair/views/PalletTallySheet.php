<?php

//TODO: Per BookfaRequired by March 2014

namespace Bookfair;

use Auth;
use TCPDF;
use DateTime;
use URL;

/*
 * Produces the tally sheets used to count pallets as they are packed at the 
 * Warehouse. Shows a number of entries based on total pallet allocation for
 * the current bookfair, with sufficient space for handwritten pallet title,
 * date and initials.
 */

class PalletTallySheet extends TCPDF {

    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_pageLen = 36;
    private $_lineStyle;
    
    public function __construct($bookfair) {
        // Always generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor(Auth::user()->name());
        $this->SetTitle('Pallet Tally Sheet');
        $this->SetSubject('Warehouse Stock Control');
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->_fair = & $bookfair;
        $this->_startDate = new DateTime($bookfair->start_date);
        $this->SetAutoPageBreak(false);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render();
    }
    
    public function PrintCol ($text, $bdr, $fill) {
        $this->Cell(10, 7, $text[0], $bdr, 0, 'C', $fill);
        $this->Cell(56, 7, $text[1], $bdr, 0, 'C', $fill);
        $this->Cell(27, 7, $text[2], $bdr, 0, 'C', $fill);        
    }
    
    public function PrintRow($header = false, $i = 0, $bdr = 'LRTB', $fill = 1 ) {
        $text1 = ($header ? array('No.', 'Description', 'Date') : array($i, ' ', ' '));
        $text2 = ($header ? $text1 : array($i + $this->_pageLen, ' ', ' '));
        $this->PrintCol($text1, $bdr, $fill);
        $this->Cell(5, 7, ' ', 0, 0, 'C', 0);
        $this->PrintCol($text2, $bdr, $fill);
        $this->Ln();        
    }

    public function Header() {
        //URL::to('user/profile'); 
        $image_file = URL::asset('bookfair/img/logo.gif');
        $this->Image($image_file, 10, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(91, 43, 0, 45);
        $hdr1 = $this->_fair->season . " Book Fair, "
                . $this->_startDate->format('F Y')
                . "\n" . "Pallet Tally Sheet";
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
        $this->Ln(1);
        $this->setCellPaddings(0, 0, 0, 0);
        $this->SetTextColor(0, 0, 0, 0);
        $this->SetFillColor(91, 43, 0, 45);
        $this->SetFont('helvetica', '', 11);
        $this->PrintRow(true);
    }

    public function Footer() {
        $this->SetY(-13);
        $this->SetFont('helvetica', 'I', 8);
        $text1 = 'Pallet Tally Sheet ' . $this->getAliasNumPage().' of '.$this->getAliasNbPages();
        $this->Cell(50, 10, $text1, false, false, 'L', 0, '', 0, false, 'T', 'M');
        $text2 = $this->_fair->season . ' Book Fair, ' . $this->_startDate->format('F Y');
        $this->Cell(0, 10, $text2, false, false, 'R', 0, '', 0, false, 'T', 'M');
    }
    
    public function Render() {
        $pallets = 144; //($this->_fair->season === 'Winter') ? 24 : 144;
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->AddPage();
        $this->SetY(30.5); 
        $i = 0;
        $fill = 0;
        while ($i + $this->_pageLen < $pallets) {
            if ($i > 0 && $i % $this->_pageLen === 0) {
                $i += $this->_pageLen;
                $this->AddPage();
                $this->SetY(30.5); 
                $fill = 0;
            } else {
                $fill = !$fill;
            }
            $i++;
            $bdr = 'LR' . ($i % $this->_pageLen === 0 ? 'B' : '');
            $this->PrintRow(false, $i, $bdr, $fill);        
      }
    }

}

?>