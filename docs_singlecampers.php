<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Documents"    => "docs_documents.php",
);

$title = "Single Campers";

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




<h1 align=center>Information for Single Campers</h1>
<ul>
<li>Single campers are those individuals or families who are not camping with a registered group. People who arrive together may camp together.
<p>
<li>Single campers may camp in areas designated as single camper space.
These are the unoccupied areas remaining after the pre-registered groups have established their borders.
<P>
<li>Single campers should not arrive before Sunday of Land Grab weekend.
<P>
<li>Single campers who wish to arrive on Friday or Saturday of Land Grab weekend
must have <a href="http://www.cooperslake.com/prereg/home/index.php">pre-registered with the campground</a>, and must fill out a
<a href="index.php">Group Registration form</a>, using their SCA name as the &quot;Group&quot;
name.  They will participate in the Land Grab process with the other Land Agents.<P>
<P>
<li>There will be maps at <strong>Troll</strong>, <strong>Information Point</strong>,
and <strong>at Public Safety</strong> indicating where the single camping areas are located.
</ul>
<P>
If you have any other questions, contact the
<a href="mailto:land&#064;pennsicwar.org">Land Staff</a>.

</div>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>