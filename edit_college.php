<?php # Kyle L. Oswald 10/21/12
	if (!isset($_SESSION)) {
		session_start();
	}
	
	include('include/site_constants.inc.php');
	if (!(isset($_SESSION['User_Permission']) 
		&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
	}
	
	$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];
	
	$page_title = 'Edit College';
	
	# Create onLoad script
	$startup_script = 
<<<EOS
<script type="text/javascript">
	function onPageLoaded() {
		initListTables();
		
		setRowClickListener('ImgTable', function (row) {
			var filePath = row.getAttribute('imgPath');
			document.getElementById('ImgPreview').src = filePath;
		});
	}
	
	function removeImage_Click(a) {
		var value = getListTableValue('ImgTable');
		if (value) {
			a.href = 'action_delete_image.php?record_id=' + value;
			return true;
		} else {
			return false;
		}
	}
	
	function editImage_Click(a) {
		var value = getListTableValue('ImgTable');
		if (value) {
			a.href = 'action_edit_image.php?record_id=' + value;
			return true;
		} else {
			return false;
		}
	}
	
	function removeDegree_Click(a) {
		var value = getListTableValue('DegTable');
		if (value) {
			a.href = 'action_remove_degree.php?record_id=' + value;
			return true;
		} else {
			return false;
		}
	}
	
	function editDegree_Click(a) {
		var value = getListTableValue('DegTable');
		if (value) {
			a.href = 'action_edit_degree.php?record_id=' + value;
			return true;
		} else {
			return false;
		}
	}
</script>
EOS;
	
	$page_styles = 
<<<EOS
<style type="text/css">
	div#ImgDiv {
	float: left;
	}

	div#PreviewDiv {
	border-style: solid;
	border-color: #999999;
	border-width: 2px;
	float: left;
	margin-left: 15px;
	}

	div#PreviewDiv img {
	max-width: 200px;
	max-height: 200px;
	width: auto;
	height: auto;
	}

	div#DegreeDiv {
	padding-top: 15px;
	clear: left;
	}
