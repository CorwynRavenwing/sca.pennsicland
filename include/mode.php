<?php
require_once("include/connect.php");

function mode_setup()
{
  global $ADMIN_MESSAGE;
  global $allow_block_changes;
  global $allow_bigredbutton;
  global $allow_email;
  global $allow_groupmoves;
  global $allow_movedata;
  global $allow_nextyear;
  global $allow_reopen;
  global $closed_message;
  global $current_date;
  global $current_mode;
  global $current_submode;
  global $current_year;
  global $pennsic_close_date;
  global $pennsic_number;
  global $pennsic_open_date;
  global $pennsic_roman;
  global $registration_close_date;
  global $registration_open;
  global $registration_open_date;
  global $show_location;
  global $user_message;
  global $mode_direction;

  $time_now = time();
  $current_year = date("Y",     $time_now );
  $current_date = date("Y-m-d", $time_now );
/*
  ${possible_modes} = [(
  "normal",
    # when we are ready to open
      # BEFORE REGISTRATION_OPEN_DATE: form is off.  Maybe show a 'will open at $date' message
      # BETWEEN REGISTRATION_OPEN_DATE and REG_CLOSE_DATE: registration_open = 1.
      # AFTER REG_CLOSE_DATE: registration closed, but allow_block_changes = 1.
  "locked",
    # happens when you hit the big red button.  allow_block_changes = 0.
  "pennsic prep",
    # happens when you send out the block letters.
      # BEFORE PENNSIC_OPEN_DATE: sets show_location = 1.
      # BETWEEN PENNSIC_OPEN_DATE and PENNSIC_CLOSE_DATE: turn show_location off, show "pennsic is on" message
      # AFTER PENNSIC_CLOSE_DATE: turn everything off, show "closed until next year" message
  "data moved"
    # happens when you press the "close year" button
  "end of year",
    # happens when you change the pennsic number and other year data.
      # once new dates are ready, change to "normal" again
  )];
*/

  $sql = "SELECT registration_open_date, registration_close_date,
        pennsic_open_date, pennsic_close_date,
        current_mode, current_year, pennsic_number
      FROM data_var
      WHERE data_var_id = 1 ";

  if (headers_sent()) {
    print "<!-- data_var SQL:\n$sql\n-->\n";
  }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  $result = mysql_fetch_assoc($query)
    or die("Found no such record in 'data_var' at file " . __FILE__ . " line " . __LINE__);

//  load record where data_var_id=1
  $registration_open_date  = $result['registration_open_date'];  # "2009-11-01";
  $registration_close_date = $result['registration_close_date'];  # "2009-11-30";
  $pennsic_open_date       = $result['pennsic_open_date'];    # "2009-12-05";
  $pennsic_close_date      = $result['pennsic_close_date'];    # "2009-12-10";
  $current_mode            = $result['current_mode'];        # "normal";
  $pennsic_number          = $result['pennsic_number'];      # 38;
#  $current_year            = $result['current_year'];        # 2009;

// create the following dependent variables
  $pennsic_roman = convert_to_roman($pennsic_number);
  $registration_open = 0;
  $allow_block_changes = 0;
  $show_location = 0;
  $allow_bigredbutton = 0;
  $allow_email = 0;
  $allow_groupmoves = 0;
  $allow_movedata = 0;
  $allow_nextyear = 0;
  $allow_reopen = 0;
  $current_submode = "";
  $closed_message = "";
  $user_message = "";
  $ADMIN_MESSAGE = "";

  // Registration for land allocation opened on January 1, 2011.



  if ($current_mode == "normal") {
    if ($current_date < $registration_open_date ) {
      $current_submode = "not open yet";
      $closed_message = "Registration for land allocation will open on $registration_open_date";
      $user_message = "Registration for land allocation will open on $registration_open_date";
      $ADMIN_MESSAGE = "Registration waiting to open";
    } elseif ($current_date <= $registration_close_date) {
      $current_submode = "reg open";
      $registration_open = 1;            # allows new users and registering groups
      $allow_block_changes = 1;        # allow groups to change their desired blocks and compressions
      $allow_email = 1;          # can email before reg closes [NEW Corwyn P40]
      $user_message = "Registration for land allocation opened on $registration_open_date and will close on $registration_close_date";
      $ADMIN_MESSAGE = "Registration open; waiting to close";
    } else {
      $current_submode = "reg closed";
      $allow_block_changes = 1;        # allow groups to change their desired blocks and compressions
      $allow_bigredbutton = 1;
      $allow_email = 1;          # can email before pushing big red button
      $user_message = "Registration for land allocation closed on " . $registration_close_date . "; changes to block locations are still allowed.  Land assignments will be sent out in early July";
      $ADMIN_MESSAGE = "Registration closed; block changes allowed until Big Red Button is pressed";
    }
  } elseif ($current_mode == "locked") {
    $allow_bigredbutton = 1;            # can push big red button twice
    $allow_email = 1;              # can also email after pushing big red button
    $allow_groupmoves = 1;            # can only move groups after pushing big red button
    $user_message = "No changes can be made to your block choices.  Land assignment emails will be sent out in early July.  Paper Land packets are no longer provided";
    $ADMIN_MESSAGE = "Move groups around until there are no overflowing blocks, then send block emails.";
  } elseif ($current_mode == "pennsic prep") {
    if ($current_date < $pennsic_open_date ) {
      $current_submode = "negotiate";
      $allow_email = 1;            # can also email during negotiation
      $allow_groupmoves = 1;          # can also move groups during negotiation
      $show_location = 1;              # turns on email-neighbors section
      $user_message = "You should have received a Land assignment email.  Paper Land packets are no longer provided.  Please contact your neighbors to discuss the map for your block";
      $ADMIN_MESSAGE = "Negotiation period.  You are allowed to move groups; agents are allowed to contact their neighbors.  Ends when pennsic opens on " . $pennsic_open_date;
    } elseif ($current_date <= $pennsic_close_date) {
      $current_submode = "pennsic";
      $allow_email = 1;            # can also email during pennsic
      $allow_groupmoves = 1;          # can also move groups during pennsic.  USE WITH CAUTION
      $closed_message = "Please don't send emails, everyone is already on their way to Pennsic.";
      $user_message = "Land packets can be picked up at the barn.  Land office hours are:<br />&nbsp;&nbsp;&nbsp;&nbsp;Friday: noon to midnight<br />&nbsp;&nbsp;&nbsp;&nbsp;Saturday: 6AM to 9AM";
      $ADMIN_MESSAGE = "Pennsic has started: closes on $pennsic_close_date";
    } else {
      $current_submode = "done";
      $allow_email = 1;            # can also email after pennsic
      $allow_groupmoves = 1;          # can also move groups after pennsic.  USE WITH CAUTION
      $allow_movedata = 1;
      $closed_message = "Registration is closed.  See you next year!";
      $user_message = "Pennsic is over.  See you next year!";
      $ADMIN_MESSAGE = "Pennsic has closed.  After doing any block location corrections, press the Close Year button";
    }
  } elseif ($current_mode == "data moved") {
    $allow_nextyear = 1;
    $closed_message = "Registration is closed.  See you next year!";
    $user_message = "Pennsic is over.  See you next year!";
    $ADMIN_MESSAGE = "Set up dates for next year's Pennsic";
  } elseif ($current_mode == "end of year") {
    $allow_nextyear = 1;
    $allow_reopen = 1;
    $closed_message = "Registration is closed.  See you next year!";
    $user_message = "Pennsic is over.  See you next year!";
    $ADMIN_MESSAGE = "Verify dates and pennsic numbers for next year, then press Re-open button";
  } else {
    print "<h2>ERROR: invalid current mode '$current_mode' line " . __LINE__ . "</h2>\n";
  } # endif

  switch ($current_mode) {

      case "normal":

    switch ($current_submode) {

        case "not open yet":
      $mode_direction["end of year"]      = "prev";
      $mode_direction["normal;not open yet"]    = "current";
      $mode_direction["normal;reg open"]    = "next";
      $mode_direction["normal;reg closed"]    = "future";
      $mode_direction["locked"]      = "future";
      break;

        case "reg open":
      $mode_direction["end of year"]      = "future";
      $mode_direction["normal;not open yet"]    = "prev";
      $mode_direction["normal;reg open"]    = "current";
      $mode_direction["normal;reg closed"]    = "next";
      $mode_direction["locked"]      = "future";
      break;

        case "reg closed":
      $mode_direction["end of year"]      = "future";
      $mode_direction["normal;not open yet"]    = "past";
      $mode_direction["normal;reg open"]    = "prev";
      $mode_direction["normal;reg closed"]    = "current";
      $mode_direction["locked"]      = "next";
      break;

        default:
      print "<h2>ERROR: invalid current submode '$current_submode' line " . __LINE__ . "</h2>\n";
      break;

    } // end switch current_submode

      $mode_direction["pennsic prep;negotiate"]  = "future";
      $mode_direction["pennsic prep;pennsic"]    = "future";
      $mode_direction["pennsic prep;done"]    = "future";
      $mode_direction["data moved"]      = "future";
    break;

      case "locked":
      $mode_direction["normal;not open yet"]    = "past";
      $mode_direction["normal;reg open"]    = "past";
      $mode_direction["normal;reg closed"]    = "prev";
      $mode_direction["locked"]      = "current";
      $mode_direction["pennsic prep;negotiate"]  = "next";
      $mode_direction["pennsic prep;pennsic"]    = "future";
      $mode_direction["pennsic prep;done"]    = "future";
      $mode_direction["data moved"]      = "future";
      $mode_direction["end of year"]      = "future";
    break;

      case "pennsic prep":
      $mode_direction["normal;not open yet"]    = "past";
      $mode_direction["normal;reg open"]    = "past";
      $mode_direction["normal;reg closed"]    = "past";

    switch ($current_submode) {

        case "negotiate":

      $mode_direction["locked"]      = "prev";
      $mode_direction["pennsic prep;negotiate"]  = "current";
      $mode_direction["pennsic prep;pennsic"]    = "next";
      $mode_direction["pennsic prep;done"]    = "future";
      $mode_direction["data moved"]      = "future";
      break;

        case "pennsic":

      $mode_direction["locked"]      = "past";
      $mode_direction["pennsic prep;negotiate"]  = "prev";
      $mode_direction["pennsic prep;pennsic"]    = "current";
      $mode_direction["pennsic prep;done"]    = "next";
      $mode_direction["data moved"]      = "future";
      break;

        case "done":

      $mode_direction["locked"]      = "past";
      $mode_direction["pennsic prep;negotiate"]  = "past";
      $mode_direction["pennsic prep;pennsic"]    = "prev";
      $mode_direction["pennsic prep;done"]    = "current";
      $mode_direction["data moved"]      = "next";
      break;

        default:
      print "<h2>ERROR: invalid current submode '$current_submode' line " . __LINE__ . "</h2>\n";
      break;

    } // end switch current_submode

      $mode_direction["end of year"]      = "future";
    break;

      case "data moved":
      $mode_direction["normal;not open yet"]    = "past";
      $mode_direction["normal;reg open"]    = "past";
      $mode_direction["normal;reg closed"]    = "past";
      $mode_direction["locked"]      = "past";
      $mode_direction["pennsic prep;negotiate"]  = "past";
      $mode_direction["pennsic prep;pennsic"]    = "past";
      $mode_direction["pennsic prep;done"]    = "prev";
      $mode_direction["data moved"]      = "current";
      $mode_direction["end of year"]      = "next";
    break;

      case "end of year":
      $mode_direction["normal;not open yet"]    = "next";
      $mode_direction["normal;reg open"]    = "past";
      $mode_direction["normal;reg closed"]    = "past";
      $mode_direction["locked"]      = "past";
      $mode_direction["pennsic prep;negotiate"]  = "past";
      $mode_direction["pennsic prep;pennsic"]    = "past";
      $mode_direction["pennsic prep;done"]    = "past";
      $mode_direction["data moved"]      = "prev";
      $mode_direction["end of year"]      = "current";
    break;

      default:
    print "<h2>ERROR: invalid current mode '$current_mode' line " . __LINE__ . "</h2>\n";
    break;

  } // end switch current_mode

  return;
}

