<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Documents"    => "docs_documents.php",
);

$title = "Rules and Procedures";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

# should be a "mode.php" variable:
$cooper_prereg_end_date = "June 9th";

$link_for_main_website = "penn43";
?>

<!-- FreeFind Keywords Words="land rules square feet footage allocation allotment allottment" Count="5" -->

<!-- link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="mobile.css" / -->

<!-- FreeFind Begin No Index -->

<!-- FreeFind End No Index -->
  <!--?php  include($DOCUMENT_ROOT . "/penn41/landnav.html");  ?-->

 <div class="clear" style="padding-top: 10px; padding-left:20px"> <!-- whole page -->



<!-- FreeFind End No Index -->

<h1 align=center>General Land Rules, Procedures, and Notes for <br>
Individuals, Groups, Royal Encampments</h1>
<br />
<h3>Individuals</h3>
<ul>
<li>Every person who <a href="http://www.cooperslake.com/prereg/account/" target="_blank">pre-registers</a> for Pennsic will have
<STRONG>250 square feet </STRONG>assigned to his/her designated group.

<P>
<li>If you will not be camping with a group, please see the
<a href="docs_singlecampers.php">Information for Single Campers</a>.
<P>
<li>All those who wish to camp in the designated Handicapped Camping area must
<a href="http://www.cooperslake.com/prereg/account/" target="_blank">register with Cooper's Lake Campground</a>
<b>by <?=$cooper_prereg_end_date?></b>, and also with the
<a href="http://www.pennsicwar.org/<?=$link_for_main_website?>/DEPTS/disabled.html">Disabilities Camping Coordinator</a>.
<p>
<li>Those who are considering use of an RV must contact the Cooper's Lake Campground directly as the Land Office does not coordinate RV camping.
<P>
</ul>
<br />

<style>

ol.doublespaced li {
	margin-bottom: 0.5em;
}

</style>

<h3>Groups / Land Agents</h3>
<ul class="doublespaced">
<li>   Groups must decide on ONE group name. (Please note that "Brotherhood of the Blade" and "Blade Brotherhood" would be regarded as two different group names.)

<li>Each group must have a Land Agent. Please see <a href="docs_handbook.php">A Handbook for Pennsic Land Agents</a> for information on being a Land Agent.

<li>Land Agents must have an e-mail account and Internet access.  E-mail assures that all Land Agents on an assigned block can contact each other easily.

<li>Land Agent contact information will not be posted to any public forum, but will be given to the other Land Agents in their assigned block and to members of the Pennsic staff if needed. 

<li>All Land Agents will be <strong>expected and required</strong> to negotiate in good faith with the other Land Agents on their assigned block, via e-mail/the lnternet prior to arrival at Pennsic.

<li>Should a block reach maximum capacity, groups will have the option to "voluntarily reduce the size" of their land allotment in an effort to not be moved from their traditional land blocks. There is no guarantee that this reduction will put you in the block you want, but every effort will be taken to do this.

<li>All groups must have a Land Agent in residence for the entire Pennsic event to:

<ol>
<li>Negotiate for Land
<li>Rope off the entire encampment perimeter
<li>Be available to discuss issues, situations, and other land-related issues
with the Land Staff.
</ol>

<li>
Land that is not roped off or is unattended  will be considered abandoned. No &quot;ghost
camps&quot; will be allowed.   In the case of an emergency that would leave the camp unattended, contact the Land Office.


<li>As always, the final placement of any and all land groups is at the sole discretion
of the Land Office.
</ul>
<a name="royalcamp">&nbsp;</a>
<h3>Royal Encampments</h3>

In an effort to standardize, it is heavily suggested that all Royal Encampment Land Agents
register their group as &quot;Kingdom of ______&quot;  (e.g.: Kingdom of the East,
Kingdom of Atlantia, etc.)  This makes it easier to find them on an alphabetized list.
<P>
People who will be camping in a Royal Encampment must use the official registered group name on their Individual pre-registration forms, under "Group Camping With."
<P>
Kingdoms may also register a populace/subject group in addition to the official Kingdom encampment. These encampments are listed as "Populace of _________" (e.g.: Populace of Calontir).<P>

Royal Land Agents can e-mail the <a href="mailto:rl@pennsicwar.org">Royalty Liaison</a> for help.
<P>
If you have any other questions, contact the
<a href="mailto:land@pennsicwar.org">Land Staff</a>.

</div>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>