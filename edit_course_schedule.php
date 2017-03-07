<?php # Kyle L. Oswald 

if (!isset($_SESSION)) {
	session_start();
}

require_once('include/site_constants.inc.php');

require_once('include/misc_functions.inc.php');

$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];

$page_title = 'Course Scheduler';

$header_elements = array(
	'<link rel="stylesheet" type="text/css" href="style/course_schedule_styles.css"/>',
	'<link rel="stylesheet" type="text/css" href="style/result_table_styles.css"/>',
	'<link rel="stylesheet" type="text/css" href="style/jquery-ui-slider-custom.css"/>',
	'<script type="text/javascript" src="JavaScript/jquery-ui.js"></script>',
	'<script type="text/javascript" src="JavaScript/Timer.js"></script>',
	'<script type="text/javascript" src="JavaScript/jquery_popup.js"></script>'
);

$on_page_ready = 'onPageReady()';

include('include/masthead.inc.html');

if (!isset($_SESSION['User_Id'])) {
	echo '<h4>You must be logged in to view this page</h4>';
	echo '<a href="index.php">Back to Index</a>';
	include('include/footer.inc.html');
	exit();	
} else if (!isset($_GET['college_id']) || !ereg('^[0-9]+$', $_GET['college_id'])) {
	#|| (isset($_GET['schedule_id']) && !ereg('^[0-9]+$', $_GET['schedule_id']))) {
	echo '<h4>Invalid access to page</h4>';
	echo '<a href="index.php">Back to Index</a>';
	include('include/footer.inc.html');
	exit();	
} else {
	$user_id = $_SESSION['User_Id'];
	$college_id = $_GET['college_id'];
	$result = mysql_query(
		"SELECT name 
		FROM Colleges 
		WHERE _id = $college_id
		LIMIT 0, 1"
	);
	$college_name = mysql_result($result, 0);
	mysql_free_result($result);
	
	echo "<h4>Course Schedule for $college_name</h4>\n<br/>\n";
	
	$result = mysql_query(
		"SELECT _id 
		FROM Saved_Schedules 
		WHERE user_id = $user_id 
			AND college_id = $college_id 
		LIMIT 1"
	);
	
	if (mysql_num_rows($result) > 0) {
		$schedule_id = mysql_result($result, 0);
		mysql_free_result($result);
	}
}

?>

<script type="text/javascript">
var displayOffered = false, displaySelected = false, displayCalendar = false;

var schedule_id = <?php echo isset($schedule_id) ? $schedule_id : 'null'; ?>;

var college_id = <?php echo $college_id; ?>;

var coursesList; // course array returned from ajax request
var offeredCoursesList; // dom elements contained in #OfferedCoursesList
var selectedCoursesList; // dom elements contained in #SelectedCoursesList

var loaderImg;

