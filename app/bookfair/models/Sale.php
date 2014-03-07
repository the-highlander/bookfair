<?php 

namespace Bookfair;

class Sale extends Statistic {

    public $hidden = array(
        'allocate',
        'packed',
        'position',
        'suggested',
        'tablegroup_id', 
        'target',
        'track'
    );

    /**
    * Overrides (Laravel or)Illuminate\Database\Eloquent\Model's query() so that the Sales Model will only return 
    * rows from the Statistics table where track = true (ie tracking sales for this category)
    *
    * @return mixed
    */
    public function newQuery($excludeDeleted = true) {
        parent::newQuery($excludeDeleted);
        $query = parent::newQuery();
        $query->whereTrack(1);
        return $query;
    }

}

?>