function registration_open_date()  { global $registration_open_date;  return $registration_open_date;  }
function registration_close_date()  { global $registration_close_date;  return $registration_close_date;}
function pennsic_open_date()    { global $pennsic_open_date;    return $pennsic_open_date;  }
function pennsic_close_date()    { global $pennsic_close_date;    return $pennsic_close_date;  }
function current_mode()      { global $current_mode;      return $current_mode;    }
function current_year()      { global $current_year;      return $current_year;    }
function pennsic_number()    { global $pennsic_number;    return $pennsic_number;    }

function current_date()      { global $current_date;      return $current_date;    }
function registration_open_str()  { global $registration_open_date;  return $registration_open_date;  }
function registration_close_str()  { global $registration_close_date;  return $registration_close_date;}
function current_submode()    { global $current_submode;    return $current_submode;  }
function registration_open()    { global $registration_open;    return $registration_open;  }
function allow_block_changes()    { global $allow_block_changes;    return $allow_block_changes;  }
function show_location()    { global $show_location;    return $show_location;    }
function allow_bigredbutton()    { global $allow_bigredbutton;    return $allow_bigredbutton;  }
function allow_email()      { global $allow_email;      return $allow_email;    }
function allow_groupmoves()    { global $allow_groupmoves;    return $allow_groupmoves;  }
function allow_movedata()    { global $allow_movedata;    return $allow_movedata;    }
function allow_nextyear()    { global $allow_nextyear;    return $allow_nextyear;    }
function allow_reopen()      { global $allow_reopen;      return $allow_reopen;    }
function closed_message()    { global $closed_message;    return $closed_message;    }
function user_message()      { global $user_message;      return $user_message;    }
function admin_message()    { global $ADMIN_MESSAGE;    return $ADMIN_MESSAGE;    }
function pennsic_roman()    { global $pennsic_roman;    return $pennsic_roman;    }

