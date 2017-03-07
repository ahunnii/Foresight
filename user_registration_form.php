<?php #Kyle L. Oswald 9/23/12
	if (!isset($_SESSION)) {
		session_start();
	}
	
	require_once('../Secure/error_reporting.inc.php');
	require_once('../Secure/db_connect.inc.php');
	require_once('include/misc_functions.inc.php');
	
	$page_title = 'User Registration';
	$on_page_ready = 'onPageReady()';
	
	$errors = array();
	if (isset($_POST['submitted'])) { # validate
		
		if (empty($_POST['first_name'])) {
			$errors['first_name'] = 'This field is required';
		} else {
			$first_name = $_POST['first_name'];
		}
		
		if (empty($_POST['last_name'])) {
			$errors['last_name'] = 'This field is required';
		} else {
			$last_name = $_POST['last_name'];
		}
		
		if (empty($_POST['username'])) {
			$errors['username'] = 'This field is required';
		} else if (strlen($_POST['username']) < 5) {
			$errors['username'] = 'Username must be at least 5 characters';
		} else {
			$username = $_POST['username'];
			
			# Check db for duplicate username
			$result = mysql_query("SELECT _id FROM Users WHERE username='" . escape_data($username) . "' LIMIT 0, 1;");
			if (mysql_num_rows($result) > 0) {
				$errors['username'] = 'This username has already been registered';
			}
		}
		
		if (empty($_POST['email'])) {
			$errors['email'] = 'This field is required';
		} else {
			$email = $_POST['email'];
		}
		
		if (empty($_POST['password1'])) {
			$errors['password'] = 'This field is required';
		} else if (strlen($_POST['password1']) < 5) {
			$errors['password'] = 'Password must be at least 5 characters';
		} else if (strcmp($_POST['password1'], $_POST['password2']) != 0) {
			$errors['password'] = 'Passwords do not match';
		} else {
			$password = $_POST['password1'];
		}
		
		if (empty($errors)) { # no errors
			
			$first_name2 = escape_data($first_name);
			$last_name2 = escape_data($last_name);
			$username2 = escape_data($username);
			$email = escape_data($email);
			$password = escape_data($password) . SHA_PASSWORD_SALT;
			
			$result = @mysql_query("INSERT INTO Users ( username, email, password, first_name, last_name, creation_date, active, last_in ) " .
				"VALUES ( '$username2', AES_ENCRYPT('$email', '" . AES_KEY . "'), UNHEX(SHA('$password')), '$first_name2', '$last_name2', NOW(), true, NOW() );");
				
			
			if ($result) { # Insert success
				
				$_SESSION['User_Id'] = mysql_insert_id();
				$_SESSION['User_Name'] = $username;
				$_SESSION['User_Permission'] = 1;
				
				include('include/masthead.inc.html');
				
			?>
			
				<p style="{ width: 100%; text-align: center; margin-left: 20px; margin-right: 20px; }">
				Thank you <?php echo $first_name . ' ' . $last_name; ?>, your account 
				has been successfully registered. Be sure to <a href="browse_colleges.php">add colleges to your profile</a>. 
				Saved colleges can be viewed from your profile under <a href="user_colleges.php">My Colleges</a>. 
				You can also create a course schedule for any of your saved colleges to increase accuracy of 
				expense calculations for materials like textbooks and commute to and from campus.
				</p>
				
			<?php 
			} else { # mysql error 
			
				include('include/masthead.inc.html');
				echo '<p style="{ width: 100%; text-align: center; margin-left: 20px; margin-right: 20px; }">';
				echo 'A system error occurred while registering your account. ';
				echo '<a href="user_registration_form.php">Click Here</a> to return to the registration page.</p>';
			}
			
			mysql_close();
			
			include('include/footer.inc.html');
			exit();
		}
	}
	
	include('include/masthead.inc.html');
?>

<script type="text/javascript">
function onPageReady() {
	$('form#UserRegistration').submit(function(e) {
		var err = false;
		
		var input = $(this).find('input[name="first_name"]');
		var val = input.attr('value');
		if (!/^[a-zA-Z]{1,30}$/.test(val)) {
			err = true;
			input.parent().next().text('Invalid Input');
		}	
			
		input = $(this).find('input[name="last_name"]');
		var val = input.attr('value');
		if (!/^[a-zA-Z]{1,30}$/.test(val)) {
			err = true;
			input.parent().next().text('Invalid Input');
		}
		
		input = $(this).find('input[name="username"]');
		var val = input.attr('value');
		if (!/^[a-zA-Z0-9]{1,30}$/.test(val)) {
			err = true;
			input.parent().next().text('Invalid Input');
		}	
		
		input = $(this).find('input[name="email"]');
		var val = input.attr('value');
		if (!/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(val)) {
			err = true;
			input.parent().next().text('Invalid Email Address');
		}
		
		input = $(this).find('input[name="password1"]');
		var pass1 = input.attr('value');
		if (!/^[a-zA-Z0-9]{5,30}$/.test(pass1)) {
			err = true;
			input.parent().next().text('Invalid Input');
		}
		
		input = $(this).find('input[name="password2"]');
		if (!pass1)  {
			err = true;
			input.parent().next().text('Invalid Input');
		}
		
		if (err) {
			e.preventDefault();
		}	
	});		
}
</script>

		<div>
			<form id="UserRegistration" method="post" action="user_registration_form.php" style="{ padding: 20px; }">
				<input type="hidden" name="submitted" value="true"/>
				
				<legend></legend>
				<table class="formtable" width="500" border="1" rules="none">
					<tr>
						<td>First Name:</td>
						<td><input type="text" name="first_name" value="<?php if (isset($first_name)) echo $first_name; ?>" size="40" maxlength="30" placeholder="Enter first name" required/></td>
						<td class="Error"><?php if (isset($errors['first_name'])) echo $errors['first_name']; ?></td>
					</tr>
					<tr>
						<td>Last Name:</td>
						<td><input type="text" name="last_name" value="<?php if (isset($last_name)) echo $last_name; ?>" size="40" maxlength="30" placeholder="Enter last name" required/></td>
						<td class="Error"><?php if (isset($errors['last_name'])) echo $errors['last_name']; ?></td>
					</tr>
					<tr>
						<td>Username:</td>
						<td><input type="text" name="username" value="<?php if (isset($username)) echo $username; ?>" size="40" maxlength="25" placeholder="Enter your username" required/></td>
						<td class="Error"><?php if (isset($errors['username'])) echo $errors['username']; ?></td>
					</tr>
					<tr>
						<td>Email:</td>
						<td><input type="email" name="email" value="<?php if (isset($email)) echo $email; ?>" size="40" maxlength="50" placeholder="Enter contact email" required/></td>
						<td class="Error"><?php if (isset($errors['email'])) echo $errors['email']; ?></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" name="password1" size="40" maxlength="25" required/></td>
						<td class="Error"><?php if (isset($errors['password'])) echo $errors['password']; ?></td>
					</tr>
					<tr>
						<td>Re-Enter Password:</td>
						<td><input type="password" name="password2" size="40" maxlength="25" required/></td>
					</tr>
					<tr>
						<td/>
						<td>
							<input type="submit" value="Send"/>&nbsp;
							<input type="reset" value="Clear"/>
						</td>
					</tr>
				</table>
			</form>
		</div>

<?php
	include('include/footer.inc.html');
?>