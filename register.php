<?
require_once("include/nav.php");
require_once("include/cooper.php");
require_once("include/land_email.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Register Group";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

$group_record = group_record($group_id);

if (! $group_id) {
  ?>
  <div style='color:blue; font-weight:bold;'>You need to choose a group before you can use this page.  Please click on REGISTER A GROUP on the LAND AGENT menu above.</div>
  <?
} elseif ( ! $group_record['block_choices_valid'] ) {
  ?>
  <div style='color:red; font-size:1.5em; font-weight:bold;'>You need to choose the four blocks you might want to camp in, before you can complete your registration.</div>
  <div style='color:red; font-size:1.2em;'>Please click on the "Edit Group Choices" link, under the LAND AGENT MENU above, to make your choices.</div>
  <?
} else {
  new_generate_registration_complete($user_id);
}

# print "user_record: <pre>"; print_r($user_record); print "</pre><br />\n";
# print "group_record: <pre>"; print_r($group_record); print "</pre><br />\n";

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>