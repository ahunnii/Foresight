<?php # Kyle L. Oswald 3/2/13
# Pulls current average fuel price for each state from http://gasbuddy.com
# Echos 1 on success, 0 on failure

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

$html = file_get_html('http://gasbuddy.com/GB_Price_List.aspx');

$row = $html->find('div.PLinfo', 0)->find('table tr', 1);

do {
	$state_name = $row->children(0)->children(0)->innertext;
	$price = $row->children(1)->innertext;
	
	mysql_query(
		"UPDATE States 
		SET avg_gas_price = $price 
		WHERE name LIKE '$state_name';"
	);
	
	$row = $row->next_sibling();
} while ($row != null);

echo '1';

?>