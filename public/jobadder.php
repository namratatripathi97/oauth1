<?php     
define("CALLBACK_URL", "https://oauth.redwoodtechnologysolutions.com/wp/oauth/public/jobadder.php");                  
define("AUTH_URL", "https://id.jobadder.com/connect/authorize");   
define("ACCESS_TOKEN_URL", "https://id.jobadder.com/connect/token");       
define("CLIENT_ID", "zx6uorfascju7jscn3a3jemtye");    
define("CLIENT_SECRET", "s3q6fv5dxmvulhzj7gyb6t46n4jzejevdq2wguhgyjeodwt2hbxu");      
define("SCOPE", "read write offline_access"); // optional        
$code=$_GET['code'];      

       
error_reporting(E_ALL);
ini_set('display_errors', 1);
//echo "hello"; exit;
/*
$data=$_GET;
   
print_r($data);
exit;   
*/

//$code = "14390f7256d91ce01fd3a8db4a8a5b82";     
          
 

    
 
 // CODE FOR Authorize STEP 2// 


/*$url = "https://id.jobadder.com/connect/token";      

$postdata  = "code=".$code;
$postdata .= "&grant_type=authorization_code";      
$postdata .= "&client_id=".CLIENT_ID;
$postdata .= "&client_secret=".CLIENT_SECRET;      
$postdata .= "&redirect_uri=".CALLBACK_URL;  

  
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

$response = json_decode($result); 
print_r($response); 
$access_token = $response->access_token;
$refresh_token = $response->refresh_token;     

echo 'ac'.$access_token;  
echo 'rf'.$refresh_token;
exit;*/

/*echo $token = $result["refresh_token"]; 
exit;*/





// CODE FOR GET ACCESS TOKEN BASED ON REFRESH TOKEN


/*$url = "https://id.jobadder.com/connect/token"; // The POST URL


$postdata  = "client_id=".CLIENT_ID;
$postdata .= "&client_secret=".CLIENT_SECRET;   
$postdata .= "&grant_type=refresh_token";    
$postdata .= "&refresh_token=".$refresh_token;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);     

$response = json_decode($result);
$access_token = $response->access_token;
$refresh_token = $response->refresh_token;         

echo 'acccc'.$access_token;
echo "<br/>"; 
echo 'refe'.$refresh_token;   */
  

//exit;


$access_token="5c3acd6c3b32a4f2fbc1f930f05d8ec1";
$refresh_token="a047c185f46100fd50e2673121792f4c";

//$url = "https://id.jobadder.com/connect/token"; // The POST URL


/*$postdata  = "client_id=".CLIENT_ID;
$postdata .= "&client_secret=".CLIENT_SECRET;   
$postdata .= "&grant_type=refresh_token";    
$postdata .= "&refresh_token=".$refresh_token;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);     

$response = json_decode($result);
echo $access_token = $response->access_token;  
echo "<br/>";   
echo $refresh_token = $response->refresh_token;
echo $apiurl = $response->api;*/
/*var_dump($result); */        
      


       
/*$data=$_GET;
$files=$_FILES;
$firstName=$data['1_3'];             
$lastName=$data['1_6'];      
$email=$data['2'];  
$phone=$data['3'];  
$linkedin=$data['12'];     
$filedata=$data['5'];
$filedata = $_FILES['5']['tmp_name'];
$filedata=$data['5'];
//$filedata = "http://100.26.243.216/wp-content/uploads/2019/10/SEO.docx";
$reference=$data['14'];  */ 
 
$firstName="Bruce";                      
$lastName="Stander";      
$email="bruce+stander@test.com";   
$phone="9213412111";              
$filedata="http://54.173.100.101/wp-content/uploads/gravity_forms/2-36ddb2bf0a7107c170c7d300a237202d/2019/12/Dayna-Quillin1.pdf";   
$reference="93474";    
//$applicant_id="3893141"; 
 // Code for submit job appliction 
     


