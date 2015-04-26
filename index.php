<?
require_once("include/nav.php");
require_once("include/user.php");

$user_motd = "
  Please note that picnic tables are first come, first served, and that chaining them, locking them down, or in any other way securing the tables prior to land grab is prohibited.
";

$group_record = group_record($group_id);

$complete = $group_record['registration_complete'];
$campers  = $group_record['pre_registration_count'];
# global $registration_open;        // set by include/mode.php

nav_start();

$crumb = array();
$title = "Zoning and Planning (Land)";  # rather than nav_scriptname() here

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

  template_load("template_main_page.htm");

  template_param("user_message",    $user_message);    // NEED THIS VARIABLE

  $legal_name_str = ((!$user_id) ? "(not logged on)"  : ($legal_name ? $legal_name : "*NO*NAME*")  );
  $group_name_str = ((!$user_id) ? "(NONE)"    : ($group_name ? $group_name : "*NONE*")  );

  template_param("legal_name_variable_string",  $legal_name_str );
  template_param("groupname_variable_string",  $group_name_str );
  template_param("motd",        $user_motd );

  # Dates of LAND GRAB PREP FRIDAY and LAND GRAB SATURDAY should be calculated variables
  $land_grab_friday   = "July 24th";
  $land_grab_saturday = "July 25th";

  template_param("land_grab_friday",   $land_grab_friday   );    # = pennsic_open
  template_param("land_grab_saturday", $land_grab_saturday );    # = +1

  // COMPOSE MAIN PAGE MESSAGE:
  $main_page_message = "";
  if (!$user_id) {
    // actually, this should never by excersized because of the outer if() statement
    $main_page_message .= "
      <p align='center'>
        <b>Not logged on.  Click <a href='login.php'>here</a> to log on.</b>
      </p>
      ";
  } elseif ($group_id) {
    # $main_page_message .= "<p>You have $campers people registered to your camp.</p>\n";
    $main_page_message .= "<p>Click on the View Prereg link on the left-most menu to see how many people, if any, are registered to your camp.</p>\n";
    if ($campers) {
      # $main_page_message .= "<p>Click on the View Prereg link on the left to see who they are.</p>\n";
    } elseif ($complete) {
      $main_page_message .= "<p>Your group's registration was completed properly.</p>\n";
    } else {
      $main_page_message .= "
        <p style='color:red; font-weight:bold; font-size:1.5em;'>
        <!-- text-decoration:blink; -->
        WARNING: Your group's registration did not complete properly!
        Click here to <a href='register.php?x=register_linkcode'>complete registering your camp</a>.
        We apologize for the inconvenience.
        </p>
      ";
    } // endif campers, complete
  } elseif ($registration_open) {
    $main_page_message .= "
      <p align='center'>
      <font color='red'>
        <b>You have not yet selected the group that you are the land agent for.<br /><br />
        Please click <a href='choose_group.php'>here</a> to choose a group.</b>
      </font>
      </p>
    ";
  } else {
    $main_page_message .= "
      <p align='center'>
      <font color='blue'>
        <b>Group registration deadline has passed.</b>
      </font>
      </p>
    ";
  } // endif group, registration_open

  template_param("main_page_message",  $main_page_message);

  print template_output();

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>