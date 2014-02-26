<?php

namespace Bookfair;

use Auth;
use BaseController;
use Input;
use Response;
use DB;

class StatisticController extends BaseController {

    public function allocations($bookfair_id) {
        // TODO:: Security?? In the route??
        return Allocation::with('category.section', 'tablegroup')->forBookfair($bookfair_id)->get();
    }

    public function sales($bookfair_id) {
        // TODO:: Security?? In the route??
        return Sale::with('category.section')->forBookfair($bookfair_id)->get();
    }

    public function targets($bookfair_id) {
        // TODO:: Security?? In the route??
        return Target::with('category.section', 'pallet')->forBookfair($bookfair_id)->get();
    }

    public function destroy($bookfair_id, $stats_id) {
        //TODO: Security Check if (Auth::user()->can('Delete Sections')) {
        $statistic = Statistic::find($stats_id);
        if (is_null($statistic)) {
            return Response::make(json_encode(array(
                        'success' => false,
                        'message' => 'Statistics Record ' . $id . ' not found',
                        'data' => null
            )));
        } else {
            $deletedRow = $statistic;
            $statistic->delete();
            return $deletedRow;
        }
    }

    public function updateallocation($bookfair_id, $id) {
        //TODO: Security Check
        try {
            $allocation = Allocation::with('category.section', 'tablegroup')->find($id);
            $allocation->packed = Input::get('packed');
            $allocation->loading = Input::get('loading');
            $allocation->allocated = Input::get('allocated');
            $allocation->suggested = Input::get('suggested');
            $grpid = Input::get('tablegroup_id');
            if ($allocation->tablegroup_id <> $grpid) {
                $newgroup = TableGroup::find($grpid);
                $allocation->tablegroup()->associate($newgroup);
            }
            $allocation->save();
            return $allocation;
        } catch (Exception $e) {
            //TODO: Pretty up the exception emssage.
            return Response::json(array(
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data' => null));
        };
    }

    public function updatesales($bookfair_id, $id) {
        // updating daily stock values and computing amount sold based on value of measure
        // Post will be a single stock item (on update of grid)
        if (Auth::user()->can('Stocktake')) {
            try {
                $sale = Sale::with('category.section')->find($id);
                $bookfair = Bookfair::find($bookfair_id);
                // assuming label, category_name and subcategory_name cannot be changed via Sales Statistics UI
                $sale->measure = Input::get('measure');
                $sale->delivered = Input::get('delivered');
                $sale->start_display = Input::get('start_display');
                $sale->start_reserve = Input::get('start_reserve');
                $sale->fri_extras = Input::get('fri_extras');
                $sale->fri_end_display = Input::get('fri_end_display');
                $sale->fri_end_reserve = Input::get('fri_end_reserve');
                $sale->sat_extras = Input::get('sat_extras');
                $sale->sat_end_display = Input::get('sat_end_display');
                $sale->sat_end_reserve = Input::get('sat_end_reserve');
                $sale->sun_extras = Input::get('sun_extras');
                $sale->sun_end_display = Input::get('sun_end_display');
                $sale->sun_end_reserve = Input::get('sun_end_reserve');
                $sale->end_extras = Input::get('end_extras');
                $sale->end_display = Input::get('end_display');
                $sale->end_reserve = Input::get('end_reserve');
                $sale->computeSales();
                $sale->save();
                return $sale;
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::json(array(
                            'success' => false,
                            'message' => $e->getMessage(),
                            'data' => null));
            }
        } else {
            return Response::json(array('success' => false, 'message' => 'Insufficient Privileges', 'data' => null));
        }
    }

    public function updatetargets($bookfair_id, $id) {
        //TODO: Security Check
        try {
            $target = Target::find($id);
            $target->name = Input::get('name');
            $target->label = Input::get('label');
            $target->measure = Input::get('measure');
            $target->target = Input::get('target');
            $target->allocate = Input::get('allocate');
            $target->track = Input::get('track');
            $grpid = Input::get('tablegroup_id');
            if (is_null($grpid)) {
                $target->tablegroup_id = null;
            } else {
                if ($grpid <> $target->tablegroup_id) {
                    $group = TableGroup::find($grpid);
                    $target->tablegroup()->associate($group);
                }
            }
            $palletid = Input::get('pallet_id');
            if (is_null($palletid)) {
                $target->pallet_id = null;
            } else {
                if ($palletid <> $target->pallet_id) {
                    $pallet = Pallet::find($palletid);
                    $target->pallet()->associate($pallet);
                }
            }
            $target->save();
            return $target;
        } catch (Exception $e) {
            //TODO: Pretty up the exception emssage.
            return Response::json(array(
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data' => null));
        }
    }

}
