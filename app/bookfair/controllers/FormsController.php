<?php 

namespace Bookfair;
use BaseController;
use Input;
use Response;
use DB;

class FormsController extends BaseController {   

    public function attendance($bookfair_id) {
        $bookfair = Bookfair::find($bookfair_id);
        $attendance = Bookfair::with('hourlyAttendance', 'dailyAttendance', 'totalAttendance')
            ->whereSeason($bookfair->season)
            ->where('year', '>=', $bookfair->year - 4)
            ->where('year', '<=', $bookfair->year)
            ->select('id', 'year', 'season', 'start_date')
            ->orderBy('year', 'desc')->get();
        $filename = $bookfair->season . $bookfair->year . "_attendance.pdf";
        $pdf = new AttendanceReport($bookfair, $attendance);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

    public function details($bookfair_id) {
        // Detailed sales for each category in each section.
        $bookfair = Bookfair::with('sections', 'totalStock')->find($bookfair_id);
        $i = 0;
        foreach ($bookfair->sections as $section) {
            $section->load(array(
                'sales'      => function($query) use ($bookfair_id) { $query->whereBookfairId($bookfair_id); },
                'soldranks'  => function($query) use ($bookfair_id) { $query->whereBookfairId($bookfair_id); },
                'unsoldranks'=> function($query) use ($bookfair_id) { $query->whereBookfairId($bookfair_id); },
                'totals'     => function($query) use ($bookfair_id) { $query->whereBookfairId($bookfair_id); }
            ));
            $i++;
        }
        //return $bookfair;
        $filename = $bookfair->season . $bookfair->year . "_saledetail.pdf";
        $pdf = new SalesDetail($bookfair); 
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }


    public function tableallocations($bookfair_id) {
        //TODO: Need to get this working before March 2014
        $bookfair = Bookfair::with('allocations')->find($bookfair_id);
        // sheet needs to show boxes on table max(boxes_packed, (allocation_ratio * tables_allocated))
        // and boxes under table min(0, (boxes_packed - (allocation_ratio * tables_allocated)))
        if (!is_null($allocations)) {           
            $filename = $bookfair->season . $bookfair->year . "_dropsheets.pdf";
            $pdf = new AllocationDropSheets($bookfair, $allocations);            
            return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
        }
    }

    public function palletassignments($bookfair_id) {
        $bookfair = Bookfair::with('palletassignments')->find($bookfair_id);
        $pallets = array();
        foreach($bookfair->palletassignments as $assignment) {
            $pallets[$assignment->pallet_name][] = $assignment;
        }
        // $pallets = Pallet::with('assignments', 'winterAssignments')->orderBy('name')->get();
        $filename = "pallet_desc_" . date('Ymd') . ".pdf";
        $pdf = new PalletDescriptions($bookfair->year, $bookfair->season, $pallets);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

    public function summary($bookfair_id) {
        $bookfair = Bookfair::with('totalStock', 'salesSummary', 'salesTotals')->find($bookfair_id);
        $filename = $bookfair->season . $bookfair->year . "_salesummary.pdf";
        $pdf = new SalesSummary($bookfair); 
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

   public function tallysheets($bookfair_id, $division_id = null) {
        $bookfair = Bookfair::find($bookfair_id);
        if (is_null($division_id)) {
            $data = Sale::forBookfair($bookfair_id)
                ->orderBy(DB::raw('(SELECT section_id FROM categories WHERE id = category_id)'), 'asc')
                ->orderBy('label', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $data = Sale::forDivision($bookfair_id, $division_id)
                ->orderBy(DB::raw('(SELECT section_id FROM categories WHERE id = category_id)'), 'asc')
                ->orderBy('label', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }
        if (!is_null($bookfair)) {           
            $filename = $bookfair->season . $bookfair->year . "_tallysheets.pdf";
            $pdf = new SalesDataCaptureForm($bookfair, $data);            
            return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
        } else {
            return Response::error(404);
        }
    }
 
     /**
     * List of Section Leaders:
     * select d.name, concat(p.first_name, ' ', p.last_name) as full_Name, p.email
         from divisions d left outer join people p 
         on p.id = d.head_person_id
         where name <> 'Logistics' and name <> 'Primary Sorters' and name <> 'Bookfair Advisory Committee'
         order by d.name
    
    public $restful = true;

    public function hello_pdf () {
        //TODO: delete this function
        $pdf = new Tcpdf();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica','B',16);
        $pdf->Cell(40,10,'Hello World!');
        return Response::make($pdf->Output(' ', 'S'), 200, array('Content-Type'=>'application/pdf'));
    }
    
    public function get_allocation($id) {
        $allocation = Bookfair::with('allocations')->find($id);
        // return Response::eloquent($allocation);
        if (!is_null($allocation)) {
            $pdf = new \Warehouse\Forms\Allocation($allocation);
            return Response::make($pdf->Output('', 'S'), 200, array('Content-Type'=>'application/pdf'));
        } else {
            return Response::error(404);
        }
    }
    
    public function get_forwardestimates($id) {
        $data = Bookfair::with('subcategories')->find($id);
        if (!is_null($data)) {           
            $pdf = new ForwardEstimates($data);            
            return Response::make($pdf->Output('', 'S'), 200, array('Content-Type'=>'application/pdf'));
        } else {
            return Response::error(404);
        }
    }
    */
    
}