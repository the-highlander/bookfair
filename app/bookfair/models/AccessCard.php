<?php

namespace Bookfair;
use Eloquent;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class AccessCard extends Eloquent  {
    
    public static $timestamps = true;
    protected $guarded = array('id');
    public static $table = 'access_cards';
    
    public function issued_to () {
        return $this->belongs_to('Bookfair\Person', 'issued_to_person_id');
    }

}

?>
