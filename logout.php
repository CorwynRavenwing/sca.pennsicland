<?
require_once("include/session.php");

session_logout();

require_once("include/nav.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Log Out", $crumb );	# was nav_scriptname()

nav_menu();

nav_right_begin();

nav_template("logout_template.htm");
nav_template("template_login.htm");
?>
<br>
<br>
<br>
<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
