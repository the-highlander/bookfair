<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Input;
use Response;
use DateTime;

class BookfairController extends BaseController {

    //public function addCategory($id) { /// ERewrite -- its a full on form using input
    //    $bookfair = Bookfair::find($id);
    //    $cat = Category::find($id);
    // category id, section id, table group id, measure, allocate, track, boxes to pack?
    //    $bookfair->addCategory($cat);
    //}

    public function create() {
        if (Auth::user()->can('Create Bookfairs')) {
            try {
                $bookfair = Bookfair::create(array(
                    'year'       => Input::get('year'),
                    'season'     => Input::get('season'),
                    'location'   => Input::get('location'),
                    'start_date' => Input::get('start_date'),
                    'duration'   => Input::get('duration'),
                    'bag_sale'   => Input::get('bag_sale'),
                    'fri_open'   => (new DateTime(Input::get('fri_open')))->format('Hi'),
                    'fri_close'  => (new DateTime(Input::get('fri_close')))->format('Hi'),
                    'sat_open'   => (new DateTime(Input::get('sat_open')))->format('Hi'),
                    'sat_close'  => (new DateTime(Input::get('sat_close')))->format('Hi'),
                    'sun_open'   => (new DateTime(Input::get('sun_open')))->format('Hi'),
                    'sun_close'  => (new DateTime(Input::get('sun_close')))->format('Hi'),
                    'locked'     => Input::get('locked')
                ));
                // Populate the Statistics Table with Categories for this Season
                $bookfair->attachPriorCatgories();
                $bookfair->addAttendance();
                return $bookfair;                
            } catch (Exception $e) {
                return Response::make(json_encode(array(
                    'success' => false, 
                    'message' => $e->getMessage(),
                    'data'    => $bookfair
                )));
            }
        } else {
            return Response::make(json_encode(array(
                'success' => false, 
                'message' => 'Insufficient Privileges', 
                'data'    => $bookfair
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

    public function index() {
        // No special privileges for reading bookfairs
        return Bookfair::with('salesTotals', 'totalAttendance')->get();
    }

    public function show($id) {
        $bookfair = Bookfair::find($id);
        if (is_null($bookfair)) {
            return Response::make(json_encode(array('success'=>false, 'message'=>'Bookfair ' . $id . ' not found', 'data'=>null)));
        } else {
            return $bookfair;
        }
    }

    public function update($id) {
        if (Auth::user()->can('Update Bookfairs')) {
            try {
                $bookfair = Bookfair::with('salesTotals', 'totalAttendance')->find($id);
                $bookfair->unguard();
                $bookfair->year = Input::get('year');
                $bookfair->season = Input::get('season');
                $bookfair->location = Input::get('location');
                $bookfair->start_date = Input::get('start_date'); // \DateTime::createFromFormat('Y-m-d', Input::get('start_date);
                $bookfair->fri_open = Input::get('fri_open');
                $bookfair->fri_close = Input::get('fri_close');
                $bookfair->sat_open = Input::get('sat_open');
                $bookfair->sat_close = Input::get('sat_close');
                $bookfair->sun_open = Input::get('sun_open');
                $bookfair->sun_close = Input::get('sun_close');
                $bookfair->end_date = Input::get('end_date'); // \DateTime::createFromFormat('Y-m-d', Input::get('start_date);
              //$bookfair->duration = Input::get('duration');
                $bookfair->bag_sale = Input::get('bag_sale');
                $bookfair->locked = Input::get('locked');
                $bookfair->save();
                // TODO:: Delete attendances before the open and after the close on each day
                // start date must be a friday or saturday. end_date must be a sunday. No need to keep these input fields.
                return $bookfair;
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

}
