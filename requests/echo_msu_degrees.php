<?php 

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

/*
$result = mysql_query(
	"SELECT _id 
	FROM Colleges 
	WHERE ( 
		name LIKE 'michigan state university' 
	);"
);

$_id = mysql_result($result, 0);
echo "<p>College _id: $_id</p>";

mysql_free_result($result);
*/

$result = mysql_query(
	"SELECT * 
	FROM Majors 
	ORDER BY name ASC;"
);

$count = mysql_num_rows($result);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	echo '<p>';
	echo var_dump($row);
	echo '</p>';
}

echo "<br/><h3>TOTAL ROWS: $count</h3><br/>";

$result = mysql_query(
	"SELECT * 
	FROM Offered_Degrees AS d 
	WHERE ( 
		d.college_id = ( 
			SELECT c._id 
			FROM Colleges AS c 
			WHERE ( 
				c.name = 'Michigan State University' 
			) 
			LIMIT 0, 1 
		) 
	);"
);

echo '<p>' . mysql_error() . '</p>';

$count = mysql_num_rows($result);
if ($count > 0) {
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo '<p>';
		echo var_dump($row);
		echo '</p>';
	}
}

echo "<br/><h3>TOTAL ROWS: $count</h3>";

?>