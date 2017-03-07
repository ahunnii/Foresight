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
	
	$header_elements = array(
		'<script type="text/javascript" src="JavaScript/jquery-ui.js"></script>',
		'<script type="text/javascript" src="JavaScript/coda-slider.js"></script>',
		'<script type="text/javascript" src="JavaScript/ListTables.js"></script>',
		'<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>',
		'<script type="text/javascript" src="JavaScript/google_map.js"></script>',
		'<script type="text/javascript" src="JavaScript/cypressnorth.twitter-feed-reader.js"></script>',
		'<link rel="stylesheet" type="text/css" href="style/display_styles.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/coda-slider.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/result_table_styles.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/view_college_styles.css"/>'
	);
		
	$on_page_load = 'onPageLoaded()';
	$on_page_ready = 'onPageReady()';
	
	require_once('include/site_constants.inc.php');
	
	include('include/masthead.inc.html');
	
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
	
	$college_address = $record['address'] . ' ' . $record['city'] . ', ' . $record['state_name'] . ' ' . $record['zipcode'];

?>

	<script type="text/javascript">
	function onPageLoaded() {
		// Initialize image gallery if not empty
		if ($('div.GalleryDiv').length > 0) {
			$('div#ImgSlider').codaSlider({
				autoHeight: false,
				dynamicArrowsGraphical: true
			});
		} else {
			$('div#ImgDiv').remove();
		}
		
		// Initialize Google Maps
		var mapDiv = $('div#MapCanvas')[0];
		var address = mapDiv.getAttribute('address');
		initializeMapWithAddress(address, mapDiv);
	}
	
	function onPageReady() {
		//console.log('hey 4');
		var feed = $('#TwitterFeed').attr('twitterFeed');
		if (feed) {
			//console.log('hey 3');
			loadLatestTweet(feed, function() {
				//console.log('hey 1');
				if ($('#TwitterFeed').is(':empty')) {
					$('#TwitterWrapper').remove();
				}
			});
		} else {
			//console.log('hey 2');
			$('#TwitterWrapper').remove();
		}
	}
	</script>
	
	<div id="DisplayWrapper">
	
	<div id="InfoWrapper">
	
	<div id="InfoDiv">
		<h4><?php echo $record['name']; ?></h4>
		<br/>
		<table class="DisplayTable">
			<tr>
				<td>Address: </td>
				<td><?php echo $college_address; ?></td>
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
	?>
		
	<div id="OfferedDegrees">
		<h4>Offered Degrees</h4>
		<br/>
		<div id="DegWrapper">
		<table id="DegTable" class="Results">
			<thead>
			<tr>
				<th>Major</th>
				<th>On Campus</th>
				<th>Online</th>
				<th>Degree</th>
			</tr>
			</thead>
			
			<tbody>
			
			<?php 
				$i = 1;
				while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
					echo '<tr class=';
					echo $i++ % 2 == 0 ? '"even"' : '"odd"';
					echo "><td>$row[1]</td><td>";
					echo $row[2] ? 'Yes' : 'No';
					echo '</td><td>';
					echo $row[3] ? 'Yes' : 'No';
					echo "</td><td>$row[4]</td></tr>\n";
				}
				mysql_free_result($result);
			?>
			</tbody>
		</table>
		</div>
	</div>
	
	<?php 
		} # End OfferedDegrees
	?>
	
<div id="TwitterWrapper">
		<div id="TwitterLogo">
		<img src="Graphics/TwitterBar.png" href="https://www.twitter.com"/>
		</div>
		<div id="TwitterFeed" <?php if ($record['twitter_feed']) echo 'twitterFeed="' . $record['twitter_feed'] . '"'; ?>></div>
</div>		
	
	</div> <!-- End #InfoWrapper -->
	
	<div id="RightDiv">
	
	<div id="ImgDiv">
		<div id="ImgSlider" class="coda-slider">
			<?php 
				$result = mysql_query("SELECT * FROM Campus_Images WHERE college_id=$college_id;");
				
				if ($result && mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
						$file_path = DIR_CAMPUS_IMAGES . $row['filename'] . '.jpg';
						echo <<<EOS
						<div class="GalleryDiv">
							<img class="GalleryImage" src="$file_path"/>
							<div class="GalleryDesc">
							<div class="Gallery-bg"></div>
							<p class="GalleryDesc">{$row['description']}</p>
							</div>
						</div>
EOS;
					}
					mysql_free_result($result);
				}
			?>
		</div>
	</div>
	
	<div id="MapCanvas" address="<?php echo $college_address; ?>"></div>
	
	</div> <!-- End #RightDiv -->
	
	<div class="clearfix"></div>
	
	</div> <!-- End #DisplayWrapper -->
<?php 
	include('include/footer.inc.html');
?>