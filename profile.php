<?php
	$page_title = 'Profile';
	$on_page_ready = 'onPageReady()';
	include ('include/masthead.inc.html');
	
	if (!isset($_SESSION['User_Id'])) {
		echo '<h4>You must be logged in to view this page</h4>';
		include('include/footer.inc.html');
		exit();
	}
	
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
		
		/**
		if (empty($_POST['address'])) {
			$errors['address'] = 'This field is required';
		} else {
			$address = $_POST['address'];
		}

		if (empty($_POST['city'])) {
			$errors['city'] = 'This field is required';
		} else {
			$city = $_POST['city'];
		}	

		if (empty($_POST['zipcode'])) {
			$errors['zipcode'] = 'This field is required';
		} else {
			$zipcode = $_POST['zipcode'];
		}	
		**/
		
		if (empty($_POST['email'])) {
			$errors['email'] = 'This field is required';
		} else {
			$email = $_POST['email'];
		}
		
		if (empty($errors)) {
			$fn = escape_data($first_name);
			$ln = escape_data($last_name);
			$em = escape_data($email);
			
			$result = @mysql_query(
			"UPDATE Users 
			SET first_name = '$fn', last_name = '$ln', email = AES_ENCRYPT('$em', '" . AES_KEY . "') 
			WHERE _id = {$_SESSION['User_Id']};");
			
			if (!$result) {
				$errors['main'] = "An error occurred while persisting your information";
			}
		}
	} else {
		$result = @mysql_query(
		"SELECT first_name, last_name, AES_DECRYPT(email, '" . AES_KEY . "') AS email 
		FROM Users
		WHERE _id = {$_SESSION['User_Id']};");
		
		if ($result && mysql_num_rows($result) > 0) {
			$record = mysql_fetch_array($result, MYSQL_ASSOC);
			
			$first_name = $record['first_name'];
			$last_name = $record['last_name'];
			$email = $record['email'];
			
			mysql_free_result($result);
		} else {
			echo '<div><h2 class="Error">Invalid ID</h2></div>';
			include('include/footer.inc.html');
			mysql_close();
			exit();
		}
	}
	
?>

<script type="text/javascript">
function onPageReady() {
	$('form#UserInfoForm').submit(function(e) {
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
		
		input = $(this).find('input[name="email"]');
		var val = input.attr('value');
		if (!/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(val)) {
			err = true;
			input.parent().next().text('Invalid Email Address');
		}
		
		if (err) {
			e.preventDefault();
		}
	});
}
</script>

<div id="ContentProfile">
	<h1>Hello, <?php echo $first_name; ?>! </h1><br>
	<p> Welcome to your profile!</p><br>
	<p> Your personal information</p><br>
	<form id="UserInfoForm" method="post" action="profile.php" style="{ padding: 20px; }">
		<input type="hidden" name="submitted" value="true"/>
		
		<h4 class="Error"><?php if (isset($errors['main'])) echo $errors['main']; ?></h4>
		<table class="formtable" width="500" border="1" rules="none">
			<tr>
				<td> First name: </td>
				<td><input type="text" name="first_name" value="<?php if (isset($first_name)) echo $first_name; ?>" size="40" maxlength="30" placeholder="Enter first name" required/></td>
				<td class="Error"><?php if (isset($errors['first_name'])) echo $errors['first_name']; ?></td>
			</tr>
			<tr>
				<td> Last name: </td>
				<td><input type="text" name="last_name" value="<?php if (isset($last_name)) echo $last_name; ?>" size="40" maxlength="30" placeholder="Enter last name" required/></td>
				<td class="Error"><?php if (isset($errors['last_name'])) echo $errors['last_name']; ?></td>
			</tr>
			<!--
			<tr>
				<td> Address: </td>
				<td><input type="text" name="address" value="<php if (isset($address)) echo $address; ?>" size="40" maxlength="30" placeholder="Enter address" required/><br>
				<td class="Error"><php if (isset($errors['address'])) echo $errors['address']; ?></td>
			</tr>
			<tr>
				<td> City: </td>
				<td><input type="text" name="city" value="<php if (isset($city)) echo $city; ?>" size="40" maxlength="30" placeholder="Enter city" required/><br>	
				<td class="Error"><php if (isset($errors['city'])) echo $errors['city']; ?></td>
			<tr>
				<td> Zipcode: </td>
				<td><input type="text" name="zipcode" value="<php if (isset($zipcode)) echo $zipcode; ?>" size="40" maxlength="30" placeholder="Enter zipcode" required/><br>
				<td class="Error"><php if (isset($errors['zipcode'])) echo $errors['zipcode']; ?></td>
			</tr> 
			-->
			<tr>
				<td> Email: </td>
				<td><input type="text" name="email" value="<?php if (isset($email)) echo $email; ?>" size="40" maxlength="50" placeholder="Enter contact email" required/></td>
				<td class="Error"><?php if (isset($errors['email'])) echo $errors['email']; ?></td>
			</tr>	
			</tr>
				<td><input type="submit" value="Accept"/></td>
				<td><input type="reset" value="Clear"/></td>
			</tr>		
		</table>
	</form>
</div>


<?php
	include('include/footer.inc.html');
?>