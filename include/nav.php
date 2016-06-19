<?
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

require_once("template.php");
require_once("mode.php");
require_once("user.php");
require_once("cooper.php");  # needed only for fix_cooper_data_count()
require_once("variable.php");

function nav_variables() {
  global $page_name;
  global $webmaster_name, $webmaster_email, $landone_email;

  $page_name    = nav_scriptname();

  $webmaster_name  =  "Corwyn Ravenwing";
  $webmaster_email = "landweb@pennsicwar.org";
  $landone_email   = "land@pennsicwar.org";

  return;
} // end function nav_variables

function nav_getname($path) {
  $name = basename($path, ".php");

  if ($name == "index")  { $name = "main_page"; }
  if ($name == ".")  { $name = "main_page"; }

  # print "{$name}<br/>\n";

  return $name;
} // end function nav_getname

function nav_scriptname() {
  $path = $_SERVER['PHP_SELF'];  # /pennsicland/index.php

  return nav_getname($path);
} // end function nav_scriptname

function nav_start_admin($html_head_block = "", $body_onload = "") {

  $extra_html_head_block = '<link rel=stylesheet href="css/adminmenu.css" type="text/css">';

  if ($html_head_block) { $html_head_block .= "\n"; }
  $html_head_block .= $extra_html_head_block;

  return nav_start($html_head_block, $body_onload);

} // end function nav_start_admin

function nav_start($html_head_block = "", $body_onload = "") {
  global $pennsic_number, $pennsic_roman, $page_name;

  nav_variables();

  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <?
  template_load("head1.htm");  // DELETE 1 WHEN PUBLISHING

  template_param("pennsic_number",  $pennsic_number);
  template_param("pennsic_roman",    $pennsic_roman);
  template_param("page_name",    $page_name);

  template_param("html_head_block",  $html_head_block);
  template_param("body_onload",    $body_onload);

  print template_output();
} // end function nav_start

// load a template and print it without variable substitution
// ... except for $redirect_to, which is needed for login redirect
function nav_template($template) {
  global $redirect_to;
  template_load($template);
  template_param("redirect_to",  $redirect_to);
  print template_output();
} // end function nav_template

function nav_head($title, $crumb_array = 0) {
  global $pennsic_number;

  global $user_id, $user_name, $logon_error;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $w_admin, $masquerade, $user_id_true;

  // top section:

  template_load("pennhdr1.htm");    // DELETE 1 WHEN PUBLISHING, POSSIBLY

  template_param("pennsic_number",  $pennsic_number  );
  template_param("title",           $title           );

  $display_alias = ($alias       ? $alias                   : "*NOBODY*" );
  $display_legal = ($legal_name  ? $legal_name              : "*NOBODY*" );
  $display_admin = ($w_admin
                       ? "<span style='color:red; font-weight:bold'> (ADMIN)</span>"
                       : ($r_admin
                             ? "<span style='color:limegreen; font-weight:bold'> (admin)</span>"
                             : ""
                         )
                   );
  $display_user  = ($user_name   ? $user_name               : "*NOBODY*" );
  $display_group = ($group_name  ? $group_name              : "<b>*NONE*</b>" );

  $display_group = "<b>$display_group</b>";

  if ($masquerade) {
    $user_record_2 = user_record($user_id_true);

    $user_name_true = $user_record_2['user_name'];

    unset($user_record_2);

    $display_user .= " {masquerade by $user_name_true}";
  } // endif masquerade

  template_param("alias",      $display_alias );
  template_param("legal_name",    $display_legal );

  template_param("admin_flag",    $display_admin );
  template_param("user_name",    $display_user  );
  template_param("group_name",    $display_group );

  if ($user_id) {
    $logon_display_string = "Username: $user_name$display_admin "
        . "Agent: $display_alias</b>&nbsp;&nbsp;&nbsp;Group: $display_group";
    template_param("logon_display_string",  $logon_display_string  );
  } else {
    $logon_display_string = "";
    template_param("logon_display_string",  $logon_display_string  );
  }

  print template_output();

  // main site navbar include:
  // @include_once("synch/navbar.html");
  @include_once("include/navbar.html");    // new P42 per Erik the Swede
  // PREVIOUS LINE NEEDS TO USE TEMPLATE INSTEAD [WCH PW43]

  // breadcrumb section:

  $crumb_add = array(
    "PW41" => "http://www.pennsicwar.org/index.html",
    "Departments" => "http://www.pennsicwar.org/penn41/DEPTS/index.html",
  );

  if (!$crumb_array) { $crumb_array = array(); }
  $crumb_array = array_merge($crumb_add, $crumb_array);
  ?>
    <div  id=crumbtrail  class="crumb">
  &nbsp;
  <?
  foreach ($crumb_array as $label => $href) {
    ?>
  <a href="<?=$href?>"><?=$label?></a> &raquo;
    <?
  } // next crumb_array
  ?>
  <?=$title?>
    </div>
  <?

  // local menu goes here
} // end function nav_head

