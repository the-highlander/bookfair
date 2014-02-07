<?php 

namespace Bookfair;
use Eloquent;

class Privilege extends Eloquent  {
    
	protected $table = "privileges";
    protected $guarded = array('id');
    public $timestamps = false;
    
    public function users() {
        return $this->belongsToMany('Bookfair\User');
    }
}

?>
