<?php # Kyle L. Oswald
	# This script will remove a record from the 
	# Offered_Degrees table.
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	# Mainly to bring {$log_email} into scope
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/error_reporting.inc.php');
	
	require_once('include/site_constants.inc.php');
	
	if (isset($_SESSION['User_Id']) && ($_SESSION['User_Permission'] == PERMISSION_ADMIN) && isset($_GET['record_id']) && is_numeric($_GET['record_id'])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/db_connect.inc.php');
		
		$record_id = $_GET['record_id'];
		@mysql_query("DELETE FROM Offered_Degrees WHERE _id=$record_id;") 
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