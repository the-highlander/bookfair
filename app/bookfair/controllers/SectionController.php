<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Input;
use Response;

class SectionController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create Sections')) {
            try {
                $section = Section::create(array(
                    'name'           => Input::get('name'),
                    'allocate_tables'=> Input::get('allocate_tables'),
                    'division_id'    => Input::get('division_id')  // TODO: Use eloquent attach?
                ));
                $divid = Input::get('division_id');
                $division = Division::find($divid);
                $section->division()->associate($division);
                return $section;
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::make(json_encode(array(
                    'success' => false, 
                    'message' => $e->getMessage(),
                    'data'    => null
                )));
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
        if (Auth::user()->can('Delete Sections')) {
            $section = Section::find($id);
            if (is_null($section)) {
                return Response::make(json_encode(array(
                    'success' => false, 
                    'message' => 'Section ' . $id . ' not found', 
                    'data'    => null
                )));
            } else {
                $deletedrow = $section;
                $section->delete();
                return $deletedrow;
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
        // No special privileges for reading Sections
        return Section::orderBy('name', 'asc')->get();
    }

    public function show($id) {
        $section = Section::find($id);
        if (is_null($section)) {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Section ' . $id . ' not found', 
                'data'    => null
            )));
        } else {
            return Response::make($section->toJson());
        }
    }

    public function update() {
        if (Auth::user()->can('Update Sections')) {
            try {
                $section = Section::find(Input::get('id'));
                $section->name = Input::get('name');
                $section->save();
                $divid = Input::get('division_id');
                if ($section->division_id <> $divid) {
                    $newdiv = Division::find($divid);
                    $section->division()->associate($newdiv);
                }
                return $section;
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::json_encode(array(
                    'success' => false, 
                    'message' => json_encode($e->getMessage()),
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
