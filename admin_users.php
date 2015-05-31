<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "View Users", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

$new_user_id = @$_REQUEST['id'];

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif ($new_user_id) {

  #set user object to correct user
  #id as carried by the OTHER linkcode field
  if ( ! $user_record = user_record( $new_user_id ) ) {
    print("script_invocation_error: can't load user #" . $new_user_id . " passed as param id");
    exit(0);
  }

  # print("user_record: <pre>"); print_r($user_record); print("</pre>\n");

  #set group object, if it exists
  $new_group_id = @$user_record['group_id'];
  $group_record = group_record( $new_group_id );

  $action   = @$_POST['action'];
  $override = @$_POST['override'];

  template_load("admin_edit_user.htm");

  $errors_found = 0;

  if (! $w_admin) {
    if ($action or $override)
	print "<h2>Your access level does not allow this action.</h2>\n";
	$action = "";
	$override = "";
    }
  } // endif w_admin

    if ($action or $override) {

  print("<h3>UPDATING RECORD ...</h3>\n");

  $new_username              = @$_POST['username'];
  $new_password_1            = @$_POST['password_1'];
  $new_password_2            = @$_POST['password_2'];
  $new_password_hint         = stripslashes( @$_POST['password_hint'] );
  $new_password_hint_answer  = stripslashes( @$_POST['password_hint_answer'] );
  $new_legal_name            = @$_POST['legal_name'];
  $new_sca_name              = @$_POST['sca_name'];
  $new_address_line_1        = @$_POST['address_line_1'];
  $new_address_line_2        = @$_POST['address_line_2'];
  $new_state                 = @$_POST['state'];
  $new_country               = @$_POST['country'];
  $new_city                  = @$_POST['city'];
  $new_postal_code           = @$_POST['postal_code'];
  $new_phone_number          = @$_POST['phone_number'];
  $new_extension             = @$_POST['extension'];
  $new_email_address         = @$_POST['email_address'];

  $username = $user_record['user_name'];
  if ($new_username != $username) {
      if ($error = invalid_username($new_username)) {
    $errors_found++;
    template_param("username_error_string",  error_string("invalid username: $error") );
      } elseif (username_in_use($new_username)) {
    $errors_found++;
    template_param("username_error_string",  error_string("invalid username: already used") );
      }
  }

  if( (! $new_password_1) and (! $new_password_2) ) {
    # do nothing
  } elseif( $new_password_1 != $new_password_2 ) {
    $errors_found++;
    template_param("password_2_error_string", error_string("Passwords do not match") );
  } elseif ($error = invalid_password($new_password_1)) {
    $errors_found++;
    template_param("password_2_error_string", error_string("invalid password; $error") );
  }

  if($error = invalid_email_address( $new_email_address )) {
    $errors_found = 1;
    template_param("email_address_error_string", error_string("invalid email address: $error") );
  }

  # validate page if any errors found
  if( $errors_found and (! $override) ) {
    template_param("top_message", top_message() );

  } else {
    update_user($new_user_id, $new_legal_name, $new_sca_name,
      $new_address_line_1, $new_address_line_2, $new_city,
      $new_state, $new_postal_code, $new_country,
      $new_phone_number, $new_extension, $new_email_address);

    if ($new_password_1) {
      update_user_password($new_user_id, $new_password_1);
    }

    update_user_private($new_user_id, $new_username,
      $new_password_hint, $new_password_hint_answer);

    $user_record['user_name']        = $new_username;
    $user_record['password_hint']    = $new_password_hint;
    $user_record['password_answer']  = $new_password_hint_answer;
    $user_record['legal_name']       = $new_legal_name;
    $user_record['alias']            = $new_sca_name;
    $user_record['street_1']         = $new_address_line_1;
    $user_record['street_2']         = $new_address_line_2;
    $user_record['state']            = $new_state;
    $user_record['country']          = $new_country;
    $user_record['city']             = $new_city;
    $user_record['postal_code']      = $new_postal_code;
    $user_record['phone_number']     = $new_phone_number;
    $user_record['extension']        = $new_extension;
    $user_record['email_address']    = $new_email_address;
  } // endif errors
    } // endif submit

  if ($errors_found) {
    template_param("submit_override_style",    "display:inline"    );
  } else {
    template_param("submit_override_style",    "display:none"      );
  }

  template_param("user_id_variable_string",               $new_user_id                     );

  template_param("username_variable_string",              $user_record['user_name']        );
  template_param("password_1_variable_string",            ""                               );
  template_param("password_2_variable_string",            ""                               );
  template_param("password_hint_variable_string",         $user_record['password_hint']    );
  template_param("password_hint_answer_variable_string",  $user_record['password_answer']  );
  template_param("legal_name_variable_string",            $user_record['legal_name']       );
  template_param("sca_name_variable_string",              $user_record['alias']            );
  template_param("address_line_1_variable_string",        $user_record['street_1']         );
  template_param("address_line_2_variable_string",        $user_record['street_2']         );
  template_param("state_variable_string",                 $user_record['state']            );
  template_param("country_variable_string",               $user_record['country']          );
  template_param("city_variable_string",                  $user_record['city']             );
  template_param("postal_code_variable_string",           $user_record['postal_code']      );
  template_param("phone_number_variable_string",          $user_record['phone_number']     );
  template_param("extension_variable_string",             $user_record['extension']        );
  template_param("email_address_variable_string",         $user_record['email_address']    );

  template_param("user_active_variable_string",           $user_record['active_account']   );

  template_param("time_created_variable_string",          $user_record['time_created']     );

  $reset_password = "forgot_password.php?action=reset_admin_override&amp;id=" . $new_user_id;

  template_param("reset_password_linkcode",               $reset_password                  );

  $test_email = "admin_test_email.php?user_id=" . $new_user_id;

  template_param("test_email_linkcode",                   $test_email                      );

  $become_user = "admin_masquerade.php?id=" . $new_user_id;

  template_param("become_user_linkcode",                  $become_user                     );

  $new_group_id  = user_group($new_user_id);
  $new_group_name  = group_name($new_group_id);
  // $group_name = "" unless defined($group_name);
  if (! $new_group_name) {
    $register_group_link = "choose_group.php?admin_user_id=$new_user_id";   // NEED TO WRITE THIS
    $register_group_href = "<a href='$register_group_link' target='_blank'><b>REGISTER GROUP</b></a>";

    $new_group_name  = "<b>NONE</b>";
    $new_group_name .= "<br/>";
    $new_group_name .= "ID: (0)";
    $new_group_name .= "&nbsp;&nbsp;&nbsp;";
    $new_group_name .= $register_group_href;
    $new_group_name .= "&nbsp;&nbsp;&nbsp;";
    $new_group_id = "ZZZ";
  } else {
    $edit_group_link = "admin_groups.php?id=$new_group_id";
    $edit_group_href = "<a href='$edit_group_link' target='_blank'><b>EDIT GROUP DATA</b></a>";

    $new_group_name  = "<b>$new_group_name</b>";
    $new_group_name .= "<br/>";
    $new_group_name .= "ID: $new_group_id";
    $new_group_name .= "&nbsp;&nbsp;&nbsp;";
    $new_group_name .= $edit_group_href;
  } // endif group_name

  template_param("groupname_variable_string",  $new_group_name  );
  # template_param("group_id",      $new_group_id  );

  print template_output();

} else {
  # no template

  $search = @$_REQUEST['search'];
  $where_clause = "user_name NOT LIKE 'admin_%' AND user_name NOT LIKE 'TEMP_%'";
  $order_by = "";

  if (! $search) { $search = "A"; }

  if ($search) {
    $where_clause .= "\nAND ( ";
    if ($search == "#") {
      # things that begin with a non-letter
      $where_clause .= "   user_name     REGEXP '^[^A-Za-z]'\n";
      # $where_clause .= "OR legal_name    REGEXP '^[^A-Za-z]'\n";
      # $where_clause .= "OR alias         REGEXP '^[^A-Za-z]'\n";
      # $where_clause .= "OR email_address REGEXP '^[^A-Za-z]'\n";
    } elseif (strlen($search) == 1) {
      # username begins with this letter
      $where_clause .= "   user_name     LIKE '$search%'\n";
    } else {
      # any field contains this fragment, or email address starts with this fragment
      $where_clause .= "   user_name     LIKE '%$search%'\n";
      $where_clause .= "OR legal_name    LIKE '%$search%'\n";
      $where_clause .= "OR alias         LIKE '%$search%'\n";
      $where_clause .= "OR email_address LIKE '%$search%@%'\n";
      $where_clause .= "OR email_address LIKE '$search%'\n";
      $where_clause .= "OR group_name    LIKE '%$search%'\n";
    }
    $where_clause .= ")\n";
  }

  $query = user_group_query_nullok($where_clause, $order_by);  # was user_query_mult()
  ?>
<form action='?' method='get'>
<table width='60%' border='1'>
  <tr>
    <td align='center'><b>Search for User:</b>
    <td><input name='search' type='text' value='<?=$search?>' /></td>
  </tr>
  <tr>
    <td colspan='2' align='center'><input name='submit' type='submit' value='Search' /></td>
  </tr>
</table>
</form>

<table width='20%' border='1'>
  <tr>
    <td width='5%' align='right'>
      <b>Jump&nbsp;to:</b>
    </td>
  <?
  $letters = "#ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  foreach (str_split($letters) as $s) {
    ?>
  <td width='1%' align='center'>
    <?
    if ($search == $s) {
      ?>
  (<?=$s?>)
      <?
    } else {
      ?>
  <b><a href='?search=<?=urlencode($s)?>'><?=$s?></a></b>
      <?
    } // endif search
    ?>
  </td>
    <?
  } // next s
    ?>
  </tr>
