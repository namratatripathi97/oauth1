<?php

 
$url = 'https://rest42.bullhornstaffing.com/rest-services/182p/resume/parseToCandidate?format=text&populateDescription=html';
$header = array('bhresttoken: 890111e5-fe40-420b-a6df-2c8013ea76e0','Content-Type: multipart/form-data');

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
var_dump($result);    

