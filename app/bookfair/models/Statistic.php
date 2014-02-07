<?php 

namespace Bookfair;
use Eloquent;

class Statistic extends Eloquent  {

    protected $table = "statistics";
    protected $guarded = array('id');
    public $timestamps = false;

    public function scopeForBookfair($query, $bookfair) {
    	return $query->where('bookfair_id', $bookfair);
    }

    public function scopeForDivision($query, $bookfair, $division) {
    	return $query
    		->where('bookfair_id', $bookfair)
    		->whereIn('category_id', 
    			function ($query) use ($division) {
                    $query->select('id')
                        ->from('categories')
                        ->whereIn('section_id',
                            function($query) use ($division) {
                                $query->select('id')
                                    ->from('sections')
                                    ->where('division_id', $division);
                            }
                        );
    			});
    }

    public function scopeForSection($query, $bookfair, $section) {
        return $query
            ->whereBookfairId($bookfair)
            ->whereSectionId($section);
    }

    public function bookfair() {
        return $this->belongsTo('Bookfair\Bookfair')->select(array('id', 'season', 'year'));
    }

    public function category() {
        return $this->belongsTo('Bookfair\Category')->select(array('id', 'name', 'section_id'));
    }

    public function pallet() {
        return $this->belongsTo('Bookfair\Pallet', 'pallet_id')->select(array('id', 'name'));
    }

    public function section() {
        return $this->belongsTo('Bookfair\Section')->select(array('id', 'name', 'division_id'));
    }

    public function tablegroup() {
    	return $this->belongsTo('Bookfair\TableGroup', 'tablegroup_id')->select(array('id', 'name', 'tables'));
    }

    public function computeSales() {
	    /*
	    * Sales data are input using different measures:
	    *    - By Table: display figure is number of tables and reserve figure is number of boxes. 
	    *    - By Box: display and reserve figures are input as number of boxes.
	    *    - By Percentage: display and input figures are input as a percentage of total stock in the category.
	    * 
	    * Table Loading is the number of boxes of delivered stock that was on display at the start of the bookfair divided by the total display space (measured in tables). 
	    * Box Loading is the number of boxes of delivered stock that was on display at the start of the bookfair divided by the number of boxes used to display them. This
	    * is a ratio because delivered boxes are consolidated or split for display. It is set at 1 when measuring by Table or Percentage.
	    * Extra boxes delivered during the bookfair are not included in the initial loading calculations.
	    */
        $this->total_stock = $this->delivered + $this->fri_extras + $this->sat_extras + $this->sun_extras + $this->end_extras;
	    switch ($this->measure) {
	        case "table":  
	            if ($this->start_display > 0) {
	                $this->loading = ($this->delivered - $this->start_reserve) / $this->start_display;
	            }
	            $this->fri_sold = ($this->start_display   * $this->loading + $this->start_reserve   + $this->fri_extras) - ($this->fri_end_display * $this->loading + $this->fri_end_reserve);
	            $this->sat_sold = ($this->fri_end_display * $this->loading + $this->fri_end_reserve + $this->sat_extras) - ($this->sat_end_display * $this->loading + $this->sat_end_reserve);
	            $this->sun_sold = ($this->sat_end_display * $this->loading + $this->sat_end_reserve + $this->sun_extras) - ($this->sun_end_display * $this->loading + $this->sun_end_reserve);
	            $this->bag_sold = ($this->sun_end_display * $this->loading + $this->sun_end_reserve + $this->end_extras) - ($this->end_display     * $this->loading + $this->end_reserve);
	            break;
	        case "box": 
                if ($this->start_display > 0) {
                    $this->loading = ($this->delivered - $this->start_reserve) / $this->start_display;
                }
	            $this->loading = ($this->delivered - $this->start_reserve) / $this->start_display;
	            $this->fri_sold = ($this->start_display   * $this->loading + $this->start_reserve   + $this->fri_extras) - ($this->fri_end_display * $this->loading + $this->fri_end_reserve);
	            $this->sat_sold = ($this->fri_end_display * $this->loading + $this->fri_end_reserve + $this->sat_extras) - ($this->sat_end_display * $this->loading + $this->sat_end_reserve);
	            $this->sun_sold = ($this->sat_end_display * $this->loading + $this->sat_end_reserve + $this->sun_extras) - ($this->sun_end_display * $this->loading + $this->sun_end_reserve);
	            $this->bag_sold = ($this->sun_end_display * $this->loading + $this->sun_end_reserve + $this->end_extras) - ($this->end_display     * $this->loading + $this->end_reserve);
	            break;
	        case "percent": 
	            // Table loading is number of boxes on display at the start of the bookfair. Don't include extra boxes delivered during the fair.
	            if ($this->start_display > 0) {
	                $this->loading = (($this->start_display / 100) * $this->delivered) / $this->allocated;
	            }
	            // Extra stock is not allocated to the days when it was added, but amortised across the entire bookfair.
	            // So extras can be ignored for the purposes of estimated % sold when recording the data at the bookfair.
	            $this->fri_sold = (($this->start_display   + $this->start_reserve)   - ($this->fri_end_display + $this->fri_end_reserve)) * ($this->total_stock) / 100;
	            $this->sat_sold = (($this->fri_end_display + $this->fri_end_reserve) - ($this->sat_end_display + $this->sat_end_reserve)) * ($this->total_stock) / 100 ;
	            $this->sun_sold = (($this->sat_end_display + $this->sat_end_reserve) - ($this->sun_end_display + $this->sun_end_reserve)) * ($this->total_stock) / 100;
	            $this->bag_sold = (($this->sun_end_display + $this->sun_end_reserve) - ($this->end_display     + $this->end_reserve))     * ($this->total_stock) / 100;
	            break;
	    }
	    $this->total_sold = $this->fri_sold + $this->sat_sold + $this->sun_sold + $this->bag_sold;
	    $this->total_unsold = $this->total_stock - $this->total_sold;
	}

}

?>