</table>

  <?
  if (mysql_num_rows($query)) {
    $columns = 0;
    ?>
<table class='sort-table' id='table-1' cellspacing='0' border='1'>
  <thead>
    <tr style="font-weight:bold">
      <td title='CaseInsensitiveString'>User&nbsp;Name</td>  <? $columns++; ?>
      <td title='CaseInsensitiveString'>Command</td>  <? $columns++; ?>
      <td title='CaseInsensitiveString' style='align:center'>SCA&nbsp;Name<br/>(Legal&nbsp;Name)</td>  <? $columns++; ?>
      <td title='CaseInsensitiveString'>Email&nbsp;Address</td>  <? $columns++; ?>
      <td title='CaseInsensitiveString'>Group&nbsp;Registered</td>  <? $columns++; ?>
    </tr>
  </thead>
  <tbody>
    <?
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $that_id = $result['user_id'];
      ?>
    <tr class='<?=$class?>'>
      <td>
        <?=$result['user_name']?>
      </td>
      <td>
        <a href='?id=<?=$that_id?>'>EDIT&nbsp;USER</a><br/>
        <a href='admin_masquerade.php?id=<?=$that_id?>'>BECOME</a>
      </td>
      <td><?=$result['alias']?><br/>(<?=$result['legal_name']?>)</td>
      <td><?=$result['email_address']?></td>
      <td><?=$result['group_name']?>&nbsp;</td>
    </tr>
      <?
    } // next result
    ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>Total of <?=$count?> Users.</b></font>
      </td>
    </tr>
  </tbody>
</table>
    <?
  } else {
    ?>
<h3>(no users found)</h3>
    <?
  }
  mysql_free_result($query);              # delete query object
} // endif id, admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>