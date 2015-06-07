<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
#  "Land Admin"      => "admin.php",
);

nav_head( "Land Admin", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

# global $mode_direction;

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  print "<h3 style='color:green; font-weight:bold;'>\n";
  print "The content that used to be on this page has moved to\n";
  print "the <a href='admin_calendar.php'>Admin Calendar</a> page.\n";
  print "</h3>\n";

  require_once("include/map_dir.php");  // side effect: prints error if missing

  template_load("admin_main_page.htm");

  template_param("current_mode",                 $current_mode                             );
  template_param("current_submode",              $current_submode                          );

  template_param("admin_message",                $ADM_MSG                                  );
  template_param("user_message",                 $user_message                             );

  template_param("registration_open_date",       $registration_open_date                   );
  template_param("registration_close_date",      $registration_close_date                  );

  template_param("pennsic_open_date",            $pennsic_open_date                        );
  template_param("pennsic_close_date",           $pennsic_close_date                       );

  template_param("class_normal;not open yet",    $mode_direction["normal;not open yet"]    );
  template_param("class_normal;reg open",        $mode_direction["normal;reg open"]        );
  template_param("class_normal;reg closed",      $mode_direction["normal;reg closed"]      );
  template_param("class_locked",                 $mode_direction["locked"]                 );
  template_param("class_pennsic prep;negotiate", $mode_direction["pennsic prep;negotiate"] );
  template_param("class_pennsic prep;pennsic",   $mode_direction["pennsic prep;pennsic"]   );
  template_param("class_pennsic prep;done",      $mode_direction["pennsic prep;done"]      );
  template_param("class_data moved",             $mode_direction["data moved"]             );
  template_param("class_end of year",            $mode_direction["end of year"]            );

  print template_output();

  print("<br/>\n");
  print("<br/>\n");
  /*
  print("<span style='font-size:1.2em; font-weight:bold; color:brown; '>\n");
  print("The old version of this website is now so far out-of-date,\n");
  print("that I hesitate to suggest that anyone use it.  Please contact me immediately\n");
  print("if you find something that's not working in this version.\n");
  print("<br/>- Corwyn\n");
  print("</span>\n");

  print("<br/>\n");
  print("<br/>\n");
  */
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>