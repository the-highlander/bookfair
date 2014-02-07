<?php 

namespace Bookfair;
use BaseController;
use View;

class DesktopController extends BaseController {

    public function show()
    {
        return View::make('desktop');
    }

}
