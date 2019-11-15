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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/auth/redirect/{provider}', 'SocialController@redirect'); 
Route::get('/callback/{provider}', 'SocialController@callback');    
//Route::get('/api/{name}/{client}/{call}','SocialController@executeApi');     
 
Route::post('/api/{name}/{client}/{call}','SocialController@executeApi');  
Route::get('/api/view-client','SocialController@viewClient')->name('view-client');              
Route::post('/api/addClient','SocialController@addClient')->name('addClient');          
Route::post('/api/addIntegrationName','SocialController@addIntegrationName')->name('addIntegrationName');                
       
//Route::post('/api/TrackerRms/Bruce/createResource','SocialController@executeApi');             