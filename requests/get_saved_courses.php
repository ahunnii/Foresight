<?php 

if (!isset($_SESSION)) {
	session_start();
}

$suppress_error_redirect = true;
include('../../Secure/error_reporting.inc.php');
include('../../Secure/db_connect.inc.php');

include('amazon_book_info.php');

header('Content-type: application/json');

function t_error($message) {
	echo '{ "error": "' . $message . '" }';
	trigger_error($message);
}

if (isset($_SESSION['User_Id'])) {
	$user_id = $_SESSION['User_Id'];
} else {
	t_error('User is not signed in');
}

if (isset($_POST['schedule_id']) && preg_match('/^[0-9]+$/', $schedule_id) === 1) {
	$schedule_id = $_POST['schedule_id'];
} else {
	t_error('Invalid Schedule ID: ' . $schedule_id);
}

$query = 
	"SELECT 
		c._id, 
		c.college_id, 
		c.course_code, 
		c.offered_online, 
		c.offered_oncampus, 
		c.start_date, 
		c.end_date, 
		c.min_credits, 
		c.max_credits, 
		c.monday, 
		c.tuesday, 
		c.wednesday, 
		c.thursday, 
		c.friday, 
		c.saturday, 
		c.sunday, 
		c.department_code, 
		c.section_code, 
		c.name, 
		LOWER(DATE_FORMAT(c.start_time, '%l:%i%p')) AS start_time, 
		LOWER(DATE_FORMAT(c.end_time, '%l:%i%p')) AS end_time 
	FROM Saved_Courses AS s 
	INNER JOIN Courses AS c 
	ON ( 
		s.schedule_id = '$schedule_id' 
		AND c._id = s.course_id 
	)";

ob_start();

echo "{\n";

$result = mysql_query($query);

if (!$result) {
	echo '"error": "';
	echo mysql_error();
	echo '",';
	echo '"query": "';
	echo $query;
	echo '",';
	echo '"params": ';
	echo json_encode($_POST);
} else  {
	echo "\"courses\": [\n";
	
	if (($count = mysql_num_rows($result)) > 0) {
		$i = 1;
		while ($course = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$materials = get_course_materials($course['_id']);
			if ($materials) {
				$course['materials'] = $materials;
			}
			
			echo json_encode($course);
			
			echo ($i++ < $count ? ",\n" : "\n");
		}
	}
	
	echo "]\n";
}

echo '}';

?>