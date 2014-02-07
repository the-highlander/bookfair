<?php 

namespace Bookfair;
use Eloquent;

class TableGroup extends Eloquent  {

    protected $table = "table_groups";
    public $timestamps = false;

    public function categories() {
         return $this->hasMany('Bookfair\Category');
    }

    public function statistics() {
    	return $this->hasMany('Bookfair\Statistic');
    }

}     
?>