function onPageReady() {
	selectedCoursesList = [];
	
	loaderImg = $('div#OfferedCoursesList img.LoaderImg');
	
	MM_preloadImages('Graphics/Buttons/arrow_up.png', 'Graphics/Buttons/button_save_h.png');
	
	$('#HeaderOfferedCourses span.Header').click(function() {
		if (displayOffered) {
			$('#DisplayOfferedCourses').slideUp();
			$('#HeaderOfferedCourses img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_up.png');
			displayOffered = false;
		} else {
			$('#DisplayOfferedCourses').slideDown();
			$('#HeaderOfferedCourses img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_down.png');
			displayOffered = true;
		}
	});
	$('#DisplayOfferedCourses').hide();
	
	$('#HeaderSelectedCourses span.Header').click(function() {
		if (displaySelected) {
			$('#DisplaySelectedCourses').slideUp();
			$('#HeaderSelectedCourses img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_up.png');
			displaySelected = false;
		} else {
			$('#DisplaySelectedCourses').slideDown();
			$('#HeaderSelectedCourses img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_down.png');
			displaySelected = true;
		}
	});
	$('#DisplaySelectedCourses').hide();
	
	$('#HeaderCalendar span.Header').click(function() {
		if (displayCalendar) {
			$('#DisplayCalendar').slideUp();
			$('#HeaderCalendar img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_up.png');
			displayCalendar = false;
		} else {
			$('#DisplayCalendar').slideDown();
			$('#HeaderCalendar img.HeaderArrow').attr('src', 'Graphics/Buttons/arrow_down.png');
			displayCalendar = true;
		}
	});
	$('#DisplayCalendar').hide();
	
	$('#TimeSlider').slider({
		'range': 	true,
		'step':		30,
		'max':		1440,
		'min':		0,
		'values':	[ 360, 1080 ],
		'slide':	function(e, ui) {
			var time, hrs, mins, ampm;
			
			time = ui.values[0];
			hrs = Math.floor(time / 60);
			mins = time % 60;
			if (hrs == 24) {
				hrs = 23;
				mins = 59;
			}
			if (hrs < 10)
				hrs = '0' + hrs;
			if (mins < 10)
				mins = '0' + mins;
			$('input[name="start_time"]').attr('value', hrs.toString() + ':' + mins.toString());
			ampm = (hrs >= 0 && hrs < 12) ? 'AM' : 'PM'
			hrs %= 12;
			if (hrs == 0)
				hrs = 12;
			$('#StartTimeDisplay').text(hrs.toString() + ':' + mins.toString() + ' ' + ampm);
			
			time = ui.values[1];
			hrs = Math.floor(time / 60);
			mins = time % 60;
			if (hrs == 24) {
				hrs = 23;
				mins = 59;
			}
			if (hrs < 10)
				hrs = '0' + hrs;
			if (mins < 10)
				mins = '0' + mins;
			$('input[name="end_time"]').attr('value', hrs.toString() + ':' + mins.toString());
			ampm = (hrs >= 0 && hrs < 12) ? 'AM' : 'PM'
			hrs %= 12;
			if (hrs == 0)
				hrs = 12;
			$('#EndTimeDisplay').text(hrs.toString() + ':' + mins.toString() + ' ' + ampm);
		},
	});
	
	$('#SaveScheduleDiv img.Loader').hide();
	$('#SaveScheduleButton').click(function(e) {

		$('#SaveScheduleButton').hide();
		$('#SaveScheduleDiv img.Loader').show();
		$('#SaveScheduleDiv p.Error').empty();
			
		var course_ids = [];
		$('#SelectedCoursesList tbody').each(function() {
			course_ids.push($(this).data('course')._id);
		});
		
		if (schedule_id === null) {
			var query = {
				'course_ids'	: course_ids,
				'college_id'	: college_id,
			};
		} else {
			var query = {
				'schedule_id'	: schedule_id, 
				'college_id'	: college_id,
				'course_ids'	: course_ids
			};
		}
		
		$.ajax({
			'url'		: '/requests/save_course_schedule.php', 
			'data'		: query,
			'dataType'	: 'json',
			'type'		: 'POST',
		})
		.done(function(data, status, jqXHR) {
			if (typeof data.error !== 'undefined') {
				var p = $('#SaveScheduleDiv p.Error');
				p.text(data.error);
				p.show(0, function() { p.fadeOut(); });
			} else {
				schedule_id = data.schedule_id;
			}
		})
		.fail(function(jqXHR, status, error) {
			var p = $('#SaveScheduleDiv p.Error');
			p.text('Server Error');
			p.show(0, function() { p.fadeOut(); });
		})
		.always(function() {
			$('#SaveScheduleDiv img.Loader').hide();
			$('#SaveScheduleButton').show();
		});
		
		e.preventDefault();
	});
	
	// intercept submit event
	$('#OfferedCoursesForm').submit(function(e) {
		var form = this;
		
		// if no prior request pending
		if (!form.request || form.ready) {
			var query = $(form).serialize();
			
			// disable form inputs
			var inputs = $(form).find('input, select, button, textarea');
			inputs.prop('disabled', true);
			
			// show ajax loader gif
			$('div#OfferedCoursesList').prop('disabled', true);
			loaderImg.show();
			
			form.ready = false;
			
			//console.log('POST Params: ' + query);
			
			form.request = $.ajax({
				'url'		: '/requests/get_courses.php',
				'data'		: query,
				'dataType'	: 'json',
				'type'		: 'POST'
			})
			.done(function(data, status, jqXHR) {
				//console.log('data = %o', data);
				if (typeof data.error !== 'undefined') {
					$(form).find('p.Error').text('Error: ' + data.error);
				} else {
					$(form).find('p.Error').empty();
					$(form).find('#ResultCount').text('Results: ' + data.courses.length);
					fillOfferedCourses(data);
				}
			})
			.fail(function(jqXHR, status, error) {
				$(form).find('p.Error').text('Error: ' + status);
			})
			.always(function() {
				// enable form inputs
				inputs.prop('disabled', false);
				
				// hide ajax loader gif
				loaderImg.hide();
				$('div#OfferedCoursesList').prop('disabled', false);
				
				form.ready = true;
			});
		}
		
		e.preventDefault();
	});
	
	MM_preloadImages('Graphics/drag_select2.jpg', 'Graphics/drag_select_remove.jpg');
	var element = $('#OfferedCoursesDrag img');
	element.hover(
	function() {
		$(this).attr('src', 'Graphics/drag_select2.jpg');
	}, 
	function() {
		$(this).attr('src', 'Graphics/drag_reg2.jpg');
	});
	
	element.droppable({
		drop: moveToSelectedCourses,
		scope: 'OfferedCourses'
	});
	
	element = $('#SelectedCoursesDrag img');
	element.hover(
	function() {
		$(this).attr('src', 'Graphics/drag_select_remove.jpg');
	},
	function() {
		$(this).attr('src', 'Graphics/drag_reg_remove.jpg');
	});
	
	element.droppable({
		drop: moveToOfferedCourses,
		scope: 'SelectedCourses'
	});
	
	loadSavedCourses();
}

