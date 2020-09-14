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
//putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/html/wp/oauth/public/zippy.json');   
    
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
     		/*$email="himstrived@gmail.com";              
     		$addcc="rahul.sharma@thinklayer.co.in";                                    
     		$setfrom="oauthsupport@staffingfuture.com";  */            
 

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
			    //echo "Send successfully";  
    			/*die("Send successfully");  */     

            
     }
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
	 	  
	 	/*dd($sendAlert);    */                      
	 	
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
        
    	//$authcode='22%3A154f19ce-973e-47cc-adca-c280d22d7c18&client_id=0260955e-4731-4471-93f8-03901ca62bfb';
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
        
    	//$authcode='22%3A154f19ce-973e-47cc-adca-c280d22d7c18&client_id=0260955e-4731-4471-93f8-03901ca62bfb';
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
		  //echo  $response;

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
  		      
  		     	//print_r($request);      
  		    
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
        
    	//$authcode='22%3A154f19ce-973e-47cc-adca-c280d22d7c18&client_id=0260955e-4731-4471-93f8-03901ca62bfb';
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
				//$dataInsert = $datastore->entity('stage-bullhorn-client');
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

				$datastore->insert($dataInsert);  

				$url="http://bullhorn.redwoodapi.com/".$request['client_name'].".xml"; 
	 			
	 			
	 			  
  		    
  				echo 'Bullhorn Feed Link Created successfully ! '.$url;  

    
  				/*$query = $datastore->query();   
				$query->kind('stage-bullhorn-client');

				$res = $datastore->runQuery($query);   

				foreach ($res as $company) 
				{    
				     
				        
				  echo $gid=$company->key()->pathEndIdentifier();     
				   echo $name=$company['name']; // Google
				   echo 'bhresttoken'.$bhRestToken=$company['bhRestToken'];  
				   echo 'accessToken'.$accessToken=$company['accessToken'];   
				   echo 'resturl'.$restUrl=$company['restUrl'];
				   echo $client_id=$company['id'];    
				   echo $client_secret=$company['secret'];  
				   echo $username=$company['username'];   
				   echo $password=$company['password'];   
				   echo "<br/>";   



   
				}*/

      
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
				//$area_of_interest=""; 
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
						//$jobSource=$gsource;
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




			echo $applicant_name=$fname.' '.$lname;    
  			     

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

									    	//$sendAlert=$this->sendEmail($name,$clientname);     
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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail';  */    
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
											/*echo $result;
											exit; */    
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
										//exit;     
  										          
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
										/* exit;  */  
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

  			/*if($clientname=='LoyalSource')
  			{*/
				if( (isset($_POST['source'])) && ($custom_source_status==1) )      
				{

					$gsource=$_POST['source'];
					if(!empty($gsource)) 
					{
						//$jobSource=$gsource;
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
			//}
			
		/*	if($clientname=='lakeshore')
  			{
				if(isset($_POST['source']))      
				{

					$gsource=$_POST['source'];
					if(!empty($gsource))
					{
						//$jobSource=$gsource;
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
			}*/

			   
			//$job="JOB-1007";     
			
			if ((!empty($job)) AND (strpos($job,'JOB-') !== false)) {			
			//if (!empty($job)) { 
				$job_id=explode('-', $job)[1];
			} else if(!empty($job)) {
				$job_id=$job;
			} else {
				$job_id=0;    
			}    
			
			/*echo $job_id;   
			exit; */   
    
 
			/*$post = $_POST;                      
  			$fname = $_POST['fname'];  
			$lname = $_POST['lname'];  */ 
			/*$fname="Ganesh";  
			$lname="Jain";           
			$email="ganesh.jain@gmail.com";    
			$phone="9829412345";      
			$job_id='1007';        
			$resume_status="Yes";    */          
			$applicant_name=$fname.' '.$lname;    
  			 
			  
			   
		  

  
			//$applicant_name="rahul jshii";   
			//$filedata="https://jobs.tracker-rms.com/wp-content/uploads/gravity_forms/1-9c988dc1818c14684b17edf95218545c/2019/11/01simple1.pdf";   
			
 			                       
 
              
			if($apicall=='createResource')                
			{             


					if($clientname=='diamondpeak') {
						$jobSource = 'Jobs+';
					}
					
					if($clientname=='penfield') {
						$jobSource = 'Jobs+';
					}
     			   
					if($resume_status=="No")  
					{   
     		         
     		     	//dd($jobSource); 
						 /*echo "hello";  
						 exit; */  

						/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": 16541,"assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobid": "'.$job_id.'","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}'; */
     
						/*$postResource = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';    */            
   
							$postResource = '{"trackerrms": {"createResource": {"credentials": {"apikey": "'.$apikey.'", "username": "", "password": "", "oauthtoken": ""},"instructions":{"overwriteresource": true,"assigntoopportunity": "'.$job_id.'","assigntolist": "short","shortlistedby": "resource"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "'.$jobSource.'","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "'.$note.'","image": ""}}}}';          
   
							/*print_r($postResource); 
							exit; */   
     
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
									 $result=json_decode($response);
									 $message=$result->message;     

									 if(($notification_status==0) && ($message=="user or API Key not found")) 
											{             
												/*$sendAlert=$this->sendEmail($name,$clientname);           
												echo 'send mail';*/    
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
             

     							/*echo $postResume;
     							exit; */    
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
									 $result=json_decode($response);
									 $message=$result->message;  

									 if(($notification_status==0) && ($message=="user or API Key not found")) 
											{             
												/*$sendAlert=$this->sendEmail($name,$clientname);           
												echo 'send mail'; */   
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";            
											}

									}   

				}
			}
			if($apicall=='createResourceFromResume')   
			{ 

					/*$postResume = '{"trackerrms": {"createResource": {"credentials": {"username": "'.$username.'","password": "'.$password.'"},"instructions":{"overwriteresource": true,"assigntoopportunity": 16541,"assigntolist": "short"},"resource": {"firstname": "'.$fname.'", "lastname": "'.$lname.'", "fullname": "'.$fname.' '.$lname.'", "cellphone": "'.$phone.'", "email": "'.$email.'","jobtitle": " ","company": " ","address1": " ","address2": " ","city": " ","state": " ","zipcode": " ","country": " ","workphone": "","homephone": "'.$phone.'","cellphone": "'.$phone.'","linkedin": "","dateofbirth": "","nationality": "","languages": "","education": "","source": "Jobs +","jobhistory": [{"company": "","jobtitle": "","startdate": "","enddate": "","description": ""}],"salary": 0,"note": "","image": ""}}}}';*/

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
												/*$sendAlert=$this->sendEmail($name,$clientname);           
												echo 'send mail';  */    
												$credentials_update=Credential::find($id);
					 							$credentials_update->notification_status=1;
												$credentials_update->save();  
												echo "update Status";
											}   

					}
  
			}   
			if($apicall=='newApplicant')        
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
						echo $access_token = $response->access_token; 					       
						echo  $instance_url = $response->api;
						/*$sendAlert=$this->sendEmail($name,$clientname);     
						echo 'send mail';*/         
					}           
					else    
					{    
						if($notification_status==0)
						{          
							/*$sendAlert=$this->sendEmail($name,$clientname);     
							echo 'send mail';    */  
							$credentials_update=Credential::find($id);
 							$credentials_update->notification_status=1;
							$credentials_update->save();  
							echo "update Status";         
						}
					}          
					    
  					/*exit;  */          
					/*$curl = curl_init();                   
					curl_setopt_array($curl, array(   
					CURLOPT_URL => $instance_url."jobboards",      
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
					print_r($err); 
					curl_close($curl);
					if ($err) {  
					 echo "cURL Error #:" . $err;
					} else {       
						echo $response;     
					}
					exit;   */    
					              
					//$reference="85799";
					//https://us1api.jobadder.com/v2/jobboards/113383/ads"
					//echo $instance_url."jobboards/".$board_id."/ads/".$job_id."/applications";		
					//exit;
					 /*echo $instance_url."jobboards/113590/ads/".$job_id."/applications";
					 exit;*/                      
    				//$instance_url."jobboards/".$board_id."/ads/".$job_id."/applications";

					$curl = curl_init();                     
					curl_setopt_array($curl, array(    
					//CURLOPT_URL => "https://us1api.jobadder.com/v2/jobboards/113590/ads/93519/applications",					                             
					 CURLOPT_URL => $instance_url."jobboards/".$board_id."/ads/".$job_id."/applications",
						/*CURLOPT_URL => $instance_url."jobboards", */  
					 //CURLOPT_URL => "https://us1api.jobadder.com/v2/jobboards/113590/ads/".$job_id."/applications",
					 CURLOPT_RETURNTRANSFER => true,        
					 CURLOPT_ENCODING => "",               
					 CURLOPT_MAXREDIRS => 10,   
					 CURLOPT_TIMEOUT => 30,
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					 CURLOPT_CUSTOMREQUEST => "POST",
					 CURLOPT_POSTFIELDS => "{  \"firstName\": \"".$fname."\",  \"lastName\": \"".$lname."\",  \"email\": \"".$email."\",  \"phone\": \"".$phone."\"}",
					 CURLOPT_HTTPHEADER => array(               
					   "Authorization: Bearer ".$access_token, 
					   "Content-Type: application/json"   
					 ),
					));
					$response = curl_exec($curl);              

					$err = curl_error($curl);
					//print_r($err); 
					//print_r($response); 
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
					/*exit; */  
					if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{   

						$ext = pathinfo($filedata, PATHINFO_EXTENSION);   
						$filename=$fname.' '.$lname.'.'.$ext;
						$filecontent = file_get_contents($filedata);                
						Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
						$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 

						$url=$resumeLink;             

						$header = array('Authorization: Bearer '.$access_token,'Content-Type: multipart/form-data');               
						
						/*$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);*/
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
						echo 'dd';  
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
						/*$sendAlert=$this->sendEmail($name,$clientname);     
						echo 'send mail';*/         
					}           
					else    
					{    
						if($notification_status==0)
						{          
							/*$sendAlert=$this->sendEmail($name,$clientname);     
							echo 'send mail';    */  
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
					 //CURLOPT_URL => $instance_url."applications?email=".$email."&phone=".$phone,      
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
					//print_r($err); 
					//print_r($response); 
					curl_close($curl);
					if ($err) {
					 echo "cURL Error #:" . $err;
					} else {        
						//echo $response;     
						$response1 = json_decode($response);
						//exit;    



						/*$applicant_id = $response1->applicationId;
						$resumeLink = $response1->links->resume;                      
						echo 'appid'.$applicant_id;   */ 
						        
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

						  	//echo $response;  

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
							// echo "<br/>";
							    
						}
     
						/*$candidateId=$response1->items[0]->candidateId;   

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

				  

					$applicantID=$response1->items[0]->applicationId;  
				  
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
							}*/
   
					}    

   
			}
			if($apicall=='createApplicant')   
			{     
   
       
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
     
					//$source="None";      	            
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

				$postdata1='{
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
				  CURLOPT_POSTFIELDS => $postdata1,   
				  CURLOPT_HTTPHEADER => array(     
				    "Content-Type: text/plain", 
				    "Cookie: SERVERID=app3"
				  ),
				));    

				$response = curl_exec($curl);
				//echo $response;
				curl_close($curl);
				$result=json_decode($response);
				if(isset($result->records[0]->candidateID))
				{ 

				  echo 'CandidateID -'.$candidateID=$result->records[0]->candidateID;

				}
				else
				{ 
				  echo $candidateID='';    

				}

				if(!empty($job_id))
				{ 

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
											/*$ext = pathinfo($filedata, PATHINFO_EXTENSION);
											$filename=$fname.' '.$lname.'.'.$ext;
											$filecontent = file_get_contents($filedata);                
								 			Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
											$path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext);  
											$file = chunk_split(base64_encode($path));*/
											
											$file = base64_encode(file_get_contents($filedata));
										} 
										else
										{ 
											//$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=Hello%20Suresh&email=hello@gmail.com&phone=299999"; 
											//$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=".$fname."%20".$lname."&email=".$email."&phone=".$phone;           
											$filedata="https://oauth.redwoodtechnologysolutions.com/wp/oauth/prod-pdf-generate.php?name=".$fname."%20".$lname."&email=".$email."&phone=".$phone;       
											 
											$file = base64_encode(file_get_contents($filedata));        
											    
											/*$name=$fname.' '.$lname;      
											$file=base64_encode($name);   */
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
												/*$sendAlert=$this->sendEmail($name,$clientname);                   
												echo 'send mail';    */  
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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail';    */ 
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
									//$access_token=$access_token;
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
											/*echo $result;
											exit;*/    
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
							
					 
       
if($clientname=='bruce811')
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
	
	/*$json_array=array(
	'FirstName'=>$fname,
	'LastName'=>$lname,        
	'Email'=>$email,                
	'Phone'=>$phone,
	'LeadSource'=>$jobSource   
	);*/
}
     

          
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
     
