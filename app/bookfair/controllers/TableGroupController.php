<?php

namespace Bookfair;

use Auth;
use BaseController;
use Input;
use Response;

class TableGroupController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create TableGroups')) {
            try {
                $group = TableGroup::create(array(
                            'name' => Input::get('year'),
                            'location' => Input::get('season'),
                            'room' => Input::get('location'),
                            'tables' => Input::get('start_date'),
                            'table_type' => Input::get('duration'),
                ));
                return $group;
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
        if (Auth::user()->can('Delete Table Groups')) {
            $group = TableGroup::find($id);
            if (is_null($group)) {
                return Response::make(json_encode(array(
                            'success' => false,
                            'message' => 'Table Group ' . $id . ' not found',
                            'data' => null)));
            } else {
                $deleted = $group;
                $group->delete();
                return $deleted;
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
        // No special privileges for reading tablegroups
        return TableGroup::orderBy('name', 'asc')->get();
    }

    public function show($id) {
        $group = TableGroup::find($id);
        if (is_null($group)) {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Tablegroup ' . $id . ' not found',
                        'data' => null)));
        } else {
            return $group;
        }
    }

    public function update($id) {
        if (Auth::user()->can('Update TableGroups')) {
            try {
                $group = TableGroup::find($id);
                $group->unguard();
                $group->year = Input::get('name');
                $group->season = Input::get('season');
                $group->location = Input::get('location');
                $group->start_date = Input::get('tables'); // \DateTime::createFromFormat('Y-m-d', Input::get('start_date);
                $group->duration = Input::get('table_type');
                $group->save();
                return $group;
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