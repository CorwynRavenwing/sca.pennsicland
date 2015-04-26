<?
require_once("include/user.php");

$id = @$_GET['id'];

$success = 0;
$message = "";
if ($id == "STOP") {
	if (! $masquerade) {
		$message = "Invalid call of Masquerade Stop when masquerade is not active, ignoring";
	} else {
		$success++;
		$message = "STOP MASQUERADING";
		
		# $message .= "<br/>BEFORE: user_id $user_id user_id_true $user_id_true masquerade $masquerade";
		
		# $user_id_true	= $user_id_true;	$_SESSION['user_id_true']	= $user_id_true;
		$user_id	= $user_id_true;	$_SESSION['user_id']		= $user_id;
		$masquerade	= 0;			$_SESSION['masquerade']		= $masquerade;
		
		# $message .= "<br/>AFTER: user_id $user_id user_id_true $user_id_true masquerade $masquerade";
	}
} elseif (! $admin) {
	$message = "Please log on as Pennsic Land staff first.";
} elseif ($id) {
	if ($masquerade) {
		$message = "Invalid call of Masquerade when masquerade is active, ignoring";
	} elseif ($user_id != $user_id_true) {
		$message = "Invalid call of Masquerade when user_id ($user_id) != user_id_true ($user_id_true), ignoring";
	} else {
		$success++;
		$message = "MASQUERADE AS USER $id";
		
		# $message .= "<br/>BEFORE: user_id $user_id user_id_true $user_id_true masquerade $masquerade";
		
		# $user_id_true	= $user_id;		$_SESSION['user_id_true']	= $user_id_true;
		$user_id	= $id;			$_SESSION['user_id']		= $user_id;
		$masquerade	= 1;			$_SESSION['masquerade']		= $masquerade;
		
		# $message .= "<br/>AFTER: user_id $user_id user_id_true $user_id_true masquerade $masquerade";
	}
} else {
	$message = "Invalid call of masquerade without an ID, ignoring";
} // endif id

if ($success) {
	$was_admin = $admin;
} else {
	$id = "";
}

# print("_SESSION: <pre>"); print_r($_SESSION); print("</pre>\n");

set_user_session_variables();	# call it again because we've changed everything

require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Masquerade as User", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

print "<h2>$message</h2>\n";
print "<h3>current user: $user_name</h3>\n"; 

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
