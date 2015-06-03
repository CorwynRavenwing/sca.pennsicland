<?
# FOLLOWING SECTION MUST NOT PRINT ANYTHING

require_once("include/user.php");

$username_input = isset($_POST['user_name']) ? $_POST['user_name'] : "";
$password_input = isset($_POST['password'])  ? $_POST['password']  : "";
$action         = isset($_POST['action'])    ? $_POST['action']    : "";

global $user_name, $logon_error;
global $legal_name, $alias, $group_id, $group_name;
global $r_admin, $masquerade, $user_id_true;
global $redirect_to;

if ($action == "Submit") {
  $user_id = attempt_user_logon($username_input, $password_input);
  # automatically sets other global variables
}

require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( nav_scriptname(), $crumb );

nav_menu();

nav_right_begin();

if ($action == "Submit") {
  if ($user_id) {
    $redirect_to = @$_POST['redirect_to'];

    if (! $redirect_to) {
      $message = "Logon as " . ($w_admin ? "ADMIN" : ($r_admin ? "admin" :  "")) . " $user_name successful!";
      $redirect_to = "index.php?message=$message";
    }

    redirect_to($redirect_to);
  } else {
    $error_string = "logon failed: $logon_error";

    template_load("failed_login_template.htm");
    template_param("login_error_string",  $error_string);
    print template_output();

    nav_template("template_login.htm");
  } // endif user found
} else {
  // submit not pressed: show the menu instead:

    $redirect_to = @$_SERVER['HTTP_REFERER'];

    if (strpos($redirect_to, 'logout.php') !== FALSE) {
      // if 'logout.php' is the referrer, ignore it
      $redirect_to = "";
    }

    if (strpos($redirect_to, 'index.php') !== FALSE) {
      // if 'index.php' is the referrer, ignore it
      $redirect_to = "";
    }

    if (
         (strpos($redirect_to, 'land.pennsicwar.org') === FALSE)
      && (strpos($redirect_to, 'http://') !== FALSE)
    ) {
      // if referrer is a different website, ignore it
      $redirect_to = "";
    }

    nav_template("welcome_template.htm");
    nav_template("template_login.htm");
} // endif submit

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>