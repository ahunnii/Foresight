<?php # Kyle L. Oswald 10/15/12
	include('include/site_constants.inc.php');
	
	$page_title = 'Browse Colleges';
	
	$body_loaded_script =  <<<EOS
	<script type="text/javascript">
		function onBodyLoaded() {
			MM_preloadImages('Graphics/Buttons/button_saved_h.png', 
				'Graphics/Buttons/button_save_h.png', 'Graphics/Buttons/button_view_h.png');
		}
		
		var autoParams = { extended_only: 1 };
		
		function onPageReady() {
			$('#college_name_input').autocomplete({
				serviceUrl: '/requests/get_colleges_by_name.php',
				params: autoParams,
				paramName: 'college_name',
				appendTo: $('#BrowseConfig .suggestion-div'),
				minChars: 3,
				maxHeight: 200
			});
			
			$('#extended_input').click(isExtendedOnly);
			
			isExtendedOnly();
		}
		
		function isExtendedOnly() {
			if ($(this).attr('checked')) 
				autoParams.extended_only = 1;
			else 
				autoParams.extended_only = 0;
		}
	</script>
EOS;
	$header_elements = array(
		'<link rel="stylesheet" type="text/css" href="style/autocomplete.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/browse_styles.css"/>',
		'<script type="text/javascript" src="JavaScript/autocomplete.js"></script>',
		'<script type="text/javascript" src="JavaScript/ComboBox.js"></script>',
		$body_loaded_script);
	$on_page_load = 'onBodyLoaded()';
	$on_page_ready = 'onPageReady()';
	include('include/masthead.inc.html');
	
	
	include('include/pagination.inc.php');
	
	function create_uri() {
		global $start_index, $count, $state_id, $extended_only;
		$uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?start_index=' . PAGINATOR_INDEX  . '&' .
			http_build_query(array('count' => $count, 'state' => $state_id, 'extended_only' => $extended_only));
		#"&count=$count&state=$state_id&extended_only=$extended_only";
		return $uri;
	}
	
	function is_college_saved($college_id) {
		if (isset($_SESSION['User_Id'])) {
			$result = mysql_query("SELECT COUNT(*) FROM Saved_Colleges WHERE user_id={$_SESSION['User_Id']} AND college_id=$college_id;");
			$row = mysql_fetch_array($result, MYSQL_NUM);
			mysql_free_result($result);
			return $row[0] >= 1;
		} else {
			return false;
		}
	}
	
	# Set the return url for php action scripts
	if (isset($_SESSION['User_Id'])) {
		$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];
		
		# Display admin utilities
		$is_admin = $_SESSION['User_Permission'] == PERMISSION_ADMIN;
	} else {
		$is_admin = false;
	}
	
	# Instantiate $_GET Parameters
	if (!empty($_GET['start_index']) && is_numeric($_GET['start_index']))
		$start_index = $_GET['start_index'];
	else 
		$start_index = 0;
		
	if (!empty($_GET['count']) && is_numeric($_GET['count'])) {
		$count = $_GET['count'];
		
		# Check for erroneous input
		if ($count > 100)
			$count = 100;
	} else 
		$count = 20;
		
	if (isset($_GET['name']) && !empty($_GET['name'])) 
		$name = $_GET['name'];
	else 
		$name = null;
		
	if (!empty($_GET['state']) && is_numeric($_GET['state']))
		$state_id = $_GET['state'];
	else 
		$state_id = 0;
		
	if (isset($_GET['extended_only']))
		$extended_only = $_GET['extended_only'];
	else 
		$extended_only = false;


	$where = '';
	if ($state_id) 
		$where .= "AND c.state_id=$state_id ";
		
	if ($extended_only)
		$where .= 'AND c.extended_info=true ';
		
	if ($name)
		$where .= "AND c.name LIKE '" . escape_data($name) . "%' ";
	

	$query = 'SELECT COUNT(*) FROM Colleges AS c ';
	
	# Remove first 'AND ';
	if (!empty($where)) {
		$query .= 'WHERE ( ' . substr($where, 4) . ' ) ';
	}
	
	$result = mysql_query($query . $where . ';');
	$row = mysql_fetch_array($result, MYSQL_NUM);
	
	$total_results = $row[0];
	$pages = $total_results / $count;
	$pages = ceil("$pages"); # string conversion to fix ceil() issue
	
	$current_page = floor($start_index / $count);
	
	mysql_free_result($result);
	
	include('include/misc_functions.inc.php');
