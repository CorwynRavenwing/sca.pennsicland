<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Setup Year", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {

    if ( allow_nextyear() ) {
	
	$submit = @$_POST['submit'];
	
	template_load("admin_next_year.htm");
	
    if ($submit) {
		
	$errors_found = 0;
	
	$new_year		= @$_POST['current_year'];
	$new_pennsic_number	= @$_POST['pennsic_number'];
	$new_registration_open	= @$_POST['registration_open_date'];
	$new_registration_close	= @$_POST['registration_close_date'];
	$new_pennsic_open	= @$_POST['pennsic_open_date'];
	$new_pennsic_close	= @$_POST['pennsic_close_date'];
	$new_xyzzy		= @$_POST['xyzzy'];
	$new_xyzzy		= @$_POST['xyzzy'];
	$new_xyzzy		= @$_POST['xyzzy'];
	
	if (! is_numeric($new_year) ) {
		$errors_found = 1;
		template_param("current_year_error_string",
			error_string( "Year must be a number" )
		);
	}
	if ( $current_year < $new_year - 1) {
		$errors_found = 1;
		template_param("current_year_error_string",
			error_string( "Year cannot change by more than 1" )
		);
	}
	if ( $current_year > $new_year) {
		$errors_found = 1;
		template_param("current_year_error_string",
			error_string( "Year cannot decrease" )
		);
	}
	if (! is_numeric($new_pennsic_number) ) {
		$errors_found = 1;
		template_param("pennsic_number_error_string",
			error_string( "Pennsic Number must be a number" )
		);
	}
	if ( $pennsic_number < $new_pennsic_number - 1) {
		$errors_found = 1;
		template_param("pennsic_number_error_string",
			error_string( "Pennsic Number cannot change by more than 1" )
		);
	}
	if ( $pennsic_number > $new_pennsic_number ) {
		$errors_found = 1;
		template_param("pennsic_number_error_string",
			error_string( "Pennsic Number cannot decrease" )
		);
	}
	if ( ! is_date( $new_registration_open ) ) {
		$errors_found = 1;
		template_param("registration_open_date_error_string",
			error_string( "registration_open_date must be in format YYYY-MM-DD" )
		);
	}
	if ( ! is_date( $new_registration_close ) ) {
		$errors_found = 1;
		template_param("registration_close_date_error_string",
			error_string( "registration_close_date must be in format YYYY-MM-DD" )
		);
	}
	if ( ! is_date( $new_pennsic_open ) ) {
		$errors_found = 1;
		template_param("pennsic_open_date_error_string",
			error_string( "pennsic_open_date must be in format YYYY-MM-DD" )
		);
	}
	if ( ! is_date( $new_pennsic_close ) ) {
		$errors_found = 1;
		template_param("pennsic_close_date_error_string",
			error_string( "pennsic_close_date must be in format YYYY-MM-DD" )
		);
	}
	if ( $new_registration_open < ($new_year - 1) or $new_registration_open > ($new_year + 1) ) {
		$errors_found = 1;
		template_param("registration_open_date_error_string",
			error_string( "registration_open_date must be in current or previous year" )
		);
	}
	if ( $new_registration_open < ($new_year - 1) . "-08-01" ) {
		$errors_found = 1;
		template_param("registration_open_date_error_string",
			error_string( "registration_open_date must be after last Pennsic" )
		);
	}
	if ( $new_registration_close < $new_year or $new_registration_close > ($new_year + 1) ) {
		$errors_found = 1;
		template_param("registration_close_date_error_string",
			error_string( "registration_close_date must be in current year" )
		);
	}
	if ( $new_pennsic_open < $new_year or $new_pennsic_open > ($new_year + 1) ) {
		$errors_found = 1;
		template_param("pennsic_open_date_error_string",
			error_string( "pennsic_open_date must be in current year" )
		);
	}
	if ( $new_pennsic_close < $new_year or $new_pennsic_close > ($new_year + 1) ) {
		$errors_found = 1;
		template_param("pennsic_close_date_error_string",
			error_string( "pennsic_close_date must be in current year" )
		);
	}
	if ( $new_registration_open > $new_registration_close ) {
		$errors_found = 1;
		template_param("registration_open_date_error_string",
			error_string( "registration_open_date must be before registration_close_date" )
		);
	}
	if ( $new_registration_close > $new_pennsic_open ) {
		$errors_found = 1;
		template_param("registration_close_date_error_string",
			error_string( "registration_close_date must be before pennsic_open_date" )
		);
	}
	if ( $new_pennsic_open > $new_pennsic_close ) {
		$errors_found = 1;
		template_param("pennsic_open_date_error_string",
			error_string( "pennsic_open_date must be before pennsic_close_date" )
		);
	}
	
	# validate page if any errors found
	if( $errors_found ) {
		template_param("top_message", top_message() );
		
	} else {
		change_mode_data( $new_year, $new_pennsic_number, $new_registration_open, $new_registration_close, $new_pennsic_open, $new_pennsic_close );
		
		if ($current_mode != "end of year") {
			change_mode("end of year");
		}
		
		$current_year		= $new_year;
		$pennsic_number		= $new_pennsic_number;
		$registration_open_date	= $new_registration_open;
		$registration_close_date= $new_registration_close;
		$pennsic_open_date	= $new_pennsic_open;
		$pennsic_close_date	= $new_pennsic_close;
		
	} // endif errors
		
    } // endif submit
	
	template_param("current_year",			$current_year			);
	template_param("pennsic_number",		$pennsic_number			);

	template_param("registration_open_date",	$registration_open_date		);
	template_param("registration_close_date",	$registration_close_date	);
	template_param("pennsic_open_date",		$pennsic_open_date		);
	template_param("pennsic_close_date",		$pennsic_close_date		);
	
	print template_output();

    } else {
	print "<h2>Setup Year cannot be run at this time.</h2>\n";
    } // endif mode

} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