function loadSavedCourses() {
	//console.log('Schedule ID: ' + schedule_id);
	if (schedule_id !== null) {
		$.ajax({
			'url'		: '/requests/get_saved_courses.php',
			'data'		: {
				'schedule_id': schedule_id
			}, 
			'dataType'	: 'json', 
			'type'		: 'POST'
		})
		.done(function(data, status, jqXHR) {
			//console.log('Saved Courses: %o', data);
			if (typeof data.error !== 'undefined') {
				alert(data.error);
			} else {
				fillSelectedCourses(data);
			}
		})
		.fail(function(jqXHR, status, error) {
			alert('loadSavedCourses() Failed: ' + error + '\n[' + jqXHR.responseText + ']');
		})
		.always(function() {
			
		});
		
	}
}

function createCourseRow(course) {
	var rowWrapper = $('<tbody course_id="' + course._id + '"></tbody>');
	rowWrapper.data('course', course);

	var row = $('<tr class="inner"></tr>');

	var cell = $('<td>' + course.department_code + ' ' + course.course_code + '</td>');
	if (typeof course.materials !== 'undefined') {
		cell.popup({ 
		'appendto'	: '#Content',
		'helper'	: function() {
			var div = $('<div class="AmazonBookDiv"/>');
			div.append($('<img class="bg-img" src="Graphics/popup.png"/>'));
			
			var rdiv = $('<div class="BookReport"/>');
			
			var materials = $(this).data('course').materials; // get materials data
			rdiv.append(
				$('<a href="' + materials.amazon_product_page + '" target="_blank"></a>').append(
					$('<img class="AmazonBookThumb" src="' + materials.product_image_small + '"/>')
				)
			);
			
			var table = $('<table class="AmazonBookTable"></table>');
			table.append($('<thead><tr><th colspan="2">Offers on Amazon</th></tr></thead>'));
			if (materials.list_price != 0 && materials.list_price != null) {
				table.append($('<tr><td>List Price:</td><td class="value">$' + materials.list_price + '</td></tr>'));
			}
			table.append($('<tr><td>New From:</td><td class="value">$' + materials.lowest_new_price + '</td></tr>'));
			table.append($('<tr><td>Used From:</td><td class="value">$' + materials.lowest_used_price + '</td></tr>'));
			
			rdiv.append(table);
			
			div.append(rdiv);
			
			return div;
		},
		'displayfunc'	: 'fade',
		'hideoptions'	: {
			'duration': 200
		},
		'showoptions'	: {
			'duration': 200
		},
		});
		
		cell.data('course', course);
		
		cell.css('color', '#00A5C8');
	}
	row.append(cell);

	cell = $('<td>Section ' + course.section_code + '</td>');
	row.append(cell);

	cell = $('<td colspan="5">' + course.name + '</td>');
	row.append(cell);

	cell = $('<td style="{text-align: right;width: 100px;}">' + (course.offered_oncampus ? '(On Campus)' : '(Online)') + '</td>');
	row.append(cell);

	rowWrapper.append(row);

	row = $('<tr class="inner"></tr>');

	cell = $('<td colspan="2">' + course.start_date + ' - ' + course.end_date + '</td>');
	row.append(cell);

	cell = $('<td colspan="2" style="width: 170px;">' + course.start_time + ' - ' + course.end_time + '</td>');
	row.append(cell);

	cell = $(
		'<td colspan="2">' + 
		(course.sunday == '1' ? 'Su&nbsp;' : '&nbsp;&nbsp;&nbsp;') +
		(course.monday == '1' ? 'M&nbsp;' : '&nbsp;&nbsp;') + 
		(course.tuesday == '1' ? 'Tu&nbsp;' : '&nbsp;&nbsp;&nbsp;') + 
		(course.wednesday == '1' ? 'W&nbsp;' : '&nbsp;&nbsp;') +
		(course.thursday == '1' ? 'Th&nbsp;' : '&nbsp;&nbsp;&nbsp;') +
		(course.friday == '1' ? 'F&nbsp;' : '&nbsp;&nbsp;') +
		(course.saturday == '1' ? 'Sa&nbsp;' : '&nbsp;&nbsp;&nbsp;') +
		'</td>'
	);
	row.append(cell);

	cell = $('<td colspan="2">' + course.min_credits + ' - ' + course.max_credits + '</td>');
	row.append(cell);

	rowWrapper.append(row);

	rowWrapper.draggable({ 
		opacity		: 0.50,
		revert		: 'invalid', 
		containment	: 'window',
		scroll		: false,
		appendTo	: 'body',
		helper		: function(e, ui) {
			var ret = $('<table class="Results"></table>');
			ret.append($(this).clone());
			ret.css('cursor', 'move');
			ret.css('background-color', $(this).css('background-color'));
			return ret[0];
		}, 
		cursorAt	: {
			top		: 1,
			left	: 1,
		},
		// fix cursor issue
		start		: function(e, ui) {
			$('body').css('cursor', 'move');
		},
		stop		: function(e, ui) {
			$('body').css('cursor', 'default');
		}
	});

	return rowWrapper;
}

