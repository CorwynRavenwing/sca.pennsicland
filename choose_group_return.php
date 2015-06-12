<?
require_once("include/nav.php");

$action         = @$_POST['action'];

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Returning Group";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

$error_string = "";
$errors = 0;

#check to make sure creation of new groups is allowed at this time
if (! $registration_open  /* and not admin override */ ) {
  template_load("message.htm");

  template_param( "header", "Registration is closed" );

  template_param(
    "top_message",
    "Creation of new users, and registration of groups, has closed for the year."
  );

  print template_output();
} elseif (! $user_id) {
  template_load("message.htm");

  template_param( "header", "Logon Required" );

  template_param(
    "top_message",
    "You need to <a href='login.php'>log on</a> before choosing your group."
  );

  print template_output();
} elseif ($group_id) {
  print "<h3>You have already chosen a group [$group_id/$group_name].</h3>\n";
} else {
  template_load("returning_group.htm");

  $group_name = stripslashes( @$_POST['group_name'] );    // undo magic_quotes_gpc [Corwyn P42]

  if ($action) {
    if (! $group_name ) {
      $error_string = "Group name is required";
      $errors++;
    } else {
      $group_id = group_id_by_name($group_name);
      if (! $group_id) {
        $error_string = "Update failed: group (" . htmlencode($group_name) . ") not found";
      } elseif ( register_group($group_id, $user_id) ) {
        $user_record  = user_record($user_id);
        $message = "Successfully registered group ($group_id," . addslashes($group_name) . ",$user_id)";
        redirect_to("register.php?message=$message");
        exit(0);
      } else {
        $error_string = "Update failed";
      }
    } // endif errors

    template_param("top_message", error_string( $error_string ) );
  } // endif action

  if (! $group_id) {
    $reserved = 1;    // we don't actually prevent people from seeing reserved groups [Corwyn P42]
    $html_field = unregistered_group_list($reserved);

    // template_param("top_message", "error goes here");
    template_param("possible_matchings", $html_field);

  } // endif
  print template_output();
}

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>