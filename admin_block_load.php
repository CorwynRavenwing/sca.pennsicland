<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

// nav_head( "Block List", $crumb );

// nav_admin_menu();	// special Admin menu nav

// nav_admin_leftnav();	// no left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	print("<h2>Creating fake land_blocks.on_gas_line data</h2>\n");
	// Blocks the gas line cuts through:
	execute_query("UPDATE land_blocks SET on_gas_line = '1' WHERE block_name = 'E11'");
	execute_query("UPDATE land_blocks SET on_gas_line = '1' WHERE block_name = 'E18'");
	execute_query("UPDATE land_blocks SET on_gas_line = '1' WHERE block_name = 'E20'");
	execute_query("UPDATE land_blocks SET on_gas_line = '1' WHERE block_name = 'E24'");
	execute_query("UPDATE land_blocks SET on_gas_line = '1' WHERE block_name = 'W21'");
	// Temp data being deleted:
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'B02'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'B03'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'E05'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'E07'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'W11'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'W13'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'N17'");
	execute_query("UPDATE land_blocks SET on_gas_line = '0' WHERE block_name = 'N19'");

	execute_query("UPDATE land_blocks SET campable = '46631', description = 'P45: campable down 5212 per Gunther' WHERE block_name = 'N40'");

	execute_query("UPDATE land_blocks SET campable = '79178', description = 'P45: campable down 2000 (road) per Gunther' WHERE block_name = 'E23'");

	execute_query("UPDATE land_blocks SET campable = '69596', description = 'P45: campable down 9588 (showers) per Gunther' WHERE block_name = 'E17'");

	execute_query("UPDATE land_blocks SET campable = '69646', description = 'P45: campable up 7163 (showers) per Gunther' WHERE block_name = 'E02'");

	print("<h2>Done.</h2>\n");
} // endif admin

nav_right_end();

# nav_footer_panix();
# nav_footer_disclaimer();

nav_end();

function execute_query($sql) {
	# if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	if (headers_sent()) { print "<pre>SQL:\n$sql\n</pre>\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} else {
		?>
<h2>A: No rows updated (<?=$num?>)</h2>
		<?
	} // endif num
}

?>
