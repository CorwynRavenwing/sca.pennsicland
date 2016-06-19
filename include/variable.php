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
		$variable_id = 0;
	}
	
	mysql_free_result($query);							# delete query object
	
	return $variable_id;
} # end function variable_id

function variable_create($name, $delay, $description = '') {
	$id = variable_id($name);

	if ($id) {
		// name already exists
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
	
	# mysql_free_result

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

function variable_delete($name) {
	$id = variable_id($name);

	if (! $id) {
		// name doesn't exist
		return 0;
	}

	$sql = "
		DELETE FROM land_variable
		WHERE variable_name = '$name'
		LIMIT 1
	";

	print "<!-- variable_delete SQL 1:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	# mysql_free_result

	return $id;
} # end function variable_delete

function variable_set($name, $value) {

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
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

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
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

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
	$sql = "
		SELECT variable_name
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
		$next_var_name = $result['variable_name'];
		$DEBUG_runtime = $result['runtime'];
		print("DEBUG: variable_next() returned name '$variable_name', runtime '$runtime'<br/>\n");
		variable_queue($next_var_name);
	} else {
		$next_var_name = "";
		print("DEBUG: variable_next() found no matches<br/>\n");
	}

	return $next_var_name;
} # end function variable_next

function variable_queue($name) {

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
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

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
	switch ($name) {
		case 'aaaa':
			$value = "zzzzz";
			break;

		case 'bbbb':
			$value = "yyyyy";
			break;

	/*

    $count_users         = count_where("user_information");
    $count_logged_on     = current_user_sessions();

    $count_group         = count_where("land_groups");
    $count_group_reg     = count_where("land_groups", "user_id                != 0");
    $count_group_unreg   = count_where("land_groups", "user_id                 = 0");
    $count_group_check   = count_where("land_groups", "status IN ( 2, 3 )
                OR group_name_base = ''
                OR group_soundex = ''
                OR group_metaphone = ''" );
    $count_group_nohist  = "";          # figure out how to count this
    $count_group_bonus   = count_where("land_groups", "bonus_footage          != 0");
    $count_group_compress= count_where("land_groups", "calculated_compression != 0");
    $count_group_notes   = count_where("land_groups", "other_group_information!= '' ");
    $count_admin_notes   = count_where("land_groups", "other_admin_information!= '' ");
    $count_group_kingdom = count_where("land_groups", "exact_land_amount      != 0");
    $count_known_people  = sum_where("land_groups",   "pre_registration_count", "user_id != 0");
	variable_create('count_unknown_people',	3600,	'Campers in unregistered groups');
    $count_unfixed_groups= fix_cooper_data_count();
    $count_orphan_groups = count_where("land_groups", "pre_registration_count > 0 AND user_id = 0");
    $count_people_prereg = count_where("cooper_data", "group_name not like ':%'");

	*/
		default:
			$value = "UNKNOWN VARIABLE PASSED TO variable_calculate($name)";
			break;
	}

	return $value;
} # end function variable_calculate

function variable_update($name) {

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
	variable_set(
		$name,
		variable_calculate($name)
	);
} # end function variable_update

function variable_cron() {

	print("DEBUG: called " . __FUNCTION__ . " line " . __LINE__ . "<br/>\n");
	
	variable_update(
		variable_next()
	);
} # end function variable_cron

// Return a list of all variable names
function variable_list() {
	
	$sql = "SELECT variable_name
		FROM land_variable
		ORDER BY variable_name
		";
	
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
		WHERE variable_name = '$name' ";
	
	if (headers_sent()) { print "<!-- variable_record SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$result = mysql_fetch_assoc($query);
	
	mysql_free_result($query);							# delete query object
	
	return $result;
} // end function variable_record

?>