<?php

namespace Bookfair;
use TCPDF;
use DateTime;
use URL;

class AttendanceReport extends TCPDF {

    private $_orientation = 'p'; 
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;
    private $_bookfair;
    private $_minYear;
    private $_logo;
    private $_startDate;
    private $_attendance;
 
    public function __construct($bookfair, $attendance) {
        // Generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, false, 'ISO-8859-1', false);
       // Transform the data 
        $this->_bookfair = $bookfair;
        $this->_minYear = $bookfair->year;
        foreach ($attendance as $fair) {
            if ($fair->year < $this->_minYear) {
                $this->_minYear = $fair->year;
            }
        }
        $this->_logo = URL::asset('bookfair/img/logo.gif');
        $this->_startDate = new DateTime($this->_bookfair->start_date); 
        $this->_attendance = $attendance;
        $this->setMargins(15, 15, 15, 15);
        $this->SetAutoPageBreak(true);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render();
    }

    public function Header() {
        $this->Image($this->_logo, 10, 10, 37.47, 10.65, 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(91, 43, 0, 45);
        $hdr1 = $this->_bookfair->season . " Bookfair Attendance\n(" . $this->_minYear . '-' . $this->_bookfair->year . ')';
        $this->MultiCell(0, 0, $hdr1, 0, 'R', false, 1);
    }

    private function setAttendanceStyle($style) {
        switch ($style) {
            case "normal": 
                $this->SetFillColor(210, 234, 241);
                $this->SetTextColor(0);
                $this->SetDrawColor(255);
                $this->SetFont('helvetica', '', 10);
                break;
            case "summary":
                //TODO: Need to pick colours and differentiate from title 
                $this->SetFillColor(210, 234, 241);
                $this->SetTextColor(0);
                $this->SetDrawColor(255);
                $this->SetLineWidth(0.3);
                $this->SetFont('helvetica', 'B', 10);
                break;
            case "title":
                //TODO: Need to pick colours
                $this->SetFillColor(75, 172, 198);
                $this->SetTextColor(255);
                $this->SetDrawColor(255);
                $this->SetLineWidth(0.3);
                $this->SetFont('helvetica', 'B', 10);
                break;
        }
    }

    private function heading ($text, $newPage = false) {
        if ($newPage) {
            $this->AddPage();
            $this->SetY(25);
        }
        $this->SetFont('helvetica', 'B', 14);
        $this->MultiCell(0, 0, $text, 0, 'L', false, 1);
        $this->Ln(3);
    }

    public function Render() {
        $this->heading('Daily Attendance Summary', true);
        $w = 20;
        $this->setAttendanceStyle("title");
        $this->Cell(30, 6, 'Day', 'LRB', 0, 'C', 1);
        foreach ($this->_attendance as $bookfair) {
            $this->Cell($w, 6, $bookfair->year, 'LRB', 0, 'C', 1);
            foreach ($bookfair->daily_attendance as $day) {
                $daily[$day->day][$bookfair->year] = $day->attendance;
            }
            foreach ($bookfair->hourly_attendance as $hour) {
                $key = date('ga', strtotime(str_pad($hour->start_hr, 4, '0', STR_PAD_LEFT))) . ' - ' . date('ga', strtotime($hour->end_hr));
                $hourly[$hour->day][$key][$bookfair->year] = $hour->attendance;
            }
        }
        $this->Ln();
        $fill = 1;
        foreach ($daily as $key=>$value) {
            $this->setAttendanceStyle("title");
            $this->Cell(30, 6, $key, 'LTBR', 0, 'L', 1);
            $this->setAttendanceStyle("normal");
            foreach ($value as $attendance) {
                $this->Cell($w, 6, $attendance, 'LTBR', 0, 'R', $fill);
            }
            for ($i = count($value); $i < count($this->_attendance); $i++) {
                $this->Cell($w, 6, ' ', 'LTBR', 0, 'R', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->setAttendanceStyle("title");
        $this->Cell(30, 6, 'Total', 1, 0, 'L', 1);
        $this->setAttendanceStyle("summary");
        foreach ($this->_attendance as $bookfair) {
            foreach ($bookfair->total_attendance as $total) {
                $this->Cell($w, 6, $total->attendance, 'LR', 0, 'R', $fill);
            }
        }
        $this->Ln(10);
        $this->heading('Hourly Breakdown');
        foreach ($daily as $day=>$daydata) {
            $this->setAttendanceStyle("title");
            $this->Cell(30, 6, $day, 'LRB', 0, 'C', 1);
            foreach ($this->_attendance as $bookfair) {
                $this->Cell($w, 6, $bookfair->year, 'LRB', 0, 'C', 1);
            }
            $this->Ln();
            $fill = 01;
            foreach ($hourly[$day] as $hour=>$hourdata) {
                $this->setAttendanceStyle("title");
                $this->Cell(30, 6, $hour, 'LTBR', 0, 'L', 1);
                $this->setAttendanceStyle("normal");
                foreach ($hourdata as $attendance) {
                    $this->Cell($w, 6, $attendance, 'LTBR', 0, 'R', $fill);
                }
                for ($i = count($hourdata); $i < count($this->_attendance); $i++) {
                    $this->Cell($w, 6, ' ', 'LTBR', 0, 'R', $fill);
                }
                $this->Ln();
                $fill = !$fill;
            }
            $this->Ln();
        }
        $this->Ln();
    }
}
?>