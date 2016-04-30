<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Cooper Count Users", $crumb );

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
<h1>SHOW COUNT OF REGISTERED USERS</h1>

<form action="http://www.cooperslake.com/prereg/land/land.php" method="post" name="coopersland" target="_self">
	<input type="hidden" name="userid"   value="<? echo $GS_userid; ?>" />
	<input type="hidden" name="password" value="<? echo $GS_password; ?>" />
	<b>Group Name:</b> <input type="text" name="arg1" value="" />
	<input type="hidden" name="function" value="registered_count_by_group_name" />
	<br />
	<input type="submit" name="submit" value="Submit">
</form>
<?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>