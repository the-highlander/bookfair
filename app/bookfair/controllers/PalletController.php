<?php

namespace Bookfair;

use Auth;
use BaseController;
use Input;
use Response;

class PalletController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create Pallets')) {
            try {
                $pallet = Pallet::create(array(
                            'name' => Input::get('name'),
                ));
                return Response::make($pallet->toJson());
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::make(json_encode(array(
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null
                )));
            }
        } else {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Insufficient Privileges',
                        'data' => null
            )));
        }
    }

    public function destroy($id) {
        if (Auth::user()->can('Delete Pallets')) {
            $pallet = Bookfair::find($id);
            if (is_null($pallet)) {
                return Response::make(json_encode(array(
                            'success' => false,
                            'message' => 'Pallet ' . $id . ' not found',
                            'data' => null)));
            } else {
                $deleted = $pallet;
                $pallet->delete();
                return Response::make($deleted->toJson());
            }
        } else {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Insufficient Privileges',
                        'data' => null
            )));
        }
    }

    public function index() {
// No special privileges for reading bookfairs
        return Pallet::orderBy('name', 'asc')->get();
    }

    public function show($id) {
        $pallet = Pallet::find($id);
        if (is_null($pallet)) {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Pallet ' . $id . ' not found',
                        'data' => null)));
        } else {
            return Response::make($pallet->toJson());
        }
    }

    public function update($id) {
        if (Auth::user()->can('Update Pallets')) {
            try {
                $pallet = Bookfair::find($id);
                $pallet->name = Input::get('name');
                $pallet->save();
                return Response::make($pallet->toJson());
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::make(json_encode(array(
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null
                )));
            }
        } else {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Insufficient Privileges',
                        'data' => null
            )));
        }
    }

}
