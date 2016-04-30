<?
require_once("include/user.php");

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! $w_admin) {
  print "<h2>Your access level does not allow this action.</h2>\n";
} else {
  # no template
  setup_GS_url();
?>
<html>
<head><title>COOPER SHOW USERS</title></head>
<body>

<h1>SHOW LIST OF REGISTERED USERS</h1>

<form action="<? echo $GS_URL; ?>" method="post" name="coopersland" target="_self">
	<input type="hidden" name="userid"   value="<? echo $GS_userid; ?>" />
	<input type="hidden" name="password" value="<? echo $GS_password; ?>" />
	<b>Group Name:</b> <input type="text" name="arg1" value="" />
	<input type="hidden" name="function" value="registered_persons_by_group_name" />
	<br />
	<input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
<?
} // endif
?>