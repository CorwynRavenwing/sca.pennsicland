<?
require_once("include/nav.php");

$action			= @$_POST['action'];

nav_start();

$crumb = array(
	"Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Change Password", $crumb );

nav_menu();

nav_right_begin();

	template_load("update_password.htm");
	
	template_param("webmaster_email",	$webmaster_email );
	
$error_string = "";
$errors = 0;

if (! $user_id) {
	$errors++;
} elseif ($action == "Change Password") {
	$password_old	= @$_POST['password_old'];
	$password_1	= @$_POST['password_1'];
	$password_2	= @$_POST['password_2'];
	
	$verified = verify_password(
		$password_old,
		$user_record['password'],
		$user_record['password_salt']
	);
	
	if ( ! $verified ) {
		template_param("password_old_error_string",	error_string("Old password incorrect") );
		$errors++;
	}
	
	if ( ! $password_1 ) {
		template_param("password_error_string",	error_string("New password required") );
		$errors++;
	} elseif ( invalid_password($password_1) ) {
		template_param("password_error_string",	error_string("Invalid characters in new password") );
		$errors++;
	} elseif ( $password_1 != $password_2 ) {
		template_param("password_error_string",	error_string("Passwords do not match") );
		$errors++;
	}
	
	if ($errors) {
		$error_string = top_message();
	} else {
		if ( update_user_password($user_id,$password_1) )
		{
			$error_string = "Update successful";
			$user_record = user_record($user_id);
		} else {
			$error_string = "Update failed";
		}
	} // endif errors
} // endif action
	
	template_param("top_message",	error_string($error_string) );
	
	if ($user_id) {
		print template_output();
	} else {
		print "<h3>Sorry, logon has expired: please log back on and make your changes again.</h3>\n";
	}
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
