<?php

//TODO: Required by March 2014

namespace Bookfair;

use TCPDF;
use DateTime;
use URL;

/*
  Produces the control sheets used when unpacking pallets at the book fair to show how many boxes
  in each category will go on top of the table and how many go underneath.
 */

class AllocationDropSheets extends TCPDF {

    private $_orientation = 'p';
    private $_units = 'mm';
    private $_pagesize = 'A4';
    private $_lineStyle;

    public function __construct($bookfair, $allocations) {
        // Always generate this report using A4 Portrait measured in millimeters.
        parent::__construct($this->_orientation, $this->_units, $this->_pagesize, true, 'UTF-8', false);
        $this->SetAutoPageBreak(false);
        $this->_lineStyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(84, 141, 212));
        $this->Render();
    }

    public function Render() {
        
    }

}

?>