<?php
	$page_title = 'Index';

	$on_page_load = 'onPageLoaded()';
	
	
	$header_elements = array(
		'<script type="text/javascript" src="JavaScript/autocomplete.js"></script>',
		'<script type="text/javascript" src="JavaScript/ajax_functions.js"></script>',
		'<script type="text/javascript" src="JavaScript/GeoDistance.js"></script>',
		'<link rel="stylesheet" type="text/css" href="style/index_styles.css"/>',
		'<link rel="stylesheet" type="text/css" href="style/autocomplete.css"/>',
		'<script type="text/javascript" src="JavaScript/PanelSlider.js"></script>',
		'<link rel="stylesheet" type="text/css" href="style/colorbox.css"/>',
		'<script type="text/javascript" src="JavaScript/jquery-colorbox-min.js"></script>'
	);
	
	include ('include/masthead.inc.html');
?>

<script type="text/javascript">
	var calcSlider = new PanelSlider();
	
	var calcdata = new Object(); // stores data between frames
	var expenses = new Object(); // stores key-value pairs of expenses
	
	//TopUp.images_path = 'JavaScript/topup/images/top_up/';
	//TopUp.players_path = 'JavaScript/topup/players/';
	
	function onPageLoaded() {
		$('.noscript').remove();
		calcSlider.initialize($('div#QuickCalcSlider')[0]);
		
		$('button.nextButton').click(function() { calcSlider.nextFrame(); });
		$('button.backButton').click(function() { calcSlider.previousFrame(); });
		$('button.redoButton').click(function() { 
			calcdata = new Object();
			expenses = new Object();
			calcSlider.toStart(); 
		});
		
		$('#GalleryTable a').colorbox({
			rel			: 'group1',
			fixed		: true,
			width		: '75%',
			height		: '75%',
		});
		
		//console.log('TopUp: %o', TopUp);
	}
</script>

