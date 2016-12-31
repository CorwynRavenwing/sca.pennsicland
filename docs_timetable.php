<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Documents"    => "docs_documents.php",
);

$title = "Land Agents' Timetable and Summary of Procedures";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

$registration_start_shorter = "January 1";
$proxy_deadline_shorter     = "July 21";
$pennsic_landgrab_long      = "Friday, July 28, 2017";
$pennsic_landgrab_short     = "Fri., July 28";
$pennsic_landgrab_shorter   = "July 28";
$pennsic_firstday_long      = "Saturday, July 29";
$pennsic_firstday_short     = "Sat., July 29";
$pennsic_sunday_short       = "Sun., July 30";
$pennsic_stop_long          = "Sunday, August 14, 2016";
?>

<style>
dt {font-weight:bold}
</style>

<!-- FreeFind Begin No Index -->

<!-- FreeFind End No Index -->

<div class="clear" style="padding-top: 10px; padding-left:20px"> <!-- whole page -->

<h1>Land Agents' Schedule and Summary of Procedures</h1>

<a name="timetable">&nbsp;</a>
<h3>Timetable (all times Eastern Standard Time)</h3>
<dl style="font-weight:normal; margin-left:0; line-height:1.3em">
<dt><?=$registration_start_shorter?>
<dd>Group registration site opens
<dt>June 18
<dd>Land agents should verify all pre-registered campers with their group to find/fix errors and/or omissions
<dt>June 23
<dd>Final date to access/update information on the Group Land Registration Page
<dt>July 7
<dd>Land assignments are sent to Land Agents
<dt>July 7 &mdash; <?=$pennsic_landgrab_shorter?>
<dd>Land Agents negotiate group placement on their assigned block
<dt><?=$proxy_deadline_shorter?>
<dd>Deadline for Proxy requests

<dt><?=$pennsic_landgrab_short?> (9:00 am &mdash; 11:00 pm)
<dd>Troll opens
<dd>Land Agents start checking in with the Land Office (in the Great Hall on N01). Bring your completed Camping Authorization Form and Block Map, and pick up any additional information pertinent to your block. 
<dd>Land Agents may finalize block negotiations with other Land Agents from their assigned block
<dd>Land Agents from the same block with finalized block maps may assemble as a group and complete the Land Grab process

<dt><?=$pennsic_firstday_short?> (8:00 am &mdash; 10:00 am)
<dd>Land Office open for LATE ARRIVAL Land Agents to check in. Bring your completed Camping Authorization Form and Block Map, and pick up any additional information pertinent to your block.

<dt><?=$pennsic_firstday_short?> (10:00 am &mdash; 12:00 noon)
<dd>Blocks with unsettled negotiations are seen by Land Office staff for dispute resolution.

<dt><?=$pennsic_sunday_short?> (10:00 am &mdash; 12:00 noon)
<dd>Land distribution for single campers and those groups not pre-registered.
<dd>Land Office open for assistance and questions.

</dl>

<style>
ol.doublespaced li {
	margin-bottom: 0.5em;
}
</style>

<h2>Land Agent Procedure Summary</h2>
<ol class="doublespaced">

<li>
	Read all information on the Land pages, and look closely at the maps since the blocks
	may have changed from last year.
</li>

<li>
	Register your group.
</li>

<li>
	The Land Office will e-mail Land Agents their block assignments on July 7.
	If you do not receive a notice by then, contact Land Staff.
</li>

<li>
	Once you get your block assignment, contact the other Land Agents also
	assigned to your block.  Try to work out which area of the block each
	group will camp in prior to arrival at Pennsic. This has worked well
	in the past and saves everyone time on Land Grab day.
</li>

<li>
	Troll opens on <strong><?=$pennsic_landgrab_long?> at 9:00 am</strong>.
	No one will be allowed onto the battlefield or into the campground without going through
	Troll first. Do not arrive
	prior to <strong>9:00 am</strong>; the staff will not be ready for you.
</li>

<li>
	After checking in at Troll, Land Agents walk to the Land Office (in the Great Hall) to
	check in with the Land Office, pick up any additional information pertinent to your block.
	Bring your completed Camping Authorization Form and Block Map.
	Once you have checked in with the Land Office, you may walk your land and talk to the
	Land Agents from your block. The Land Office will be open
	<?=$pennsic_landgrab_long?> from 9:00 am until 11:00 pm
	and on <?=$pennsic_firstday_long?> from 8:00 am &mdash; 6:00 pm.
	<b>You must check in with Troll and the Land Office by 10:00 am on Saturday.</b>
</li>

<li>
	You will be allowed to camp Friday night on the battlefield / parking lot.
	NO fires are permitted on the battlefield.
</li>

<li>
	All Land Agents must sign one block map indicating where their group is camping. You will also be required, if so noted on your block assignment email, to set aside unallocated land in your block for single campers. Single camper space must be clearly shown on the map. Single campers must have clear access to a main road.  Registered groups may not save single camper space for people arriving at a later date.
</li>

<li>
	If you cannot come to an agreement on group placement on your block, submit your block to Land Staff for binding
	arbitration.
</li>

<li>
	Your final approved map will be used for: informational purposes; to resolve disputes; and by the Public Safety department.
</li>

<li>
	Once the block map has been approved, you and all your party who have gone
	through Troll will receive authorization to drive into the campground.
</li>

<li>
	Rope off the perimeter of your land allocation and post your camping authorization form at the entrance in
	 a waterproof container. Note: It is important that you rope off your encampment as it makes it easier for
	single campers to find unused land left in a block.
</li>

<li>
	Once all this is finished, set up your camp and enjoy Pennsic!
</li>

</ol>

</div>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>