$curl = curl_init();
curl_setopt_array($curl, array(
 CURLOPT_URL => "https://us1api.jobadder.com/v2/jobboards/113645/ads/".$reference."/applications",
 //CURLOPT_URL => "https://us1api.jobadder.com/v2/jobboards", 
 //CURLOPT_URL => "https://us1api.jobadder.com/v2/jobboards/113645/ads", 
 CURLOPT_RETURNTRANSFER => true,
 CURLOPT_ENCODING => "", 
 CURLOPT_MAXREDIRS => 10, 
 CURLOPT_TIMEOUT => 30,
 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,          
 CURLOPT_CUSTOMREQUEST => "POST",
 CURLOPT_POSTFIELDS => "{  \"firstName\": \"".$firstName."\",  \"lastName\": \"".$lastName."\",  \"email\": \"".$email."\"}",
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
 //echo $response;
 $response1 = json_decode($response);
 //print_r($response1);
echo $applicant_id = $response1->applicationId;
             
//echo 'appid'.$applicant_id;  
}   


     
//$applicant_id="5214696";
//$reference="85799";	*/       

/*$ext = pathinfo($filedata, PATHINFO_EXTENSION);
$filecontent = file_get_contents($filedata);
file_put_contents("$applicant_id.$ext",$filecontent);
$the_content_type = mime_content_type("$applicant_id.$ext");*/
//file_put_contents("$applicant_id.pdf",$filecontent);



/*$path = "$applicant_id.$ext";
$file = new CURLFile('/var/www/html/wp/oauth/public/'.$applicant_id.'.'.$ext);
//$file = new CURLFile('/var/www/html/wp/oauth/public/'.$applicant_id.'.'.$ext,'application/'.$ext,$applicant_id);
//$file = new CURLFile($path);
$file->setMimeType("$the_content_type");
$size = filesize($path);*/

//print_r($file); exit;

$the_content_type = mime_content_type("/var/www/html/wp/oauth/public/3893141.pdf");
//echo $the_content_type;
$cfile = new CURLFile('/var/www/html/wp/oauth/public/3893141.pdf');
$cfile->setMimeType($the_content_type);
$fields = array('file' => $cfile);
//print_r($fields);
//$size = filesize($filedata);

echo $reference; echo "<br>";
echo $applicant_id;


  
$ch = curl_init();     
//Get the response from cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Set the Url
curl_setopt($ch, CURLOPT_URL, "https://us1api.jobadder.com/v2/jobboards/113645/ads/".$reference."/applications/".$applicant_id."/Resume");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Authorization: Bearer {$access_token}",
  "Content-Type: multipart/form-data",
));

//Create a POST array with the file in it
$postData = array('fileData' => "hello testing");

curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

// Execute the request
$response = curl_exec($ch);

echo $response;       

//unlink("$applicant_id.$ext");





























/*

$fields = [
    'name' => new \CurlFile('https://www.marketersondemand.com/wp-content/uploads/2018/05/EToD-logo.png', 'image/png', 'filename.png')
];
     
    
 /*CURLOPT_URL => "https://api.jobadder.com/v2/jobboards/113383/ads/85831/applications/2832282/Resume",
 CURLOPT_URL => "https://api.jobadder.com/v2/jobboards/113383/ads/85831/applications/".$applicant_id."/Resume",*/

/*
$postfields = array("filedata" => "@$filedata");

$applicant_id="5214696";
$reference="85799";	

// Code for submit documnts// 
$curl = curl_init();
curl_setopt_array($curl, array(   
 CURLOPT_URL => "https://api.jobadder.com/v2/jobboards/113383/ads/".$reference."/applications/".$applicant_id."/Resume",
 CURLOPT_RETURNTRANSFER => true,     
 CURLOPT_ENCODING => "", 
 CURLOPT_MAXREDIRS => 10,  
 CURLOPT_TIMEOUT => 30,
 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,            
 CURLOPT_CUSTOMREQUEST => "POST",
 CURLOPT_POSTFIELDS => $postfields,  
 CURLOPT_HTTPHEADER => array(       
   "Authorization: Bearer ".$access_token, 
   "Content-Type:multipart/form-data",     
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
*/




     

?>
