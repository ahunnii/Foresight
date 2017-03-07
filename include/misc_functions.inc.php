<?php 

function ellipsize($string, $length, $end='...') {
  if (strlen($string) > $length) {
	$length -=  strlen($end); 
	$string  = substr($string, 0, $length);
	$string .= $end; 
  }
  return $string;
}

# Enable string embedded function calls, Ex. ( "blah blah {$ellipsize('a string', 3')} blah" )
$ellipsize = 'ellipsize';

function cURLcheckBasicFunctions() {
  if( !function_exists("curl_init") &&
      !function_exists("curl_setopt") &&
      !function_exists("curl_exec") &&
      !function_exists("curl_close") ) 
	return false;
  else 
	return true;
} 

?>