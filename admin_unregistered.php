<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Unregistered Groups", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template

  $where_clause = "user_id = 0";
  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  if (mysql_num_rows($query)) {
    $columns = 0;
  ?>
<style type="text/css">
    .forbid {
        background-color: pink;
    }
</style>

<h2>List of Unregistered Groups</h2>

<h3>
    Click here for
    <a href='admin_unregistered_raw.php'>raw data</a>
    like the users see
</h3>

<table class='sort-table' id='table-1' cellspacing='0' border='1'>
  <thead>
    <tr style="background-color:silver; font-weight:bold;">
      <td title='CaseInsensitiveString'>Group Name</td>  <? $columns++; ?>
      <!--
      <td title='CaseInsensitiveString'>Legal Name</td>  <? $columns++; ?>
      <td title='CaseInsensitiveString'>Sca Name</td>    <? $columns++; ?>
      <td title='CaseInsensitiveString'>User Name</td>  <? $columns++; ?>
      -->
    </tr>
  </thead>
  <tbody>
    <?
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $g_id             = $result['group_id'];
      $u_id             = $result['user_id'];
      $group_name       = $result['group_name'];

      $g_status         = group_status_name( $result['status'] );

      $user_rec         = user_record($u_id);    # add left join to query?
      $user_legal_name  = $user_rec['legal_name'];
      $user_alias       = $user_rec['alias'];
      $user_username    = $user_rec['user_name'];

      if ($g_status == "Forbid") {
          $class_2 = "forbid";
      } else {
          $class_2 = "";
      }
      ?>
    <tr class='<?=$class?> <?=$class_2?>'>
      <td>
        <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
          <nobr><?=$group_name?></nobr>
        </a>
      </td>
      <!--
      <td>
      <? if ($u_id) { ?>
        <a href='admin_users.php?id=<?=$u_id?>' target='_blank'>
          <?=$user_legal_name?>
        </a>
      <? } else { ?>
          (not&nbsp;registered)
      <? } // endif ?>
      </td>
      <td><?=$user_alias?></td>
      <td><?=$user_username?></td>
      -->
    </tr>
      <?
    } // next result

    mysql_free_result($query);        # delete query object

    if (! $count) {
      ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>No Unregistered Groups.</b></font>
      </td>
    </tr>
      <?
    } else {
      ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>Total of <?=$count?> Unregistered Groups.</b></font>
      </td>
    </tr>
      <?
    } // endif count
    ?>
  </tbody>
</table>
    <?
  } else {
    ?>
<h3>(no groups found)</h3>
    <?
  }
} // endif id, admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>