<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Section extends Eloquent  {

    protected $table = 'sections';
    public $timestamps = false;
    
    public function scopeForBookfair ($query, $bookfair) {
        return $query
            ->whereIn('id',
                function ($query) use ($bookfair) {
                    return $query->select('section_id')
                        ->from('categories')
                        ->whereIn('id',
                            function ($query) use ($bookfair) {
                                return $query->select('category_id')
                                    ->from('statistics')
                                    ->where('bookfair_id', $bookfair);
                            }
                        );
                    }
                )
            ->select('id', 'name', DB::raw($bookfair . ' AS bookfair_id'));
    }

    public function categories() {
        return $this->hasMany('Bookfair\Category');
    }

    public function bookfairs() {
        return $this->belongsToMany('Bookfair\Bookfair', 'statistics', 'bookfair_id', 'section_id');
    }

    public function division() {
        return $this->belongsTo('Bookfair\Division')->select(array('id','name'));
    }

    public function sales() {
        return $this->hasMany('Bookfair\Statistic')->orderBy('label');
    }

    public function totals() {
        //TODO: Need to adjust figures for box size differences -- standardise to A3 boxes.
        return $this
            ->hasMany('Bookfair\Statistic')
            ->select(array(
                'section_id',
                DB::raw('SUM(fri_sold) AS fri_sold'),
                DB::raw('SUM(sat_sold) AS sat_sold'),
                DB::raw('SUM(sun_sold) AS sun_sold'),
                DB::raw('SUM(bag_Sold) AS bag_sold'),
                DB::raw('SUM(total_stock) AS total_stock'),
                DB::raw('SUM(total_sold) AS total_sold'),
                DB::raw('SUM(total_unsold) AS total_unsold')
            ));
    }            


    public function unsoldranks() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('section_id, group_concat(distinct total_unsold order by total_unsold desc) as ranks'));
    }

    public function soldranks() {
        return $this->hasMany('Bookfair\Statistic')
            ->select(DB::raw('section_id, group_concat(distinct total_sold order by total_sold desc) as ranks'));
    }

}     
?>