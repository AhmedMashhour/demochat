<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home', 'HomeController@index');
Route::post('/check_tables','ChatController@check_chats');
Route::post('/add_tables','ChatController@add_table');
Route::post('/send_message','ChatController@save_message');
Route::post('/get_message','ChatController@get_message');
Route::post('/readed','ChatController@readed');
Route::post('/return_image','ChatController@return_image');
