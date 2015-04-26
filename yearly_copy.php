<?
# require_once("include/nav.php");
require_once("include/connect.php");

$tables = array(
	"MAPOBJECTS",
	"Math_Test",
	"NEWUSERSTATE",
	"OBJECTPOINTS",
	"OBJECTPOINTS2",
	"cooper_data",
#	"cooper_data_old",
	"cooper_prereg_count",
	"cooper_preregistration",
	"data_var",
#	"data_var_old",
	"images_access_log",
	"images_categories",
	"images_category_images",
	"images_group_members",
	"images_groups",
	"images_information",
	"images_permissions",
	"images_servers",
	"land_block_images",
	"land_blocks",
	"land_correlation",
	"land_group_history",
	"land_groups",
#	"land_groups_BACKUP",
	"land_preregistration_changes",
	"landoffice",
	"mailmerge",
	"mailmerge_recipients",
	"mailmerge_variables",
	"nametest_records",
	"security_groups",
	"security_object_groups",
	"soap_land_groups",
	"soap_prereg",
	"soap_statecode",
	"soap_userlogin",
	"soap_users",
	"user_accounts",
	"user_address",
	"user_email",
	"user_group_members",
	"user_groups",
	"user_information",
	"user_linkcodes",
	"user_linkcodes2",
	"user_logins",
	"user_logins2",
	"user_mail",
	"user_mail2",
	"user_phone",
	"user_security_groups",
	"user_tracking",
	"user_tracking2",
	"users_watch",
#	"users_watch_old",
);

foreach ($tables as $t) {
	print "table $t\n";
	
	$pn  = "P38";
	$tt  = "${pn}_${t}";
	$old = "${tt}_old";
	$new = "${tt}_new";
	
	$sql = "CREATE TABLE IF NOT EXISTS $t ( x char )";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "SELECT Count(*) AS num FROM $t";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	if ($result = mysql_fetch_assoc($query)) {
		$num = $result['num'];
	} else {
		$num = "err";
	}
	print ": $num<br />\n";
	
	$sql = "CREATE TABLE IF NOT EXISTS $tt ( x char )";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "DROP TABLE IF EXISTS $new";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "CREATE TABLE $new LIKE $t";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "INSERT INTO $new SELECT * FROM $t";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "DROP TABLE IF EXISTS $old";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	
	$sql = "RENAME TABLE $tt to $old, $new TO $tt";
	# print "$sql<br />\n";
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
	# print "<br />\n";
} // next t

print "<br />Done<br />\n";

/*
while ($result = mysql_fetch_assoc($query)) {
	$group_name = $result['Group_Name'];
} // next result
*/

?>

</body>
</html>
