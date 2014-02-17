<?php 

namespace Bookfair;
use Auth;
use BaseController;
use Input;
use Response;

class CategoryController extends BaseController {

    public function create() {
        if (Auth::user()->can('Create Categories')) {
            //TODO:: need to redevelop. allocate and track are now seasonal in separate table
            try {
                $section = Section::find(Input::get('section_id'));
                $tablegroup = TableGroup::find(Input::get('table_group_id'));
                $category = new Category(array(
                    'name'            => Input::get('name'),
                    'label'           => Input::get('label'),
                    'measure'         => Input::get('measure'),
                    'pallet_loading'  => Input::get('pallet_loading'),
                    'allocate'        => Input::get('allocate'),
                    'track'           => Input::get('track')
                ));
                $category->tableGroup()->associate($tablegroup);                
                $category->pallet()->associate($pallet);
                $category->section()->associate($section);
                $category = $category->save();
                return $category;
            } catch (Exception $e) {
                // TODO: Pretty up the exception message. Currently its the SQL dump. Not pretty
                return Response::json(array(
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
        if (Auth::user()->can('Delete Categories')) {
            $category = Category::find($id);
            if (is_null($category)) {
                return Response::json("Category not found", 404);
            } else {
                $deletedCategory = $category;
                $category->delete();
                return $deletedCategory;
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
        // No special privileges for reading categories
        return Category::with('section')->get();
    }

    public function show($id) {
        $category = Category::with('section')->with('tablegroup')->with('pallet')->find($id);
        if (is_null($category)) {
            return Response::make('Category ' . $id . ' not found', 404);
        } else {       
            return $category;
        }
    }


    public function update($id) {
        if (Auth::user()->can('Update Categories')) {
            try {
                $tablegroup = TableGroup::find(Input::get('table_group_id'));
                $pallet = Pallet::find(Input::get('pallet_id'));
                $section = Section::find(Input::get('section_id'));
                $category = Category::find($id);

                $category->name = Input::get('name');
                $category->label = Input::get('label');


                $category->allocate = Input::get('allocate');
                $category->track = Input::get('track');
                $category->spring = Input::get('spring');
                $category->autumn = Input::get('autumn');
                $category->winter = Input::get('winter');               
                $category->tablegroup()->associate($tablegroup);            
                $category->pallet()->associate($pallet);                
                $category->section()->associate($section);
                $category->save();
                return $category;
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
