<?php # Kyle L. Oswald
# In case phpMyAdmin is innaccessible

if (!isset($_SESSION)) {
	session_start();
}

include('include/site_constants.inc.php');
if (!(isset($_SESSION['User_Permission']) 
	&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
	header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
}

$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];

require_once('include/misc_functions.inc.php');

$page_title = 'Mysql Interface';

$header_elements = array(
	'<link rel="stylesheet" type="text/css" href="style/mysql_interface_styles.css"/>',
	'<link rel="stylesheet" type="text/css" href="style/result_table_styles.css"/>'
);

include('include/masthead.inc.html');

$errors = array();
if (isset($_POST['submitted'])) {
	if (isset($_POST['query']) && !empty($_POST['query'])) {
		$query = $_POST['query'];
		
		$result = mysql_query($query);
		
		$err = mysql_error();
		if ($err) {
			$errors['query'] = 'MySql Error: ' . $err;
		} else if (is_resource($result)) {
			$query_result_count = mysql_num_rows($result);
		}
	} else {
		$errors['query'] = 'Empty query';
	}
}

?>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="submitted" value="1"/>
	<span>
	<p>Query:</p><p class="Error"><?php if (isset($errors['query'])) echo $errors['query']; ?></p>
	</span>
	<textarea id="QueryInput" name="query" rows="10"><?php if (isset($query)) echo $query; ?></textarea>
	
	<br/>
	
	<span>
	<input type="submit" value="Run"/>&nbsp;
	<input type="reset" value="Clear"/>
	</span>
</form>

<br/>

<?php 
if (isset($query_result_count) && $query_result_count) {
	echo "<div class=\"Results\">\n";
	echo "<table class=\"Results\">\n";
	
	echo "<tr>\n";
	for ($i = 0; $i < mysql_num_fields($result); $i++) {
		echo '<th>' . mysql_field_name($result, $i) . "</th>\n";
	}
	echo "</tr>\n";
	
	$i = 1;
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		echo '<tr class="' . ($i++ % 2 == 0 ? 'even' : 'odd') . "\">\n";
		
		foreach ($row as $value) {
			echo "<td>$value</td>\n";
		}
		
		echo "</tr>\n";
	}
	
	echo "</table>\n";
	echo "</div>\n";
}
?>

<?php 
include('include/footer.inc.html');
?>