function fillSelectedCourses(data) {
	var coursesList = data.courses;
	
	var table = $('#SelectedCoursesList table.Results');
	
	for (var i = 0, k = 0; i < coursesList.length; i++) {
		var course = coursesList[i];
		
		// skip
		if (inSelected(course._id)) {
			continue;
		} else {
			k++;
		}
		
		var rowWrapper = createCourseRow(course);
		rowWrapper.draggable('option', 'scope', 'SelectedCourses');
		
		var clazz = (k % 2 == 0 ? 'even' : 'odd');
		rowWrapper.addClass(clazz);
		
		table.append(rowWrapper);
	}
	
	return k;
}

function fillOfferedCourses(data) {
	coursesList = data.courses;
	
	var table = $('#OfferedCoursesList table.Results');
	table.empty();
	
	table.append($('<thead><th style="width: 70px;">Course Number</th><th>Section Number</th><th colspan="5">Course Name</th><th style="width: 100px;">Location</th></thead>'));
	
	
	for (var i = 0, k = 0; i < coursesList.length; i++) {
		var course = coursesList[i];
		
		// skip
		if (inSelected(course._id)) {
			continue;
		} else {
			k++;
		}
		
		var rowWrapper = createCourseRow(course);
		rowWrapper.draggable('option', 'scope', 'OfferedCourses');
		
		var clazz = (k % 2 == 0 ? 'even' : 'odd');
		rowWrapper.addClass(clazz);
		
		table.append(rowWrapper);
	}
	
	return k;
}