function nav_admin_menu($label_id = "adminmenu") {
  global $user_id, $user_name, $logon_error;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $w_admin, $masquerade, $user_id_true;

  global $count_group_check;

  nav_menu_begin($label_id);

  if ($masquerade) {
      # should mark as an ADMIN type of link:
      nav_menu_group_begin("STOP MASQUERADING",  "admin_masquerade.php?id=STOP");
      nav_menu_group_end();
  } elseif ($user_id) {
      nav_menu_group_begin("Logout",    "logout.php");
      nav_menu_group_end();
  } else {
      nav_menu_group_begin("LOG ON",    "login.php");
      nav_menu_group_end();
  } // endif masquerade / user_id

      nav_menu_group_begin("(Agent)",  "index.php");
      nav_menu_group_end();

      if ($r_admin) {
    nav_menu_group_begin("LAND ADMIN MENU", "admin.php");

    nav_menu_active("Calendar View",  "admin_calendar.php",   "");  // , count
    nav_menu_active("Land One View",  "admin_land_one.php",   "");  // , count
    nav_menu_active("On-Site View",   "admin_onsite.php",     "");
      } else {
    nav_menu_group_begin("ADMINS ONLY",  "");
      } // endif admin

      // always
    nav_menu_group_end();

      if ($r_admin) {
    $count_users         = count_where("user_information");
    $count_logged_on     = current_user_sessions();

    $count_group         = count_where("land_groups");
    $count_group_reg     = count_where("land_groups", "user_id                != 0");
    $count_group_unreg   = count_where("land_groups", "user_id                 = 0");
    $count_group_check   = count_where("land_groups", "status IN ( 2, 3 )
                OR group_name_base = ''
                OR group_soundex = ''
                OR group_metaphone = ''" );
    $count_group_nohist  = "";          # figure out how to count this
    $count_group_bonus   = count_where("land_groups", "bonus_footage          != 0");
    $count_group_compress= count_where("land_groups", "calculated_compression != 0");
    $count_group_notes   = count_where("land_groups", "other_group_information!= '' ");
    $count_admin_notes   = count_where("land_groups", "other_admin_information!= '' ");
    $count_group_kingdom = count_where("land_groups", "exact_land_amount      != 0");
    $count_known_people  = sum_where("land_groups",   "pre_registration_count", "user_id != 0");
    $count_unfixed_groups= fix_cooper_data_count();
    $count_orphan_groups = count_where("land_groups", "pre_registration_count > 0 AND user_id = 0");
    $count_people_prereg = count_where("cooper_data", "group_name not like ':%'");

    # $count_xyzzy       = count_where("xyzzy", "xyzzy != ''");


    nav_menu_group_begin("USERS AND GROUPS",  "");

    // Things that can happen at any time:
    nav_menu_active_count("<b>USERS</b>",    "admin_users.php",    $count_users);
    nav_menu_active_count("User LOGINS",     "admin_login_history.php",  $count_logged_on);
    # the following is no longer necessary, as user creation is not forbidden outside of the registration period [Corwyn PW40]
    # nav_menu_active("Create User",    "admin_user_create.php");

    nav_menu_active_count("<b>GROUPS</b>",  "admin_groups.php",       $count_group);
    nav_menu_active_count("REGISTERED",     "admin_registered.php",   $count_group_reg);
    nav_menu_active_count("UNREG",          "admin_unregistered.php", $count_group_unreg);
    nav_menu_active_count("CHECK NAMES",    "admin_group_check.php",  $count_group_check);
    nav_menu_active_count("w/o HISTORY",    "admin_group_nohist.php", $count_group_nohist);
    nav_menu_active_count("w/NOTES",        "admin_notes.php",        $count_group_notes);
    nav_menu_active_count("w/NOTES (admin)","admin_notes_2.php",      $count_admin_notes);
    nav_menu_active_count("w/BONUSES",      "admin_bonus.php",        $count_group_bonus);
    nav_menu_active_count("w/COMPRESS",     "admin_compress.php",     $count_group_compress);
    nav_menu_active_count("w/KINGDOMS",     "admin_kingdom.php",      $count_group_kingdom);
    nav_menu_active_count("HISTORY",        "admin_history.php",      "");

    nav_menu_group_end();

    nav_menu_group_begin_count("COOPER DATA", "admin_clean.php",          ($count_unfixed_groups+$count_orphan_groups) );
    nav_menu_active_count("Fix Count",        "admin_prereg_count.php",   $count_unfixed_groups);
    nav_menu_active_count("Fix Orphans",      "admin_prereg_cleanup.php", $count_orphan_groups);

    nav_menu_active_count("PREREG",         "admin_prereg.php",       $count_people_prereg);
    nav_menu_active_count("(dup PENN#)",    "admin_penndups.php",     "");
    
    nav_menu_active("COOPER Count",           "admin_cooper_count_registrations.php",  1);
  if ($w_admin) {
    nav_menu_active("COOPER Move",            "admin_cooper_move_tool.php",    1);
    # nav_menu_active("Show Registrations",   "admin_cooper_show_registrations.php",  1);
    # nav_menu_active("Move Registrations",   "admin_cooper_move_registrations.php",  1);
    nav_menu_active("COOPER Create",          "admin_cooper_create_group.php",    1);
    nav_menu_active("COOPER Delete",          "admin_cooper_delete_group.php",    1);
  } else {
    nav_menu_inactive("COOPER Move",            "");
    # nav_menu_inactive("Show Registrations",   "");
    # nav_menu_inactive("Move Registrations",   "");
    nav_menu_inactive("COOPER Create",          "");
    nav_menu_inactive("COOPER Delete",          "");
  }
    nav_menu_group_end();

    nav_menu_group_begin("BLOCK ASSIGNMENT",  "admin_block_help.php", 0, "Click for Help");

  # if ( allow_groupmoves() ) {
    nav_menu_active("BLOCKS",                 "admin_block.php", 0, "Make group-move decisions");
  # } else {
  #   nav_menu_inactive("BLOCKS",    "(after Big Red Button)");
  # } // endif allow_groupmoves

    if ( allow_email() ) {
      nav_menu_active("EMAIL MERGE",          "admin_email.php", 0, "Send emails to groups");
    } else {
      nav_menu_inactive("EMAIL MERGE",        "(after registration closes)");
    } // endif allow_email

    nav_menu_group_end();

    # nav_menu_group_begin("Live Data to Test",    "db_backup.cgi");
    # nav_menu_group_inactive("ERASES previous copy!");
    # nav_menu_group_end();

    # nav_menu_group_begin("Corwyn's Test Page",  "admin_x.php");
    # nav_menu_group_end();

    # nav_menu_group_begin("Apache Log",      "log_watch.cgi");
    # nav_menu_group_inactive("(Does not work)");
    # nav_menu_group_end();

      } // endif admin

  nav_menu_end();
} // end function nav_admin_menu

function nav_admin_leftnav() {
  global $current_date;

  global $user_id, $user_name, $logon_error;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $masquerade, $user_id_true;

  # nav_admin_leftnav_begin();

  # nav_leftnav_inactive("DATE: [$current_date]");

  # nav_admin_leftnav_end();
} // end function nav_admin_leftnav

///////// move these functions out

function nav_menu_begin($label_id) {
  ?>
<div id="<?=$label_id?>-container">
    <div id="<?=$label_id?>">
  <?
} // end function nav_menu_begin

function nav_menu_end() {
  ?>
    </div>
</div>

<br/>
<br/>
  <?
} // end function nav_menu_end

function nav_menu_item($text, $link, $new_page = 0, $title = "", $class = "") {
  print "<!-- called nav_menu_item(text=$text, link=$link, new=$new_page, title=$title, class=$class) -->\n";
  print "\t    ";
  print "<li>";
  print "<a";
  if ($class)  { print " class='$class'";  }
  if ($link)  { print " href='$link'";  }
  if ($title)  { print " title='$title'";  }
  if ($new_page)  { print " target='_blank'";  }
  print ">";
  print $text;
  print "</a>";
  # do not close <li> here
} // end function nav_menu_item

function nav_menu_group_begin_count($text, $link, $count = "", $new_page = 0, $title = "", $class = "") {

  if ($count) {
    $count = "   ($count)";
  } elseif ($count === "0") {        // exact compare to string 0
    $count = "   (0)";
  } elseif ($count === 0) {          // exact compare to number 0
    $count = "   (0)";
  }
  // else leave blank alone

  return nav_menu_group_begin("$text$count", $link, $new_page, $title, $class);

} // end function nav_menu_group_begin_count

function nav_menu_group_begin($text, $link, $new_page = 0, $title = "", $class = "") {
  print "\t<ul>\n";
  nav_menu_item($text, $link, $new_page, $title, $class);
  print "\n";
  print "\t\t<ul>\n";
} // end function nav_menu_group_begin

function nav_menu_group_end() {
  print "\t\t</ul>\n";
  print "\t    </li>\n";
  print "\t</ul>\n";
} // end function nav_menu_group_end

function nav_menu_active_count($text, $link, $count = "", $new_page = 0, $title = "") {

  if ($count) {
    $count = "   ($count)";
  } elseif ($count === 0) {
    $count = "   (0)";
  } elseif ($count === "0") {
    $count = "   (0)";
  }

  return nav_menu_active("$text$count", $link, $new_page, $title);

} // end function nav_menu_active_count

function nav_menu_active($text, $link, $new_page = 0, $title = "") {
  $class = "";
  print "\t";
  nav_menu_item($text, $link, $new_page, $title, $class);
  print "</li>\n";
} // end function nav_menu_active

function nav_menu_inactive($text, $link = "", $new_page = 0, $title = "") {
  $link = "";  # override
  $new_page = 0;  # override
  $class = "inactive";
  print "\t";
  nav_menu_item($text, $link, $new_page, $title, $class);
  print "</li>\n";
} // end function nav_menu_active

///////// end move functions out

function nav_menu($label_id = "landmenu") {
  // replaces nav_leftnav()

  global $user_id, $user_name, $logon_error;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $masquerade, $user_id_true;
  global $registration_open;        // set by include/mode.php

  // require_once("include/landnav3.html");

  nav_menu_begin($label_id);

  if ($masquerade) {
      # should mark as an ADMIN type of link:
      nav_menu_group_begin("STOP MASQUERADING",  "admin_masquerade.php?id=STOP");
      nav_menu_group_end();
  } elseif ($user_id) {
      nav_menu_group_begin("Logout",    "logout.php");
      nav_menu_group_end();
  } else {
      nav_menu_group_begin("LOG ON",    "login.php");
      nav_menu_group_end();
  } // endif masquerade / user_id

  nav_menu_group_begin("LAND AGENT MENU", "land_menu.php");
    nav_menu_active("Land Home",    "index.php");
      if ($user_id) {
    nav_menu_active("Edit Land Agent Info",    "editperson.php");
      } else {
    nav_menu_inactive("Edit Land Agent Info");
      } // endif user_id

      if ($user_id) {
    nav_menu_active("Change Password",    "chgpasswd.php");
      } else {
    nav_menu_inactive("Change Password");
      } // endif user_id

      if ($group_id) {
    nav_menu_active("Edit Group Choices",    "editgroup.php");
      } elseif ($user_id and $registration_open) {
    nav_menu_active("REGISTER A GROUP",    "choose_group.php");
      } else {
    nav_menu_inactive("Edit Group Choices");
      }

      if ($group_id) {
    nav_menu_active("View Group History",    "history.php");
      } else {
    nav_menu_inactive("View Group History");
      } // endif group_id

      if ($group_id) {
    nav_menu_active("View Preregs",      "prereg.php");
      } else {
    nav_menu_inactive("View Preregs");
      } // endif group_id

      if ($group_id) {
    nav_menu_active("Email Neighbors",    "neighbors.php");
      } else {
    nav_menu_inactive("Email Neighbors");
      } // endif group_id

  nav_menu_group_end();

  if ($r_admin) {
      nav_menu_group_begin("(ADMIN)", "admin.php");
      nav_menu_group_end();
  } // endif admin

  nav_menu_group_begin("DOCUMENTS",        "docs_documents.php");
    nav_menu_active("Land Rules",          "docs_rules.php");
    nav_menu_active("Agents' Handbook",    "docs_handbook.php");
    nav_menu_active("Agent Procedures",    "docs_timetable.php");
    nav_menu_active("View Pennsic Maps",   "map.php", 1);
    nav_menu_active("Land Announcements",  "docs_announce.php");
  nav_menu_group_end();

  nav_menu_group_begin("CAMPING INFO",        "docs_camping.php", 0);
    nav_menu_active("Registered Groups",      "view_groups.php");
    nav_menu_active("Construction Projects",  "docs_const.php");
    nav_menu_active("Block Maps",             "map.php", 1);
    nav_menu_active("Single Campers",         "docs_singlecampers.php");
    // nav_menu_active("Tent Rental Procedure",  "http://www.pennsicwar.org/penn41/GENERAL/deliveries.html", 1);
    // nav_menu_active("Tent Rental Companies",  "http://www.pennsicwar.org/penn41/GENERAL/tents.html", 1);
  nav_menu_group_end();

  nav_menu_end();
} // end function nav_menu

function nav_leftnav() {
  global $current_date;

  global $user_id, $user_name, $logon_error;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $masquerade, $user_id_true;
  global $registration_open;        // set by include/mode.php

  nav_leftnav_begin();

  nav_leftnav_inactive("DATE: [$current_date]");
  // always
  nav_leftnav_active("Main Page",        "index.php");    # [generate_main_page]
  if ($user_id) {
    nav_leftnav_active("Edit Land Agent Info",  "editperson.php");  # [generate_edit_information]
  } else {
    nav_leftnav_inactive("Edit Land Agent Info");
  } // endif user_id
  if ($user_id) {
    nav_leftnav_active("Change Password",    "chgpasswd.php");  # [generate_change_password]
  } else {
    nav_leftnav_inactive("Change Password");
  } // endif user_id

  if ($group_id) {
    nav_leftnav_active("Edit Group Choices",  "editgroup.php");  # [~generate_edit_information]
  } elseif ($user_id and $registration_open) {
    nav_leftnav_active("REGISTER A GROUP",    "choose_group.php");  # [generate_group_name]
  } else {
    nav_leftnav_inactive("Edit Group Choices");
  }
  if ($group_id) {
    nav_leftnav_active("View Group History",  "history.php");    # [generate_view_history]
  } else {
    nav_leftnav_inactive("View Group History");
  } // endif group_id
  if ($group_id) {
    nav_leftnav_active("View Prereg",    "prereg.php");    # [generate_view_prereg]
  } else {
    nav_leftnav_inactive("View Prereg");
  } // endif group_id
  if ($group_id) {
    nav_leftnav_active("Email Neighbors",    "neighbors.php");  # [generate_email_other_groups]
  } else {
    nav_leftnav_inactive("Email Neighbors");
  } // endif group_id
  // always
  nav_leftnav_active("View Pennsic Maps",      "map.php");    # [generate_view_pennsic_maps]
  if ($r_admin) {
    nav_leftnav_admin( "ADMIN SECTION",    "admin.php");    # [admin_linkcode]
  } // endif admin
  if ($masquerade) {
    nav_leftnav_admin( "STOP MASQUERADING",    "admin_masquerade.php?id=STOP");  # [stop masquerading]
  } elseif ($user_id) {
    nav_leftnav_active("Logout",      "logout.php");    # [logout]
  } else {
    nav_leftnav_active("Log On",      "login.php");
  } // endif masquerade / user_id

  nav_leftnav_end();
} // end function nav_leftnav

function nav_leftnav_begin() {
  template_load("leftnav_begin.htm");

  print template_output();
} // end function nav_leftnav_begin

function nav_admin_leftnav_begin() {
  template_load("admin_leftnav_begin.htm");

  print template_output();
} // end function nav_leftnav_begin

function nav_leftnav_active($text, $link, $stack=0, $qs="") {
  return nav_leftnav_active_count($text, $link, $count, $stack, $qs);
} // end function nav_leftnav_active

function nav_leftnav_sub_count($text, $link, $count=0, $stack=0, $qs="") {
  return nav_leftnav_level_count($text, $link, "sub", $count, $stack, $qs);
} // end function nav_leftnav_sub_count

function nav_leftnav_active_count($text, $link, $count=0, $stack=0, $qs="") {
  return nav_leftnav_level_count($text, $link, "active", $count, $stack, $qs);
} // end function nav_leftnav_active_count

function nav_leftnav_level_count($text, $link, $class="", $count=0, $stack=0, $qs="") {
  global $page_name;

  $querystring = $_SERVER['QUERY_STRING'];

  # delete any query-string values that don't matter to the navigation
  $queries = array(
      'message',
      'search',
      'submit',
    );

  foreach ($queries as $q) {
    $pattern = "/\b$q=[^&]*&?/";
    $replacement = "";
    $querystring = preg_replace($pattern, $replacement, $querystring);
  } // endfor q

  if ($count) {
    $count = "($count)";
  } elseif ($count === "0") {
    $count = "(0)";
  }

  if ((nav_getname($link) == $page_name) and ($qs == $querystring)) {
    nav_leftnav_highlight_count($text, $count, $stack, $class);
  } elseif ($link) {
    if ($qs) {
      $link .= "?$qs";
    }

    if ($stack) {
      nav_leftnav_class_stack($class, $text, $link, "", $count);
    } else {
      nav_leftnav_class_link($class, $text, $link, $count);
    }
  } else {
    nav_leftnav_inactive($text);
  }
} // end function nav_leftnav_level_count

function nav_leftnav_highlight($text, $stack=0, $class="") {
  if ($stack) {
    nav_leftnav_class_stack("highlight $class", $text, "", "", 0);
  } else {
    nav_leftnav_class_title("highlight $class", $text, "", 0);
  }
} // end function nav_leftnav_highlight

function nav_leftnav_highlight_count($text, $count=0, $stack=0, $class="") {
  if ($stack) {
    nav_leftnav_class_stack("highlight $class", $text, "", "", $count);
  } else {
    nav_leftnav_class_title("highlight $class", $text, "", $count);
  }
} // end function nav_leftnav_highlight_count

function nav_leftnav_inactive($text, $title="") {
  nav_leftnav_class_title("unused", $text, $title, 0);
} // end function nav_leftnav_inactive

function nav_leftnav_admin($text, $link) {
  return nav_leftnav_class_link("admin", $text, $link);
} // end function nav_leftnav_admin

// ======================================================================

function nav_leftnav_class_stack($class, $text, $link, $title="", $count=0) {
  ?>
<div class="stack <?=$class?>">
  <? if ($link) { ?>
    <a href="<?=$link?>">
  <? } // endif link ?>
    <?=$text?>
  <? if ($link) { ?>
    </a>
  <? } // endif link ?>
  <? if ($count) { ?>
  <!-- <?=$count?> -->
  <? } // endif count ?>
</div>
  <?
} // end function nav_leftnav_class_stack

function nav_leftnav_class_title($class, $text, $title="", $count=0) {
  ?>
<li class="<?=$class?>">
  <span class="<?=$class?>" title="<?=$title?>">
    <?=$text?>
  </span>
  <? if ($count) { ?>
  <div class="count"><?=$count?></div>
  <? } // endif ?>
</li>
  <?
} // end function nav_leftnav_class_title

function nav_leftnav_class_link($class, $text, $link, $count=0) {
  ?>
<li class="<?=$class?>">
  <a class="<?=$class?>" href="<?=$link?>">
    <?=$text?>
  </a>
  <? if ($count) { ?>
  <div class="count"><?=$count?></div>
  <? } // endif ?>
</li>
  <?
} // end function nav_leftnav_class_link

// ======================================================================

function nav_leftnav_end() {
  template_load("leftnav_end.htm");

  print template_output();
} // end function nav_leftnav_end

function nav_admin_leftnav_end() {
  template_load("admin_leftnav_end.htm");

  print template_output();
} // end function nav_leftnav_end

function nav_right_begin() {
  template_load("right_begin.htm");

  print template_output();

  $message = @$_GET['message'];

  if ($message) {
    print "<h3 style='color:green'>$message</h3>\n";
  }

  return;
} // end function nav_right_begin

function nav_right_end() {
  global $r_admin;
  global $user_id;
  global $test_mode;
  global $user_record;

  $temp_special_user = -1;    // should be a user id [Corwyn P42]

  if (($r_admin) or ($user_id == $temp_special_user)) {

    require_once("include/javascript.php");    // required for javascript_hidable_div code below

    javascript_hidable_div_begin("DEBUG");

      print("<div style='clear:both'>DEBUG: (shown to admins only)</div><pre>\n");

      print("TEST MODE: "); print($test_mode);     print("\n");
      print("USER:      "); print_r($user_record); print("\n");

      print("_GET:      "); print_r($_GET);        print("\n");
      print("_POST:     "); print_r($_POST);       print("\n");
      print("_SERVER:   "); print_r($_SERVER);     print("\n");
      print("_SESSION:  "); print_r($_SESSION);    print("\n");

      print("</pre>\n");
    javascript_hidable_div_end();
  }

  template_load("right_end.htm");
  print template_output();
} // end function nav_right_end

function nav_footer_panix() {

  # defunct

} // end function nav_footer_panix

function nav_footer_disclaimer() {
  global $current_year;
  global $webmaster_name, $webmaster_email;

  # template_load("template_panix.htm");

  # print template_output();

  template_load("disclaimer.htm");

  template_param("current_year",    $current_year  );

  template_param("webmaster_name",  $webmaster_name        );
  template_param("webmaster_email",  $webmaster_email      );

  print template_output();
} // end function nav_footer_disclaimer

function nav_end() {

  print "<span style='font-size:0.5em'>";
# print synch_file("penn41/navbar.html");
# print synch_file("pwnew.css");
# print synch_file("menu.css");
  print "</span>\n";

  template_load("end.htm");

  print template_output();
} // end function nav_end

function coming_soon($message) {
  template_load("update_coming_soon.htm");
  template_param( 'top_message' , $message );

  print template_output();
} // end function coming_soon

function files_differ($f1, $f2) {
    $READ_LEN = 4096;

    $t1 = @filetype($f1);
    $t2 = @filetype($f2);
    if ($t1 !== $t2) { return 1; }

    $s1 = @filesize($f1);
    $s2 = @filesize($f2);
    if ($s1 != $s2) { return 1; }

    // possibly compare md5_file(x) here

    if(!$fp1 = fopen($f1, 'rb'))
        return 1;

    if(!$fp2 = fopen($f2, 'rb')) {
        fclose($fp1);
        return 1;
    }

    $retval = 0;
    while (!feof($fp1) and !feof($fp2)) {
        if (fread($fp1, $READ_LEN) !== fread($fp2, $READ_LEN)) {
            $retval = 1;
            break;
        } // endif fread
    } // wend feof

    if(feof($fp1) !== feof($fp2)) {
        $retval = FALSE;
    }

    fclose($fp1);
    fclose($fp2);

    return $retval;
} // end function files_differ

// returns a text string describing success or failure
function synch_file($cache_uri /* relative to pennsicwar.org */, $local_file = "", $cache_life = 86400 /* one day */) {
  $cache_folder = "synch";

  if ( ! file_exists($cache_folder) ) {
    return "<span style='color:red'>Error: Cache folder '$cache_folder' not found</span> ";
  }

  if ( ! is_dir($cache_folder) ) {
    return "<span style='color:red'>Error: '$cache_folder' is not a folder</span> ";
  }

  if ( ! is_writeable($cache_folder) ) {
    return "<span style='color:red'>Error: Cache folder '$cache_folder' not writeable</span> ";
  }

  if ($local_file == "") {
    $local_file = basename($cache_uri);
  }

  $datestamp = date("Y-m-d-H-i-s");

  $cache_file = "$cache_folder/$local_file";
  $cache_temp = "$cache_folder/$local_file.tmp";
  $cache_prev = "$cache_folder/${datestamp}_${local_file}";

  $filemtime = @filemtime($cache_file);  // returns FALSE if file does not exist

  if (!$filemtime or (time() - $filemtime >= $cache_life)){
      $f = @file_get_contents("http://www.pennsicwar.org/$cache_uri");
      if ($f) {
    file_put_contents($cache_temp, $f);

    if ( files_differ( $cache_file, $cache_temp) ) {
      @unlink($cache_prev);
      @rename($cache_file, $cache_prev);
      rename($cache_temp, $cache_file);
      return "<span style='color:green'>$local_file: SYNC</span> ";
    } else {
      @unlink($cache_file);
      rename($cache_temp, $cache_file);
      return "<span style='color:blue'>$local_file: OK</span> ";
    } // endif not different
      } else {
      # 999900 is a dark yellow
      return "<span style='color:#999900'>$local_file: FAIL</span> ";
      } // endif f not blankk
  } // endif file too old

  return "";
} // end function synch_file

function image($file_name) {
  $timestamp = @filemtime($file_name);

  if ($timestamp) {
    $retval = "'$file_name?t=$timestamp'";
  } else {
    $image_missing = "/image/no_image.gif";
    $retval = "'$image_missing?t=$file_name'";
  }

  return $retval;
} // end function href

# set up default values:
$errors = 0;
$error_string = "";
?>