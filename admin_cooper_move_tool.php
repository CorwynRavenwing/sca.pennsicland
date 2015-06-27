<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Cooper Move-Preregistrations Tool", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! $w_admin) {
  print "<h2>Your access level does not allow this action.</h2>\n";
} else {
  # no template
?>
<h1>MOVE USERS IN COOPER DATABASE</h1>

<h3>1. Search for people in OLD group name in this window:</h3>

<iframe id="window_1" src="admin_cooper_show_registrations.php" frameborder="1" width="750"> </iframe>

<h3>2. Search for people in NEW group name in this window:</h3>

<iframe id="window_2" src="admin_cooper_show_registrations.php" frameborder="1" width="750"> </iframe>

<h3>3. Use this window to move the person:</h3>

<iframe id="window_3" src="admin_cooper_move_registrations.php" frameborder="1" width="750"> </iframe>

<h3>4. Use the above windows to verify the move happened.</h3>
<?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>