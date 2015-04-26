<?
require_once("include/nav.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
);

$title = "VANILLA PAGE";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();
?>

	body goes here

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
