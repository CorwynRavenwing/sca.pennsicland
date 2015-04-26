<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Groups with Admin Notes", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template

  ?>
<h2>List of Groups with Administrative Notes</h2>
  <?
  $where_clause = "(other_admin_information != '')";
  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  if (mysql_num_rows($query)) {
    ?>
<table border='1' cellpadding='4' cellspacing='0'>
  <tr bgcolor='silver'>
    <td>Group ID</td>
    <td>Group Name</td>
    <td>Administrative Notes</td>
  </tr>
    <?
    $count = 0;
    $total = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $id            = $result['group_id'];
      $group_name    = $result['group_name'];
      $notes_2       = $result['other_admin_information'];
      ?>
  <tr class='<?=$class?>'>
    <td align='right'><?=$id?></td>
    <td>
      <a href='admin_groups.php?id=<?=$id?>' target='_blank'>
        <?=$group_name?>
      </a>
    </td>
    <td align='center'><?=$notes_2?></td>
  </tr>
      <?
    } // next result
    ?>
  <tr>
    <td colspan='4' align='center'><b>Total of <?=$count?> Groups with Admin Notes</b></td>
  </tr>
</table>
    <?
  } else {
    ?>
<h3>(none)</h3>
    <?
  }
  mysql_free_result($query);              # delete query object
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>