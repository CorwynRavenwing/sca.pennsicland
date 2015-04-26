<?php
# block.php: functions regarding the block object

require_once("connect.php");

function block_list($allow_reserved = 0, $allow_hidden = 0) {
	$sql = "SELECT block_name
		FROM land_blocks
		";
	$where_clause_array = array();
	if (! $allow_hidden) {
	    array_push($where_clause_array, "hide != '1'");
	}
	if (! $allow_reserved) {
	    array_push($where_clause_array, "reserved != '1'");
	}
	$where_clause = join(" AND ", $where_clause_array);
	
	if ($where_clause) {
	    $sql .= "
		WHERE $where_clause
		";
	}
	$sql .= "ORDER BY block_name";
	
	print "<!-- block_list SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$retVal = array();
	
	while ($result = mysql_fetch_assoc($query)) {
		$block_name = $result['block_name'];
		
		array_push($retVal, $block_name);
	}
	
	return $retVal;		# caller is responsible for freeing result
} // end function block_list

function block_list_options($selected = "", $allow_reserved = 0, $allow_hidden = 0) {
	$retVal = "";
	$blocks = block_list($allow_reserved, $allow_hidden);
	
	foreach ($blocks as $block)
	{
		$selected_string = ( ($selected == $block) ? "selected='selected'" : "");
			
		$retVal .= "<option $selected_string>$block</option>";
	}
	# $retVal .= "<option></option>";
	
	return $retVal;
}

function block_name($block_id, $default="") {
	if ($block_id == "") {
		return "";
	}
	if ($default == "") {
		$default = "[$block_id]";
	}
	
	$sql = "SELECT block_name
		FROM land_blocks
		where block_id = '$block_id' ";
	
	# if (headers_sent()) { print "<!-- block_name SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$block_name = $result['block_name'];
	} else {
		$block_name = $default;
	}
	
	mysql_free_result($query);							# delete query object
	
	return $block_name;
} # end function block_name

function block_free($block_id) {
	if ($block_id == "") {
		return "";
	}
	
	$sql = "SELECT free_space
		FROM land_blocks
		where block_id = '$block_id' ";
	
	if (headers_sent()) { print "<!-- block_free SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$free_space = $result['free_space'];
	} else {
		$free_space = "000";
	}
	
	mysql_free_result($query);							# delete query object
	
	return $free_space;
} # end function block_free

function block_id($block_name) {
	if ($block_name == "") {
		return "";
	}
	
	$sql = "SELECT block_id
		FROM land_blocks
		where block_name = '$block_name' ";
	
	if (headers_sent()) { print "<!-- block_id SQL:\n$sql\n-->\n"; }

	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$block_id = $result['block_id'];
	} else {
		$block_id = "[$block_name]";
	}
	
	mysql_free_result($query);							# delete query object
	
	return $block_id;
} # end function block_id

function get_block_ids_ordered_by_block_name() {
	$retVal = array();
	
	$sql = "SELECT block_id, block_name
		FROM land_blocks
		ORDER BY block_name";
	
	print "\n<!-- get_block_ids_query:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	while($result = mysql_fetch_assoc($query)) {
		$retVal[ $result['block_id'] ] = $result['block_name'];
	}
	
	return($retVal);
} // end function get_block_ids_ordered_by_block_name

function block_record( $block_id ) {
	$sql = "SELECT *
		FROM land_blocks
		where block_id = '$block_id' ";
	
	if (headers_sent()) { print "<!-- block_id_to_sizes SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$result = mysql_fetch_assoc($query);
	
	mysql_free_result($query);							# delete query object
	
	return $result;
} // end function block_record

function block_data( $block_id ) {
	$sql = "SELECT campable_square_footage, used_space, has_changed, description
		FROM land_blocks
		where block_id = '$block_id' ";
	
	if (headers_sent()) { print "<!-- block_id_to_sizes SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$total_footage	= $result['campable_square_footage'];
		$used_footage	= $result['used_space'];
		$has_changed	= $result['has_changed'];
		$description		= $result['description'];
	} else {
		$total_footage	= "";
		$used_footage	= "";
		$has_changed	= "";
		$description		= "";
	}
	
	mysql_free_result($query);							# delete query object
	
	return array($total_footage,$used_footage,$has_changed,$description);
} // end function block_data
?>
