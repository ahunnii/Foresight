<?php 
	$page_title = 'Browse Scholarships';
	$header_elements = array('<link rel="stylesheet" type="text/css" href="style/browse_styles.css"/>',
		'<script type="text/javascript" src="JavaScript/ComboBox.js"></script>');
	include('include/masthead.inc.html');
	
	include('include/pagination.inc.php');
	
	$default_params = array(
		'start_index' => 0, 
		'count' => 20);
	
	# Union of arguments with defaults to
	# ensure all variables are defined.
	$args = $_REQUEST + $default_params;
?>
<div id="Wrapper">
	
	<div id="BrowseConfig">
		<form method="get" action="browse_colleges.php">
			<span>
			<input type="submit" value="Go"/>&nbsp;
			<p>Results: <?php echo $total_results; ?></p>
			</span>
			
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
			
			<span>
			<input type="checkbox" name="extended_only" <?php if ($extended_only) echo 'checked '; ?>value="1"/>&nbsp;
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
					
					echo "<td colspan=\"3\"><span><p>{$row['name']}</p></span><span><p>{$row['city']}, {$row['abbreviation']}</p></span></td>\n";
					echo '<td>';
					
					if (is_college_saved($row['_id'])) {
						echo '<span><p>Saved</p></span>';
					} else {
						echo "<span><a onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/button_pressed.jpg')\" href=\"action_save_college.php?college_id={$row['_id']}\"><img src=\"Graphics/button.jpg\">Save</img></a></span>";
					}
					
					echo "<span><a onMouseOut=\"MM_swapImgRestore()\" onMouseOver=\"MM_aswapImage(this, '', 'Graphics/button_pressed.jpg')\"><img src=\"Graphics/button.jpg\">View</img></a></span></td></tr>\n";
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
	include('include/footer.inc.html');
?>