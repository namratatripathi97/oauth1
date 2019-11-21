<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use Socialite;
use App\User;  
use App\Credential;   
use App\IntegrationName;   
use Illuminate\Support\Facades\Storage;   

class SocialController extends Controller
{
    
	 public function redirect($provider)
	 {
	     return Socialite::driver($provider)->redirect();
	 }
	 public function callback($provider)  
	 { 
	   $getInfo = Socialite::driver($provider)->user();         
	  /* dd($getInfo);    */ 
	   $user = $this->createUser($getInfo,$provider); 
	   auth()->login($user); 
	   return redirect()->to('/home');   
	 }
	 function createUser($getInfo,$provider){      
	  
	 /*dd($getInfo); */       
	 $user = User::where('provider_id', $getInfo->id)->first();
	 if (!$user) {
	      $user = User::create([ 
	         'name'     => $getInfo->name,  
	         'email'    => $getInfo->email,
	         'provider' => $provider,
	         'provider_id' => $getInfo->id
	     ]);
	   }
	   return $user;      
	 }
	 public function viewClient()
	 {
	 	$integrationname=IntegrationName::all();  
	 	
	 	return view('client',["integrationname"=>$integrationname]);           
	 }
	 public function addClient(Request $request)
	 {

	 	$request=$request->all();            
	 	$url="https://oauth.redwoodtechnologysolutions.com/wp/oauth/public/api/".$request['name']."/".$request['client_name']."/".$request['apicall']."";
	 	Credential::create($request);            
  		     
  		//return redirect('api/view-client')->with('status', 'Client Created successfully !');  
  		return response('Client Created successfully ! '.$url.'', 200)->header('Content-Type', 'text/plain');        
	 	//return "Client Save successfully";   
  
	 	//return view('client');      
	 }  
	  public function addIntegrationName(Request $request)
	 {
  
	 	$request=$request->all();
	 	IntegrationName::create($request);          
  		return response('Integration Name Created successfully !', 200)->header('Content-Type', 'text/plain');        
	 }
	public function executeApi($name,$clientname,$apicall)
	{
 
			$credential_details = Credential::where('name',$name)->where('client_name',$clientname)->first();
	 		$username=$credential_details->username;
		    $password=$credential_details->password;
			$apiurl=$credential_details->url; 
			$id=$credential_details->id; 
			$client_id=$credential_details->client_id;  
			$apikey=$credential_details->client_secret;     
			$refresh_token=$credential_details->refresh_token;  
			$access_token=$credential_details->access_token;    
  	   
  			$post = $_POST;                         
  			$fname = $_POST['1_3'];   
			$lname = $_POST['1_6'];           
			$email = $_POST['2']; 
			$phone = $_POST['3'];          
			$resume_status = $_POST['4'];       
			$filedata=$_POST['5'];           
			$job=$_POST['7'];   
			//$job="JOB-1007";
			if ((!empty($job)) AND (strpos($job,'JOB-') !== false)) {			
			//if (!empty($job)) {      
 				 $job_id=explode('-', $job)[1];  
			}  
			else if(!empty($job))
			{   
				$job_id=$job;  
			}
			else {
				$job_id=" ";  
			}
			/*$post = $_POST;                      
  			$fname = $_POST['fname'];  
			$lname = $_POST['lname'];   
			$fname="Lokesh";  
			$lname="Jain";     
			$job_id='1007';   
			$resume_status="Yes";  */             
			$applicant_name=$fname.' '.$lname;  
  			 
			
			   
		  


			//$applicant_name="rahul jshii";   
			//$filedata="https://jobs.tracker-rms.com/wp-content/uploads/gravity_forms/1-9c988dc1818c14684b17edf95218545c/2019/11/283559722.pdf";   
			
 			                       
 
              
			if($apicall=='createResource')                
			{            
              
     			   
					if($resume_status=="No")  
					{   
     		     


						/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": 16541,"assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobid": "'.$job_id.'","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}'; */
     
						/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';    */            
   
							$postResource = '{"trackerrms": {"createResource": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short","shortlistedby": "resource"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';                      
 
  
								/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"apikey": "yl4luqj0drBGpOjU5Q6P"},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';   */  
      

							/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"username": " ","password": " ","apikey": "'.$apikey.' "},"instructions":{"overwriteresource": true,"assigntoopportunity": 16541,"assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobid": "'.$job_id.'","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';  */    
				       
									$curl = curl_init();                 
									curl_setopt_array($curl, array(   
									 CURLOPT_URL => $apiurl.$apicall, 
									 CURLOPT_RETURNTRANSFER => true,       
									 CURLOPT_ENCODING => "", 
									 CURLOPT_MAXREDIRS => 10,    
									 CURLOPT_TIMEOUT => 30,
									 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,          
									 CURLOPT_CUSTOMREQUEST => "POST",
									 CURLOPT_POSTFIELDS => $postResource,  
									 CURLOPT_HTTPHEADER => array(       
									   "Content-Type: application/json",          
									 ), 
									));    
									$response = curl_exec($curl);       
									$err = curl_error($curl);   
									curl_close($curl);
									if ($err) {
									 echo "cURL Error #:" . $err;  
									} else {  
									 echo $response;  

									}  

				}
				else
				{  


								$ext = pathinfo($filedata, PATHINFO_EXTENSION);
								$filename=$fname.' '.$lname.'.'.$ext;
								$filecontent = file_get_contents($filedata);                
								 Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   
								$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
								$file = chunk_split(base64_encode($path));     

								$apicall="createResourceFromResume";   
								$postResume='{"trackerrms": {"createResourceFromResume": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short","shortlistedby": "resource"},"resource": {"firstname": "'.$fname.'","lastname": "'.$lname.'","fullname": "'.$fname.' '.$lname.'","jobtitle": " ","email": "'.$email.'","source": "Jobs +"},"file": {"filename": "'.$filename.'","data": "'.$file.'"}}}}';        

/*$postResume='{  
	"trackerrms": {
		"createResourceFromResume": {
			"credentials": {  
				"username": "'.$username.'",
				"password": "'.$password.'",  
				"apikey": "'.$apikey.'"    
			},
			"instructions": {
				"assigntoopportunity": "'.$job_id.'",
				"assigntolist": "short"
			},
			"resource": {
				"firstname": "Love",
				"lastname": "Sharma",
				"fullname": "Love Sharma",
				"jobtitle": "",
				"email": "love.sharma@somewhere.com",
				"source": "Jobs +"
			},  
			"file": {
				"filename": "Love Sharma.pdf",
				"data": "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eXBlL0NhdGFsb2cvUGFnZXMgMTAgMCBSPj4KZW5kb2JqCjEyIDAgb2JqCjw8L01vZERhdGUoRDoyMDE5MDkzMDIyNTQwNS0wNycwMCcpL0NyZWF0aW9uRGF0ZShEOjIwMTkwOTMwMjI1NDA1LTA3JzAwJykvUHJvZHVjZXIoaVRleHQgMi4xLjcgYnkgMVQzWFQpPj4KZW5kb2JqCnhyZWYKMCAxMwowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMTI5MDEgMDAwMDAgbiAKMDAwMDAwMDAxNSAwMDAwMCBuIAowMDAwMDEzMTQyIDAwMDAwIG4gCjAwMDAwMTMyMzAgMDAwMDAgbiAKMDAwMDAxMzMyMyAwMDAwMCBuIAowMDAwMDA3MDY3IDAwMDAwIG4gCjAwMDAwMDczMTEgMDAwMDAgbiAKMDAwMDAxMDgzMiAwMDAwMCBuIAowMDAwMDExMTUxIDAwMDAwIG4gCjAwMDAwMTM0MjMgMDAwMDAgbiAKMDAwMDAxMzQ4NyAwMDAwMCBuIAowMDAwMDEzNTM0IDAwMDAwIG4gCnRyYWlsZXIKPDwvSW5mbyAxMiAwIFIvSUQgWzwxYjFjMTI4MGJiOWVkMzZhZWYzMjY5ZGU1MTkyOTRmMD48OTFjM2Q1YTgyZjQ1NGNmMjBlODUwMGUwMDM1NTM2NWE+XS9Sb290IDExIDAgUi9TaXplIDEzPj4Kc3RhcnR4cmVmCjEzNjU3CiUlRU9GCg=="
			}
		}
	}
}';          */    
	  
  
									$curl = curl_init();
									curl_setopt_array($curl, array(     
									 CURLOPT_URL => $apiurl.$apicall,   
									 CURLOPT_RETURNTRANSFER => true,     
									 CURLOPT_ENCODING => "",    
									 CURLOPT_MAXREDIRS => 10,    
									 CURLOPT_TIMEOUT => 30,
									 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
									 CURLOPT_CUSTOMREQUEST => "POST",
									 CURLOPT_POSTFIELDS => $postResume,          
									 CURLOPT_HTTPHEADER => array(            
									   "Content-Type: application/json",      
									 ),   
									));
									$response = curl_exec($curl);       
									$err = curl_error($curl);           
									curl_close($curl);
									if ($err) {       
									 echo "cURL Error #:" . $err;
									} else {
									 echo $response;

									}   

				}
			}
			if($apicall=='createResourceFromResume')   
			{ 

					/*$postResume = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": 16541,"assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","cellphone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';*/

					$postResume='{"trackerrms": {"createResourceFromResume": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions": {"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short"},"resource": {"firstname": "'.$fname.'","lastname": "'.$lname.'","fullname": "'.$fname.' '.$lname.'","jobtitle": " ","email": "'.$email.'","source": "Jobs +"},"file": {"filename": "'.$fname.' '.$lname.'Resume.docx","data": "'.$attach_resume.'"}}}}';
	     
					$curl = curl_init();    
					curl_setopt_array($curl, array(        
					 CURLOPT_URL => $apiurl.$apicall,   
					 CURLOPT_RETURNTRANSFER => true,     
					 CURLOPT_ENCODING => "",    
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => $postResume,          
					 CURLOPT_HTTPHEADER => array(            
					   "Content-Type: application/json",      
					 ),   
					));
					$response = curl_exec($curl);       
					$err = curl_error($curl);           
					curl_close($curl);
					if ($err) {     
					 echo "cURL Error #:" . $err;
					} else {
					 echo $response;

					}
  
			}      
			if($apicall=='getRecords')   
			{ 
					$getRecord='{"trackerrms": {"getRecords": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions": {"recordtype": "R","state": "","searchtext": "","onlyrecords": true}}}}';  
	            
					$curl = curl_init();       
					curl_setopt_array($curl, array(              
					 CURLOPT_URL => $apiurl.$apicall,   
					 CURLOPT_RETURNTRANSFER => true,     
					 CURLOPT_ENCODING => "",   
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => $getRecord,          
					 CURLOPT_HTTPHEADER => array(            
					   "Content-Type: application/json",         
					 ),   
					));
					$response = curl_exec($curl);       
					$err = curl_error($curl);           
					curl_close($curl);
					if ($err) {     
					 echo "cURL Error #:" . $err;
					} else {
					 echo $response;

					}
   
			} 
			if($apicall=='applicants')   
			{ 


					$applicants='{"username": "'.$username.'","password": "'.$password.'","candidate_first_name": "Tom","candidate_last_name": "Larsen","candidate_ email": "unixolutions @gmail.com","candidate_phone_number": "7703301987","job_id": "67","resume": ""}';     

					$curl = curl_init();       
					curl_setopt_array($curl, array(            
					 CURLOPT_URL => $apiurl.$apicall,   
					 CURLOPT_RETURNTRANSFER => true,     
					 CURLOPT_ENCODING => "",   
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,              
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => $applicants,          
					 CURLOPT_HTTPHEADER => array(              
					   "Content-Type: application/json",        
					 ),   
					));
					$response = curl_exec($curl);       
					$err = curl_error($curl);               
					curl_close($curl);
					if ($err) {     
					 echo "cURL Error #:" . $err;
					} else {
					 echo $response;

					}  
  
			} 
			if($apicall=='createCandidate')   
			{   



									$url = $apiurl; 
									   
									$postdata  = "grant_type=refresh_token";  
									$postdata .= "&refresh_token=".$refresh_token;
									$postdata .= "&client_id=".$client_id;         
									$postdata .= "&client_secret=".$apikey;       

									$ch = curl_init($url);      
									curl_setopt($ch, CURLOPT_POST, true);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									$result = curl_exec($ch);        
									   
									$response = json_decode($result);
									$access_token = $response->access_token; 
 									$refresh_token =$response->refresh_token;    

 									$credentials_update=Credential::find($id);
 									$credentials_update->access_token  = $access_token;
									$credentials_update->refresh_token = $refresh_token; 
									$credentials_update->save();            
  
									//$access_token=$access_token;   
									$url1="https://rest.bullhornstaffing.com/rest-services/login";  
									$postdata1  = "version=*";
									$postdata1 .= "&access_token=".$access_token; 
									   
									 
									$ch1 = curl_init($url1);
									curl_setopt($ch1, CURLOPT_POST, true);
									curl_setopt($ch1, CURLOPT_POSTFIELDS, $postdata1);   
									curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
									$result1 = curl_exec($ch1);     

									$response1 = json_decode($result1);
									$resturl = $response1->restUrl;  
									$bhtoken = $response1->BhRestToken;       
 
									
									$url=$resturl."entity/Candidate?BhRestToken=".$bhtoken;     
									$postResume='{"firstName": "'.$fname.'","lastName": "'.$lname.'"}';           
									$curl = curl_init();
									curl_setopt_array($curl, array(      
									 CURLOPT_URL => $url,           
									 CURLOPT_RETURNTRANSFER => true,         
									 CURLOPT_ENCODING => "",    
									 CURLOPT_MAXREDIRS => 10,      
									 CURLOPT_TIMEOUT => 30,    
									 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
									 CURLOPT_CUSTOMREQUEST => "PUT",
									 CURLOPT_POSTFIELDS => $postResume,          
									 CURLOPT_HTTPHEADER => array(            
									   "Content-Type: application/json",      
									 ),   
									));
									$response = curl_exec($curl);       
									$err = curl_error($curl);            
									curl_close($curl);  
									if ($err) {     
									 echo "cURL Error #:" . $err;   
									} else {
									 echo $response;  
									 $responseTest = json_decode($response);  
									} 

									// post a job 

									$url2=$resturl."entity/JobSubmission?BhRestToken=".$bhtoken;     
									$candidateId =$responseTest->changedEntityId; 
									$postJob2='{"candidate": {"id": "'.$candidateId.'"},"jobOrder": {"id": "'.$job_id.'"},"status": "New Lead"}';  
									//echo $postJob2; exit;									
									$curl2 = curl_init();
									curl_setopt_array($curl2, array(        
									 CURLOPT_URL => $url2,           
									 CURLOPT_RETURNTRANSFER => true,           
									 CURLOPT_ENCODING => "",      
									 CURLOPT_MAXREDIRS => 10,      
									 CURLOPT_TIMEOUT => 30,    
									 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,             
									 CURLOPT_CUSTOMREQUEST => "PUT", 
									 CURLOPT_POSTFIELDS => $postJob2,          
									 CURLOPT_HTTPHEADER => array(            
									   "Content-Type: application/json",        
									 ),   
									));
									$response2 = curl_exec($curl2);       
									$err2 = curl_error($curl2);             
									curl_close($curl2);  
									if ($err2) {     
									 echo "cURL Error #:" . $err2;   
									} else {
									 echo $response2;  
									 //$responseTest2 = json_decode($response);  
									} 
									//end post job
 
									if($resume_status=="Yes")  
										{       
											$changedEntityId =$responseTest->changedEntityId; 
											$url1=$resturl."file/Candidate/".$changedEntityId."?BhRestToken=".$bhtoken;     
											$postResume1='{"externalID": "portfolio","fileContent": "'.$file.'","fileType": "SAMPLE","name": "'.$filename.'"}';
			
												$curl1 = curl_init();
												curl_setopt_array($curl1, array(     
												 CURLOPT_URL => $url1,               
												 CURLOPT_RETURNTRANSFER => true,           
												 CURLOPT_ENCODING => "",    
												 CURLOPT_MAXREDIRS => 10,      
												 CURLOPT_TIMEOUT => 30,    
												 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
												 CURLOPT_CUSTOMREQUEST => "PUT",
												 CURLOPT_POSTFIELDS => $postResume1,          
												 CURLOPT_HTTPHEADER => array(            
												   "Content-Type: application/json",      
												 ),   
												));
												$response1 = curl_exec($curl1);       
												$err1 = curl_error($curl1);            
												curl_close($curl1);  
												if ($err1) {     
												 echo "cURL Error #:" . $err1;
												} else {    
												 echo $response1;  

												}     

										}
									
  
			}  
	 		        
	 }     

}
