<?php

use Illuminate\Database\Seeder;
use App\IntegrationName;    


class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $users = [
            ['name' => 'TrackerRms'],
            ['name' => 'Hephaestus'],
            ['name' => 'Jobscience'],  
            ['name' => 'Bullhorn']        
        ]; 
  
        foreach($users as $user){     
 
$user1 = IntegrationName::where('name', '=', $user['name'])->first();
            if ($user1 === null) {  
                    
               IntegrationName::create($user);  
            }  
              
        }   
    }
}
