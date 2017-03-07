<?php # Kyle L. Oswald 10/15/12
	if (isset($_GET['college_id'])) {
		$college_id = $_GET['college_id'];
	} else {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
	}
	
	if (isset($_GET['college_name']))
		$page_title = $_GET['college_name'];
	else
		$page_title = 'Viewing College';
	
	$startup_script = <<<EOS
	<script type="text/javascript">
	function onPageLoaded() {
		// Initialize image gallery if not empty
		if ($('div.GalleryDiv').length > 0) {
			$('div#ImgSlider').codaSlider({
				autoHeight: false,
				dynamicArrowsGraphical: true
			});
		}
	}
	</script>
EOS;
	$page_styles = <<<EOS
	<style type="text/css">
	
	
	div#InfoDiv {
	width: 100%;
	height: 100%;
	float: left;
	}
	
	div#InfoDiv td {
	font-size: 1em;
	}
	
	div#InfoDiv h4 {
	font-size: 1.4em;
	}

	div#ImgDiv {
	width: 375px;
	height: 300px;
	max-width: 500px;
	clear: none;
	position: absolute;
	right: 0px;
	}	
	
	div.GalleryDiv {
	width: 300px;
	height: 300px;
	}
	
	img.GalleryImage {
	width: 250px;
	height: auto;
	margin: auto;
	background-color: black;
	}
	
	div#DisplayWrapper {
	position: relative;
	padding-right: 385px;
	min-height: 325px;
	}
	
	
	
	</style>
EOS;
	$header_elements = array('<script type="text/javascript" src="JavaScript/jquery.js"></script>',
		'<script type="text/javascript" src="JavaScript/jquery-ui.js"></script>',
		'<script type="text/javascript" src="JavaScript/coda-slider.js"></script>',
		'<script type="text/javascript" src="JavaScript/ListTables.js"></script>',
		'<link rel="stylesheet" type="text/css" href="style/display_styles.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/coda-slider.css"/>',
		$page_styles,
		$startup_script);
		
	$on_page_load = 'onPageLoaded()';
	include('include/masthead.inc.html');
	
	include('include/site_constants.inc.php');
	
	$result = @mysql_query(
		"SELECT c.*, s.name AS state_name 
		FROM Colleges AS c 
		INNER JOIN States s 
		ON ( c._id=$college_id 
			AND s._id=c.state_id );");
	
	if ($result && mysql_num_rows($result) > 0) {
		$record = mysql_fetch_array($result, MYSQL_ASSOC);
	} else { # Non-existant id
		echo '<div><h2 class="Error">Invalid ID</h2></div>';
		include('include/footer.inc.html');
		mysql_close();
		exit();
	}
	
	include('include/misc_functions.inc.php');

?>
	<div id="DisplayWrapper">
	
	<div id="InfoDiv">
		<h4><?php echo $record['name']; ?></h4>
		<br/>
		<table class="DisplayTable">
			<tr>
				<td>Address: </td>
				<td><?php echo $record['address'] . ' ' . $record['city'] . ', ' . $record['state_name'] . ' ' . $record['zipcode']; ?></td>
			</tr>
			<tr>
				<td>Phone: </td>
				<td><?php echo ( empty($record['phone']) ? 'Unknown' : $record['phone'] ); ?></td>
			</tr>
			<tr>
				<td>Website: </td>
				<td><?php echo ( empty($record['website']) ? 'Unknown' : ( '<a href="http://' . $record['website'] . '">' . ellipsize($record['website'], 30) . '</a>' ) ); ?></td>
			</tr>
			<tr>
				<td>In-State Tuition: </td>
				<td><?php echo ( empty($record['instate_tuition']) ? 'Unknown' : ( '$' . number_format($record['instate_tuition']) ) ); ?></td>
			</tr>
			<tr>
				<td>Out-of-State Tuition: </td>
				<td><?php echo ( empty($record['outstate_tuition']) ? 'Unknown' : ( '$' . number_format($record['outstate_tuition']) ) ); ?></td>
			</tr>
			<tr>
				<td>Dorm Expenses: </td>
				<td><?php echo ( empty($record['dorm_cost']) ? 'Unknown' : ( '$' . number_format($record['dorm_cost']) ) ); ?></td>
			</tr>
			<tr>
				<td>Average ACT Score: </td>
				<td><?php echo ( empty($record['avg_act']) ? 'Unknown' : $record['avg_act'] ); ?></td>
			</tr>
			<tr>
				<td>Average GPA: </td>
				<td><?php echo ( empty($record['avg_gpa']) ? 'Unknown' : $record['avg_gpa'] ); ?></td>
			</tr>
		</table>
	</div>
	
	<div id="ImgDiv">
		<div id="ImgSlider" class="coda-slider">
			<?php 
				$result = mysql_query("SELECT * FROM Campus_Images WHERE college_id=$college_id;");
				
				#echo '<p class="Error">' . mysql_error() . '</p>';
				if ($result && mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
						$file_path = DIR_CAMPUS_IMAGES . $row['filename'] . '.jpg';
						echo <<<EOS
						<div class="GalleryDiv">
							<img class="GalleryImage" src="$file_path"/>
							<p class="GalleryDesc">{$row['description']}</p>
						</div>
EOS;
					}
					mysql_free_result($result);
				}
			?>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
	</div> <!-- End DisplayWrapper Div -->
<?php 
	include('include/footer.inc.html');
?>