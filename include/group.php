<?
# group.php: functions regarding the land_groups object

require_once("block.php");

function group_name($group_id) {
  $result = group_record($group_id);

  if ($result) {
    $group_name = $result['group_name'];
  } else {
    $group_name = "";
  }

  return $group_name;
} # end function group_name

function group_id_by_name($group_name) {
  $group_name = mysql_real_escape_string($group_name);

  $where_clause = " group_name = '$group_name' ";

  $result = group_query($where_clause);

  if ($result) {
    $group_id = $result['group_id'];
  } else {
    $group_id = "";
  }

  return $group_id;
} // end function group_id_by_name

function group_record($group_id) {
  $where_clause = " group_id = '$group_id' ";

  $result = group_query($where_clause);

  return $result;
} // end function group_record

function group_query($where_clause, $order_by = "") {
  $query = group_query_mult($where_clause, $order_by);  # perform multiple query
  $result = mysql_fetch_assoc($query);          # pick the first one
  mysql_free_result($query);              # delete query object

  return $result;                  # return array of column data
} # end function group_query

function group_query_mult($where_clause, $order_by = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }
  if (! $order_by)   { $order_by = "group_name";                }

  $sql = "SELECT *,
      pre_registration_count * 250 AS calculated_square_footage,
      IF( exact_land_amount != 0,
          exact_land_amount,
          (pre_registration_count * 250 + bonus_footage) -
          (calculated_compression*.01) *
          (pre_registration_count * 250 +
          bonus_footage )
      ) AS alloted_square_footage
    FROM land_groups
    $where_clause
    ORDER BY $order_by ";

  if (headers_sent()) { print "<!-- group_query_mult SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return $query;  # caller iterates and should destroy this object
} # end function group_query_mult

// input: possible groupname
// output: "" if success, failure reason if failure.
function invalid_groupname($gr) {
  if (! $gr)
    return "blank";
  if (preg_match("/[^-_A-Za-z0-9 ']/", $gr))
    return "invalid character";
  return "";
} // end function invalid_groupname

