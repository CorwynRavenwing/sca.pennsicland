<?
require_once("include/user.php");

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! $w_admin) {
  print "<h2>Your access level does not allow this action.</h2>\n";
} else {
  # no template
?>
<html>
<head><title>COOPER MOVE</title></head>
<body>

<h1>MOVE USERS IN COOPER DATABASE</h1>

<form action="<? echo $GS_URL; ?>" method="post" name="coopersland" target="_self">
	<input type="hidden" name="userid"   value="<? echo $GS_userid; ?>" />
	<input type="hidden" name="password" value="<? echo $GS_password; ?>" />
	<b>PENN NUMBER:</b> <input type="text" name="arg1" value="" />
	<b>NEW Group Name:</b> <input type="text" name="arg2" value="" />
	<input type="hidden" name="function" value="update_user_land_group" />
	<br />
	<input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
<?
} // endif
?>