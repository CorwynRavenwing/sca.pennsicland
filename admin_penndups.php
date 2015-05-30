<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "PENN Number Duplicates", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {

  $sql = "select penn_number, count(*) as num
      from cooper_data
      group by penn_number
      having num > 1
  ";

  $query = mysql_query($sql)
      or die("query failed: " . mysql_error()
          . " at file " . __FILE__ . " line " . __LINE__
          . " SQL<pre>$sql</pre>");
  ?>
<table border='1'>
  <tr style="background-color:silver; font-weight:bold; text-align:center;">
    <td>PENN NUMBER</td>
    <td>COUNT</td>
    <td>First Name</td>
    <td>Last Name</td>
    <td>SCA Name</td>
    <td>Group Name</td>
    <td>Previous Group</td>
    <td>ID</td>
  </tr>
  <?
  $count = 0;
  while ($result = mysql_fetch_assoc($query)) {
    $penn = $result['penn_number'];
    $num  = $result['num'];
    ?>
  <tr>
    <td><?=$penn?></td>
    <td><?=$num?></td>
    <?
    $sql2 = "select * from cooper_data
            where penn_number = '" . mysql_real_escape_string($penn) . "'
    ";

    $query2 = mysql_query($sql2)
      or die("query failed: " . mysql_error()
          . " at file " . __FILE__ . " line " . __LINE__
          . " SQL<pre>$sql2</pre>");
    $count = 0;
    while ($result2 = mysql_fetch_assoc($query2)) {
      if ($count++) {
        ?>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
        <?
      }
      ?>
    <td><?=$result2['first_name']?></td>
    <td><?=$result2['last_name']?></td>
    <td><?=$result2['sca_name']?></td>
    <td><?=$result2['group_name']?></td>
    <td><?=$result2['previous_group']?></td>
    <td><?=$result2['cooper_data_id']?></td>
      <?
    }
    ?>
  </tr>
    <?
  }
  if (!$count) {
    ?>
  <tr>
    <td colspan="10" style='text-align:center; font-weight:bold'>
        No duplicate PENN records found.
    </td>
  </tr>
    <?
  }
  ?>
</table>
  <?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>