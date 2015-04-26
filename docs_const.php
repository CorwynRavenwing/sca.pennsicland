<?
require_once("include/nav.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Documents"		=> "docs_documents.php",
);

$title = "Construction Projects";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

?>

<!-- FreeFind Keywords Words="single independant independent campers" Count="5" -->

<!-- link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="mobile.css" / -->
 
<!-- FreeFind Begin No Index -->

<!-- FreeFind End No Index -->
  <!--?php  include($DOCUMENT_ROOT . "/penn41/landnav.html");  ?-->
 
<div class="clear" style="padding-top: 10px; padding-left:20px"> <!-- whole page -->

<h1>Construction Projects</h1>

While structures may add to the general ambiance of the Pennsic site, please be aware of the following rules when planning a construction project.  
<ol  >
<li>	No structure may be taller than 16 feet. 
<div style="line-height:.5em">&nbsp;</div>

<li>	Construction projects shall be defined as any non-tent structures, including but not limited to gates, towers, houses, scaffolding, etc. 
<div style="line-height:.5em">&nbsp;</div>

<li>    Guy ropes for any structures in camping or merchant areas must be staked within the boundaries of Cooper’s Lake Campground.  No ropes may pass over or through fence lines along local, County or State roads.
<div style="line-height:.5em">&nbsp;</div>

<li>	All construction projects may be inspected on site by the Division of Zoning and Planning.
<div style="line-height:.5em">&nbsp;</div>
<li>	Cooper’s Lake Campground Management and the appropriate Pennsic War Staff reserves the absolute right to order dismantled any construction projects which, in their judgment, are deemed unsafe, unsightly and/or pose an unacceptable risk of injury and/or property damage.
</ol>

If you have any questions, contact the
<a href="mailto:land&#064;pennsicwar.org">Land Staff</a>.

</div>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