//$parseResumeCand='{"ContactId": "'.$contact_id.'","Name": "'.$filename.'","ContentType": "application/'.$ext.'","Body": "'.$file.'"}'; 
  
     
								$curl = curl_init();      
							 curl_setopt_array($curl, array(       
							 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ContentVersion/",   
							 //CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ts2__Application__c",                  
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
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

					if( ($clientname=='bruce811')  && (!empty($job_id)) )   
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
						//CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"".$contact_id."\", \"Division__c\": \"".$division."\",\"ts2__Job__c\": \"".$job_id."\"}", 
						CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"".$contact_id."\", \"StaffingFuture_ID__c\": \"".$staffingfutureid."\", \"ts2__Job__c\": \"".$job_id."\"}", 
						 CURLOPT_HTTPHEADER => array(                            
						   "Authorization: Bearer ".$access_token,           
						   "Content-Type: application/json" 
						 ),        
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);    
						//print_r($err);
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
				else
				{

					   

					/*if($resume_status=="Yes" || $resume_status=="YES" || $resume_status=="yes")
					{      
								
     
								 
					}*/

				}   





			}
			if($apicall=='createContact')   
			{


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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail'; */    
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
						    
						//temporary loop please remove   
						/*$username_bullhorn='apiuser.LewisJames';         
					    $password_bullhorn='!0!0Huntcliff';
						$apiurl_bullhorn='https://auth.bullhornstaffing.com/oauth/token'; 
						$id_bullhorn='74'; 
						$client_id_bullhorn='09f2b351-7793-4a2f-8258-60d59bb35b61';  
						$apikey_bullhorn='BeXYnFeQW2ZQWS9MQNaET4WJ';     
						$refresh_token_bullhorn='42:5ab2a598-d4dd-4887-ac3f-5da0e141eb83';  
						$access_token_bullhorn='42:144c0d32-2b17-44fe-8de0-8c032e902723';    
						$source_bullhorn='';*/
						
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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail';    */ 
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
									//$access_token=$access_token;
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
											/*echo $result;
											exit;*/    
											$err = curl_error($resource);
											curl_close($resource);
											if ($err) {
									 echo "cURL Error #:" . $err;
									} else {
											$result_parse=json_decode($result);
											$parsedescription=$result_parse->candidate->description;
											$description=$parsedescription;
											//$description = mysql_escape_mimic($description);  


   

											//$description = json_encode($description);
											// string(16) ""<html><\/html>""
											/*var_dump($escaped);*/

											//$description = json_encode($description, JSON_UNESCAPED_SLASHES);
											// string(15) ""<html></html>""
											//var_dump($unescaped);  
      

											/*$text = str_replace("\n", "", $description);
											$data = preg_replace('/oauth.redwoodtechnologysolutions.com\s+/m', ' ', $description);
											$lines = explode("oauth.redwoodtechnologysolutions.com", $data);*/
											/*$description = htmlspecialchars(trim(strip_tags($description)));
											$description = trim(preg_replace('/\s+/', ' ', $description)); */
											//$description = mysql_escape_mimic($description);
											//echo 'successfully';
											//$description = str_replace(' ', '', $description);

											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);       
										}
									   }
									else
									  {   
									  		//$description="".$fname." ".$lname." oauth.redwoodtechnologysolutions.com Phone: ".$phone." oauth.redwoodtechnologysolutions.com Email: ".$email."oauth.redwoodtechnologysolutions.com";

									  		$description="".$fname." ".$lname."Phone: ".$phone."Email: ".$email."";      
									  }


					}     
  
					//$postContact='{"FirstName": "'.$fname.'","LastName": "'.$lname.'","Email": "'.$email.'","Phone": "'.$phone.'","LeadSource": "'.$jobSource.'","ts2__Text_Resume__c":"'.$description.'"}';   

					//$description="<HTML><HEAD><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\"><style>\tp.std   { margin-top: 0; margin-bottom: 0; border: 0 0 0 0; }</style></HEAD><BODY><!-- [[[ PDF.Page--><BR> &nbsp;&nbsp;<BR>2006 Balsam Way, Round Rock, TX 78665 ? M80.daynajq@gmail.com &nbsp;? (512) 803-7456 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>OBJECTIVES <BR>With over 16 years of outstanding customer service and satisfaction, advocating equanimity and dignity while &nbsp;<BR>promoting conscientiousness and ethics through character cultivation are my personal policies. These personal <BR>policies facilitate fundamental connections with the folks involved in all my endeavors. All of my clients and <BR>coworkers after working with me can attest that they are not just a number, but an invaluable asset. Unfortunately, <BR>due to an &nbsp;accident, I am unable to continue my career in Emergency Medical Services. Fortunately I am still able to <BR>continue to do what I love, bringing positivity into people’s lives! &nbsp;<BR>EDUCATION &nbsp;<BR>EMTS Academy &nbsp;<BR>November 2015 &nbsp;? &nbsp;&nbsp;&nbsp;Emergency Medical Technician – B &nbsp;<BR>? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>2011 &nbsp;&nbsp;&nbsp;Austin Community College &nbsp;<BR>Majors: Business, Anthropology <BR>&nbsp;&nbsp;<BR>CERTIFICATIONS &amp; SKILLS <BR>American Sign Language (ASL) ? &nbsp;Intermediate level of communication &amp; interpreting &nbsp;<BR>? &nbsp;<BR>TABC Certification ? July 2019 <BR>? &nbsp;<BR>Advanced Life Support ? October 2017 <BR>? &nbsp;<BR>CPR &amp; AED ? July 2019 &nbsp;&nbsp;<BR>? &nbsp;<BR>FEMA ?October 2015 &nbsp;<BR>Introduction to Hazardous Materials ? ·Emergency Management Institute <BR>?<BR>OSHA &nbsp;Safety in the Workplace Compliance ? 2015 <BR>? <BR>HIPPA and Patient Confidentiality ? 2015 <BR>? &nbsp;<BR>NREMT ?December 2015 &nbsp;<BR>·Registry #: E3225836 &nbsp;<BR>· ? &nbsp;<BR>EMT – B Certification ?November 2015 <BR>EMTS Academy <BR>DAYNA JON QUILLIN <BR>&nbsp;&nbsp;| P a g e 1<BR> <BR><!-- ]]] PDF.Page--><P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P><!-- [[[ PDF.Page--><BR>REFERENCES <BR>Sean Mullin &nbsp;? &nbsp;Former Supervisor &nbsp;? (512) 658-2948 ? &nbsp;seanmullin@me.com <BR>Seth Spurgers &nbsp;&nbsp;? &nbsp;Former Supervisor &nbsp;? (512) 263-0700 ? sspurgers@goldsgym.com <BR>Corey Savala &nbsp;&nbsp;? &nbsp;Former Supervisor &nbsp;? &nbsp;&nbsp;csavala@goldsgym.com <BR>Dorsie Martin &nbsp;? &nbsp;Former Coworker &nbsp;? (512) 284-5847 &nbsp;<BR>EXPERIENCE &nbsp;<BR>Favor Deliveries ? &nbsp;&nbsp;&nbsp;&nbsp;1705 Guadalupe St, Austin, TX <BR>Runner ? &nbsp;June 2017 – Currently an independent contractor <BR>? <BR>Uncle Gary’s Bar ? Farm to Market Road &nbsp;Pflugerville, TX <BR>Bartender ? June 2019 – Currently Employed <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;? &nbsp;<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Acadian Ambulance ? 4100 E. Ed Bluestein Blvd. Austin, TX <BR>EMT-B ? October 2017 – March 2018 <BR>? &nbsp;<BR>Golds Gym ? 12480 Bee Cave Rd, Bee Cave, TX &nbsp;<BR>Housekeeping; Kid’s Club ? &nbsp;June 2015 – February 2017 &nbsp;<BR>? &nbsp;&nbsp;<BR>The Great Vapor Caper ? &nbsp;1807 Red Fox Rd. &nbsp;<BR>Owner &nbsp;&amp; CEO ? &nbsp;&nbsp;June 2012 – January 2016 &nbsp;<BR>? <BR>Target ? 2300 W Ben White Blvd, Austin, TX &nbsp;&nbsp;<BR>Logistics Early AM; Instocks; Team lead/Manager &nbsp;? October 2011 – May 2012 &nbsp;<BR>? &nbsp;<BR>Black Sheep Lodge ? 2108 S Lamar Blvd, Austin, TX &nbsp;<BR>Waitress; Bartender ? July 2010 – February 2011 &nbsp;<BR>? &nbsp;<BR>Cherry Creek Catfish ? 5712 Manchaca Rd, Austin, TX &nbsp;<BR>Manager; Waitress, Bartender, Hostess,, Line Cook ? &nbsp;December 2008 – July 2010 &nbsp;<BR>? &nbsp;<BR>Chilis ? Buda, TX &amp; Austin, TX &nbsp;<BR>Waitress ? &nbsp;November 2005 – November 2009 &nbsp;<BR>Thank you so much for your time reviewing my resume! <BR>I sincerely hope my words have sparked your interest and look forward to our future pursuits! <BR>&nbsp;Have a great and productive day! <BR>&nbsp;&nbsp;<BR>&nbsp;&nbsp;| P a g e 2<BR> <BR><!-- ]]] PDF.Page--><P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P><!-- [[[ PDF.Page--><BR> <BR>&nbsp;&nbsp;| P a g e 3<BR> <BR><!-- ]]] PDF.Page--><P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P></BODY></HTML>";    
					//$description='';
					$html_content=$description;    
					//echo $description;					
					 
       
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
	/*$json_array=array(     
	'FirstName'=>$fname,   
	'LastName'=>$lname,          
	'Email'=>$email,                
	'Phone'=>$phone,
	'LeadSource'=>$jobSource    
	);*/

	$json_array=array(
	'FirstName'=>$fname,
	'LastName'=>$lname,        
	'Email'=>$email,                
	'Phone'=>$phone,
	'LeadSource'=>$jobSource   
	//'TR1__Source__c'=>$jobSource          
	);
}
    
