<?php
# user.php: functions regarding the user_information object

require_once("session.php");

$SUCCESS = "successful_login";

$current_time = time();

$user_id  = @$_SESSION['user_id'];  $_SESSION['user_id']    = $user_id;
$masquerade  = @$_SESSION['masquerade'];  $_SESSION['masquerade']    = $masquerade;
$user_id_true  = @$_SESSION['user_id_true'];  $_SESSION['user_id_true']  = $user_id_true;

require_once("connect.php");

require_once("group.php");

set_user_session_variables();  // call it now

function set_user_session_variables() {
  global $user_id, $user_name, $user_record;
  global $legal_name, $alias, $group_id, $group_name;
  global $r_admin, $w_admin, $masquerade, $user_id_true;

  if ($user_id) {
    $user_record = user_record($user_id);

    $user_name  = $user_record['user_name'];
    $legal_name  = $user_record['legal_name'];
    $alias    = $user_record['alias'];
    $group_id  = user_group($user_id);
    $group_name  = group_name($group_id);
    if ( is_admin_ro_account($user_name) ) {
      $r_admin        = 1;
      $w_admin        = 0;
      # $masquerade   = 0;
      $user_id_true = $user_id;
    } elseif ( is_admin_rw_account($user_name) ) {
      $r_admin        = 1;
      $w_admin        = 1;
      # $masquerade   = 0;
      $user_id_true = $user_id;
    } else {
      $r_admin        = 0;
      $w_admin        = 0;
      # $masquerade   = 0;
      # $user_id_true = 0;
    } // endif is_admin_account
  } else {
    $user_name    = "";
    $legal_name   = "";
    $alias        = "";
    $group_id     = 0;
    $group_name   = "";
    $r_admin      = 0;
    $w_admin      = 0;
    $masquerade   = 0;
    $user_id_true = 0;
  }

  $_SESSION['user_id']      = $user_id;
  $_SESSION['user_name']    = $user_name;    # unused?
  $_SESSION['legal_name']   = $legal_name;   # unused?
  $_SESSION['alias']        = $alias;        # unused?
  $_SESSION['group_id']     = $group_id;     # unused?
  $_SESSION['group_name']   = $group_name;   # unused?
  $_SESSION['admin']        = 0;             # unused?
  $_SESSION['r_admin']      = $r_admin;      # unused?
  $_SESSION['w_admin']      = $w_admin;      # unused?
  $_SESSION['masquerade']   = $masquerade;
  $_SESSION['user_id_true'] = $user_id_true;
} // end function set_user_session_variables

# function user_logon could use this function:
function user_by_username($username_input) {
  $username_input = mysql_real_escape_string($username_input);
  $where_clause = "user_name = '$username_input'";

  $user_record = user_query($where_clause);

  return $user_record;
} // end function user_by_username

function user_id_by_username($username_input) {
  $user_record = user_by_username($username_input);

  if ($user_record) {
    $user_id     = $user_record['user_id'];
  } else {
    $user_id = 0;
  } // endif user_record

  return $user_id;
} // function user_id_by_username

#time to lock out users who have failed login 3 times
#in seconds
$lockout_time = 180;

#number of attempts before system locks users out
$max_failed_login_attempts = 3;

function db_time( $time ) {
  return date("Y-m-d H:i:s", $time);
} // end function db_time

# Stores login data for password system
# returns 1 if successful
function record_system_access($access_type, $userid_if_successful_login) {
  $current_time = db_time( time() );

  $host    = $_SERVER['HTTP_HOST'];  # was REMOTE_HOST
  $referer  = $_SERVER['HTTP_REFERER'];
  $user_agent  = $_SERVER['HTTP_USER_AGENT'];
  $remote_addr  = $_SERVER['REMOTE_ADDR'];
  $request_method  = $_SERVER['REQUEST_METHOD'];

  $sql = "INSERT INTO user_logins (
      user_id,
      access_type,
      http_refferer,
      http_user_agent,
      remote_addr,
      remote_host,
      request_method,
      login_time
    ) VALUES (
      '$userid_if_successful_login',
      '$access_type',
      '$referer',
      '$user_agent',
      '$remote_addr',
      '$host',
      '$request_method',
      '$current_time'
    )";

  if (headers_sent()) { print "<!-- record_system_access SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  return 1;
} // end function record_system_access

