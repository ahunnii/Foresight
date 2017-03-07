<?php # Kyle L. Oswald 10/22/12
	if (!$_SESSION) {
		session_start();
	}
	
	include('include/site_constants.inc.php');
	
	# Verify permissions
	if (!(isset($_SESSION['User_Permission']) 
		&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
		exit();
	}
	
	# Set max upload size to 2MB
	define('MAX_IMG_SIZE', 2000000);
	
	$page_title = 'Campus Image';
	$header_elements = array('<script type="text/javascript" src="JavaScript/ComboBox.js"></script>');
	include('include/masthead.inc.html');
	
	$record_id = 0;
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
				
				$description = null;
				
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
				
				$result = @mysql_query(
					"SELECT * 
					FROM Campus_Images 
					WHERE _id=$record_id;");
					
				if (!$result) {
					$errors['main'] = 'SQL Error';
					error_log($log_email, 1, mysql_error());
				} else if (mysql_num_rows($result) < 1) {
					$errors['main'] = 'Record does not exist';
					mysql_free_result($result);
				} else {
					$row = mysql_fetch_array($result, MYSQL_ASSOC);
					$description = $row['description'];
					mysql_free_result($result);
					
					$_SESSION['action'] = ACTION_UPDATE;
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
				
				if ($_POST['submit_type'] == 'file' && isset($_FILES['file'])) {
					if (!($_FILES['file']['type'] == 'image/jpeg')) {
						$errors['file'] = 'File must be a jpeg image';
					} else if ($_FILES['file']['size'] > MAX_IMG_SIZE) {
						$errors['file'] = 'File size cannot exceed 2MB';
					}
				} else {
					$errors['main'] = 'Invalid Page Access';
				}
				
				$description = null;
				
				if (empty($errors)) {
					$result = @mysql_query(
						"INSERT INTO Campus_Images ( college_id, description, filename ) 
						VALUES ( $college_id, NULL,
							HEX(
							( SELECT AUTO_INCREMENT 
							FROM information_schema.TABLES 
							WHERE TABLE_NAME='Campus_Images' ) 
							)
						);");
					
					if ($result) {
						$record_id = mysql_insert_id();
						$file_path = DIR_CAMPUS_IMAGES . strtoupper(dechex($record_id)) . '.jpg';
						
						move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
						
						$_SESSION['action'] = ACTION_UPDATE;
					} else {
						$errors['main'] = 'SQL Insert Error' . mysql_error();
					}
				}
				break;
			case ACTION_UPDATE:
				if (isset($_POST['record_id'])) {
					$record_id = $_POST['record_id'];
				} else {
					echo '<h5 class="Error">Missing required parameter</h5>';
					include('include/footer.inc.html');
					mysql_close();
					exit();
				}
				
				$description = isset($_POST['description']) ? $_POST['description'] : null;
				
				if ($_POST['submit_type'] == 'file') {
					if (!isset($_FILES['file'])) {
						$errors['file'] = 'Failed upload';
					} else if (!($_FILES['file']['type'] == 'image/jpeg')) {
						$errors['file'] = 'File must be a jpeg image';
					} else if ($_FILES['file']['size'] > MAX_IMG_SIZE) {
						$errors['file'] = 'File size cannot exceed 2MB';
					} else {
						$file_path = DIR_CAMPUS_IMAGES . strtoupper(dechex($record_id)) . '.jpg';
						
						move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
					}
				} else {
					$result = @mysql_query(
						'UPDATE Campus_Images 
						SET description=' . (empty($description) ? 'NULL' : "'" . escape_data($description) . "'") . 
						" WHERE _id=$record_id;");
						
					if (!$result) {
						$errors['main'] = 'SQL Update Error';
						error_log($log_email, 1, mysql_error());
					}
				}
				
				break;
		}
	}
?>
	<h4 class="Error"><?php if (isset($errors['main'])) echo $errors['main']; ?></h4>
	
	<br/>

	<form enctype="multipart/form-data" action="image_operation.php" method="post">
		<?php 
			if ($college_id) echo '<input type="hidden" name="college_id" value="' . $college_id . '"/>'; 
			if ($record_id) echo '<input type="hidden" name="record_id" value="' . $record_id . '"/>'; 
		?>
		<input type="hidden" name="submit_type" value="file"/>
		<table class="Formtable">
			<tr class="Formrow">
				<td>File:</td>
				<td><input type="file" name="file"/></td>
				<td colspan="2" class="Error"><?php if (isset($errors['file'])) echo $errors['file']; ?></td>
			</tr>
			<tr class="Formrow">
				<td><input type="submit" value="Upload"/></td>
			</tr>
		</table>
	</form>
<?php 
	if ($_SESSION['action'] == ACTION_UPDATE) {
	echo '<form action="image_operation.php" method="post">';
	
		if ($college_id) echo '<input type="hidden" name="college_id" value="' . $college_id . '"/>'; 
		if ($record_id) echo '<input type="hidden" name="record_id" value="' . $record_id . '"/>'; 
		echo 
		'<input type="hidden" name="submit_type" value="data"/>
		<table class="Formtable">
			<tr class="Formrow">
				<td>Description:</td>
				<td><input type="text" name="description" value="'; if (!empty($description)) echo $description; echo '" maxlength="255" width="150" height="80"/></td>
				<td colspan="2" class="Error">'; if (isset($errors['description'])) echo $errors['description']; echo '</td>
			</tr>
			<tr class="Formrow">
				<td><input type="submit" value="Accept"/></td>
			</tr>
		</table>
	</form>';
	}
?>

<?php 
	include('include/footer.inc.html');
?>