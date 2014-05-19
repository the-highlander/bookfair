<?php

namespace Bookfair;

use TCPDF;
use DateTime;
use URL;

class PalletDescriptions extends TCPDF {

    private $_orientation = 'l';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;
    private $_logo;
    private $_year = "";
    private $_season = "";

    public function __construct($year, $season, $pallets) {
        // generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, false, 'ISO-8859-1', false);
        $this->setMargins(15, 20, 15, false);
        $this->SetAutoPageBreak(true, 15);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->_logo = URL::asset('bookfair/img/logo.gif');
        $this->SetCellPaddings(2, 1, 2, 1);
        $this->_year = $year;
        $this->_season = $season;
        $this->Render($pallets);
    }

    public function Header() {
        $this->Image($this->_logo, 15, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 26);
        $this->SetTextColor(91, 43, 0, 45);
        $this->MultiCell(0, 0, $this->_season . " " . $this->_year . " Pallet Assignment", 0, 'R', false, 1);
    }

    private function formatAssignments($assignments) {
        $result = null;
        $prev = null;
        $first = true;
        $label = null;
        foreach ($assignments as $assignment) {
            if (is_null($prev) || $prev <> $assignment->section_id) {
                $result .= (is_null($prev) ? '' : (!$first ? '), ' : ', ')) . $assignment->section_name;
                $first = true;
            }
            if (is_null($assignment->minlabel) || $assignment->minlabel == $assignment->maxlabel) {
                if ($assignment->minlabel !== '') {
                    $label = $assignment->maxlabel;
                }
            } else {
                if (!is_null($assignment->maxlabel)) {
                    $label = $assignment->minlabel . '-' . $assignment->maxlabel;
                }
            }
            if (!is_null($label)) {
                if ($first) {
                    $result .= ' (' . $label;
                    $first = false;
                } else {
                    $result .= ', ' . $label;
                }
            }
            $prev = $assignment->section_id;
        }
        if (!$first) {
            $result .= ')';
        }
        return $result;
    }

    public function Render($pallets) {
        $this->AddPage();
        $this->SetY(25);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetFillColor(91, 43, 0, 45);
        $this->SetTextColor(255);
        $this->SetDrawColor(255);
        $this->SetLineWidth(0.3);
        $this->MultiCell(45, 0, 'Label', 1, 'L', 1, 0);
        $this->MultiCell(0, 0, 'Categories', 1, 'L', true, 1);
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        foreach ($pallets as $name => $pallet) {
            $this->SetFont('helvetica', 'B', 16);
            $this->MultiCell(45, 0, $name, 0, 'L', false, 0);
            $this->SetFont('helvetica', 'N', 16);
            $this->MultiCell(0, 0, $this->formatAssignments($pallet), 0, 'L', false, 1);
        }
    }

}

?>