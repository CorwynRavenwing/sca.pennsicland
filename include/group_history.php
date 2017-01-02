<?php
# group_history.php: functions regarding the block object

require_once("connect.php");

# this function replaces both of the following functions
function get_group_history_both_by_year( $group_id, $year ) {
	$sql = "SELECT block_name,  attendance
			FROM land_group_history  
			RIGHT JOIN land_blocks USING ( block_id )
			WHERE group_id = '$group_id' AND group_id != '' AND year = '$year'
		";
	
	print "<!-- land_group_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$block_name = $result['block_name'];
		$attendance = $result['attendance'];
	} else {
		$block_name = "";
		$attendance = "";
	}
	
	return array($block_name, $attendance);
} // end function get_group_history_blockname_by_year

function get_group_history_blockname_by_year( $group_id, $year ) {
	$sql = "SELECT block_name
			FROM land_group_history  
			RIGHT JOIN land_blocks USING ( block_id )
			WHERE group_id = '$group_id' AND group_id != '' AND year = '$year'
		";
	
	print "<!-- land_group_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$block_name = $result['block_name'];
	} else {
		$block_name = "";
	}
	
	return $block_name;
} // end function get_group_history_blockname_by_year

function get_group_history_attendance_by_year( $group_id, $year ) {
	$sql = "SELECT attendance
			FROM land_group_history
			WHERE group_id = '$group_id' AND group_id != '' AND year = '$year'
		";
	
	print "<!-- land_group_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$attendance = $result['attendance'];
	} else {
		$attendance = "";
	}
	
	return $attendance;
} // end function get_group_history_attendance_by_year

function get_years_in_block_count($group_id, $block_id) {
	$sql = "SELECT Count(*) AS num
			FROM land_group_history
			WHERE group_id = '$group_id'
				AND block_id = '$block_id'
		";

	print "<!-- land_group_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$num = $result['num'];
	} else {
		$num = "";
	}
	
	return $num;
} // end function get_years_in_block_count

function get_total_years_in_system($group_id) {
	$sql = "SELECT Count(*) AS num
			FROM land_group_history
			WHERE group_id = '$group_id'
		";
	print "<!-- land_group_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$num = $result['num'];
	} else {
		$num = "";
	}
	
	return $num;
} // end function get_total_years_in_system

function count_groups_without_history() {
	$sql = "SELECT count(*) AS num
		FROM land_groups G
		LEFT JOIN land_group_history H ON(G.group_id=H.group_id)
		WHERE H.group_id IS NULL
		";
	
	print "<!-- count_groups_without_history SQL:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_assoc($query)) {
		$num = $result['num'];
	} else {
		$num = "";
	}
	
	return $num;
} // end function count_groups_without_history

function blockchoice_magic($label, $chosen, $next, $skipped, $free, $this) {
	# print "<h5>DEBUG: called blockchoice_magic($label, chosen=$chosen, next=$next, skip=$skipped, free=$free, this=$this)</h5>\n";
	
	if (! $label) {
		$label = "&nbsp;" ;
	}
	
	$people = round( ($free / 250), 0 );
	# my($free_title, $color);
	if ($free < 0) {
		$free_title = "OVER BY " . (-$free) . " (" . (-$people) . ")";
		$color = 'red';
	} elseif ($free == 0) {
		$free_title = "FULL";
		$color = 'dark orange';
	} elseif ($free < $this) {
		$free_title = "only $free ($people)";
		$color = 'blue';
	} else {
		$free_title = "$free ($people) free";
		$color = 'green';
	}
	
	if ($skipped) {
		$style	= "strike";
		$title	= "(skip)";
	} elseif ($next) {
		$style	= "b";
		$title	= "next choice; $free_title";
	} elseif ($chosen) {
		$style	= "u";
		$title	= "(this block)";
	} else {
		$style	= "span";
		$title	= $free_title;
	}
	
	return "<font color='$color'><$style><span title='$title'>$label</span></$style></font>";
} // end function blockchoice_magic
?>