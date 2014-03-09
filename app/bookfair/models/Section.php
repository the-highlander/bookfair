<?php 

namespace Bookfair;
use Eloquent;
use DB;

class Section extends Eloquent  {

    protected $table = 'sections';
    public $timestamps = false;
    
    public function scopeForBookfair ($query, $bookfair) {
        return $query
            ->whereIn('id',
                function ($query) use ($bookfair) {
                    return $query->select('section_id')
                        ->from('categories')
                        ->whereIn('id',
                            function ($query) use ($bookfair) {
                                return $query->select('category_id')
                                    ->from('statistics')
                                    ->where('bookfair_id', $bookfair);
                            }
                        );
                    }
                )
            ->select('id', 'name', DB::raw($bookfair . ' AS bookfair_id'));
    }

    public function categories() {
        return $this->hasMany('Bookfair\Category');
    }

    public function division() {
        return $this->belongsTo('Bookfair\Division')->select(array('id','name'));
    }

}