<?php # Kyle L. Oswald 10/22/12
	if (!$_SESSION) {
		session_start();
	}
	
	include('include/site_constants.inc.php');
	
	# Verify permissions
	if (!(isset($_SESSION['User_Permission']) 
		&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
	}
	
	$page_title = 'Degree Program';
	$header_elements = array('<script type="text/javascript" src="JavaScript/TableSelect.js"></script>',
		'<script type="text/javascript" src="JavaScript/ComboBox.js"></script>');
	include('include/masthead.inc.html');
	
	$college_id = 0;
	
	$errors = array();
	if (isset($_SESSION['action'])) {
		switch ($_SESSION['action']) {
			case ACTION_NEW:
				if (isset($_GET['college_id'])) {
					$college_id = $_GET['college_id'];
				} else {
					echo '<h5 class="Error">Missing required parameter</h5>';
					include('include/footer.inc.html');
					mysql_close();
					exit();
				}
				
				$_SESSION['action'] = ACTION_INSERT;
				
				$record_id = 0;
				$major_id = 0;
				$degree_level = 1;
				$oncampus = 1;
				$online = 0;
				$major_name = '';
				break;
			case ACTION_EDIT:
				if (isset($_GET['record_id'])) {
					$record_id = $_GET['record_id'];
				} else {
					echo '<h5 class="Error">Missing required parameter</h5>';
					include('include/footer.inc.html');
					mysql_close();
					exit();
				}
				
				$_SESSION['action'] = ACTION_UPDATE;
				
				# Retrieve record
				$result = @mysql_query("SELECT * FROM Offered_Degrees WHERE _id=$record_id;");
				if ($result) {
					if (mysql_num_rows($result) >= 1) {
						$row = mysql_fetch_array($result, MYSQL_ASSOC);
						$major_id = $row['major_id'];
						$degree_level = $row['level'];
						$oncampus = $row['offered_oncampus'];
						$online = $row['offered_online'];
						$major_name = '';
						$college_id = $row['college_id'];
						
						mysql_free_result($result);
					} else {
						echo '<h5 class="Error">Record Does Not Exist</h5>';
						include('include/footer.inc.html');
						mysql_close();
						exit();
					}
				} else {
					echo '<h5 class="Error">SQL Error</h5>';
					include('include/footer.inc.html');
					mysql_close();
					exit();
				}
				break;
			case ACTION_INSERT:
				if (isset($_POST['college_id'])) {
					$college_id = $_POST['college_id'];
				} else {
					echo '<h5 class="Error">Missing required parameter</h5>';
					include('include/footer.inc.html');
					mysql_close();
					exit();
				}
				
				if ($_POST['existing']) {
					$major_id = $_POST['major_id'];
					$major_name = '';
				} else {
					$major_id = 0;
					$major_name = $_POST['major_name'];
					if (!ereg('^[a-zA-Z]+( [a-zA-Z]+)*$', $major_name)) {
						$errors['major_name'] = 'Major name must contain only letters';
					} else if (strlen($major_name) > 50) {
						$errors['major_name'] = 'Major cannot contain over 50 characters';
					} else { # Set major_id = major_name if it exists, or insert
						$result = @mysql_query("SELECT _id FROM Majors WHERE name='$major_name';");
						if ($result) {
							if (mysql_num_rows($result) >= 1) {
								$row = mysql_fetch_array($result, MYSQL_NUM);
								$major_id = $row[0];
								mysql_free_result($result);
							} else {
								mysql_free_result($result);
								$result = mysql_query("INSERT INTO Majors ( name ) VALUES ( '$major_name' );");
								$major_id = mysql_insert_id();
							}
						} else {
							error_log($log_email, 1, mysql_error());
							$errors['main'] = 'SQL Error';
						}
					}
				}
				
				$degree_level = $_POST['degree_level'];
				
				$oncampus = isset($_POST['oncampus']) && $_POST['oncampus'] ? 1 : 0;
				
				$online = isset($_POST['online']) && $_POST['online'] ? 1 : 0;
				
				$record_id = 0;
				
				# Insert new record
				if (empty($errors)) {
					$result = @mysql_query(
						"INSERT INTO Offered_Degrees ( college_id, major_id, 
							offered_oncampus, offered_online, level ) 
						VALUES ( $college_id, $major_id, $oncampus, $online, $degree_level );");
					if ($result) {
						$record_id = mysql_insert_id();
						$_SESSION['action'] = ACTION_UPDATE;
					} else {
						error_log($log_email, 1, mysql_error());
						$errors['main'] = 'SQL Error';
					}
				}
				break;
			case ACTION_UPDATE:
				if (isset($_POST['existing']) && $_POST['existing']) {
					$major_id = $_POST['major_id'];
					$major_name = '';
				} else {
					$major_id = 0;
					$major_name = $_POST['major_name'];
					if (!ereg('^[a-zA-Z]+( [a-zA-Z]+)*$', $major_name)) {
						$errors['major_name'] = 'Major name must contain only letters';
					} else if (strlen($major_name) > 50) {
						$errors['major_name'] = 'Major cannot contain over 50 characters';
					} else {  # Set major_id = major_name if it exists, or insert
						$result = @mysql_query("SELECT _id FROM Majors WHERE name='$major_name';");
						if ($result) {
							if (mysql_num_rows($result) >= 1) {
								$row = mysql_fetch_array($result, MYSQL_NUM);
								$major_id = $row[0];
								mysql_free_result($result);
							} else {
								mysql_free_result($result);
								$result = mysql_query("INSERT INTO Majors ( name ) VALUES ( '$major_name' );");
								$major_id = mysql_insert_id();
							}
						} else {
							error_log($log_email, 1, mysql_error());
							$errors['main'] = 'SQL Error';
						}
					}
				}
				
				$oncampus = isset($_POST['oncampus']) && $_POST['oncampus'] ? 1 : 0;
				
				$online = isset($_POST['online']) && $_POST['online'] ? 1 : 0;
				
				$degree_level = $_POST['degree_level'];
				
				$record_id = $_POST['record_id'];
				
				$college_id = $_POST['college_id'];
				
				# Update record
				if (empty($errors)) {
					$result = @mysql_query(
						"UPDATE Offered_Degrees 
						SET major_id=$major_id, 
							offered_oncampus=$oncampus,
							offered_online=$online,
							level=$degree_level 
						WHERE _id=$record_id;");
							
					if (!$result) {
						error_log($log_email, 1, mysql_error());
						$errors['main'] = 'SQL Error';
					}
				}
				break;
		}
	}
	
?>
	<h4>Degree Information</h4>
	<br/>
	<?php if (isset($errors['main'])) echo "<p>{$errors['main']}</p>"; ?>
	<form action="degree_operation.php" method="post">
		<input type="hidden" name="submitted" value="1"/>
		<?php 
			if (isset($college_id)) echo '<input type="hidden" name="college_id" value="' . $college_id . '"/>'; 
			if (isset($record_id)) echo '<input type="hidden" name="record_id" value="' . $record_id . '"/>'; 
		?>
		<table class="Formtable">
			<tr class="Formrow">
				<td><input type="radio" name="existing" value="1" <?php if ($major_id > 0) echo 'checked'; ?>/></td>
				<td>Select Existing Major</td>
			</tr>
			<input id="major_input" type="hidden" name="major_id" value="<?php echo $major_id > 0 ? $major_id : 1; ?>"/>
			<tr class="Formrow">
				<td/>
				<td>
					<select id="major_selector" onChange="onOptionSelected(this, 'major_input')">
					<?php 
						$result = mysql_query('SELECT _id, name FROM Majors ORDER BY name;');
						
						while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
							echo "<option value=\"$row[0]\"";
							if ($major_id == $row[0])
								echo ' selected';
							echo ">$row[1]</option>";
						}
						
						mysql_free_result($result);
					?>
					</select>
				</td>
			</tr>
			<tr class="Formrow">
				<td><input type="radio" name="existing" value="0" <?php if ($major_id == 0) echo 'checked'; ?>/></td>
				<td>Create New Major</td>
			</tr>
			<tr class="Formrow">
				<td/>
				<td><input type="text" name="major_name" placeholder="Enter Major Name" <?php if (!empty($major_name)) echo $major_name; ?>/></td>
				<td class="Error" colspan="2"><?php if (isset($errors['major_name'])) echo $errors['major_name']; ?></td>
			</tr>
			<tr class="Formrow">
				<td><input type="checkbox" name="oncampus" value="1" <?php if ($oncampus) echo 'checked'; ?>/></td>
				<td>On Campus</td>
			</tr>
			<tr class="Formrow">
				<td><input type="checkbox" name="online" value="1" <?php if ($online) echo 'checked'; ?>/></td>
				<td>Online</td>
			</tr>
			<tr class="Formrow">
				<td colspan="2">Degree Level</td>
			</tr>
			<input id="level_input" type="hidden" name="degree_level" value="<?php echo $degree_level; ?>"/>
			<tr class="Formrow">
				<td colspan="2">
					<select id="level_selector" onChange="onOptionSelected(this, 'level_input')">
					<?php 
						$result = mysql_query('SELECT _id, name FROM Degree_Levels;');
						
						while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
							echo "<option value=\"$row[0]\"";
							if ($degree_level == $row[0])
								echo ' selected';
							echo ">$row[1]</option>";
						}
						
						mysql_free_result($result);
					?>
					</select>
				</td>
			</tr>
			<tr class="Formrow">
				<td colspan="2"><input type="submit" value="Accept"/></td>
			</tr>
		</table>
	</form>
	
	<br/>
	
	<a href="edit_college.php?id=<?php echo $college_id; ?>#DegreeDiv">Back to Edit College</a>
	
	<br/>
<?php 
	include('include/footer.inc.html');
?>