/**/
          
$postContact=json_encode($json_array);                           
   

					                       
					$curl = curl_init();  
					curl_setopt_array($curl, array(                 
					 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Contact",    
					 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
					 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/ts2__Application__c",    
					 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Candidate",   
					 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Account", 
					 CURLOPT_RETURNTRANSFER => true, 
					 CURLOPT_ENCODING => "",       
					 CURLOPT_MAXREDIRS => 10,    
					 CURLOPT_TIMEOUT => 30,            
					 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
					 CURLOPT_CUSTOMREQUEST => "POST",  
					 //CURLOPT_POSTFIELDS => "{  \"AccountId\": \"".$accountid."\",  \"FirstName\": \"".$firstName."\",  \"LastName\": \"".$lastName."\"}",  
					 //CURLOPT_POSTFIELDS => "{ \"ContactId\": \"0033s0000105RnsAAE\",  \"Name\": \"TestResume.pdf\",  \"ContentType\": \"application/pdf\",  \"Body\": \"".$pdfcontent."\"}",              
					//CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"0033s0000105RnsAAE\",  \"ts2__Job__c\": \"a0K3s00000BpizZEAR\"}", 
					 //CURLOPT_POSTFIELDS => "{ \"FirstName\": \"".$fname."\",  \"LastName\": \"".$lname."\",\"Email\": \"".$email."\",  \"Phone\": \"".$phone."\",  \"LeadSource\": \"".$jobSource."\",\"ts2__Text_Resume__c\": \"".$description."\"}",   
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
					//echo 'appid'.$applicant_id;  
					}
					//echo "<br/>";               
					echo "CONTACT_ID:".$contact_id;           
					//echo "<br/>";            
					// Create Application
					
					//echo $clientname;


					if($clientname=='Synergishr') 
					{        
						$curl = curl_init();               
						curl_setopt_array($curl, array(           
						 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Contact",    
						 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
						 CURLOPT_URL => $instance_url."/services/data/v48.0/sobjects/ts2__Application__c",    
						 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Candidate",   
						 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Account", 
						 CURLOPT_RETURNTRANSFER => true, 
						 CURLOPT_ENCODING => "",       
						 CURLOPT_MAXREDIRS => 10,    
						 CURLOPT_TIMEOUT => 30,            
						 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
						 CURLOPT_CUSTOMREQUEST => "POST",  
						 //CURLOPT_POSTFIELDS => "{  \"AccountId\": \"".$accountid."\",  \"FirstName\": \"".$firstName."\",  \"LastName\": \"".$lastName."\"}",  
						 //CURLOPT_POSTFIELDS => "{ \"ContactId\": \"0033s0000105RnsAAE\",  \"Name\": \"TestResume.pdf\",  \"ContentType\": \"application/pdf\",  \"Body\": \"".$pdfcontent."\"}",                 
						CURLOPT_POSTFIELDS => "{ \"ts2__Candidate_Contact__c\": \"".$contact_id."\",  \"ts2__Job__c\": \"".$job_id."\"}", 
						 //CURLOPT_POSTFIELDS => "{ \"FirstName\": \"".$fname."\",  \"LastName\": \"".$lname."\",\"Email\": \"".$email."\",  \"Phone\": \"".$phone."\",  \"LeadSource\": \"Jobs +\"}",       
						 CURLOPT_HTTPHEADER => array(               
						   "Authorization: Bearer ".$access_token,           
						   "Content-Type: application/json"
						 ),     
						));
						$response = curl_exec($curl);          
						$err = curl_error($curl);    
						//print_r($err);
						curl_close($curl);     
						if ($err) {
						 echo "cURL Error #:" . $err;
						} else {      
						 echo $response;   
						  //echo "applicant create"; 
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
   								/*$parseResumeCand='{
"ContactId" : "0033s000010uhP1AAI",
"Name" : "test1212.txt",
"ContentType": "text/plain",      
"Body" : "hello testing"  
}';*/


   		/*$post_text=[
    "Name" => $filename,
	"body" => base64_encode(file_get_contents($filedata)),
	"parentId" => $contact_id,
]; 
$parseResumeCand=json_encode($post_text);   */      

     
$parseResumeCand='{"ContactId": "'.$contact_id.'","Name": "'.$filename.'","ContentType": "application/'.$ext.'","Body": "'.$file.'"}'; 
  
     
								$curl = curl_init();      
							 curl_setopt_array($curl, array(                      
							 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Attachment/",      
							  CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume",       
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ResumeAddUpdateBackend",     
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
							 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Contact",    
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
							 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/TR1__Application__c",    
							 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Candidate",   
							 //CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Account", 
							 CURLOPT_RETURNTRANSFER => true, 
							 CURLOPT_ENCODING => "",          
							 CURLOPT_MAXREDIRS => 10,     
							 CURLOPT_TIMEOUT => 30,            
							 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,                     
							 CURLOPT_CUSTOMREQUEST => "POST",  
							 //CURLOPT_POSTFIELDS => "{  \"AccountId\": \"".$accountid."\",  \"FirstName\": \"".$firstName."\",  \"LastName\": \"".$lastName."\"}",  
							 //CURLOPT_POSTFIELDS => "{ \"ContactId\": \"0033s0000105RnsAAE\",  \"Name\": \"TestResume.pdf\",  \"ContentType\": \"application/pdf\",  \"Body\": \"".$pdfcontent."\"}",                 
							CURLOPT_POSTFIELDS => "{ \"TR1__Applicant__c\": \"".$contact_id."\",  \"TR1__Job__c\": \"".$job_id."\",  \"TR1__Source__c\": \"Job Board\"}", 
							 //CURLOPT_POSTFIELDS => "{ \"FirstName\": \"".$fname."\",  \"LastName\": \"".$lname."\",\"Email\": \"".$email."\",  \"Phone\": \"".$phone."\",  \"LeadSource\": \"Jobs +\"}",       
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
							  //echo "applicant create"; 
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
   								/*$parseResumeCand='{
"ContactId" : "0033s000010uhP1AAI",
"Name" : "test1212.txt",
"ContentType": "text/plain",      
"Body" : "hello testing"  
}';*/


   		/*$post_text=[
    "Name" => $filename,    
	"body" => base64_encode(file_get_contents($filedata)),   
	"parentId" => $contact_id,   
]; 
$parseResumeCand=json_encode($post_text);   */         
   
     
$parseResumeCand='{"ParentId": "'.$contact_id.'","Name": "'.$filename.'","ContentType": "application/'.$ext.'","Body": "'.$file.'"}'; 
  
     
								$curl = curl_init();      
							 curl_setopt_array($curl, array(                          
							 CURLOPT_URL => $instance_url."/services/data/v42.0/sobjects/Attachment/",      
							  //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume", 
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ParseResume",       
							 //CURLOPT_URL => $instance_url."/services/apexrest/ts2/ResumeAddUpdateBackend",     
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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail'; */  
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

									    	//$sendAlert=$this->sendEmail($name,$clientname);     
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
												/*$sendAlert=$this->sendEmail($name,$clientname);     
												echo 'send mail';  */    
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
      

									if($resume_status=="Yes"  || $resume_status=="YES" || $resume_status=="yes")       
									   {      

									   	  $ext = pathinfo($filedata, PATHINFO_EXTENSION);   
										  $filename=$fname.' '.$lname.'.'.$ext;
										  $filecontent = file_get_contents($filedata);                
								 		  Storage::disk('local')->put("public/" .$applicant_name.'.'.$ext, $filecontent);         
										  $path=Storage::disk('local')->get("public/" .$applicant_name.'.'.$ext); 
										  //$file = chunk_split(base64_encode($path));


										/* $url = 'https://rest42.bullhornstaffing.com/rest-services/182p/resume/parseToCandidate?format=text&populateDescription=html';
$header = array('bhresttoken: '.$bhtoken,'Content-Type: multipart/form-data');

$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/Monish Soni.pdf','application/pdf','Monish Soni');
// Assign POST data
$fields = array('file' => $cfile);     
          
$resource = curl_init();          
curl_setopt($resource, CURLOPT_URL, $url);             
curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($resource, CURLOPT_POST, 1);
curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
$result = json_decode(curl_exec($resource));  
curl_close($resource);
print_r($result);
exit; 
$parsedescription=$result->candidate->description;   
echo $parsedescription;
exit;   
$description=$parsedescription;  
echo $description;   
exit;  
*/
						//$url = 'https://rest42.bullhornstaffing.com/rest-services/182p/resume/parseToCandidate?format=text&populateDescription=html';
						$url=$resturl."resume/parseToCandidate?format=text&populateDescription=html";
						$header = array('bhresttoken: '.$bhtoken,'Content-Type: multipart/form-data');
    
						//$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/Paul Dhaliwal.pdf','application/pdf','Paul Dhaliwal');
						$cfile = new CURLFile('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext,'application/'.$ext,$applicant_name);
									//$cfile = new \CurlFile('/var/www/html/wp/oauth/storage/app/public/Monish Soni.pdf','application/pdf','Monish Soni');
											// Assign POST data          
											$fields = array('file' => $cfile);           
											         
										/*echo "url";        
										echo $url;    
										echo "fiels";      
										print_r($fields);     
    					exit; 	          */
											$resource = curl_init();     
											curl_setopt($resource, CURLOPT_URL, $url);        
											curl_setopt($resource, CURLOPT_HTTPHEADER, $header);    
											curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($resource, CURLOPT_POST, 1);
											curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
											$result = curl_exec($resource);  
											/*echo $result;
											exit; */    
											$err = curl_error($resource);   
											curl_close($resource);    
											if ($err) {          
									 echo "cURL Error #:" . $err;     
									} else {
											$result_parse=json_decode($result);      
											$parsedescription=$result_parse->candidate->description;   
											$description=$parsedescription;  
											$description = mysql_escape_mimic($description);  
											/*$text = str_replace("\n", "", $description); 
											$data = preg_replace('/oauth.redwoodtechnologysolutions.com\s+/m', ' ', $description);
											$lines = explode("oauth.redwoodtechnologysolutions.com", $data);*/

											/*$description = htmlspecialchars(trim(strip_tags($description)));  
											$description = trim(preg_replace('/\s+/', ' ', $description)); */   
  
											//$description = mysql_escape_mimic($description); 
											//echo 'successfully';  
											//$description = str_replace(' ', '', $description);               

											unlink('/var/www/html/wp/oauth/storage/app/public/'.$applicant_name.'.'.$ext);  
										} 
									   }
									else     
									  {         
									  		$description="".$fname." ".$lname."  Phone: ".$phone."  Email: ".$email." ";  
									  }      
   
									 /* echo $description;   
									  exit; */  
									/* echo $text;       
									  exit;   */
      								//code for resume parse start here 
									/*$result_parse=json_decode($test);
									$parsedescription=$result_parse->candidate->description;       
									if($resume_status=="Yes")  
									   {   
									      $description=$parsedescription; 
									   }
									else 
									  {
									    $description="".$fname." ".$lname." oauth.redwoodtechnologysolutions.com Phone: ".$phone." oauth.redwoodtechnologysolutions.com Email: ".$email."oauth.redwoodtechnologysolutions.com";                          
									  }  
 									$postResume='{"firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","phone": "'.$phone.'","description":"'.$description.'"}';*/ 
 									//code end here   


//$postResume='{"firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","phone": "'.$phone.'","description":"'.$description.'"}'; 

/*$postdata='{
	"firstName": "Lucky",
	"lastName": "Jain",   
	"email": "lucky.jain@gmail.com", 
	"phone": "9777711111",
	"description": "<HTML>oauth.redwoodtechnologysolutions.com<HEAD>oauth.redwoodtechnologysolutions.com<!-- saved from url=(0014)about:internet --><META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">oauth.redwoodtechnologysolutions.com<style>oauth.redwoodtechnologysolutions.com	p.std   { margin-top: 0; margin-bottom: 0; border: 0 0 0 0; }oauth.redwoodtechnologysolutions.com</style>oauth.redwoodtechnologysolutions.com</HEAD>oauth.redwoodtechnologysolutions.com<BODY>oauth.redwoodtechnologysolutions.com\n<!-- [[[ PDF.Page-->\n<BR>\nWORK EXPERIENCE <BR>\nAdministrative/Safety Assistant <BR>\nBlack Eagle Transport - Stony Plain, AB <BR>\nJuly 2018 to Present <BR>\n• Answering telephones and providing general information, referring callers to other staff or taking messages as necessary.&nbsp;<BR>\n• Replying and receiving for all Voicemail, E-Mail and Postal inquiries.&nbsp; <BR>\n• Ordering supplies from various vendors (Greggs, Kal-Tire, Home Depot, Staples, Kentworth, Peterbilt and more)&nbsp; <BR>\n• Troubleshooting and resolution of all office equipment Phones, Tablets, PC\'s, Printers and Misc.)&nbsp; <BR>\n• New hire orientations (Fill-out application, verifying and copying licenses, certificates and insurance. Administering safety<BR>\ntraining courses on laptops and then verifying completion, All employee related data-entry into company database.)&nbsp; <BR>\n• International Fuel Tax Agreement Reporting.&nbsp; <BR>\n• Monitoring truck locations and speed limit violations through online GPS tracking software (TitanGPS)&nbsp; <BR>\n• Verifying contractors, licenses and insurance through various resources. ( Labour Clearance through WCB, Certificates of<BR>\ninsurance, Driver abstract reviews)&nbsp; <BR>\n• First response to all work related safety incidents.&nbsp; <BR>\nPAUL<BR>\nDHALIWAL<BR>\nEdmonton, AB T6W 3L9 <BR>\npauldhaliwal92@hotmail.com <BR>\n825-993-9370 (New Edmonton Area-Code) <BR>\nCore Skills:&nbsp; <BR>\nPunctual &amp; Organizational - ability to complete required tasks or fulfill obligations before or at a previously designated time<BR>\nwhile also demonstrating structure in doing so.&nbsp; <BR>\n&nbsp; <BR>\nAnalytical &amp; Problem Solving - ability to examine information or a situation in detail in order to identify key or important<BR>\nelements, their strengths and weaknesses and use these to make a recommendation or solve a problem.&nbsp; <BR>\n&nbsp; <BR>\nCommunication &amp; Teamwork - ability to communicate excellently verbally, written, in public, to groups or via electronic<BR>\nmedia while also possessing a strong commitment to the team environment by planning, organizing and collaborating<BR>\neffectively.&nbsp; <BR>\n&nbsp; <BR>\nTechnical Skills:&nbsp; <BR>\n• Words Per Minute (WPM): 62&nbsp; <BR>\n• PC Hardware (Assembly, Maintenance, Troubleshooting)&nbsp; <BR>\n• PC Software (Installing, Debugging, Microsoft Office Expert, Adobe Photoshop,&nbsp; <BR>\nSharePoint, Axon, SafetySync)&nbsp; <BR>\n• PC Operating Systems (Windows XP/Vista/7/8/10, Android, iOS, macOS)&nbsp; <BR>\n• PC Networks (Configurations, Servers, Routers, TCP/IP Socket, LAN Technology)&nbsp; <BR>\n• PC Security (Virus Protection, Maintenance, Monitoring, Backup Management, Disaster Recovery) <BR>\n<BR>\n\n<!-- ]]] PDF.Page-->\n<P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P>\n<!-- [[[ PDF.Page-->\n<BR>\n• Data entry of all safety and administrative information (Incident reports, Invoices, Guest-log report, Payroll hour reporting)&nbsp;<BR>\n• Handling of all insurance related issues or inquiries through various agencies.&nbsp; <BR>\n• Manage all company social media platforms (Website, Facebook, Instagram, LinkedIn)&nbsp; <BR>\n• Supervise Yard Laborers and Mechanic Shop employees. <BR>\nAdministrative/Project Assistant <BR>\nLoadstar Dispatchers - Edmonton, AB <BR>\n2016 to 2018 <BR>\n• Answering telephones and providing general information, referring callers to other staff or taking messages as necessary.&nbsp;<BR>\n• Replying and receiving for all Voicemail, E-Mail and Postal inquiries.&nbsp; <BR>\n• Create various types of Excel spreadsheets, PowerPoint presentations and Word documents for various projects&nbsp; <BR>\n• Data entry for various reports of Inventory, orders and misc. items.&nbsp; <BR>\n• Full-scale filing of various paperwork. (Invoices, Job applications, reports)&nbsp; <BR>\n• Maintain payroll information by collecting, calculating, and entering data.&nbsp; <BR>\n• Ordering supplies from various vendors (Greggs, Staples and Uline.)&nbsp; <BR>\n• Supervise Yard Laborers and assign tasks. (Pipe Yard Area) <BR>\nMaterial Handler <BR>\nTCL Supply Chain - Acheson, AB <BR>\n2015 to 2016 <BR>\n• Using automated voice-directed technology headsets to pick orders in a timed manner from various aisles in room and<BR>\nfrigid temperatures throughout warehouse.&nbsp; <BR>\n• Enter all reports into various internal company software\'s.&nbsp; <BR>\n• Safely operate material handling equipment including motorized pallet jacks, reach trucks and counterbalance forklifts.&nbsp; <BR>\n• Member of Safety Committee <BR>\nShipper/Receiver <BR>\nHalliburton - Leduc, AB <BR>\n2015 to 2015 <BR>\n• Checking and loading the necessary equipment required for scheduled jobs.&nbsp; <BR>\n• Use overhead crane and forklift to move equipment.&nbsp; <BR>\n• Notify supplier of any discrepancies, as well as marking BOL (bill of lading).&nbsp; <BR>\n• Entering orders, returns, reports and other information through various management software\'s.&nbsp; <BR>\n• Communicating effectively with customers through telephone or e-mail.&nbsp; <BR>\n• Member of Safety Committee <BR>\nMobile Advisor <BR>\nThe Mobile Shop - Edmonton, AB <BR>\n2014 to 2015 <BR>\n• Advising customers on latest deals, phones and accessories. &nbsp; <BR>\n• Phone activation\'s and troubleshooting advice.&nbsp; <BR>\n• Inventory reporting <BR>\n<BR>\n\n<!-- ]]] PDF.Page-->\n<P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P>\n<!-- [[[ PDF.Page-->\n<BR>\nComputer Sales Associate <BR>\nFuture Shop - Edmonton, AB <BR>\n2011 to 2013 <BR>\n• Working the sales floor and assisting customers with all product selection and inquiries.&nbsp; <BR>\n• Advising and selling company promotions to customers.&nbsp; <BR>\n• Re-stocking and front-facing hourly.&nbsp; <BR>\n• Inventory reporting.&nbsp; <BR>\n• Cold-calling previous customers for current promotions.&nbsp; <BR>\n• Shift-end clean-up. <BR>\nEDUCATION <BR>\nHigh School Diploma <BR>\nJ. Percy Page Senior School <BR>\nSKILLS <BR>\nHighly Self-Motivated, Ability to adapt to any role effortlessly, Highly dependable and punctual, Windows Expert, <BR>\nQuick Learner, Highly Computer Proficent (10+ years), Always positive attitude no matter the situation, Customer<BR>\nService (5 years), Warehouse Related Work (3 years), Work well under pressure, Very detail oriented, <BR>\nAdministrative Assistant, Billing, Outlook, Payroll, Microsoft Word, Receptionist, Microsoft Excel, SafetySync <BR>\nCERTIFICATIONS AND LICENSES <BR>\nStandard First Aid and CPR <BR>\nCSTS-20 <BR>\nWHIMIS-2015 <BR>\nCompTIA ITF+ <BR>\nASSESSMENTS <BR>\nCustomer Focus &amp; Orientation Skills — Highly Proficient <BR>\nApril 2019 <BR>\nHandling challenging customer situations. <BR>\n<BR>\n\n<!-- ]]] PDF.Page-->\n<P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P>\n<!-- [[[ PDF.Page-->\n<BR>\nFull results: https://share.indeedassessments.com/share_to_profile/<BR>\n59346bb0d515fdcb57215cef0bb511e6eed53dc074545cb7 <BR>\nData Entry — Expert <BR>\nDecember 2019 <BR>\nAccurately inputting data into a database. <BR>\nFull results: https://share.indeedassessments.com/share_to_profile/<BR>\n4b1d96d1caafede16d9cbe606c74057eeed53dc074545cb7 <BR>\nCustomer Service — Highly Proficient <BR>\nDecember 2019 <BR>\nIdentifying and addressing customer needs. <BR>\nFull results: https://share.indeedassessments.com/share_to_profile/<BR>\nd8ca7dec2c11e2dea2cb949035a6a2e8eed53dc074545cb7 <BR>\nProblem Solving — Highly Proficient <BR>\nNovember 2019 <BR>\nAnalyzing information when making decisions. <BR>\nFull results: https://share.indeedassessments.com/share_to_profile/<BR>\nd94d4c8c23acff277c04b19ebeb98980eed53dc074545cb7 <BR>\nWorkplace English (US) — Expert <BR>\nNovember 2019 <BR>\nUnderstanding spoken and written English in work situations. <BR>\nFull results: https://share.indeedassessments.com/share_to_profile/<BR>\n0d9e53b476f5f2ea0daf30822227beebeed53dc074545cb7 <BR>\nSafety Orientation Skills — Highly Proficient <BR>\nSeptember 2019 <BR>\nEmploying accident prevention strategies. <BR>\nFull results: https://share.indeedassessments.com/share_to_profile/10ce7b1fca0a30559778872056913b0a <BR>\nIndeed Assessments provides skills tests that are not indicative of a license or certification, or continued development in<BR>\nany professional field.<BR> \n<BR>\n\n<!-- ]]] PDF.Page-->\n<P style=\"page-break-before:always; border-top-style: dashed; border-top-width:thin; color:silver; \" ></P></BODY></HTML>"
}';*/
/*$curl = curl_init();
 
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://rest42.bullhornstaffing.com/rest-services/182p/entity/Candidate",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT", 
  CURLOPT_POSTFIELDS => "{oauth.redwoodtechnologysolutions.com\t\"firstName\": \"".$fname."\",oauth.redwoodtechnologysolutions.com\t\"lastName\": \"".$lname."\",oauth.redwoodtechnologysolutions.com\t\"email\": \"".$email."\",oauth.redwoodtechnologysolutions.com\t\"phone\": \"".$phone."\",oauth.redwoodtechnologysolutions.com\t\"description\": \"".$description."\"oauth.redwoodtechnologysolutions.com}",
  CURLOPT_HTTPHEADER => array(  
    "bhresttoken: ".$bhtoken,                 
    "cache-control: no-cache",       
    "postman-token: 7f00fb79-897b-0983-aee4-1a6bfa6ae99b"
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
} */      

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
									else if($clientname=='professionalalternatives' || $clientname=='AtlasStaffing')
									{
										$candidateStatus="New Candidate";	
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
									else if( (!empty($email_status)) && ($clientname=="AtlasStaffing") )
									{          
										echo 'ifloop for AtlasStaffing update';
										$candidateId=$email_status;  
									}
									else         
									{       
										echo 'elseloop executeapi';
										//exit;     
  										          
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
   

												//$categoryurl="https://rest.bullhornstaffing.com/rest-services/e999/meta/Candidate?fields=*";
												$area_of_interest = $_POST['area_of_interest']; 
												/*$categoryurl=$resturl."entity/Category?fields=*";           
												//$postCategory='{"name": "'.$area_of_interest.'","description":"'.$area_of_interest.'"}';
												$curl = curl_init();          
												curl_setopt_array($curl, array(                    
												 CURLOPT_URL => $categoryurl,               
												 CURLOPT_RETURNTRANSFER => true,            
												 CURLOPT_ENCODING => "",         
												 CURLOPT_MAXREDIRS => 10,        
												 CURLOPT_TIMEOUT => 30,    
												 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
												 CURLOPT_CUSTOMREQUEST => "GET",  
												 //CURLOPT_POSTFIELDS => $postCategory,          
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
												 exit;   
												}    */


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
											//$area_of_interest=$_POST['area_of_interest'];              
 											
											   
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
 
										/*echo $postResume;
										exit;  */              
	 								    //$postResume='{"name": "'.$fname.' '.$lname.'","firstName": "'.$fname.'","lastName": "'.$lname.'","email": "'.$email.'","status": "'.$candidateStatus.'","source": "'.$jobSource.'","phone": "'.$phone.'","description":"'.$description.'"}';   
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
										/* exit;  */  
										 $responseTest = json_decode($response);  
										}
										$candidateId =$responseTest->changedEntityId; 
									}    


									if(($clientname=='AtlasStaffing') && (!empty($candidateId)))
									{        
     						
     									echo 'hello';    

     									echo $candidateId; 

     									if($steps=='Step2')
     									{
     										echo 'Step2';



    
     										 

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
											/*echo $result;
											exit; */    
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

     										//$customTextBlock1="Are you 18 years or older - ".$age; 
											$customTextBlock2="Shift Availability - ".$shift;
											$customTextBlock3="Day Availability - ".$day; 


     										$postUpdate='{"id": "'.$candidateId.'","address": {
            "address1": "'.$address1.'",
            "address2": "'.$address2.'",   
            "city": "'.$city.'",         
            "state": "'.$state.'",                
            "zip": "'.$zip.'"                           
        }}';   


        $postUpdate='{"id": "'.$candidateId.'","customTextBlock2":"'.$customTextBlock2.'","customTextBlock3":"'.$customTextBlock3.'","occupation":"'.$jobtype.'","dateAvailable":"'.$savedateavailable.'"}';

 
        //echo $postUpdate;   
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


        $postUpdate='{"id": "'.$candidateId.'","customTextBlock1":"'.$customTextBlock1.'","workAuthorized":"'.$workauthorized.'","educationDegree":"'.$educationdegree.'"}';



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
											    //echo 'not apply for the job'; 
											}  
											else      
											{               
												// post a job       
													//echo 'apply'; 

													$url2=$resturl."entity/JobSubmission";
													
													if($clientname=='cybersearchsf'){											
														/*$postJob2='{"candidate": {"id": "'.$candidateId.'"},"jobOrder": {"id": "'.$job_id.'"},"status": "New Applicant","source": "'.$jobSource.'"}';*/
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
													 //$responseTest2 = json_decode($response);  
													} 
													//end post job
											} 

										}

									/*if($result->total==0)
									{
										$applyjobid=0;
									}
									else   
									{
										$applyjobid=$result->data[0]->jobOrder->id;	
									}    
									echo 'jobid';
									echo $job_id; 
									echo 'appid';       
									echo $applyjobid;
									exit; */         
									/*if($applyjobid!=$job_id)     
									{       
	
											
  									}*/

  									/*if( (!empty($email_status)) && (!empty($phone_status)) )
									{
										echo 'ifloop added';
										
									   $candidateId=$email_status;
									}
									else
									{
										echo 'else'  

									}*/



									
   


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
                       