<?php 

namespace Bookfair;

class Target extends Statistic {

    public $hidden = array(
        'table_group_id',
        'packed',
        'suggested', 
        'allocated',
        'delivered',
        'loading',
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
}

?>