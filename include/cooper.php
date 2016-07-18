<!-- begin cooper.php -->
<?php
require_once("include/connect.php");
require_once("include/block.php");

$update_cooper_data = 1;    // set to 0 to prevent automatic updating from the coopers site [Corwyn P42]

function setup_GS_url() {
  global $test_mode, $GS_URL, $GS_userid, $GS_password;

  if ($test_mode != 2) {
    require_once("pw_cooper_live.php");
  } else {
    require_once("pw_cooper_test.php");
  }
} // end function setup_GS_url

function call_cooper_curl_function($function_name, $arg1, $die_error=0) {
  global $GS_URL, $GS_userid, $GS_password;

  setup_GS_url();

  # print "DEBUG: writing to gs_url '$GS_URL'<br />\n";

  $postfields = "userid=$GS_userid&password=$GS_password&arg1=$arg1&function=$function_name";

  $c = curl_init();
  curl_setopt($c, CURLOPT_URL, $GS_URL);
  curl_setopt($c, CURLOPT_POST, true);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($c, CURLOPT_POSTFIELDS, $postfields);
  curl_exec($c);
  $value = curl_multi_getcontent($c);
  curl_close($c);

  # print "DEBUG: curl::$function_name($arg1) returned <pre>"; print_r($value); print "</pre>\n";

  if ($die_error) {
    if( $value == '' ) {
      die("remote system returned no data");
    } elseif( $value == 'log-in failed' ) {
      die("remote system unavailable: " . $value);
    } elseif( $value == 'FAIL' ) {
      die("remote system FAIL");
    } // endif error
  } // endif die_error

  if (strpos($value,"<") === 0) {
    die("remote system FAIL: answer begins with a less-than character.<pre>$value</pre>");
  }
  return $value;
} // function call_cooper_curl_function

// returns list of $group => $count
function get_preregistration_count_list() {
  print "called get_preregistration_count_list()<br />\n";

  $retVal = array();

  $value = call_cooper_curl_function("registered_count_by_group_name", "", 1);

  $records = split("<br>", $value);
  unset($value);    # don't need it anymore

  $n = 0;
  print "<font color='green'>reading groups\n";
  # print "<br />\n";  ob_flush();  # debug

  foreach ($records as $record) {
    chop($record);
    @list($count, $group_name) = split( "\|", $record );
    # unset($record);    # don't need it anymore

    # print "&nbsp;&nbsp;&nbsp;$group_name -> $count<br />\n";  # DEBUG
    if (! ($n++ % 25)) { print "= "; }
    # $group_name_to_count{ $group_name } = $count;

    if ($group_name and $count != "") {
      $retVal[ $group_name ] = $count;
    } else {
      $n--;
    }

    # unset($group_name);    # don't need it anymore
    # unset($count);
  } // next record

  print "\ndone ($n).</font><br />\n";

  return $retVal;
} // end function get_preregistration_count_list

function cooper_create_group($group_name)
{
  print "called cooper_create_group($group_name)<br />\n";

  $group_name = mysql_real_escape_string($group_name);

  # $retVal = array();

  $value = call_cooper_curl_function("add_land_group", $group_name, 0);

  if( $value == '' ) {
    die("remote system returned no data");
  } elseif( $value == 'log-in failed' ) {
    die("remote system unavailable: " . $value);
  } elseif( $value == 'FAIL' ) {
    # print "Failed to create group '$group_name', perhaps it already exists?";
    print "Group '$group_name' already exists on Cooper website";
    return 0;
  } else {
    print "Created group '$group_name' on Cooper website.";
  } // endif error

  return 0;
} // end function cooper_create_group

function cooper_delete_group($group_name)
{
  print "called cooper_delete_group($group_name)<br />\n";

  $group_name = mysql_real_escape_string($group_name);

  # $retVal = array();

  $value = call_cooper_curl_function("remove_land_group", $group_name, 0);

  if( $value == '' ) {
    die("remote system returned no data");
  } elseif( $value == 'log-in failed' ) {
    die("remote system unavailable: " . $value);
  } elseif( $value == 'FAIL' ) {
    # print "Failed to create group '$group_name', perhaps it already exists?";
    print "Group '$group_name' does not exist on Cooper website";
    return 0;
  } else {
    print "Deleted group '$group_name' on Cooper website.";
  } // endif error

  return 0;
} // end function cooper_delete_group

