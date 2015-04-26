<?
require_once("include/nav.php");
#require_once("include/user.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Express Check-In";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

  template_load("express.htm");

  # Dates of LAND GRAB PREP FRIDAY and LAND GRAB SATURDAY should be calculated variables
  $land_grab_friday   = "July 25th";
  $land_grab_saturday = "July 26th";

  template_param("land_grab_friday",   $land_grab_friday   );    # = pennsic_open
  template_param("land_grab_saturday", $land_grab_saturday );    # = +1

  # template_param("user_message",    $user_message);    // NEED THIS VARIABLE

  print template_output();

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>