?>
	<div id="Wrapper">
	
	<div id="BrowseConfig">
		<form method="get" action="browse_colleges.php">
			<span>
			<input type="submit" value="Go"/>&nbsp;
			<p>Results: <?php echo $total_results; ?></p>
			</span>
			
			<br/>
			
			<p>Name</p>
			<input type="text" id="college_name_input" name="name" size="25" maxLength="80" title="College or University" <?php if ($name) echo "value=\"$name\""; ?>/>
			<div class="suggestion-div"></div>
			
			<br/>
			
			<input type="hidden" id="state_input" name="state" value="<?php echo $state_id; ?>"/>
			<p>State</p>
			<select id="state_selector" onChange="onOptionSelected(this, 'state_input')">
				<?php # Enumerate through states to populate option tags
					$result = mysql_query('SELECT _id, name FROM States ORDER BY name');
					
					$state_count = mysql_num_rows($result);
					if ($state_id > $state_count || $state_id < 0)
						$state_id = 0;
						
					echo '<option value="0"';
					if ($state_id == 0)
						echo ' selected';
					echo '>ALL</option>';
					
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<option value=\"$row[0]\"";
						if ($state_id == $row[0])
							echo ' selected';
						echo ">$row[1]</option>";
					}
				?>
			</select>
			
			<br/>
			
			<span>
			<input id="extended_input" type="checkbox" name="extended_only" <?php if ($extended_only) echo 'checked '; ?>value="1"/>&nbsp;
			<p>Extensive Info.</p>
			</span>
		</form>
	</div>
	
	<div id="BrowseWindow">
		
		<table class="ResultTable">
			<?php 
				$query = 
					'SELECT c._id, c.city, c.name, s.abbreviation 
					FROM Colleges AS c INNER JOIN States AS s 
					ON c.state_id=s._id ';
				
				$query .= $where . "LIMIT $start_index, $count;";
				
				$result = mysql_query($query);
				
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					echo "<tr class=\"SearchElement\">";
					
					if ($is_admin) {
						echo "<td><a href=\"edit_college.php?id={$row['_id']}\">Edit</a></td>";
					}
					
					echo "<td colspan=\"3\"><span><p>{$ellipsize($row['name'], 45)}</p></span><span><p>{$row['city']}, {$row['abbreviation']}</p></span></td>\n";
					echo '<td>';
					
					if (is_college_saved($row['_id'])) {
						echo '<span><img alt="Saved" src="Graphics/Buttons/button_saved.png"/></span>';
					} else {
						echo "<span><a href=\"action_save_college.php?college_id={$row['_id']}\" onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/Buttons/button_save_h.png')\"><img src=\"Graphics/Buttons/button_save.png\"/></a></span>";
					}
					
					echo "<span><a href=\"view_college.php?college_id={$row['_id']}&college_name={$row['name']}\" onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/Buttons/button_view_h.png')\"><img src=\"Graphics/Buttons/button_view.png\"/></a></span></td></tr>\n";
				}
				
				mysql_free_result($result);
				
				echo "</table>\n";
				
				# ==== Create page navigation ====
				
				echo '<br/><br/>';
				
				print_pagination($current_page, $count, $pages, 5, create_uri());
				
				echo '<br/>';
			?>
		
	</div>
	
	</div> <!-- End Wrapper Div -->
<?php 
	mysql_close();
	include('include/footer.inc.html');
?>