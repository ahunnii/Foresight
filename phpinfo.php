<!DOCTYPE html>
<html>
<head>
	<meta encoding="utf-8"/>
	<title>PHP TESTING</title>
</head>

<body>
	<?php # Kyle L. Oswald 9/20/12
		$file = $_SERVER["PHP_SELF"];
		$user = $_SERVER["HTTP_USER_AGENT"];
		$address = $_SERVER["REMOTE_ADDR"];
		
		echo "<p>SERVER_NAME: {$_SERVER['SERVER_NAME']}</p>\n";
		
		echo "<p>HTTP_HOST: {$_SERVER['HTTP_HOST']}</p>\n";
		
		# print file
		echo "<p>You are running the file <b>$file</b>.</p>\n";
		
		# print user information
		echo "<p>You are viewing this page using:<br/><b>$user</b><br/>
			from the IP address:<br/><b>$address</b></p>\n";
		
		# use single quotes to avoid overhead from parsing
		# to find embedded variables
		$city = 'Auburn';
		$state = 'Michigan';
		
		# String functions: strlen($str), strtolower(), strtoupper(), ucfirst(), ucwords()
		$city = strtoupper($city);
		
		# the period (.) character can be used for string concatenation
		$address = $city . ', ' . $state;
		
		echo "<p>I live in <b>$address</b>.<p>\n";
		
		# Number functions: round(number num, number dec), number_format(number num, number dec)
		$lowFloat = 5.2;
		$highFloat = 7.8345;
		
		$lowFloat = round($lowFloat);
		
		echo "<br/>\n<p>The low number equals $lowFloat</p>\n\n";
		
		$highFloat = floor($highFloat);
		
		echo "<p>The high number equals $highFloat</p>\n";
		
		
		# Constants have special declaration syntax
		define ('TODAY', 'Friday');
		
		echo "<br/>\n<p>Today is " . TODAY . "</p>\n";
		
		# PHP default constants: PHP_VERSION, PHP_OS, SID (session ID)
		
	?>
</body>

</html>