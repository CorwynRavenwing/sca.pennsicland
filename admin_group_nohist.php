<?
require_once("include/nav.php");
require_once("include/land_email.php");

$html_head_block = '<script type="text/javascript" language="javascript" src="js/check_block_answers.js"></script>';

$body_onload = "check_block_answers();";

nav_start_admin($html_head_block, $body_onload);

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "View Groups witnout History", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
# $count_history = count_where("land_group_history", "group_id = '$id'");

  # no template

  $sql = "SELECT
        g.group_id,
        g.group_name,
        g.status,
        u.user_id,
        u.alias,
        u.email_address
      FROM land_groups AS g
        LEFT JOIN land_group_history AS h USING(group_id)
        INNER JOIN user_information AS u USING(user_id)
      WHERE h.group_id IS NULL
        AND user_id != 0
        AND status in (0, 1, 3)
      ORDER BY g.group_name
  ";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);


  if (mysql_num_rows($query)) {
    $columns = 0;
    ?>
<h3>Groups with No History</h3>

<table class='sort-table' id='table-1' cellspacing='0' border='1'>
  <thead>
    <tr style="background-color:silver; font-weight:bold;">
      <td>Group Name</td>              <? $columns++; ?>
      <td>Group Status</td>            <? $columns++; ?>
      <td>User SCA Name</td>           <? $columns++; ?>
      <td>User Email Address</td>      <? $columns++; ?>
    </tr>
  </thead>
  <tbody>
    <?
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $g_id        = $result['group_id'];
      $u_id        = $result['user_id'];
      $group_name  = $result['group_name'];
      $g_status    = $result['status'];
      $user_alias  = $result['alias'];
      $user_email  = $result['email_address'];

#      $user_rec         = user_record($u_id);    # add left join to query?
#      $user_legal_name  = $user_rec['legal_name'];
#      $user_alias       = $user_rec['alias'];
#      $user_username    = $user_rec['user_name'];
      ?>
    <tr class='<?=$class?>'>
      <td>
        <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
          <nobr><?=$group_name?></nobr>
        </a>
      </td>
      <td>
        <?=group_status_name($g_status)?>
      </td>
      <td>
      <? if ($u_id) { ?>
        <a href='admin_users.php?id=<?=$u_id?>' target='_blank'>
          <?=$user_alias?>
        </a>
      <? } else { ?>
          (not&nbsp;registered)
      <? } // endif ?>
      </td>
      <td><?=$user_email?></td>
    </tr>
      <?
    } // next result

    ?>
    <tr>
      <td colspan="<?=$columns?>" align="center">
        <font size='+2'><b>Total of <?=$count?> Groups.</b></font>
      </td>
    </tr>
  </tbody>
</table>
    <?
  } else {
    ?>
<h3>(no groups found)</h3>
    <?
  }
  mysql_free_result($query);              # delete query object
} // endif id, admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>