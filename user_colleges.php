<?php
	$page_title = 'My Colleges';
	
	$body_loaded_script =  <<<____EOS
	<script type="text/javascript">
		function onBodyLoaded() {
			MM_preloadImages('Graphics/Buttons/button_remove_h.png', 'Graphics/Buttons/button_view_h.png');
		}
	</script>
____EOS;

	$header_elements = array('<link rel="stylesheet" type="text/css" href="style/browse_styles.css"></link>');
	include ('include/masthead.inc.html');
	
	include('include/misc_functions.inc.php');
	
	include('include/pagination.inc.php');
	
	if (!isset($_SESSION['User_Id'])) {
		echo '<h4>You must be logged in to view this page</h4>';
		include('include/footer.inc.html');
		exit();	
	}
	
	function create_uri() {
		global $start_index;
		$uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?start_index=' . PAGINATOR_INDEX;
		#"&count=$count&state=$state_id&extended_only=$extended_only";
		return $uri;
	}
	
	$records_per_page = 4;
	
	$result = mysql_query(
		"SELECT COUNT(*) 
		FROM Saved_Colleges 
		WHERE user_id = {$_SESSION['User_Id']};");
		
	$record_count = mysql_fetch_array($result, MYSQL_NUM);
	$record_count = $record_count[0];
	
	$total_pages = $record_count / $records_per_page;
	$total_pages = ceil("$total_pages"); # string conversion to fix ceil() issue
	
	$start_index = isset($_GET['start_index']) ? $_GET['start_index'] : 0;
	
	$current_page = floor($start_index / $records_per_page);
	
	mysql_free_result($result);
	
	$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];
	
?>


<html>
<h1> These are your colleges:</h1>

<br/>

<table class="ResultTable">
	<?php
		$result = @mysql_query(
			"SELECT c.*, st.abbreviation 
			FROM Saved_Colleges s 
			INNER JOIN Colleges c 
			ON (
				s.user_id = {$_SESSION['User_Id']} 
				AND  
				s.college_id = c._id 
			) 
			LEFT JOIN States st
			ON ( 
				c.state_id = st._id
			) 
			ORDER BY c.name 
			LIMIT $start_index, $records_per_page;");
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			echo "<tr class=\"SearchElement\">";
			echo "<td><a href=\"edit_course_schedule.php?college_id={$row['_id']}\">Course Schedule</a></td>\n";
			echo "<td colspan=\"3\"><span><p>{$ellipsize($row['name'], 50)}</p></span><span><p>{$row['city']}, {$row['abbreviation']}</p></span></td>\n";
			echo '<td>';
			echo "<span><a href=\"view_college.php?college_id={$row['_id']}&college_name={$row['name']}\" onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/Buttons/button_view_h.png')\"><img src=\"Graphics/Buttons/button_view.png\"/></a></span>\n";
			echo "<span><a href=\"action_remove_college.php?college_id={$row['_id']}\" onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/Buttons/button_remove_h.png')\"><img src=\"Graphics/Buttons/button_remove.png\"/></a></span>\n";
			echo '</td></tr>';
		}
		
		mysql_free_result($result);
		
		echo '</table>';
		
		# ==== Create page navigation ====
		
		echo '<br/><br/>';
		
		print_pagination($current_page, $records_per_page, $total_pages, 5, create_uri());
		
		echo '<br/>';
	?>
</html>


	
<?php
	include('include/footer.inc.html');
?>