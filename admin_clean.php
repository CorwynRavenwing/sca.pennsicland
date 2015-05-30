<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Cooper Data Help", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	// no template
	?>
<h2>COOPER DATA</h2>

<p>
During the time while preregistration is open, two things need to be done occasionally
to keep the camper-registration data in synch between the Coopers' system and ours:
</p>

<h3>Fix Count</h3>

<p>
This downloads a copy of the list of groups with registered campers, with the count
of campers for each group, from the Coopers' site.  For each group where our count
of campers differs from theirs, it also downloads the list of campers for those groups.
</p>

<p>
The "GROUPS : PREREG" menu item on the Admin screen searches only the most recently
downloaded data.  If you search for someone there and don't find them, try running
"Prereg Count" and searching again.
</p>

<p>
The "View Prereg" page on the Land Agent menu, always shows the data directly from the Coopers' site,
rather from the download.
</p>

<h3>Fix Orphans</h3>

<p>
Because the previous years' group names are no longer erased from the Coopers' database, they still
appear in the list of groups to camp with, even if they have not been registered this year.  Therefore
we must use this tool to determine if there are any campers signed up for groups without a Land Agent.
</p>

<p>
On the other hand, administrative groups like MERCHANT and fictional groups like None Selected never
have a Land Agent, but they will show up on this list.  They've been separated out from the real orphaned
groups in the Prereg Cleanup tool.  Land Staff should use this tool to auto-register any administrative
and fictional groups that have campers in them.  Any real groups that show up in this tool, need to be
contacted to inform them that they need to get their Land Agent to register the group prior
to the deadline, or their group will not exist and they will be moved to Singles Camping.
</p>

<p>
We do not have access to the email addresses of campers, just their names and PENN numbers.  Compose a letter
for the campers and send it to the Coopers asking that they forward to everyone registered with certain groups.
</p>

<p>
It might also be a good idea to send such a letter to those in group Landgroup Not Listed, None Selected,
and Not Filled In, and possibly even Individual Camping to ensure these campers don't think they chose a group.
Anyone who chose a group like MERCHANT or RV CAMPING probably knows what they're doing.
</p>
	<?
} // endif admin
	
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