</style>
EOS;

	$header_elements = array(
		'<script type="text/javascript" src="JavaScript/TableSelect.js"></script>',
		'<script type="text/javascript" src="JavaScript/ListTables.js"></script>',
		$startup_script,
		'<script type="text/javascript" src="JavaScript/cookies.js"></script>',
		$page_styles);
	$on_page_load = 'onPageLoaded()';
	include('include/masthead.inc.html');
	
	$errors = array();
	if (isset($_POST['submitted'])) {
		$college_id = $_POST['id'];
		$college_name = $_POST['college_name'];
		$college_address = $_POST['college_address'];
		$college_phone = $_POST['college_phone'];
		$college_website = $_POST['college_website'];
		$college_city = $_POST['college_city'];
		$college_zip = $_POST['college_zip'];
		$college_state_id = $_POST['college_state_id'];
		
		if (isset($_POST['college_ext_info'])) {
			$college_ext_info = $_POST['college_ext_info'] ? 1 : 0;
		} else {
			$college_ext_info = 0;
		}
		
		if (isset($_POST['instate_tuition']) && !empty($_POST['instate_tuition'])) {
			$instate_tuition = $_POST['instate_tuition'];
			
			if (!ereg('^[0-9]+$', $instate_tuition)) {
				$errors['instate_tuition'] = 'Value must be an integer';
			}
		} else {
			$instate_tuition = null;
		}
		
		if (isset($_POST['outstate_tuition']) && !empty($_POST['outstate_tuition'])) {
			$outstate_tuition = $_POST['outstate_tuition'];
			
			if (!ereg('^[0-9]+$', $outstate_tuition)) {
				$errors['outstate_tuition'] = 'Value must be an integer';
			}
		} else {
			$outstate_tuition = null;
		}
		
		if (isset($_POST['dorm_cost']) && !empty($_POST['dorm_cost'])) {
			$dorm_cost = $_POST['dorm_cost'];
			
			if (!ereg('^[0-9]+$', $dorm_cost)) {
				$errors['dorm_cost'] = 'Value must be an integer';
			}
		} else {
			$dorm_cost = null;
		}
		
		if (isset($_POST['avg_act']) && !empty($_POST['avg_act'])) {
			$avg_act = $_POST['avg_act'];
			
			if (!ereg('^[0-9]{1,2}$', $avg_act)) {
				$errors['avg_act'] = 'Value must be a positive integer (1-36)';
			} else if ($avg_act > 36 || $avg_act == 0) {
				$errors['avg_act'] = 'Value must be within the range 1-36 (inclusive)';
			}
		} else {
			$avg_act = null;
		}
		
		if (isset($_POST['avg_gpa']) && !empty($_POST['avg_gpa'])) {
			$avg_gpa = $_POST['avg_gpa'];
			
			if (!ereg('^[0-9]\.[0-9]$', $avg_gpa)) {
				$errors['avg_gpa'] = 'Value must be a positive single precision decimal, Ex 4.0, 3.2, 0.8, etc.';
			}
		} else {
			$avg_gpa = null;
		}
		
		# Validate
		if (empty($college_name)) {
			$errors['college_name'] = 'This field is required';
		}
		
		if (empty($college_address)) {
			$errors['college_address'] = 'This field is required';
		}
		
		if (empty($college_city)) {
			$errors['college_city'] = 'This field is required';
		}
		
		if (empty($college_zip)) {
			$errors['college_zip'] = 'This field is required';
		} else if (!ereg('^[0-9]{3,10}$', $college_zip)) {
			$errors['college_zip'] = 'Invalid zipcode';
		} 
		
		if (!empty($college_phone) && !ereg('^([0-9]-)?([0-9]{3}-){2}[0-9]{4}$', $college_phone)) {
			$errors['college_phone'] = 'This is not a valid phone number';
		}
		
		if (isset($_POST['twitter_feed'])) {
			$twitter_feed = $_POST['twitter_feed'];
		} else {
			$twitter_feed = null;
		}
		
		# Update
		if (empty($errors)) {
			if (!@mysql_query(
				"UPDATE Colleges 
				SET name='". escape_data($college_name) . "',
					address='" . escape_data($college_address) . "',
					phone='" . escape_data($college_phone) . "',
					website='" . escape_data($college_website) . "',
					city='" . escape_data($college_city) . "',
					zipcode=$college_zip,
					state_id=$college_state_id,
					extended_info=$college_ext_info, 
					instate_tuition=" . ($instate_tuition === null ? 'NULL' : $instate_tuition) . ", 
					outstate_tuition=" . ($outstate_tuition === null ? 'NULL' : $outstate_tuition) . ", 
					dorm_cost=" . ($dorm_cost === null ? 'NULL' : $dorm_cost) . ", 
					avg_act=" . ($avg_act === null ? 'NULL' : $avg_act) . ", 
					avg_gpa=" . ($avg_gpa === null ? 'NULL' : $avg_gpa) . ", 
					twitter_feed='" . ($twitter_feed === null ? 'NULL' : escape_data($twitter_feed)) . "' 
				WHERE _id=$college_id;"
			)) {
				$errors['main'] = 'SQL error on update';
				error_log($log_email, 1, mysql_error());
			}
		}
	} else {
		if (isset($_GET['id']) && is_numeric($_GET['id'])) { # Load from db
			$college_id = $_GET['id'];
			
			$result = mysql_query("SELECT * FROM Colleges WHERE _id=$college_id;");
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			
			$college_name = $row['name'];
			$college_address = $row['address'];
			$college_phone = $row['phone'];
			$college_website = $row['website'];
			$college_city = $row['city'];
			$college_zip = $row['zipcode'];
			$college_state_id = $row['state_id'];
			$college_ext_info = $row['extended_info'];
			$instate_tuition = $row['instate_tuition'];
			$outstate_tuition = $row['outstate_tuition'];
			$dorm_cost = $row['dorm_cost'];
			$avg_act = $row['avg_act'];
			$avg_gpa = $row['avg_gpa'];
			$twitter_feed = $row['twitter_feed'];
			
			mysql_free_result($result);
		} else { # Non-existant id
			echo '<div><h2>Invalid ID</h2></div>';
			include('include/footer.inc.html');
			mysql_close();
			exit();
		}
	}
	
