<?php 

namespace Bookfair;

class Allocation extends Statistic {

    /**
    * hide columns that are not required for table allocations
    */
    public $hidden = array(
        'bookfair_id',
        'allocate',
        'track',
        'measure',
        'delivered',
        'start_display',
        'start_reserve',
        'fri_extras',
        'fri_end_display',
        'fri_end_reserve',
        'fri_sold',
        'sat_extras',
        'sat_end_display',
        'sat_end_reserve',
        'sat_sold',
        'sun_extras',
        'sun_end_display',
        'sun_end_reserve',
        'sun_sold',
        'end_extras',
        'end_display',
        'end_reserve',
        'bag_sold',
        'total_stock',
        'total_sold',
        'total_unsold'
    );

    public function scopeForTablegroup($query, $bookfair, $group) {
        return $query
            ->where('bookfair_id', $bookfair)
            ->where('tablegroup_id', $group);
    }

    /**
    * Overrides (Laravel or)Illuminate\Database\Eloquent\Model's query() so that the Allocations Model will only return 
    * categories in sections where allocate = true (ie allocating tables for this category)
    *
    * @return mixed
    */
    public function newQuery($excludeDeleted = true) {
        parent::newQuery($excludeDeleted);
        $query = parent::newQuery();
        $query->whereAllocate(1);
        return $query;
    }

}