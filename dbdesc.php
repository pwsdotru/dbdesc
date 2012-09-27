<?php
/**
 * Script for builde description of database structure
 * Author: Aleksandr Novikov, pwsdotru@gmail.com, http://pws.ru
 */

set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors", 1);
$run_params = array(
    "host" => "localhost",
    "user" => "root",
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
if ( $run_params["base"] == "" ) {
	command_line_banner("Error: You should set database name");
	exit(1);
}
if ( $run_params["out"] == "" ) {
	$run_params["out"] = $run_params["base"].".html";
}

$cn = mysqli_connect($run_params["host"], $run_params["user"], $run_params["password"]);

if ( !$cn ) {
	echo mysqli_error($cn)."\n";
	exit(1);
}
if ( !mysqli_select_db($cn, $run_params["base"]) )  {
	echo mysqli_error($cn)."\n";
	exit(1);
}

$out = fopen($run_params["out"], "w");
if ( !$out ) {
	echo "Can't open file '" . $run_params["out"] . " for output\n";
	exit();
}
$tables = mysqli_query($cn, "SHOW TABLES");
if ( !$tables ) {
	echo "Can't get tables list:" . mysqli_error($cn)."\n";
	exit(1);
}

fwrite($out, "<html>\n<head>\n<title>Database \"" . $run_params["base"] . "\"</title>\n</head>\n");
fwrite($out, "<body>\n<a name=\"top\"></a>\n<h1>Database \"". $run_params["base"] . "\"</h1>\n\n<ul>\n");
$tables_text = "";
while ( $table_info =  mysqli_fetch_array($tables) ) {
	if ( isset($table_info[0]) && $table_info[0] != "" ) {
		$table_name = trim($table_info[0]);
		$table_desc = mysqli_query($cn, "DESC ".$table_name);
		if ( !$table_desc ) {
			echo "Can't get table '" . $table_name . "' info:" . mysqli_error($cn)."\n";
			continue;
		}
		$table_fields = array();
		while ( $fields = mysqli_fetch_assoc($table_desc) ) {
			$table_fields[] = $fields;
		}
		if ( count($table_fields) > 0 ) {
			fwrite($out, buidContextEntry($table_name));
			$tables_text .= buildTableEntry($table_name, $table_fields);
		}
		mysqli_free_result($table_desc);
	}
}
mysqli_free_result($tables);
mysqli_close($cn);
fwrite($out, "</ul>\n".$tables_text);
fwrite($out, "\n</body>\n</html>\n");
fclose($out);
exit();
function command_line_banner( $message = "" ) {
	echo "Script build description of all tables for MySQL database in HTML file\n";
	if ( $message != "" ) {
		echo $message."\n";
	}
	echo "Usage: ".basename(__FILE__)." --host=db_host --user=db_user --password=db_password --base=db_name --out=outputfile.html\n";
}

function buidContextEntry($name) {
	$res = "<li><a href=\"#" . $name . "\">" . $name . "</a>\n - \n</li>\n";
	return $res;
}

function buildTableEntry($name, $fields) {
	$res = "\n<a name=\"" . $name . "\"></a>\n<h3>" . $name . "</h3>\n<p>\n\n</p>\n\n";
	$res .= "<table border=\"1\">\n";
	$res .= "<tr><th>Field</th><th>Type</th><th>Default</th><th>Null</th><th>Key</th><th>Description</th></tr>\n";

	foreach ( $fields as $fld ) {
		$res .= "<tr>\n";
		$res .= "\t<td>".$fld["Field"]."</td>\n";
		$res .= "\t<td>".$fld["Type"]."</td>\n";
		$res .= "\t<td>".($fld["Default"] == "" ? "&nbsp;" : $fld["Default"])."</td>\n";
		$res .= "\t<td>".$fld["Null"]."</td>\n";
		$res .= "\t<td>".($fld["Key"] == "" ? "&nbsp;" : $fld["Key"])."</td>\n";
		$res .= "\t<td>\n\t\t".($fld["Extra"] == "" ? $fld["Field"] : $fld["Extra"])."\n\t</td>\n";
		$res .= "</tr>\n";
	}
	$res .= "</table>\n";
	$res .= "<p><a href=\"#top\">top</a></p>\n";
	return $res;
}
?>
