<?php 
if (!isset($_SESSION)) {
	session_start();
}

$suppress_error_redirect = true;
include('../../Secure/error_reporting.inc.php');
include('../../Secure/db_connect.inc.php');

header('Content-type: application/json');

if (isset($_SESSION['User_Id'])) {
	$user_id = $_SESSION['User_Id'];
} else {
	t_error('User is not signed in');
}

if (isset($_POST['course_ids']) 
	&& is_array($_POST['course_ids']) 
	&& check_values($_POST['course_ids'])) {
	
	$course_ids = $_POST['course_ids'];
} else {
	t_error('Invalid Data For Course IDs: ' . print_r($_POST['course_ids']));
}

if (isset($_POST['college_id']) && preg_match('/^[0-9]+$/', $_POST['college_id'])) {
	$college_id = $_POST['college_id'];
} else {
	t_error('Invalid College ID: ' . $_POST['college_id']);
}

if (isset($_POST['schedule_id'])) {
	$schedule_id = $_POST['schedule_id'];
	if (preg_match('/^[0-9]+$/', $schedule_id) === 1) {
		
		#$list = implode(' ),( ', $course_ids);
		mysql_query(
			"DELETE FROM Saved_Courses 
			WHERE schedule_id = $schedule_id" #AND course_id NOT IN ( $list )"
		);

	} else {
		t_error('Invalid Schedule ID: ' . $schedule_id);
	}
} else {
	mysql_query(
		"INSERT INTO Saved_Schedules ( 
			user_id, 
			college_id
		) 
		VALUES (
			$user_id, 
			$college_id 
		)"
	);
	
	$schedule_id = mysql_insert_id();
}

$list = implode("),($schedule_id,", $course_ids);
mysql_query(
	"INSERT INTO Saved_Courses (
		schedule_id, 
		course_id 
	) 
	VALUES ( $schedule_id, $list )"
);

echo '{ "schedule_id": "' . $schedule_id . '" }';

	
function check_values($arr) {
	$count = count($arr);
	for ($i = 0; $i < $count; $i++) {
		if (preg_match('/^[0-9]+$/', $arr[$i]) === 0) 
			return false;
	}
	
	return true;
}

function t_error($message) {
	echo '{ "error": "' . $message . '" }';
	trigger_error($message);
}
?>