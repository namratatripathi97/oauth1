<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use Socialite;
use App\User;     
use App\Credential;    
use App\IntegrationName;   
use Illuminate\Support\Facades\Storage;   
use CURLFile;  
use PHPMailer\PHPMailer\PHPMailer;  
use PHPMailer\PHPMailer\Exception;         
use Google\Cloud\Datastore\DatastoreClient;
    
class SocialController extends Controller     
{
        
     function sendEmail($integration_name,$client_name)
     {          
     		 	 		      
     		$subject="Tokens revoked for ".$client_name." for ".$integration_name."";    

     		$message  = '';
			$message .= "Hey!" ."<br><br>";
			$message .= "Logins have changed for ".$client_name." and ".$integration_name."<br><br>";
			$message .= "Thanks!"."<br><br>";         
              
     		$email="gaurav.ideabox@outlook.com";              
     		$addcc="bruce@staffingfuture.com";                                    
     		$setfrom="oauthsupport@staffingfuture.com";         
     		

     		    $mail = new PHPMailer(true); // notice the \  you have to use root namespace here
			    try 
			    {
			        $mail->isSMTP(); // tell to use smtp
			        $mail->CharSet = "utf-8"; // set charset to utf8
			        $mail->SMTPAuth = true;  // use smpt auth
			        $mail->SMTPSecure = "tls"; // or ssl
			        $mail->Host = "smtp.sendgrid.net";       
			        $mail->Port = 587; // most likely something different for you. This is the mailtrap.io port i use for testing. 
			        $mail->Username = "apikey";
			        $mail->Password = "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";
			        $mail->setFrom($setfrom, "oAuth Support Staffing Future");
			        $mail->Subject = $subject;     
			        $mail->MsgHTML($message);                     
			        $mail->addAddress($email, "Gaurav Pareek");
			        $mail->addCC($addcc);       
			        $mail->send();    
			    }   
			    catch (phpmailerException $e) 
			    {     
			        dd($e);
			    } catch (Exception $e) 
			    {
			        dd($e);   
			    }
			     

            
     }
	 public function redirect($provider)
	 {   
	     return Socialite::driver($provider)->redirect();
	 }
	 public function callback($provider)  
	 { 
	   $getInfo = Socialite::driver($provider)->user();         
	  
	   $user = $this->createUser($getInfo,$provider); 
	   auth()->login($user); 
	   return redirect()->to('/home');   
	 }
	 function createUser($getInfo,$provider){      
	  
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
	 public function viewBullhorn()
	 {   
   
	 	return view('bullhorn-credential');           
	 }
	 public function editViewBullhorn()
	 {   
        
	 	return view('edit-credential');           
	 }
	 public function indeedApply()
	 {
	 	$apitoken=$_GET['apitoken'];  
	 	$jobid=$_GET['jobid'];  
	 	$joblocation=$_GET['joblocation'];  
	 	$jobcompanyname=$_GET['jobcompanyname'];  
	 	$jobtitle=$_GET['jobtitle'];  
	 	$joburl=$_GET['joburl'];  
	 	$posturl=$_GET['posturl'];  
	 	 
	 	 return view('indeed',["apitoken"=>$apitoken,"jobid"=>$jobid,"joblocation"=>$joblocation,"jobcompanyname"=>$jobcompanyname,"joburl"=>$joburl,"jobtitle"=>$jobtitle,"jobtitle"=>$posturl]);      

	 }
	  public function indeedRedirect()
	  {
	  	return view('indeed-redirect');  
	  }
	 
	 public function editBullhorn(Request $request)
	 {
    
	 	    
	 	$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.$request['client_id'].'&response_type=code';
    $data = "action=Login&username=".$request['username']."&password=".$request['password']."";  
   	$options = array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $data,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,   
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,       
			CURLOPT_CONNECTTIMEOUT => 120,   
			CURLOPT_TIMEOUT        => 120,   
		);
    $ch  = curl_init( $url );  
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );  
    curl_close( $ch );
    if(preg_match('#Location: (.*)#', $content, $r)) 
    {
		$l = trim($r[1]);
		$temp = preg_split("/code=/", $l);
		$authcode = $temp[1];
	  
    }                
        
    	
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code=".$authcode."&client_secret=".$request['client_secret'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);   

		curl_close($curl);
		   
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {

		 $accessToken=json_decode($response)->access_token;     	
		  echo  $response;
		}


