<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
require('fpdf/fpdf.php');   
  
//create a FPDF object
$name=$_GET['name'];  
$email=$_GET['email']; 
$phone=$_GET['phone'];   
$content="Name:".$name."\n"."Email:".$email."\n"."Phone:".$phone."\n";
     
$pdf=new FPDF();

//set document properties 
$pdf->SetAuthor('Lana Kovacevic');      
$pdf->SetTitle('Resume');  

//set font for the entire document
$pdf->SetFont('Helvetica','B',20);
$pdf->SetTextColor(50,60,100);

//set up a page
$pdf->AddPage('P');
$pdf->SetDisplayMode(real,'default');

//insert an image and make it a link
//$pdf->Image('logo.png',10,20,33,0,' ','http://www.fpdf.org/');

//display the title with a border around it
$pdf->SetXY(50,20);  
$pdf->SetDrawColor(50,60,100);
$pdf->Cell(100,10,'Resume',1,0,'C',0);
  
//Set x and y position for the main text, reduce font size and write content  
$pdf->SetXY (10,50);
$pdf->SetFontSize(10);          
$pdf->Write(5,$content);

//Output the document
$pdf->Output('example1.pdf','I');
?>