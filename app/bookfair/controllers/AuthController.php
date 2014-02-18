<?php

namespace Bookfair;

use BaseController;
use View;
use Input;
use Redirect;
use Auth;
use Session;

class AuthController extends BaseController {

    public function showLogin() {
        return View::make('login');
    }

    public function postLogin() {
        $credentials = array(
            'id' => Input::get('username'),
            'password' => Input::get('password'),
            'locked' => 0
        );
        if (Auth::attempt($credentials)) {
            return Redirect::intended('desktop');
        } else {
            Session::flash('flash_notice', 'Incorrect username or password');
            return Redirect::to('login');
        }
    }

}