		echo "<br/>";        
		         
		        
		$curl = curl_init();  

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://rest.bullhornstaffing.com/rest-services/login?version=*&access_token=".$accessToken,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,   
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache"  
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;       
		} else {        
		  echo  $response;
		}


	 }
	 public function addBullhorn(Request $request)
	 {

	 	$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.$request['client_id'].'&response_type=code';
    $data = "action=Login&username=".$request['username']."&password=".$request['password']."";  
   	$options = array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $data,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,   
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,       
			CURLOPT_CONNECTTIMEOUT => 120,     
			CURLOPT_TIMEOUT        => 120,   
		);
    $ch  = curl_init( $url );  
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );  
    curl_close( $ch );
    if(preg_match('#Location: (.*)#', $content, $r)) 
    {
		$l = trim($r[1]);
		$temp = preg_split("/code=/", $l);
		$authcode = $temp[1];
	  
    }                
        
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code=".$authcode."&client_secret=".$request['client_secret'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);   

		curl_close($curl);
		   
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {

		 	$result=json_decode($response);     	   
		  

		  if(isset($result->access_token) && (isset($result->refresh_token)))
		  {  
		  		
  				$call='createCandidate';     
		  		$request['name']='Bullhorn';    
		  		$request['url'] ='https://auth.bullhornstaffing.com/oauth/token'; 
		  		$request['access_token']  = $result->access_token; 
		  		$request['refresh_token'] = $result->refresh_token; 
		  		$request['source'] = $request['name'].' Websites';   
		  		$request['board_id'] = '';     
		  		$request=$request->all();   
		  	$url="https://oauth.redwoodtechnologysolutions.com/wp/oauth/public/api/".$request['name']."/".$request['client_name']."/".$call.""; 
	 			
	 			
	 			Credential::create($request);            
  		      
  		     	
  		    
  				echo 'Bullhorn Webhook Link Created successfully ! '.$url."<br/><br/><br/>";   

  				$this->addBullhornDatastore($request);           
		  }    
		}


		

	 }
	 function addBullhornDatastore($request)    
	 {
	 	  
       
   
	 	$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.$request['client_id'].'&response_type=code';
    $data = "action=Login&username=".$request['username']."&password=".$request['password']."";  
   	$options = array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $data,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,   
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,       
			CURLOPT_CONNECTTIMEOUT => 120,   
			CURLOPT_TIMEOUT        => 120,   
		);
    $ch  = curl_init( $url );  
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );  
    curl_close( $ch );
    if(preg_match('#Location: (.*)#', $content, $r)) 
    {
		$l = trim($r[1]);
		$temp = preg_split("/code=/", $l);
		$authcode = $temp[1];
	  
    }                
        
    	
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code=".$authcode."&client_secret=".$request['client_secret'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);   

		curl_close($curl);
		   
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {

		  	
		 

		   if(isset(json_decode($response)->access_token))  
		  {
		  		$accessToken=json_decode($response)->access_token;    
		  		$refreshToken=json_decode($response)->refresh_token;    

		  		$request['accessToken'] = $accessToken;
				$request['refreshToken'] = $refreshToken;  
		  }


		}


		echo "<br/>";        
		         
		        
		$curl = curl_init();  

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://rest.bullhornstaffing.com/rest-services/login?version=*&access_token=".$accessToken,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,   
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache"  
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;       
		} else {     
		  

		  if(isset(json_decode($response)->BhRestToken)) 
		  {  
		  		$BhRestToken=json_decode($response)->BhRestToken;
		  		$restUrl=json_decode($response)->restUrl;
    
 
		  		$datastore = new DatastoreClient();      
				$dataInsert = $datastore->entity('bullhorn-prod-clientDetails');
				$dataInsert['name'] = $request['client_name'];   
				$dataInsert['id'] = $request['client_id'];      
				$dataInsert['secret'] = $request['client_secret'];          
				$dataInsert['username'] = $request['username'];    
				$dataInsert['password'] = $request['password'];        
				$dataInsert['accessToken'] = $request['accessToken'];   
				$dataInsert['refreshToken'] = $request['refreshToken'];     
				$dataInsert['bhRestToken'] = $BhRestToken;    
				$dataInsert['restUrl'] = $restUrl;     
				$dataInsert['start'] = '0';      
				//$dataInsert['count'] = '500';     

				$datastore->insert($dataInsert);  

				$url="http://bullhorn.redwoodapi.com/".$request['client_name'].".xml"; 
	 			
	 			
	 			  
  		    
  				echo 'Bullhorn Feed Link Created successfully ! '.$url;  
      
			}

		}


	 }         
	 public function addClient(Request $request)
	 {


	 	$request=$request->all();       
	 	$url=$request['url'];               
	 	$name=$request['name']; 
	 	$apicall=$request['apicall'];    
	 	if($name=="TrackerRms")    
	 	{  
	 		$call="createResource";

	 		if($url==null)
	 		{
	 			$request['url'] ='https://evoapi.tracker-rms.com/api/widget/';          
	 		}
	 	} 
	 	else if($name=="Bullhorn")
	 	{
	 		$call="createCandidate";
	 		if($url==null)
	 		{
	 			$request['url'] ='https://auth.bullhornstaffing.com/oauth/token';          
	 		}

	 	}
	 	else if($name=="TopEchelon")
	 	{  
	 		$call="newApply";    
	 		if($url==null) 
	 		{    
	 			$request['url'] ='https://apigw.topechelon.com/v1/apply';          
	 		}  
	 	} 
	 	else if($name=="Brightmove")
	 	{ 
	 		$call="createApplicant";    
	 		if($url==null)
	 		{    
	 			$request['url'] ='https://secure.brightmove.com/ATS/rest/jobboard/apply/';          
	 		}  
	 	}  
	 	else if($name=="Jobscience")
	 	{
	 		$call="createContact";    
	 		if($url==null)
	 		{
	 			$request['url'] ='https://login.salesforce.com/services/oauth2/token';          
	 		}
	 	} 
	 	else if($name=="Hephaestus")   
	 	{
	 		$call="applicants";
	 		if($url==null)
	 		{
	 			$request['url'] ='https://federatedstaffing.com/ins/api/';          
	 		}        
	 	}  
	 	else if($name=="JobAdder")      
	 	{
	 		$call="newApplicant";
	 		if($url==null)    
	 		{
	 			$request['url'] ='https://id.jobadder.com/connect/token';          
	 		}           
	 	} 
	 	else if($name=="Arithon")      
	 	{
	 		$call="pushCandidate";
	 		if($url==null)    
	 		{ 
	 			$request['url'] ='https://eu.arithon.com/ArithonAPI.php';          
	 		}        
	 	}  
	 	else             
	 	{    
	 		$call=$apicall;  
	 	}
	 	       
	 	                      
	 	
	 	$url="https://oauth.redwoodtechnologysolutions.com/wp/oauth/public/api/".$name."/".$request['client_name']."/".$call.""; 
	 	Credential::create($request);            
  		     
  		
  		return response('Client Created successfully ! '.$url.'', 200)->header('Content-Type', 'text/plain');        
	 	 
	 }  
	 public function addIntegrationName(Request $request) 
	 {   
  
	 	$request=$request->all();
	 	IntegrationName::create($request);          
  		return response('Integration Name Created successfully !', 200)->header('Content-Type', 'text/plain');        
	 }

	public function customApi($name,$clientname)
	{
      
		function mysql_escape_mimic($inp) 
 			{      
			    if(is_array($inp))
			        return array_map(__METHOD__, $inp);

			    if(!empty($inp) && is_string($inp)) {
			        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
			    }
			  
			    return $inp;   
			} 
			function mysql_escape_mimic1($inp) 
 			{    
			    if(is_array($inp))
			        return array_map(__METHOD__, $inp);

			    if(!empty($inp) && is_string($inp)) {
			        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '', '', "\\'", '\\"', '\\Z'), $inp);
			    }
			    
			    return $inp;
			}  
			$credential_details = Credential::where('name',$name)->where('client_name',$clientname)->first();
	 		$username=$credential_details->username;
		    $password=$credential_details->password;
			$apiurl=$credential_details->url;   
			$id=$credential_details->id; 
			$client_id=$credential_details->client_id;  
			$apikey=$credential_details->client_secret;                 
			$refresh_token=$credential_details->refresh_token;  
			$access_token=$credential_details->access_token;     
			$source=$credential_details->source;
			$notification_status=$credential_details->notification_status;      
			$custom_source_status=$credential_details->custom_source_status;     
  	   		if(empty($source))    
  	   		{ 
  	   			$jobSource="Jobs +";
  	   		}
  	   		else
  	   		{
  	   			$jobSource=$source;   
  	   		}       
  			$post = $_POST;                         
  			$fname = $_POST['1_3'];   
			$lname = $_POST['1_6'];       
			$email = $_POST['2']; 
			$phone = $_POST['3'];
			$phone = str_ireplace( array( '\'', '"', ',' , ';', '<', '>', '(', ')', '-', ' ' ), '', $phone);  
			$resume_status = $_POST['4'];        
			$filedata=$_POST['5'];        
			$candidateStatus="New Candidate";	           
				     
  			if(isset($_POST['im_a']))    
			{     
				$im_a=$_POST['im_a'];
			}
			else
			{
				$im_a=""; 
			} 
			if(isset($_POST['area_of_interest']))    
			{   
				$area_of_interest=$_POST['area_of_interest'];
			}
			else
			{           
				
				$area_of_interest ="2000015";    
			} 
    
		  
			if(isset($_POST['company_name']))    
			{
				$company_name=$_POST['company_name'];
			}
			else
			{        
				$company_name="";    
			} 
			if(isset($_POST['message']))    
			{
				$message=$_POST['message'];
			}
			else
			{          
				$message="";     
			} 

			if(isset($_POST['division']))    
			{  
				$division=$_POST['division'];
			}
			else
			{          
				$division="";     
			}
			if(isset($_POST['age']))    
			{  
				$age=$_POST['age'];
			}
			else
			{          
				$age="";     
			}
			if(isset($_POST['shift']))    
			{  
				$shift=$_POST['shift'];
			}
			else
			{          
				$shift="";     
			}
			if(isset($_POST['day']))    
			{  
				$day=$_POST['day'];
			}
			else
			{          
				$day="";     
			}
			if(isset($_POST['jobtype']))    
			{  
				$jobtype=$_POST['jobtype'];
			}
			else
			{           
				$jobtype="";     
			}
			if(isset($_POST['workauthorized']))    
			{  
				$workauthorized=$_POST['workauthorized'];
			}
			else
			{           
				$workauthorized="";     
			}
			if(isset($_POST['dateavailable']))    
			{   
				$dateavailable=$_POST['dateavailable'];

				if($dateavailable=="Yes")
				{
					$savedateavailable=date("m/d/Y");
				}
				else
				{
					$savedateavailable=date("m/d/Y"); 
				}
			}
			else   
			{           
				$dateavailable="";     
			}

			

			if(isset($_POST['educationdegree']))    
			{  
				$educationdegree=$_POST['educationdegree'];
			}
			else 
			{           
				$educationdegree="";     
			}	  

			if(isset($_POST['staffingfutureid']))    
			{  
				$staffingfutureid=$_POST['staffingfutureid'];
			}
			else
			{          
				$staffingfutureid="";     
			}			

        	if( (isset($_POST['source'])) && ($custom_source_status==1) )      
				{

					$gsource=$_POST['source'];
					if(!empty($gsource)) 
					{
						
						$jobSource = parse_url($gsource, PHP_URL_HOST);
					}             
					else
					{           

						$jobSource=$jobSource;
					}  
					
				} 
				else
				{   
					$jobSource=$jobSource;
				}  




			$applicant_name=$fname.' '.$lname;    
  			     

				$url = $apiurl;   
								if($notification_status==1)  
								{              

									define('CLIENT_ID', $client_id);
									define('CLIENT_SECRET', $apikey);
									define('USER', $username);                   
									define('PASS', $password);          
									   
							     
									    
									  
										$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.CLIENT_ID.'&response_type=code';
									    $data = "action=Login&username=".USER."&password=".PASS."";  

									   	$options = array(
												CURLOPT_POST           => true,
												CURLOPT_POSTFIELDS     => $data,
												CURLOPT_RETURNTRANSFER => true,
												CURLOPT_HEADER         => true,
												CURLOPT_FOLLOWLOCATION => true,
												CURLOPT_AUTOREFERER    => true,
												CURLOPT_CONNECTTIMEOUT => 120,
												CURLOPT_TIMEOUT        => 120,
											);
									    $ch  = curl_init( $url );  
									    curl_setopt_array( $ch, $options );
									    $content = curl_exec( $ch );
									    curl_close( $ch );

									      
									    if(preg_match('#Location: (.*)#', $content, $r)) {
										$l = trim($r[1]);
										$temp = preg_split("/code=/", $l);
										
											if(isset($temp[1]))   
											{ 
												$authcode = $temp[1];
												echo "IF";    
												$curl = curl_init();
												curl_setopt_array($curl, array(
												  CURLOPT_URL => "https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code=".$authcode."&client_secret=".CLIENT_SECRET,
												  CURLOPT_RETURNTRANSFER => true,
												  CURLOPT_ENCODING => "",
												  CURLOPT_MAXREDIRS => 10,
												  CURLOPT_TIMEOUT => 30,
												  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
												  CURLOPT_CUSTOMREQUEST => "POST",
												  CURLOPT_HTTPHEADER => array(
												    "cache-control: no-cache",
												    "content-type: application/x-www-form-urlencoded"
												  ),
												));

												$response = curl_exec($curl);
												$err = curl_error($curl);   

												curl_close($curl);
												   
												if ($err) 
												{
												  echo "cURL Error #:" . $err;
												} 
												else 
												{
													   $response = json_decode($response);
														if(isset($response->access_token))       
														{   
															$access_token = $response->access_token; 
					 										$refresh_token =$response->refresh_token;   

					 										$credentials_update=Credential::find($id);
					 										$credentials_update->notification_status=0;
					 										$credentials_update->access_token=$access_token;
										 					$credentials_update->refresh_token=$refresh_token;
															$credentials_update->save();       

															echo "update status and access_token and refresh_token";      
														}       
												}

											}
									    }
									    else
									    {
									    	echo "invalid Client";   

									    	
											echo 'send mail';  
									    }
									     

								  }   
									   
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
									if(isset($response->access_token))   
									{
										$access_token = $response->access_token; 
 										$refresh_token =$response->refresh_token;     
									}     
									else    
									{  
										if($notification_status==0)
											{             
												  
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";      
											} 

									}    

 									$credentials_update=Credential::find($id);
 									$credentials_update->access_token  = $access_token;
									$credentials_update->refresh_token = $refresh_token;     
									$credentials_update->save();            
  
									
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



			if($im_a=='Hiring Manager')
			{
				echo 'CreateContact';        
   

				
				$url=$resturl."find?query=$company_name&countPerEntity=1";     
									echo $url;	 								
									$header = array('bhresttoken: '.$bhtoken); 
									$resource = curl_init();             
									curl_setopt($resource, CURLOPT_URL, $url);               
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);       
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);         
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									     

									if(empty($result->data)) 
									{
										$phone_status='';         
									}  
									else
									{
										$phone_status=$result->data[0]->entityId;   
									}
									  
									echo 'company status:'.$phone_status;  


									


									if( (!empty($phone_status)) )
									{
										echo 'ifloop';
										$company_id=$phone_status;  
									}
									else         
									{   

								


			   
				$url=$resturl."entity/ClientCorporation";       
    
     	    
											          
				$postClient='{"name": "'.$company_name.'","phone": "'.$phone.'","status": "Active"}';            
          
    							  
        								            
										$curl = curl_init();            
										curl_setopt_array($curl, array(                   
										 CURLOPT_URL => $url,                 
										 CURLOPT_RETURNTRANSFER => true,            
										 CURLOPT_ENCODING => "",         
										 CURLOPT_MAXREDIRS => 10,            
										 CURLOPT_TIMEOUT => 30,    
										 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
										 CURLOPT_CUSTOMREQUEST => "PUT",  
										 CURLOPT_POSTFIELDS => $postClient,          
										 CURLOPT_HTTPHEADER => array(      
										   "BhRestToken: ".$bhtoken,           
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

										 $company_id=$responseTest->changedEntityId;   
										}
				}

										$url=$resturl."find?query=$email&countPerEntity=1";    
									$header = array('bhresttoken: '.$bhtoken);                     
									$resource = curl_init();                 
									curl_setopt($resource, CURLOPT_URL, $url);           
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									   
										        
								 
									if(empty($result->data)) 
									{
										$email_status='';      
									}
									else
									{
										$email_status=$result->data[0]->entityId;   
									}     

    
									            
									  
									           
									$url=$resturl."find?query=$phone&countPerEntity=1";  
									echo $url;									
									$header = array('bhresttoken: '.$bhtoken); 
									$resource = curl_init();             
									curl_setopt($resource, CURLOPT_URL, $url);               
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);       
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);         
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									     

									if(empty($result->data)) 
									{
										$phone_status='';         
									}  
									else
									{
										$phone_status=$result->data[0]->entityId;   
									}
									echo 'email_status:'.$email_status;
									echo 'phone status:'.$phone_status;    


									if( (!empty($email_status)) && (!empty($phone_status)) )
									{
										echo 'ifloop';
										$contactId=$email_status;  
									}
									else         
									{
   										echo 'create new Contact';   
										$url=$resturl."entity/ClientContact";            

											$candidateStatus="NEW CONTACT";       
     	
											      
								$postClient='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","clientCorporation" : {"id" : "'.$company_id.'"},"source": "'.$jobSource.'","phone": "'.$phone.'","comments":"'.$message.'"}';      
				           
    							  
        								            
										$curl = curl_init();          
										curl_setopt_array($curl, array(                  
										 CURLOPT_URL => $url,               
										 CURLOPT_RETURNTRANSFER => true,            
										 CURLOPT_ENCODING => "",         
										 CURLOPT_MAXREDIRS => 10,            
										 CURLOPT_TIMEOUT => 30,    
										 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
										 CURLOPT_CUSTOMREQUEST => "PUT",  
										 CURLOPT_POSTFIELDS => $postClient,          
										 CURLOPT_HTTPHEADER => array(      
										   "BhRestToken: ".$bhtoken,           
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

									}





				   
				 

			}
			else if($im_a=='Job Seeker')   
			{   
				echo 'CreateCandidate';


				if($resume_status=="Yes"  || $resume_status=="YES" || $resume_status=="yes")       
									   {      

									   	  $ext = pathinfo($filedata, PATHINFO_EXTENSION);   
										  $filename=$fname.' '.$lname.'.'.$ext;
										  $filecontent = file_get_contents($filedata);                
								 		  Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
										  $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 
									
						$url=$resturl."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken,'Content-Type: multipart/form-data');
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
								
											// Assign POST data          
											$fields = array('file' => $cfile);           
											$resource = curl_init();     
											curl_setopt($resource, CURLOPT_URL, $url);        
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);  
											  
											$err = curl_error($resource);   
											curl_close($resource);    
											if ($err) {          
									 echo "cURL Error #:" . $err;     
									} else {
											$result_parse=json_decode($result);      
											$parsedescription=$result_parse->candidate->description;   
											$description=$parsedescription;  
											$description = mysql_escape_mimic($description);  
											
											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);  
										} 
									   }
									else     
									  {         
									  		$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email." ";  
									  }      
            

  								   //Code for check the candidate status in bullhorn 

									$url=$resturl."find?query=$email&countPerEntity=1";    
									$header = array('bhresttoken: '.$bhtoken);                     
									$resource = curl_init();                 
									curl_setopt($resource, CURLOPT_URL, $url);           
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									   
										        
								 
									if(empty($result->data)) 
									{
										$email_status='';      
									}
									else
									{
										$email_status=$result->data[0]->entityId;   
									} 


									            
									  
									           
									$url=$resturl."find?query=$phone&countPerEntity=1";  
									echo $url;									
									$header = array('bhresttoken: '.$bhtoken); 
									$resource = curl_init();             
									curl_setopt($resource, CURLOPT_URL, $url);               
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);       
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);         
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									     

									if(empty($result->data)) 
									{
										$phone_status='';         
									}  
									else
									{
										$phone_status=$result->data[0]->entityId;   
									}
									echo 'email_status:'.$email_status;
									echo 'phone status:'.$phone_status;    


									if( (!empty($email_status)) && (!empty($phone_status)) )
									{
										echo 'ifloop';
										$candidateId=$email_status;  
									}
									else         
									{           
										echo 'elseloop';
										     
  										          
										$url=$resturl."entity/Candidate";    


											   
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","address": {
            "address1": "",
            "address2": "",
            "city": "", 
            "state": "",         
            "zip": ""                          
        },"category" : {            
      "id" : "'.$area_of_interest.'"            
    },"source": "'.$jobSource.'","phone": "'.$phone.'","description":"'.$description.'","comments":"'.$message.'"}';     

    							  
        								         
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
										   "BhRestToken: ".$bhtoken,           
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
										$candidateId =$responseTest->changedEntityId; 
									}    

										if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
										{                  
											$ext = pathinfo($filedata, PATHINFO_EXTENSION);
											$filename=$fname.' '.$lname.'.'.$ext;
											$filecontent = file_get_contents($filedata);                
								 			Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   
											$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
											$file = chunk_split(base64_encode($path)); 

											$changedEntityId =$candidateId;       


											$url=$resturl."entityFiles/Candidate/".$changedEntityId."";   
											$header = array('bhresttoken: '.$bhtoken);                     
											$resource = curl_init();                 
											curl_setopt($resource, CURLOPT_URL, $url);           
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
											$result = json_decode(curl_exec($resource));  
											curl_close($resource);

											foreach ($result->EntityFiles as $rows)       
											{
												$rows->filename = $rows->name;                        
											}          
 									  
 									if(in_array($filename, array_column($result->EntityFiles, 'filename'))) 
 									{    
									    	echo 'notadded file';
									}  
									else   
									{     
  											echo 'add file';
									  
											$url1=$resturl."file/Candidate/".$changedEntityId."";        
											$postResume1='{"externalID": "portfolio","fileContent": "'.$file.'","fileType": "SAMPLE","type": "CV","name": "'.$filename.'"}';  
			      
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
												 	"BhRestToken: ".$bhtoken,        
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
										unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);     

									}     
      

									
			}
   
	}       

	public function executeApi($name,$clientname,$apicall)
	{
 
 		            	         
 			function mysql_escape_mimic($inp) 
 			{
			    if(is_array($inp))
			        return array_map(__METHOD__, $inp);

			    if(!empty($inp) && is_string($inp)) {
			        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
			    }
			  
			    return $inp;   
			} 
			function mysql_escape_mimic1($inp) 
 			{
			    if(is_array($inp))
			        return array_map(__METHOD__, $inp);

			    if(!empty($inp) && is_string($inp)) {
			        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '', '', "\\'", '\\"', '\\Z'), $inp);
			    }
			    
			    return $inp;
			}  
			$credential_details = Credential::where('name',$name)->where('client_name',$clientname)->first();
	 		$username=$credential_details->username;
		    $password=$credential_details->password;
			$apiurl=$credential_details->url;   
			$id=$credential_details->id; 
			$client_id=$credential_details->client_id;  
			$apikey=$credential_details->client_secret;                 
			$refresh_token=$credential_details->refresh_token;  
			$access_token=$credential_details->access_token;     
			$source=$credential_details->source;
			$notification_status=$credential_details->notification_status;      
			$custom_source_status=$credential_details->custom_source_status;     
  	   		if(empty($source))    
  	   		{ 
  	   			$jobSource="Jobs +";              
  	   		}
  	   		else
  	   		{
  	   			$jobSource=$source;   
  	   		}      
  			$post = $_POST;                         
  			$fname = $_POST['1_3'];   
			$lname = $_POST['1_6'];           
			$email = $_POST['2']; 
			$phone = $_POST['3'];    
			$phone = str_ireplace( array( '\'', '"', ',' , ';', '<', '>', '(', ')', '-', ' ' ), '', $phone);
			$resume_status = $_POST['4'];        
			$filedata=$_POST['5'];                
			$job=$_POST['7'];
			$defineAccessKey="OKy4aukCze";   
			
			 
			if(isset($_POST['note']))   
			{
    			$note=$_POST['note'];    
			}
			else
			{   
				$note=" ";                      
			}	     
  
  			if(isset($_POST['address1']))    
			{
				$address1=$_POST['address1'];
			}
			else
			{
				$address1=""; 
			} 
			if(isset($_POST['address2']))    
			{
				$address2=$_POST['address2'];
			}
			else
			{
				$address2=""; 
			} 
			if(isset($_POST['city']))    
			{
				$city=$_POST['city'];
			}
			else
			{  
				$city=""; 
			} 
			if(isset($_POST['state']))    
			{
				$state=$_POST['state'];
			}
			else
			{  
				$state=""; 
			} 
			if(isset($_POST['zip']))    
			{
				$zip=$_POST['zip'];
			}
			else
			{     
				$zip=""; 
			} 
			if(isset($_POST['division']))    
			{  
				$division=$_POST['division'];
			}
			else
			{          
				$division="";     
			} 
			if(isset($_POST['age']))    
			{  
				$age=$_POST['age'];
			}
			else
			{          
				$age="Yes";     
			}
			if(isset($_POST['shift']))    
			{  
				$shift=$_POST['shift'];
			}
			else
			{          
				$shift="";     
			}
			if(isset($_POST['day']))    
			{  
				$day=$_POST['day'];
			}
			else
			{          
				$day="";     
			}
			if(isset($_POST['jobtype']))    
			{  
				$jobtype=$_POST['jobtype'];
			}
			else
			{           
				$jobtype="";     
			}
			if(isset($_POST['steps']))    
			{  
				$steps=$_POST['steps'];
			}
			else
			{             
				$steps="Step1";        
			}
			if(isset($_POST['workauthorized']))    
			{  
				$workauthorized=$_POST['workauthorized'];
			}
			else
			{           
				$workauthorized="Yes";     
			}
			if(isset($_POST['selectstartdate']))    
			{  
				$selectstartdate = date("m/d/Y", strtotime($_POST['selectstartdate']));
			}
			else  
			{            
				$selectstartdate=date("m/d/Y");   
			}

			if(isset($_POST['dateavailable']))    
			{       
				$dateavailable=$_POST['dateavailable'];

				if($dateavailable=="Yes")
				{ 
					$savedateavailable=date("m/d/Y");
				}
				else   
				{
					$savedateavailable=$selectstartdate; 
				}
			}
			else   
			{                       
				$dateavailable="";     
				$savedateavailable=date("m/d/Y");
			}

			

			if(isset($_POST['employmentPreference']))    
			{  
				$employmentPreference=$_POST['employmentPreference'];
			}
			else        
			{             
				$employmentPreference="";     
			}

			if(isset($_POST['noticePeriod']))    
			{  
				$noticePeriod=$_POST['noticePeriod'];
			}
			else        
			{             
				$noticePeriod="";        
			}
			
			if(isset($_POST['educationdegree']))    
			{  
				$educationdegree=$_POST['educationdegree'];
			}
			else 
			{             
				$educationdegree="";     
			}

			if(isset($_POST['accesskey']))    
			{  
				$accesskey=$_POST['accesskey'];
			}
			else 
			{              
				$accesskey="";     
			}	   
			if(isset($_POST['staffingfutureid']))    
			{  
				$staffingfutureid=$_POST['staffingfutureid'];
			}
			else
			{          
				$staffingfutureid="";     
			}
			if(isset($_POST['7']))    
			{  
				$job_id=$_POST['7'];
			}			
						

  			
				if( (isset($_POST['source'])) && ($custom_source_status==1) )      
				{

					$gsource=$_POST['source'];
					if(!empty($gsource)) 
					{
						
						$jobSource = parse_url($gsource, PHP_URL_HOST);
					}             
					else
					{           

						$jobSource=$jobSource;
					}  
					
				} 
				else
				{
					$jobSource=$jobSource;
				}
			 
			
			if ((!empty($job)) AND (strpos($job,'JOB-') !== false)) {			
			
				$job_id=explode('-', $job)[1];
			} else if(!empty($job)) {
				$job_id=$job;
			} else {
				$job_id=0;    
			}    
			
			    
			$applicant_name=$fname.' '.$lname;    
  			 
			  
			 if(($clientname=="Bruce") && ($name=="Bullhorn"))
			 {
			 	if($defineAccessKey==$accesskey)
			 	{ 
			 		echo "MatchedKey";
 					
			 	} 
			 	else
			 	{
			 		echo "Accesskey Not Matched";  
			 		exit;
			 	} 
			 }  
			   
	
              
			if($apicall=='createResource')                
			{             
 

					//Code For TrackerRMS OAUTH Portal

					if($clientname=='diamondpeak') {
						$jobSource = 'Jobs+';
					}
					
					if($clientname=='penfield') {
						$jobSource = 'Jobs+';
					}
     			   
					if($resume_status=="No")  
					{   
     		         
     		           
   
							$postResource = '{"trackerrms": {"createResource": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short","shortlistedby": "resource"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "'.$jobSource.'","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "'.$note.'","image": ""}}}}';          
   
						
				       
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
									 $result=json_decode($response);
									 $message=$result->message;     

									 if(($notification_status==0) && ($message=="user or API Key not found")) 
											{             
												   
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";        
											}       
									 echo 'recordId'.$recordId=$result->recordId;    
									 echo 'recordName'.$recordName=$result->recordName;
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
								$postResume='{"trackerrms": {"createResourceFromResume": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short","shortlistedby": "resource"},"resource": {"firstname": "'.$fname.'","lastname": "'.$lname.'","fullname": "'.$fname.' '.$lname.'","jobtitle": " ","email": "'.$email.'","source": "'.$jobSource.'","note": "'.$note.'"},"file": {"filename": "'.$filename.'","data": "'.$file.'"}}}}';          
             
  
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
									 $result=json_decode($response);
									 $message=$result->message;  

									 if(($notification_status==0) && ($message=="user or API Key not found")) 
											{             
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";            
											}

									}   

				}
			}
			if($apicall=='newApply')   
			{ 

 
				echo 'New TopEchelon Start';   

					if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
				{
					$ext = pathinfo($filedata, PATHINFO_EXTENSION);
					$filename=$fname.' '.$lname.'.'.$ext;
					$filecontent = base64_encode(file_get_contents($filedata));
				}
				else
				{
					$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=".$fname."%20".$lname."&email=".$email."&phone=".$phone;
						$ext="pdf";
						$filename=$fname.' '.$lname.'.'.$ext;
					$filecontent = base64_encode(file_get_contents($filedata));
				}

					$postResume='{ 
	"job_id": "'.$job_id.'",
	"email_address": "'.$email.'",
	"resume_data": "'.$filecontent.'",
	"resume_filename": "'.$filename.'",
	"first_name": "'.$fname.'",
	"last_name": "'.$lname.'",
	"phone_number": "'.$phone.'",
	"venue": "'.$address1.'",
	"city": "'.$city.'",  
	"state": "'.$state.'", 
	"zip": "'.$zip.'"
}';       


						     
					$curl = curl_init();    
					curl_setopt_array($curl, array(        
					 CURLOPT_URL => $apiurl,   
					 CURLOPT_RETURNTRANSFER => true,     
					 CURLOPT_ENCODING => "",    
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => $postResume,          
					 CURLOPT_HTTPHEADER => array( 
					 "X-API-KEY: $apikey",           
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
			if($apicall=='createResourceFromResume')   
			{ 

					$postResume='{"trackerrms": {"createResourceFromResume": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions": {"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short"},"resource": {"firstname": "'.$fname.'","lastname": "'.$lname.'","fullname": "'.$fname.' '.$lname.'","jobtitle": " ","email": "'.$email.'","source": "'.$jobSource.'","note": "'.$note.'},"file": {"filename": "'.$fname.' '.$lname.'Resume.docx","data": "'.$attach_resume.'"}}}}';       
	     
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
					 $result=json_decode($response);
									 $message=$result->message;  

									 if(($notification_status==0) && ($message=="user or API Key not found")) 
											{             
												   
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";
											}   

					}
  
			}   
			if($apicall=='newApplicant')        
			{      

   
  					 //Code for JObAdder Oauth JobPortal Plugin       

					$board_id=$credential_details->board_id;
					$url = $apiurl;    
					$postdata  = "grant_type=refresh_token";
					$postdata .= "&client_id=".$client_id; 
					$postdata .= "&client_secret=".$apikey;              
					$postdata .= "&refresh_token=".$refresh_token;    

					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, true);  
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);     
     
					$response = json_decode($result);    
					if(isset($response->access_token))   
					{
						echo $access_token = $response->access_token; 					       
						echo  $instance_url = $response->api;
						    
					}           
					else    
					{    
						if($notification_status==0)
						{          
							
							$credentials_update=Credential::find($id);
 							$credentials_update->notification_status=1;
							$credentials_update->save();  
							echo "update Status";         
						}
					}          
					    
  				
					$curl = curl_init();                     
					curl_setopt_array($curl, array(    
					CURLOPT_URL => $instance_url."jobboards/".$board_id."/ads/".$job_id."/applications",
					 CURLOPT_RETURNTRANSFER => true,        
					 CURLOPT_ENCODING => "",               
					 CURLOPT_MAXREDIRS => 10,   
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => "{  \"firstName\": \"".$fname."\",  \"lastName\": \"".$lname."\",  \"email\": \"".$email."\",  \"phone\": \"".$phone."\",  \"source\": \"".$jobSource."\"}",
					 CURLOPT_HTTPHEADER => array(                 
					   "Authorization: Bearer ".$access_token, 
					   "Content-Type: application/json"   
					 ),
					));
					$response = curl_exec($curl);              

					$err = curl_error($curl);
					curl_close($curl);
					if ($err) {
					 echo "cURL Error #:" . $err;
					} else {      
						echo $response;     
						$response1 = json_decode($response);
						$applicant_id = $response1->applicationId;
						$resumeLink = $response1->links->resume;                      
						echo 'appid'.$applicant_id;    
						        
					}    
					
					if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{   

						$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
						$filename=$fname.' '.$lname.'.'.$ext;
						$filecontent = file_get_contents($filedata);                
						Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
						$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 

						$url=$resumeLink;             

						$header = array('Authorization: Bearer '.$access_token,'Content-Type: multipart/form-data');               
						
						
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name.'.'.$ext);          
						$cfile->setMimeType('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);

						
						$fields = array('file' => $cfile);                  
						            
						$resource = curl_init();        
						curl_setopt($resource, CURLOPT_URL, $url);        
						curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
						curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);  
						curl_setopt($resource, CURLOPT_POST, 1);
						curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
						echo $result = curl_exec($resource);        
						 
						unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);    
					}
					else   
					{                    
						$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=".$fname."%20".$lname."&email=".$email."&phone=".$phone;   
						$ext="pdf";          
						$filecontent = file_get_contents($filedata);                     
						Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);             
						$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);    
						
						$url=$resumeLink;                

						$header = array('Authorization: Bearer '.$access_token,'Content-Type: multipart/form-data');               
						
						
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name.'.'.$ext);          
						$cfile->setMimeType('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);

						
						$fields = array('file' => $cfile);                  
						            
						$resource = curl_init();        
						curl_setopt($resource, CURLOPT_URL, $url);        
						curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
						curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);     
						curl_setopt($resource, CURLOPT_POST, 1);   
						curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
						echo $result = curl_exec($resource);        
						echo 'Resume parse done';    
						unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);       

					}    
   
			}
			if($apicall=='updateApplicant')        
			{         

   
  			   
					$board_id=$credential_details->board_id;
					$url = $apiurl;    
					$postdata  = "grant_type=refresh_token";
					$postdata .= "&client_id=".$client_id; 
					$postdata .= "&client_secret=".$apikey;              
					$postdata .= "&refresh_token=".$refresh_token;    

					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, true);  
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);     
     
					$response = json_decode($result);    
					if(isset($response->access_token))   
					{
						 $access_token = $response->access_token; 					       
					     $instance_url = $response->api;
						     
					}           
					else    
					{    
						if($notification_status==0)
						{           
							
							$credentials_update=Credential::find($id);
 							$credentials_update->notification_status=1;
							$credentials_update->save();  
							echo "update Status";         
						}
					}          
					


					if( (!empty($email)) && (!empty($phone)) )
					{
						echo 'search with both';

						$searchUrl=$instance_url."candidates?email=".$email."&phone=".$phone;
					}
					else if( (empty($email)) && (!empty($phone)) )
					{   
						echo 'search with phone';
						$searchUrl=$instance_url."candidates?phone=".$phone;
					}
					else if( (!empty($email)) && (empty($phone)) )
					{
						echo 'search with email';
						$searchUrl=$instance_url."candidates?email=".$email;
					}

					   

					$curl = curl_init();                         
					curl_setopt_array($curl, array(           
					 CURLOPT_URL => $searchUrl,      
					 CURLOPT_RETURNTRANSFER => true,        
					 CURLOPT_ENCODING => "",               
					 CURLOPT_MAXREDIRS => 10,     
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					 CURLOPT_CUSTOMREQUEST => "GET",
					 CURLOPT_HTTPHEADER => array(               
					   "Authorization: Bearer ".$access_token, 
					   "Content-Type: application/json"    
					 ),
					));
					$response = curl_exec($curl);              

					$err = curl_error($curl);
					
					curl_close($curl);
					if ($err) {
					 echo "cURL Error #:" . $err;
					} else {        
						 
						$response1 = json_decode($response);
					
					}    
					      
					if($response1->totalCount>0)
					{       
						echo 'update';       
						
						  
						for($i=0; $i<count($response1->items); $i++) 
						{
							echo $i;
							echo $candidateId=$response1->items[$i]->candidateId; 
							

							$curl = curl_init();                         
							curl_setopt_array($curl, array(            
							 CURLOPT_URL => $instance_url."candidates/".$candidateId."/applications",      
							 CURLOPT_RETURNTRANSFER => true,        
							 CURLOPT_ENCODING => "",               
							 CURLOPT_MAXREDIRS => 10,      
							 CURLOPT_TIMEOUT => 30,
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,    
							 CURLOPT_CUSTOMREQUEST => "GET",
							 CURLOPT_HTTPHEADER => array(               
							   "Authorization: Bearer ".$access_token, 
							   "Content-Type: application/json"    
							 ),
							));
							$response = curl_exec($curl);              

							$err = curl_error($curl);
						
							curl_close($curl);
							if ($err) 
							{
							 echo "cURL Error #:" . $err;
							} 
							else 
							{       
								$response1 = json_decode($response);
							} 

						  	

							echo $applicantID=$response1->items[0]->applicationId;       

							$attachResumeUrl1=$instance_url."jobboards/".$board_id."/ads/".$job_id."/applications/".$applicantID."/Resume";     

							   
							if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes") 
							{              

								$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
								$filename=$fname.' '.$lname.'.'.$ext;
								$filecontent = file_get_contents($filedata);                
								Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 

								$url=$attachResumeUrl1;             

								$header = array('Authorization: Bearer '.$access_token,'Content-Type: multipart/form-data');               
								
								
								$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name.'.'.$ext);          
								$cfile->setMimeType('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);

								
								$fields = array('file' => $cfile);                      
								            
								$resource = curl_init();        
								curl_setopt($resource, CURLOPT_URL, $url);        
								curl_setopt($resource, CURLOPT_HTTPHEADER, $header);       
								curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);  
								curl_setopt($resource, CURLOPT_POST, 1);
								curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
								echo $result = curl_exec($resource);        
								echo 'Resume Update Done';      
								unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);    
							}    
							
							    
						}
     
					
					}    

   
			}
			if($apicall=='createApplicant')   
			{     
   

   				// Code For Brightmove Job Portal 
       
				$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email."";      
				if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
				{       

					$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
					$content_type="application/".$ext;     
					$filename=$fname.' '.$lname.'.'.$ext;
					$filecontent = base64_encode(file_get_contents($filedata));
				}    
				else  
				{     
					$content_type="";
					$filename="";
					$filecontent="";        
				}

				        
					$postdata='{    
   "applicant":{   
      "coverletter":"",       
      "email":"'.$email.'",     
      "fullName":"'.$fname.' '.$lname.'",             
      "resume":{    
         "file":{    
            "contentType":"'.$content_type.'",
            "data":"'.$filecontent.'",
            "fileName":"'.$filename.'"
         },
         "hrXml":null,      
         "html":"",     
         "json":{
            "additionalInfo":null,
            "associations":null,
            "awards":null,
            "certifications":null,
            "firstName":"'.$fname.'",
            "headline":null,
            "lastName":"'.$lname.'",
            "links":null,  
            "militaryServices":null,                 
            "patents":null,        
            "phoneNumber":"'.$phone.'",
            "publicProfileURl":null,
            "publications":null,
            "skills":null,
            "summary":null
         },   
         "text":"'.$description.'"          
      }
   },
   "appliedOnMillis":0,      
   "job":{        
      "jobId":"'.$job_id.'"      
   }       
}';             
     
					           
					$curl = curl_init();             
					curl_setopt_array($curl, array(                     
					 CURLOPT_URL => $apiurl.$source,   
					 CURLOPT_RETURNTRANSFER => true,     
					 CURLOPT_ENCODING => "", 
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,  
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => $postdata,          
					 CURLOPT_HTTPHEADER => array(            
					   "Content-Type: application/json",        
					   "x-brightmove-company-apikey:".$client_id,
    				   "x-brightmove-user-apikey: ".$apikey 
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
			if($apicall=='pushCandidate')   
			{    
					 
 





				$postdata1='{
          "authorise": {  
            "company": "'.$client_id.'",
            "key": "'.$apikey.'" 
          },  
          "request": {
            "command": "CandidateDetails",
            "data": {
              "candidateName": "'.$fname.' '.$lname.'",
              "email": "'.$email.'",
              "phoneMobile":"'.$phone.'"
                     
            }  
          }
        }';          

        $curl = curl_init();
 
        curl_setopt_array($curl, array(
          CURLOPT_URL => $apiurl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",   
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postdata1,   
          CURLOPT_HTTPHEADER => array(     
            "Content-Type: text/plain", 
            "Cookie: SERVERID=app3"
          ),
        ));    

        $response = curl_exec($curl);
        
        curl_close($curl);
        $result=json_decode($response);
        $candidateCount=$result->count; 

        echo $candidateCount; 

        if($candidateCount==0) 
        { 
           

            $postdata='{   
          "authorise": { 
            "company": "'.$client_id.'",
            "key": "'.$apikey.'"
          }, 
          "request": {
            "command": "PushCandidate",
            "data": {
              "candidateName": "'.$fname.' '.$lname.'",
              "status": "Available",    
              "email": "'.$email.'",
              "phoneMobile":"'.$phone.'",
              "source":"'.$jobSource.'",
              "status":"New Record", 
              "gdprAccept":"Yes"
            }   
          } 
        }'; 

        $curl = curl_init();

        curl_setopt_array($curl, array(    
          CURLOPT_URL => $apiurl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postdata,   
          CURLOPT_HTTPHEADER => array(  
            "Content-Type: text/plain",
            "Cookie: SERVERID=app3"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo 'Candidate Create Done';
        echo $response;

          $postdata2='{
            "authorise": { 
              "company": "'.$client_id.'",
              "key": "'.$apikey.'"
            },
            "request": {
              "command": "CandidateDetails",
              "data": {
                "candidateName": "'.$fname.' '.$lname.'",
                "email": "'.$email.'"
              }
            }
          }';          

          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => $apiurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",   
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postdata2,   
            CURLOPT_HTTPHEADER => array(     
              "Content-Type: text/plain", 
              "Cookie: SERVERID=app3"
            ),
          ));    

          $response = curl_exec($curl);
          
          curl_close($curl);
          $result=json_decode($response);
          if(isset($result->records[0]->candidateID))
          { 
 
            echo 'NewCandidateID -'.$candidateID=$result->records[0]->candidateID;

          }
         
        }
        else
        {
 
         
          if(isset($result->records[0]->candidateID))
          {
              $candidateID=$result->records[0]->candidateID;  
          }
          
          echo "Not Create Candidate";    
          echo $candidateID;
        }
   




        if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
                    {            
                       

 
                      $ext = pathinfo($filedata, PATHINFO_EXTENSION);
                      $filename=$fname.' '.$lname.'.'.$ext;
                      $filecontent = file_get_contents($filedata);
                      Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);
                      $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);
          

 
                      $curl = curl_init(); 
                      curl_setopt_array($curl, array( 
                        CURLOPT_URL => $apiurl, 
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10, 
                        CURLOPT_TIMEOUT => 0,  
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,         
                        CURLOPT_CUSTOMREQUEST => "POST", 
                        CURLOPT_POSTFIELDS => array('authorise[key]' => ''.$apikey.'','authorise[company]' => ''.$client_id.'','request[command]' => 'PushCandidate','request[data][candidateID]' => ''.$candidateID.'', 'request[data][file][name]' => '/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'request[data][file][type]' => 'application/'.$ext,'attachedFile'=> new CurlFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,'/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext)),
                        CURLOPT_HTTPHEADER => array( 
                          "Cookie: SERVERID=app2" 
                        ), 
                      ));
                        
                      $response = curl_exec($curl); 
                        
                      curl_close($curl);  
                      echo 'Resume Upload done'; 
                      echo $response;        


 
                    }    
				   


				if(!empty($job_id))
				{  

						 $postdataCheck='{ 
          "authorise": {  
            "company": "'.$client_id.'",
            "key": "'.$apikey.'" 
          },  
          "request": { 
            "command": "ListSelections",
            "data": {
              "candidateID": "'.$candidateID.'",
              "vacancyID": "'.$job_id.'"
            }   
          } 
        }';  
           

        $curl = curl_init();
 
        curl_setopt_array($curl, array(
          CURLOPT_URL => $apiurl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",   
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postdataCheck,   
          CURLOPT_HTTPHEADER => array(     
            "Content-Type: text/plain", 
            "Cookie: SERVERID=app3"
          ),
        ));    

        $response = curl_exec($curl);
        
        curl_close($curl); 
 
        
        $result=json_decode($response);
        echo $jobcount=$result->count;

        if($jobcount==0)
        {  
          echo 'Applied for job';

           $jobdata='{
              "authorise": { 
                "company": "'.$client_id.'",
                "key": "'.$apikey.'"
                },
              "request": {
                "command": "PushSelection",
                "data": {
                  "candidateID": '.$candidateID.',
                  "vacancyID": '.$job_id.'
                }
              }
            }'; 

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $apiurl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $jobdata,      
              CURLOPT_HTTPHEADER => array(     
                "Content-Type: text/plain",
                "Cookie: SERVERID=app3"
              ),
            ));    

            $response = curl_exec($curl); 
            echo 'Job Apply Done';
            echo 'VacancyID -'.$job_id;    
            echo $response;

        }  
        else
        {
        	echo 'Already Applied for the JOb';
        }
				 
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

					if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
										{           
											
											
											$file = base64_encode(file_get_contents($filedata));
										} 
										else
										{ 
											     
											$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=".$fname."%20".$lname."&email=".$email."&phone=".$phone;       
											 
											$file = base64_encode(file_get_contents($filedata));        
										
										}
					$applicants='{"username": "'.$username.'","password": "'.$password.'","Candidate first name": "'.$fname.'","Candidate last name": "'.$lname.'","Candidate email": "'.$email.'","Candidate phone number": "'.$phone.'","Job ID": "'.$job_id.'","Resume": "'.$file.'"}';     
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
					 "username: ".$username,     
					 "password: ".$password,         
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
					  $result=json_decode($response);
					  $message=$result->message[0];                   
					         
									 if(($notification_status==0) && ($message=="Invalid login")) 
											{                   
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";         
											}

					}  
  
			} 
			if($apicall=='createCustomGrantContact')   
			{

					  


					$url = $apiurl;

					$curl = curl_init();   

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",    
  CURLOPT_POSTFIELDS => array('username' => $username,'password' => $password,'grant_type' => 'password','client_id' => $client_id,'client_secret' =>$apikey),
  CURLOPT_HTTPHEADER => array(     
    "Cookie: BrowserId=uepbnrwvEeq_USfRDhPhVg"
  ),
));

