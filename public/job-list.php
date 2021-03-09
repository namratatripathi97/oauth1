<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Bootstrap 4 Accordion with Plus Minus Icons</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
  

@import url("https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css");  

@import url('https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap'); 

.h3, h3 {  
    font-size: 2.1rem;
   
    font-family: "Source Sans Pro",sans-serif;
    font-weight:700; 
} 
p
{
	color: #000000;
	font-family: "Source Sans Pro",sans-serif; 
}
.h4, h4 { 
    /*font-size: 2.1rem;
    font-weight: bold; */    
    font-family: "Source Sans Pro",sans-serif; 
    font-weight:600; 
} 
a:hover {
    color: #582e56;
    text-decoration: underline;
}
.subtext
{ 
	color: #000000;
}

a{
    color: #582e56; 
    text-decoration: none;
    background-color: transparent; 
}

.panel-default
{ 
 
border-bottom: 3px solid #582e56;
padding: 30px;
    /*border-bottom: 3px solid purple;*/ 
}

.panel-title > a:before { 
    float: right !important;
    font-family: FontAwesome;
    content:"\f068";
    padding-right: 5px;
}
.panel-title > a.collapsed:before {
    float: right !important;
    content:"\f067";
}
.panel-title > a:hover, 
.panel-title > a:active, 
.panel-title > a:focus  {
    text-decoration:none;
}
hr.new4 {  
  border: 2px solid #c39fc0; 
}

.twosixdiv 
{ 
	background-color:#f8f8f8;
	padding: 20px;
	 color: #582e56;
}
</style>
<script> 
   
</script>
</head>
<body> 
 






<div class="container">

<div class="row">
<div class="col-md-12">
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true" style="border-top: 3px solid #582e56;">

  <?php 
$mk=1;
$array=array("one","two"); 
 
foreach ($array as $row) 
{    $mk++;
  ?>
 




    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne<?php echo $mk?>">
             <h3 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne<?php echo $mk?>" aria-expanded="true" aria-controls="collapseOne<?php echo $mk?>">
          Associate Training Academy 
       <div class="row">
       	<div class="col-md-3"><h4>Stage: &nbsp;<span class="subtext">Consultant</span></h4>
       	</div>
       	<div class="col-md-3"><h4>Duration: &nbsp;<span class="subtext">3 months</span></h4> 
       	</div>
       </div>  
 
         </a> 
      </h3> 
   
        </div>
        <div id="collapseOne<?php echo $mk?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne<?php echo $mk?>">
           <div class="panel-body">
          <hr class="new4">
          <h4><span class="subtext">Overview</span></h4>
          <p>Lorem ipsum dolor sit amet, ea qui dictas consulatu, ad maiestatis mnesarchum vim, meis percipitur instructior nam te. Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.Est ex dico partiendo gloriatur.Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.</p>
            <p>Lorem ipsum dolor sit amet, ea qui dictas consulatu, ad maiestatis mnesarchum vim, meis percipitur instructior nam te. Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.Est ex dico partiendo gloriatur.Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.</p>
             <p>Lorem ipsum dolor sit amet, ea qui dictas consulatu, ad maiestatis mnesarchum vim, meis percipitur instructior nam te. Est ex dico partiendo gloriatur.Ipsum aliquam detraxit vis no, nulla graecis legendos et has. Te perfecto eleifend nam, ad pri veritus inimicus. Est ex dico partiendo gloriatur.</p>

              <h4><span class="subtext">Training Background</span></h4> 
              <div class="row">
              	<div class="col-md-6"><div class="twosixdiv">  
              		<h4>Resourcing | 4 weeks (20 hours)</h4>  
              		<h5 style="color: #000000;font-size: 16px;font-weight: bold;padding-top: 10px;">Key Skills</h5> 
              		<p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p> 
    <p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p>
    <p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p>     
              	</div>  
              </div>       
              	<div class="col-md-6"><div class="twosixdiv">
              		<h4>Business Development | 4 weeks (20 hours)</h4> 
              		<h5 style="color: #000000;font-size: 16px;font-weight: bold;padding-top: 10px;">Key Skills</h5> 
              		<p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p> 
    <p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p>
    <p><span style="font-size: 20px;
    font-weight: 700;
    color: #c39fc0;">&#8212;</span>&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #000000;font-weight: 600;">How to format a CV</span> </p>
              	</div>
              </div> 
          	  </div> 
        	</div>
        </div> 
    </div>  

<?php }?>
   
   
</div>
</div>
</div>
</div>
</body>
</html>