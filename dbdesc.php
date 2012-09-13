<?php
/**
 * Script for builde description of database structure
 * Author: Aleksandr Novikov, pwsdotru@gmail.com, http://pws.ru
 */

set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors", 1);
$run_params = array(
    "host" => "",
    "user" => "",
    "password" => "",
    "base" => "",
    "out" => "",
);

/*
 * Read configuration from command string
 */
if ( isset($argv) && is_array($argv) && count($argv) > 1 ) {
	foreach ( $argv AS $param ) {
		if ( substr($param, 0, 2) == "--" ) {
			$temp = explode("=", substr($param, 2), 2);
			$param_key = trim($temp[0]);
			if ( isset($temp[1]) && isset($run_params[$param_key]) ) {
				$run_params[$param_key] = trim($temp[1]);
			}
		}
	}
} else {
	command_line_banner();
	exit(1);
}
/*
 * Check configuration
 */
print_r($run_params);

function command_line_banner( $message = "" ) {
	echo "Script build description of all tables for MySQL database in HTML file\n";
	if ( $message != "" ) {
		echo $message."\n";
	}
	echo "Usage: ".basename(__FILE__)." --host=db_host --user=db_user --password=db_password --base=db_name --out=outputfile.html\n";
}

?>
