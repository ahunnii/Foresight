<?php 

if (!isset($_SESSION)) {
	session_start();
}

include('../include/site_constants.inc.php');
if (!(isset($_SESSION['User_Permission']) 
	&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
	echo '0';
	exit();
}

date_default_timezone_set('America/Detroit');

#require_once($_SERVER['DOCUMENT_ROOT'] . "../../Secure/error_reporting.inc.php");

# Set site reporting level
error_reporting(E_ALL & ~E_STRICT);

# Global constant specifying report mode
define('DEBUG', true);

# Default log email
$log_email = 'kyleleeswald@gmail.com';

#Function for error reporting, do not call this directly
function handle_exception($e_number, $e_message, $e_file, $e_line, $e_vars) {
	global $log_email;
	
	if (DEBUG) {
		echo "<div class=\"Error\"> \n" .
			"An exception has occured: <br/>\n" .
			"\tNumber - $e_number <br/>\n" . 
			"\tLine - $e_line <br/>\n" .
			"\tFile - $e_file <br/>\n" . 
			"\tMessage - $e_message <br/>\n" . 
			"</div>\n";
	} else {
		echo "<div class=\"error\"> \n" . 
			"An error has occurred.\n" . 
			"</div>\n";
	}
	
	error_log($e_message, 1, $log_email);
	
	ob_end_flush();
	
	exit();
}

# Set delegate to receive error notifications
set_error_handler('handle_exception');

/*
# Output buffering
function ob_file_callback($buffer) {
	global $ob_file;
	fwrite($ob_file, $buffer);
}

$ob_file = fopen('fill_msu_courses_log.txt', 'w');

ob_start('ob_file_callback');
*/

ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '../../Secure/db_connect.inc.php');

include('../include/simple_html_dom.inc.php');

$result = mysql_query(
	"SELECT _id 
	FROM Colleges 
	WHERE name = 'Michigan State University' 
	LIMIT 0, 1;"
);
$college_id = mysql_result($result, 0);
mysql_free_result($result);

/*
$html = new simple_html_dom();
$html->load_file('http://www.schedule.msu.edu/searchResults.asp');

$options = $html->find('#Subject[name="Subject"] option');
*/

$header = array(
	'Content-Length: ', 
	'Host: www.schedule.msu.edu',
	'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	'Accept-Language: en-US,en;q=0.5', 
	'Accept-Encoding: gzip, deflate', 
	'Connection: keep-alive', 
	'Content-Type: application/x-www-form-urlencoded',
	'Referer: http://www.schedule.msu.edu/',
	'Cookie: __utma=51441333.920976852.1362401593.1362401593.1362401593.1; __utmz=51441333.1362401593.1.1.utmcsr=am.etwebapp.com|utmccn=(referral)|utmcmd=referral|utmcct=/view_college.php; ASPSESSIONIDSQSBDRBT=JIHFHDHDHOKOMKJOLHGPILCO; BIGipServerscheduleofcourses=1493278730.26407.0000'
);

#echo '<p>' . count($options) . ' Options found</p>';

$i = 0;
$subject = $_GET['subject'];
#foreach ($options as $opt) {
	#$subject = $opt->value;
	
	$post = "Semester=US131132summer+2013&POST=Y&Button=&Online=&Subject=$subject&CourseNumber=*&Instructor=ANY&StartTime=0600&EndTime=2350&OnBeforeDate=&OnAfterDate=&Sunday=Su&Monday=M&Tuesday=Tu&Wednesday=W&Thursday=Th&Friday=F&Saturday=Sa&OnCampus=Y&OffCampus=Y&OnlineCourses=Y&StudyAbroad=Y&MSUDubai=Y&OpenCourses=A&AllOnePage=Y&Submit=Search+for+Courses";
	
	$header[0] = 'Content-Length: ' . strlen($post);
	
	$ch = curl_init();
	$ch.curl_setopt($ch, CURLOPT_URL, 'http://www.schedule.msu.edu/searchResults.asp');
	$ch.curl_setopt($ch, CURLOPT_HEADER, false);
	$ch.curl_setopt($ch, CURLOPT_POST, true);
	$ch.curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$ch.curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$ch.curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$content = curl_exec($ch);
	
	$err = curl_errno($ch);
	if ($err != 0) {
		echo "<p>Subject $subject... Error: $err</p>\n";
		curl_close($ch);
		break;
	} else {
		echo "<h4>Subject: $subject</h4>\n";
		$course_count = parse_courses($content, $college_id, $subject);
		echo "<h4>Total Courses: $course_count</h4>\n";
		echo "<br/>\n";
		#echo "<p>Subject $subject... Success</p>";
	}
	
	curl_close($ch);
	
	ob_end_flush();
#}

#ob_end_clean();

