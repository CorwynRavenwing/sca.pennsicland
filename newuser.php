<?
require_once("include/nav.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "New User", $crumb );

nav_menu();

nav_right_begin();

	template_load("newuser.htm");
	
	$errors_found = 0;
	
	$action					= (isset($_POST['action'])				? $_POST['action']				: "");
	$legal_name				= (isset($_POST['legal_name'])			? $_POST['legal_name']			: "");
	$alias					= (isset($_POST['alias'])				? $_POST['alias']				: "");
	$street_1				= (isset($_POST['street_1'])			? $_POST['street_1']			: "");
	$street_2				= (isset($_POST['street_2'])			? $_POST['street_2']			: "");
	$state					= (isset($_POST['state'])				? $_POST['state']				: "");
	$country				= (isset($_POST['country'])				? $_POST['country']				: "");
	$city					= (isset($_POST['city'])				? $_POST['city']				: "");
	$postal_code			= (isset($_POST['postal_code'])			? $_POST['postal_code']			: "");
	$phone					= (isset($_POST['phone_number'])		? $_POST['phone_number']		: "");
	$extension				= (isset($_POST['extension'])			? $_POST['extension']			: "");
	$email_address			= (isset($_POST['email_address'])		? $_POST['email_address']		: "");
	$requested_user_name	= (isset($_POST['requested_user_name'])	? $_POST['requested_user_name']	: "");
	$requested_password_1	= (isset($_POST['requested_password_1'])? $_POST['requested_password_1']: "");
	$requested_password_2	= (isset($_POST['requested_password_2'])? $_POST['requested_password_2']: "");
	$password_hint			= (isset($_POST['password_hint'])		? $_POST['password_hint']		: "");
	$password_answer		= (isset($_POST['password_answer'])		? $_POST['password_answer']		: "");
	
	if ($action) {
		if ($reason = invalid_username($requested_user_name)) {
			$errors_found = 1;
			template_param("user_name_error_string", error_string("invalid username: $reason") );
		} elseif ( username_in_use($requested_user_name) ) {
			$errors_found = 1;
			template_param("user_name_error_string", error_string("sorry, that username is in use, please pick another") );
		}
		if (! $legal_name) {
			$errors_found = 1;
			template_param("legal_name_error_string", error_string("legal name required") );
		}
		if (! $alias) {
			$errors_found = 1;
			template_param("alias_error_string", error_string("SCA name required") );
		}
		if (! $street_1) {
			$errors_found = 1;
			template_param("street_1_error_string", error_string("street_1 required") );
		}
		/*
		if (! $street_2) {
			$errors_found = 1;
			template_param("street_2_error_string", error_string("street_2 required")   );
		}
		*/
		if (! $state) {
			$errors_found = 1;
			template_param("state_error_string", error_string("state required")   );
		}
		if (! $country) {
			$errors_found = 1;
			template_param("country_error_string", error_string("country required") );
		}
		if (! $city) {
			$errors_found = 1;
			template_param("city_error_string", error_string("city required")    );
		}
		if (! $postal_code) {
			$errors_found = 1;
			template_param("postal_code_error_string", error_string("postal_code required")      );
		}
		if (! $phone) {
			$errors_found = 1;
			template_param("phone_number_error_string", error_string("phone_number required") );
		}
		/*
		if (! $extension) {
			$errors_found = 1;
			template_param("extension_error_string", error_string("extension required") );
		}
		*/
		if ($reason = invalid_email_address($email_address)) {
			$errors_found = 1;
			template_param("email_address_error_string", error_string("invalid email address: $reason") );
		}
		#validate and update password
		if ($reason = invalid_password($requested_password_1)) {
			$errors_found = 1;
			template_param("password_error_string", error_string("invalid password: $reason") );
		} elseif($requested_password_1 != $requested_password_2) {
			$errors_found = 1;
			template_param("password_error_string", error_string("Passwords do not match") );
		}
		
		if(! $password_hint )
		{
			$errors_found = 1;
			template_param("password_hint_error_string", error_string("password_hint required") );
		}
		
		if(!$password_answer)
		{
			$errors_found = 1;
			template_param("password_answer_error_string", error_string("password answer required") );
		}
	}
	
	template_param("legal_name_variable_string",      $legal_name          );
	template_param("alias_variable_string",           $alias               );
	template_param("street_1_variable_string",        $street_1            );
	template_param("street_2_variable_string",        $street_2            );
	template_param("state_variable_string",           $state               );
	template_param("country_variable_string",         $country             );
	template_param("city_variable_string",            $city                );
	template_param("postal_code_variable_string",     $postal_code         );
	template_param("phone_number_variable_string",    $phone               );
	template_param("extension_variable_string",       $extension           );
	template_param("email_address_variable_string",   $email_address       );
	template_param("user_name_variable_string",		  $requested_user_name );
    template_param("password_1_variable_string",      $requested_password_1);
    template_param("password_2_variable_string",      $requested_password_2);
    template_param("password_hint_variable_string",   $password_hint       );
    template_param("password_answer_variable_string", $password_answer     );

	# validate page if any errors found
	if ($action) {
		if( $errors_found ) {
			template_param("top_message", top_message() );
		} else {
			$result = create_user(
				$requested_user_name,$requested_password_1,$legal_name,$alias,
				$street_1,$street_2,$city,$state,$postal_code,$country,
				$phone,$extension,$email_address,$password_hint,$password_answer);
			if ($result) {
				template_load("newuser_success.htm");
				print template_output();
				
				print "<br/><br/>\n";
				
				template_load("login.htm");
				template_param("user_name",		$requested_user_name	);
				template_param("password",		$requested_password		);
				print template_output();
			} else {
				print "Failed to create user: " . mysql_error();
			}
			exit(0);
		}
	} else {
		template_param("top_message", "Enter your information:" );
	} // endif validate form
	
	print template_output();

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
