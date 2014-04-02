<?php 

namespace Bookfair;
use BaseController;
use Input;
use Response;
use DB;

class FormsController extends BaseController {   
    
    private function filename ($bookfair, $name) {
        return $bookfair->year . '_' . $bookfair->season . '_' . $name . '.pdf';
    }

    public function attendance($bookfair_id) {
        $bookfair = Bookfair::find($bookfair_id);
        $attendance = Bookfair::with('hourlyAttendance', 'dailyAttendance', 'totalAttendance')
            ->whereSeason($bookfair->season)
            ->where('year', '>=', $bookfair->year - 6)
            ->where('year', '<=', $bookfair->year)
            ->select('id', 'year', 'season', 'start_date')
            ->orderBy('year', 'desc')->get();
        $filename = $this->filename($bookfair, 'attendance');
        $pdf = new AttendanceReport($bookfair, $attendance);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

    public function details($bookfair_id) {
        // Detailed sales for each category in each section.
        $bookfair = Bookfair::with('totalStock', 'sections')->find($bookfair_id);
        foreach ($bookfair->sections as $section) {
            $section->sales = Statistic::forSection($bookfair_id, $section->id)->get();
        }
        $filename = $this->filename($bookfair, 'saledetail');
        $pdf = new SalesDetail($bookfair); 
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }


    public function boxdrops($bookfair_id) {
        //TODO: Need to get this working before March 2014
        $allocations = Allocation::with('stats.category.section', 'tablegroup')->forBookfair($bookfair_id)
                ->orderBy(DB::raw('(SELECT g.order FROM table_groups g WHERE g.id = allocations.tablegroup_id)'))
                ->orderBy('position')
                ->get();
        $bookfair = Bookfair::find($bookfair_id);
        // sheet needs to show boxes on table max(boxes_packed, (allocation_ratio * tables_allocated))
        // and boxes under table min(0, (boxes_packed - (allocation_ratio * tables_allocated)))
        if (!is_null($bookfair)) {           
            $filename = $this->filename($bookfair, 'dropsheets');
            $pdf = new AllocationDropSheets($bookfair, $allocations);            
            return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
        }
    }

    public function packingsheets($bookfair_id) {
        //TODO: Get the target data        
        $bookfair = Bookfair::with('targets.pallet', 'targets.category.section')->find($bookfair_id);
        $filename = $this->filename($bookfair, 'packingsheets');
        $pdf = new PalletPackingSheet($bookfair);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));    
    }
    
    public function palletassignments($bookfair_id) {
        $bookfair = Bookfair::with('palletassignments')->find($bookfair_id);
        $pallets = array();
        foreach($bookfair->palletassignments as $assignment) {
            $pallets[$assignment->pallet_name][] = $assignment;
        }
        // $pallets = Pallet::with('assignments', 'winterAssignments')->orderBy('name')->get();
        $filename = $this->filename($bookfair, 'pallet_desc');
        $pdf = new PalletDescriptions($bookfair->year, $bookfair->season, $pallets);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }
    
    public function pallettally($bookfair_id) {
        // Produces a PDF document used to track pallets for a bookfair.
        // Seq#, Section(s), Date wrapped, Initials
        $bookfair = Bookfair::find($bookfair_id);
        $filename = $this->filename($bookfair, 'pallet_tally');
        //TODO: Currently outputs 144 pallets. Need to calculate per bookfair. Winter is 24-36. Spring/Autumn 144. Autum 2014 was 149 Pallets!
        //Should be able to print/reprint  pallets already packed
        //TODO: Need a summary : number of pallets of each pallet_id (table = bookfair_pallets)
        $pdf = new PalletTallySheet($bookfair);
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

    public function summary($bookfair_id) {
        $bookfair = Bookfair::with('totalStock', 'salesSummary', 'salesTotals')->find($bookfair_id);
        $filename = $this->filename($bookfair, 'salesummary');
        $pdf = new SalesSummary($bookfair); 
        return Response::make($pdf->Output($filename, 'S'), 200, array('Content-Type'=>'application/pdf'));
    }

   public function salestallysheets($bookfair_id, $division_id = null) {
        $bookfair = Bookfair::find($bookfair_id);
        if (is_null($division_id)) {
            $data = Sale::forBookfair($bookfair_id)
                ->with('allocations', 'children', 'category.section')
                ->orderBy(DB::raw('(SELECT s.name FROM sections s JOIN categories c ON s.id = c.section_id WHERE c.id = statistics.category_id)'), 'asc')
                ->orderBy('label', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $data = Sale::forDivision($bookfair_id, $division_id)
                ->with('allocations', 'children', 'category.section')
                ->orderBy(DB::raw('(SELECT s.name FROM sections s JOIN categories c ON s.id = c.section_id WHERE c.id = statistics.category_id)'), 'asc')
                ->orderBy('label', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }
        if (!is_null($bookfair)) {           
            $filename = $this->filename($bookfair, 'tallysheets');
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