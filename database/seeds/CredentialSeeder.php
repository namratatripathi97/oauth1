<?php

use Illuminate\Database\Seeder;
use App\Credential;  

class CredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

 
        /*Credential::query()->truncate();   */

        $users = [
            ['name' => 'TrackerRms', 'client_name' => 'Bruce', 'url' => 'https://evoapi.tracker-rms.com/api/widget/', 'username' => 'bruce@brucestander.com', 'password' =>'Luju6277', 'client_secret' =>'yl4luqj0drBGpOjU5Q6P'],
            ['name' => 'Hephaestus', 'client_name' => 'Bruce', 'url' => 'https://federatedstaffing.com/ins/api/', 'username' => 'camryn@innovien.com','password' =>'9dac8bf670c03a2b0a750f29836b81894a3593b0'],
            ['name' => 'TrackerRms', 'client_name' => 'John', 'url' => 'https://evoapi.tracker-rms.com/api/widget/', 'username' => 'bruce@brucestander.com', 'password' =>'Luju6277', 'client_secret' =>'yl4luqj0drBGpOjU5Q6P'],
            ['name' => 'TrackerRms', 'client_name' => 'Peter', 'url' => 'https://evoapi.tracker-rms.com/api/widget/', 'username' => 'bruce@brucestander.com', 'password' =>'Luju6277', 'client_secret' =>'yl4luqj0drBGpOjU5Q6P']      
        ]; 
  
        foreach($users as $user){     

$user1 = Credential::where('name', '=', $user['name'])->where('client_name', '=', $user['client_name'])->where('url', '=', $user['url'])->where('username', '=', $user['username'])->where('password', '=', $user['password'])->first();
            if ($user1 === null) {  
                    
               Credential::create($user); 
            }  
              
        }   
        //Seeder for Tracker RMS
        /*$credential= new Credential();
        $credential->name='TrackerRms';
        $credential->client_name='Bruce';
        $credential->url='https://evoapi.tracker-rms.com/api/widget/';
        $credential->username='bruce@brucestander.com';
        $credential->password='Luju6277';
        $credential->client_secret='yl4luqj0drBGpOjU5Q6P';
        $credential->save();   */    
       
        //Seeder for Hephaestus   
     /*   $credential= new Credential(); 
        $credential->name='Hephaestus';
        $credential->client_name='Bruce';
        $credential->url='https://federatedstaffing.com/ins/api/';    
        $credential->username='camryn@innovien.com';
        $credential->password='9dac8bf670c03a2b0a750f29836b81894a3593b0';   
        $credential->save();  */   


    }
}