$result = curl_exec($curl);
      
curl_close($curl);
$response = json_decode($result);
$access_token = $response->access_token;  
$instance_url = $response->instance_url;  


 
     
					

					        
					       
					// CODE FOR CONVERT PDF, DOC TO HTML

					$name_bh="Bullhorn";      
					$clientname_bh="LewisJames";       
					$description=''; //initializing resume as blank will be replaced if resume is parsed

					if(($name_bh=="Bullhorn") && ($clientname_bh=="LewisJames") && ($resume_status=='Yes'))
					{ 
						$credential_details = Credential::where('name',$name_bh)->where('client_name',$clientname_bh)->first();					
				 		$username_bullhorn=$credential_details->username; 
					    $password_bullhorn=$credential_details->password;
						$apiurl_bullhorn=$credential_details->url;      
						$id_bullhorn=$credential_details->id; 
						$client_id_bullhorn=$credential_details->client_id;  
						$apikey_bullhorn=$credential_details->client_secret;           
						$refresh_token_bullhorn=$credential_details->refresh_token;   
						$access_token_bullhorn=$credential_details->access_token;      
						$source_bullhorn='';    
					
						
						$url = $apiurl_bullhorn;    
						$postdata  = "grant_type=refresh_token";
						$postdata .= "&refresh_token=".$refresh_token_bullhorn;
						$postdata .= "&client_id=".$client_id_bullhorn;   
						$postdata .= "&client_secret=".$apikey_bullhorn;
						$ch = curl_init($url);
									curl_setopt($ch, CURLOPT_POST, true);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									$result = curl_exec($ch);   

									$response = json_decode($result);
									 

									if(isset($response->access_token))   
									{
										$access_token_bullhorn = $response->access_token;        
 										$refresh_token_bullhorn =$response->refresh_token;       
									}   
									else    
									{  
										if($notification_status==0)
											{             
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";          
											}  
									}

									     
 									      
 									$credentials_update=Credential::find($id_bullhorn);   
 									$credentials_update->access_token  = $access_token_bullhorn;
									$credentials_update->refresh_token = $refresh_token_bullhorn;
									$credentials_update->save();  
									
									$url1="https://rest.bullhornstaffing.com/rest-services/login";
									$postdata1  = "version=*";
									$postdata1 .= "&access_token=".$access_token_bullhorn;
									$ch1 = curl_init($url1);     
									curl_setopt($ch1, CURLOPT_POST, true);
									curl_setopt($ch1, CURLOPT_POSTFIELDS, $postdata1);    
									curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
									$result1 = curl_exec($ch1);
									$response1 = json_decode($result1);
									$resturl_bullhorn = $response1->restUrl; 
									$bhtoken_bullhorn = $response1->BhRestToken;     
									                
									if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")   
									   {     
									   	  $ext = pathinfo($filedata, PATHINFO_EXTENSION);
										  $filename=$fname.' '.$lname.'.'.$ext;
										  $filecontent = file_get_contents($filedata);
								 		  Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);
										  $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);
										 
						$url=$resturl_bullhorn."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken_bullhorn,'Content-Type: multipart/form-data');
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
								
											// Assign POST data
											$fields = array('file' => $cfile);
									
											$resource = curl_init();
											curl_setopt($resource, CURLOPT_URL, $url);
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);
											  
											$err = curl_error($resource);
											curl_close($resource);
											if ($err) {
									 echo "cURL Error #:" . $err;
									} else {
											$result_parse=json_decode($result);
											$parsedescription=$result_parse->candidate->description;
											$description=$parsedescription;
											

											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);       
										}
									   }
									else
									  {   
									  		

									  		$description="".$fname." ".$lname."Phone: ".$phone."Email: ".$email."";      
									  }


					}     
  
				
					$html_content=$description;    
							
					 


