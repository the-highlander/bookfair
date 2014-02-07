<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Input;
use Response;

class TableGroupController extends BaseController {

/*    public function create() {
        if (Auth::user()->can('Create TableGroups')) {
            try {
                $group = Bookfair::create(array(
                    'year'       => Input::get('year'),
                    'season'     => Input::get('season'),
                    'location'   => Input::get('location'),
                    'start_date' => Input::get('start_date'),
                    'duration'   => Input::get('duration'),
                    'bag_sale'   => Input::get('bag_sale'),
                    'published'  => Input::get('published')
                ));
                // TODO: SHould populate sales_statistics at this point.

                return Response::make($group->toJson());                
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
        if (Auth::user()->can('Delete Bookfairs')) {
            $bookfair = Bookfair::find($id);
            if (is_null($bookfair)) {
                return Response::make(json_encode(array('success'=>false, 'message'=>'Bookfair ' . $id . ' not found', 'data'=>null)));
            } else {
                $deletedBookfair = $bookfair;
                $bookfair->delete();
                return Response::make($deletedBookfair->toJson());
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => null
            )));
        }
    }
*/    

    public function index() {
        // No special privileges for reading bookfairs
        return TableGroup::orderBy('name', 'asc')->get();
    }

/*
    public function show($id) {
        $bookfair = Bookfair::find($id);
        if (is_null($bookfair)) {
            return Response::make(json_encode(array('success'=>false, 'message'=>'Bookfair ' . $id . ' not found', 'data'=>null)));
        } else {
            return Response::make($bookfair->toJson());
        }
    }

    public function update($id) {
        if (Auth::user()->can('Update Bookfairs')) {
            try {
                $bookfair = Bookfair::find($id);
                $bookfair->unguard();
                $bookfair->year = Input::get('year');
                $bookfair->season = Input::get('season');
                $bookfair->location = Input::get('location');
                $bookfair->start_date = Input::get('start_date'); // \DateTime::createFromFormat('Y-m-d', Input::get('start_date);
                $bookfair->duration = Input::get('duration');
                $bookfair->bag_sale = Input::get('bag_sale');
                $bookfair->published = Input::get('published');
                $bookfair->save();
                return Response::make($bookfair->toJson());
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
*/    

}
?>