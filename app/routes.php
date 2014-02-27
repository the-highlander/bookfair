<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'HomeController@showWelcome');

Route::get('login', 'Bookfair\AuthController@showLogin');
Route::post('login', 'Bookfair\AuthController@postLogin');

Route::get('logout', function() {
    Auth::logout();
    return Redirect::to('login');
});

Route::get('test', function() {
    return DB::getQueryLog();
});

Route::get('bookfairs', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@index'));
Route::post('bookfairs/0', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@create'));
Route::put('bookfairs/0', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@create'));
Route::put('bookfairs/{bookfair}/add/category/{category}', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@create'));
Route::put('bookfairs/{bookfair}', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@update'));
Route::get('bookfairs/{bookfair}', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@show'));
Route::delete('bookfairs/{bookfair}', array('before'=>'auth', 'uses'=>'Bookfair\BookfairController@destroy'));
 
Route::get('categories', array('before'=>'auth', 'uses'=>'Bookfair\CategoryController@index'));
Route::post('categories/0', array('before'=>'auth', 'uses'=>'Bookfair\CategoryController@create'));
Route::put('categories/{id}', array('before'=>'auth', 'uses'=>'Bookfair\CategoryController@update'));
Route::get('categories/{id}', array('before'=>'auth', 'uses'=>'Bookfair\CategoryController@show'));
Route::delete('categories/{id}', array('before'=>'auth', 'uses'=>'Bookfair\CategoryController@destroy'));

Route::get('desktop', array('before'=>'auth', 'uses'=>'Bookfair\DesktopController@show'));

Route::get('divisions', array('before'=>'auth', 'uses'=>'Bookfair\DivisionController@index'));
Route::post('divisions/0', array('before'=>'auth', 'uses'=>'Bookfair\DivisionController@create'));
Route::put('divisions/{id}', array('before'=>'auth', 'uses'=>'Bookfair\DivisionController@update'));
Route::get('divisions/{id}', array('before'=>'auth', 'uses'=>'Bookfair\DivisionController@show'));
Route::delete('divisions/{id}', array('before'=>'auth', 'uses'=>'Bookfair\DivisionController@destroy'));

Route::get('people', array('before'=>'auth', 'uses'=>'Bookfair\PersonController@index'));
Route::post('people/0', array('before'=>'auth', 'uses'=>'Bookfair\PersonController@create'));
Route::put('people/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PersonController@update'));
Route::get('people/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PersonController@show'));
Route::delete('people/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PersonController@destroy'));

Route::get('statistics/bookfair/{bookfair}/allocations', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@allocations'));
Route::put('statistics/bookfair/{bookfair}/allocations/{id}', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@updateallocation'));
Route::get('statistics/bookfair/{bookfair}/sales', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@sales'));
Route::put('statistics/bookfair/{bookfair}/sales/{id}', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@updatesales'));
Route::get('statistics/bookfair/{bookfair}/targets', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@targets'));
Route::put('statistics/bookfair/{bookfair}/targets/{id}', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@updatetargets'));
Route::delete('statistics/bookfair/{bookfair}/targets/{id}', array('before'=>'auth', 'uses'=>'Bookfair\StatisticController@destroy'));

Route::get('forms/bookfair/{bookfair}/sales/tallysheet', array('before'=>'auth', 'uses'=>'Bookfair\FormsController@salestallysheets'));
Route::get('forms/bookfair/{bookfair}/division/{division}/sales/tallysheet', array('before'=>'auth', 'uses'=>'Bookfair\FormsController@salestallysheets'));
Route::get('forms/statistics/bookfair/{bookfair}/attendance', array('before'=>'auth', 'uses'=>'Bookfair\FormsController@attendance'));
Route::get('forms/statistics/bookfair/{bookfair}/summary', array('before'=>'auth', 'uses'=>'Bookfair\FormsController@summary'));
Route::get('forms/statistics/bookfair/{bookfair}/details', array('before'=>'auth', 'uses'=>'Bookfair\FormsController@details'));

Route::get('forms/bookfair/{bookfair}/pallet/assignments', array('uses'=>'Bookfair\FormsController@palletassignments'));
Route::get('forms/bookfair/{bookfair}/pallet/packingsheets', array('uses'=>'Bookfair\FormsController@packingsheets'));
Route::get('forms/bookfair/{bookfair}/pallet/tallysheets', array('uses'=>'Bookfair\FormsController@pallettally'));
Route::get('forms/bookfair/{bookfair}/allocation/boxdrops', array('uses'=>'Bookfair\FormsController@boxdrops'));

Route::get('pallets', array('before'=>'auth', 'uses'=>'Bookfair\PalletController@index'));
Route::post('pallets/0', array('before'=>'auth', 'uses'=>'Bookfair\PalletController@create'));
Route::put('pallets/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PalletController@update'));
Route::get('pallets/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PalletController@show'));
Route::delete('pallets/{id}', array('before'=>'auth', 'uses'=>'Bookfair\PalletController@destroy'));

Route::get('sections', array('before'=>'auth', 'uses'=>'Bookfair\SectionController@index'));
Route::post('sections/0', array('before'=>'auth', 'uses'=>'Bookfair\SectionController@create'));
Route::put('sections/{id}', array('before'=>'auth', 'uses'=>'Bookfair\SectionController@update'));
Route::get('sections/{id}', array('before'=>'auth', 'uses'=>'Bookfair\SectionController@show'));
Route::delete('sections/{id}', array('before'=>'auth', 'uses'=>'Bookfair\SectionController@destroy'));

Route::get('tablegroups', array('before'=>'auth', 'uses'=>'Bookfair\TableGroupController@index'));
Route::post('tablegroups/0', array('before'=>'auth', 'uses'=>'Bookfair\TableGroupController@create'));
Route::put('tablegroups/{id}', array('before'=>'auth', 'uses'=>'Bookfair\TableGroupController@update'));
Route::get('tablegroups/{id}', array('before'=>'auth', 'uses'=>'Bookfair\TableGroupController@show'));
Route::delete('tablegroups/{id}', array('before'=>'auth', 'uses'=>'Bookfair\TableGroupController@destroy'));

Route::get('users', array('before'=>'auth', 'uses'=>'Bookfair\UserController@index'));
Route::post('users/0', array('before'=>'auth', 'uses'=>'Bookfair\UserController@create'));
Route::put('users/{id}', array('before'=>'auth', 'uses'=>'Bookfair\UserController@update'));
Route::get('users/{id}', array('before'=>'auth', 'uses'=>'Bookfair\UserController@show'));
Route::delete('users/{id}', array('before'=>'auth', 'uses'=>'Bookfair\UserController@destroy'));
