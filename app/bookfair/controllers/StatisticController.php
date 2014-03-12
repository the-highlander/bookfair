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
        return Allocation::with('stats.category.section', 'tablegroup')->forBookfair($bookfair_id)->get();
    }

    public function create($bookfair_id) {
        try {
            $bookfair = Bookfair::find($bookfair_id);
            $category = Category::find(Input::get('category_id'));
            $target = new Target(array(
                'allocate' => Input::get('allocate'),
                'measure' => Input::get('measure'),
                'track' => Input::get('track'),
                'label' => Input::get('label'),
                'name' => Input::get('name')
            ));
            if ($target->measure == "") {
                $target->measure = 'table';
            }
            // Calculate loading as average loading of all prior bookfairs with the same season!
            // or previous bookfair's actual loading?
            $target->bookfair()->associate($bookfair);
            $target->category()->associate($category);
            $target->save();
            return Target::with('category.section', 'pallet')->find($target->id);
        } catch (Exception $e) {
            return Response::json(array(
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data' => null));
        }
    }

    public function freecats($bookfair_id) {
        $result = Category::whereNotExists(function($query) use ($bookfair_id) {
                    $query->select(DB::raw(1))
                            ->from('statistics')
                            ->whereBookfairID($bookfair_id)
                            ->whereRaw('statistics.category_id = categories.id');
                })->get();
        return $result;
    }

    public function freesecs($bookfair_id) {
        $result = Section::whereExists(function($query) use ($bookfair_id) {
                    $query->select(DB::raw(1))
                            ->from('categories')
                            ->whereRaw('categories.section_id = sections.id')
                            ->whereNotExists(function ($query) use ($bookfair_id) {
                                $query->select(DB::raw(1))
                                        ->from('statistics')
                                        ->whereBookfairID($bookfair_id)
                                        ->whereRaw('statistics.category_id = categories.id');
                            });
                })->get();
        return $result;
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
                        'message' => 'Statistics Record ' . $stats_id . ' not found',
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
            $allocation = Allocation::find($id);
            $allocation->loading = Input::get('loading');
            $allocation->tables = Input::get('tables');
            $allocation->suggested = Input::get('suggested');
            $allocation->position = Input::get('position');
            $allocation->display = Input::get('display');
            $allocation->reserve = Input::get('reserve');
            $grpid = Input::get('tablegroup_id');
            if (is_null($grpid)) {
                $allocation->tablegroup_id = null;
            } else {
                if ($grpid <> $allocation->tablegroup_id) {
                    $allocation->position = Allocation::forTablegroup($bookfair_id, $grpid)->count() + 1;
                    $group = TableGroup::find($grpid);
                    $allocation->tablegroup()->associate($group);
                }
            }
            $allocation->save();
            $stock = $allocation->stats()->first();           
            $stock->packed = Input::get('packed');
            $stock->save();
            return Allocation::with('stats.category.section', 'tablegroup')->find($id);
        } catch (Exception $e) {
            //TODO: Pretty up the exception emssage.
            return Response::json(array(
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data' => null));
        }
    }

    public function updatesales($bookfair_id, $id) {
        // updating daily stock values and computing amount sold based on value of measure
        // Post will be a single stock item (on update of grid)
        if (Auth::user()->can('Stocktake')) {
            try {
                $sale = Sale::with('category.section')->find($id);
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
