<?php # Kyle L. Oswald 3/2/13

if (!isset($_SESSION)) {
	session_start();
}

include('include/site_constants.inc.php');
if (!(isset($_SESSION['User_Permission']) 
	&& $_SESSION['User_Permission'] == PERMISSION_ADMIN)) {
	header('Location: http://' . $_SERVER['SERVER_NAME'] . '/index.php');
}

$_SESSION['Return_Url'] = $_SERVER['REQUEST_URI'];

$page_title = 'Admin Panel';

$startup_script = 
<<<EOS
<script type="text/javascript">

	// cached request
	var xmlhttp;
	
	var occupied;
	
	function onPageReady() {
		xmlhttp = createXMLHttpRequest();
		occupied = false;
		
		$('#action_repopulate_fuel').click(function() {
			if (!occupied) {
				occupied = true;
				
				$('#action_repopulate_fuel').next('.state').text('Working');
				
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4) { // request finished
						if (xmlhttp.status == 200 && xmlhttp.responseText == '1') { // success
							$('#action_repopulate_fuel').next('.state').text('Success');
						} else { // fail
							$('#action_repopulate_fuel').next('.state').text('Failed');
						}
						
						occupied = false;
					}
				};
				
				xmlhttp.open('GET', 'requests/repopulate_fuel_prices.php', true);
				xmlhttp.send();
				
			}
		});
		
		$('#actionFillMsuCourses').click(function() { actionFillMsuCourses_onClick(); });
	}
	
</script>
EOS;

$on_page_ready = 'onPageReady()';
	
$header_elements = array(
	'<script type="text/javascript" src="JavaScript/ajax_functions.js"></script>',
	$startup_script);
	
include('include/masthead.inc.html');

?>

<div>

<span>
<p>Repopulate Fuel Prices </p>
<button id="action_repopulate_fuel" type="button">Go</button>
<p class="state">Ready</p>
</span>

<span>
<p>Fill MSU Courses </p>
<button id="actionFillMsuCourses" type="button">Go</button>
<p class="state">Ready</p>
</span>

<script type="text/javascript">
var cache;
var index;
var done;
var que = new Array(
'AAAS', 'ABM', 'ACC', 'ACR', 'ADV', 'AE', 'AEC', 'AEE', 'AESC', 'AFR', 'AL', 'AMS', 'ANP', 'ANR', 'ANS', 'ANT', 'ANTR', 'ANTV', 
'ARB', 'AS', 'ASC', 'ASN', 'AST', 'AT', 'ATD', 'ATL', 'ATM', 'BCH', 'BCM', 'BE', 'BLD', 'BMB', 'BME', 'BOT', 'BS', 'BUS', 'CAS', 'CE', 'CEM', 'CEP', 'CHE', 
'CHS', 'CIC', 'CJ', 'CLA', 'CLS', 'CMB', 'CMBA', 'CMP', 'COM', 'CSD', 'CSE', 'CSP', 'CSS', 'CSX', 'DAN', 'EAD', 'EC', 'ECE', 'ED', 'EEP', 
'EGR', 'EM', 'EMB', 'ENE', 'ENG', 'ENT', 'EPI', 'ES', 'ESA', 'ESL', 'ESP', 'FCE', 'FCM', 'FI', 'FIM', 'FM', 'FMP', 'FOR', 'FRN', 
'FRS', 'FSC', 'FSM', 'FW', 'GBL', 'GEN', 'GEO', 'GLG', 'GPI', 'GRK', 'GRM', 'GSAH', 'GSP', 'GSX', 'GUSP', 'HA', 'HB', 'HDFS', 'HEB', 
'HEC', 'HED', 'HM', 'HNF', 'HRLR', 'HRT', 'HST', 'IAH', 'IDES', 'IDV', 'IM', 'INP', 'INX', 'ISB', 'ISE', 'ISP', 'ISS', 
'ITL', 'ITM', 'JPN', 'JRN', 'KIN', 'LA', 'LAW', 'LB', 'LBS', 'LCS', 'LIN', 'LIR', 'LL', 'LLT', 'LNG', 'LTN', 'MBA', 'MC', 'ME', 'MED', 
'MGT', 'MIC', 'MIGS', 'MKT', 'MMG', 'MS', 'MSC', 'MSE', 'MSM', 'MT', 'MTH', 'MTHE', 'MUS', 'NEU', 'NOP', 'NSC', 'NUR', 'OGR', 'OMM', 'ORO', 
'OSS', 'OST', 'PDC', 'PDI', 'PED', 'PHD', 'PHL', 'PHM', 'PHY', 'PIM', 'PKG', 'PLB', 'PLP', 'PLS', 'PMR', 'PPL', 'PRM', 'PRO', 'PRR', 'PRT', 
'PSC', 'PSL', 'PSY', 'PTH', 'QB', 'RAD', 'RCAH', 'RD', 'REL', 'RET', 'ROM', 'RUS', 'SCM', 'SCS', 'SME', 'SOC', 'SPN', 
'SSC', 'STA', 'STT', 'SUR', 'SW', 'SYS', 'TC', 'TCC', 'TE', 'THR', 'TSM', 'UGS', 'UNIV', 'UP', 'VIPP', 
'VM', 'WRA', 'WS', 'YD', 'ZOL'
);

function actionFillMsuCourses_onClick() {
	if (cache) {
		stopRequests();
		return;
	} else {
		index = 0;
		done = 0;
		cache = new Array();

		$('#actionFillMsuCourses').next('.state').text('%0');
					
		var xmlhttp;
		for (var i = 0; i < 5; i++) {
			cache[i] = xmlhttp = createXMLHttpRequest();
			
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) { // request finished
					if (xmlhttp.status != 200)  { // fail
						$('#actionFillMsuCourses').next('.state').after('<p class="Error">Request Failed: ' + xmlhttp.status + '</p>');
					}
								
					var p = (++done / que.length) * 100;
					p = p.toFixed(0);
					$('#actionFillMsuCourses').next('.state').text('%' + p);
					
					launchNextRequest(this);
				}
			};
			
			launchNextRequest(xmlhttp);
		}
		
		$('#actionFillMsuCourses').text('Stop');
		
		return;
	}
}

function launchNextRequest(xmlhttp) {
	if (index >= que.length) { // done
		$('#actionFillMsuCourses').next('.state').text('Done');
		$('#actionFillMsuCourses').text('Go');
		cache = null;
	} else if (cache) { // launch next request
		xmlhttp.open('GET', 'requests/fill_msu_courses.php?subject=' + que[index++], true);
		xmlhttp.send();
	}
}

function stopRequests() {
	$('#actionFillMsuCourses').next('.state').text('Done');
	for (var xmlhttp in cache) {
		xmlhttp.abort();
	}
	cache = null;
}
</script>

</div>

<?php 

include('include/footer.inc.html');

?>