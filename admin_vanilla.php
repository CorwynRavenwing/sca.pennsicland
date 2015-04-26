<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "VANILLA PAGE", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	echo "Page Goes Here";
	# template_load("admin_main_page.htm");
	# template_param("admin_message",				$admin_message );
	# print template_output();
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
