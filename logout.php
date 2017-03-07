<?php
	if (!isset($_SESSION)) {
		session_start();
	}
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/error_reporting.inc.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/db_connect.inc.php');
	
	if (!empty($_SESSION['User_Id'])) {
		# Update login information
		mysql_query("UPDATE Users SET last_out=NOW() WHERE _id={$_SESSION['User_Id']};");
		
		# Reset session variables
		$_SESSION = array();
	}
	
	mysql_close();
	
	# Redirect to index
	header("Location: http://{$_SERVER['SERVER_NAME']}/index.php");
	exit();
?>