# logs user on, if username and password match.  Returns user_id.
# Sets session variables, therefore MUST BE CALLED BEFORE PRINTING ANYTHING!
# Returns 0, and sets global variable logon_error, if there was a problem logging the user on.
function attempt_user_logon($user_name, $user_pass) {
  global $user_id, $logon_error, $user_record;
  global $lockout_time, $max_failed_login_attempts;
  global $SUCCESS;

  #checks if username or password are blank
  if( ! $user_name ) {
    record_system_access( "blank_username", 0 );
    $logon_error = 'Please enter a user name';
    $user_id = 0;
    return 0;
  }

  if ( invalid_username($user_name) ) {
    record_system_access( "username_malformed", 0 );
    $logon_error = 'Username contains illegal characters';
    $user_id = 0;
    return 0;
  }

  $user_name = mysql_real_escape_string($user_name);
  $where_clause = "user_name = '$user_name'";

  $user_record = user_query($where_clause);

  if (! $user_record) {
    record_system_access( "no_such_user: $user_name", 0 );
    $logon_error = "NO SUCH USER";
    $user_id = 0;
    return 0;
  }

  $user_id = $user_record['user_id'];

  if( ! $user_pass ) {
    record_system_access( "blank_password", $user_id );
    $logon_error = 'Please enter a password';
    $user_id = 0;
    return 0;
  }

  if ( invalid_password($user_pass) ) {
    record_system_access( "password_malformed", $user_id );
    $logon_error = 'Password contains illgal characters';
    $user_id = 0;
    return 0;
  }

  if( check_IP_logins( $ENV{'REMOTE_ADDR'} ) ) {
    #checks if IP has tried to log in too many times
    record_system_access( "too_many_attempts", $user_id );
    $lockout_minutes = $lockout_time / 60;
    $logon_error = "Your login has been blocked: please try back in $lockout_minutes minutes";
    $user_id = 0;
    return 0;
  }

  # print "DEBUG: user record returned by user_query() follows: <pre>";
  # print_r($user_record);
  # print "</pre>\n";

  if ( verify_password($user_pass, $user_record['password'], $user_record['password_salt']) ) {
    $logon_error = "PASSWORD OKAY";
    # don't delete user_id
    set_user_session_variables();
    # print "DEBUG: $logon_error<br/>\n";
    record_system_access( $SUCCESS, $user_id );
    return $user_id;
  } else {
    record_system_access( "password_invalid", $user_id );
    $logon_error = "BAD PASSWORD";
    $user_record = 0;
    $user_id = 0;
    return 0;
  }

  return $user_id;
} // end function attempt_user_logon

# CTests for existance of more than $max_failed_login_attempts
# failed logins in the last ten minutes.
# ARGS database handle,  ip of user calling script
# RETURNS undef if failed 1 if proceed

