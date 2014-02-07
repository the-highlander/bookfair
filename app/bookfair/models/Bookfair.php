<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Bookfair extends Eloquent  {

    protected $table = "bookfairs";
    protected $guarded = array('id');
    public $timestamps = false;

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
                    'section_id' => $cat["section_id"],
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
                    'section_id'    => $cat["section_id"],
                    'label'         => $cat["label"], 
                    'name'          => $cat["name"],
                    'tablegroup_id' => $cat["pivot"]["tablegroup_id"],
                    'allocate'      => ($cat["pivot"]["track"]) ? $cat["pivot"]["allocate"] : 0,
                    'track'         => $cat["pivot"]["track"],
                    'measure'       => $cat["pivot"]["measure"],
                    'loading'       => is_null($loading) ? 0 : $loading,
                    'target'        => $cat["pivot"]["target"]
                );
            }, $priorBookfair[0]->categories->toArray()); 
        $this->categories()->attach($cats);   
    }     

    public function hourlyAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('dayname(day) as day'), 'start_hr', 'end_hr', 'attendance')
            ->orderBy('day')->orderBy('start_hr');
    }

    public function dailyAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('dayname(day) as day'), DB::Raw('sum(attendance) as attendance'))
            ->groupBy('day')
            ->orderBy('day');
    }

    public function totalAttendance () {
        return $this
            ->hasMany('Bookfair\Attendance')
            ->select('bookfair_id', DB::Raw('sum(attendance) as attendance'));
    }

    public function allocations() {
        return $this->hasMany('Bookfair\Allocation')
            ->select(array('label', 'name', 'packed', 'allocation_ratio', 'tables_allocated'));
    }

    public function attendances() {
        return $this->hasMany('Bookfair\Attendance');
    }

    public function sales() {
        return $this->hasMany('Bookfair\Sale');
    }

    public function categories() {
        return $this
            ->belongsToMany('Bookfair\Category', 'statistics', 'bookfair_id', 'category_id')
            ->withPivot('allocate', 'track', 'measure', 'tablegroup_id', 'pallet_id', 'target');
    }

    public function sections() {
        return $this
            ->belongsToMany('Bookfair\Section', 'statistics', 'bookfair_id', 'section_id')
            ->groupBy('section_id')
            ->orderBy('division_id')->orderBy('sections.name');
    }
    
    public function palletassignments() {
        return $this
            ->hasMany('Bookfair\Statistic')
            ->whereNotNull('pallet_id')
            ->join('sections', 'statistics.section_id', '=', 'sections.id')
            ->join('pallets', 'statistics.pallet_id', '=', 'pallets.id')
            ->select(
                'bookfair_id', 
                DB::raw('pallets.name AS pallet_name'), 
                'section_id',
                DB::raw('sections.name AS section_name'), 
                DB::raw('MIN(label) AS minlabel'), 
                DB::raw('MAX(label) AS maxlabel'))
            ->groupBy(
                'pallet_id', 
                'section_id', 
                DB::raw('(SELECT min(c1.label) FROM statistics c1 WHERE c1.section_id = statistics.section_id AND c1.pallet_id <> statistics.pallet_id AND c1.label > statistics.label)'),
                DB::raw('substr(label, 1, 1)'))
            ->orderBy(DB::raw('pallets.name, sections.name, minlabel'));            
    }

    public function totalStock() {
        return $this->hasMany('Bookfair\Statistic')
            ->select('bookfair_id', DB::raw('sum(total_stock) as stock'));
    }

    public function salesSummary() {
        //TODO: Need to adjust figures for box size differences -- standardise to A3 boxes.
        return $this
            ->hasMany('Bookfair\Statistic')
            ->select(array(
                'bookfair_id',
                DB::raw('(SELECT section_id FROM categories WHERE id = category_id) as section_id'),
                DB::raw('(SELECT (SELECT name FROM sections WHERE id = section_id) FROM categories WHERE id = category_id) as section_name'),
                DB::raw('SUM(fri_sold) AS fri_sold'),
                DB::raw('SUM(sat_sold) AS sat_sold'),
                DB::raw('SUM(sun_sold) AS sun_sold'),
                DB::raw('SUM(bag_Sold) AS bag_sold'),
                DB::raw('SUM(total_stock) AS total_stock'),
                DB::raw('SUM(total_sold) AS total_sold'),
                DB::raw('SUM(total_unsold) AS total_unsold')
            ))
            ->groupBy(DB::raw('(SELECT section_id FROM categories WHERE id = category_id)'));
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
            ));
    }            

    public function soldRankedBySection() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('DISTINCT bookfair_id, sum(total_sold) AS tots'))
            ->groupBy(DB::raw('(SELECT section_id FROM categories WHERE id = statistics.category_id)'))
            ->orderby('tots', 'desc');
    }

    public function unsoldRankedBySection() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('DISTINCT bookfair_id, sum(total_unsold) AS tots'))
            ->groupBy(DB::raw('(SELECT section_id FROM categories WHERE id = statistics.category_id)'))
            ->orderby('tots', 'desc');
    }
    
    public function soldGroupConcatBySection() {
        return DB::select('SELECT GROUP_CONCAT(DISTINCT rw1.total_values ORDER BY rw1.total_values DESC) AS ranked_values FROM ' .
                              '(SELECT DISTINCT SUM(rw2.total_sold) AS total_values FROM statistics rw2, categories rw3 ' .
                                    'WHERE rw2.bookfair_id = ? AND rw3.id = rw2.category_id ' .
                                    'GROUP BY rw3.section_id) rw1', array($this->id));
    }

    public function unsoldGroupConcatBySection() {
        return DB::select('SELECT GROUP_CONCAT(DISTINCT rw1.total_values ORDER BY rw1.total_values DESC) AS ranked_values FROM ' .
                              '(SELECT DISTINCT SUM(rw2.total_unsold) AS total_values FROM statistics rw2, categories rw3 ' .
                                    'WHERE rw2.bookfair_id = ? AND rw3.id = rw2.category_id ' .
                                    'GROUP BY rw3.section_id) rw1', array($this->id));   
    }


    //TODO::UPTOHERE  How to get a list of categories by section for a bookfair  bookfair: {.... Sectoins: [{    Categories: [{}...]} 
    function categorySummary () {
        //TODO: Need to adjust figures for box size differences -- standardise to A3 boxes

    }
    /*
    -- Query 1 Category Summary
SELECT s.name AS section_name, t.category_id, t.category_name, t.total_stock,
         CONCAT(round((t.total_stock / s.total_stock * 100),2), '%') AS share_of_section,
         CONCAT(round((t.total_stock / f.total_stock * 100),2), '%') AS share_of_bookfair,
         t.fri_sold, t.sat_sold, t.sun_sold, t.bag_sold, t.total_sold, t.total_unsold,
         CONCAT(round((t.fri_sold / t.total_stock * 100), 2), '%') as fri_pctsold,
         CONCAT(round((t.sat_sold / t.total_stock * 100), 2), '%') as sat_pctsold,
         CONCAT(round((t.sun_sold / t.total_stock * 100), 2), '%') as sun_pctsold,
         CONCAT(round((t.bag_sold / t.total_stock * 100), 2), '%') as bag_pctsold,
         CONCAT(round((t.total_sold / t.total_stock * 100), 2), '%') as tot_sold_pct,
         CONCAT(round((t.total_unsold / t.total_stock * 100), 2), '%') as tot_unsold_pct,
         FIND_IN_SET(t.total_sold, rs.ranked_values) AS soldrank,
         FIND_IN_SET(t.total_unsold, rw.ranked_values) AS wasterank
  FROM (SELECT c.section_id, c.name as category_name, c.id as category_id,
                    d.fri_sold, d.sat_sold, d.sun_sold, d.bag_sold, d.total_stock,
                    d.total_sold, d.total_unsold
             FROM statistics d, categories c 
             WHERE d.bookfair_id =  12
                AND c.id = d.category_id) t 
                            
  JOIN (SELECT b.section_id, SUM(a.total_stock) AS total_stock 
          FROM statistics a, categories b 
             WHERE a.bookfair_id = 12 AND b.id = a.category_id
             GROUP BY b.section_id) s
                 ON s.section_id = t.section_id 
                         
  JOIN (SELECT SUM(a2.total_stock) AS total_stock
          FROM statistics a2
            WHERE a2.bookfair_id = 12) f         
             
  JOIN (SELECT rs3.section_id, GROUP_CONCAT(DISTINCT rs2.total_sold ORDER BY rs2.total_sold DESC) AS ranked_values
             FROM statistics rs2, categories rs3
            WHERE rs2.bookfair_id = 12
               AND rs3.id = rs2.category_id
             GROUP BY rs3.section_id) rs
    ON rs.section_id = t.section_id
                          
  JOIN (SELECT rw3.section_id, GROUP_CONCAT(DISTINCT rw2.total_unsold ORDER BY rw2.total_unsold) AS ranked_values 
             FROM statistics rw2, categories rw3 
            WHERE rw2.bookfair_id = 12 
               AND rw3.id = rw2.category_id
             GROUP BY rw3.section_id) rw
    ON rw.section_id = t.section_id

  JOIN  sections s ON s.id = t.section_id
  order by section_name, soldrank
*/
}
?>