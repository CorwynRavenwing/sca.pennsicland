<?
# forgot_password.php: allow user to retrieve his password if he has forgotten it.

require_once("include/connect.php");
require_once("include/nav.php");
require_once("include/user.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

$title = "Forgot Password";

nav_head($title, $crumb);

nav_menu();

nav_right_begin();

  generate_lost_password();

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();

function generate_lost_password() {

  $webmaster_name_short = "Corwyn Ravenwing";

  # this code had been called but not defined, in code above that was defined but not called.
  # Did I mention Patrick was a moron?

  # generate_lost_password_main()

  $search_param = @$_POST['search_param'];
  $action       = @$_REQUEST['action'];
  $id           = @$_REQUEST['id'];
  $answer       = @$_REQUEST['answer'];

  $override = ($action == "reset_admin_override");

  if ($override) {
    if (! $w_admin) {
	print "<h2>Your access level does not allow this action.</h2>\n";
	$override = "";
    } // endif w_admin
  } // endif override

  print("<!-- DEBUG: action = '$action' -->\n");

  template_load("forgot_password_template.htm");
  template_param("search_param",  $search_param);

  $body = "";

  $user_action = "";

  if ( $override or ($action == "reset") or ($action == "Submit Answer") ) {
    if ($id) {
      $user = user_record($id);

      template_load("admin_generic.htm");

      $user_name  = $user['user_name'];
      $user_email = $user['email_address'];

      $display_answer = $answer;

      if ($answer) {
        $db_answer = $user['password_answer'];

        if ($answer == $db_answer) {
          print "<h3>Answer correct: resetting password.</h3>\n";
          $user_action = "PW Reset User";
        } else {
          print "<h3>Answer incorrect: please try again.</h3>\n";
          $answer = "";
          $user_action = "PW Reset Bad Answer";
        }
      } # endif answer

      if (! $answer) {
        if ($override) {
          print "<h3>Admin override: resetting password.</h3>\n";
          $display_answer = "ADMIN OVERRIDE";
          $user_action = "PW Reset ADMIN";
        } else {
          $question = $user['password_hint'];

          template_param("head",  "Reset User Password: Ask secret question");
          template_param("body",  "Resetting password for account '$user_name': <!-- ($id) -->");

          print template_output();

          // following should be a template:
          print("
<br />
<form action='?'>
  <input type='hidden' name='id' value='$id' />
<table border='1'>
  <tr>
    <td>Secret Question:</td>
    <td>$question</td>
  </tr>
  <tr>
    <td>Secret Answer:</td>
    <td><input type='text' name='answer' value='$display_answer' size='50' /></td>
  </tr>
</table>
  <input type='submit' name='action' value='Submit Answer' />
</form>
<br />
            "
          );
        } # endif override
      } # endif answer

      if ($answer or $override ) {
        template_param("head",  "Reset User Password");
        template_param("body",  "Resetting password for account '$user_name': <!-- ($id) -->");

        print template_output();

        $password = reset_password($id);

        $header_array = array();
        array_push($header_array, 'From: landweb@pennsicwar.org');  # From: me.  I want errors and replies to come to me
        array_push($header_array, 'Cc: landweb@pennsicwar.org');  # send me a blind copy
        $headers = join("\r\n", $header_array);

        $to = $user_email;            # Send user's new password to the user himself
        $subject = "Pennsic Land Agent password reset";
        $message = "
At your request, the password for your account: $user_name
has been reset to password: $password
(please note that capitalization is important).

You may want to log on and change this to something more easily remembered.

If you have any trouble using this password, please copy and paste the password from
this email into the logon form and the password-change form.

Please let me know if you have any further difficulties.  Thank you.

- $webmaster_name_short
Land Webmin
        ";

        $succ = mail(
          $to,
          $subject,
          $message,
          $headers
          # [, string additional_parameters]
        );

        if ($succ) {
          print "<br />Your new password was sent to your account's official email address.<br />\n";
          print "<br />Once the email arrives, <a href='login.php'>log on</a> using your new password<br />\n";

          print "<br /><b>Password reset complete.</b><br />\n";
        } else {
          print "<br />Your password was reset, but the email telling you your new password\n";
          print "failed for some reason.  You may want to verify that your email address\n";
          print "(presently <b>$user_email</b>) is correct once you get logged on.<br />\n";
          print "<br />Please, <a href='login.php' target='_blank'>log on</a> using your new password.\n";
          print "<br />\n";
          print "<br />account: $user_name\n";
          print "<br />password: $password\n";
          print "<br />\n";
          print "<br />Please note: if you leave this page, <b>I CANNOT</b> retrieve\n";
          print "this password for you again; our only recourse will be to reset it again.\n";
          $user_action = "PW Reset Email Fail";
        } // endif
      } # endif answer

      if ($user_action) {
          record_system_access( $user_action, $id );
          # print "DEBUG: called record_system_access( $user_action, $id )<br/>\n";
      } else {
          # print "DEBUG: user_action blank ($user_action): not recording system action<br/>\n";
      }

      return;
    } else {
      print "<h3>No userid passed.</h3>\n";
    }
  } elseif ($action == "Search") {
    # print( "DEBUG: action was set: trying the search.<br />\n");

    $search_fields_1 = array(
      "user_name",
      "alias",
      "legal_name",
      "email_address",
    );
    $search_fields_2 = array(
      "group_name",
    );

    $ids = array();

    $table = "";

    $table .= "<tr style='background-color:silver; font-weight:bold'>";
    # $table .= "<td>uid</td>";
    $table .= "<td>Account Name</td>";
    $table .= "<td>Real Name</td>";
    $table .= "<td>SCA Name</td>";
    $table .= "<td>Email Address</td>";
    $table .= "<td>Group Name</td>";
    $table .= "<td>Reset</td>";

    $table .= "</tr>";

    $class = "even";
    $count = 0;

    $words = split(" ", $search_param);

    $did_anything = 0;
    if ( count($search_fields_1) ) {
      $number  = 10;
      $start  =  0;
      $where_clauses = array();

      foreach ($words as $value) {
        $this_where_clause_array = array();
        foreach ($search_fields_1 as $key) {
        #  $body .= "$key = '$value'<br />\n";
          array_push($this_where_clause_array, "$key LIKE '%$value%'");
        }
        $this_where_clause = "(" . join(" OR ", $this_where_clause_array) . ")";
        array_push($where_clauses, $this_where_clause);
      }

      $this_where_clause = "( user_name NOT LIKE 'admin_%' )";
      array_push($where_clauses, $this_where_clause);

      $order_by = "";
      $where_clause_AND = join(" AND ", $where_clauses);

      print "<!--\nSQL where clause $where_clause_AND\n-->\n";

      $query = user_query_mult($where_clause_AND);
      while ($result = mysql_fetch_assoc($query)) {

        // print("<!-- DEBUG: result = "); print_r($result); print(" -->\n");

        $id  = $result['user_id'];
        array_push($ids, $id);
        $username = $result['user_name'];
        $legal    = $result['legal_name'];
        $alias    = $result['alias'];
        $email    = $result['email_address'];
        $group = "---";
        if ($id) {
          /*
          if ( $landgroup->load_record_by_user_id( $id ) ) {
            $group = $landgroup->get_group_name;
          }
          */
        }

        $count++;
        $table .= "<tr class='$class'>";

        # $table .= "<td>$id</td>\n";

        $table .= "<td>";
        $table .= obfuscate($username, $words);
        $table .= "</td>\n";

        $table .= "<td>";
        $table .= obfuscate($legal, $words);
        $table .= "</td>";

        $table .= "<td>";
        $table .= $alias;
        $table .= "</td>";

        $table .= "<td>";
        $table .= obfuscate($email, $words);
        $table .= "</td>";

        $table .= "<td align='center'>";
        $table .= $group;
        $table .= "</td>";

        $table .= "<td align='center'>";
        if ($id) {
          $table .= "<a href='?action=reset&id=$id'>";
          $table .= "reset";
          $table .= "</a>";
        } else {
          $table .= "(-)  <!-- ID was '$id' -->";
        }
        $table .= "</td>";

        $table .= "</tr>";

        $class = ($class == "odd") ? "even": "odd";
      } # next user

      # $table .= "<tr><td colspan='10' bgcolor='blue'>&nbsp;</td></tr>\n";

      $did_anything = 1;
    }

    if ($did_anything) {
      if ($count) {
        $body .= "<table border='1'>$table</table>\n";
        $body .= "<h4>Total of $count matching accounts found.</h4>";
      } else {
        $body .= "<h4>No matching accounts found: try a different search phrase.</h4>";
      }
    } else {
      $body .= "No boxes were filled in: please try again.<br />";
    }
  } elseif ($action) {
    print "<h3>Unknown action '$action' passed, quitting.</h3>\n";
    return;
  } else {
    # print( "DEBUG: action was not set: show search screen.<br />\n");
    $body .= "Fill in any identifying information to search for your account.";
  }

#   template_param("head",  "???");
  template_param("body",  $body);

  print template_output();
} // end function generate_lost_password

function obfuscate($input, $words) {
  # print "DEBUG: input '$input' " . __LINE__ . "<br />\n";
  # print "DEBUG: words (" . join(",", $words) . ") " . __LINE__ . "<br />\n";

  $mask = $input;

  foreach ($words as $word) {
    $len = strlen($word);
    $stars = str_repeat("*", $len);
    if ($len >= 3) {
      $mask = str_ireplace($word, $stars, $mask);
    } else {
      $mask = str_ireplace( " $word "  , " $stars ", $mask);
      $mask = preg_replace("/^$word /i",  "$stars ", $mask);
      $mask = preg_replace("/ $word$/i", " $stars",  $mask);
    }
    # print "DEBUG: word '$word' stars '$stars' mask '$mask' " . __LINE__ . "<br />\n";
  }
  # print "DEBUG: mask '$mask' " . __LINE__ . "<br />\n";

  $extensions = array("com", "net", "org", "edu");

  foreach ($extensions as $ext) {
    $word  = "[.]$ext";
    $stars = str_repeat("*", strlen($ext)+1 );
    $mask = preg_replace("/$word$/i", $stars,  $mask);
    # print "DEBUG: word '$word' stars '$stars' mask '$mask' " . __LINE__ . "<br />\n";
  }
  # print "DEBUG: mask '$mask' " . __LINE__ . "<br />\n";

  $letters = array(' ', '-', '_', '@', '.');

  foreach ($letters as $word) {
    $stars = str_repeat("*", strlen($word) );
    $mask = preg_replace("/[$word]/i", $stars,  $mask);
    # print "DEBUG: word '$word' stars '$stars' mask '$mask' " . __LINE__ . "<br />\n";
  }
  # print "DEBUG: mask '$mask' " . __LINE__ . "<br />\n";

  $mask_array    = str_split($mask);  # split into single letters
  $letters_array = str_split($input);
  $obfus_array   = array();

  # print "walk array DEBUG:";

  foreach ($mask_array as $s) {
    $l = array_shift($letters_array);
    if ($s == "*") {
      $o = $l;
    } else {
      $o = "*";
    }
    array_push($obfus_array, $o);
    # print "($s,$l,$o)";  # DEBUG
  }
  # print ":DEBUG<br />\n";

  $obfus = join("", $obfus_array);
  # print "DEBUG: return obfus '$obfus' " . __LINE__ . "<br /><br />\n";

  return $obfus;
} // end function obfuscate