function courseExists(_id) {
	if (coursesList != null) {
		for (var i = 0; i < coursesList.length; i++) {
			if (coursesList[i]._id == _id)
				return true;
		}
	}
	
	return false;
}

function inSelected(_id) {
	var ret = false;
	$('#SelectedCoursesList tbody').each(function() {
		if ($(this).data('course')._id == _id) {
			ret = true;
			return false;
		}
	});
	
	return ret;
}

function moveToSelectedCourses(e, ui) {
	var row = ui.draggable;
	selectedCoursesList[selectedCoursesList.length] = row;
	row.detach();
	$('#SelectedCoursesList table.Results').append(row);
	row.draggable('option', 'scope', 'SelectedCourses');
	reStripe($('#SelectedCoursesList tbody'));
	reStripe($('#OfferedCoursesList tbody'));
}

function moveToOfferedCourses(e, ui) {
	var row = ui.draggable;
	if (courseExists(row.data('course')._id)) { // switch list
		row.detach();
		$('#OfferedCoursesList table.Results').append(row);
		row.draggable('option', 'scope', 'OfferedCourses');
		reStripe($('#SelectedCoursesList tbody'));
		reStripe($('#OfferedCoursesList tbody'));
	} else { // throw away
		row.draggable('destroy');
		row.remove();
		reStripe($('#SelectedCoursesList tbody'));
	}
}

function reStripe(list) {
	var i = 1;
	list.each(function() {
		if (i++ % 2 == 0) {
			$(this).removeClass('odd');
			$(this).addClass('even');
		} else {
			$(this).addClass('odd');
			$(this).removeClass('even');
		}
	});
}
</script>

<p>
Creating a course schedule allows for more accurate reports on expenses 
like course materials and how often you will be making the commute to and 
from campus. The Offered Courses tab will allow you to search for courses 
available to you based on the criterion entered on the left panel. If 
there are any materials or textbooks required for the course then its 
course number will be hightlighted (Ex. <em style="font-style: normal; color: #00A5C8;">ACC 201</em>), 
hover over it to see offers on Amazon. Drag offered courses to the add box 
to move them to your selection or move selected courses to the remove box 
to return them. Be sure to save your selection so it can be processed and 
added to your profile!
</p>

<div id="SaveScheduleDiv" style="padding-top: 20px; height: 50px;">
<span>
	<a id="SaveScheduleButton" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_aswapImage(this, '', 'Graphics/Buttons/button_save_h.png')">
		<img src="Graphics/Buttons/button_save.png"/>
	</a>
	<img class="Loader" src="Graphics/ajax_loader_snake.gif" style="margin-left: 30px;"/>
	<p class="Error"></p>
</span>
</div>

<div id="HeaderOfferedCourses" class="Header">
	<span class="Header">
		<h4>Offered Courses</h4>
		<div id="OfferedCoursesArrow">
			<img class="HeaderArrow" src="Graphics/Buttons/arrow_up.png"/>
		</div>
	</span>
