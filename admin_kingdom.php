<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "View Kingdoms", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template

  ?>
<h2>List of Groups with Fixed Sizes (Kingdoms)</h2>
  <?
  $where_clause = "
      (exact_land_amount > 0)
      or (group_name LIKE 'Kingdom of %')
      or (group_name LIKE 'Populace of %')
  ";
  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  if (mysql_num_rows($query)) {
    ?>
<table border='1' cellpadding='4' cellspacing='0'>
  <tr bgcolor='silver'>
    <td>Group ID</td>
    <td>Group Name</td>
    <td>Exact Land Amount</td>
    <td>Registered?</td>
  </tr>
    <?
    $count = 0;
    $total = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $g_id          = $result['group_id'];
      $group_name    = $result['group_name'];
      $exact_footage = $result['exact_land_amount'];
      $u_id          = $result['user_id'];
      $total += $exact_footage;
      ?>
  <tr class='<?=$class?>'>
    <td align='right'><?=$g_id?></td>
    <td>
      <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
        <?=$group_name?>
      </a>
    </td>
    <td align='right'><?=$exact_footage?></td>
    <td align='center'>
      <?
      if ($u_id) {
        ?>
      <a href='admin_users.php?id=<?=$u_id?>' target='_blank'><?=$u_id?></a>
        <?
      } else {
        ?>
      (NO)
        <?
      }
      ?>
    </td>
  </tr>
      <?
    } // next result
    ?>
  <tr>
    <td colspan='4' align='center'><b>Total of <?=$count?> Groups with Fixed Sizes Totalling <?=$total?> Square Feet</b></td>
  </tr>
</table>
    <?
  } else {
    ?>
<h3>(none)</h3>
    <?
  }
  mysql_free_result($query);              # delete query object
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>