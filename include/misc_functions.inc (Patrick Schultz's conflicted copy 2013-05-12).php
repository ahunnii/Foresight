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

?>