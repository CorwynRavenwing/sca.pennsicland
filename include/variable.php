<?php
# variable.php: functions regarding the variable object

require_once("connect.php");

function variable_id($variable_name) {
	if ($variable_name == "") {
		return "";
	}
	
	$sql = "
		SELECT variable_id
		FROM land_variable
		WHERE variable_name = '$variable_name'
		LIMIT 1
	";
	
	if (headers_sent()) { print "<!-- variable_id SQL:\n$sql\n-->\n"; }

	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$variable_id = $result['variable_id'];
	} else {
		$variable_id = "[$variable_name]";
	}
	
	mysql_free_result($query);							# delete query object
	
	return $variable_id;
} # end function variable_id

function variable_create($name, $delay, $description = '') {
	$id = variable_id($name);

	if ($id) {
		return $id;
	}

	$sql = "
		INSERT INTO land_variable
			(variable_name, value,     delay,  description,    updated)
		VALUES
			('$name',       'UNKNOWN', $delay, '$description', 0)
	";

	print "<!-- variable_create SQL 1:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	mysql_free_result($query);							# delete query object

	$sql = "SELECT LAST_INSERT_ID() AS id";
	
	print "<!-- variable_create SQL 2:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_array($query)) {
		$id = $result['id'];
	} else {
		$id = 0;
	}
	
	mysql_free_result($query);							# delete query object

	return $id;
} # end function variable_create

function variable_set($name, $value) {
	$sql = "
		UPDATE land_variable
			SET value = '$value'
			  , updated = NOW()
			  , queued = 0
		WHERE variable_name = '$name'
		LIMIT 1
	";

	print "<!-- variable_set SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	return mysql_affected_rows();
} # end function variable_set

function variable_get($name) {
	$sql = "
		SELECT value
		  , (updated + delay < NOW()) AS is_old
		  , (queued > 0) AS is_queued
		FROM land_variable
		WHERE variable_name = '$name'
		LIMIT 1
	";

	print "<!-- variable_get SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

	if ($result = mysql_fetch_assoc($query)) {
		$value = $result['value'];
		if (!$value) {
			$value = "BLANK";
		}
		if ($result['is_old']) {
			$value .= " (*)";
		}
		if ($result['is_queued']) {
			$value .= " (!)";
		}
	} else {
		$value = "[$name] (?)";
	}

	return $value;
} # end function variable_get

function variable_next() {
	$sql = "
		SELECT name
			 , (updated + delay) AS runtime
		FROM land_variable
		WHERE (updated + delay) < NOW()
		  AND queued = 0
		ORDER BY runtime ASC
		LIMIT 1
	";

	print "<!-- variable_next SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

	if ($result = mysql_fetch_assoc($query)) {
		$next_var_name = $result['name'];
		$DEBUG_runtime = $result['runtime'];
		print("DEBUG: variable_next() returned name '$name', runtime '$runtime'<br/>\n");
		variable_queue($next_var_name);
	} else {
		$next_var_name = "";
		print("DEBUG: variable_next() found no matches<br/>\n");
	}

	return $next_var_name;
} # end function variable_next

function variable_queue($name) {
	$sql = "
		UPDATE land_variable
			SET queued = 1
		WHERE variable_name = '$name'
		LIMIT 1
	";

	print "<!-- variable_queue SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	return mysql_affected_rows();
} # end function variable_queue

function variable_calculate($name) {
	switch ($name) {
		case 'aaaa':
			$value = "zzzzz";
			break;

		case 'bbbb':
			$value = "yyyyy";
			break;

		default:
			$value = "UNKNOWN VARIABLE PASSED TO variable_calculate($name)";
			break;
	}

	return $value;
} # end function variable_calculate

function variable_update($name) {
	
	variable_set(
		$name,
		variable_calculate($name)
	);

} # end function variable_update

function variable_cron() {

	variable_update(
		variable_next()
	);

} # end function variable_cron

// Return a list of all variable names
function variable_list() {
	$sql = "SELECT variable_name
		FROM land_variable
		";
	
	print "<!-- variable_list SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$retVal = array();
	
	while ($result = mysql_fetch_assoc($query)) {
		$variable_name = $result['variable_name'];
		
		array_push($retVal, $variable_name);
	}
	
	mysql_free_result($query);							# delete query object

	return $retVal;
} // end function variable_list

function variable_record( $name ) {
	$sql = "SELECT *
		FROM land_variable
		where variable_name = '$name' ";
	
	if (headers_sent()) { print "<!-- variable_record SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$result = mysql_fetch_assoc($query);
	
	mysql_free_result($query);							# delete query object
	
	return $result;
} // end function variable_record

?>