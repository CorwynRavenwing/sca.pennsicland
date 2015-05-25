<?
require_once("include/nav.php");
require_once("include/land_email.php");

$html_head_block = '<script type="text/javascript" language="javascript" src="js/check_block_answers.js"></script>';

$body_onload = "check_block_answers();";

nav_start_admin($html_head_block, $body_onload);

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "View All Groups", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

$id      = @$_REQUEST['id'];
$action  = @$_REQUEST['action'];

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif ($id) {
  template_load("admin_edit_group.htm");

  $error_string = "";
  $errors = 0;

  $group_rec = group_record($id);

  if (! $group_rec) {
    print("<h2>No such group found.</h2>\n");
    # should print footers here first
    exit(0);
  } elseif ($action == "Reset") {
    $error_string = "Changes reverted.";
  } elseif ($action == "register") {
    if (! $w_admin) {
      print("<h2>Your access level does not allow this action.</h2>");
      exit(0);
    } // endif w_admin

    # catch 'register this group' action
    $error_string = "<span style='font-weight:bold; font-size:1.25em'>Group Registration Complete.</span>";
    new_generate_registration_complete(0, $id);  // "id" is the group id, we don't know the user id here
    # ... then show the normal edit screen again
  } elseif ($action == "Continue") {
    if (! $w_admin) {
      print("<h2>Your access level does not allow this action.</h2>");
      exit(0);
    } // endif w_admin

    # admin_evaluate_group_information

    $error_string = "Submit was pressed.";

    $groupname               = @$_POST['groupname'               ];  $groupname = stripslashes($groupname);
    $first_block_choice      = @$_POST['first_block_choice'      ];
    $second_block_choice     = @$_POST['second_block_choice'     ];
    $third_block_choice      = @$_POST['third_block_choice'      ];
    $fourth_block_choice     = @$_POST['fourth_block_choice'     ];
    $final_block_location    = @$_POST['final_block_location'    ];
    $block_choices_valid     = @$_POST['block_choices_valid'     ];
    $on_site_representative  = @$_POST['on_site_representative'  ];
    $compression_percentage  = @$_POST['compression_percentage'  ];
    $other_group_information = @$_POST['other_group_information' ];
    $other_admin_information = @$_POST['other_admin_information' ];
    $reserved_group          = @$_POST['reserved_group'          ];
    $new_group               = @$_POST['new_group'               ];
    $staff_group             = @$_POST['staff_group'             ];
    $registration_complete   = @$_POST['registration_complete'   ];
    $exact_land_amount       = @$_POST['exact_land_amount'       ];
    $new_user_id             = @$_POST['user_id'                 ];
    $bonus_footage           = @$_POST['bonus_footage'           ];
    $bonus_reason            = @$_POST['bonus_reason'            ];
    # $xyzzy                 = @$_POST['xyzzy'                   ];

    if ($new_user_id != $group_rec['user_id']) {

      if ($new_user_id) {
        if (register_group($id, $new_user_id)) {
          # print("DEBUG: Registered group to user $new_user_id<br/>\n");
        } else {
          $errors++;
          template_param( "user_id_error_string", error_string("error registering group") );
        }
      } else {
        if (unregister_group($id)) {
          # print("DEBUG: Un-registered group<br/>\n");
        } else {
          $errors++;
          template_param( "user_id_error_string", error_string("error un-registering group") );
        }
      } // endif user_id zero

    } else {
      # print("DEBUG: group registration unchanged<br/>\n");
    } // endif user_id changed

    if ($groupname != $group_rec['group_name']) {

      if ($reason = invalid_groupname($groupname) ) {
        $errors++;
        template_param( "groupname_error_string", error_string("invalid groupname '$groupname' ($reason)") );
      } elseif ($existing_group = group_id_by_name($groupname) ) {
        $errors++;
        template_param( "groupname_error_string", error_string("groupname '$groupname' in use for groupid '$existing_group'") );
      } elseif ( group_set_name( $id, $groupname ) ) {
        # print("DEBUG: Set group name<br/>\n");
      } else {
        $errors++;
        template_param( "groupname_error_string", error_string("failed to set groupname to '$groupname'") );
      }

    } else {
      # print("DEBUG: group name unchanged<br/>\n");
    } // endif groupname changed

    if ( ($first_block_choice  != block_name( $group_rec['first_block_choice' ] ) )
      or ($second_block_choice != block_name( $group_rec['second_block_choice'] ) )
      or ($third_block_choice  != block_name( $group_rec['third_block_choice' ] ) )
      or ($fourth_block_choice != block_name( $group_rec['fourth_block_choice'] ) )
      or ($block_choices_valid !=             $group_rec['block_choices_valid']   )
        ) {
      if (set_block_choices($id, $first_block_choice, $second_block_choice,
          $third_block_choice, $fourth_block_choice, $block_choices_valid )
        ) {
        # print("DEBUG: Set block choices<br/>\n");
      } else {
        $errors++;
        template_param( "first_block_choice_error_string", error_string("failed to set block choices") );
      }
    } else {
      # print("DEBUG: block choices unchanged<br/>\n");
    } // endif choices changed

    if ( $final_block_location != block_name( $group_rec['final_block_location' ] ) ) {

      if (group_set_final_block_location($id, $final_block_location )) {
        # print("DEBUG: Set final block location<br/>\n");
      } else {
        $errors++;
        template_param( "final_block_location_error_string", error_string("failed to set final block location") );
      }
    } // endif final location changed

    if ( ($on_site_representative  != $group_rec['on_site_representative' ] )
      or ($compression_percentage  != $group_rec['compression_percentage' ] )
      or ($other_group_information != $group_rec['other_group_information'] )
      or ($other_admin_information != $group_rec['other_admin_information'] )
        ) {
      if (set_group_data($id, $on_site_representative, $compression_percentage, $other_group_information, $other_admin_information ) ) {
        # print("DEBUG: Set onsite rep, compression, and other info<br/>\n");
      } else {
        $errors++;
        template_param( "on_site_representative_error_string", error_string("failed to set onsite rep, compression, and other info") );
      }
    } else {
      # print("DEBUG: onsite rep, compression, and other info unchanged<br/>\n");
    } // endif choices changed

    if ( $exact_land_amount != $group_rec['exact_land_amount'] ) {
      if (group_set_exact_land_amount( $id, $exact_land_amount )) {
        # print("DEBUG: Set exact_land_amount<br/>\n");
      } else {
        $errors++;
        template_param( "final_block_location_error_string", error_string("failed to set exact_land_amount") );
      }
    } else {
      # print("DEBUG: exact_land_amount unchanged<br/>\n");
    } // endif final location changed

    if ( ($reserved_group        != $group_rec['reserved_group'       ] )
      or ($new_group             != $group_rec['new_group'            ] )
      or ($registration_complete != $group_rec['registration_complete'] )
        ) {
      if (set_group_flags($id, $reserved_group, $new_group, $registration_complete) ) {
        # print("DEBUG: Set group flags<br/>\n");
      } else {
        $errors++;
        template_param( "reserved_group_error_string", error_string("failed to set group flags") );
      }
    } else {
      # print("DEBUG: group flags unchanged<br/>\n");
    } // endif choices changed

    if ( $bonus_footage != $group_rec['bonus_footage'] ) {
      if (group_set_bonus_footage( $id, $bonus_footage)) {
        # print("DEBUG: Set bonus_footage<br/>\n");
      } else {
        $errors++;
        template_param( "bonus_footage_error_string", error_string("failed to set bonus_footage") );
      }
    } else {
      # print("DEBUG: bonus_footage unchanged<br/>\n");
    } // endif final location changed

    if ( $bonus_reason != $group_rec['bonus_reason'] ) {
      if (group_set_bonus_reason( $id, $bonus_reason)) {
        # print("DEBUG: Set bonus_reason<br/>\n");
      } else {
        $errors++;
        template_param( "bonus_reason_error_string", error_string("failed to set bonus_reason") );
      }
    } else {
      # print("DEBUG: bonus_reason unchanged<br/>\n");
    } // endif final location changed

    if ($errors) {
      $error_string = top_message();
      $group_rec = group_record($id);
    } else {
      $error_string = "Update successful";
      $group_rec = group_record($id);
    } // endif errors
  } else {
    $errors++;  # print initial screen
  } // endif action

  template_param("top_message",  error_string($error_string) );

  # admin_edit_group_information

  template_param("group_id_variable_string"      , $id  );

  $group_time_registered = $group_rec['time_registered'];
  if ($group_time_registered) {
    $group_time_registered = date("Y-m-d H:i:s T", $group_time_registered ) ;
  } else {
    $group_time_registered = "(not registered)";
  }

  template_param("groupname_variable_string"             , $group_rec['group_name']            );
  template_param("user_id_variable_string"               , $group_rec['user_id']               );
  template_param("time_registered_variable_string"       , $group_time_registered              );
  template_param("reserved_group_variable_string"        , $group_rec['reserved_group']        );
  template_param("new_group_variable_string"             , $group_rec['new_group']             );
  template_param("staff_group_variable_string"           , $group_rec['staff_group']           );
  template_param("registration_complete_variable_string" , $group_rec['registration_complete'] );

  template_param("group_status_variable_string"          , group_status_name( $group_rec['status'] ) );

  $group_name_base = $group_rec['group_name_base'];
  $group_soundex   = $group_rec['group_soundex'];
  $group_metaphone = $group_rec['group_metaphone'];

  template_param("group_name_base_variable_string"  , $group_name_base        );
  template_param("group_soundex_variable_string"    , $group_soundex          );
  template_param("group_metaphone_variable_string"  , $group_metaphone        );

  $same_name_base  = count_where("land_groups", " group_name_base = '$group_name_base' ");
  $same_soundex    = count_where("land_groups", " group_soundex   = '$group_soundex'   ");
  $same_metaphone  = count_where("land_groups", " group_metaphone = '$group_metaphone' ");

  $same_name_base--;  # don't count this group matching itself!  [Corwyn PW41]
  $same_soundex--;
  $same_metaphone--;

  template_param("same_name_base_variable_string"          , $same_name_base        );
  template_param("same_soundex_variable_string"            , $same_soundex          );
  template_param("same_metaphone_variable_string"          , $same_metaphone        );

  template_param("block_list"  , block_list_options("", 1, 1) );

  template_param("first_block_choice_variable_string"      , block_name( $group_rec['first_block_choice']   ) );
  template_param("second_block_choice_variable_string"     , block_name( $group_rec['second_block_choice']  ) );
  template_param("third_block_choice_variable_string"      , block_name( $group_rec['third_block_choice']   ) );
  template_param("fourth_block_choice_variable_string"     , block_name( $group_rec['fourth_block_choice']  ) );
  template_param("final_block_location_variable_string"    , block_name( $group_rec['final_block_location'] ) );

  template_param( "block_choices_valid"                    , 0 );  # default to "no"

  $complete_registration_linkcode = "<a href='?id=$id&action=register'>Finish Registration (sends confirmation emails)</a>";
  template_param( "complete_registration_linkcode"         , $complete_registration_linkcode );

  template_param("bonus_footage_variable_string"           , $group_rec['bonus_footage']        );
  template_param("bonus_reason_variable_string"            , $group_rec['bonus_reason']        );

  template_param("compression_percentage_variable_string"  , $group_rec['compression_percentage']      );

  template_param("calculated_square_footage_variable_string",$group_rec['calculated_square_footage']    );
  template_param("alloted_square_footage_variable_string"   , $group_rec['alloted_square_footage']      );
  template_param("exact_land_amount_variable_string"        , $group_rec['exact_land_amount']      );
  template_param("other_group_information_variable_string"  , $group_rec['other_group_information']      );
  template_param("other_admin_information_variable_string"  , $group_rec['other_admin_information']      );
  template_param("on_site_representative_variable_string"   , $group_rec['on_site_representative']      );

  $count_mailmerge = count_where("mailmerge_recipients", "group_id = '$id'");
  template_param("email_merges_count_string"                , $count_mailmerge          );

  $count_history = count_where("land_group_history", "group_id = '$id'");
  template_param("group_history_count_string"               , $count_history          );

  print template_output();
} else {
  # no template

  $where_clause = "";
  $order_by = "group_name";

  $search = @$_REQUEST['search'];

  if (! $search) { $search = "A"; }

  if ($search) {
    $where_clause .= "( ";    # was " AND "
    if ($search == "#") {
      # things that begin with a non-letter
      $where_clause .= "   group_name              REGEXP '^[^A-Za-z]'\n";
    } elseif (strlen($search) == 1) {
      # username begins with this letter
      $where_clause .= "   group_name              LIKE '$search%'\n";
    } else {
      # any field contains this fragment, or email address starts with this fragment
      $where_clause .= "   group_name              LIKE '%$search%'\n";
      $where_clause .= "OR on_site_representative  LIKE '%$search%'\n";
      $where_clause .= "OR other_group_information LIKE '%$search%'\n";
      $where_clause .= "OR other_admin_information LIKE '%$search%'\n";
    }
    $where_clause .= ")\n";
  }

  $query = group_query_mult($where_clause, $order_by);
  ?>
<form action='?' method='get'>
<table width='60%' border='1'>
  <tr>
    <td align='center'><b>Search for Group:</b>
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
    <tr style="background-color:silver; font-weight:bold;">
      <td title='CaseInsensitiveString'>Group Name</td>              <? $columns++; ?>
      <td title='CaseInsensitiveString'>Legal Name</td>              <? $columns++; ?>
      <td title='CaseInsensitiveString'>Sca Name</td>                <? $columns++; ?>
      <td title='CaseInsensitiveString'>User Name</td>               <? $columns++; ?>
      <td title='CaseInsensitiveString'>On-site Rep</td>             <? $columns++; ?>
      <td title='CaseInsensitiveString'>Land Agent Comments</td>     <? $columns++; ?>
      <td title='CaseInsensitiveString'>Administrative Comments</td> <? $columns++; ?>
    </tr>
  </thead>
  <tbody>
    <?
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $g_id        = $result['group_id'];
      $u_id        = $result['user_id'];
      $group_name  = $result['group_name'];
      $on_site_rep = $result['on_site_representative'];
      $comments    = $result['other_group_information'];
      $comments_2  = $result['other_admin_information'];

      $max_size = 20;
      if (strlen($comments) > $max_size+3) {
        $comments = substr($comments,0,$max_size) . "...";
      }
      if (strlen($comments_2) > $max_size+3) {
        $comments_2 = substr($comments_2,0,$max_size) . "...";
      }

      $user_rec         = user_record($u_id);    # add left join to query?
      $user_legal_name  = $user_rec['legal_name'];
      $user_alias       = $user_rec['alias'];
      $user_username    = $user_rec['user_name'];
      ?>
    <tr class='<?=$class?>'>
      <td>
        <a href='?id=<?=$g_id?>' target='_blank'>
          <nobr><?=$group_name?></nobr>
        </a>
      </td>
      <td>
      <? if ($u_id) { ?>
        <a href='admin_users.php?id=<?=$u_id?>' target='_blank'>
          <?=$user_legal_name?>
        </a>
      <? } else { ?>
          (not&nbsp;registered)
      <? } // endif ?>
      </td>
      <td><?=$user_alias?></td>
      <td><?=$user_username?></td>
      <td><nobr><?=$on_site_rep?></nobr></td>
      <td><nobr><?=$comments?></nobr></td>
      <td><nobr><?=$comments_2?></nobr></td>
    </tr>
      <?
    } // next result

    ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>Total of <?=$count?> Groups.</b></font>
      </td>
    </tr>
  </tbody>
</table>
    <?
  } else {
    ?>
<h3>(no groups found)</h3>
    <?
  }
  mysql_free_result($query);              # delete query object
} // endif id, r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>