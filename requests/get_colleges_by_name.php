<?php # Kyle L. Oswald 02/14/13

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/error_reporting.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/db_connect.inc.php");

echo "{\r\n";

if (isset($_GET['college_name'])) {

	$college_name = $_GET['college_name'];
	
	if (isset($_GET['extended_only']) && $_GET['extended_only'] == 1)
		$extended_only = 1;
	else 
		$extended_only = 0;
	
	echo '"college_name"' . ": \"$college_name\",\r\n";
	
	if (!empty($college_name)) {
		echo '"suggestions"' . ": [\r\n";
		
		$college_name = escape_data($college_name);
		$result = mysql_query(
			"SELECT _id, name 
			FROM Colleges 
			WHERE ( 
				name LIKE '$college_name%' 
				AND extended_info >= $extended_only 
			)
			ORDER BY CHAR_LENGTH(name) ASC 
			LIMIT 0, 50;"
		);
		
		$count = mysql_num_rows($result);
		
		if ($count > 0) {
			$i = 1;
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				echo '{ "value": "' . $row[1] . '", "data": ' . $row[0] . ' }';
				echo $i++ < $count ? ",\r\n" : "\r\n";
			}
		}
		
		mysql_free_result($result);
		
		mysql_close();
		
		echo "]\r\n";
	}
}

echo '}';

?>