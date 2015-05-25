<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Unregistered Groups (raw)", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template
  ?>
<h2>List of Unregistered Groups</h2>
  <?
  $reserved = 1;    // we don't actually prevent people from seeing reserved groups [Corwyn P42]
  $html_field = unregistered_group_list($reserved);

  echo $html_field;
} // endif id, r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>