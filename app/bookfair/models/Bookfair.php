<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Bookfair extends Eloquent  {

    protected $table = "bookfairs";
    protected $guarded = array('id');
    public $timestamps = false;

    public function addAttendance() {
        $this->loadTimeslots($this->start_date, $this->fri_open, $this->fri_close);
        $this->loadTimeslots($this->start_date->add(new DateInterval('P1D')), $this->sat_open, $this->sat_close);
        $this->loadTimeslots($this->start_date->add(new DateInterval('P2D')), $this->sun_open, $this->sun_close);
    }

 /*   public function addCategory($cat) {
        $thisBookfair = $this->id;
        $priorBookfair = Bookfair::whereSeason($this->season)->whereYear($this->year - 1)->pluck('id');
        $loading = Statistic::whereCategoryId($cat["id"])->avg('loading');
        $target = is_null($priorBookfair) ? 0 : Statistic::whereCategoryId($cat["id"])->whereBookfairId($priorBookfair)->pluck('target');
        $this->categories()
            ->attach(
                array(
                    'bookfair_id' => $thisBookfair,
                    'category_id' => $cat["id"],
                    'tablegroup_id' => $cat["tablegroup_id"],
                    'name' => $cat["name"],
                    'label'    => $cat["label"], 
                    'allocate' => $cat["allocate"],
                    'track' => $cat["track"],
                    'measure' => $cat["measure"],
                    'loading' => is_null($loading) ? 0 : $loading,
                    'target' => $target
                )
            );
    }
    */

    public function attachPriorCatgories () {
        $thisBookfair = $this->id;
        $priorBookfair = Bookfair::with('categories')->whereSeason($this->season)->whereYear($this->year - 1)->get();
        $cats = array_map(
            function($cat) use ($thisBookfair, $priorBookfair) {
               $loading = ($cat["pivot"]["allocate"]) ? Statistic::whereCategoryId($cat["id"])->avg('loading') : 0;
                return array(
                    'bookfair_id'   => $thisBookfair,
                    'category_id'   => $cat["id"],
                    'label'         => $cat["label"], 
                    'name'          => $cat["name"],
                    'allocate'      => ($cat["pivot"]["track"]) ? $cat["pivot"]["allocate"] : 0,
                    'track'         => $cat["pivot"]["track"],
                    'measure'       => $cat["pivot"]["measure"],
                    'target'        => $cat["pivot"]["target"]
                );
            }, $priorBookfair[0]->categories->toArray()); 
        $this->categories()->attach($cats);   
    }     

    public function attendances() {
        return $this->hasMany('Bookfair\Attendance');
    }

    public function categories() {
        return $this
            ->belongsToMany('Bookfair\Category', 'statistics', 'bookfair_id', 'category_id')
            ->withPivot('allocate', 'track', 'measure', 'pallet_id', 'target');
    }

    public function dailyAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('dayname(day) as day'), DB::Raw('sum(attendance) as attendance'))
            ->groupBy('day')
            ->orderBy('day');
    }

    public function hourlyAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('dayname(day) as day'), 'start_hr', 'end_hr', 'attendance')
            ->orderBy('day')->orderBy('start_hr');
    }

    public function loadTimeslots ($day, $start, $finish) {
        // Delete attendance records for the specified day of this bookfair that fall outside the new opening hours
        $this->hourlyAttendance()
            ->whereDay($day)
            ->where(function ($query) use ($start, $finish) {
                $query->where('start_hr', '<', $start)
                    ->orWhere('start_hr', '>', $finish);
            })
            ->delete();
        // Compute the new set of timing blocks
        for ($i = $start; $i <= $finish; $i+=100) {
            $reqtimes[] = array(
                'bookfair_id' => $this->id,
                'day'         => $day,
                'start_hr'    => $i,
                'end_hr'      => $i + 100
            );
        }
        // Compare what's needed for the new opening hours and what's already in the table and add any missing timeslots
        $curtimes = $this->hourlyAttendance()->whereDay($day)->get();
        if (count($curtimes) == 0) {
            $new = $reqtimes;
        } else {
            $new = array_diff($reqtimes, $curtimes);
        }
        if (count($new) > 0 ) {
            $this->attendances()->insert($new);
        }
    }    

    public function palletassignments() {
        return $this
            ->hasMany('Bookfair\Statistic')
            ->whereNotNull('pallet_id')
            ->join('categories', 'statistics.category_id', '=', 'categories.id')
            ->join('sections', 'categories.section_id', '=', 'sections.id')
            ->join('pallets', 'statistics.pallet_id', '=', 'pallets.id')
            ->select(
                'bookfair_id', 
                DB::raw('pallets.name AS pallet_name'), 
                'categories.section_id',
                DB::raw('sections.name AS section_name'), 
                DB::raw('MIN(statistics.label) AS minlabel'), 
                DB::raw('MAX(statistics.label) AS maxlabel'))
            ->groupBy(
                'pallet_id', 
                'categories.section_id',
                DB::raw('(SELECT MIN(t2.label) FROM statistics t2, categories t3 ' .
                           'WHERE t2.label IS NOT NULL ' .
                             'AND t2.label > statistics.label ' .
                             'AND t2.bookfair_id = statistics.bookfair_id ' .
                             'AND t3.id = t2.category_id ' .
                             'AND (t3.section_id <> categories.section_id ' .
                               'OR t2.pallet_id <> statistics.pallet_id))'))
            ->orderBy(DB::raw('pallets.name, sections.name, minlabel'));            
    }

    public function sales() {
        return $this->hasMany('Bookfair\Sale');
    }

    public function salesSummary() {
        //TODO: Need to adjust figures for box size differences -- standardise to A3 boxes.
        return $this
            ->hasMany('Bookfair\Statistic')
            ->join('categories', 'statistics.category_id', '=', 'categories.id')
            ->select(array(
                'bookfair_id', 'categories.section_id',
                DB::raw('(SELECT name FROM sections WHERE id = categories.section_id) AS section_name'),
                DB::raw('SUM(fri_sold) AS fri_sold'),
                DB::raw('SUM(sat_sold) AS sat_sold'),
                DB::raw('SUM(sun_sold) AS sun_sold'),
                DB::raw('SUM(bag_Sold) AS bag_sold'),
                DB::raw('SUM(total_stock) AS total_stock'),
                DB::raw('SUM(total_sold) AS total_sold'),
                DB::raw('SUM(total_unsold) AS total_unsold')
            ))
            ->groupBy(DB::raw('bookfair_id, (SELECT section_id FROM categories WHERE id = category_id)'));
    }            

    public function salesTotals() {
        //TODO: Need to adjust figures for box size differences -- standardise to A3 boxes.
        return $this
            ->hasMany('Bookfair\Statistic')
            ->select(array(
                'bookfair_id',
                DB::raw('SUM(fri_sold) AS fri_sold'),
                DB::raw('SUM(sat_sold) AS sat_sold'),
                DB::raw('SUM(sun_sold) AS sun_sold'),
                DB::raw('SUM(bag_Sold) AS bag_sold'),
                DB::raw('SUM(total_stock) AS total_stock'),
                DB::raw('SUM(total_sold) AS total_sold'),
                DB::raw('SUM(total_unsold) AS total_unsold')
            ))
            ->groupBy('bookfair_id');
    }            
  
    public function sections() {
        return $this
            ->hasMany('Bookfair\Statistic')
            ->join('categories', 'statistics.category_id', '=', 'categories.id')
            ->join('sections', 'categories.section_id', '=', 'sections.id')
            ->select(array(
                'bookfair_id', 
                'categories.section_id', 
                'sections.id', 'sections.name', 
                'sections.division_id',
                DB::raw('SUM(fri_sold) AS fri_sold'),
                DB::raw('SUM(sat_sold) AS sat_sold'),
                DB::raw('SUM(sun_sold) AS sun_sold'),
                DB::raw('SUM(bag_Sold) AS bag_sold'),
                DB::raw('SUM(total_stock) AS total_stock'),
                DB::raw('SUM(total_sold) AS total_sold'),
                DB::raw('SUM(total_unsold) AS total_unsold'),
                DB::raw('group_concat(distinct total_sold order by total_sold desc) as sold_ranks'),
                DB::raw('group_concat(distinct total_unsold order by total_unsold desc) as unsold_ranks')))
            ->groupBy('sections.id')
            ->orderBy('sections.division_id')->orderBy('sections.name');
    }
    
    public function soldGroupConcatBySection() {
        return DB::select('SELECT GROUP_CONCAT(DISTINCT rw1.total_values ORDER BY rw1.total_values DESC) AS ranked_values FROM ' .
                              '(SELECT DISTINCT SUM(rw2.total_sold) AS total_values FROM statistics rw2, categories rw3 ' .
                                    'WHERE rw2.bookfair_id = ? AND rw3.id = rw2.category_id ' .
                                    'GROUP BY rw3.section_id) rw1', array($this->id));
    }

    public function soldRankedBySection() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('DISTINCT bookfair_id, sum(total_sold) AS tots'))
            ->groupBy(DB::raw('(SELECT section_id FROM categories WHERE id = statistics.category_id)'))
            ->orderby('tots', 'desc');
    }

    public function targets() {
        return $this->hasMany('Bookfair\Target')
            ->orderBy('pallet_id')
            ->orderBy(DB::Raw('(SELECT section_id FROM categories AS t1 WHERE t1.id = statistics.category_id)'))
            ->orderBy('label');
    }

    // Individual Column Totals   
    public function totalAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('sum(attendance) as value'))
            ->groupBy('bookfair_id');
    }
    
    public function totalPercentSold () {
        return $this
            ->hasMany('Bookfair\Sale')
            ->select('bookfair_id', DB::Raw('sum(total_sold)/sum(total_stock) as percent'))
            ->groupBy('bookfair_id');
    }

    public function totalSold () {
        return $this
            ->hasMany('Bookfair\Sale')
            ->select('bookfair_id', DB::Raw('sum(total_sold) as sold'))
            ->groupBy('bookfair_id')->first();
    }

    public function totalStock() {
        return $this->hasMany('Bookfair\Sale')
            ->select('bookfair_id', DB::raw('sum(total_stock) as stock'))
            ->groupBy('bookfair_id');
    }
    
    public function unsoldGroupConcatBySection() {
        return DB::select('SELECT GROUP_CONCAT(DISTINCT rw1.total_values ORDER BY rw1.total_values DESC) AS ranked_values FROM ' .
                              '(SELECT DISTINCT SUM(rw2.total_unsold) AS total_values FROM statistics rw2, categories rw3 ' .
                                    'WHERE rw2.bookfair_id = ? AND rw3.id = rw2.category_id ' .
                                    'GROUP BY rw3.section_id) rw1', array($this->id));   
    }

    public function unsoldRankedBySection() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('DISTINCT bookfair_id, sum(total_unsold) AS tots'))
            ->groupBy(DB::raw('(SELECT section_id FROM categories WHERE id = statistics.category_id)'))
            ->orderby('tots', 'desc');
    }
    
}