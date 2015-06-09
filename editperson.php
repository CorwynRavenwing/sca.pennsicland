<?
require_once("include/nav.php");

$action         = @$_POST['action'];

nav_start();

$crumb = array(
	"Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Edit Person", $crumb );

nav_menu();

nav_right_begin();

	template_load("update_person_info.htm");
	
$error_string = "";
$errors = 0;

if (! $user_id) {
	$errors++;
} elseif ($action == "Reset") {
	$error_string = "Changes reverted.";
} elseif ($action == "Continue") {
	$error_string = "Submit was pressed.";
	
	$legal_name	= @$_POST['legal_name'];
	$alias		= @$_POST['alias'];
	$street_1	= @$_POST['street_1'];
	$street_2	= @$_POST['street_2'];
	$city		= @$_POST['city'];
	$state		= @$_POST['state'];
	$postal_code	= @$_POST['postal_code'];
	$country	= @$_POST['country'];
	$email_address	= @$_POST['email_address'];
	$phone_number	= @$_POST['phone_number'];
	$extension	= @$_POST['extension'];
	
	if (! $legal_name ) {
		template_param("legal_name_error_string",	error_string("Legal name is required") );
		$errors++;
	}
	
	if (! $alias ) {
		template_param("alias_error_string",	error_string("SCA name is required") );
		$errors++;
	}
	
	if (! $street_1 ) {
		template_param("street_1_error_string",	error_string("Address line 1 is required") );
		$errors++;
	}
	
	# $street_2 NOT REQUIRED
	
	if (! $city ) {
		template_param("city_error_string",	error_string("City is required") );
		$errors++;
	}
	
	if (! $state ) {
		template_param("state_error_string",	error_string("State is required") );
		$errors++;
	}
	
	if (! $postal_code ) {
		template_param("postal_code_error_string",	error_string("Postal code is required") );
		$errors++;
	}
	
	if (! $country ) {
		template_param("country_error_string",	error_string("Country is required") );
		$errors++;
	}
	
	if ( ! $email_address ) {
		template_param("email_address_error_string",	error_string("Email address is required") );
		$errors++;
	} elseif ( invalid_email_address( $email_address ) ) {
		template_param("email_address_error_string",	error_string("Invalid email address") );
		$errors++;
	}
	
	if (! $phone_number ) {
		template_param("phone_number_error_string",	error_string("Phone number is required") );
		$errors++;
	}
	
	# $extension NOT REQUIRED
	
	if ($errors) {
		$error_string = top_message();
	} else {
		if ( update_user($user_id,$legal_name,$alias,
			$street_1,$street_2,$city,$state,$postal_code,$country,
			$phone_number,$extension,$email_address) )
		{
			$error_string = "Update successful";
			$user_record = user_record($user_id);
		} else {
			$error_string = "Update failed";
			$user_record = user_record($user_id);
		}
	} // endif errors
} else {
	$errors++;	# print initial screen
	$user_record = user_record($user_id);
} // endif action
	
	# print("user_record: <pre>"); print_r($user_record); print("</pre>\n");
	
	if (! $errors) {
		print("<h2>Updates made successfully.  Click <a href='?'>here</a> to make more edits.</h2>\n");
	} else {
	
		template_param("top_message",	error_string($error_string) );
	
		template_param("legal_name_variable_string",	$user_record['legal_name']	);
		template_param("alias_variable_string",		$user_record['alias']		);
		template_param("street_1_variable_string",	$user_record['street_1']	);
		template_param("street_2_variable_string",	$user_record['street_2']	);
		template_param("city_variable_string",		$user_record['city']		);
		template_param("state_variable_string",		$user_record['state']		);
		template_param("postal_code_variable_string",	$user_record['postal_code']	);
		template_param("country_variable_string",	$user_record['country']		);
		template_param("email_address_variable_string",	$user_record['email_address']	);
		template_param("phone_number_variable_string",	$user_record['phone_number']	);
		template_param("extension_variable_string",	$user_record['extension']	);
	
		if ($user_id) {
			print template_output();
		} else {
			print "<h3>Sorry, logon has expired: please log back on and make your changes again.</h3>\n";
		}
	} // endif errors
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
