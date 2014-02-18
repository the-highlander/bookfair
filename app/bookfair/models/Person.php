<?php 

namespace Bookfair;
use Eloquent;

class Person extends Eloquent  {
    
    protected $table = 'people';
    protected $guarded = array('id');
    public $timestamps = true;
    public $hidden = array('created_at', 'updated_at');

    public function user_account() {
        // foreign key column for Person ID in Users table doesn't follow expected naming convention
        return $this->hasOne('Bookfair\User');
    }
    
    public function fullname() {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    //public function emergency_person() {
    //    return $this->has_one('EmergencyContact', 'id');
   // }
    
    public function access_card() {
    	return $this->hasMany('AccessCard', 'issued_to_person_id'); //TODO: Check is that the right value for column reference.
    }
}

?>