# =======================================================

function update_pre_registration_count() {
  global $update_cooper_data;

  if (! $update_cooper_data) {
    print("<font size='+1' color='blue'>NOT running update_pre_registration_count()</font>\n");
    return;
  }

  print "getting prereg count list ...<br />\n";
  $prereg_list = get_preregistration_count_list();
  print "got list, preparing query ...<br />\n";

  # first, clear out the old data ...
  $sql = "DELETE FROM cooper_prereg_count WHERE 1 ";
  if (headers_sent()) { print "<!-- update_prereg_count SQL 1:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  # now, store the new data ...

  $n = 0;
  print "<font color='red'>saving counts...\n";

  # print "<br/>DEBUG: prereg_list<pre>"; print_r($prereg_list); print "</pre>\n";

  $values_array = array();
  foreach ($prereg_list as $group_name => $count) {
      # print "&nbsp;&nbsp;&nbsp;group $group_name = count $count<br />";
    $count    = mysql_real_escape_string( $count );
    $group_name  = mysql_real_escape_string( $group_name );

    array_push( $values_array, "('$count','$group_name')" );

    if (! ($n++ % 25)) { print "= "; }
  } // next prereg_list

  $chunked_array = array_chunk($values_array, 25);

  foreach ($chunked_array as $chunk) {

    $sql = "
      INSERT INTO cooper_prereg_count
        (pre_registration_count,group_name)
      VALUES
      " . join(",\n", $chunk);

    if (headers_sent()) { print "<!-- update_prereg_count SQL 2:\n$sql\n-->\n"; }

    $query2 = mysql_query($sql)
      or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

    print "! ";
  } // next chunk

  print "\ndone ($n).</font><br />\n";

  print "<font color='blue'>Storing data ...\n";

  # first, clear out the old data ...
  $sql = "
    UPDATE land_groups SET pre_registration_count = 0
    WHERE pre_registration_count != 0
    ";
  if (headers_sent()) { print "<!-- update_prereg_count SQL 3:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  print "(" . mysql_affected_rows() . ")\n";

  # now, store the new data ...
  $sql = "
    UPDATE land_groups AS g, cooper_prereg_count AS c
    SET g.pre_registration_count = c.pre_registration_count
    WHERE g.group_name = c.group_name
    ";
  if (headers_sent()) { print "<!-- update_prereg_count SQL 4:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  print "(" . mysql_affected_rows() . ")\n";

  # determine success rate ...
  $sql = "SELECT count(*) AS num FROM land_groups WHERE pre_registration_count > 0 ";

  if (headers_sent()) { print "<!-- update_prereg_count SQL 5:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ($result = mysql_fetch_assoc($query)) {
    $num = $result['num'];
  } else {
    $num = "0";
  }

  print "(" . $num . ")\n";

  print "\ndone.</font><br />\n";
} // end function update_pre_registration_count

function show_fix_cooper_data() {
  global $update_cooper_data;

  if (! $update_cooper_data) {
    print("<font size='+1' color='blue'>NOT running show_fix_cooper_data()</font>\n");
    return;
  }
  ?>
<table border="1">
  <tr style="background-color:silver; font-weight:bold">
    <td align="center">Group Name</td>
    <td align="center">Land<br/>DB</td>
    <td align="center">Cooper<br/>DB</td>
    <!-- cooper live -->
  </tr>
  <?
  $sql = "
    SELECT group_name, pre_registration_count,
      count(cooper_data_id) AS num
    FROM land_groups
      LEFT JOIN cooper_data USING(group_name)
    GROUP BY group_name
    HAVING pre_registration_count != num
    ORDER BY group_name
    LIMIT 1000
    ";

  if (headers_sent()) { print "<!-- update_prereg_count SQL 5:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $count = 0;
  $num = mysql_num_rows($query);
  while ($result = mysql_fetch_assoc($query)) {
    $count++;
    ?>
  <tr>
    <td style="text-align:center; font-weight:bold"><?=$result['group_name']?></td>
    <td align="center"><?=$result['pre_registration_count']?></td>
    <td align="center"><?=$result['num']?></td>
    <!-- ... -->
  </tr>
    <?
  } // next result
  ?>
</table>
  <?
} // end function show_fix_cooper_data

function fix_cooper_data_count() {      # SHOULD move to group.php or something
  # should merge with SQL in next function
  $sql = "
    SELECT group_name, pre_registration_count,
      count(cooper_data_id) AS num
    FROM land_groups
      LEFT JOIN cooper_data USING(group_name)
    GROUP BY group_name
    HAVING pre_registration_count != num
    ORDER BY RAND()
    LIMIT 1000
    ";

  if (headers_sent()) { print "<!-- fix_cooper_data_count SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $retVal = "0";
  while ($result = mysql_fetch_assoc($query)) {
    $old_count  = $result['num'];
    $new_count  = $result['pre_registration_count'];

    $diff = ($old_count - $new_count);
    if ($diff < 0) { $diff = -$diff; }

    $retVal += $diff;
  } // next result

  return $retVal;
} // end function fix_cooper_data_count

function fix_cooper_data() {
  global $update_cooper_data;

  if (! $update_cooper_data) {
    print("<font size='+1' color='blue'>NOT calling fix_cooper_data()</font>\n");
    return;
  }

  $display_left_number = 1;

  print("<font size='+1' color='green'>Checking for groups with out-of-date Cooper data ...</font>\n");

  # should merge with SQL in previous function
  $sql = "
    SELECT group_name, pre_registration_count,
      count(cooper_data_id) AS num
    FROM land_groups
      LEFT JOIN cooper_data USING(group_name)
    GROUP BY group_name
    HAVING pre_registration_count != num
    ORDER BY RAND()
    LIMIT 1000
    ";

  if (headers_sent()) { print "<!-- update_prereg_count SQL 5:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $count = 0;
  $num = mysql_num_rows($query);
  while ($result = mysql_fetch_assoc($query)) {
    $count++;

    if (! @$printed_label++) {
      print("<font size='+1' color='green'>found some ($num):</font><br />\n");
      $elapsed = show_elapsed_time($display_left_number);
    }
    $group_name  = $result['group_name'];
    $old_count  = $result['num'];
    $new_count  = $result['pre_registration_count'];
    print("fixing group '$group_name' ($old_count->$new_count) ...\n");

    load_preregistrations_by_group_name($group_name);

    print " done.<br />\n";

    $elapsed = show_elapsed_time($display_left_number);

    if ($elapsed >= 60) {
      break;
    } elseif ($count >= 100) {
      break;
    }
  } // next result

  $sql = "
    SELECT group_name, count(cooper_data_id) AS num
    FROM cooper_data
      LEFT JOIN land_groups USING(group_name)
    WHERE group_name NOT LIKE ':%'
      AND group_id IS NULL
    GROUP BY group_name
    ORDER BY RAND()
    LIMIT 100
    ";

  if (headers_sent()) { print "<!-- update_prereg_count SQL 6:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $num = mysql_num_rows($query);
  while ($result = mysql_fetch_assoc($query)) {
    $count++;

    if (! @$printed_label_2++) {
      print("<font size='+1' color='red'>found some obsolete groups ($num):</font><br />\n");
    }

    $group_name  = $result['group_name'];
    $old_count  = $result['num'];
    $new_count  = 0;
    print("fixing group '$group_name' ($old_count->$new_count) ...\n");

    load_preregistrations_by_group_name($group_name);
    # the following appears not to be a good replacement:
    # set_cooper_count($group_name, $new_count);

    print " done.<br />\n";
  } // next result

  print "<font size='+1' color='green'>OK</font><br />\n";

  if ($count) {
    print "<font size='+1'><a href='admin_prereg_fix.php'>Search again...</a></font><br />\n";
  }

  return $count;
} // end function fix_cooper_data

function set_cooper_count($group_name, $new_count) {
  $group_name = mysql_real_escape_string($group_name);

  $sql = "UPDATE land_groups
      SET pre_registration_count = '$new_count'
      WHERE group_name = '$group_name'
      LIMIT 1
    ";

  print "<!-- set_cooper_count SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return;
} // end function set_cooper_count

# _raw function simply returns the number of cooper records found in database
function count_cooper_records_raw($group_name) {
  # print "DEBUG: counting cooper records for $group_name<br />\n";

  $group_name = mysql_real_escape_string($group_name);

  $sql = "SELECT Count(*) AS num
      FROM cooper_data
      WHERE group_name = '$group_name'
    ";

  print "<!-- count_cooper_records_raw SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ($result = mysql_fetch_assoc($query)) {
    $num = $result['num'];
    # print "DEBUG: found num $num<br />\n";
  }

  return $num;
} // end function count_cooper_records_raw

# non- _raw function also updates the land_groups.pre_registration_count data
function count_cooper_records($group_name) {
  # print "DEBUG: counting cooper records for $group_name<br />\n";

  $num = count_cooper_records_raw($group_name);

  set_cooper_count($group_name, $num);

  return $num;
} # end function count_cooper_records

function untouch_cooper_records($group_name) {
  $group_name = mysql_real_escape_string($group_name);

  $sql = "UPDATE cooper_data
      SET garbage_collect = 1,
        modified_date = modified_date
      WHERE group_name = '$group_name' ";

  print "<!-- untouch_cooper_records SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return(1);
} // end function untouch_cooper_records

function save_cooper_data($group_name, $penn_number, $first, $last, $sca) {
  global $r_admin;

  $old_group = find_cooper_group_by_penn($penn_number);
  if ($old_group == $group_name) {
    touch_cooper_record($penn_number)
      or print "ERROR: failed to touch record '$penn_number'<br />\n";
  } elseif ($old_group) {
    if ($r_admin) { print("<br/>&nbsp;&nbsp;&nbsp;&nbsp;(m <a title='$sca'>$penn_number</a> '$old_group'->'$group_name'"); @ob_flush(); }
    move_cooper_record($group_name, $penn_number)
      or print "ERROR: failed to move record '$penn_number' to '$group_name'<br />\n";
    if ($r_admin) { print(")"); @ob_flush(); }
  } else {
    if ($r_admin) { print("<br/>&nbsp;&nbsp;&nbsp;&nbsp;(c <a title='$sca'>$penn_number</a>    -> '$group_name'"); @ob_flush(); }
    create_cooper_record($group_name, $penn_number, $first, $last, $sca)
      or print "ERROR: failed to create record ('$group_name', '$penn_number', '$first', '$last', '$sca')<br />\n";;
    if ($r_admin) { print(")"); @ob_flush(); }
  } # endif old_group
} // end function save_cooper_data

function find_cooper_group_by_penn($penn_number) {
  $s_penn_number = mysql_real_escape_string($penn_number);

  $sql = "SELECT group_name
      FROM cooper_data
      WHERE penn_number = '$s_penn_number' ";

  print "<!-- find_cooper_group_by_penn SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $count = 0;
  $group_name = "";
  while ($result = mysql_fetch_assoc($query)) {
    $count++;
    $group_name = $result['group_name'];
    # print "DEBUG: found group $group_name<br />\n";
  }

  if ($count > 1) {
    print "<!-- ERROR: found penn number $penn_number $count times! -->\n";
  }

  return $group_name;
} // end function find_cooper_group_by_penn

function touch_cooper_record($penn_number) {
  $s_penn_number = mysql_real_escape_string($penn_number);

  $sql = "UPDATE cooper_data
      SET garbage_collect = 0,
        modified_date = modified_date
      WHERE penn_number = '$s_penn_number' ";

  print "<!-- touch_cooper_record SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $new_group = find_cooper_group_by_penn($penn_number);

  return ($new_group != '');
} // end function touch_cooper_record

function move_cooper_record($group_name, $penn_number) {
  $s_group_name    = mysql_real_escape_string($group_name);
  $s_penn_number  = mysql_real_escape_string($penn_number);

  $sql = "UPDATE cooper_data
      SET
        previous_group = IF(group_name = ':', previous_group, group_name),
        group_name = '$s_group_name',
        garbage_collect = 0
      WHERE penn_number = '$s_penn_number' ";

  print "<!-- move_cooper_record SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $new_group = find_cooper_group_by_penn($penn_number);

  return ($new_group == $group_name);
} // end function move_cooper_record

function create_cooper_record($group_name, $penn_number, $first, $last, $sca) {
  $s_group_name    = mysql_real_escape_string($group_name);
  $s_penn_number   = mysql_real_escape_string($penn_number);
  $s_first         = mysql_real_escape_string($first);
  $s_last          = mysql_real_escape_string($last);
  $s_sca           = mysql_real_escape_string($sca);

  $sql = "INSERT INTO cooper_data
      (group_name, penn_number, first_name, last_name, sca_name)
      VALUES
      ('$s_group_name','$s_penn_number','$s_first','$s_last','$s_sca') ";

  print "<!-- create_cooper_record SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $new_group = find_cooper_group_by_penn($penn_number);

  return ($new_group == $group_name);
} // end function create_cooper_record

function reject_untouched_cooper_records($group_name) {
  $s_group_name = mysql_real_escape_string($group_name);

  $sql = "UPDATE cooper_data
      SET
        previous_group = IF(group_name = ':', previous_group, group_name),
        group_name = ':',
        garbage_collect = 0
      WHERE group_name = '$s_group_name'
        AND garbage_collect = 1 ";

  print "<!-- reject_untouched_cooper_records SQL:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return(1);
} // end function reject_untouched_cooper_records

# loads registrations from database, don't clean them up, return list of registrations found
function load_preregistrations_by_search($search) {
  if (! $search) { return array(); }

  $retVal = array();

  $search = mysql_real_escape_string($search);

  $sql = "SELECT cooper_data_id, penn_number, group_name, previous_group, first_name, last_name, sca_name
    FROM cooper_data
    WHERE (    penn_number    LIKE  '$search%'
            OR group_name     LIKE '%$search%'
            OR previous_group LIKE '%$search%'
            OR first_name     LIKE '%$search%'
            OR last_name      LIKE '%$search%'
            OR sca_name       LIKE '%$search%'
         ) AND group_name NOT LIKE ':%'
    ORDER BY group_name, previous_group
    ";

  print "<!-- load_preregistrations_by_search SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_assoc($query)) {

    $rec = array(
      "id"             => $result['penn_number'],
      "first_name"     => $result['first_name'],
      "last_name"      => $result['last_name'],
      "sca_name"       => $result['sca_name'],
      "group_name"     => $result['group_name'],
      "previous_group" => $result['previous_group'],
      "cooper_data_id" => $result['cooper_data_id'],
    );

    array_push( $retVal, $rec );
  } // endwhile result

  return $retVal;
} // end function load_preregistrations_by_search

# loads registrations from cooper site, cleans up database, returns list of registrations found
function load_preregistrations_by_group_name($group_name) {
  global $update_cooper_data;

  if ($update_cooper_data) {

  # print "DEBUG: called load_preregistrations_by_group_name($group_name)<br />\n";

  # print("<br/>");  ob_flush();  # debug
  show_elapsed_time();
  # print("[A");  ob_flush();  # debug

  $value = call_cooper_curl_function("registered_persons_by_group_name", $group_name, 0);

  if( $value == '' ) {
    # not a fatal error here
  } elseif( $value == 'log-in failed' ) {
    die("remote system unavailable: " . $value);
  } elseif( $value == 'FAIL' ) {
    die("remote system FAIL");
  } // endif error

  $records = split("<br>", $value);
  unset($value);    # don't need it anymore

  # print("<br/>&nbsp;&nbsp;");  ob_flush();  # debug
  # show_elapsed_time();
  # print("(B");  ob_flush();  # debug
  untouch_cooper_records($group_name);
  # print(")");  ob_flush();  # debug
  # show_elapsed_time();

  $retVal = array();
  $i = 0;
  foreach ($records as $record) {
    chop($record);
    @list($id, $first, $last, $sca, $junk) = split( "\|", $record );
    # unset($record);    # don't need it anymore

    if (strpos($id, "'") !== false) {
        die("remote system FAIL: returned ID containing apostrophe: $id");
    } elseif (strpos($id, "<") !== false) {
        die("remote system FAIL: returned ID containing less-than: $id");
    }

    $i++;
    # print("<br/>&nbsp;&nbsp;&nbsp;&nbsp;");  ob_flush();  # debug
    # show_elapsed_time();
    # print("-:1");  ob_flush();  # debug
    $rec = array(
      "id"             => $id,
      "first_name"     => $first,
      "last_name"      => $last,
      "sca_name"       => $sca,
      "group_name"     => $group_name,
      "previous_group" => "N/A",
      "cooper_data_id" => "0",
    );

    if ($id) {
      array_push( $retVal, $rec );
      # show_elapsed_time();
      # print("{scd($id)");  ob_flush();  # debug
      save_cooper_data($group_name, $id, $first, $last, $sca);
      # print("}");  ob_flush();  # debug
    } else {
      $i--;
    }
    # print(":-");  ob_flush();  # debug
    # show_elapsed_time();
  } // endfor records

  # print("<br/>&nbsp;&nbsp;");  ob_flush();  # debug
  # show_elapsed_time();
  # print("(D");  ob_flush();  # debug
  reject_untouched_cooper_records($group_name);
  # print(")");  ob_flush();  # debug
  # show_elapsed_time();

  # print("<br/>&nbsp;&nbsp;");  ob_flush();  # debug
  # show_elapsed_time();
  # print("(E");  ob_flush();  # debug
  $num = count_cooper_records($group_name);  # we're not doing anything with $num
  # print(")<br/>]");  ob_flush();  # debug
  show_elapsed_time();

  } else {

  print("<font size='+1' color='blue'>NOT calling load_preregistrations_by_group_name()</font>\n");

  $retVal = load_preregistrations_by_search($group_name);  # will also match people named like the group

  } // endif
  return $retVal;
} // end function load_preregistrations_by_group_name

# I am confident this function is obsolete.  Nevertheless, moved it here in case I'm wrong.  [Corwyn P39]
function match_prereg_to_groups() {
  /*
  $self = shift;

  #preload the groupnames and group_id's into a hash for speed
  my %groupname_to_group_id;

  $sqlQuery = "SELECT group_id, group_name
            FROM land_groups";

  $query = $main::dbh->prepare( $sqlQuery );

  $query->execute() || die $main::dbh->errstr;

  my @result_array;

  while( @result_array = $query->fetchrow_array )
  {
    $groupname_to_group_id{ lc($result_array[1]) } = $result_array[0];
  }

  #remove any records that have been deleted from the
  #cooper_preregistration table

  $sqlQuery =   "( SELECT cooper_id
                       FROM land_correlation
                       LEFT JOIN cooper_preregistration ON ( cooper_id = id )
                      WHERE cooper_preregistration.id IS NULL )
                      UNION
                      ( SELECT cooper_id
                        FROM land_correlation
                        LEFT JOIN cooper_preregistration ON ( cooper_id = id )
                        WHERE cooper_preregistration.time_updated >
                              land_correlation.time_updated           )";

  $query = $main::dbh->prepare( $sqlQuery );

  $query->execute( ) || die $main::dbh->errstr;

  $deleted_id;

  while( $deleted_id = $query->fetchrow_array() )
  {
    $sqlQuery =   "DELETE FROM land_correlation
              WHERE cooper_id = ?";

    $query = $main::dbh->prepare( $sqlQuery );

    $query->execute( $deleted_id ) || die $main::dbh->errstr;
  }

  #select from cooper_preregistration where not in
  #land_correlation thus getting all of the new
  #and updated records

  $sqlQuery =   "SELECT cooper_preregistration.id,
                            cooper_preregistration.landgroup
                       FROM land_correlation
                      RIGHT JOIN cooper_preregistration ON ( cooper_id = id )
                      WHERE land_correlation.cooper_id IS NULL";

  $query = $main::dbh->prepare( $sqlQuery );

  $query->execute( ) || die $main::dbh->errstr;

  #prepare inster query for speed reasons
  $sqlQuery =   "INSERT INTO land_correlation
                      (cooper_id, group_id, fuzzy_match, manual_match)
                      VALUES( ?,?,?,? )";

  $query2 = $main::dbh->prepare( $sqlQuery );

  while( @result_array = $query->fetchrow_array() )
  {
    $exact_match = 0;
    $fuzzy_match = 0;
    $group_id_match = 0;

    #begin matching sequence

    #print "match<br>";

    my %groupid_by_match_distance;
    $name;
    #test for exact match

    $group_id = 0;
    $group_id = $groupname_to_group_id{ lc( $result_array[1] ) };

    #print "x $result_array[0] = $group_id, " . $groupname_to_group_id{ $result_array[1] } . ", " . lc($result_array[1]) .", <br>";

    if( defined( $group_id ) )
    {
      $exact_match = 1;
      $group_id_match = $group_id;
    }
    else
    {
      foreach $name ( keys %groupname_to_group_id )
      {
        $groupid_by_match_distance{ $groupname_to_group_id{ $name } } =
          abs( adistr( lc( $result_array[1] ), $name ) );
      }
    }

    #print "z = $exact_match, $group_id<br>";

    if( $exact_match != 1 )
    {
      my @d = sort { $groupid_by_match_distance{$a} <=>
               $groupid_by_match_distance{$b} }
               keys %groupid_by_match_distance;

      #print "Y1  $d[0], $groupid_by_match_distance{ $d[0] }   <br>";

      if( $groupid_by_match_distance{ $d[0] } < .2 )
      {
        #print "Y2 =" . $d[0] ." <br>";

        $group_id_match = $d[0];
        $fuzzy_match = 1;
      }
    }

    if( $fuzzy_match eq 1 )
    {
      print "storing $result_array[0] fzy<br>";
      $query2->execute( $result_array[0], $group_id_match, "1", "0"  ) #|| die $main::dbh->errstr;
    }
    else
    {
      print "storing $result_array[0] !fzy<br>";
      $query2->execute( $result_array[0], $group_id_match, "0", "0"  ) #|| die $main::dbh->errstr;
    }
  }
  */
} // end function match_prereg_to_groups

# NEW FUNCTION CORWYN 2007
function move_all_groups_to_first_choice() {
  $other_blockid = block_id("Other");

  $sql = "UPDATE land_groups SET first_block_choice = $other_blockid
                           WHERE first_block_choice = '' ";

  print "<!-- move_all_blocks_to_first_choice SQL 1:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  print "<h3>Changed first block choice from Blank to Other for " . mysql_affected_rows() . " groups.</h3>\n";

  $sql = "UPDATE land_groups SET final_block_location = first_block_choice
                           WHERE (final_block_location = '' or final_block_location = $other_blockid)
           AND user_id != 0
           AND pre_registration_count > 0
           ";

  print "<!-- move_all_blocks_to_first_choice SQL 2:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  print "<h3>Moved " . mysql_affected_rows() . " non-empty groups to their first block choice.</h3>\n";

  $sql = "UPDATE land_groups SET final_block_location = $other_blockid
                           WHERE (final_block_location = '' or final_block_location = first_block_choice)
           AND user_id != 0
           AND pre_registration_count = 0
           ";

  print "<!-- move_all_blocks_to_first_choice SQL 3:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  print "<h3>Moved " . mysql_affected_rows() . " empty groups to block Other.</h3>\n";

  return;
} // end function move_all_groups_to_first_choice

# NEW FUNCTION CORWYN 2007
function roll_up_used_space() {
  global $pennsic_number;

  if (! $pennsic_number) {
    die("ERROR: roll_up_used_space() called while pennsic_number($pennsic_number) is false.\n\nPlease tell Corwyn what page you received this error message on.");
  }

  print "<!-- called roll_up_used_space() -->\n";

  $sql = "
    update land_groups
    set used_space = (
        if(
          exact_land_amount,
          exact_land_amount,
          (250 * pre_registration_count) * (1 - calculated_compression / 100)
        )
        + bonus_footage
      )
  ";
  print "<!-- roll_up_used_space SQL 1:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  # NOTE: new formula.  Never compress exact values, never compress bonuses [Corwyn 2007]
  # NOTE: This must agree with formula in admin_decide_block.htm JavaScript.

  $sql = "
    update land_blocks
    set used_space = (
      select sum(used_space) from land_groups
      where final_block_location = block_id
      group by final_block_location
    )
  ";
  print "<!-- roll_up_used_space SQL 2A:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);



  $sql = "
    update land_blocks
    set
      map_link      = CONCAT(
                        'http://$_SERVER[SERVER_NAME]/maps/$pennsic_number/',
                        block_name,
                        '_L.pdf'
                      ),
      auth_link     = 'http://$_SERVER[SERVER_NAME]/docs/form_auth.pdf',
      gasline_link  = IF(
                        on_gas_line='1',
                        'http://$_SERVER[SERVER_NAME]/docs/form_gas_line.pdf',
                        ''
                      )
  ";
  print "<!-- roll_up_used_space SQL 2B:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);





  $sql = "
    update land_blocks
    set free_space = Greatest(0, (campable_square_footage - used_space) )
  ";
  print "<!-- roll_up_used_space SQL 3:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = "
    update land_blocks
    set free_space = Greatest(0, (campable_square_footage - used_space) )
  ";
  print "<!-- roll_up_used_space SQL 4:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = "
    update land_groups left join user_information USING(user_id)
      set group_data = Concat(
        'group_name    : ', group_name, '\n',
        'Legal Name    : ', legal_name, '\n',
        'Sca   Name    : ', alias, '\n',
        'EmailAddress  : ', email_address, '\n',
        'Allotted Sq Ft: ', used_space, '\n'
      )
  ";
  print "<!-- roll_up_used_space SQL 5:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = " SET SESSION group_concat_max_len = 16777215 ";  # allow use of full MEDIUMTEXT size
  print "<!-- roll_up_used_space SQL 6:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = "
    update land_blocks
    set generate_neighbors = (
      select group_concat(group_data ORDER BY group_name SEPARATOR '\n==========\n\n') from land_groups
      where final_block_location = block_id
      and group_data != ''
      group by final_block_location
    )
  ";
  print "<!-- roll_up_used_space SQL 7:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = "
    update land_groups
    set has_changed = (used_space != used_space_save)
  ";
  print "<!-- roll_up_used_space SQL 8:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $sql = "
    update land_blocks
    set has_changed = (
      select sum(has_changed) from land_groups
      where final_block_location = block_id
      group by final_block_location
    )
  ";
  print "<!-- roll_up_used_space SQL 9:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return;
} // end function roll_up_used_space

function mark_sent($group_id) {
  $sql = "UPDATE land_groups
      SET used_space_save = used_space, has_changed = 0
       WHERE group_id = '$group_id'
        AND (used_space_save != used_space OR has_changed != 0)
    ";
  print "<!-- mark_sent SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  # print("<h4>DEBUG: group_id = '$group_id'; rows = '" . mysql_affected_rows() . "''</h4>\n");

  return mysql_affected_rows();
} // end function mark_sent

function mark_everybody_sent() {
  $sql = "UPDATE land_groups SET used_space_save = used_space, has_changed = 0
          WHERE (used_space_save != used_space OR has_changed != 0)";

  print "<!-- mark_everybody_sent SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return mysql_affected_rows();
} // end function mark_everybody_sent

function load_groups_by_final_location_in_match_order($block_id) {
  $sql = "SELECT land_groups.group_id,
        group_name,
        pre_registration_count,
        SUM(IF(block_id= '$block_id',1,0)) AS I,
        SUM(IF(block_id!='$block_id',1,0)) AS O
      FROM land_groups
        LEFT JOIN land_group_history ON
          ( land_groups.group_id = land_group_history.group_id )
      WHERE final_block_location = '$block_id'
        AND (user_id != 0 or pre_registration_count != 0)
      GROUP BY land_groups.group_id
      ORDER BY I DESC,pre_registration_count DESC";

  # note: this used to hide groups with registrations, if they were missing their land agent [Corwyn 2007]

  print "<!-- load_groups_by_final_location_in_match_order sql:\n$sql\n-->\n";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $group_data = array();

  while ($result = mysql_fetch_assoc($query)) {
    $group_id      = $result['group_id'];
    $data = group_record( $group_id );
    # $data['group_id']    = $result['group_id'];
    # $data['group_name']  = $result['group_name'];
    $data['prereg_count']  = $result['pre_registration_count'];
    $data['I']        = $result['I'];
    $data['O']      = $result['O'];

    array_push($group_data, $data);
  } // next result

  return $group_data;
} // end function load_groups_by_final_location_in_match_order

$global_time = 0;
function show_elapsed_time($display = 1) {
  global $global_time;
  global $r_admin, $w_admin;

  $prev_time = $global_time;
  $global_time = time();

  if (! $prev_time) {
    if ($display) {
      if ($r_admin) { print("<span style='font-size:0.75em; color:blue;'>{X}</span>"); @ob_flush(); }
    }
    $elapsed_time = 0;
  } else {
    $elapsed_time = $global_time - $prev_time;
    if (! $elapsed_time) {
      if ($display) {
        if ($r_admin) { print("<span style='font-size:0.75em; color:blue;'>{" . $elapsed_time . "}</span>"); @ob_flush(); }
      }
    } else {
      if ($display) {
        if ($w_admin) {
          print("<span style='font-size:0.75em; color:green;'>{" . $elapsed_time . "}</span>");  @ob_flush();
        } elseif ($r_admin) {
          print("<span style='font-size:0.75em; color:red;'>{" . $elapsed_time . "}</span>");  @ob_flush();
        }
    }
    }
  }

  return $elapsed_time;
}
?>