?>
	<h4 style="color: black;">College Information</h4>
	<br/>
	<p class="Error"><?php if (isset($errors['main'])) echo $errors['main']; ?></p>
	<form class="Formtable" action="edit_college.php" method="post">
		<input type="hidden" name="submitted" value="1"/>
		<input type="hidden" name="id" value="<?php echo $college_id; ?>"/>
		<table>
			<tr class="Formrow">
				<td>Name: </td>
				<td><input type="text" name="college_name" maxlength="255" value="<?php echo $college_name; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_name'])) echo $errors['college_name']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Address: </td>
				<td><input type="text" name="college_address" maxlength="255" value="<?php echo $college_address; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_address'])) echo $errors['college_address']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>City: </td>
				<td><input type="text" name="college_city" maxlength="255" value="<?php echo $college_city; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_city'])) echo $errors['college_city']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Zipcode: </td>
				<td><input type="text" name="college_zip" maxlength="10" value="<?php echo $college_zip; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_zip'])) echo $errors['college_zip']; ?></td>
			</tr>
			<tr class="Formrow">
				<input id="state_input" type="hidden" name="college_state_id" value="<?php echo $college_state_id; ?>"/>
				<td>State: </td>
				<td>
					<select id="state_selector" onChange="onOptionSelected(this, 'state_input')">
						<?php # Enumerate through states to populate option tags
							$result = mysql_query('SELECT _id, name FROM States ORDER BY name');
							
							$state_count = mysql_num_rows($result);
							if ($college_state_id > $state_count || $college_state_id <= 0)
								$college_state_id = 1;
								
							while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
								echo "<option value=\"$row[0]\"";
								if ($college_state_id == $row[0])
									echo ' selected';
								echo ">$row[1]</option>";
							}
							
							mysql_free_result($result);
						?>
					</select>
				</td>
			</tr>
			<tr class="Formrow">
				<td>Phone: </td>
				<td><input type="text" name="college_phone" maxlength="14" value="<?php if (!empty($college_phone)) echo $college_phone; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_phone'])) echo $errors['college_phone']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Website: </td>
				<td><input type="text" name="college_website" maxlength="255" value="<?php if (!empty($college_website)) echo $college_website; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['college_website'])) echo $errors['college_website']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>In-State Tuition: </td>
				<td><input type="text" name="instate_tuition" maxlength="10" value="<?php if (!empty($instate_tuition)) echo $instate_tuition; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['instate_tuition'])) echo $errors['instate_tuition']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Out-of-State Tuition: </td>
				<td><input type="text" name="outstate_tuition" maxlength="10" value="<?php if (!empty($outstate_tuition)) echo $outstate_tuition; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['outstate_tuition'])) echo $errors['outstate_tuition']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Dorm Cost: </td>
				<td><input type="text" name="dorm_cost" maxlength="10" value="<?php if (!empty($dorm_cost)) echo $dorm_cost; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['dorm_cost'])) echo $errors['dorm_cost']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Average ACT: </td>
				<td><input type="text" name="avg_act" maxlength="10" value="<?php if (!empty($avg_act)) echo $avg_act; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['avg_act'])) echo $errors['avg_act']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Average GPA: </td>
				<td><input type="text" name="avg_gpa" maxlength="10" value="<?php if (!empty($avg_gpa)) echo $avg_gpa; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['avg_gpa'])) echo $errors['avg_gpa']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Twitte Feed: </td>
				<td><input type="text" name="twitter_feed" maxlength="100" value="<?php if (!empty($twitter_feed)) echo $twitter_feed; ?>"/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['twitter_feed'])) echo $errors['twitter_feed']; ?></td>
			</tr>
			<tr class="Formrow">
				<td>Extended Info:</td>
				<td><input type="checkbox" name="college_ext_info" value="1" <?php if ($college_ext_info) echo 'checked'; ?>/></td>
			</tr>
			<tr class="Formrow">
				<td><input type="submit" value="Save"/></td>
			</tr>
		</table>
	</form>

	<br/>

	<h4>Images</h4>
	<br/>
	<div>
		<!-- Table Div -->
		<div id="ImgDiv" class="ListTable">
			<table id="ImgTable" class="ListTable">
				<?php 
					$result = mysql_query("SELECT _id, description, filename FROM Campus_Images WHERE college_id=$college_id LIMIT 0, 100;");
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						$file_path = DIR_CAMPUS_IMAGES . $row[2] . '.jpg';
						echo "<tr rowValue=\"$row[0]\" imgPath=\"$file_path\"><td>$row[1]</td></tr>";
					}
					mysql_free_result($result);
				?>
			</table>
			<br/>
			<span>
				<a href="action_new_image.php?college_id=<?php echo $college_id; ?>">Add</a>&nbsp;
				<a href="#" onClick="removeImage_Click(this)">Remove</a>&nbsp;
				<a href="#" onClick="editImage_Click(this)">Edit</a>
			</span>
		</div>
		
		<!-- Preview Div -->
		<div id="PreviewDiv">
			<img id="ImgPreview" alt="Preview"/>
		</div>
	</div>

	<br/>

	<div id="DegreeDiv">
		<h4>Degrees</h4>
		<br/>
		<table id="DegTable" class="ListTable">
			<tr>
				<th>Major</th>
				<th>On Campus</th>
				<th>Online</th>
				<th>Degree</th>
			</tr>
			<?php 
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
				
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<tr rowValue=\"$row[5]\"><td>$row[1]</td><td>";
						echo $row[2] ? 'Yes' : 'No';
						echo '</td><td>';
						echo $row[3] ? 'Yes' : 'No';
						echo "</td><td>$row[4]</td></tr>\n";
					}
					mysql_free_result($result);
				}
			?>
		</table>
		<br/>
		<span>
			<a href="action_new_degree.php?college_id=<?php echo $college_id; ?>">Add</a>&nbsp;
			<a href="#" onClick="removeDegree_Click(this)">Remove</a>&nbsp;
			<a href="#" onClick="editDegree_Click(this)">Edit</a>
		</span>
	</div>

<?php 
	mysql_close();
	include('include/footer.inc.html');
?>