<?php 

namespace Bookfair;
use BaseController;
use Input;
use Reponse;

class PersonController extends BaseController {

    public function destroy($id = null) {
        $person = Person::find($id);
        if (is_null($person)) {
            return Response::json("Person not found", 404);
        } else {
            $deleted = $person;
            $person->delete();
            return Response::eloquent($deleted);
        }

            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));

    }

    public function index() {
        return Response::make(Person::with('user_account')->get()->toJson());
    }

    public function show($id = null) {
        $person = Person::find($id);
        if (is_null($person)) {
            return Response::make('Person ' . $id . ' not found', 404);
        } else {       
            return Response::make(Person::with('user_account')->find($id)->toJson());
        }
    }
    
    public function create() {
        if (Auth::user()->allowed_to('Create People')) {
            $input = Input::json();
            $person = Person::create($input);
            return $person;
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }
        
    public function update() {
        if (Auth::user()->allowed_to('Update People')) {
            $input = Input::json();
            // if that doesn't work go back to here:http://forums.laravel.io/viewtopic.php?id=170
            $person = Person::find($input->id);
            $person->fill($input);
            $person->save();
            return Response::eloquent($person);
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }        
    }
        
}

?>
