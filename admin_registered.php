<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Registered Groups", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template

  $filter = @$_REQUEST['filter'];    if (!$filter) { $filter = "B"; }

  $where_clause = "user_id != 0";

  $filter_array = array(
      "B" => "ALL",
      "C" => "Complete",
      "I" => "Incomplete",
  );
  $filter_display = "";

  switch ($filter) {

      case "B":
          // don't change $where_clause
          break;

      case "C":
          $where_clause .= " AND time_registered != 0 ";
          break;

      case "I":
          $where_clause .= " AND time_registered = 0 ";
          break;

      default:
          warn("invalid filter $filter in " . __FILE__ . " line " . __LINE__);
          break;

  } // end switch

  foreach ($filter_array as $code => $label) {
      if ($code == $filter) {
          $filter_display .= " $label";
      } else {
          $filter_display .= " <a href='?filter=$code'>$label</a>";
      } // endif
  } // next filter_array

  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  if (mysql_num_rows($query)) {
    $columns = 0;
    ?>
<h2>List of Registered Groups</h2>
<h3>Filter: <?=$filter_display?></h3>

<table class='sort-table' id='table-1' cellspacing='0' border='1'>
  <thead>
    <tr style="background-color:silver; font-weight:bold;">
      <td>Group Name</td>      <? $columns++; ?>
      <td>Legal Name</td>      <? $columns++; ?>
      <td>Sca Name</td>        <? $columns++; ?>
      <td>User Name</td>       <? $columns++; ?>
      <td>When Registered</td> <? $columns++; ?>
    </tr>
  </thead>
  <tbody>
    <?
    $count = 0;
    $count_incomplete = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $g_id         = $result['group_id'];
      $u_id         = $result['user_id'];
      $g_name       = $result['group_name'];
      $g_registered = $result['time_registered'];
      if ($g_registered) {
        $g_registered = date("Y-m-d H:i:s T", $g_registered ) ;
      } else {
        $g_registered = "<span style='color:red'>(registration incomplete)<span>";
        $count_incomplete++;
      }

      $user_rec         = user_record($u_id);    # add left join to query?
      $user_legal_name  = $user_rec['legal_name'];
      $user_alias       = $user_rec['alias'];
      $user_username    = $user_rec['user_name'];
      ?>
    <tr class='<?=$class?>'>
      <td>
        <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
          <nobr><?=$g_name?></nobr>
        </a>
      </td>
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
      <td style="white-space: nowrap;"><?=$g_registered?></td>
    </tr>
      <?
    } // next result

    mysql_free_result($query);        # delete query object

    if (! $count) {
      ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>No Registered Groups.</b></font>
      </td>
    </tr>
      <?
    } else {
      ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2' style='font-weight:bold'>Total of <?=$count?> Registered Groups.</font>
        <?
        if ($count_incomplete) {
            ?>
        <font size='+2' style='font-weight:bold; color:red'>(<?=$count_incomplete?> incomplete)</font>
            <?
        } // endif $count_incomplete
        ?>
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
} // endif id, r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>