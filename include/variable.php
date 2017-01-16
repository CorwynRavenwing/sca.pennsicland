<?php
# variable.php: functions regarding the variable object

require_once("connect.php");
require_once("cooper.php");   # needed only for fix_cooper_data_count()
require_once("group_history.php");  # needed for count_groups_without_history()

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
  
  mysql_free_result($query);              # delete query object
  
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
  
  mysql_free_result($query);              # delete query object

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
  $sql = "
    UPDATE land_variable
      SET value = '$value'
        , updated = UNIX_TIMESTAMP(NOW())
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
      , (updated + delay < UNIX_TIMESTAMP(NOW()) ) AS is_old
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
    if ($value === '') {
      $value = "BLANK";
    }
    if ($result['is_old']) {
      $value .= "*";
    }
    if ($result['is_queued']) {
      $value .= "!";
    }
  } else {
    $value = "$name?";
  }

  return $value;
} # end function variable_get

function variable_next() {
  $sql = "
    SELECT variable_name
       , (updated + delay) AS runtime
    FROM land_variable
    WHERE (updated + delay) < UNIX_TIMESTAMP(NOW())
      AND queued = 0
    ORDER BY runtime ASC
    LIMIT 1
  ";

  print "<!-- variable_next SQL:\n$sql\n-->\n";
  
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ($result = mysql_fetch_assoc($query)) {
    $next_var_name = $result['variable_name'];
    print("<!-- variable_next() returned name '$next_var_name' -->\n");
    variable_queue($next_var_name);
  } else {
    $next_var_name = "";
    print("<!-- variable_next() found no matches -->\n");
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
    case '':
      $value = "";
      break;

    case 'count_users':
      $value = count_where("user_information");
      break;

    case 'count_logged_on':
      $value = current_user_sessions();
      break;

    case 'count_group':
      $value = count_where("land_groups");
      break;

    case 'count_group_reg':
      $value = count_where("land_groups", "user_id                != 0");
      break;

    case 'count_group_unreg':
      $value = count_where("land_groups", "user_id                 = 0");
      break;

    case 'count_group_check':
      $value = count_where("land_groups",
        "status IN ( 2, 3 )
                  OR group_name_base = ''
                  OR group_soundex = ''
                  OR group_metaphone = ''
                " );
      break;

    case 'count_group_nohist':
      $value = count_groups_without_history();
      break;

    case 'count_group_bonus':
      $value = count_where("land_groups", "bonus_footage          != 0");
      break;

    case 'count_group_compress':
      $value = count_where("land_groups", "calculated_compression != 0");
      break;

    case 'count_group_notes':
      $value = count_where("land_groups", "other_group_information!= '' ");
      break;

    case 'count_admin_notes':
      $value = count_where("land_groups", "other_admin_information!= '' ");
      break;

    case 'count_group_system':
      $value = count_where("land_groups", "system_group!='' ");
      break;

    case 'count_group_prev':
      $value = count_where("land_groups", "prev_user_id!='' ");
      break;

    case 'count_group_kingdom':
      $value = count_where("land_groups", "exact_land_amount      != 0");
      break;

    case 'count_known_people':
      $value = sum_where("land_groups",   "pre_registration_count", "user_id != 0");
      break;

    case 'count_unknown_people':
      $value = sum_where("land_groups",   "pre_registration_count", "user_id  = 0");
      break;

    case 'count_unfixed_groups':
      $value = fix_cooper_data_count();
      break;

    case 'count_orphan_groups':
      $value = count_where("land_groups", "pre_registration_count > 0 AND user_id = 0");
      break;

    case 'count_people_prereg':
      $value = count_where("cooper_data", "group_name not like ':%'");
      break;

    /*
    case 'xyzzy':
      $value = "xyzzy";
      break;
    */

    default:
      $value = "UNKNOWN: variable_calculate($name)";
      break;
  }

  return $value;
} # end function variable_calculate

function variable_update($name) {
  if (!$name) {
    return 0;
  }
  
  return variable_set(
    $name,
    variable_calculate($name)
  );
} # end function variable_update

// Calculate whichever variable needs it most
function variable_cron() {
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
  
  mysql_free_result($query);              # delete query object

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
  
  mysql_free_result($query);              # delete query object
  
  return $result;
} // end function variable_record

function variable_init() {
  variable_create('count_users',          3600, 'All existing users');
  variable_create('count_logged_on',        60, 'Users currently logged on');
  variable_create('count_group',          3600, 'All existing groups');
  variable_create('count_group_reg',       600, 'Registered groups');
  variable_create('count_group_unreg',     600, 'Unregistered groups');
  variable_create('count_group_check',     600, 'Groups needing name checks');
  variable_create('count_group_nohist',   3600, 'Groups with no history');
  variable_create('count_group_bonus',    3600, 'Groups with bonus land');
  variable_create('count_group_compress', 3600, 'Groups with compression');
  variable_create('count_group_notes',     600, 'Groups with notes from Land Agent');
  variable_create('count_admin_notes',    3600, 'Groups with notes from Admin staff');
  variable_create('count_group_system',   3600, 'System Groups');
  variable_create('count_group_prev',     3600, 'Groups with a Previous Registration');
  variable_create('count_group_kingdom',  3600, 'Groups with a fixed size');
  variable_create('count_known_people',    600, 'Campers in registered groups');
  variable_create('count_unknown_people', 3600, 'Campers in unregistered groups');
  variable_create('count_unfixed_groups', 3600, 'Groups with bad Cooper data');
  variable_create('count_orphan_groups',   600, 'Orphan groups');
  variable_create('count_people_prereg',   600, 'Campers prereged');
# variable_create('xyzzy',                3600, 'xyzzy');
} // end function variable_init

// call init function when this file is included
variable_init();
?>