function change_mode($new_mode) {
  global $current_mode;

  if ($new_mode == $current_mode) {
    print "mode is already $new_mode<br/>\n";
    return;
  }

  print "change_mode() from $current_mode to $new_mode<br />\n";

  $new_mode = mysql_real_escape_string( $new_mode );

  $sql = "UPDATE data_var
      SET current_mode = '$new_mode'
      WHERE data_var_id = 1 ";

  if (headers_sent()) {
    print "<!-- change_mode SQL:\n$sql\n-->\n";
  }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ( ! mysql_affected_rows() ) {
    print "ERROR: " . mysql_error() . "<br />\n";
  } else {
    print "success<br />\n";
  } // endif affected_rows

  $current_mode = $new_mode;

  return;
} // end function change_mode

function change_mode_data( $new_year, $new_pennsic_number, $new_registration_open, $new_registration_close, $new_pennsic_open, $new_pennsic_close ) {

  print "change_mode_data() to:<br/>YEAR $new_year, P# $new_pennsic_number,<br/>REG: $new_registration_open - $new_registration_close,<br/>PENN $new_pennsic_open, $new_pennsic_close<br/>\n";

  $new_year    = mysql_real_escape_string( $new_year      );
  $new_pennsic_number  = mysql_real_escape_string( $new_pennsic_number    );
  $new_registration_open  = mysql_real_escape_string( $new_registration_open  );
  $new_registration_close  = mysql_real_escape_string( $new_registration_close  );
  $new_pennsic_open  = mysql_real_escape_string( $new_pennsic_open    );
  $new_pennsic_close  = mysql_real_escape_string( $new_pennsic_close    );

  $sql = "UPDATE data_var
      SET current_year    = '$new_year'
        , pennsic_number    = '$new_pennsic_number'
        , registration_open_date  = '$new_registration_open'
        , registration_close_date  = '$new_registration_close'
        , pennsic_open_date    = '$new_pennsic_open'
        , pennsic_close_date    = '$new_pennsic_close'
    WHERE data_var_id = 1 ";

  if (headers_sent()) {
    print "<!-- change_mode_data SQL:\n$sql\n-->\n";
  }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

  if ( ! mysql_affected_rows() ) {
    if ( mysql_error() ) {
      print "<b>ERROR: " . mysql_error() . "</b><br />\n";
    } else {
      print "<b>no changes made</b><br />\n";
    } // endif mysql_error
  } else {
    print "<b>success</b><br />\n";
  } // endif affected_rows

  # $current_mode = $new_mode;

  return;
} // end function change_mode_data

