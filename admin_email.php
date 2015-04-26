<?
# admin_email.php -- prepare the email but don't send it. Sending is handled
#             by admin_email_send.php

require_once("include/nav.php");
require_once("include/mail_merge.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Email Merge", $crumb );

nav_admin_menu();  // special Admin menu nav

require_once("include/javascript.php");    // required for template admin_mail_merge_groups

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

$maxlen = 20;

if (! $admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {

  $action  = @$_REQUEST['action'];
  $details  = @$_REQUEST['details'];  # not used anymore?
  $my_merge_id  = @$_REQUEST['merge_id'];

  # this code is repeated above and below the switch($action) section
  if ($my_merge_id) {
  $array = query_mailmerges_status($my_merge_id);

  # print("DEBUG: q_m_s() returned<pre>"); print_r($array); print("</pre>\n");

  foreach ($array as $merge_id => $result) {
    # print "DEBUG: going through loop once<br/>\n";

    $display_from    = $result['from'];
    $display_subject  = $result['subject'];
    $display_body    = $result['body'];

    $selected_count    = $result['selected_count'];
    $unselected_count  = $result['unselected_count'];
    $sent_count    = $result['sent_count'];
    $unsent_count    = $result['unsent_count'];

    $status      = $result['status'];

  } // endfor array

  $groups_array = get_merge_recipients_both($my_merge_id);
  } # endif my_merge_id

  switch ($action) {
  case "":
    break;

  case "MODE":
    $top_message = "<h2>Updating mode to 'pennsic prep'</h2>";

    change_mode("pennsic prep");

    $action = "";
    $my_merge_id = "";
    break;

  case "NEW":
    $my_merge_id = make_new_merge( );
    redirect_to("?merge_id=$my_merge_id");
    exit(0);
    break;

  case "copy":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'copy': quitting</h2>");
      die();
    } // endif my_merge_id
    $my_merge_id = copy_merge( $my_merge_id );
    redirect_to("?merge_id=$my_merge_id");
    exit(0);
    break;

  case "edit_from":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_from': quitting</h2>");
      die();
    } // endif my_merge_id

    template_load("admin_mail_merge_from.htm");

    template_param( 'merge_id', $my_merge_id );

    if (! $display_from) {
      $display_from = $user_record['email_address'];  # user == the logged on person at this point [Corwyn 2007]
    }

    template_param( 'my_email', $display_from );

    print( template_output() );
    die();
    break;

  case "edit_from_save":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_from_save': quitting</h2>");
      die();
    } // endif my_merge_id

    if (@$_POST['cancel']) {
      print "<h3 style='color:red'>Edit cancelled.</h3>\n";
      break;
    }

    $from_address = $_POST['sender_email'];
    set_merge_from($my_merge_id, $from_address);
    print "<h3>From address is now '$from_address'.</h3>\n";
    ### $display_from = $from_address;
    break;

  case "edit_body":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_body': quitting</h2>");
      die();
    } // endif my_merge_id

    template_load("admin_mail_merge_letter.htm");

    template_param( 'merge_id', $my_merge_id );

    $merge_object_functions_by_variable = array();    // IS THIS USED ANYWHERE?  [CORWYN P41]
    # $merge_data = "";
    $merge_functions = array();
    $merge_elements = array();

    /* original function cleared and re-created this list every time it was called, I don't know why [Corwyn P41] */
    $merge_variables = get_merge_variables_list();

    foreach ($merge_variables as $var => $value) {
      $merge_var = "<[" . $var . "]>";

      $display_var = $var;

      $merge_object_functions_by_variable[ $var ] = $merge_var;

      # # NOTE: merge_methods[] is NOT SET TO ANYTHING!
      # $merge_data .= $merge_var .',' . $var . ',' . $merge_methods[ $var ] . "|";
      # # NOTE: but then merge_data IS NEVER USED!  Commenting out the whole section [Corwyn PW42]

      $merge_var   = "'" . $merge_var   . "'";
      $display_var = "'" . $display_var . "'";

      array_push( $merge_functions, $merge_var   );
      array_push( $merge_elements,  $display_var );
    } // next var

    $functions = join( ",", $merge_functions );
    $elements  = join( ",", $merge_elements );

    template_param( 'mergeElements',   $elements  );
    template_param( 'mergeFunctions',  $functions );

    template_param( 'subject',         htmlentities($display_subject)  );
    template_param( 'letter',          $display_body      );

    print( template_output() );
    die();
    break;

  case "edit_body_save":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_body_save': quitting</h2>");
      die();
    } // endif my_merge_id

    if (@$_POST['cancel']) {
      print "<h3 style='color:red'>Edit cancelled.</h3>\n";
      break;
    }

    $subject = $_POST["subject"];
    $body    = $_POST["letter"];

    // should possibly undo htmlentities() here with html_entity_decode() ?  [Corwyn P41]

    $truncate_body    = (strlen($body) > $maxlen) ? (substr($body, 0, $maxlen) . "...") : ($body);
    set_merge_subject_body($my_merge_id, $subject, $body);
    print "<h3>Subject is now '$subject' and body is '$truncate_body'.</h3>\n";
    ### $display_subject = $subject;
    ### $display_body    = $body;
    break;

  case "edit_groups":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_groups': quitting</h2>");
      die();
    } // endif my_merge_id

    template_load("admin_mail_merge_groups.htm");

    template_param( 'merge_id', $my_merge_id );

    $group_assoc = array();

    $group_id_list    = "";
    $group_name_list  = "";
    $group_ch1_list   = "";
    $group_ch2_list   = "";
    $group_ch2_list   = "";
    $group_ch3_list   = "";
    $group_ch4_list   = "";
    $group_final_list = "";

    $where_clause = "user_id != 0 ";

    $order_by     = "group_name";

    $query = group_query_mult($where_clause, $order_by);

    $comma = "";  // start out list without a leading comma
    while ($result = mysql_fetch_assoc($query)) {
      $this_group_id   = $result['group_id'];
      $this_group_name = $result['group_name'];

      $group_safe_java = $this_group_name;
      $group_safe_java = str_replace("'", "\\'", $group_safe_java);

      $group_assoc[ $this_group_id ] = $this_group_name;

      $group_id_list    .= $comma . "'" . $this_group_id        . "'";
      $group_name_list  .= $comma . "'" . $group_safe_java        . "'";

      $group_ch1_list    .= $comma . "'" . block_name( $result['first_block_choice'] )  . "'";
      $group_ch2_list    .= $comma . "'" . block_name( $result['second_block_choice'] )  . "'";
      $group_ch3_list    .= $comma . "'" . block_name( $result['third_block_choice'] )  . "'";
      $group_ch4_list    .= $comma . "'" . block_name( $result['fourth_block_choice'] )  . "'";
      $group_final_list  .= $comma . "'" . block_name( $result['final_block_location'] )  . "'";

      $comma = ",";
    } // next result

    mysql_free_result($query);              # delete query object

    template_param( 'groupIDs',  $group_id_list    );
    template_param( 'groupNames',  $group_name_list  );
    template_param( 'firstChoice',  $group_ch1_list   );
    template_param( 'secondChoice',  $group_ch2_list   );
    template_param( 'thirdChoice',  $group_ch3_list   );
    template_param( 'fourthChoice',  $group_ch4_list   );
    template_param( 'finalLocation',$group_final_list );

    // --------------------------------------------------------------------------------------------------------

    $block_list = block_list();

    template_param( 'block_list', "'" . join("', '", $block_list) . "'" );

    // --------------------------------------------------------------------------------------------------------

    $actions_list = array(
      "true"  => "ADD groups",
      "false"  => "REMOVE groups",
    );

    $action_radio_buttons = "";
    foreach ($actions_list as $action_value => $action_label) {
      $action_radio_buttons .=
        "<nobr><input type='button' name='action_type' value='$action_label' onclick='block_set_magic($action_value)' /></nobr><br />\n";
    }

    template_param( 'action_radio_buttons', $action_radio_buttons);

    // --------------------------------------------------------------------------------------------------------

    $types_list = array(
      "first_choice"    => "1st",
      "second_choice"    => "2nd",
      "third_choice"    => "3rd",
      "fourth_choice"    => "4th",
      "final_location"  => "Final",
    );

    $type_radio_buttons = "";
    foreach ($types_list as $type_value => $type_label) {
      $type_radio_buttons .=
        "<nobr><input type='radio' name='block_type' value='$type_value'>$type_label</input></nobr><br />\n";
    }

    template_param( 'type_radio_buttons', $type_radio_buttons);

    // --------------------------------------------------------------------------------------------------------

    $block_radio_buttons = "";
    $prev_initial = "";
    $count = 0;
    foreach ($block_list as $b) {
      $initial = strtoupper( substr($b,0,1) );  # grab initial letters, uppercase it
      if ($prev_initial != $initial) {
        if ($prev_initial != "") {
          $block_radio_buttons .= "<br/><br/>";
          $count = 0;
        }
        $prev_initial = $initial;
        # $block_radio_buttons .= "<b>$initial</b>\n";
      } elseif (! (++$count % 100)) {
        $block_radio_buttons .= "<br/>";
        # $block_radio_buttons .= "<b>+</b>\n";
      }

      $block_radio_buttons .=
        "<nobr>"
        . "<input"
        . " type='radio'"
        . " name='block_id'"
        . " id='block_$b'"
        . " value='$b'"
        . "><span id='block_label_$b'>$b</span></input>"
        . "</nobr>\n";
    }

    template_param( 'block_radio_buttons', $block_radio_buttons);

    // --------------------------------------------------------------------------------------------------------

    $groups_array = get_merge_recipients_both($my_merge_id);

    template_param( 'chosenGroups', "'" . join("', '", array_keys($groups_array) ) . "'"  );

    // --------------------------------------------------------------------------------------------------------

    $group_checkboxes = "";

    $prev_initial = "";
    foreach ($group_assoc as $i => $g) {
      $initial = strtoupper( substr($g,0,1) );  # grab initial letters, uppercase it
      if ($prev_initial != $initial) {
        if ($prev_initial != "") {
          $group_checkboxes .= "<br/><br/>";
          $count = 0;
        }
        $prev_initial = $initial;
        # $group_checkboxes .= "<b>$initial</b>\n";
      }
      $group_checkboxes .=
        "<nobr>"
        .   "<input"
        .   " type='checkbox'"
        .   " id='group_$i'"
        .   " name='group_$i'"
        .   " value='1'"
        .   " onChange='update_text_count();'"
        .   " /><span id='group_label_$i' class='set_me'>$g</span>"
        .   ""
        . "</nobr>\n";
    }

    template_param( 'group_checkboxes', $group_checkboxes );

    // --------------------------------------------------------------------------------------------------------

    print( template_output() );
    die();
    break;

  case "edit_groups_save":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'edit_groups': quitting</h2>");
      die();
    } // endif my_merge_id

    if (@$_POST['cancel']) {
      print "<h3 style='color:red'>Edit cancelled.</h3>\n";
      break;
    }

    $groups_chosen = $_POST['groups_chosen'];

    if ($groups_chosen) {
      $groups_chosen_list = split(",", $groups_chosen );
      # print("DEBUG: groups_chosen '$groups_chosen'; split '" . join(",", $groups_chosen_list) . "'<br/>\n");
    } else {
      $groups_chosen_list = array();
    }
    set_merge_recipients($my_merge_id, $groups_chosen_list);
    $group_count = count($groups_chosen_list);

    print "<h3>Set email recipient list to $group_count groups for id $my_merge_id.</h3>\n";

    ### $groups_array = get_merge_recipients_both($my_merge_id);  // recreate list with group names included
    ### $selected_count = $group_count;
    break;

  case "delete":
    if (! $my_merge_id) {
      print("<h2>ERROR: no merge_id passed to action 'delete': quitting</h2>");
      die();
    } // endif my_merge_id

    delete_merge($my_merge_id);

    print "<h3>Deleted merge #$my_merge_id.</h3>\n";

    $my_merge_id = "";  // can't display the one we just deleted
    break;

  default:
    print("<h2>ERROR: invalid action '$action': quitting</h2>");
    die();
    break;
  } // end switch action

  # this code is repeated above and below the switch($action) section
  if ($my_merge_id) {
  $array = query_mailmerges_status($my_merge_id);

  # print("DEBUG: q_m_s() returned<pre>"); print_r($array); print("</pre>\n");

  foreach ($array as $merge_id => $result) {
    # print "DEBUG: going through loop once<br/>\n";

    $display_from    = $result['from'];
    $display_subject  = $result['subject'];
    $display_body    = $result['body'];

    $selected_count    = $result['selected_count'];
    $unselected_count  = $result['unselected_count'];
    $sent_count    = $result['sent_count'];
    $unsent_count    = $result['unsent_count'];

    $status      = $result['status'];
  } // endfor array

  $groups_array = get_merge_recipients_both($my_merge_id);
  } # endif my_merge_id

  # optionally show Help
  javascript_hidable_div_begin("Directions");
    template_load("admin_mail_merge_help.htm");
    // no template_param() values
    print( template_output() );
  javascript_hidable_div_end();

  if ($my_merge_id) {
  template_load("admin_mail_merge_detail.htm");

  template_param( "top_message",  "<h2>mail merge #$my_merge_id&nbsp;&nbsp;&nbsp;&nbsp;<a href='?'>(close)</a></h2>" );

  // data has already been loaded

  $none = "<b><font color='red'>(NONE)</font></b>";

  $groups_details = "<br/>";
  foreach ($groups_array as $group_id => $group_name) {
    $groups_details .= "<nobr><div style='float:left; margin-left:2em; margin-top:0.25em;'>$group_name</div></nobr>";
  } // next group

  $edit_mode = 0;
  if ( ($status == "SETUP") or ($status == "READY") ) {
    $edit_mode = 1;
    # $edit_href    = "<a href='?merge_id=$my_merge_id'>(EDIT)</a>";
    $edit_href    = "";  # you're already editing it [Corwyn P40]
  } else {
    # $edit_href    = "<a href='?merge_id=$my_merge_id'>(VIEW)</a>";
    $edit_href    = "";  # you're already viewing it [Corwyn P40]
  }

  if ( ($status == "READY") or ($status == "SENDING") ) {
    $send_href    = "<a href='admin_email_send.cgi?merge_id=$my_merge_id'>(SEND)</a>";
  } else {
    $send_href    = "";
  }

  if ($status == "SENT") {
    $copy_href    = "<a href='?action=copy&amp;merge_id=$my_merge_id'>(COPY THIS)</a>";
  } else {
    $copy_href    = "";
  }

  if ( ($status == "SENDING") or ($status == "SENT") ) {
    $history_href    = "<a href='admin_email_history.php?merge_id=$my_merge_id'>(SHOW DETAILS)</a>";
  } else {
    $history_href    = "";
  }

  $any_data = 0;
  if (! $display_from)  { $display_from     = $none;    } else { $any_data++; }
  if (! $display_subject)  { $display_subject = "Subject: $none";  } else { $any_data++; }
  if (! $display_body)  { $display_body     = "Message: $none";  } else { $any_data++; }
  if (! $selected_count)  { $selected_count = "0";    } else { $any_data++; }

  if (! $any_data) {
    $delete_href    = "<a href='?merge_id=$my_merge_id&amp;action=delete'>DELETE</a>";
  } else {
    $delete_href    = "";
  }

  $all_href = join(" ", array($copy_href, $edit_href, $send_href, $delete_href, $history_href) );

  $display_body = str_replace("\r\n", "\n",  $display_body);
  $display_body = str_replace("\n\n", "\n",  $display_body);
  $display_body = str_replace("\n",   "<br/>\n",  $display_body);

  $groups_text = "<b>$selected_count&nbsp;groups&nbsp;selected</b>";

  if ($edit_mode) {
    $display_from    .= "&nbsp;&nbsp;&nbsp;<a href='?merge_id=$my_merge_id&amp;action=edit_from'>EDIT</a>";
    $display_subject  .= "&nbsp;&nbsp;&nbsp;<a href='?merge_id=$my_merge_id&amp;action=edit_body'>EDIT</a>";
    $display_body    .= "&nbsp;&nbsp;&nbsp;<a href='?merge_id=$my_merge_id&amp;action=edit_body'>EDIT</a>";
    $groups_text    .= "&nbsp;&nbsp;&nbsp;<a href='?merge_id=$my_merge_id&amp;action=edit_groups'>EDIT</a>";
  }

  $groups_text .= $groups_details;

  template_param( "mail_id",  $my_merge_id    );
  template_param( "from_email",  $display_from    );
  template_param( "subject",  $display_subject  );
  template_param( "body",    $display_body    );
  template_param( "groups",  $groups_text    );
  template_param( "status",  $status      );
  template_param( "href",    $all_href    );
  # template_param( "reset_button",  $reset_button    );
  # template_param( "submit_button",$submit_button    );

  print template_output();
  } else {
  # no my_merge_id: show menu instead
  $top_message = "Email Merge Tool";

  template_load("admin_mail_merge_menu.htm");

  template_param( "top_message", $top_message);

  print template_output();    // opens table

  $years = query_mailmerge_years();

  $display_year = @$_GET['year'];
  foreach ($years as $year => $num) {
    if (! $display_year) {
      $display_year = $year;
    }
    ?>
  <tr>
    <td colspan="5" style="background-color:lightgreen">
      YEAR <?=$year?> (<?=$num?> mail merges)
      <?=( ($display_year == $year) ? (":") : ("<a href='?year=$year'>SHOW</a>") )?>
    </td>
  </tr>
    <?
    if ($display_year == $year) {

      $array = query_mailmerges_status("", $display_year);

      $none = "<b><font color='red'>(NONE)</font></b>";

      foreach ($array as $merge_id => $result) {
        $display_from    = $result['from'];
        $display_subject  = $result['subject'];
        $display_body    = $result['body'];

        $selected_count    = $result['selected_count'];
        $unselected_count  = $result['unselected_count'];
        $sent_count    = $result['sent_count'];
        $unsent_count    = $result['unsent_count'];

        $status      = $result['status'];

        if (! $display_from)  { $display_from     = $none;    }
        if (! $display_subject)  { $display_subject = "Subject: $none";  }
        if (! $display_body)  { $display_body     = "Message: $none";  }

        if (! $selected_count) { $selected_count = "0"; }
        $groups_text = "<b>$selected_count&nbsp;groups</b>";

        $recp_href = "";  // SHOULD BE A LINK TO SHOWING ALL THE GROUPS BY NAME HERE [CORWYN 2012]

        $display_body    = substr($display_body, 0, $maxlen) . "...";

        if ( ($status == "SETUP") or ($status == "READY") ) {
          $edit_href    = "<a href='?merge_id=$merge_id'>(EDIT)</a>";
        } else {
          $edit_href    = "<a href='?merge_id=$merge_id'>(VIEW)</a>";
        }
        $send_href    = "";
        $copy_href    = "";

        $all_href = join(" ", array($copy_href, $edit_href, $send_href) );

        ?>
  <tr>
    <td><?=$merge_id?></td>
    <td><?=$display_from?></td>
    <td>
      <b><?=$display_subject?></b><br />
      <?=$display_body?>
    </td>
    <td><?=$groups_text?> <?=$recp_href?></td>
    <td><?=$status?> <?=$all_href?></td>
  </tr>
        <?
      } // next array


    } // endif display_year
  } // next year

  ?>
  <tr bgcolor="yellow" style="font-weight:bold;">
    <td colspan="5" align="center">
      START A NEW MAIL MERGE: <a href="?action=NEW">GO</a>
    </td>
  </tr>
  <?

  if ($current_mode == "locked") {
    ?>
  <tr bgcolor="pink" style="font-weight:bold;">
    <td colspan="5" align="center">
      DECLARE THAT YOU HAVE SENT EMAILS TO EVERYONE,<br />
      UPDATING THE SYSTEM TO 'PENNSIC PREP' MODE: <a href="?action=MODE">GO</a>
    </td>
  </tr>
    <?
  } elseif ($current_mode == "pennsic prep") {
    ?>
  <tr bgcolor="#99FF99" style="font-weight:bold;">  <!-- light green -->
    <td colspan="5" align="center">
      MODE IS ALREADY 'PENNSIC PREP'
    </td>
  </tr>
    <?
  // else
    // print nothing
  } // endif current_mode
  ?>
</table>
  <?
  } # endif action, merge_id

} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>