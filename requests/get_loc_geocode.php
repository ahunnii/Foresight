<?php # Kyle L. Oswald 02/20/13

# am.etwebapp.com/requests/get_loc_geocode.php?adminDistrict=MI&locality=Auburn&postalCode=48611&addressLine=4409+South+9+Mile+Road

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Secure/error_reporting.inc.php");

DEFINE('BING_MAPS_KEY', 'AuuHwdAEHxI5RwiDGkmqv8Lc9-5_zEDFkXzKArQpDbsOg2YkLJPsZUvj9Evd52EP');
DEFINE('MAX_RESULTS', '1');
	
function fail() {
	echo '-1';
	exit();
}

# State
if (isset($_GET['adminDistrict'])) {
	$adminDistrict = $_GET['adminDistrict'];
	$adminDistrict = trim($adminDistrict);
	if (!eregi('^[a-z]{2}$', $adminDistrict))
		fail();
} else {
	fail();
}

# City
if (isset($_GET['locality'])) {
	$locality = $_GET['locality'];
	$locality = trim($locality);
	if (!eregi('^([a-z]+ ?)+$', $locality))
		fail();
} else {
	fail();
}

# Zipcode
if (isset($_GET['postalCode'])) {
	$postalCode = $_GET['postalCode'];
	$postalCode = trim($postalCode);
	if (!ereg('^[0-9]{4,8}$', $postalCode))
		fail();
} else {
	fail();
}

# Street Address
if (isset($_GET['addressLine'])) {
	$addressLine = $_GET['addressLine'];
	$addressLine = trim($addressLine);
	if (!eregi('^([a-z0-9\.|-]+ ?)+$', $addressLine))
		fail();
} else {
	fail();
}

$query_args = array(
	'countryRegion' => 'US',
	'adminDistrict' => $adminDistrict, 
	'locality' => $locality, 
	'postalCode' => $postalCode,
	'addressLine' => $addressLine,
	'maxResults' => MAX_RESULTS,
	'key' => BING_MAPS_KEY
	);

# Retrieve coords from Bing
$url = 'http://dev.virtualearth.net/REST/v1/Locations?' . http_build_query($query_args);

$ch = curl_init($url);

$result = curl_exec($ch);

curl_close($ch);

echo substr($result, 0, strlen($result) - 1);

?>