function convert_to_roman($number) {
  $roman = "";

  while  ($number >= 1000) { $number -= 1000; $roman .= "M";  }
  if  ($number >=  900) { $number -=  900; $roman .= "CM";  }
  while  ($number >=  500) { $number -=  500; $roman .= "D";  }
  if  ($number >=  400) { $number -=  400; $roman .= "CD";  }
  while  ($number >=  100) { $number -=  100; $roman .= "C";  }
  if  ($number >=   90) { $number -=   90; $roman .= "XC";  }
  while  ($number >=   50) { $number -=   50; $roman .= "L";  }
  if  ($number >=   40) { $number -=   40; $roman .= "XL";  }
  while  ($number >=   10) { $number -=   10; $roman .= "X";  }
  if  ($number >=    9) { $number -=    9; $roman .= "IX";  }
  while  ($number >=    5) { $number -=    5; $roman .= "V";  }
  if  ($number >=    4) { $number -=    4; $roman .= "IV";  }
  while  ($number >=    1) { $number -=    1; $roman .= "I";  }

  return $roman;
} // end function convert_to_roman

function is_date( $str ) {
  $stamp = strtotime( $str );

  if (! is_numeric($stamp)) {
    return FALSE;
  }
  $month = date( 'm', $stamp );
  $day   = date( 'd', $stamp );
  $year  = date( 'Y', $stamp );

  if ( checkdate($month, $day, $year) ) {
    return TRUE;
  }

  return FALSE;
}

mode_setup();  // call it now
?>