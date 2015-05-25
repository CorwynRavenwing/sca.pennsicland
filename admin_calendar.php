<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
#  "Land Admin Calendar"        => "admin.php",
);

nav_head( "Land Admin Calendar", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

# global $mode_direction;

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  template_load("admin_calendar_page.htm");

  template_param("current_mode",                 $current_mode            );
  template_param("current_submode",              $current_submode         );

  template_param("admin_message",                $ADMIN_MESSAGE           );
  template_param("user_message",                 $user_message            );

  template_param("registration_open_date",       $registration_open_date  );
  template_param("registration_close_date",      $registration_close_date );

  template_param("pennsic_open_date",            $pennsic_open_date       );
  template_param("pennsic_close_date",           $pennsic_close_date      );

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
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>