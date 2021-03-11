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
Route::get('/api/view-bullhorn','SocialController@viewBullhorn')->name('view-bullhorn'); 
Route::get('/api/edit-bullhorn','SocialController@editViewBullhorn')->name('edit-bullhorn');           
Route::post('/api/addBullhorn','SocialController@addBullhorn')->name('addBullhorn'); 
Route::post('/api/editBullhorn','SocialController@editBullhorn')->name('editBullhorn');                      
Route::post('/api/addClient','SocialController@addClient')->name('addClient');          
Route::post('/api/addIntegrationName','SocialController@addIntegrationName')->name('addIntegrationName');               

Route::get('/api/indeed','SocialController@indeedApply');     

Route::get('/api/indeed-redirect','SocialController@indeedRedirect');    
   
// CustomCallFor ProAlt Contact Form   
Route::post('/api/{clientname}/{clientintegration}','SocialController@customApi');  

Route::get('image-upload', 'ImageuploadController@image_upload')->name('image.upload');
Route::post('image-upload', 'ImageuploadController@upload_post_image')->name('upload.post.image');


//http://oauth.redwoodtechnologysolutions.com/wp/oauth/public/api/indeed?apitoken=aa102235a5ccb18bd3668c0e14aa3ea7e2503cfac2a7a9bf3d6549899e125af4&jobid=eca53bf1169ee76e590d&joblocation=Charlotte, NC&jobcompanyname=Coast Personnel Services&jobtitle=Quality Technician&joburl=https://www.indeedjobs.com/redwood-technology-solutions/jobs/eca53bf1169ee76e590d&posturl=https://dradisindeedapply.sandbox.indeed.net/process-indeedapply

/*  
  <span class="indeed-apply-widget"  
data-indeed-apply-apitoken="aa102235a5ccb18bd3668c0e14aa3ea7e2503cfac2a7a9bf3d6549899e125af4" 
data-indeed-apply-jobid="eca53bf1169ee76e590d"      
data-indeed-apply-joblocation="Charlotte, NC"   
data-indeed-apply-jobcompanyname="Coast Personnel Services" 
data-indeed-apply-jobtitle="Quality Technician" 
data-indeed-apply-joburl="https://www.indeedjobs.com/redwood-technology-solutions/jobs/eca53bf1169ee76e590d" 
data-indeed-apply-locale="en" 
data-indeed-apply-posturl="https://dradisindeedapply.sandbox.indeed.net/process-indeedapply" 
data-indeed-apply-continueurl="https://www.indeedjobs.com/redwood-technology-solutions/jobs/eca53bf1169ee76e590d" 
data-indeed-apply-jobmeta="indeed-career-pages"     
data-indeed-apply-resume="REQUIRED" 
data-indeed-apply-questions="nigma://Wmh2bl9XElVTFEddREA?locale=en_US&amp;v=3"></span>  */
       
//Route::post('/api/TrackerRms/Bruce/createResource','SocialController@executeApi');             
