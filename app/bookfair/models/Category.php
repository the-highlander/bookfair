<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Category extends Eloquent  {

	protected $table = "categories";
    protected $guarded = array('id');
    public $timestamps = false;    

    public function section() {
        return $this->belongsTo('Bookfair\Section', 'section_id')->select(array('id','name'));
    }

    public function bookfairs() {
        return $this->belongsToMany('Bookfair\Bookfair', 'statistics', 'category_id', 'bookfair_id');
    }

}     
?>