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

	variable_create('count_users',		3600,	'All existing users');
	variable_create('count_logged_on',	  60,	'Users currently logged on');
	variable_create('count_group',		3600,	'All existing groups');
	variable_create('count_group_reg',	 600,	'Registered groups');
	variable_create('count_group_unreg',	 600,	'Unregistered groups');
	variable_create('count_group_check',	 600,	'Groups needing name checks');
	variable_create('count_group_nohist',	3600,	'Groups with no history');
	variable_create('count_group_bonus',	3600,	'Groups with bonus land');
	variable_create('count_group_compress',	3600,	'Groups with compression');
	variable_create('count_group_notes',	 600,	'Groups with notes from Land Agent');
	variable_create('count_admin_notes',	3600,	'Groups with notes from Admin staff');
	variable_create('count_group_kingdom',	3600,	'Groups with a fixed size');
	variable_create('count_known_people',	 600,	'Campers in registered groups');
	variable_create('count_unknown_people',	3600,	'Campers in unregistered groups');
	variable_create('count_unfixed_groups',	3600,	'Groups with bad Cooper data');
	variable_create('count_orphan_groups',	 600,	'Orphan groups');
	variable_create('count_people_prereg',	 600,	'Campers prereged');
#	variable_create('xyzzy',		3600,	'xyzzy');

	variable_cron();
	?>
<style type="text/css">

.header td {
	font-weight: bold;
	background-color: silver;
	text-align: center;
}

.footer td {
	font-weight: bold;
	text-align: center;
}

</style>
	<?
	$count = 0;
	echo "<h2>Land Variables</h2>\n";
	echo "<table width='100%' border='1'>\n";
	echo "  <tr class='header'>\n";
	echo "    <td>ID</td>\n";
	echo "    <td>Name</td>\n";
	echo "    <td>value</td>\n";
	echo "    <td>delay</td>\n";
	echo "    <td>queued</td>\n";
	echo "    <td>updated</td>\n";
	echo "    <td>description</td>\n";
	echo "    <td>modified_date</td>\n";
	echo "  </tr>\n";
	$var_list = variable_list();
	foreach ($var_list as $name) {
		$data = variable_record( $name );
		echo "  <tr>\n";
		echo "    <td>$data[variable_id]</td>\n";
		echo "    <td>$name</td>\n";
		echo "    <td>";
		$value1 = $data['value'];
		$value2 = variable_get($name);
		if ($value === $value2) {
			// exact match: show only #1
			echo $value1;
		} else if ($value == $value2) {
			// value match: show only #2
			echo "<i>$value2</i>";
		} else {
			// non-match: show both
			echo "$value1<br/><b>$value2</b>";
		}
		echo "</td>\n";
		echo "    <td>$data[delay]</td>\n";
		echo "    <td>$data[queued]</td>\n";
		echo "    <td>$data[updated]</td>\n";
		echo "    <td>$data[description]</td>\n";
		echo "    <td>$data[modified_date]</td>\n";
		echo "  </tr>\n";
		$count++;
	}
	echo "  <tr class='footer'>\n";
	echo "    <td colspan=8>Total of $count variables found</td>\n";
	echo "  </tr>\n";
	echo "</table>\n";
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
