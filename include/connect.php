<?php
# corrected this script that it won't print anything if headers have not been sent [Corwyn P38]

$SCRIPT_NAME = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : "COMMAND LINE";
$SERVER_NAME = isset($_SERVER['SERVER_NAME'])     ? $_SERVER['SERVER_NAME']     : "/localhost/";

if (strpos ($SERVER_NAME, "localhost") !== false) {
  $test_mode = 2;
} elseif (strpos ($SCRIPT_NAME, "_TEST") !== false) {
  $test_mode = 1;
} else {
  $test_mode = 0;
}
# these were both stripos() , which is exactly what we DON'T want [Corwyn 2013]

if (headers_sent()) {
  print "<!-- test_mode: " . $test_mode . " -->\n";

  print "
<style>
.loud {
  font-size:16px;
  font-weight:bold;
}

.test {
  background-color:#FFCCCC;
}

.virtual {
  background-color:#CCCCFF;
}
</style>
";

  print "<!-- SCRIPT_NAME $SCRIPT_NAME -->\n";
  print "<!-- SERVER_NAME $SERVER_NAME -->\n";
} // endif headers_sent

switch ($test_mode) {
  case 0:
    if (headers_sent()) { print "<!-- LIVE MODE -->\n"; }
    $db_name = 'land_db';
    break;

  case 1:
    if (headers_sent()) { print "<span class='loud test'>TEST MODE</span><br />\n"; }
    $db_name = 'test_db';
    break;

  case 2:
    if (headers_sent()) { print "<span class='loud virtual'>VIRTUAL MODE</span><br />\n"; }
    # $db_name = 'virtual_db';
    $db_name = 'laptop_db';
    break;

  default:
    break;
} // end switch test_mode

if (headers_sent()) { print "<!-- db_name: " . $db_name . " -->\n"; }

switch ($db_name) {
  case "land_db":
    require_once("pw_db_live.php");
    break;

  case "test_db":
    require_once("pw_db_test.php");
    break;

  case "laptop_db":
    require_once("pw_db_laptop.php");
    break;

  default:
    break;
} // end switch db_name

if (headers_sent()) { print "<!-- db: " . $db_server . ":" . $db_dbname . " -->\n"; }

$link = mysql_connect($db_server, $db_user_name, $db_password)
    or die('Could not connect: ' . mysql_error());
# echo "Connected successfully<br />\n";
mysql_select_db($db_dbname)
  or die('Could not select database');
# echo "Changed database successfully<br />\n";

function count_where($table_name, $where_clause = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }

  $sql = "SELECT count(*) AS num
    FROM $table_name
    $where_clause
    ";

  print "<!-- count_where SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>" . $sql);
    # was or trigger_error( ... ) instead [Corwyn 2013]

  if ($result = mysql_fetch_assoc($query)) {
    $num = $result['num'];
  } else {
    $num = 0;
  } // endif result

  return $num;
} // end function count_where

function sum_where($table_name, $field_name, $where_clause = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }

  $sql = "SELECT sum($field_name) AS num
    FROM $table_name
    $where_clause
    ";

  print "<!-- sum_where SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ($result = mysql_fetch_assoc($query)) {
    $num = $result['num'];
  } else {
    $num = 0;
  } // endif result

  return $num;
} // end function sum_where

?>