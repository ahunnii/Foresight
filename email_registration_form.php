<?php #Kyle L. Oswald 9/23/12
	$page_title = 'User Registration';
	include('include/masthead.inc.html');
?>
		<form method="post" action="handle_email_registration.php" method="post">
			<legend>Enter your contact information below</legend>
			<table class="formtable" width="300" border="1" rules="none">
				<tr>
					<td>First Name:</td>
					<td><input type="text" name="firstname" size="40" maxlength="30" placeholder="Enter first name" required/></td>
				</tr>
				<tr>
					<td>Last Name:</td>
					<td><input type="text" name="lastname" size="40" maxlength="30" placeholder="Enter last name" required/></td>
				</tr>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="username" size="40" maxlength="25" placeholder="Enter your username" required/></td>
				</tr>
				<tr>
					<td>Email:</td>
					<td><input type="email" name="email-address" size="40" maxlength="50" placeholder="Enter contact email" required/></td>
				</tr>
				<tr>
					<td>Re-enter Password:</td>
					<td><input type="password" name="password1" size="40" maxlength="25" required/></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password2" size="40" maxlength="25" required/></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" value="Send"/>&nbsp;
						<input type="reset" value="Clear"/>
					</td>
				</tr>
			</table>
		</form>

<?php
	include('include/footer.inc.html');
?>