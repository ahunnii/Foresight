<?php
	$page_title = 'About';
	include ('include/masthead.inc.html');
?>

	<h3 style="color: black;"> What Is Foresight? </h3>
	
	<br/>
	
	<p>
	Foresight is a website created with the express purpose of assisting 
	prospective college students.  There are multiple levels of complexity 
	to the site.  Students may wish to receive a quick estimate through our 
	quick calculator rather than utilize the full extent of the site.  
	Otherwise, anyone is able to create an account and get to work 
	personalizing their profile to receive much more precise results and 
	calculations.  All of the information needed to determine which college 
	to attend and the expense of doing so is made easily accessible in one 
	location.  
	</p>
	
	<br>
	
	<h3 style="color: black;"> More Information </h3>
	
	</br>
	
	<p> If you would like to know more or had anything you would like to suggest please feel free to contact us below. </p>
	
	<br/><br/>
	
<form class="email" action="mailer.php" method="post">
<p>Name:</p>
<input type="text" name="name" />
<p>E-mail:</p>
<input type="text" name="email" />
<p>Subject:</p>
<input type="text" name="subject" />
<p>Message:</p>
<textarea name="message"></textarea></p>
<input class="send" type="submit" value="Send">
</form>

<?php
	include('include/footer.inc.html');
?>