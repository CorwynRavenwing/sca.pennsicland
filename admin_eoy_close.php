<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Close Year", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! $w_admin) {
  print "<h2>Your access level does not allow this action.</h2>\n";
} else {

    if ( allow_movedata() ) {
  print "<br/><b>Moving data for last year's attendance</b>\n";

  $sql = "
    INSERT INTO land_group_history
    (group_id, year, block_id, attendance)
      SELECT group_id, $pennsic_number, final_block_location, pre_registration_count
      FROM land_groups
      WHERE final_block_location <> ''
    ";
  print "<!-- SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
  $rows  = mysql_affected_rows();
  print ": changed $rows rows\n";

  print "<br /><b>Clearing last year's data</b>\n";

  $sql = "
    UPDATE land_groups
    SET user_id = 0,
      registration_complete = 0,
      time_registered = 0,
      pre_registration_count = 0,
      calculated_compression = 0,
      final_block_location = '',
      used_space_save = 0,
      other_group_information = ''
    WHERE 1
    ";
  #   group_data = ''           # not sure what this field is for, why blank it?

  print "<!-- SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
  $rows  = mysql_affected_rows();
  print ": changed $rows rows\n";

  print "<br /><b>Turning off all staff flags</b>\n";

  $sql = "
    UPDATE land_groups
    SET staff_group = 0
    WHERE 1
    ";
  print "<!-- SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
  $rows  = mysql_affected_rows();
  print ": changed $rows rows\n";

  print "<br /><b>Make all accounts inactive</b>\n";

  $sql = "
    UPDATE user_information
    SET active_account = 'F',
      last_update = last_update
    WHERE 1
    ";
  print "<!-- SQL:\n$sql\n-->\n";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
  $rows  = mysql_affected_rows();
  print ": changed $rows rows\n";

  # $menu_linkcode = $linkcode_processor->create_linkcode( $user_id, "", "admin_main_admin_menu" , "", "", "" );

  print "<br /><b>Set mode to 'data moved'</b><br />\n";

  change_mode("data moved");

  # print "<b>Done.</b>  Click <a href='land.cgi?linkcode=$menu_linkcode'>here</a> for the main menu.\n";

    } elseif ($current_mode == "data moved") {
  print "<h3>Close Year has already been run.</h3>\n";
    } else {
  print "<h2>Close Year cannot be run at this time.</h2>\n";
    } // endif mode

} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>