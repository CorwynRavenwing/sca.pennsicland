<?
require_once("include/nav.php");
require_once("include/user.php");

$action         = @$_POST['action'];

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Choose Group";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

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
    "You need to log on before choosing your group."
  );

  print template_output();
} elseif ($group_id) {
  print "<h3>You have already chosen a group [$group_id/$group_name].</h3>\n";
} else {
  $errors_found = 0;
  $error_string = "";
  $new_group_name = "";
  
  if ($action) {
    $new_group_name = @$_POST['new_group_name'];
    print "<!-- DEBUG: provided group name '$new_group_name' -->\n";
    $new_group_name = preg_replace("/  +/"," ",$new_group_name);  # multiple spaces -> one space
    $new_group_name = trim($new_group_name);            # no space at beginning or end
    print "<!-- DEBUG: fixed group name '$new_group_name' -->\n";

    if( $new_group_name == "" ) {
      $errors_found++;
      $error_string = "This field must be filled in";
    } elseif ($reason = invalid_groupname($new_group_name) ) {
      $errors_found++;
      $error_string = "Invalid group name: $reason";
    } else {
      $new_group_id = group_id_by_name($new_group_name);
      if ($new_group_id) {
        $errors_found++;
        $error_string = "Cannot create that group: it already exists.<br/>Instead, click link 'Existing Groups Click Here', above, if your group existed last year, or choose another group name.";
      } else {
        $new_group_id = create_group($new_group_name);
        if (! $new_group_id) {
          $errors_found++;
          $error_string = "Failed to create group";
        } else {
          register_group($new_group_id, $user_id);

          $message = "Successfully created group ($new_group_id,$user_id)";
          redirect_to("editgroup.php?message=$message");
          exit(0);
        }
      }
    }

    if ($errors_found) {
      // not sure what to do here, the error message is already set
    } else {
      // should not get here, all paths leading here include exit(0)
    }
  } // endif action

  template_load("new_group_name_template.htm");

  // if error found,
  template_param("group_name_error_string", error_string($error_string) );
  template_param("group_name_variable_string", $new_group_name);

  /*
  -> "new_returning_group_name"
  -> "cancel_keep_user"
  */
  print template_output();
}

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>