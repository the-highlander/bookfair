<?php 

namespace Bookfair;
use Eloquent;

class Attendance extends Eloquent  {

    protected $table = "attendances";
    public $timestamps = false;
    public $hidden = array(
        'bookfair_id'
    );

    public function bookfair() {
        return $this->belongsTo('Bookfair\Bookfair');
    }

}     
?>