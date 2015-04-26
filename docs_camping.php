<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Camping Documents";

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

<h1>Camping Information</h1>

<ul>
<li><a href="view_groups.php">Registered Groups</a>
<li><a href="docs_const.php">Construction Projects</a>
<li><a href="map.php" target="_blank">Block Maps</a>
<li><a href="docs_singlecampers.php">Single Campers</a>
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