<?php 

namespace Bookfair;

class Target extends Statistic {

    public $hidden = array(
        'bag_sold',
        'delivered',
        'end_display',
        'end_extras',
        'end_reserve',
        'fri_end_display',
        'fri_end_reserve',
        'fri_extras',
        'fri_sold',
        'loading',
        'sat_end_display',
        'sat_end_reserve',
        'sat_extras',
        'sat_sold',
        'start_display',
        'start_reserve',
        'sun_end_display',
        'sun_end_reserve',
        'sun_extras',
        'sun_sold',
        'total_sold',
        'total_stock',
        'total_unsold'
    );
    
    public function category() {
        return $this->belongsTo('Bookfair\Category');
    }

}
