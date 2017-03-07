<?php
	if (!isset($_SESSION)) {
		session_start();
	}
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/error_reporting.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/db_connect.inc.php");
	
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
	} else {
		exit();
	}
	
	if (!(empty($username) || empty($password))) {
		$username = escape_data($username);
		$password = escape_data($password) . SHA_PASSWORD_SALT;
		
		$result = mysql_query("SELECT * FROM Users WHERE username = '$username' AND password = UNHEX(SHA('$password')) LIMIT 0, 1");
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			
			# Save to session variables
			$_SESSION['User_Id'] = $row['_id'];
			$_SESSION['User_Name'] = $row['username'];
			$_SESSION['User_Permission'] = $row['permission'];
			
			# Update last_in field
			mysql_query("UPDATE Users SET last_in=NOW() WHERE _id={$row['_id']};");
			
			# Close connection
			mysql_close();
			
			# Follow redirect (usually to previous page)
			$redirect = $_POST['redirect'];
			header('Location: http://' . $redirect);
			exit();
		} 
	} 
		
	mysql_close();
	
	include('include/masthead.inc.html');
?>

	<p>Invalid username or password</p>
	
<?php
	include('include/footer.inc.html');
?>