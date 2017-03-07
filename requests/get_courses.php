<?php # Kyle L. Oswald

$suppress_error_redirect = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "../../Secure/error_reporting.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '../../Secure/db_connect.inc.php');

include('amazon_book_info.php');

header('Content-type: application/json');

function fail() {
	exit();
}

$where = ''; # where clause

if (isset($_POST['college']) && 
	ereg('^[0-9]+$', $_POST['college'])) {
	$where = 'college_id = ' . $_POST['college'];
} else {
	fail();
}

if (isset($_POST['dept'])) {
	$dept = $_POST['dept'];
	if (ereg('^[0-9a-zA-Z]+$', $dept)) {
		$where .= " AND department_code LIKE '$dept' ";
	} else {
		fail();
	}
}

# TODO
if (isset($_POST['degree'])) {

} 

if (isset($_POST['course_num']) && 
	ereg('^[0-9a-zA-Z]+$', $_POST['course_num'])) {
	$where .= ' AND course_code = ' . $_POST['course_num'] . ' ';
}

$on_days = '';
$off_days = '';

if (isset($_POST['su'])) {
	$on_days .= 'OR sunday = true ';
} else {
	$off_days .= 'AND sunday = false ';
}

if (isset($_POST['m'])) {
	$on_days .= 'OR monday = true ';
} else {
	$off_days .= 'AND monday = false ';
}

if (isset($_POST['tu'])) {
	$on_days .= 'OR tuesday = true ';
} else {
	$off_days .= 'AND tuesday = false ';
}

if (isset($_POST['w'])) {
	$on_days .= 'OR wednesday = true ';
} else {
	$off_days .= 'AND wednesday = false ';
}

if (isset($_POST['th'])) {
	$on_days .= 'OR thursday = true ';
} else {
	$off_days .= 'AND thursday = false ';
}

if (isset($_POST['f'])) {
	$on_days .= 'OR friday = true ';
} else {
	$off_days .= 'AND friday = false ';
}

if (isset($_POST['sa'])) {
	$on_days .= 'OR saturday = true ';
} else {
	$off_days .= 'AND saturday = false ';
}

if ($on_days) {
	$where .= ' AND ( ' . substr($on_days, 2) . ' ) ';
}

if ($off_days) {
	$where .= ' AND ( ' . substr($off_days, 3) . ' ) ';
}

if (isset($_POST['start_time'])) {
	$where .= ' AND start_time >= \'' . escape_data($_POST['start_time']) . '\' ';
}

if (isset($_POST['end_time'])) {
	$where .= ' AND end_time <= \'' . escape_data($_POST['end_time']) . '\' ';
}

if (isset($_POST['oncampus'])) {
	if (isset($_POST['online'])) {
		$where .= ' AND ( offered_oncampus = true OR offered_online = true ) ';
	} else {
		$where .= ' AND offered_oncampus = true ';
	}
} else if (isset($_POST['online'])) {
	$where .= ' AND offered_online = true ';
}

if (isset($_POST['min_credits']) && 
	ereg('^[0-9]{1,2}$', $_POST['min_credits'])) {
	$where .= ' AND min_credits >= ' . $_POST['min_credits'];
}

if (isset($_POST['max_credits']) && 
	ereg('^[0-9]{1,2}$', $_POST['max_credits'])) {
	$where .= ' AND max_credits <= ' . $_POST['max_credits'];
}

$query = 
	'SELECT 
		_id, 
		college_id, 
		course_code, 
		offered_online, 
		offered_oncampus, 
		start_date, 
		end_date, 
		min_credits, 
		max_credits, 
		monday, 
		tuesday, 
		wednesday, 
		thursday, 
		friday, 
		saturday, 
		sunday, 
		department_code, 
		section_code, 
		name, 
		LOWER(DATE_FORMAT(start_time, \'%l:%i%p\')) AS start_time, 
		LOWER(DATE_FORMAT(end_time, \'%l:%i%p\')) AS end_time 
	FROM Courses ' . 
	'WHERE ' . $where . 
	' LIMIT 0, 100';

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

ob_end_flush();

?>