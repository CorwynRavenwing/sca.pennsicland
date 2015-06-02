
      if (! $w_admin) {
	    print "<h2>Your access level does not allow this action.</h2>\n";
      } else {

      } // endif w_admin


  if ($xyzzy) {
    if (! $w_admin) {
	print "<h2>Your access level does not allow this action.</h2>\n";
	$xyzzy = "";
    } // endif w_admin
  } // endif xyzzy


  if ($xyzzy or $xyzzzy) {
    if (! $w_admin) {
	print "<h2>Your access level does not allow this action.</h2>\n";
	$xyzzy = "";
	$xyzzzy = "";
    } // endif w_admin
  } // endif xyzzy or xyzzzy


} elseif (! $w_admin) {
  print "<h2>Your access level does not allow this action.</h2>\n";


==================

Masquerading?

Test email?

Reset password?

