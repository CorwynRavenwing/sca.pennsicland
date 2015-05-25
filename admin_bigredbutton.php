<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Big Red Button", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! allow_bigredbutton() ) {
  print "<h2>Big Red Button may not be pushed at this time.</h2>\n";
} else {
  # no template
  print "<h2>Executing Match Algorithm</h2>\n";

  print "<div style='font-size:20; font-weight:bold'>[loading]</div>";

  print "<div id='detailed_comments'>\n";

  print "<div style='font-size:20; font-weight:bold'>matching prereg to groups ...</div>";
  match_prereg_to_groups();

  print "<div style='font-size:20; font-weight:bold'>updating prereg count ...</div>";
  update_pre_registration_count();

  print "<div style='font-size:20; font-weight:bold'>moving everyone to their first choice ...</div>";
  move_all_groups_to_first_choice();

  print "<div style='font-size:20; font-weight:bold'>rolling up used space ...</div>";
  roll_up_used_space();

  if ($current_mode == "locked") {
    print "<div style='font-size:20; font-weight:bold'>mode is already updated ...</div>";
  } else {
    print "<div style='font-size:20; font-weight:bold'>updating mode ...</div>";
    change_mode("locked");
  }

  print "</div>  <!-- detailed_comments -->\n";

  print "<div style='font-size:20; font-weight:bold'>done!</div>";
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>