function parse_courses($content, $college_id, $department_code) {
	$html = new simple_html_dom();
	$html->load($content);
	
	echo "<table class=\"Results\">\n";

	echo "<tr>
		<th>department_code</th>
		<th>course_code</th>
		<th>section_code</th>
		<th>name</th>
		<th>start_time</th>
		<th>end_time</th>
		<th>start_date</th>
		<th>end_date</th>
		<th>min_credits</th>
		<th>max_credits</th>
		<th>online</th>
		<th>monday</th>
		<th>tuesday</th>
		<th>wednesday</th>
		<th>thursday</th>
		<th>friday</th>
		<th>saturday</th>
		<th>sunday</th>
		</tr>\n";
		
	$count = 0;
	$table = $html->find('table[summary="subject"]', 2);
	while ($table != null) {
	
		$rows = $table->find('tr');
		
		if ($rows == null || count($rows) < 3) {
			$table = $table->next_sibling();
			continue;
		}
		
		#echo '<p>[1]</p>';
		
		$course_code = $rows[0]->find('a[title="Click for Course Description"]', 0)->innertext;
		if ($course_code == null) {
			$table = $table->next_sibling();
			continue;
		}
		$arr = explode('&nbsp;', $course_code);
		foreach ($arr as $str) {
			if (ereg('^[0-9]+[a-zA-Z]?$', $str)) {
				$course_code = $str;
				break;
			}
		}
		if (is_string($course_code)) {
			$course_code = escape_data($course_code);
		} else {
			$table = $table->next_sibling();
			continue;
		}
		
		#echo "<p>[2]=$course_code</p>";
		
		$course_name = $rows[0]->find('td', 1)->children(0)->innertext;
		$course_name = escape_data($course_name);
		
		#echo "<p>[3]=$course_name</p>";
		
		$row = $rows[2];
		while ($row != null) {
			if (!isset($row->class)) {
				$row = $row->next_sibling();
				if (!$row) {
					break;
				}
			}
			
			$section_a = $row->find('td[headers="Section"] h3 a', 0);
			if ($section_a) {
				# Parse section code
				$section_code = escape_data($section_a->innertext);
				
				# Books / Required Materials
				$book_id = $section_a->onclick;
				$book_id = explode("'", $book_id);
				$book_id = $book_id[1];
			} else {
				$section_code = '';
			}
			
		#echo "<p>[2-1]=$section_code</p>";
		
			# Parse credits
			$credits = $row->find('td[headers="Credits"]', 0);
			if ($credits) {
				$credits = $credits->innertext;
				$credits = substr($credits, 0, strpos($credits, '&'));
				if (strpos($credits, '-')) {
					$credits = explode('-', $credits);
					$min_credits = $credits[0];
					$max_credits = $credits[1];
				} else {
					$min_credits = $max_credits = $credits;
				}
			} else {
				$min_credits = $max_credits = 'null';
			}
			
		#echo "<p>[2-2]=$min_credits-$max_credits</p>";
		
			# Parse days
			$days = $row->find('td[headers="Days"]', 0)->innertext;
			$monday = ereg('M', $days) ? 'true' : 'false';
			$tuesday = ereg('Tu', $days) ? 'true' : 'false';
			$wednesday = ereg('W', $days) ? 'true' : 'false';
			$thursday = ereg('Th', $days) ? 'true' : 'false';
			$friday = ereg('F', $days) ? 'true' : 'false';
			$saturday = ereg('Sa', $days) ? 'true' : 'false';
			$sunday = ereg('Su', $days) ? 'true' : 'false';
			
		#echo "<p>[2-3]=$days</p>";
		
			# Parse times
			$times = $row->find('td[headers="Times"]', 0)->innertext;
			$times = substr($times, 0, strpos($times, '&'));
			if (strlen($times) > 0) {
				$times = preg_split('(:|( - )| )', $times);
				
				$hours = $times[0];
				if ($hours == '12')
					$hours = '0';
				$mins = $times[1];
				if ($times[2] == 'PM')
					$hours += 12;
					
				$start_time = '\'' . $hours . ':' . $mins . '\'';
				
				$hours = $times[3];
				if ($hours == '12')
					$hours = '0';
				$mins = $times[4];
				if ($times[5] == 'PM')
					$hours += 12;
				
				$end_time = '\'' . $hours . ':' . $mins . '\'';
					
			} else {
				$start_time = $end_time = 'null';
			}
			
		#echo "<p>[2-4]=$start_time-$end_time</p>";
		
			# Online?
			$location = $row->find('td[headers="Building"]', 0)->innertext;
			if ($location == 'Online&nbsp;') {
				$online = 'true';
				$oncampus = 'false';
			} else {
				$online = 'false';
				$oncampus = 'true';
			}
			
			# Parse dates
			$row = $row->next_sibling();
			if ($row && !isset($row->class)) {
				$dates = $row->children(2)->innertext;
				$dates = substr($dates, 0, strpos($dates, '<'));
				$dates = explode(' - ', $dates);
				
				$start_date = explode('/', $dates[0]);
				$start_date = '\'' . $start_date[2] . '-' . $start_date[0] . '-' . $start_date[1] . '\'';
				
				$end_date = explode('/', $dates[1]);
				$end_date = '\'' . $end_date[2] . '-' . $end_date[0] . '-' . $end_date[1] . '\'';
			} else {
				$start_date = $end_date = 'null';
			}
			
			echo "<tr>
				<td>$department_code</td>
				<td>$course_code</td>
				<td>$section_code</td>
				<td>$course_name</td>
				<td>$start_time</td>
				<td>$end_time</td>
				<td>$start_date</td>
				<td>$end_date</td>
				<td>$min_credits</td>
				<td>$max_credits</td>
				<td>$online</td>
				<td>$monday</td>
				<td>$tuesday</td>
				<td>$wednesday</td>
				<td>$thursday</td>
				<td>$friday</td>
				<td>$saturday</td>
				<td>$sunday</td>
				</tr>\n";
				
			$result = mysql_query(
				"INSERT INTO Courses ( 
					college_id, 
					department_code, 
					course_code, 
					name, 
					section_code, 
					offered_online, 
					offered_oncampus, 
					start_time, 
					end_time, 
					start_date, 
					end_date, 
					min_credits, 
					max_credits, 
					monday, 
					tuesday, 
					wednesday, 
					thursday, 
					friday, 
					saturday, 
					sunday 
				) 
				VALUES ( 
					$college_id, 
					'$department_code', 
					'$course_code', 
					'$course_name', 
					'$section_code', 
					$online, 
					$oncampus, 
					$start_time, 
					$end_time, 
					$start_date, 
					$end_date, 
					$min_credits, 
					$max_credits, 
					$monday, 
					$tuesday, 
					$wednesday, 
					$thursday, 
					$friday, 
					$saturday, 
					$sunday
				)"
			);
			if (!$result) {
				trigger_error('Failed to insert course: ' . mysql_error(), E_USER_ERROR);
			} else {
				$course_id = mysql_insert_id();
			}
			
			$book_data = parse_books($book_id, $course_id);
				
			if ($book_data) {
				echo "<tr>
					<td></td>
					<td colspan=\"18\">Required Material: $book_data[0] - $book_data[1] - $book_data[2] - $book_data[3]</td>
					</tr>\n";
			}
			
			$row = $row->next_sibling();
			
		#echo '<p>[2-5]</p>';
		
			$count++;
		}
		
		$table = $table->next_sibling('table[summary="subject"]');
		#echo '<p>[4]</p>';
		
	}
	
	echo "</table>\n";
	
	$html->clear();
	
	return $count;
}

