<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Input;
use Response;

class DivisionController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create Divisions')) {
            $data = Input::json();
            try {
                $division = Division::create(array(
                    'name'       => $data->name,
                    'published'  => $data->published
                ));
                return Response::eloquent($division);
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::json_encode(array(
                    'success' => false, 
                    'message' => $e->getMessage(),
                    'data'    => null));
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }  
    }

    public function destroy($id) {
        if (Auth::user()->can('Delete Divisions')) {
            $division = Division::find($id);
            if (is_null($division)) {
                return Response::make("Division " . $id . " not found", 404);
            } else {
                $deletedDivision = $division;
                $division->delete();
                return Response::make($deletedDivision->toJson());
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }

    public function index() {
    	// No special privileges for reading Divisions
        return Division::all();
    }

    public function show($id) {
        $division = Division::find($id);
        if (is_null($division)) {
            return Response::make('Division ' . $id . ' not found', 404);
        } else {
            return Response::make($division->toJson());
        }
    }

    public function update() {
        if (Auth::user()->can('Update Divisions')) {
            $data = Input::json();
            try {
                $division = Division::find($data->id);
                $division->name = $data->name;
                $division->published = $data->published;
                $division->save();
                return $division;
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::json_encode(array(
                    'success' => false, 
                    'message' => $e->getMessage(),
                    'data'    => null));
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }  
    }

}
