<?
require_once("include/nav.php");

$html_head_block = '<script type="text/javascript" language="javascript" src="js/check_block_answers.js"></script>';

$body_onload = "check_block_answers();";

nav_start($html_head_block, $body_onload);

$crumb = array(
  "Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Edit Group", $crumb );

nav_menu();

nav_right_begin();

if (! $user_id) {
  ?>
<span style="color:red; font-size:1.5em; font-weight:bold;">
Please log on to edit your group information.
</span>
  <?
} elseif (! $group_id) {
  ?>
<table border="0" cellpadding="4" cellspacing="0" align="left" >
<tr>
  <td valign="top">
    Group:
  </td>
  <td colspan="2" valign="top">
    <input type="text" disabled="disabled" name="groupname" id="groupname" value="(NONE)" />
  </td>
</tr>
<tr>
  <td colspan="3" align="center">
    <font color="red">
  <?
  if ($registration_open) {
    ?>
      <b>You have not yet selected the group that you are the land agent for.<br /><br />
      Please click <a href="choose_group.php">here</a> to choose a group.</b>
    <?
  } else {
    ?>
      <font style='color:blue; font-weight:bold'>You did not select a group this year,
      and group registration deadline has passed.</font>
    <?
  } // endif registration open
  ?>
    </font>
  </td>
</tr>
</table>
  <?
    } else {

  template_load("update_group_info_template.htm");

  $block_locked_reason = "";
  $compress_locked_reason = "";
  $block_choice_disabled = "";

  $action  = @$_POST['action'];

  $id = $group_id;

  $group_rec = group_record($id);

  if ($allow_block_changes) {
    $form_onsubmit          = "return block_answers_enforce();";
  } else {
    $form_onsubmit          = "block_answers_warn();";
    $block_locked_reason    = "block assignments are being made";
    $compress_locked_reason = "block assignments are being made";
    $block_choice_disabled  = "disabled='disabled'";
  } // endif allow_block_changes

  # kind of dumb, should really have an "i am a kingdom camp" flag on group instead
  if ( preg_match( "/^Kingdom of /i", $group_name ) ) {
    $block_locked_reason    = "Kingdom group";
    $compress_locked_reason = "Kingdom group";
  }

  template_param( "form_onsubmit",           $form_onsubmit    );
  template_param( "blocks_locked_reason",    $block_locked_reason  );
  template_param( "compress_locked_reason",  $compress_locked_reason  );

  template_param( "block_choices_valid",    0      );  # default to "no"

  if ($block_locked_reason) {
    template_param( "block_changes_label",        "Cannot change block choices: "    );
  }

  if ($compress_locked_reason) {
    template_param( "compression_changes_label" , "Cannot change compression percent: "  );
  }

  template_param( "block_choice_disabled", $block_choice_disabled );

  if ($action == "Reset") {
    # we don't actually ever get here.
    $error_string = "Changes reverted.";
  } elseif ($action == "Save Changes") {

    $error_string = "Submit was pressed.";

    # replace with the real variable names:
    $first_block_choice      = @$_POST['first_block_choice'      ];
    $second_block_choice     = @$_POST['second_block_choice'     ];
    $third_block_choice      = @$_POST['third_block_choice'      ];
    $fourth_block_choice     = @$_POST['fourth_block_choice'     ];
    $block_choices_valid     = @$_POST['block_choices_valid'     ];
    $on_site_representative  = stripslashes( @$_POST['on_site_representative'  ] );
    $compression_percentage  = @$_POST['compression_percentage'  ];
    $other_group_information = stripslashes( @$_POST['other_group_information' ] );
    # $reserved_group        = @$_POST['reserved_group'          ];
    # $new_group             = @$_POST['new_group'               ];
    # $staff_group           = @$_POST['staff_group'             ];
    # $registration_complete = @$_POST['registration_complete'   ];
    # $exact_land_amount     = @$_POST['exact_land_amount'       ];
    # $new_user_id           = @$_POST['user_id'                 ];
    # $bonus_footage         = @$_POST['bonus_footage'           ];
    # $bonus_reason          = @$_POST['bonus_reason'            ];
    # $xyzzy                 = @$_POST['xyzzy'                   ];

    # CANNOT CHANGE USERID

    # CANNOT CHANGE GROUPNAME

    if ( ($first_block_choice  != block_name( $group_rec['first_block_choice' ] ) )
      or ($second_block_choice != block_name( $group_rec['second_block_choice'] ) )
      or ($third_block_choice  != block_name( $group_rec['third_block_choice' ] ) )
      or ($fourth_block_choice != block_name( $group_rec['fourth_block_choice'] ) )
      or ($block_choices_valid !=             $group_rec['block_choices_valid']   )
        ) {

      /*
      if ($admin) {
          ?>
<div>
  <br/>
  <b>DEBUG: Shown to admins only</b><br/>

    block 1: <?=$first_block_choice?>  != <?=block_name( $group_rec['first_block_choice' ] )?><br/>
    block 2: <?=$second_block_choice?> != <?=block_name( $group_rec['second_block_choice'] )?><br/>
    block 3: <?=$third_block_choice?>  != <?=block_name( $group_rec['third_block_choice' ] )?><br/>
    block 4: <?=$fourth_block_choice?> != <?=block_name( $group_rec['fourth_block_choice'] )?><br/>
    valid:   <?=$block_choices_valid?> != <?=            $group_rec['block_choices_valid']  ?><br/>

</div>
          <?
      } // endif admin
      */

      if ($block_locked_reason) {
          if ($first_block_choice) {
        # print("DEBUG: Cannot set block choices ($first_block_choice,$second_block_choice,$third_block_choice,$fourth_block_choice)<br/>\n");
          } else {
        # print("DEBUG: Cannot set block choices<br/>\n");
          }
      } elseif (set_block_choices($id, $first_block_choice, $second_block_choice,
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

    # CANNOT CHANGE FINAL BLOCK LOCATION

    if ($compress_locked_reason and $compression_percentage) {
        if ($compression_percentage != $group_rec['compression_percentage' ] ) {
      # print("DEBUG: Cannot set compression ($compression_percentage)<br/>\n");
      $compression_percentage =  $group_rec['compression_percentage' ];
        }
    }

    if ( ($on_site_representative  != $group_rec['on_site_representative' ] )
      or ($compression_percentage  != $group_rec['compression_percentage' ] )
      or ($other_group_information != $group_rec['other_group_information'] )
        ) {

      $temp_special_user = "nobody";    // should be a user id [Corwyn P42]

      if ($user_id == $temp_special_user) {
          ?>
    <div style='color:red'>
        <br/>
        <span>DEBUG: shown only to you.  Hi!  Please send this section to landweb@pennsicwar.org -- thanks.</span><br/>
            <?=$on_site_representative?>  != <?=$group_rec['on_site_representative' ]?><br/>
            <?=$compression_percentage?>  != <?=$group_rec['compression_percentage' ]?><br/>
            <?=$other_group_information?> != <?=$group_rec['other_group_information']?><br/>
    </div>
          <?
      }

      if (set_group_data($id, $on_site_representative, $compression_percentage, $other_group_information ) ) {
        # print("DEBUG: Set onsite rep, compression, and other info<br/>\n");
      } else {
        $errors++;
        template_param( "on_site_representative_error_string", error_string("failed to set onsite rep, compression, and other info") );
      }
    } else {
      # print("DEBUG: onsite rep, compression, and other info unchanged<br/>\n");
    } // endif choices changed

    # CANNOT CHANGE EXACT LAND AMOUNT

    # CANNOT CHANGE GROUP FLAGS

    # CANNOT CHANGE BONUS FOOTAGE

    # CANNOT CHANGE BONUS REASON

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

  if (! $errors) {
    print("<h2>Updates made successfully.  Click <a href='?'>here</a> to make more edits.</h2>\n");
  } else {

    template_param("top_message",  error_string($error_string) );

    template_param("group_id_variable_string"      , $id  );

    $group_name = $group_rec['group_name'];

    template_param( "groupname_variable_string", $group_name );

    template_param("block_list"  , block_list_options("", $group_rec['reserved_group'] ) );

    template_param("first_block_choice_variable_string"     , block_name( $group_rec['first_block_choice']   )  );
    template_param("second_block_choice_variable_string"    , block_name( $group_rec['second_block_choice']  )  );
    template_param("third_block_choice_variable_string"     , block_name( $group_rec['third_block_choice']   )  );
    template_param("fourth_block_choice_variable_string"    , block_name( $group_rec['fourth_block_choice']  )  );
    template_param("final_block_location_variable_string"   , block_name( $group_rec['final_block_location'] )  );

    template_param("compression_percentage_variable_string" , $group_rec['compression_percentage']       );

    template_param("other_group_information_variable_string", $group_rec['other_group_information']      );
    template_param("on_site_representative_variable_string" , $group_rec['on_site_representative']       );

    print template_output();
  } // endif errors

} // endif user_id, group_id

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>