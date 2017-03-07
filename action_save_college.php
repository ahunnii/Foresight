<?php # Kyle L. Oswald
	# This script will add a new record into the 
	# Saved_Colleges table for the logged in user.
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	# Mainly to bring {$log_email} into scope
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/error_reporting.inc.php');
	
	if (isset($_SESSION['User_Id']) && isset($_GET['college_id']) && is_numeric($_GET['college_id'])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/db_connect.inc.php');
		
		@mysql_query("INSERT INTO Saved_Colleges ( user_id, college_id ) VALUES ( {$_SESSION['User_Id']}, {$_GET['college_id']} );") 
			OR error_log($log_email, 1, mysql_error());
		
		mysql_close();
	}
	
	if (!empty($_SESSION['Return_Url'])) {
		header('Location: ' . $_SESSION['Return_Url']);
	} else { # Redirect to index
		header("Location: http://{$_SERVER['SERVER_NAME']}/index.php");
	}
	
	exit();
?>