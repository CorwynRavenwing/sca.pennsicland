<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Corwyn Special Page", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

	print "Adding columns to land_groups<br/>\n";
	
	
	/*
	$sql = "ALTER TABLE land_groups ADD COLUMN (column_name) (TYPE) (SIZE) NOT NULL";
	
	if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} // endif num
	*/
	
	/*
	$sql = "ALTER TABLE land_groups ADD COLUMN status int(1) NOT NULL DEFAULT 2";
	
	if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} // endif num
	
	
	
	$sql = "ALTER TABLE land_groups ADD COLUMN group_name_base varchar(255) NOT NULL";
	
	if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} // endif num
	
	
	
	$sql = "ALTER TABLE land_groups ADD COLUMN group_metaphone varchar(255) NOT NULL";
	
	if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} // endif num
	
	
	
	$sql = "ALTER TABLE land_groups ADD COLUMN group_soundex varchar(255) NOT NULL";
	
	if (headers_sent()) { print "<!-- X(1) SQL:\n$sql\n-->\n"; }
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($num = mysql_affected_rows()) {
		?>
<h2>A: Updated <?=$num?> rows</h2>
		<?
	} // endif num
	*/
	
	
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
