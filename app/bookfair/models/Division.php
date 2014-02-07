<?php 

namespace Bookfair;
use Eloquent;

class Division extends Eloquent  {

    protected $table = "divisions";
    protected $guarded = array('id');
    public $timestamps = false;

    public function sections() {
          return $this->hasMany('Bookfair\Section');
    }

}     
?>