<?php 

namespace Bookfair;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Eloquent;
use Validator;

class User extends Eloquent implements UserInterface, RemindableInterface {
    
    protected $table = 'users';
    public $timestamps = false;    
    public $hidden = array('password');
    public static $rules = array('id' => 'required|min:3');        
    public static $max_attempts = 5;
    
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    public function name() {
       return $this->person()->first()->fullname();       
    }
    
    public static function validate($data) {
        return Validator::make($data, static::$rules);
    }
    
    /**
     * Lock user accounts once maximum password attempts reaches 5
     */
    public function save(array $options = array()) {
        if ($this->attempts >= User::$max_attempts) { 
            $this->locked = true;
        }
        parent::save($options);
    }
    
    public function person() {
        return $this->belongsTo('Bookfair\Person')->select(array('id', 'first_name', 'last_name', 'email'));
    }    
        
    public function privileges() {
        return $this->belongsToMany('Bookfair\Privilege');
    }
    
    public function can($name) {        
        $granted = $this->privileges()->where('name', '=', $name);
        return !is_null($granted); 
    }
    
}

?>
