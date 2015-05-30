<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Block Assignment Help", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
    ?>
<h1>Block Assignment Process</h1>

<p>
The block assignment process, in which each group is assigned to a block, begins with you pressing the "Big Red Button"
This moves every group tentatively to their first block choice.  It also prevents land agents from making any further
changes to their block choices or their allowable compression percentage.
</p>

<p>
Then, go to the BLOCKS page.  Blocks with plenty of room will be colored green, empty blocks will be colored yellow, and
overcrowded blocks will be colored red.  Check out the red-colored (overcrowded) blocks.  Clicking on a block shows that
block's details in a new window.  Groups are shown in order of longevity: kingdoms first, then by years of history in
this block, and finally by years of history in Pennsic altogether.  Groups that fit will be colored green, and groups
that do not fit will be colored red.  You make decisions as to who moves to their next block choice in order to clear the
overcrowding on this block.  Each group's other block choices will be listed, colorized by whether this group would fit
into that new block either.
</p>

<p>
Once block choices are finalized for all blocks (or most blocks with a few troublesome holdouts), use the EMAIL MERGE
tool to send an email to all the groups in the finalized blocks, telling them how much room each group gets and who their
neighbors are.  The Email Merge tool also includes the opportunity to tell the system you are done making block moves,
putting it into "pennsic prep" mode and allowing land agents to contact their neighbors.
</p>

<p style="font-weight: bold">
If anything on this page needs to be changed, please contact Corwyn with the updates.  Thanks!
</p>

    <?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
