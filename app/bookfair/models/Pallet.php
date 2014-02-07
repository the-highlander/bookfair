<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Pallet extends Eloquent  {

    protected $table = "pallets";
    protected $guarded = array('id');
    public $timestamps = false;

    public function winterAssignments() {
        return $this->hasMany('Bookfair\Category')
            ->whereNotNull('label')
            ->whereWinter(true)
            ->join('sections', 'categories.section_id', '=', 'sections.id')
            ->select('pallet_id', 'section_id', 'sections.name', DB::raw('MIN(categories.label) AS minlabel'), DB::raw('MAX(categories.label) AS maxlabel'))
            ->groupBy('pallet_id', 'section_id',
                DB::raw('(SELECT min(c1.label) FROM categories c1 WHERE c1.section_id = categories.section_id AND c1.pallet_id <> categories.pallet_id AND c1.label > categories.label)'),
                DB::raw('substr(label, 1, 1)'))
            ->orderBy(DB::raw('sections.name, minlabel'));
    }

    public function assignments() {
        return $this->hasMany('Bookfair\Category')
            ->whereNotNull('label')
            ->where(function($query) { 
                $query->where('spring', 1)
                    ->orWhere('autumn', 1);
            })
            ->join('sections', 'categories.section_id', '=', 'sections.id')
            ->select('pallet_id', 'section_id', 'sections.name', DB::raw('MIN(categories.label) AS minlabel'), DB::raw('MAX(categories.label) AS maxlabel'))
            ->groupBy('pallet_id', 'section_id',
                DB::raw('(SELECT min(c1.label) FROM categories c1 WHERE c1.section_id = categories.section_id AND c1.pallet_id <> categories.pallet_id AND c1.label > categories.label)'),
                DB::raw('substr(label, 1, 1)'))
            ->orderBy(DB::raw('sections.name, minlabel'));
    }

}
 
?>