if(isset($_POST['source']))
{

	$eighteleven_jobSource = $_POST['source'];
}
else
{
	$eighteleven_jobSource = ""; 
}
if($clientname=='bruce811' || $clientname=='bruce811Dev')
{      
	$json_array=array(      
	'FirstName'=>$fname,       
	'LastName'=>$lname,    
	'Email'=>$email,         
	'Phone'=>$phone,
	'RecordTypeId'=>'01237000000EvwYAAS',
	'AccountId'=>'0013700000P9wqGAAR' 
	);



}
else    
{     
	
	
}

					$curl = curl_init();  
					curl_setopt_array($curl, array(
					 CURLOPT_URL => $instance_url."/services/data/v42.0/query/?q=SELECT+Email,Id,Phone+FROM+Contact+WHERE+Email='".$email."'+OR+Phone='".$phone."'",
					 CURLOPT_RETURNTRANSFER => true, 
					 CURLOPT_ENCODING => "",       
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,            
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					 CURLOPT_CUSTOMREQUEST => "GET",
					 CURLOPT_HTTPHEADER => array(             
					   "Authorization: Bearer ".$access_token,                  
					   "Content-Type: application/json"         
					 ),     
					));     
					$response = curl_exec($curl);
					$response1 = json_decode($response);

					echo "Email and Phone matching done.";
					print_r($response1);
					 
					if(isset($response1->records[0]->Id))
					{
						
						$contact_id =$response1->records[0]->Id;
						echo "Contact exists.";	
					}
					else
					{
						$postContact=json_encode($json_array);                                                  
						$curl = curl_init();  
						curl_setopt_array($curl, array(                 
						 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Contact",    
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",       
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "POST",  
						 CURLOPT_POSTFIELDS => $postContact,                
						 CURLOPT_HTTPHEADER => array(             
						   "Authorization: Bearer ".$access_token,                  
						   "Content-Type: application/json"         
						 ),     
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);         
						print_r($err);    
						curl_close($curl); 
						if ($err) {
						 echo "cURL Error #:" . $err;
						} else {
						  
						
						echo $response;         
						$response1 = json_decode($response);
						
						if(isset($response1->id))    
						{ 
							$contact_id =$response1->id;     
						} 
						else
						{
							$contact_id ='';  
						}      
						
						}
						echo "New contact created.";
					}					
					echo "CONTACT_ID:".$contact_id; 
 					
 				   if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{       
								$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
								$filename=$fname.' '.$lname.'.'.$ext;
								$filecontent = file_get_contents($filedata);                
								 Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   

								$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
								$file = chunk_split(base64_encode($path));            
   								$file = mysql_escape_mimic1($file);    
   								

$parseResumeCand='{"Title": "'.$filename.'","ContentLocation": "S","FirstPublishLocationId": "'.$contact_id.'","PathOnClient": "'.$filename.'","VersionData": "'.$file.'"}'; 
     

							 $curl = curl_init();      
							 curl_setopt_array($curl, array(       
							 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ContentVersion/",   
							 CURLOPT_RETURNTRANSFER => true,            
							 CURLOPT_ENCODING => "",                        
							 CURLOPT_MAXREDIRS => 10,       
							 CURLOPT_TIMEOUT => 30,            
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
							 CURLOPT_CUSTOMREQUEST => "POST",       
							 CURLOPT_POSTFIELDS => $parseResumeCand,        
							 CURLOPT_HTTPHEADER => array(                  
							   "Authorization: Bearer ".$access_token,             
							   "Content-Type: application/json"
							 ),     
							));
							$response = curl_exec($curl);            
							$err = curl_error($curl);    
							print_r($err);         
							curl_close($curl);   
							if ($err) {     
							 echo "cURL Error #:" . $err;  
							} else {    
							 echo $response;    
							 echo "resume upload"; 
							 echo "resume upload backend";      
							 $response1 = json_decode($response);
							 unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);   
							}  
					}

					if( ($clientname=='bruce811' || $clientname=='bruce811Dev')  && (!empty($job_id)) )   
					{                
						echo "JOB_ID: ".$job_id;
						echo "CONTACT_ID: ".$contact_id;				
						$curl = curl_init();                    
						curl_setopt_array($curl, array(           
						 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ts2__Application__c",    
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",       
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "POST",                 
						CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"".$contact_id."\", \"ts2__Job__c\": \"".$job_id."\", \"ts2__Application_Source__c\": \"".$eighteleven_jobSource."\"}", 
						 CURLOPT_HTTPHEADER => array(                            
						   "Authorization: Bearer ".$access_token,           
						   "Content-Type: application/json" 
						 ),        
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);    
						
						curl_close($curl);        
						if ($err) {
						 echo "cURL Error #:" . $err;
						} else {       
						 echo $response;   
						  
						 $response1 = json_decode($response);   
						 $applicant_id = $response1->id;     
						echo 'Applicant ID:'.$applicant_id;       
						} 
   

						$curl = curl_init();                    
						curl_setopt_array($curl, array(              
						 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ts2__Job__c/$job_id",     
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",        
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "PATCH",                  
						CURLOPT_POSTFIELDS => "{\"StaffingFuture_ID__c\": \"".$staffingfutureid."\"}", 
						 CURLOPT_HTTPHEADER => array(                               
						   "Authorization: Bearer ".$access_token,           
						   "Content-Type: application/json" 
						 ),         
						)); 
						$response = curl_exec($curl);          
						$err = curl_error($curl);    
						
						curl_close($curl);     
						if ($err) {
						 echo "cURL Error #:" . $err;
						} else {    
						echo "Add StaffingFuture_ID__c column";  
						 echo $response;   
						  
						      
						}       

       				
   

					
				} 
				else
				{

				}   





			}
			if($apicall=='createContact')    
			{


					 //Code for JobScience Salesforce Oauth Plugin
 					$url = $apiurl;

					$postdata  = "grant_type=refresh_token";    
					$postdata .= "&client_id=".$client_id; 
					$postdata .= "&client_secret=".$apikey;        
					$postdata .= "&refresh_token=".$refresh_token;    

					$ch = curl_init($url);   
					curl_setopt($ch, CURLOPT_POST, true);  
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);     
    
					$response = json_decode($result);        
					if(isset($response->access_token))
					{
						$access_token = $response->access_token; 
						$instance_url = $response->instance_url;   
					}   
					else    
					{
										if($notification_status==0)
											{             
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";          
											}  
					}

					        
					       
					// CODE FOR CONVERT PDF, DOC TO HTML

					$name_bh="Bullhorn";
					$clientname_bh="LewisJames";       
					$description=''; //initializing resume as blank will be replaced if resume is parsed

					if(($name_bh=="Bullhorn") && ($clientname_bh=="LewisJames") && ($resume_status=='Yes'))
					{ 
						$credential_details = Credential::where('name',$name_bh)->where('client_name',$clientname_bh)->first();					
				 		$username_bullhorn=$credential_details->username; 
					    $password_bullhorn=$credential_details->password;
						$apiurl_bullhorn=$credential_details->url;      
						$id_bullhorn=$credential_details->id; 
						$client_id_bullhorn=$credential_details->client_id;  
						$apikey_bullhorn=$credential_details->client_secret;           
						$refresh_token_bullhorn=$credential_details->refresh_token;   
						$access_token_bullhorn=$credential_details->access_token;      
						$source_bullhorn='';    
						    
					
						$url = $apiurl_bullhorn;    
						$postdata  = "grant_type=refresh_token";
						$postdata .= "&refresh_token=".$refresh_token_bullhorn;
						$postdata .= "&client_id=".$client_id_bullhorn;   
						$postdata .= "&client_secret=".$apikey_bullhorn;
						$ch = curl_init($url);
									curl_setopt($ch, CURLOPT_POST, true);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									$result = curl_exec($ch);   

									$response = json_decode($result);
									 

									if(isset($response->access_token))   
									{
										$access_token_bullhorn = $response->access_token;        
 										$refresh_token_bullhorn =$response->refresh_token;       
									}   
									else    
									{  
										if($notification_status==0)
											{             
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";          
											}  
									}

									     
 									      
 									$credentials_update=Credential::find($id_bullhorn);   
 									$credentials_update->access_token  = $access_token_bullhorn;
									$credentials_update->refresh_token = $refresh_token_bullhorn;
									$credentials_update->save();  
									
									$url1="https://rest.bullhornstaffing.com/rest-services/login";
									$postdata1  = "version=*";
									$postdata1 .= "&access_token=".$access_token_bullhorn;
									$ch1 = curl_init($url1);     
									curl_setopt($ch1, CURLOPT_POST, true);
									curl_setopt($ch1, CURLOPT_POSTFIELDS, $postdata1);    
									curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
									$result1 = curl_exec($ch1);
									$response1 = json_decode($result1);
									$resturl_bullhorn = $response1->restUrl; 
									$bhtoken_bullhorn = $response1->BhRestToken;     
									                
									if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")   
									   {     
									   	  $ext = pathinfo($filedata, PATHINFO_EXTENSION);
										  $filename=$fname.' '.$lname.'.'.$ext;
										  $filecontent = file_get_contents($filedata);
								 		  Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);
										  $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);
										 
						$url=$resturl_bullhorn."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken_bullhorn,'Content-Type: multipart/form-data');
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
								
											// Assign POST data
											$fields = array('file' => $cfile);
									
											$resource = curl_init();
											curl_setopt($resource, CURLOPT_URL, $url);
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);
										
											$err = curl_error($resource);
											curl_close($resource);
											if ($err) {
									 echo "cURL Error #:" . $err;
									} else {
											$result_parse=json_decode($result);
											$parsedescription=$result_parse->candidate->description;
											$description=$parsedescription;
										

											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);       
										}
									   }
									else
									  {   
									  		

									  		$description="".$fname." ".$lname."Phone: ".$phone."Email: ".$email."";      
									  }


					}     
  
			
					$html_content=$description;    
									
					 
       
