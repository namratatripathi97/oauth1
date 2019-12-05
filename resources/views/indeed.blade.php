<?php $apitoken=$_GET['apitoken'];  
	 	$jobid=$_GET['jobid'];  
	 	$joblocation=$_GET['joblocation'];  
	 	$jobcompanyname=$_GET['jobcompanyname'];  
	 	$jobtitle=$_GET['jobtitle'];  
	 	$joburl=$_GET['joburl'];  
	 	$posturl=$_GET['posturl'];  

?>  
<span class="indeed-apply-widget" 
data-indeed-apply-apitoken="<?php echo $apitoken;?>"     
data-indeed-apply-jobid="<?php echo $jobid;?>"      
data-indeed-apply-joblocation="<?php echo $joblocation;?>"   
data-indeed-apply-jobcompanyname="<?php echo $jobcompanyname;?>" 
data-indeed-apply-jobtitle="<?php echo $jobtitle;?>" 
data-indeed-apply-joburl="<?php echo $joburl;?>" 
data-indeed-apply-locale="en" 
data-indeed-apply-posturl="<?php echo $posturl;?>"   
data-indeed-apply-continueurl="<?php echo $joburl;?>" 
data-indeed-apply-jobmeta="indeed-career-pages"     
data-indeed-apply-resume="REQUIRED" 
data-indeed-apply-questions="nigma://Wmh2bl9XElVTFEddREA?locale=en_US&amp;v=3"></span>  

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>     

	$(window).click(function(e) {    
    /*alert(e.target.id); // gives the element's ID 
    alert(e.target.className); // gives the elements class(es)*/

     Redirect();  
});
	 function Redirect() {
               window.location = "http://oauth.redwoodtechnologysolutions.com/wp/oauth/public/api/indeed-redirect";
            }  
(function(d, s, id) {
var js, iajs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)){return;}
js = d.createElement(s); js.id = id;js.async = true;
js.src = "https://apply.indeed.com/indeedapply/static/scripts/app/bootstrap.js";
iajs.parentNode.insertBefore(js, iajs); 
}
(document, 'script', 'indeed-apply-js'));    


</script>  