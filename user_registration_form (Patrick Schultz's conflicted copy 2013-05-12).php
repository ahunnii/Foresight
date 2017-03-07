<?php #Kyle L. Oswald 9/23/12
	$page_title = 'User Registration';
	include('include/masthead.inc.html');
	
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
			
			$first_name = escape_data($first_name);
			$last_name = escape_data($last_name);
			$username = escape_data($username);
			$email = escape_data($email);
			$password = escape_data($password);
			
			$result = @mysql_query("INSERT INTO Users ( username, email, password, first_name, last_name, creation_date, active ) " .
				"VALUES ( '$username', '$email', SHA('$password'), '$first_name', '$last_name', NOW(), true );");
				
			if ($result) { # Insert success
				echo '<p style="{ width: 100%; text-align: center; margin-left: 20px; margin-right: 20px; }">';
				echo 'Your account has been registered. <a href="">Click Here</a> to log in.</p>';
			} else { # mysql error 
				echo '<p style="{ width: 100%; text-align: center; margin-left: 20px; margin-right: 20px; }">';
				echo 'A system error occurred while registering your account. ';
				echo '<a href="user_registration_form.php">Click Here</a> to log return to the registration page.</p>';
			}
			
			mysql_close();
			
			include('include/footer.inc.html');
			exit();
		}
	}
?>
		<div>
			<form method="post" action="user_registration_form.php" style="{ padding: 20px; }">
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