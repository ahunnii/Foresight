<?php # Kyle L. Oswald 3/5/13

if (!isset($_SESSION)) {
	session_start();
}

include('../include/site_constants.inc.php');
if (!(isset($_SESSION['User_Permission']) 
	&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
	echo '0';
	exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . "../../Secure/error_reporting.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "../../Secure/db_connect.inc.php");

include('../include/simple_html_dom.inc.php');

$html = file_get_html('http://admissions.msu.edu/academics/majors_list.asp');

$row = $html->find('table tr', 1);

if ($row != null) {
	$result = mysql_query("SELECT _id FROM Colleges WHERE name = 'Michigan State University' LIMIT 0, 1;");
	$college_id = mysql_result($result, 0);
	mysql_free_result($result);
	
	$count = 0;
	$maj_count = 0;
	do {
		$degree_name = $row->children(0)->innertext;
		if (($ch = strpos($degree_name, '(')) != 0) {
			$degree_name = substr($degree_name, 0, $ch);
		}
		if (($ch = strpos($degree_name, '<')) != 0) {
			$degree_name = substr($degree_name, 0, $ch);
		}
		$degree_name = escape_data(
			trim($degree_name)
		);
		
		# Get major id from db
		$result = mysql_query(
			"SELECT _id 
			FROM Majors 
			WHERE ( 
				name LIKE '$degree_name' 
			) 
			LIMIT 0, 1;"
		);
		
		if (mysql_num_rows($result) == 0) {
			mysql_free_result($result);
			mysql_query(
				"INSERT INTO Majors (
					name 
				) 
				VALUES (
					'$degree_name' 
				);"
			);
			$major_id = mysql_insert_id();
			$maj_count++;
		} else {
			$major_id = mysql_result($result, 0);
			mysql_free_result($result);
		}
		
		$degree_code = $row->children(1)->innertext;
		$degree_code = escape_data($degree_code);
		
		$degree_level = $row->children(3)->innertext;
		switch ($degree_level) {
			case 'Undergraduate': # Bachelors
				$degree_level = 2;
				break;
			case 'Masters':
				$degree_level = 3;
				break;
			case 'Doctoral':
				$degree_level = 4;
				break;
			case 'GC': # Graduate Cert
				$degree_level = 5;
				break;
			default: # skip
				$row = $row->next_sibling();
				continue;
		}
		
		mysql_query(
			"INSERT INTO Offered_Degrees ( 
				college_id, 
				major_id, 
				level, 
				degree_code, 
				offered_online, 
				offered_oncampus 
			) 
			VALUES ( 
				$college_id, 
				$major_id, 
				$degree_level, 
				'$degree_code', 
				false, 
				true
			);"
		);
		
		echo "<p>Insert Success: ( $major_id, $degree_name, $degree_code, $degree_level )</p>\n";
		
		$count++;
		
		$row = $row->next_sibling();
	} while ($row != null);
	
	echo "<br/><h3>TOTAL DEGREE INSERTIONS: $count</h3>\n";
	echo "<h3>TOTAL MAJOR INSERTIONS: $maj_count</h3>";
}

?>