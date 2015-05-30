<?
require_once("include/nav.php");

$webmaster_name_short = "Corwyn Ravenwing";	# should be in include/nav:php; also used elsewhere

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Admin Test Email", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	$test_user_id = @$_GET['user_id'];

	if (! $test_user_id) {
		print "<h2>error: no user_id passed</h2>\n";
		exit(0);
	}
	
	template_load("admin_generic.htm");
	
	$user_record = user_record($test_user_id);
	
	$user_name  = $user_record['user_name'];
	$user_email = $user_record['email_address'];
	
	template_param("head",	"Test User Email"						);
	template_param("body",	"Testing email for account '$user_name': <!-- ($user_id) -->"	);
	
	print template_output();
	
	$email_from = $webmaster_email;			# From: me.  I want errors and replies to come to me
	$email_to   = $user_email;			# Send user's new password to the user himself
	$email_bcc  = $webmaster_email;			# send me a blind copy
	$email_subj = "Pennsic Land Agent email test";	# Subject
	$email_body = "
Greetings,

I am testing the email address for your account: $user_name

If you receive this, your email is set up properly on the Pennsic Land Admin website.

Please let me know if you have any further difficulties.  Thank you.

- $webmaster_name_short
Land Webmin
		";
	$email_headers	= "From: $email_from\r\n"
			. "Bcc: $email_bcc";
	
	$success = mail( $email_to, $email_subj, $email_body, $email_headers );
	
	if ($success) {
		print "<br />A test email was sent to your account's official email address.<br />\n";
	} else {
		print "<br />The test email to your account's official email address FAILED.<br />\n";
	}
	
	print("<br /><b>Email test complete.</b>");
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
