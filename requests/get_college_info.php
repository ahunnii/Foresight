<?php # Kyle L. Oswald 02/19/13

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/error_reporting.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/db_connect.inc.php");

if (isset($_GET['college_name'])) {

	$college_name = $_GET['college_name'];
	
	if (!empty($college_name)) {
		
		$college_name = escape_data($college_name);
		$result = mysql_query(
			"SELECT c.*, s.abbreviation AS state_abbreviation, 
				s.name AS state_name, s.avg_gas_price AS state_avg_gas_price 
			FROM Colleges AS c 
			INNER JOIN States s 
			ON ( 
				c.name LIKE '$college_name' 
				AND c.extended_info = TRUE 
				AND s._id = c.state_id 
			) 
			LIMIT 0, 1;"
		);
		
		$count = mysql_num_rows($result);
		
		if ($count > 0) {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			
			$college_id = $row['_id'];
			
			$json = json_encode($row);
			
			echo "{\n\"collegeInfo\": $json,\n";
			
			mysql_free_result($result);
			
			# Retrieve Offered Degrees
			$result = mysql_query(
				"SELECT off.major_id, maj.name, off.offered_oncampus, 
					off.offered_online, lev.name AS level, off._id 
				FROM Offered_Degrees AS off 
				INNER JOIN Majors AS maj 
				ON ( off.college_id=$college_id 
					AND maj._id=off.major_id ) 
				INNER JOIN Degree_Levels AS lev 
				ON ( off.level=lev._id ) 
				ORDER BY off.major_id, off.level ASC;");
			
			echo "\"offeredDegrees\": [\n";
			
			if (($count = mysql_num_rows($result)) > 0) {
				$i = 1;
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					echo json_encode($row);
					if ($i++ < $count) {
						echo ",\n";
					} else {
						echo "\n";
					}
				}
			}
			
			echo "]\n";
				
			echo '}';
		} else {
			echo '-1';
		}
		
		mysql_free_result($result);
		
		mysql_close();
	} else {
		echo '-1';
	}
}

?>