function parse_books($id, $course_id) {
	$html = new simple_html_dom();
	$html->load_file('http://www.schedule.msu.edu/SctnDates.asp?SctnID=' . $id);
	
	$book = null;
	$table = $html->find('table', 0)->find('table', 1);
	if ($table) {
		$row = $table->find('tr', 4);
		if ($row && eregi('title', $row->children(1)->innertext)) {
			$book_title = "'" . escape_data($row->children(3)->innertext) . "'";
			
			$row = $row->next_sibling();
			$book_author = $row->children(3)->innertext;
			if (!$book_author) 
				$book_author = 'null';
			else
				$book_author = "'" . escape_data($book_author) . "'";
			
			$row = $row->next_sibling();
			$book_isbn = $row->children(3)->innertext;
			if (!$book_isbn)
				$book_isbn = 'null';
			else 
				$book_isbn = "'$book_isbn'";
			
			$row = $row->next_sibling()->next_sibling();
			$book_edition = $row->children(3)->innertext;
			if ($book_edition) {
				if (preg_match('/[0-9]+/', $book_edition, $matches)) {
					$book_edition = $matches[0];
				} else {
					$book_edition = 'null';
				}
			} else {
				$book_edition = 'null';
			}
			
			$result = mysql_query(
				"INSERT IGNORE INTO Books ( 
					title, 
					author, 
					isbn, 
					edition 
				) 
				VALUES ( 
					$book_title, 
					$book_author, 
					$book_isbn, 
					$book_edition 
				)"
			);
			if (!$result) {
				trigger_error('Failed to insert book: ' . mysql_error(), E_USER_ERROR);
			} else {
				$book_id = mysql_insert_id();
			}
			
			$result = mysql_query(
				"INSERT INTO Book_Refs (
					book_id, 
					course_id 
				) 
				VALUES ( 
					$book_id, 
					$course_id 
				)"
			);
			if (!$result) {
				trigger_error('Failed to insert book ref: ' . mysql_error(), E_USER_ERROR);
			}
			
			$book = array($book_title, $book_author, $book_isbn, $book_edition);
		}
	}
	
	$html->clear();
	
	return $book;
}

?>