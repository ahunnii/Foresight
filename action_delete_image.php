<?php # Kyle L. Oswald
	# This script will remove a record from the 
	# Campus_Images table & delete the image file.
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	# Mainly to bring {$log_email} into scope
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/error_reporting.inc.php');
	
	require_once('include/site_constants.inc.php');
	
	if (isset($_SESSION['User_Id']) && ($_SESSION['User_Permission'] == PERMISSION_ADMIN) && isset($_GET['record_id']) && is_numeric($_GET['record_id'])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/db_connect.inc.php');
		
		$record_id = $_GET['record_id'];
		
		$result = @mysql_query("SELECT filename FROM Campus_Images WHERE _id=$record_id;");
		
		if ($result) {
			$row = mysql_fetch_array($result, MYSQL_NUM);
			$file_path = DIR_CAMPUS_IMAGES . $row[0] . '.jpg';
		
			if (@mysql_query("DELETE FROM Campus_Images WHERE _id=$record_id;")) { 
				@unlink($file_path);
			} else {
				error_log($log_email, 1, mysql_error());
			}
		} else {
			error_log($log_email, 1, mysql_error());
		}
			
		mysql_close();
	}
	
	if (!empty($_SESSION['Return_Url'])) {
		header('Location: ' . $_SESSION['Return_Url']);
	} else { # Redirect to index
		header("Location: http://{$_SERVER['SERVER_NAME']}/index.php");
	}
	
	exit();

?>