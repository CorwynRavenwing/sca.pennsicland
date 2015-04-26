<?
$fake_email = 0;

# new function [Corwyn 2008] which we should refactor all the other emailing programs to use.
function send_email($from, $to, $cc, $bcc, $subject, $body) {
  global $landone_email;
  global $fake_email;

  if (! $from)  { $from = $landone_email; }
  if (! $to)    { $to   = $landone_email; }

  $headers  = 'From: '   . $from . "\r\n";
  $headers .= 'Reply-To: ' . $from . "\r\n";
    if ($cc) {
  $headers .= 'Cc: '   . $cc . "\r\n";
    }
    if ($bcc) {
  $headers .= 'Bcc: '   . $bcc . "\r\n";
    }
  $headers .= 'X-Mailer: PHP/' . phpversion();

    if ($fake_email) {
  print("DEBUG: CALLING mail( $to, $subject, <br/>\n" . substr($body,0,50) . ", " . str_replace("\r", "<br/>", $headers) . " );<br/>\n");
  $succ = 1;
    } else {
  $succ = mail( $to, $subject, $body, $headers );
    }

  if ($succ) {
    # print "mail succeeded<br/>\n";
  } else {
    print "mail failed<br/>\n";
  }

  return $succ;
}

function send_admin_letter($from, $subject, $body) {
  global $landone_email;

  $to = $landone_email;
  $cc = "";
  $bcc = "";
  return send_email($from, $to, $cc, $bcc, $subject, $body);
}

function send_cooper_letter($dummy_from, $subject, $body) {
  global $landone_email;
  global $cooper_email;
  global $webmaster_email;

  $from = $landone_email;
  $to = $cooper_email;
  $cc = $webmaster_email;
  $bcc = "";
  return send_email($from, $to, $cc, $bcc, $subject, $body);
}

# new version of function [Corwyn 2007]: 'from' and 'to' are email addresses not user_ids
function send_letter_NEW( $from, $to, $subject, $body ) {
  global $landone_email;
  global $webmaster_email;

  $cc = "";      # cc should be "$landone_email, $webmaster_email"
  $bcc = $webmaster_email;  # bcc should be ""

  return send_email($from, $to, $cc, $bcc, $subject, $body);
} // end function send_letter_NEW

/* send_letter() uses user ids  */
function send_letter( $sender_user_id, $recipient_user_id, $subject, $body ) {
  $sender_record    = user_record( $sender_user_id    );
  $recipient_record = user_record( $recipient_user_id );

  $sender_email_address    = $sender_record[   'email_address'];
  $sender_group_id         = $sender_record[   'group_id'     ];

  $recipient_email_address = $recipient_record['email_address'];
  $recipient_group_id      = $sender_record[   'group_id'     ];

  # $email_id;

  $sender_group_id    = ($sender_group_id    ? $sender_group_id    : 0);
  $recipient_group_id = ($recipient_group_id ? $recipient_group_id : 0);

  print "<!-- send_letter called:\n";
  print "From: $sender_user_id, $sender_email_address, $sender_group_id\n";
  print "To: $recipient_user_id, $recipient_email_address, $recipient_group_id\n";
  print "-->\n";

  if ($recipient_email_address) {

    $sql_recipient_user_id  = mysql_real_escape_string($recipient_user_id );
    $sql_recipient_group_id = mysql_real_escape_string($recipient_group_id);
    $sql_sender_user_id     = mysql_real_escape_string($sender_user_id    );
    $sql_sender_group_id    = mysql_real_escape_string($sender_group_id   );
    $sql_subject            = mysql_real_escape_string($subject           );
    $sql_body               = mysql_real_escape_string($body              );

    $sql = "INSERT INTO user_mail
        (recipient_user_id,
         recipient_group_id, sender_user_id,
         sender_group_id, subject, body)
        VALUES( '$sql_recipient_user_id','$sql_recipient_group_id','$sql_sender_user_id','$sql_sender_group_id','$sql_subject','$sql_body' )";

    if (headers_sent()) { print "<!-- send_letter SQL 1:\n$sql\n-->\n"; }

    $query = mysql_query($sql)
      or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

    $sql = "SELECT LAST_INSERT_ID() AS id";

    if (headers_sent()) { print "<!-- send_letter SQL 2:\n$sql\n-->\n"; }

    $query = mysql_query($sql)
      or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

    if ($result = mysql_fetch_assoc($query) ) {
      $email_id = $result['id'];
    } else {
      $email_id = 0;
    }
  }

  if (!$recipient_email_address)
  {
    # ... um, but, we only did the insert if there *was* a recipient address, so this code will always fail!

    # print "Error Sending email To: $recipient_email_address<br>\n";
    update_email_status($email_id, "no email_address");
    $retVal = 0;
  } else {
    $retVal = send_letter_NEW( $sender_email_address, $recipient_email_address, $subject, $body );

    if ($retVal) {
      # print "Sending email To:  $sender_email_address <br>\n";
      update_email_status($email_id, "sent");
    } else {
      update_email_status($email_id, "sending mail failed");
    }
  }

  return $retVal;
} // end function send_letter

