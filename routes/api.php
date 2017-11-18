<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', 'Api\UserController@login');

//Route::post('details', 'Api\UserController@details');

Route::group(['middleware' => 'auth:api'], function(){

	Route::get('details', 'Api\UserController@details');
	Route::get('attendance','Api\UserController@attendance');
	Route::post('attendance-create','Api\UserController@attendance_create');
	Route::post('student-classwise','Api\UserController@student_classwise');
	Route::post('/attendance-view','Api\UserController@attendance_view');

});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
