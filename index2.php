<?php #Kyle L. Oswald 9/23/12
	$page_title = 'Index';
	$header_elements = array('<script src="http://localhost/JavaScript/SimpleCalc.js"></script>');
	include('include/masthead.inc.html');
?>
		<p>
			<a href="email_registration_form.php">Register</a>
			<br/>
			<a href="phpinfo.php">Info</a>
			<br/>
			<a href="browse_colleges.php">Colleges</a>
			<!--
			<a href="http://www.google.com">
				<td style="{position: relative;}">
					<img src="Graphics/button.jpg" style="{position: absolute; display: none; top: 0px; left: 0px; clear: none;}"/>
					<div style="{position: absolute; display: none; clear: none; top: 0px; left: 0px; right: 0px; text-align: center;">Text</div>
				</td>
			</a>
			-->
			<a id="LinkButton" href="http://www.google.com">
				<div id="ImgDiv" style="{position: relative;}">
					<img src="Graphics/button.jpg" style="{position: absolute; display: none; top: 0px; left: 0px; clear: none;}"/>
					<div style="{position: relative; display: none; clear: none; top: -20px; text-align: center;}">Text</div>
				</div>
			</a>
		</p>
<?php
	include('include/footer.inc.html');
?>