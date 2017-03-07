<?php 
	if (!isset($_SESSION)) {
		session_start();
	}
	
	# Mainly to bring {$log_email} into scope
	$suppress_error_redirect = true;
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/error_reporting.inc.php');
	
	if (isset($_SESSION['User_Id']) && isset($_GET['college_id']) && is_numeric($_GET['college_id'])) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../Secure/db_connect.inc.php');
		
		$user_id = $_SESSION['User_Id'];
		$college_id = $_GET['college_id'];
		
		mysql_query(
			"DELETE FROM Saved_Colleges 
			WHERE user_id = $user_id 
				AND college_id = $college_id"
		);
		
		@mysql_query(
			"DELETE s, c 
			FROM Saved_Schedules AS s 
			INNER JOIN Saved_Courses AS c 
			ON s.user_id = $user_id 
				AND s.college_id = $college_id 
				AND c.schedule_id = s._id"
		) OR trigger_error(mysql_error());
		
		mysql_close();
	}
	
	if (!empty($_SESSION['Return_Url'])) {
		header('Location: ' . $_SESSION['Return_Url']);
	} else { # Redirect to index
		header("Location: http://{$_SERVER['SERVER_NAME']}/index.php");
	}
	
	exit();
?>