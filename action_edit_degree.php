<?php # Kyle L. Oswald 10/25/12
	# This is not a viewable page, it is a proxy 
	# which translates arguments & sets the 
	# $_SESSION['action'] variable to be read by 
	# another script.
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	include('include/site_constants.inc.php');
	
	# Verify permissions
	if (!(isset($_SESSION['User_Permission']) 
		&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
	}
	
	# Validate arguments
	if (isset($_GET['record_id']))
		$record_id = $_GET['record_id'];
	else 
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/invalid_page_access.php');
	
	$_SESSION['action'] = ACTION_EDIT;
	
	header('Location: http://' . $_SERVER['SERVER_NAME'] . "/degree_operation.php?record_id=$record_id");
	exit();
?>