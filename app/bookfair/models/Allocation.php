<?php 

namespace Bookfair;
use Eloquent;

class Allocation extends Eloquent  {

    protected $table = "allocations";
    protected $guarded = array('id');
    public $timestamps = false;

    public function stats() {
          return $this->belongsTo('Bookfair\Statistic', 'statistic_id')
                      ->select('id', 'bookfair_id', 'category_id', 'label', 'name', 'packed');
    }

    public function tablegroup() {
          return $this->belongsTo('Bookfair\TableGroup');
    }
        
    public function scopeForBookfair($query, $bookfair) {
          return $query->whereIn('statistic_id', 
                  function($query) use ($bookfair) {
                      $query->select('id')->from('statistics')->whereBookfairId($bookfair);
                  });
    }

    public function scopeForTablegroup($query, $bookfair, $group) {
        return $query
            ->where('tablegroup_id', $group)
            ->whereIn('statistic_id', 
                  function($query) use ($bookfair) {
                      $query->select('id')->from('statistics')->whereBookfairId($bookfair);
                  });
    }

}