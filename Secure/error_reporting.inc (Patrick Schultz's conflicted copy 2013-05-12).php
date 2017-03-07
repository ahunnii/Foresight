<?php # Kyle L. Oswald 10/11/12
	
	# Set site reporting level
	error_reporting(E_ALL);
	
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
				"\tVariables - " . print_r($e_vars, 1) . "\n" .
				"</div>\n";
		} else {
			echo "<div class=\"error\"> \n" .
				"An error has occurred.\n" . 
				"</div>\n";
		}
		
		error_log($e_message, 1, $log_email);
	}
	
	# Set delegate to receive error notifications
	set_error_handler('handle_exception');
	
?>