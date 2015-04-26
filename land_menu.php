<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Land Home";

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

<h1>Land Agent Menu</h1>

<ul>
<li><a href="index.php">Land Home</a></li>

<? if ($user_id) { ?>
<li><a href="editperson.php">Edit Land Agent Info</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>Edit Land Agent Info</li>
<? } // endif user_id ?>

<? if ($user_id) { ?>
<li><a href="chgpasswd.php.php">Change Password</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>Change Password</li>
<? } // endif user_id ?>

<? if ($group_id) { ?>
<li><a href="editgroup.php">Edit Group Choices</a></li>
<? } elseif ($user_id and $registration_open) { ?>
<li><a href="choose_group.php">REGISTER A GROUP</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>Edit Group Choices</li>
<? } // endif ?>

<? if ($group_id) { ?>
<li><a href="history.php">View Group History</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>View Group History</li>
<? } // endif group_id ?>

<? if ($group_id) { ?>
<li><a href="prereg.php">View Preregs</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>View Preregs</li>
<? } // endif group_id ?>

<? if ($group_id) { ?>
<li><a href="neighbors.php">Email Neighbors</a></li>
<? } else { ?>
<li style='color:grey; font-weight:bold'>Email Neighbors</li>
<? } // endif group_id ?>

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