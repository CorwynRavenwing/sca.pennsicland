<?
require_once("include/nav.php");
require_once("include/variable.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Land Variables", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	$count = 0;
	echo "<h2>Land Variables</h2>\n";
	echo "<ul>\n";
	$var_list = variable_list();
	foreach ($var_list as $name) {
		$data = variable_record( $name );
		echo "  <li>$name\n";
		echo "    <ul>\n";
		foreach ($data as $field => $value) {
			echo "      <li>$field => $value</li>\n";
			$count++;
		}
		echo "    </ul>\n";
		echo "  </li>\n";
	}
	echo "  <li>Total of $count variables found</li>";
	echo "</ul>\n";
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