<div id="IndexWrapper">
	<div id="BlurbDiv" align="center">
		<h1>Welcome to ForeSight!</h1>
		<br>
		<div id="placeholder">
			<?php 
				do {
					$result = mysql_query(
						"SELECT * 
						FROM Campus_Images 
						WHERE _id >= FLOOR( RAND() * ( SELECT MAX(_id) ) ) 
						LIMIT 0, 12"
					);
					
					#echo '<h3>' . mysql_error() . '</h3>';
					#exit();
				} while (mysql_num_rows($result) < 12);
				
				echo "<table id=\"GalleryTable\">\n";
				
				$x = 0;
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					if ($x++ % 4 == 0) {
						echo '<tr>';
					}
					echo "<td><a class=\"top_up\" href=\"Files/Campus_Images/{$row['filename']}.jpg\"><img src=\"Files/Campus_Images/{$row['filename']}.jpg\"/></a></td>\n";
					if ($x % 4 == 0) {
						echo '</tr>';
					}
				}
				
				echo "</table>\n";
				
				mysql_free_result($result);
			?>
		</div>
		 
		<div align="left">
			<br>
			<p>
			To get started, register your account, or if you want to just make a fast 
			calculation, try our quick calculator! If you want to get the most out of 
			the site, be sure to <a href="browse_colleges.php">add colleges to your profile</a>. 
			Saved colleges can be viewed from your profile under <a href="user_colleges.php">My Colleges</a>. 
			You can also create a course schedule for any of your saved colleges to increase accuracy of 
			expense calculations for materials like textbooks and commute to and from campus.
			</p>
		</div>
	</div>

	<div id="QuickCalc">
		<p class="noscript">JavaScript Must Be Enabled To Utilize The Quick-Calculator</p>
		
		<h4 style="font-style: italic; padding-bottom: 5px;">Quick Calculator</h4>
			
	<div id="QuickCalcSlider" class="PanelSlider">

	<div id="CalcSelectCollege" class="calcDiv" onFrameInit="CalcSelectCollege_onFrameInit" 
		onFrameLoad="CalcSelectCollege_onFrameLoad" onFrameExit="CalcSelectCollege_onFrameExit">
		<script type="text/javascript">
			function CalcSelectCollege_onFrameInit() {
				$('#college_name_input').autocomplete({
					serviceUrl: '/requests/get_colleges_by_name.php',
					paramName: 'college_name',
					appendTo: $('#CalcSelectCollege .suggestion-div'),
					minChars: 3,
					maxHeight: 200
				});
			}
			
			function CalcSelectCollege_onFrameLoad() {
				if (calcdata.college) {
					$('#college_name_input').attr('value', calcdata.college.collegeInfo.name);
				} else {
					$('#college_name_input').attr('value', '');
				}
				
				if (typeof calcdata.stateResident === 'undefined') {
					calcdata.stateResident = true;
					$('input[type="radio"][name="resident_group"][value="1"]').attr('checked', 'checked');
				}
			}
			
			function CalcSelectCollege_onFrameExit() {
				var xmlhttp = createXMLHttpRequest();
				
				if (getCollegeInfo(xmlhttp) && getCollegeLocation(xmlhttp)) {
					return true;
				} else {
					return false;
				}
			}
			
			function getCollegeInfo(xmlhttp) {
				if (!xmlhttp)
					xmlhttp = createXMLHttpRequest();
				
				var collegeName = $('#college_name_input').attr('value');
					
				xmlhttp.open('GET', 'http://am.etwebapp.com/requests/get_college_info.php?college_name=' + 
					encodeURIComponent(collegeName), false);
				
				xmlhttp.send();
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					var json = xmlhttp.responseText;
					if (json != -1) {
						calcdata.college = $.parseJSON(json);
						$('#CalcSelectCollege p.Error').css('visibility', 'hidden');
						return true;
					} else {
						$('#CalcSelectCollege p.Error').css('visibility', 'visible');
					}
				}
				
				return false;
			}
			
			function getCollegeLocation(xmlhttp) {
				if (!xmlhttp)
					xmlhttp = createXMLHttpRequest();
			
				xmlhttp.open('GET', 'http://am.etwebapp.com/requests/get_loc_geocode.php?' + 
					'adminDistrict=' + calcdata.college.collegeInfo.state_abbreviation +
					'&locality=' + encodeURIComponent(calcdata.college.collegeInfo.city) +
					'&postalCode=' + calcdata.college.collegeInfo.zipcode +
					'&addressLine=' + encodeURIComponent(calcdata.college.collegeInfo.address), 
					false);
				
				xmlhttp.send();
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					var json = xmlhttp.responseText;
					if (json != -1) {
						calcdata.college.location = $.parseJSON(json);
						return true;
					}
				}
				
				return false;
			}
			
			function CalcSelectCollege_onRadioClick(radio) {
				if (radio.value == '1') {
					calcdata.stateResident = true;
				} else {
					calcdata.stateResident = false;
				}
			}
		</script>
		
		<h4>Select a College</h4>
		
		<br/>
		
		<span>
		<input type="text" title="College or University" id="college_name_input" name="college_name" size="30" maxLength="80"/>
		<p class="Error" style="visibility: hidden;">Invalid College Name</p>
		</span>
		<div class="suggestion-div"></div>
		
		<p class="fieldDesc">
		Enter the name of the college you are seeking to attend, lowercase or uppercase 
		letters are acceptable. 
		</p>
		
		<br/>
		
		<p>Are you a state resident?</p>
		<span>
		<p>Yes</p>
		<input type="radio" name="resident_group" value="1" onclick="CalcSelectCollege_onRadioClick(this)" checked="checked"/>
		<p>No</p>
		<input type="radio" name="resident_group" value="2" onclick="CalcSelectCollege_onRadioClick(this)"/>
		</span>
		<p class="fieldDesc">
		State residents often qualify for lower in-state tuition rates and state education grants. 
		Most states have established residency requirements designed to prevent out-of-state students 
		who become residents incidental to their education from qualifying.
		</p>
		
		
		<button type="button" class="nextButton" value="Next">Next</button>
	</div>
	
	<div id="CalcSelectDegree" class="calcDiv" onFrameInit="CalcSelectDegree_onFrameInit" 
		onFrameLoad="CalcSelectDegree_onFrameLoad" onFrameExit="CalcSelectDegree_onFrameExit">
		<script type="text/javascript">
			function CalcSelectDegree_onFrameInit() {
			
			}
			
			function CalcSelectDegree_onFrameLoad() {
				var select = $('#degree_selector');
				select.empty();
				select = select[0];
				
				var degree;
				for (var i = 0, len = calcdata.college.offeredDegrees.length; i < len; i++) {
					degree = calcdata.college.offeredDegrees[i];
					
					var years = 0;
					switch (degree.level) {
						case 'Associates':
							years = 2;
							break;
						case 'Bachelors':
							years = 4;
							break;
						case 'Masters':
							years = 6;
							break;
						case 'Doctorate':
							years = 8;
							break;
						default:
							years = 2;
							break;
					}
					
					var option = document.createElement('OPTION');
					option.value = years;
					option.appendChild(document.createTextNode(degree.name + ' - ' + degree.level));
					
					select.appendChild(option);
				}
			}
			
			function CalcSelectDegree_onFrameExit() {
				var select = $('#degree_selector')[0];
				
				calcdata.years = select.options[select.selectedIndex].value;
				
				if (calcdata.stateResident)
					expenses.Tuition = calcdata.years * calcdata.college.collegeInfo.instate_tuition;
				else 
					expenses.Tuition = calcdata.years * calcdata.college.collegeInfo.outstate_tuition;
				
				return true;
			}
		</script>
		
		<h4>Select Your Degree</h4>
		
		<br/>
		
		<select id="degree_selector">
		
		</select>
		
		<p class="fieldDesc">
		Your degree will determine how long you must attend school 
		and what courses you must take to graduate.
		</p>
		
		<button type="button" class="backButton" value="Back">Back</button>
		<button type="button" class="nextButton" value="Next">Next</button>
	</div>

	<div id="CalcSelectLocation" class="calcDiv" onFrameInit="CalcSelectLocation_onFrameInit" 
		onFrameLoad="CalcSelectLocation_onFrameLoad" onFrameExit="CalcSelectLocation_onFrameExit">
		<script type="text/javascript">
			function CalcSelectLocation_onFrameInit() {
				
			}
			
			function CalcSelectLocation_onFrameLoad() {
				$('#CalcAddressDiv .Error').text(''); // clear error text
				
				if (typeof calcdata.addressInfo === 'undefined') {
					calcdata.addressInfo = new Object();
					calcdata.addressInfo.onCampus = true;
					$('#CalcAddressDiv').hide();
					$('#CalcAddressDormDiv').show();
				} else if (calcdata.addressInfo.onCampus) {
					$('#CalcAddressDiv').hide();
					$('#CalcAddressDormDiv').show();
				} else {
					$('#CalcAddressDiv').show();
					$('#CalcAddressDormDiv').hide();
				}
			}
			
			function CalcSelectLocation_onFrameExit() {
				if (!calcdata.addressInfo.onCampus) { // validate address info
					$('#CalcAddressDiv .Error').text(''); // clear error text
					
					var valid = true;
					
					// validate address
					var address = $('#address_input').attr('value');
					if (address == null || address.length == 0) {
						valid = false;
						$('#address_input').next('.Error').text('This field is required');
					} else {
						calcdata.addressInfo.address = address;
					}
					
					// validate city
					var city = $('#city_input').attr('value');
					if (city == null || city.length == 0) {
						valid = false;
						$('#city_input').next('.Error').text('This field is required');
					} else {
						calcdata.addressInfo.city = city;
					}
					
					// validate zip code
					var zip = $('#zip_input').attr('value');
					if (zip == null || zip.length == 0) { // empty
						valid = false;
						$('#zip_input').next('.Error').text('This field is required');
					} else if (!zip.match(/^[0-9]+$/)) { // format
						valid = false;
						$('#zip_input').next('.Error').text('Invalid format');
					} else {
						calcdata.addressInfo.zipcode = zip;
					}
					
					// retrieve state 
					var state_selector = $('#state_selector')[0];
					var state = state_selector.options[state_selector.selectedIndex].value;
					
					if (valid) { // calc distance to college
						var xmlhttp = createXMLHttpRequest();
						xmlhttp.open('GET', 
							'http://am.etwebapp.com/requests/get_loc_geocode.php?' + 
							'&adminDistrict=' + state + 
							'&locality=' + encodeURIComponent(city) + 
							'&postalCode=' + encodeURIComponent(zip) + 
							'&addressLine=' + encodeURIComponent(address),
							false);
						xmlhttp.send();
						if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
							var json = xmlhttp.responseText;
							if (json != '-1') {
								calcdata.userAddress = $.parseJSON(json);
								
								var userGeo = calcdata.userAddress.resourceSets[0].resources[0].point.coordinates;
								var collegeGeo = calcdata.college.location.resourceSets[0].resources[0].point.coordinates;
								var dist = GeoDistance.kmDist(
									userGeo[0], userGeo[1], 
									collegeGeo[0], collegeGeo[1]
									);
								dist = GeoDistance.toMiles(dist);
								
								var round = dist * 2;
								var mpg = 25;
								
								expenses.Commute = calcdata.years * 250 * ((round / mpg) * calcdata.college.collegeInfo.state_avg_gas_price);
								
								if (typeof expenses['Dorm Costs'] !== 'undefined')
									delete expenses['Dorm Costs'];
									
								return true;
							} else {
								return false;
							}
						}
						
						return false;
					} else {
						return false;
					}
				} else { // add dorm costs
					if (typeof expenses.Commute !== 'undefined')
						delete expenses.Commute;
						
					expenses['Dorm Costs'] = calcdata.college.collegeInfo.dorm_cost * calcdata.years;
					return true;
				}
			}
			
			function CalcSelectLocation_onRadioClick(radio) {
				switch (radio.value) {
					case '1': // Yes
						calcdata.addressInfo.onCampus = true;
						$('#CalcAddressDiv').slideUp(250, function() { $('#CalcAddressDormDiv').slideDown(250); });
						//$('#CalcAddressDiv').css('visibility', 'hidden');
						break;
					case '2': // No
						calcdata.addressInfo.onCampus = false;
						$('#CalcAddressDormDiv').slideUp(250, function() { $('#CalcAddressDiv').slideDown(250); });
						//$('#CalcAddressDiv').css('visibility', 'visible');
						break;
				}
			}
			
			function onStateSelected(pList) {
				calcdata.addressInfo.stateId = pList.options[pList.selectedIndex].value;
			}
		</script>
		
		<h4>Will You Stay On Campus?</h4>
		
		<br/>
		
		<span>
		<p>Yes</p>
		<input type="radio" name="group1" value="1" onclick="CalcSelectLocation_onRadioClick(this)" checked="checked"/>
		<p>&nbsp;No</p>
		<input type="radio" name="group1" value="2" onclick="CalcSelectLocation_onRadioClick(this)"/>
		</span>
		
		<br/>
		
		<div id="CalcAddressDormDiv">
			<p class="fieldDesc">
			Dorm expenses will be factored into your calculation.
			</p>
		</div>
		
		<div id="CalcAddressDiv">
		<table>
			<tr>
			<td><p>Street Address</p></td>
			<td><input id="address_input" type="text" size="30" maxLength="80"/><p class="Error"></p></td>
			</tr>
			
			<tr>
			<td><p>City</p></td>
			<td><input id="city_input" type="text" size="30" maxLength="80"/><p class="Error"></p></td>
			</tr>
			
			<tr>
			<td><p>State</p></td>
			<td>
			<select id="state_selector">
				<?php # Enumerate through states to populate option tags
					$result = mysql_query('SELECT abbreviation, name FROM States ORDER BY name');
					
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<option value=\"$row[0]\">$row[1]</option>";
					}
					
					mysql_free_result($result);
				?>
			</select>
			</td>
			</tr>
			
			<tr>
			<td><p>Zipcode</p></td>
			<td><input id="zip_input" type="text" size="10" maxlength="10"/><p class="Error"></p></td>
			</tr>
		</table>
		
		<p class="fieldDesc">
		Your place of residence while attending college. This information is used 
		to estimate the cost of commuting between home and campus.
		</p>
		
		</div>
		
		<button type="button" class="backButton" value="Back">Back</button>
		<button type="button" class="nextButton" value="Next">Next</button>
	</div>
	
	<div id="FinancialAid" class="calcDiv" onFrameInit="FinancialAid_onFrameInit"
		onFrameLoad="FinancialAid_onFrameLoad" onFrameExit="FinancialAid_onFrameExit">
		<script type="text/javascript">
			function FinancialAid_onFrameInit() {
				$('input[name="financial_situation_1"]').click(financial_situation_1);
			}
			
			function FinancialAid_onFrameLoad() {
				if (typeof expenses['Financial Aid'] === 'undefined') {
					expenses['Financial Aid'] = 0;
					$('input[name="financial_situation_1"][value="1"]').attr('checked', 'checked');
					$('input[name="financial_situation_2"][value="1"]').attr('checked', 'checked');
					$('#faidtypewrapper').show();
				}
			}
			
			function FinancialAid_onFrameExit() {
				var period = $('input[name="financial_situation_1"]:checked').attr('value');
				if (period == '1') { // full time
					var dependent = $('input[name="financial_situation_2"]:checked').attr('value');
					if (dependent == '1') { // yes 
						expenses['Financial Aid'] = -13100 * calcdata.years;
					} else { // no
						expenses['Financial Aid'] = -11700 * calcdata.years;
					}
				} else { // part time
					expenses['Financial Aid'] = -5800 * calcdata.years;
				}
				return true;
			}
			
			function financial_situation_1() {
				if ($(this).attr('value') == '1') {
					$('#faidtypewrapper').slideDown();
				} else {
					$('#faidtypewrapper').slideUp();
				}
			}
		</script>
			<h4> Financial Aid </h4>
			<br/>			
			<p> Check the box that matches your situation </p>
			<br/>
			<div id="FinancialAid">
			
			<p class="fieldDesc">
			Enrollment Period
			</p>
			<span>
			<input type="radio" name="financial_situation_1" value="1" checked="checked"/>
			<p> Full Time / Full Year </p>
			</span>
			
			<span>
			<input type="radio" name="financial_situation_1" value="2"/>
			<p> Part Time or Part Year </p>
			</span>
			
			<div id="faidtypewrapper">
			<br/>
			<p class="fieldDesc">
			Dependancy Status
			</p>
			<span>
			<input type="radio" name="financial_situation_2" value="1" checked="checked"/>
			<p> Dependent </p>
			
			<input type="radio" name="financial_situation_2" value="2"/>
			<p> Independent </p>
			</span>
			</div>
			
			<br/>
			<p>*These are averages</p>
			</div>
			
		<button type="button" class="backButton" value="Back">Back</button>
		<button type="button" class="nextButton" value="Next">Next</button>
	</div>

	<div id="CalcCostReport" class="calcDiv" onFrameInit="CalcCostReport_onFrameInit" 
		onFrameLoad="CalcCostReport_onFrameLoad" onFrameExit="CalcCostReport_onFrameExit">
		<script type="text/javascript">
			function CalcCostReport_onFrameInit() {
			
			}
			
			function CalcCostReport_onFrameLoad() {
				var table = $('#CalcCostReportExpenses');
				table.empty();
				table = table[0];
				
				var total = 0;
				for (var key in expenses) {
					var row = document.createElement('TR');
					row.className = 'ExpensesRow';
					
					var keyCell = document.createElement('TD');
					keyCell.className = 'ExpensesKeyCell';
					var keyText = document.createTextNode(key);
					keyCell.appendChild(keyText);
					row.appendChild(keyCell);
					
					var valCell = document.createElement('TD');
					valCell.className = 'ExpensesValCell';
					var valText;
					var val = expenses[key];
					if (typeof val === 'number') {
						valText = document.createTextNode('$' + val.toFixed(2));
						total += val;
					} else {
						valText = document.createTextNode(val);
					}
					valCell.appendChild(valText);
					row.appendChild(valCell);
					
					table.appendChild(row);
				}
				
				var totalRow = document.createElement('TR');
				totalRow.className = 'ExpensesTotalRow';
				
				keyCell = document.createElement('TD');
				keyCell.className = 'ExpensesKeyCell';
				keyCell.appendChild(document.createTextNode('Total:'));
				totalRow.appendChild(keyCell);
				
				valCell = document.createElement('TD');
				valCell.className = 'ExpensesValCell';
				valCell.appendChild(document.createTextNode('$' + total.toFixed(2)));
				totalRow.appendChild(valCell);
				
				table.appendChild(totalRow);
			}
			
			function CalcCostReport_onFrameExit() {
				return true;
			}
		</script>
		
		<h4>Cost Report</h4>
		
		<br/>
		
		<table id="CalcCostReportExpenses">
		
		</table>
		
		<button type="button" class="backButton" value="Back">Back</button>
		<button type="button" class="redoButton" value="Redo">Redo</button>
	</div>
	
	</div> <!-- End #QuickCalcSlider.PanelSlider -->
</div>

	<!--<div id="StatsData" align="center">
		<p>Quick Stats/ Data Visualization</p>

		<p>------------------------------<WBR>------------</p>

		<p>------------------------------<WBR>-------------</p>
	</div>
-->
<?php
	include('include/footer.inc.html');
?>