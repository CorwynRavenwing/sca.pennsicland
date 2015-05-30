<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Open Year", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
    if ( allow_reopen() ) {

	print("DEBUG: admin_reopen_page<br />\n");
	
	print("<b>Set mode to 'normal'</b><br />\n");
	
	change_mode("normal");
	
	print("<b>Done.</b>\n");
	
    } elseif ($current_mode = "normal") {
	print "<h3>Re-open Year has already been run.</h3>\n";
    } else {
	print "<h2>Re-open Year cannot be run at this time.</h2>\n";
    } // endif mode
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
