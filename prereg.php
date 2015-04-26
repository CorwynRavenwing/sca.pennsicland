<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "View Group Preregistered Campers", $crumb );

nav_menu();

nav_right_begin();

	template_load("view_prereg.htm");

	$group_name_display = ($group_name ? $group_name : "(NONE)" );
	
	$table = "
	<tr>
		<td colspan='5' align='center' style='font-size:large; font-weight:bold'>Group: $group_name_display</td>
	</tr>
	<tr>
		<td>#</td>
		<td>Penn Number</td>
		<td>First Name</td>
		<td>Last Name</td>
		<td>Sca Name</td>
	</tr>
	";
	# OLD WAY:
	# BODY OF TABLE GETS INSERTED HERE LATER, USING JAVASCRIPT [CORWYN 2007-05-25]
	# template_param( 'table', $table );
	# print template_output();	# PRINT IT EARLY, SUBSTITUTE DATA IN LATER
	
	$prereg_list = load_preregistrations_by_group_name($group_name);
	
	$count = 0;
	foreach ($prereg_list as $reg) { 
		$count++;

		$d_id			= $reg['id'];
		$d_first_name	= $reg['first_name'];
		$d_last_name		= $reg['last_name'];
		$d_sca_name		= $reg['sca_name'];

		$table .= "
	<tr>
		<td>$count&nbsp;</td>
		<td>$d_id&nbsp;</td>
		<td>$d_first_name&nbsp;</td>
		<td>$d_last_name&nbsp;</td>
		<td>$d_sca_name&nbsp;</td>
	</tr>
		";
	} // next reg
	
	if( $count ) {
		$table .= "
	<tr>
		<td colspan='5' align='center' style='font-size:large; font-weight:bold'>Total of $count Registrations</td>
	</tr>
		";
	} else {
		$table .= "
	<tr>
		<td colspan='5' align='center' style='font-size:large; font-weight:bold'>No Registrations Yet For This Group</td>
	</tr>
		";
	}
	
	template_param( 'table', $table );
	print template_output();

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
