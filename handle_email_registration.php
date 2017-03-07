<?php #Kyle L. Oswald 9/23/12
	$page_title = 'Email Registration';
	include('include/masthead.inc.html');
?>
	<body>
		<?php # Kyle L. Oswald 9/22/12
			$firstname = $_REQUEST['firstname'];
			$lastname = $_REQUEST['lastname'];
			$fullname = $firstname . ' ' . $lastname;
			$affiliation = $_REQUEST['affiliation'];
			$email = $_REQUEST['email-address'];
			
			echo "<p>Thankyou $firstname $lastname for registering with Forsight, <br/>
				your email address at $email has been successfully stored in our <br/>
				database.</p>\n";
			
			# access checkbox array subs
			$sub_newsletter = isset($_POST['subs'][0]);
			$sub_notices = isset($_POST['subs'][1]);
			
			if ($sub_newsletter && $sub_notices) {
				echo "<p>You are subscribed to recieve the weekly newsletter & all other notices.</p>\n";
			} elseif ($sub_newsletter) {
				echo "<p>You are subscribed to recieve the weekly newsletter.</p>\n";
			} elseif ($sub_notices) {
				echo "<p>You are subscribed to recieve notices.</p>\n";
			} else {
				echo "<p>You have no active subscriptions.</p>\n";
			}
		?>
		<p><a href="index.php">To Index</a></p>
<?php 
	include('include/footer.inc.html');
?>