# new function: should probably use this elsewhere from other functions
function update_email_status($email_id, $status) {
  $email_id = mysql_real_escape_string($email_id);
  $status   = mysql_real_escape_string($status);

  $sql = "UPDATE user_mail SET mail_error = '$status' WHERE user_mail_id = '$email_id' ";

  if (headers_sent()) { print "<!-- update_mail_status SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  return mysql_affected_rows();
} // end function update_email_status

function new_generate_registration_complete($user_id, $group_id=0, $testing = 0) {
  global $webmaster_name, $webmaster_email, $landone_email, $landone_userid;
  global $pennsic_number, $pennsic_roman, $page_name;

  $saved_template_data = template_save();

  if ($user_id) {
    $user_record   = user_record($user_id);
    $email_address = $user_record['email_address'];
    if (!$group_id) {
      $group_id      = user_group($user_id);
    }
  }

  if ($group_id) {
    $group_record  = group_record($group_id);
    $group_name    = $group_record['group_name'];
    if (!$user_id) {
      $user_id     = $group_record['user_id'];
      # repeat preceeding lines, since user_id just got set now
      $user_record   = user_record($user_id);
      $email_address = $user_record['email_address'];
    }
  }

  template_load("registration_letter.txt", 0);    // 0: no template begin/end comments [Corwyn P42]

  # set some variables here
  template_param("pennsic_number",   $pennsic_number   );
  template_param("landone_email",    $landone_email    );
  template_param("webmaster_email",  $webmaster_email  );
  $message = template_output();

  template_load("new_registration_complete.htm");

  $subject       = "GROUP REGISTERED: $group_name";
  $to_user_id    = $user_id;
  $to_email_addr = $email_address;

  if ($testing) {
      $subject .= " (TEST)";
      $to_user_id = 3294;                           // Corwyn Ravenwing's ID
      $to_email_addr = "oakenraven@juno.com";    // Corwyn Ravenwing's email

      # print("DEBUG: message is <pre>$message</pre><br/>\n");
  }

  send_letter( $landone_userid, $to_user_id, $subject, $message );

  # throw away $message here
  print "Sending registration confirmation email.  You should receive it shortly.<br />\n";

  $message = "";
  $message .= "UID        : " . $user_id    . "\n";
  $message .= "Group ID   : " . $group_id   . "\n";
  $message .= "Group Name : " . $group_name . "\n";

  $subject = str_replace(": ", ": [admin] ", $subject);

  send_cooper_letter( $to_email_addr, $subject, $message );

  send_admin_letter(  $email_address, $subject, $message );   // first parameter is FROM address

#  if ($testing) {
#      print("<span style='font-size:1.25em; color:green;'>Sending TEST letters: <pre>$message</pre></span><br/>\n");
#  }

  if (! $testing) {
      cooper_create_group( $group_name );

      complete_registration( $group_id );
  }

  print template_output();

  template_restore($saved_template_data);
}

?>