</div>
<div id="DisplayOfferedCourses">
	<div id="OfferedCoursesWrapper">
	
	<div id="OfferedCoursesConfig">
		<form id="OfferedCoursesForm">
		
		<input type="hidden" name="college" value="<?php echo $college_id; ?>"/>
		
		<p class="Error"></p>
		
		<span>
		<input type="submit" value="Go"/>
		<p id="ResultCount">Results: 0</p>
		</span>
		
		<br/>
		
		<p>Department</p>
		<select name="dept">
		<?php 
			$result = mysql_query(
				"SELECT department_code 
				FROM Courses 
				WHERE college_id = $college_id 
				GROUP BY department_code 
				ORDER BY department_code ASC"
			);
			
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				echo "<option value=\"$row[0]\">$row[0]</option>\n";
			}
			
			mysql_free_result($result);
		?>
		</select>
		
		<br/>
		
		<p>Degree</p>
		<select name="degree">
		<?php 
			$result = mysql_query(
				"SELECT d._id, m.name, l.name, l._id 
				FROM Offered_Degrees AS d 
				INNER JOIN Degree_Levels AS l 
				ON ( 
					d.college_id = $college_id 
					AND l._id = d.level 
				) 
				INNER JOIN Majors AS m 
				ON ( 
					d.major_id = m._id 
				) 
				ORDER BY m.name ASC, l._id ASC"
			);
			
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				echo "<option value=\"$row[0]\">$row[1] - $row[2]</option>\n";
			}
			
			mysql_free_result($result);
		?>
		</select>
		
		<br/>
		
		<p>Course Number</p>
		<input type="text" name="course_num"/>
		
		<br/>
		
		<input type="hidden" name="start_time" value="06:00"/>
		<input type="hidden" name="end_time" value="18:00"/>
		
		<p>Time</p>
		<span style="padding: 3px;">
		<p id="StartTimeDisplay">6:00 AM</p>
		<p>&nbsp;-&nbsp;</p>
		<p id="EndTimeDisplay">6:00 PM</p>
		</span>
		<div id="TimeSlider"></div>
		
		<br/>
		
		<p>Days</p>
		<div id="SectionDays">
			<span>
			<input type="checkbox" name="su" value="1" checked />
			<p>&nbsp;Sunday</p>
			</span>
			
			<span>
			<input type="checkbox" name="m" value="1" checked />
			<p>&nbsp;Monday</p>
			</span>
			
			<span>
			<input type="checkbox" name="tu" value="1" checked />
			<p>&nbsp;Tuesday</p>
			</span>
			
			<span>
			<input type="checkbox" name="w" value="1" checked />
			<p>&nbsp;Wednesday</p>
			</span>
			
			<span>
			<input type="checkbox" name="th" value="1" checked />
			<p>&nbsp;Thursday</p>
			</span>
			
			<span>
			<input type="checkbox" name="f" value="1" checked />
			<p>&nbsp;Friday</p>
			</span>
			
			<span>
			<input type="checkbox" name="sa" value="1" checked />
			<p>&nbsp;Saturday</p>
			</span>
		</div>
		
		<br/>
		
		<span>
		<input type="checkbox" name="oncampus" value="1" checked />
		<p>&nbsp;On Campus</p>
		</span>
		
		<br/>
		
		<span>
		<input type="checkbox" name="online" value="1" checked />
		<p>&nbsp;Online</p>
		</span>
		
		</form>
	</div>
	
	<div>
	
	<div id="OfferedCoursesList">
		<div class="Results">
			<table class="Results">
				
			</table>
		</div>
		<img class="LoaderImg" src="Graphics/square_loader.gif"></img>
	</div>
	
	<div id="OfferedCoursesDrag">
		<img src="Graphics/drag_reg2.jpg"/>
	</div>
	
	</div>
	
	<div class="clearfix"></div>
	
	</div>
</div>

<div id="HeaderSelectedCourses" class="Header">
	<span class="Header">
		<h4>Selected Courses</h4>
		<div id="SelectedCoursesArrow">
			<img class="HeaderArrow" src="Graphics/Buttons/arrow_up.png"/>
		</div>
	</span>
</div>
<div id="DisplaySelectedCourses">
	<div id="SelectedCoursesList">
		<div class="Results">
			<table class="Results">
				<thead><th style="width: 70px;">Course Number</th><th>Section Number</th><th colspan="5">Course Name</th><th style="width: 100px;">Location</th></thead>
			</table>
		</div>
	</div>
	
	<div id="SelectedCoursesDrag">
		<img src="Graphics/drag_reg_remove.jpg"/>
	</div>
	
</div>

<div id="HeaderCalendar" class="Header">
	<span class="Header">
		<h4>Calendar</h4>
		<div id="CalendarArrow">
			<img class="HeaderArrow" src="Graphics/Buttons/arrow_up.png"/>
		</div>
	</span>
</div>
<div id="DisplayCalendar">
	<h5 style="padding: 20px; color: black;">
	Coming Soon!!!
	</h5>
</div>

<?php 
include('include/footer.inc.html');
?>