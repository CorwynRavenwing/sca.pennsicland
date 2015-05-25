#!/usr/local/bin/php
<?
# admin_email_send.php -- just send the email, don't prepare it.  Preparation
#                  is handled by admin_email.php

# require_once("include/nav.php");
require_once("include/connect.php");
require_once("include/mail_merge.php");

# nav_start_admin();

# $crumb = array(
#   "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
#   "Land Admin"      => "admin.php",
# );

# nav_head( "Email Merge", $crumb );

# nav_admin_menu();  // special Admin menu nav

# require_once("include/javascript.php");    // required for template admin_mail_merge_groups

# nav_admin_leftnav();  // special Admin left nav

# nav_right_begin();

$maxlen = 20;

# if (! $r_admin) {
#   print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
# } else {

  $my_merge_id  = @$_REQUEST['merge_id'];

    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'send': quitting</h2>");
      die();
    } else {
      print("<h2>SENDING MAIL MERGE #$my_merge_id</h2>");
      admin_mail_merge_send($my_merge_id);
      $my_merge_id = "";
      print("Click here to <a href='admin_email.php'>return to the email merge page</a>\n");
    } // endif my_merge_id

# } // endif r_admin

# nav_right_end();

# nav_footer_panix();
# nav_footer_disclaimer();

# nav_end();
?>