if($clientname=='Synergishr')
{    
	$json_array=array(
	'FirstName'=>$fname,       
	'LastName'=>$lname,    
	'Email'=>$email,        
	'Phone'=>$phone,
	'LeadSource'=>$jobSource,
	'ts2__Text_Resume__c'=>$html_content        
	); 
}
else    
{  
	

	$json_array=array(
	'FirstName'=>$fname,
	'LastName'=>$lname,        
	'Email'=>$email,                
	'Phone'=>$phone,
	'LeadSource'=>$jobSource            
	);
}
    

$postContact=json_encode($json_array);                           
					
					// changes made by gaurav on 02-10-2020
					$curl = curl_init();  
					curl_setopt_array($curl, array(
					 CURLOPT_URL => $instance_url."/services/data/v42.0/query/?q=SELECT+Email,Id,Phone+FROM+Contact+WHERE+Email='".$email."'+OR+Phone='".$phone."'",
					 CURLOPT_RETURNTRANSFER => true, 
					 CURLOPT_ENCODING => "",       
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,            
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					 CURLOPT_CUSTOMREQUEST => "GET",
					 CURLOPT_HTTPHEADER => array(             
					   "Authorization: Bearer ".$access_token,                  
					   "Content-Type: application/json"         
					 ),     
					));     
					$response = curl_exec($curl);
					$response1 = json_decode($response);

					echo "Email and Phone matching done.";
					
					if(isset($response1->records[0]->Id))
					{
						
						$contact_id =$response1->records[0]->Id;
						echo "Contact exists.";	
					}


					if(empty($contact_id)) {					                       
						$curl = curl_init();  
						curl_setopt_array($curl, array(                 
						 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Contact",    
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",       
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "POST",  
						 CURLOPT_POSTFIELDS => $postContact,                
						 CURLOPT_HTTPHEADER => array(             
								"Authorization: Bearer ".$access_token,                  
								"Content-Type: application/json"         
							),     
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);         
						print_r($err);    
						curl_close($curl); 
						if ($err) {
							echo "cURL Error #:" . $err;
						} else {
							echo 'firstclientrespnse';   
							echo $response;         
							$response1 = json_decode($response);					

							if(isset($response1->id))   
							{
								$contact_id =$response1->id;     
							}
							else
							{
								$contact_id =''; 
							}      
							
						}
					}
					          
					echo "CONTACT_ID: ".$contact_id;  
					

					if($clientname=='Synergishr') 
					{        
						$curl = curl_init();               
						curl_setopt_array($curl, array(           
						 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ts2__Application__c",    
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",       
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "POST",  
						CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"".$contact_id."\",  \"ts2__Job__c\": \"".$job_id."\"}", 
						 CURLOPT_HTTPHEADER => array(               
						   "Authorization: Bearer ".$access_token,           
						   "Content-Type: application/json"
						 ),     
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);    
						
						curl_close($curl);     
						if ($err) {
						 echo "cURL Error #:" . $err;
						} else {      
						 echo $response;   
						 
						 $response1 = json_decode($response);   
						 $applicant_id = $response1->id;     
						echo 'Applicant ID:'.$applicant_id;       
						}       

       				
   

					if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{      
								$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
								$filename=$fname.' '.$lname.'.'.$ext;
								$filecontent = file_get_contents($filedata);                
								 Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   

								$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
								$file = chunk_split(base64_encode($path));            
   								$file = mysql_escape_mimic1($file);    
   								
     
$parseResumeCand='{"ContactId": "'.$contact_id.'","Name": "'.$filename.'","ContentType": "application/'.$ext.'","Body": "'.$file.'"}'; 
  
     
								$curl = curl_init();      
							 curl_setopt_array($curl, array(                      
							  CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
							 CURLOPT_RETURNTRANSFER => true,            
							 CURLOPT_ENCODING => "",                        
							 CURLOPT_MAXREDIRS => 10,       
							 CURLOPT_TIMEOUT => 30,            
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
							 CURLOPT_CUSTOMREQUEST => "POST",       
							 CURLOPT_POSTFIELDS => $parseResumeCand,        
							 CURLOPT_HTTPHEADER => array(                 
							   "Authorization: Bearer ".$access_token,             
							   "Content-Type: application/json"
							 ),     
							));
							$response = curl_exec($curl);            
							$err = curl_error($curl);    
							print_r($err);         
							curl_close($curl);   
							if ($err) {     
							 echo "cURL Error #:" . $err;  
							} else {    
							 echo $response;    
							 echo "resume upload"; 
							 echo "resume upload backend";      
							 $response1 = json_decode($response);
							 unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);   
							}  
					}
				} 
				else
				{

					if(!empty($job_id))
					{    
						$curl = curl_init();               
							curl_setopt_array($curl, array(             
							 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/TR1__Application__c",    
							 CURLOPT_RETURNTRANSFER => true, 
							 CURLOPT_ENCODING => "",          
							 CURLOPT_MAXREDIRS => 10,     
							 CURLOPT_TIMEOUT => 30,            
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
							 CURLOPT_CUSTOMREQUEST => "POST",  
							CURLOPT_POSTFIELDS => "{ \"TR1__Applicant__c\": \"".$contact_id."\",  \"TR1__Job__c\": \"".$job_id."\",  \"TR1__Source__c\": \"Job Board\"}",   
							 CURLOPT_HTTPHEADER => array(                 
							   "Authorization: Bearer ".$access_token,           
							   "Content-Type: application/json" 
							 ),  
							));
							$response = curl_exec($curl);          
							$err = curl_error($curl);    
							print_r($err);    
							curl_close($curl);     
							if ($err) {
							 echo "cURL Error #:" . $err;
							} else {      
							 echo $response;   
							  
							 $response1 = json_decode($response);   
							 $applicant_id = $response1->id;     
							echo 'Applicant ID:'.$applicant_id;       
							} 
					}

						if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{      
								$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
								$filename=$fname.' '.$lname.'.'.$ext;
								$filecontent = file_get_contents($filedata);                
								 Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   

								$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
								$file = chunk_split(base64_encode($path));            
   								$file = mysql_escape_mimic1($file);    
   								
$parseResumeCand='{"ParentId": "'.$contact_id.'","Name": "'.$filename.'","ContentType": "application/'.$ext.'","Body": "'.$file.'"}'; 
  
     
								$curl = curl_init();      
							 curl_setopt_array($curl, array(                          
							 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Attachment/",      
							 CURLOPT_RETURNTRANSFER => true,            
							 CURLOPT_ENCODING => "",                        
							 CURLOPT_MAXREDIRS => 10,       
							 CURLOPT_TIMEOUT => 30,            
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
							 CURLOPT_CUSTOMREQUEST => "POST",       
							 CURLOPT_POSTFIELDS => $parseResumeCand,        
							 CURLOPT_HTTPHEADER => array(                 
							   "Authorization: Bearer ".$access_token,             
							   "Content-Type: application/json"
							 ),     
							));
							$response = curl_exec($curl);            
							$err = curl_error($curl);    
							print_r($err);         
							curl_close($curl);   
							if ($err) {     
							 echo "cURL Error #:" . $err;  
							} else {    
							 echo $response;    
							 echo "resume upload"; 
							 echo "resume upload backend";      
							 $response1 = json_decode($response);
							 unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);    
							}  
					}

				}   

			}	
			if($apicall=='createLead')   
			{   
				if($clientname=='bridgeview')
				{   
					/*Code for only 'bridgeview' CLient */

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
									if(isset($response->access_token))   
									{
										$access_token = $response->access_token; 
 										$refresh_token =$response->refresh_token;     
									}     
									else    
									{  
										if($notification_status==0)
											{             
												 
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";         
											}       
									}
									  

 									$credentials_update=Credential::find($id);
 									$credentials_update->access_token  = $access_token;
									$credentials_update->refresh_token = $refresh_token; 
									$credentials_update->save();            
  
									
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
    
									$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email." "; 

									$url=$resturl."entity/Lead";         
	 								    $postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "New Lead","comments": "2020 salary guide download","leadSource": "'.$jobSource.'","phone": "'.$phone.'","mobile": "'.$phone.'","description":"'.mysql_escape_mimic($description).'"}';   
    
	 								     
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
										   "BhRestToken: ".$bhtoken,           
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
										echo "successfully";     
				}   

			}
			if($apicall=='createCandidate')   
			{   

				//Code for Bullhorn Job Portal Oauth 

									$url = $apiurl;   
								if($notification_status==1)  
								{              

									define('CLIENT_ID', $client_id);
									define('CLIENT_SECRET', $apikey);
									define('USER', $username);                   
									define('PASS', $password);          
									   
							     
									    
									  
										$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.CLIENT_ID.'&response_type=code';
									    $data = "action=Login&username=".USER."&password=".PASS."";  

									   	$options = array(
												CURLOPT_POST           => true,
												CURLOPT_POSTFIELDS     => $data,
												CURLOPT_RETURNTRANSFER => true,
												CURLOPT_HEADER         => true,
												CURLOPT_FOLLOWLOCATION => true,
												CURLOPT_AUTOREFERER    => true,
												CURLOPT_CONNECTTIMEOUT => 120,
												CURLOPT_TIMEOUT        => 120,
											);
									    $ch  = curl_init( $url );  
									    curl_setopt_array( $ch, $options );
									    $content = curl_exec( $ch );
									    curl_close( $ch );

									      
									    if(preg_match('#Location: (.*)#', $content, $r)) {
										$l = trim($r[1]);
										$temp = preg_split("/code=/", $l);
										
											if(isset($temp[1]))   
											{ 
												$authcode = $temp[1];
												echo "IF";    
												$curl = curl_init();
												curl_setopt_array($curl, array(
												  CURLOPT_URL => "https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code=".$authcode."&client_secret=".CLIENT_SECRET,
												  CURLOPT_RETURNTRANSFER => true,
												  CURLOPT_ENCODING => "",
												  CURLOPT_MAXREDIRS => 10,
												  CURLOPT_TIMEOUT => 30,
												  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
												  CURLOPT_CUSTOMREQUEST => "POST",
												  CURLOPT_HTTPHEADER => array(
												    "cache-control: no-cache",
												    "content-type: application/x-www-form-urlencoded"
												  ),
												));

												$response = curl_exec($curl);
												$err = curl_error($curl);   

												curl_close($curl);
												   
												if ($err) 
												{
												  echo "cURL Error #:" . $err;
												} 
												else 
												{
													   $response = json_decode($response);
														if(isset($response->access_token))       
														{   
															$access_token = $response->access_token; 
					 										$refresh_token =$response->refresh_token;   

					 										$credentials_update=Credential::find($id);
					 										$credentials_update->notification_status=0;
					 										$credentials_update->access_token=$access_token;
										 					$credentials_update->refresh_token=$refresh_token;
															$credentials_update->save();       

															echo "update status and access_token and refresh_token";      
														}       
												}

											}
									    }
									    else
									    {
									    	echo "invalid Client";   

									    	 
											echo 'send mail';  
									    }
									     

								  }   
									   
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
									if(isset($response->access_token))   
									{
										$access_token = $response->access_token; 
 										$refresh_token =$response->refresh_token;     
									}     
									else    
									{  
										if($notification_status==0)
											{             
												
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";      
											} 

									}    

 									$credentials_update=Credential::find($id);
 									$credentials_update->access_token  = $access_token;
									$credentials_update->refresh_token = $refresh_token;     
									$credentials_update->save();            
  
									
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
      

									if($resume_status=="Yes"  || $resume_status=="YES" || $resume_status=="yes")       
									   {      

									   	if(!empty($filedata))
     												{

									   	  $ext = pathinfo($filedata, PATHINFO_EXTENSION);   
										  $filename=$fname.' '.$lname.'.'.$ext;
										  $filecontent = file_get_contents($filedata);                
								 		  Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
										  $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 
										
						$url=$resturl."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken,'Content-Type: multipart/form-data');
    
						
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
									
											// Assign POST data          
											$fields = array('file' => $cfile);           
											         
									
											$resource = curl_init();     
											curl_setopt($resource, CURLOPT_URL, $url);        
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);  
											
											$err = curl_error($resource);   
											curl_close($resource);    
											if ($err) {          
									 echo "cURL Error #:" . $err;     
									} else {
											$result_parse=json_decode($result);      
											$parsedescription=$result_parse->candidate->description;   
											$description=$parsedescription;  
											$description = mysql_escape_mimic($description);  
										
											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);  
										} 
									}
									   } 
									else     
									  {         
									  		$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email." ";  
									  }      
   
									

  								   //Code for check the candidate status in bullhorn 

									$url=$resturl."find?query=$email&countPerEntity=1";    
									$header = array('bhresttoken: '.$bhtoken);                     
									$resource = curl_init();                 
									curl_setopt($resource, CURLOPT_URL, $url);           
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									   
										        
								 
									if(empty($result->data)) 
									{    
										$email_status='';      
									}
									else
									{
										$email_status=$result->data[0]->entityId;   
									} 


									            
									if($clientname=='cybersearchsf')
									{
										$candidateStatus="Unreviewed";	
									}
									else if($clientname=='professionalalternatives')
									{
										$candidateStatus="New Candidate";	
									}
									else if($clientname=='fisergroup1')
									{
										$candidateStatus="Available";	
									}									
									else       
									{   
										$candidateStatus="New Lead";
									}
									           
									$url=$resturl."find?query=$phone&countPerEntity=1";  
									echo $url;									
									$header = array('bhresttoken: '.$bhtoken); 
									$resource = curl_init();             
									curl_setopt($resource, CURLOPT_URL, $url);               
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);       
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);         
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);
									     

									if(empty($result->data)) 
									{
										$phone_status='';         
									}  
									else
									{
										$phone_status=$result->data[0]->entityId;   
									}
									echo 'email_status:'.$email_status;
									echo 'phone status:'.$phone_status;    


									if( (!empty($email_status)) && (!empty($phone_status)) )
									{
										echo 'ifloop';
										$candidateId=$email_status;  
									}
									else if( (!empty($email_status)) && ($clientname=="AtlasStaffing" || $clientname=="fisergroup1" || $clientname=="ETSStaffingFuture") )
									{           
										echo 'ifloop for AtlasStaffing and fisergroup1  and ETSStaffingFuture update';
										$candidateId=$email_status;  
									}
									else         
									{       
										echo 'elseloop executeapi';  
										 
  										          
										$url=$resturl."entity/Candidate";    

										if(isset($_POST['im_a']))
											{ 
												$im_a=$_POST['im_a'];
											}
											else
											{
												$im_a="";       
											}  

										if(($clientname=='professionalalternatives') || ($im_a=='Job Seeker'))        
										{
											 
											if(isset($_POST['area_of_interest']))    
											{ 
   

												
												$area_of_interest = $_POST['area_of_interest']; 
												
											}
											else    
											{ $area_of_interest ="2000015"; }       

											if(isset($_POST['street_address']))
											{ 
												$street_address=$_POST['street_address'];
											}
											else
											{
												$street_address="";       
											} 
											if(isset($_POST['address_line2']))
											{ 
												$address_line2=$_POST['address_line2'];
											}
											else
											{
												$address_line2="";       
											} 
											if(isset($_POST['city']))
											{ 
												$city=$_POST['city'];
											}
											else
											{
												$city="";       
											}  
											if(isset($_POST['state']))
											{ 
												$state=$_POST['state'];
											}
											else
											{
												$state="";       
											} 
											if(isset($_POST['postal_code']))
											{ 
												$postal_code=$_POST['postal_code'];
											}
											else
											{
												$postal_code="";       
											} 
											if(isset($_POST['country']))
											{ 
												$country=$_POST['country'];
											}
											else
											{
												$country="";       
											}  
											  
											  
											if(isset($_POST['message']))
											{  
												$message=$_POST['message'];
											}
											else
											{
												$message="";         
											}
											           
 											
											   
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","address": {
            "address1": "'.$street_address.'",
            "address2": "'.$address_line2.'",
            "city": "'.$city.'", 
            "state": "'.$state.'",         
            "zip": "'.$postal_code.'"                          
        },"category" : {        
      "id" : "'.$area_of_interest.'"            
    },"source": "'.$jobSource.'","phone": "'.$phone.'","description":"'.$description.'","comments":"'.$message.'"}';          

        								         
        								         
										}
										else if($clientname=='AtlasStaffing')        
										{           

											    
  												 
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","source": "'.$jobSource.'","phone": "'.$phone.'"}';  


										}
										else if($clientname=='ETSStaffingFuture')        
										{           

											    
  												 
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","source": "'.$jobSource.'","phone": "'.$phone.'"}';  


										}
										else if($clientname=='fisergroup1')        
										{              
 
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","source": "'.$jobSource.'","phone": "'.$phone.'"}';
      
   
										}
																		
										else 
										{       
											$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","source": "'.$jobSource.'","phone": "'.$phone.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",  
            "city": "'.$city.'", 
            "state": "'.$state.'",            
            "zip": "'.$zip.'"                          
        },"description":"'.$description.'"}';
										}    
 
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
										   "BhRestToken: ".$bhtoken,           
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
										$candidateId =$responseTest->changedEntityId; 
									}    


				if((($clientname=='AtlasStaffing') || ($clientname=='ETSStaffingFuture') || ($clientname=='fisergroup1')) && (!empty($candidateId)))
									{           
     						
     									

     									echo $candidateId; 
 
     									if($steps=='Step2')
     									{   
     										echo 'Step2'; 

 


     												if(!empty($filedata))
     												{   

			     									if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
													{                  
														$ext = pathinfo($filedata, PATHINFO_EXTENSION);
														$filename=$fname.' '.$lname.'.'.$ext;
														$filecontent = file_get_contents($filedata);                
											 			Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
											   
														$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
											  
														$file = chunk_split(base64_encode($path)); 

														$changedEntityId =$candidateId;       


														$url=$resturl."entityFiles/Candidate/".$changedEntityId."";   
														$header = array('bhresttoken: '.$bhtoken);                     
														$resource = curl_init();                 
														curl_setopt($resource, CURLOPT_URL, $url);           
														curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
														curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
														$result = json_decode(curl_exec($resource));  
														curl_close($resource);

														foreach ($result->EntityFiles as $rows)       
														{
															$rows->filename = $rows->name;                        
														}          
			 									  
				 									if(in_array($filename, array_column($result->EntityFiles, 'filename'))) 
				 									{    
													    	echo 'notadded file';
													}  
													else   
													{      
				  											echo 'add file';
													  
															$url1=$resturl."file/Candidate/".$changedEntityId."";        
															$postResume1='{"externalID": "portfolio","fileContent": "'.$file.'","fileType": "SAMPLE","type": "CV","name": "'.$filename.'"}';
							      
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
																 	"BhRestToken: ".$bhtoken,        
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

													
						$url=$resturl."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken,'Content-Type: multipart/form-data');
    
						
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
									    
											$fields = array('file' => $cfile);           
											         
										
											$resource = curl_init();     
											curl_setopt($resource, CURLOPT_URL, $url);        
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);  
											   
											$err = curl_error($resource);   
											curl_close($resource);    
											if ($err) 
											{           
									 			echo "cURL Error #:" . $err;     
											} 
											else 
											{
												$result_parse=json_decode($result);      
												$parsedescription=$result_parse->candidate->description;   
												$description=$parsedescription;  
												$description = mysql_escape_mimic($description);  
												 
												
											} 

												unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);  
												}	    

												}
												else
												{
													$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email." ";   
												} 	
												$postUpdate='{"id": "'.$candidateId.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",   
            "city": "'.$city.'",        
            "state": "'.$state.'",                  
            "zip": "'.$zip.'"                           
        },"description":"'.$description.'"}';  

     									}
     									else if($steps=='Step3')
     									{   
     										echo 'Step3';   

     										
											$customTextBlock2="Shift Availability - ".$shift;
											$customTextBlock3="Day Availability - ".$day; 


     										$postUpdate='{"id": "'.$candidateId.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",   
            "city": "'.$city.'",         
            "state": "'.$state.'",                
            "zip": "'.$zip.'"                           
        }}';   




        					if($clientname=='AtlasStaffing')
        					{
        						  $postUpdate='{"id": "'.$candidateId.'","customTextBlock2":"'.$customTextBlock2.'","customTextBlock3":"'.$customTextBlock3.'","occupation":"'.$jobtype.'","dateAvailable":"'.$savedateavailable.'"}';
        					}
        					else if($clientname=='fisergroup1')
        					{ 
        						  $postUpdate='{"id": "'.$candidateId.'","employmentPreference":"'.$employmentPreference.'","customText4":"'.$noticePeriod.'"}';
        					}
        					else if($clientname=='ETSStaffingFuture')
        					{     
        						  $postUpdate='{"id": "'.$candidateId.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",    
            "city": "'.$city.'",            
            "state": "'.$state.'",                
            "zip": "'.$zip.'"                            
        },"customTextBlock2":"'.$customTextBlock2.'","employeeType":"'.$jobtype.'","occupation":"'.$jobtype.'","dateAvailable":"'.$savedateavailable.'"}';
        					}

 


      

 
       
     									}
     									else if($steps=='Step4')
     									{ 
 
     										echo 'Step4';

     										$customTextBlock1="Are you 18 years or older - ".$age; 
											


     										$postUpdate='{"id": "'.$candidateId.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",   
            "city": "'.$city.'",         
            "state": "'.$state.'",                 
            "zip": "'.$zip.'"                           
        }}';         


        $postUpdate='{"id": "'.$candidateId.'","customText8":"'.$age.'","workAuthorized":"'.$workauthorized.'","educationDegree":"'.$educationdegree.'"}';



        								$postEducation='{"candidate": {"id": "'.$candidateId.'"},"degree": "'.$educationdegree.'"}';
										$urlCandidateEducation=$resturl."entity/CandidateEducation";  
										$curl = curl_init();          
										curl_setopt_array($curl, array(                 
										 CURLOPT_URL => $urlCandidateEducation,               
										 CURLOPT_RETURNTRANSFER => true,             
										 CURLOPT_ENCODING => "",          
										 CURLOPT_MAXREDIRS => 10,        
										 CURLOPT_TIMEOUT => 30,    
										 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
										 CURLOPT_CUSTOMREQUEST => "PUT",  
										 CURLOPT_POSTFIELDS => $postEducation,          
										 CURLOPT_HTTPHEADER => array(      
										   "BhRestToken: ".$bhtoken,           
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
     
     									$Posturl=$resturl."entity/Candidate/".$candidateId;      

     									
										     
   	
										if($steps!="Step1")
										{  
											$curl = curl_init();           
											curl_setopt_array($curl, array(                
											 CURLOPT_URL => $Posturl,               
											 CURLOPT_RETURNTRANSFER => true,            
											 CURLOPT_ENCODING => "",         
											 CURLOPT_MAXREDIRS => 10,        
											 CURLOPT_TIMEOUT => 30,    
											 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
											 CURLOPT_CUSTOMREQUEST => "POST",  
											 CURLOPT_POSTFIELDS => $postUpdate,          
											 CURLOPT_HTTPHEADER => array(       
											   "BhRestToken: ".$bhtoken,           
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
									    }


									}    


 

									//Check candiate apply for the job or not
									echo "applied for job";
									$url=$resturl."search/JobSubmission?query=candidate.id:$candidateId&fields=*";                                             
									$header = array('bhresttoken: '.$bhtoken);                 
									$resource = curl_init();                       
									curl_setopt($resource, CURLOPT_URL, $url);           
									curl_setopt($resource, CURLOPT_HTTPHEADER, $header);        
									curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);             
									$result = json_decode(curl_exec($resource));  
									curl_close($resource);    
									

									foreach ($result->data as $rows)       
									{
										$rows->JOBID = $rows->jobOrder->id;                        
									}          
 									  
 									if(!empty($job_id))    
 									{  
		 									if(in_array($job_id, array_column($result->data, 'JOBID'))) 
		 									{    
											   
											}  
											else      
											{               
												

													$url2=$resturl."entity/JobSubmission";
													
													if($clientname=='cybersearchsf'){											
														
														$postJob2='{"candidate": {"id": "'.$candidateId.'"},"jobOrder": {"id": "'.$job_id.'"},"status": "Job Posting Response","source": "'.$jobSource.'"}';         


													} else {
														$postJob2='{"candidate": {"id": "'.$candidateId.'"},"jobOrder": {"id": "'.$job_id.'"},"status": "New Lead","source": "'.$jobSource.'"}';
													}												
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
													   "BhRestToken: ".$bhtoken,         
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
													 
													}  
													
											} 

										}


									if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")  
										{              

											if(!empty($filedata))
     												{

											$ext = pathinfo($filedata, PATHINFO_EXTENSION);
											$filename=$fname.' '.$lname.'.'.$ext;
											$filecontent = file_get_contents($filedata);                
								 			Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
								   
											$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
								  
											$file = chunk_split(base64_encode($path)); 

											$changedEntityId =$candidateId;       


											$url=$resturl."entityFiles/Candidate/".$changedEntityId."";   
											$header = array('bhresttoken: '.$bhtoken);                     
											$resource = curl_init();                 
											curl_setopt($resource, CURLOPT_URL, $url);           
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);           
											$result = json_decode(curl_exec($resource));  
											curl_close($resource);

											foreach ($result->EntityFiles as $rows)       
											{
												$rows->filename = $rows->name;                        
											}          
 									  
 									if(in_array($filename, array_column($result->EntityFiles, 'filename'))) 
 									{     
									    	echo 'notadded file';
									}  
									else   
									{     
  											echo 'add file';
									  
											$url1=$resturl."file/Candidate/".$changedEntityId."";        
											
			      							$postResume1='{"externalID": "portfolio","fileContent": "'.$file.'","fileType": "SAMPLE","type": "CV","name": "'.$filename.'"}';
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
												 	"BhRestToken: ".$bhtoken,        
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
										unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);     
									}

									} 	  
									
        
			}  
	 		        
	 }       
  
}
                       