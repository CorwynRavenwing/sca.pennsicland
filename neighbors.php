<?
require_once("include/nav.php");
require_once("include/land_email.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Email Neighbors", $crumb );  # was nav_scriptname()

nav_menu();

nav_right_begin();

  $group_record = group_record($group_id);

  $block_id = $group_record['final_block_location'];

  #set template page
  template_load( "email_neighbors.htm" );

  $submit = @$_POST['submit'];

  $errors_found = 0;
  $show_form    = 1;

  if ($submit) {

    // validate email sending part

    $email_subject = @$_POST['subject'];

    if( ! $email_subject ) {
      template_param( 'subject_error', "No value entered" );
      $errors_found = 1;
    }

    $email_message = @$_POST['message'];
    if( ! $email_message )
    {
      template_param( 'message_error', "No value entered" );
      $errors_found = 1;
    }

    // NOT SURE WHY THE FOLLOWING 4 LINES ARE HERE [CORWYN 2012]
    $block_name = $_POST['block_name'];
    template_param( 'block_name', $block_name );

    $email_list = $_POST['email_list'];
    template_param( 'email_list', $email_list );

    if (! $errors_found) {
      #send message

      $my_email_address = $user_record['email_address'];
      $cc = "";
      $bcc = "landweb@pennsicwar.org";

      $succ = send_email(
        $my_email_address,
        $email_list,
        $cc,
        $bcc,
        "[Pennsic Block $block_name] $email_subject",
        $email_message
      );

      if ($succ) {
        # set new template page
        template_load( "email_neighbors_sent.htm" );

        print( template_output() );

        $show_form = 0;
      } else {
        print("ERROR: email attempted to neighbors, but an error occurred.  Please try again later.");

        $show_form = 1;
      }

    } else {
      # there were errors: show form again
      $show_form = 1;
    } // endif errors_found

  } // endif submit

  $email_subject = @$_POST['subject'];
  $email_message = @$_POST['message'];
  template_param( 'subject', $email_subject );
  template_param( 'message', $email_message );

  if ($show_form) {

    if ( ! $group_id ) {
      $message = "This page is only relevant to users who have registered a group";
      if ($registration_open) {
        $message .= ", which you can still do until registration closes on "
          . registration_close_str();
      } else {
        $message .= ", but the registration period closed on "
          . registration_close_str();
      }
      $message .= ".";
      print coming_soon($message);
    } elseif ( ! $block_id ) {
      $message = "This page will become available once Land One assigns your group to a block";
      if ($registration_open) {
        $message .= ", which will happen sometime after registration closes on "
          . registration_close_str();
      }
      $message .= ".  Thank you for your patience.";
      print coming_soon($message);
    } else {
      $block_name = block_name( $block_id );

      $member_list = "";
      $member_list .= "<tr>\n  <td colspan='5' align='center'>Send email to the following land agents for groups on block <b>$block_name</b>:</td>\n</tr>\n";

      if ($current_mode == "pennsic prep") {
        $member_list .= "<tr>\n  <td colspan='5' align='center' style='color:green; font-size:1.2em;'>\n";
        $member_list .= "    Block assigment emails have gone out.\n";
        $member_list .= "  </td>\n</tr>\n";
      } else {
        $member_list .= "<tr>\n  <td colspan='5' align='center' style='color:red; font-size:1.2em;'>\n";
        $member_list .= "    Please note: the block assignments have not been finalized.\n";
        $member_list .= "    Either your group, or the other groups in this list, might end up in a different block.\n";
        $member_list .= "    Blocks are not final until you receive your\n";
        $member_list .= "    <span style='font-weight:bold;'>land assignment email</span>.<br/>\n";
        $member_list .= "    If you still want to contact this group of tentative neighbors,\n";
        $member_list .= "    this tool will allow you to do so.\n";
        $member_list .= "  </td>\n</tr>\n";
      }

      $member_list .= "<tr bgcolor='silver'>\n";
      $member_list .= "  <td>#</td>\n";
      $member_list .= "  <td>Group Name</td>\n";
      $member_list .= "  <td>Legal Name<br />(SCA Name)</td>\n";
      $member_list .= "  <td>Email Address</td>\n";
      $member_list .= "</tr>\n";

      $query = query_groups_by_block_id($block_id);

      $recipient_emails = array();
      # $recipient_user_ids = array();
      $count = 0;
      while( $result = mysql_fetch_assoc($query) ) {
        $count++;

        # print("DEBUG: query_groups_by_block_id($block_id) returned:<pre>"); print_r($result); print("</pre>\n");

        $d_group_name  = $result['group_name'];
        $d_user_id  = $result['user_id'];
        $d_legal_name  = $result['legal_name'];
        $d_sca_name  = $result['alias'];
        $d_email_addr  = $result['email_address'];

        if ( $d_user_id ) {
          array_push( $recipient_emails, $d_email_addr );
          # array_push( $recipient_user_ids, $d_user_id );
        } else {
          # no land agent for this group
          $d_legal_name = "No land agent";
          $d_sca_name  = "";
          $d_email_addr = "&nbsp";
        } // endif user_id

        $member_list .= "<tr>\n";
        $member_list .= "  <td>$count</td>\n";
        $member_list .= "  <td>$d_group_name</td>\n";
        $member_list .= "  <td>$d_legal_name";
        if ($d_sca_name) {
          $member_list .= "<br />($d_sca_name)";
        }
        $member_list .= "</td>\n";
        $member_list .= "  <td>$d_email_addr</td>\n";
        $member_list .= "</tr>\n";
      } # next group

      if (! $count) {
        $member_list .= "<tr><td colspan='5' align='center'>You have nobody on your block</td></tr>\n";
      }

      template_param( 'email_list', join(",", $recipient_emails) );
      template_param( 'block_name', $block_name );
      template_param( 'email_table', $member_list );

      print template_output();
    } // endif final_block_location

  } // endif show_form

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>