function create_group($group_name) {
  if ( invalid_groupname($group_name) ) {
    return 0;
  }

  if ( group_id_by_name($group_name) ) {
    return 0;
  }

  $new_group_name = mysql_real_escape_string($group_name);

  $sql = "INSERT INTO land_groups
    (group_name, staff_group, new_group, reserved_group)
    VALUES ('$new_group_name', 0, 0, 0) ";

  if (headers_sent()) { print "<!-- group_query_mult SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return group_id_by_name($group_name);
} # end function create_group

function register_group($group_id, $user_id) {
  if (! $user_id) {
    return 0;
  }

  $sql = "UPDATE land_groups
    SET user_id = '$user_id'
    WHERE group_id = '$group_id'
    LIMIT 1 ";

  if (headers_sent()) { print "<!-- register_group SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function register_group

function complete_registration($group_id) {
  if (! $group_id) {
    return 0;
  }

  $sql = "UPDATE land_groups
    SET time_registered = UNIX_TIMESTAMP( NOW() ),
      registration_complete = 1
    WHERE group_id = '$group_id'
    LIMIT 1 ";
  if (headers_sent()) { print "<!-- complete_registration SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function complete_registration

function unregister_group($group_id) {
  $sql = "UPDATE land_groups
    SET user_id = '0',
      time_registered = 0,
      registration_complete = 0
    WHERE group_id = '$group_id'
    LIMIT 1 ";
  # was UNIX_TIMESTAMP( '0000-00-00 00:00:00' )

  if (headers_sent()) { print "<!-- unregister_group SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end unregister_group

# misnamed function: actually returns all UNREGISTERED group names [Corwyn P41]
function all_group_names($reserved = 0) {
  $retVal = array();

  $where_clause = "user_id = 0 ";
  if (! $reserved) {
    $where_clause .= "AND reserved_group = 0 ";
  }
  $order_by     = "group_name";

  $query = group_query_mult($where_clause, $order_by);

  while ($result = mysql_fetch_assoc($query)) {
    $group_name = $result['group_name'];
    $g_status         = group_status_name( $result['status'] );
    if ($g_status != "Forbid") {
      array_push($retVal, $group_name);
    }
  } // next result

  mysql_free_result($query);              # delete query object

  return $retVal;
} // end function all_group_names

function unregistered_group_list($reserved = 0) {
  $matching_groupnames = all_group_names($reserved);

  $html_field = "";

  $html_field .= "<table width='100%' border='0' cellpadding='4' cellspacing='0'>\n";
  $html_field .= "<tr>\n<td valign='top' bordercolor='#0000FF'>\n";

  $prev_initial = "";
  $i = 0;
  $columns = 3;
  $count = count($matching_groupnames);
  $break = round( $count / $columns , 0 ) + 5;  # was + 1;
  foreach ( $matching_groupnames as $g )
  {
    $initial = strtoupper( substr($g,0,1) );  # grab initial letter, uppercase it
    if ($prev_initial != $initial) {
      $prev_initial = $initial;
      $html_field .= "<h3 align='center'> - $initial - </h3>\n";
    }
    $html_field .= "<nobr><input type='radio' name='group_name' value=\"$g\">$g</nobr><br />\n";
    if (! (++$i % $break)) {
      $html_field .= "</td>\n<!-- column break at $break rows -->\n";
      $html_field .= "<td valign='top' bordercolor='#0000FF'>\n";
      # $html_field .= "<h4 align='center'> - $initial continued - </h4>\n";
    }
  }
  $html_field .= "</tr>\n";
  $html_field .= "<tr>\n";
  $html_field .= "<td colspan='$columns' style='text-align:center'>\n";
  $html_field .= "<b>Total of $i unregistered groups</b>\n";
  $html_field .= "<hr />\n";
  $html_field .= "<br />\n";
  $html_field .= "</td>\n";
  $html_field .= "</tr>\n";
  $html_field .= "</table>\n";

  return $html_field;
} // end function unregistered_group_list

function query_groups_by_block_id($block_id) {
  $where_clause = " final_block_location = '$block_id' ";
  $order_by = "group_name";

  return user_group_query($where_clause, $order_by);
} // end function groups_by_block_id

function group_set_name( $group_id, $group_name ) {
  if ($existing_group = group_id_by_name($group_name) ) {
    return 0;
  }

  $group_name = mysql_real_escape_string($group_name);

  $sql = "UPDATE land_groups
      SET group_name = '$group_name',
          group_name_base = '',
          group_soundex   = '',
          group_metaphone = ''
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_name SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end funciton group_set_name

# returns 1 on success, 0 on failure
function set_block_choices_id($group_id, $block_1_id, $block_2_id, $block_3_id, $block_4_id, $valid) {

  # print("called set_block_choices_id($group_id, $block_1_id, $block_2_id, $block_3_id, $block_4_id, $valid)<br/>\n");

  $sql = "UPDATE land_groups
      SET first_block_choice  = '$block_1_id'
        , second_block_choice = '$block_2_id'
        , third_block_choice  = '$block_3_id'
        , fourth_block_choice = '$block_4_id'
        , block_choices_valid = '$valid'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_block_choices_id SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function set_block_choices_id

# returns 1 on success, 0 on failure
function set_block_choices($group_id, $block_1, $block_2, $block_3, $block_4, $valid) {

  # print("called set_block_choices($group_id, $block_1, $block_2, $block_3, $block_4, $valid)<br/>\n");

  $block_1_id = block_id( $block_1 );
  $block_2_id = block_id( $block_2 );
  $block_3_id = block_id( $block_3 );
  $block_4_id = block_id( $block_4 );

  return set_block_choices_id($group_id, $block_1_id, $block_2_id, $block_3_id, $block_4_id, $valid);
} // end function set_block_choices

# returns 1 on success, 0 on failure
function group_set_final_block_id( $group_id, $block_id ) {
  $sql = "UPDATE land_groups
      SET final_block_location = '$block_id'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_final_block_id SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function group_set_final_block_id

function group_set_final_block_location( $group_id, $new_block_location ) {
  $block_id = block_id( $new_block_location );

  return group_set_final_block_id($group_id, $block_id);
} // end function group_set_final_block_location

function set_group_data($group_id, $on_site_representative, $compression_percentage, $other_group_information, $other_admin_information = "@" ) {
  $group_id                = mysql_real_escape_string($group_id);
  $on_site_representative  = mysql_real_escape_string($on_site_representative);
  $compression_percentage  = mysql_real_escape_string($compression_percentage);
  $other_group_information = mysql_real_escape_string($other_group_information);
  $other_admin_information = mysql_real_escape_string($other_admin_information);

  $sql = "UPDATE land_groups
      SET on_site_representative  = '$on_site_representative'
        , compression_percentage  = '$compression_percentage'
        , other_group_information = '$other_group_information'
    ";
  if ($other_admin_information != "@") {
      $sql .= "
        , other_admin_information = '$other_admin_information'
        ";
  }
  $sql .= "
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- set_group_data SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function set_group_data

function group_set_calculated_compression( $group_id, $compression ) {
  $sql = "UPDATE land_groups
      SET calculated_compression = '$compression'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_calculated_compression SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function group_set_calculated_compression

function group_set_exact_land_amount( $group_id, $exact_land_amount ) {
  $sql = "UPDATE land_groups
      SET exact_land_amount = '$exact_land_amount'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_exact_land_amount SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function group_set_exact_land_amount

function set_group_flags($group_id, $reserved_group, $new_group, $registration_complete) {
  $sql = "UPDATE land_groups
      SET reserved_group = '$reserved_group'
        , new_group = '$new_group'
        , registration_complete = '$registration_complete'
        , reserved_group = '$reserved_group'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_group_flags SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end set_group_flags

function group_set_bonus_footage( $group_id, $bonus ) {
  $sql = "UPDATE land_groups
      SET bonus_footage = '$bonus'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_bonus_footage SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function group_set_bonus_footage

function group_set_bonus_reason( $group_id, $bonus_reason ) {
  $sql = "UPDATE land_groups
      SET bonus_reason = '$bonus_reason'
      WHERE group_id = '$group_id'
    ";
  if (headers_sent()) { print "<!-- group_set_bonus_reason SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>\n$sql<br/>\n");

  return mysql_affected_rows();
} // end function group_set_bonus_reason

function group_status_name($g_status) {
  $retVal = "unknown!";

  switch ($g_status) {

    case 0:    $retVal = "Allow";      break;
    case 1:    $retVal = "Forbid";      break;
    case 2:    $retVal = "NEW";      break;
    case 3:    $retVal = "PROBLEM";      break;
    default:  $retVal = "Unknown ($g_status)";  break;

  } // end switch

  return $retVal;
} // end function group_status_name

function admin_group_type_list() {
  return array(
    "A" => "Admin",
    "F" => "Fictional",
    ""  => "ORPHAN",
  );
} // end function admin_group_type_list

function admin_group_type_descriptions() {
  return array(
    "Admin"     => "Groups used for administrative purposes",
    "Fictional" => "Groups that collectively mean 'I am in the wrong place'",
    "ORPHAN"    => "Normal groups missing their land agents",
  );
} // end function admin_group_type_descriptions

?>