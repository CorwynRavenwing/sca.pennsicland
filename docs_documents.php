<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Land Documents";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

?>

<!-- link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="mobile.css" / -->

<!-- FreeFind Begin No Index -->

<!-- FreeFind End No Index -->
<!--?php  include($DOCUMENT_ROOT . "/penn41/landnav.html");  ?-->

 <div class="clear" style="padding-top: 10px; padding-left:20px"> <!-- whole page -->

 <P>

<h1 > Land Rules and Procedures</h1>

<ul>
<li><a href="docs_rules.php">General Land Rules &amp; Procedures</a>
for Individuals, Groups, Royal Encampments<P>

<li><a href="docs_handbook.php">Land Agent Handbook</a>        <P>
<li><a href="docs_timetable.php">Land Agent Timetable &amp; Summary of Procedures</a><P>
<li><a href="docs_singlecampers.php">Single Campers Information</a>  <P>
<li><a href="http://www.pennsicwar.org/penn43/GENERAL/deliveries.html">Tent Rental Procedures</a>

</ul>
<P>
If you have  questions, contact the
<a href="mailto:land&#064;pennsicwar.org">Land Staff</a>.

</div>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>