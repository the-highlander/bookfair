<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Bookfair\User as User;
use DB;
use Hash;
use Input;
use Response;

class UserController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create Users')) {
                DB::transaction(function() {
                    $person = new Person;
                    $person->first_name = Input::get('first_name');
                    $person->last_name = Input::get('last_name');
                    $person->email = Input::get('email');
                    $person->save();
                    $user = new User;
                    $user->id = Input::get('username');
                    $user->password = Hash::make(Input::get('password'));
                    $user->locked = Input::get('locked');
                    $user->person()->associate($person);
                    $user->save();
                });
                return Response::make(User::with('person')->find(Input::get('username'))->toJson());
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }  
    }

    public function destroy($id) {
        if (Auth::user()->can('Delete Users')) {
            // $user = User::where('person_id', '=', $id)->first();
            $user = User::find(Input::get('username'));
            if (is_null($user)) {
                return Response::make(json_encode(array('success'=>false, 'message'=>'User ' . Input::get('username') . ' not found', 'data'=>null)));
            } else {
                $deletedUser = $user;
                $user->delete();
                return Response::make($deletedUser->toJson());
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }
 
    public function index()
    {
        if (Auth::user()->can('Read Users')) {
            return Response::make(User::with('person')->get());
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }
    
    public function show($id)
    {
        if (Auth::user()->can('Read Users')) {
            $user = User::with('person')->find($id);
            if (is_null($user)) {
                return Response::make(json_encode(array('success'=>false, 'message'=>'User ' . $id . ' not found', 'data'=>null)));
            } else {       
                return Response::make($user->toJson());
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }
       
    /*
     */
    public function update($id) {
        if (Auth::user()->can('Update Users')) {
            DB::transaction(function() {
                $user = User::where('person_id', '=', Input::get('id'))->firstOrFail();
                $user->id = Input::get('username');
                $user->locked = Input::get('locked');
                if (Input::has('password')) {
                    $password = Input::get('password');
                    if (!is_null($password)) {
                        $user->password = Hash::make($password);
                        $user->last_reset = DB::raw('NOW()');
                        $user->attempts = 0;
                    }
                }
                $user->person->first_name = Input::get('first_name');
                $user->person->last_name = Input::get('last_name');
                $user->save();
                $user->person->save();
            });
            return Response::make(User::with('person')->find(Input::get('username'))->toJson());
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