function query_login_history( $user_id="", $limit=10 ) {
  # was global $SUCCESS

  $where_clause = "";
  if ($user_id) {
    $where_clause = "WHERE user_id = '$user_id' ";
  }

  $sql = "SELECT *
    FROM user_logins
    $where_clause
    ORDER BY login_id DESC
    LIMIT $limit
  ";
  # was WHERE remote_addr = '$remote_addr'
  # was   AND login_time  > '$lockout_horizon'
  # was   AND access_type != '$SUCCESS'
  # was ORDER BY login_time DESC

  if (headers_sent()) { print "<!-- record_system_access SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  return $query;  # caller must iterate and destroy query
} // end function query_login_history

function check_IP_logins( $remote_addr ) {
  global $lockout_time, $max_failed_login_attempts;
  global $SUCCESS;

  $lockout_horizon = db_time(time - $lockout_time);

  $sql = "SELECT Count(*) AS num
    FROM user_logins
    WHERE remote_addr = '$remote_addr'
      AND login_time  > '$lockout_horizon'
      AND access_type != '$SUCCESS' ";

  if (headers_sent()) { print "<!-- record_system_access SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  $result = mysql_fetch_assoc($query);

  $num = $result['num'];

  print "<!-- found $num failed logins (out of $max_failed_login_attempts) since time $lockout_horizon from IP $remote_addr -->\n";

  if( $num > $max_failed_login_attempts ) {
      return 1;
  } else {
     return 0;
  }
} // end function check_IP_logins

function user_logon($username_input, $password_input) {

  die("called old version of user_logon() at " . __FILE__ . " line " . __LINE__);

} # end function user_logon

function user_record($user_id) {
  $where_clause = " user_id = '$user_id' ";

  $result = user_query($where_clause);

  return $result;
} // end function user_record

function user_query($where_clause, $order_by = "") {
  $query = user_query_mult($where_clause, $order_by);  # perform multiple query
  $result = mysql_fetch_assoc($query);      # pick the first one
  mysql_free_result($query);        # delete query object

  return $result;            # return array of column data
} # end function user_query

function user_query_mult($where_clause, $order_by = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }
  if (! $order_by)   { $order_by = "user_name";                 }

  $sql = "SELECT *
    FROM user_information
    $where_clause
    ORDER BY $order_by ";

  if (headers_sent()) { print "<!-- user_query_mult SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  return $query;  # caller iterates and should destroy this object
} // end function user_query_mult

# This version of the function will return ONLY users who have registered a group
function user_group_query($where_clause, $order_by = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }
  if (! $order_by)   { $order_by = "user_name";                 }

  $where_clause = str_replace("user_id", "u.user_id", $where_clause);

  $sql = "SELECT u.*, g.group_name
    FROM user_information AS u
    INNER JOIN land_groups AS g USING(user_id)
    $where_clause
    ORDER BY $order_by ";

  if (headers_sent()) { print "<!-- user_group_query SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return $query;  # caller iterates and should destroy this object
} // end function user_group_query

# This version of the function will return ALL users, those who have not registered a group will have a null group_name
function user_group_query_nullok($where_clause, $order_by = "") {
  if ($where_clause) { $where_clause = " WHERE $where_clause "; }
  if (! $order_by)   { $order_by = "user_name";                 }

  $where_clause = str_replace("user_id", "u.user_id", $where_clause);

  $sql = "SELECT u.*, g.group_name
    FROM user_information AS u
    LEFT JOIN land_groups AS g USING(user_id)
    $where_clause
    ORDER BY $order_by ";

  if (headers_sent()) { print "<!-- user_group_query SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return $query;  # caller iterates and should destroy this object
} // end function user_group_query

function user_group($user_id) {
  $where_clause = " user_id = '$user_id' ";

  $result = group_query($where_clause);

  if ($result) {
    $group_id = $result['group_id'];
  } else {
    $group_id = "";
  }

  return $group_id;
} // end function user_group

// input: email address to test;
// output: "" if success, failure reason if failure.
function invalid_email_address($email_address) {
  if (! $email_address)
    return "blank";
  if (preg_match("/[^-_A-Za-z0-9.@]/", $email_address))
    return "invalid character";
  if (! preg_match("/^[-_A-Za-z0-9.]*@[-_A-Za-z0-9.]*[.][-_A-Za-z0-9]*$/", $email_address))
    return "invalid format";
  return "";
} // end function invalid_email_address

function username_in_use($un) {
  $result = user_query(" user_name = '$un' ");
  if ($result) {
    $result = 0;
    return 1;
  } else {
    return 0;
  }
} // end function username_in_use

// input: possible username
// output: "" if success, failure reason if failure.
function invalid_username($un) {
  if (! $un)
    return "blank";
  if (preg_match("/[^-_A-Za-z0-9]/", $un))
    return "invalid character";
  return "";
} // end function validate_username

// input: possible password
// output: "" if success, failure reason if failure.
function invalid_password($pw) {
  if (! $pw)
    return "blank";
  if (preg_match("/[^-_A-Za-z0-9`~!@#$%^&*()=+[]\\{}|,.\/<>?]/", $pw))
    return "invalid character";
  return "";
} // end function validate_password

function verify_password_raw($input_password, $stored_password, $stored_salt) {

  if ($reason = invalid_password($input_password)) {
    // don't actually do anything with $reason here
    return 0;
  }

  #hash the salt with the data
  $digest = md5( $stored_salt . $input_password);

  # print "DEBUG: digest__________: $digest <br/>\n";

  if( $stored_password == $digest ) {
    return 1;
  } else {
    return 0;
  } // endif passwords match
} // end function verify_password_raw

function verify_password($input_password, $stored_password, $stored_salt) {

  if ($pass = verify_password_raw($input_password, $stored_password, $stored_salt)) {
    return 1;
  } else {
    # try without final space characters
    $truncated_password = trim($input_password);

    if ($pass = verify_password_raw($truncated_password, $stored_password, $stored_salt)) {
      return 1;
    } else {
      # try truncated to 12 characters wide
      $truncated_password = trim(substr($input_password, 0, 12));

      return verify_password_raw($truncated_password, $stored_password, $stored_salt);
    } // endif truncated
  } // endif verify_password

} // end function verify_password

# SHOULD REALLY PUT THIS IN THE DATABASE [Corwyn 2007]
function is_admin_rw_account($user_name) {
  switch($user_name) {
    case 'angusland':     # angus taylor
    case 'baronangus':    # angus kerr
    case 'BaronDevon':
    case 'caradawc':
    case 'corwyn':        # corwyn ravenwing
    case 'dagmar':
    case 'de_labarre':
    case 'emerson':       # g emerson true
    case 'evan':          # Evan
    case 'winterstar':    # Magdalena, L1 PW44
    case 'masterjohn':    # master john littleton
    case 'Ursus':         # Gaerwen
    case 'wharmon':       # corwyn ravenwing
      # print "IS ADMIN ACCOUNT ($user_name)<br />\n";
      return 1;
      break;

    default:
      # print "IS NOT AN ADMIN ACCOUNT ($user_name)<br />\n";
      return 0;
  } // end switch
} // end function is_admin_rw_account

function is_admin_ro_account($user_name) {
  switch($user_name) {
    case 'aakin':         # aakin the mapmaker
    case 'Caryl':         # Countess Caryl
    case 'corwyn':        # corwyn ravenwing readonly account
    case 'kegslayer':     # Nameneeded, L2 PW44
      # print "IS ADMIN ACCOUNT ($user_name)<br />\n";
      return 1;
      break;

    default:
      # print "IS NOT AN ADMIN ACCOUNT ($user_name)<br />\n";
      return 0;
  } // end switch
} // end function is_admin_ro_account

function is_admin_account($user_name) {
  die("call to obsolete function is_admin_account()" .
        " at file " . __FILE__ . ", line " . __LINE__);
} // end function is_admin_account

function error_string($string) {
  $error_html =
             "<font face='Arial, Helvetica, sans-serif' size='2'
              color='red'>
              <br>error</font>";
  $error_html = str_replace("error", $string, $error_html);

  return $error_html;
} // end function error_string

function top_message() {
  $topmessage  = "<font color='red'>PLEASE CORRECT ENTRIES IN RED</FONT>";
  return $topmessage;
}

// returns 1 for success, 0 for failure, mysql_error() may include the reason
function create_user($requested_user_name,$requested_password,$legal_name,$alias,
  $street_1,$street_2,$city,$state,$postal_code,$country,
  $phone,$extension,$email_address,$password_hint,$password_answer)
{
  $salt = generate_salt();
  # print "DEBUG: salt $salt<br />\n";

  $salted_password = $salt . $requested_password;
  # print "DEBUG: salted_password $salted_password<br />\n";

  $digest = md5( $salt . $requested_password);
  # print "DEBUG: digest $digest<br />\n";

  $requested_user_name  = mysql_real_escape_string($requested_user_name  );
  $legal_name    = mysql_real_escape_string($legal_name    );
  $alias      = mysql_real_escape_string($alias    );
  $street_1    = mysql_real_escape_string($street_1    );
  $street_2    = mysql_real_escape_string($street_2    );
  $city      = mysql_real_escape_string($city    );
  $state      = mysql_real_escape_string($state    );
  $postal_code    = mysql_real_escape_string($postal_code    );
  $country    = mysql_real_escape_string($country    );
  $phone      = mysql_real_escape_string($phone    );
  $extension    = mysql_real_escape_string($extension    );
  $email_address    = mysql_real_escape_string($email_address  );
  $password_hint    = mysql_real_escape_string($password_hint  );
  $password_answer  = mysql_real_escape_string($password_answer  );

  $sql = "INSERT INTO user_information
    (user_name,password_salt,password,legal_name,alias,street_1,street_2,
    city,state,postal_code,country,
    phone_number,extension,email_address,password_hint,password_answer,
    time_created,active_account,temporary_account) VALUES
    ('$requested_user_name','$salt','$digest','$legal_name','$alias',
    '$street_1','$street_2','$city','$state','$postal_code','$country',
    '$phone','$extension','$email_address','$password_hint','$password_answer',
    NOW(),'T','F')";

  # print "DEBUG: sql $sql<br />\n";

  if (headers_sent()) { print "<!-- create_user SQL:\n$sql\n-->\n"; }

  mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return mysql_affected_rows();
} // end function create_user

function update_user_private($user_id, $requested_user_name, $password_hint, $password_answer) {
  $user_id             = mysql_real_escape_string($user_id             );
  $requested_user_name = mysql_real_escape_string($requested_user_name );
  $password_hint       = mysql_real_escape_string($password_hint       );
  $password_answer     = mysql_real_escape_string($password_answer     );

  $sql = "UPDATE user_information
    SET user_name    = '$requested_user_name',
        password_hint  = '$password_hint',
        password_answer  = '$password_answer'
    WHERE user_id = '$user_id'
    ";

  if (headers_sent()) { print "<!-- update_user_private SQL:\n$sql\n-->\n"; }

  mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__ . "<br/>SQL:\n$sql\n");

  return mysql_affected_rows();
} // end function update_user_private

function update_user($user_id,$legal_name,$alias,
  $street_1,$street_2,$city,$state,$postal_code,$country,
  $phone,$extension,$email_address) {

  $user_id    = mysql_real_escape_string($user_id    );
  $legal_name    = mysql_real_escape_string($legal_name    );
  $alias      = mysql_real_escape_string($alias    );
  $street_1    = mysql_real_escape_string($street_1    );
  $street_2    = mysql_real_escape_string($street_2    );
  $city      = mysql_real_escape_string($city    );
  $state      = mysql_real_escape_string($state    );
  $postal_code    = mysql_real_escape_string($postal_code    );
  $country    = mysql_real_escape_string($country    );
  $phone      = mysql_real_escape_string($phone    );
  $extension    = mysql_real_escape_string($extension    );
  $email_address    = mysql_real_escape_string($email_address  );

  $sql = "UPDATE user_information
    SET legal_name    = '$legal_name',
        alias         = '$alias',
        street_1      = '$street_1',
        street_2      = '$street_2',
        city          = '$city',
        state         = '$state',
        postal_code   = '$postal_code',
        country       = '$country',
        phone_number  = '$phone',
        extension     = '$extension',
        email_address = '$email_address'
    WHERE user_id = '$user_id'
    ";

  if (headers_sent()) { print "<!-- update_user SQL:\n$sql\n-->\n"; }

  mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return mysql_affected_rows();
} // end function update_user

function update_user_password($user_id,$requested_password) {
  $salt = generate_salt();
  # print "DEBUG: salt $salt<br />\n";

  $salted_password = $salt . $requested_password;
  # print "DEBUG: salted_password $salted_password<br />\n";

  $digest = md5( $salt . $requested_password);
  # print "DEBUG: digest $digest<br />\n";

  $user_id    = mysql_real_escape_string($user_id    );

  $sql = "UPDATE user_information
    SET password_salt = '$salt',
        password      = '$digest'
    WHERE user_id = '$user_id'
    ";

  if (headers_sent()) { print "<!-- update_user SQL:\n$sql\n-->\n"; }

  mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return mysql_affected_rows();
} // end function update_user_password

function generate_salt() {
    return create_random(32);
} // end function generate_salt

function create_random($len) {
  $chars = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789");
  $count = count($chars);

  $random_string = "";
  while ($len--) {
    $random_string .= $chars[ rand(0, $count-1) ];
  }

  return( $random_string );
} // function create_random

function reset_password($user_id) {
  $new_password = create_random(8);

  if ( update_user_password($user_id, $new_password) ) {
    return $new_password;
  } else {
    return 0;
  }
} // end function reset_password

function redirect_to($url) {
  $url = addslashes($url);    # was urlencode()
  $url_display = preg_replace("/\?.*$/", "...", $url);
  print "Please wait while you are redirected to <a href='$url'>$url_display</a><br />\n";
  flush();
  sleep(2);
  print "
<script language='javascript' type='text/javascript'>
  window.location.href = '$url';
</script>
  ";
  return;
} // end function redirect_to

?>