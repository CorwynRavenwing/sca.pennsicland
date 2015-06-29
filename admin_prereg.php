<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Preregistered Campers", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

$id         = @$_GET['id'];
$group_name = @$_GET['group'];
$search     = @$_GET['search'];  $search     = trim( $search );

if (! $group_name) {
  if ($id) {
    $group_name = group_name($id);
  }
}

?>
<form name="search_form" action="?" method="get">
  Search for a camper (by name or PENN number):
  <input name="search" id="search" type="text" value="<?=$search?>" />
  <input name="submit" id="submit" type="submit" value="Search" />
</form>
<?

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif (! $group_name and ! $search) {

  $where_clause = " pre_registration_count != 0 ";
  $order_by = "pre_registration_count DESC, group_name";

  $query = group_query_mult($where_clause, $order_by);
  ?>
<h2>People Registered in Various Groups</h2>

<table border="1">
  <tr style="background-color:silver; font-weight:bold">
    <td>Group Name</td>
    <td># Campers</td>
  </tr>
  <?
  $count = 0;
  $total_campers = 0;
  while ($result = mysql_fetch_assoc($query)) {
    $class = ($count++ % 2) ? "even" : "odd";
    $total_campers += $result['pre_registration_count'];
    ?>
  <tr class="<?=$class?>">
    <td>
      <a href="admin_groups.php?id=<?=$result['group_id']?>">
        <?=$result['group_name']?>
      </a>
    </td>
    <td style="text-align:right;">
      <a href="?id=<?=$result['group_id']?>">
        <?=$result['pre_registration_count']?>
      </a>
    </td>
  </tr>
    <?
  } // next result
  ?>
  <tr>
    <td colspan="2" style="text-align:center; font-weight:bold">
      Total of <?=$count?> Groups containing <?=$total_campers?> Campers
    </td>
  </tr>
</table>
  <?
} else {
  if ($search) {
    $prereg_list = load_preregistrations_by_search($search);
    ?>
<h2>People Matching Search '<?=$search?>'</h2>
    <?
  } else {
    $prereg_list = load_preregistrations_by_group_name($group_name);
    ?>
<h2>People Registered in Group '<?=$group_name?>'</h2>
    <?
  } // endif search

  ?>
<table border='1'>
  <tr bgcolor='silver' style='font-weight:bold'>
    <td>Penn Number</td>
    <td>First Name</td>
    <td>Last Name</td>
    <td>Sca Name</td>
    <td>Group Name</td>

    <td>Previous Group</td>
  </tr>
  <?
  $count = 0;

  foreach ($prereg_list as $reg) {
    $count++;
    ?>
  <tr>
    <td><?=$reg['id']?>&nbsp;</td>
    <td><?=$reg['first_name']?>&nbsp;</td>
    <td><?=$reg['last_name']?>&nbsp;</td>
    <td><?=$reg['sca_name']?>&nbsp;</td>
    <td><?=$reg['group_name']?>&nbsp;</td>

    <td><?=$reg['previous_group']?>&nbsp;</td>

    <? /*
    <td><a href="?id=<?=$id?>&del=<?=$reg['cooper_data_id']?>">DELETE</a>
// note: previous line will always fail, because cooper_data_id always returns 0,
// because we're coming from the Cooper data rather than from the local database's
// copy of the Cooper data.  Not really sure how to fix this.  [Corwyn PW42]
    */ ?>
  </tr>
    <?
  }
  ?>
</table>

<h2>Total of <?=$count?> people</h2>
  <?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>