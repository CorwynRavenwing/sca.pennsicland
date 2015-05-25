<?
require_once("include/nav.php");
require_once("include/cooper.php");
require_once("include/group_history.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "On-Site View", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  $last_year = ($pennsic_number - 1);  # IS THIS USED?

  # no template page

  $table_id = "table-1";

  $columns = 0;
  ?>
<!-- h5>You should probably run <a href='admin_prereg_count.php'>Update Prereg Count</a>, if you haven't lately</h5 -->

<table class='sort-table' id='$table_id' cellspacing='0'>
  <col />
  <col />
  <thead>
  <tr bgcolor="silver">

    <td>Group Name<br />(GroupID)</td>   <? $columns++; ?>
    <td>Legal Name<br />(Sca Name)</td>  <? $columns++; ?>
    <td title='First Choice'>Ch1</td>    <? $columns++; ?>
    <td title='Second Choice'>Ch2</td>   <? $columns++; ?>
    <td title='Third Choice'>Ch3</td>    <? $columns++; ?>
    <td title='Fourth Choice'>Ch4</td>   <? $columns++; ?>
    <td>Final Location</td>              <? $columns++; ?>
    <td title='Number Of People'>#</td>  <? $columns++; ?>
    <td>Square Footage</td>              <? $columns++; ?>
    <td>Neighbors</td>                   <? $columns++; ?>
  </tr>
  </thead>
  <tbody>
  <?
  roll_up_used_space();

  # load registered groups
  $where_clause = "user_id != 0";
  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  $count = 0;
  while ($result = mysql_fetch_assoc($query)) {
    $class = (++$count % 2) ? "even" : "odd" ;

    # print("DEBUG: group<pre>"); print_r($result); print("</pre>\n");

    $g_id         = $result['group_id'];
    $u_id         = $result['user_id'];
    $group_name   = $result['group_name'];
    $prereg_count = $result['pre_registration_count'];
    $alloted_feet = floor( $result['alloted_square_footage'] );

    $choice_1     = $result['first_block_choice'];   $choice_1 = block_name($choice_1);
    $choice_2     = $result['second_block_choice'];  $choice_2 = block_name($choice_2);
    $choice_3     = $result['third_block_choice'];   $choice_3 = block_name($choice_3);
    $choice_4     = $result['fourth_block_choice'];  $choice_4 = block_name($choice_4);
    $final_id     = $result['final_block_location'];

    $user_rec    = user_record($u_id);
    $user_alias  = $user_rec['alias'];
    $user_legal  = $user_rec['legal_name'];

    # print("DEBUG: user<pre>"); print_r($user_rec); print("</pre>\n");

    $block_rec  = block_record($final_id);
    $final      = $block_rec['block_name'];
    $neighbors  = $block_rec['generate_neighbors'];

    # print("DEBUG: block<pre>"); print_r($block_rec); print("</pre>\n");
    ?>
  <tr class="<?=$class?>">
    <td valign='top'>
      <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
        <?=$group_name?>
      </a>
      <br />(<?=$g_id?>)
    </td>
    <td valign='top'>
      <a href='admin_users.php?id=<?=$u_id?>' target='_blank'>
        <?=$user_legal?>
      </a>
      <br />(<?=$user_alias?>)
    </td>
    <td align='center' valign='top'><?=$choice_1?></td>
    <td align='center' valign='top'><?=$choice_2?></td>
    <td align='center' valign='top'><?=$choice_3?></td>
    <td align='center' valign='top'><?=$choice_4?></td>
    <td align='center' valign='top'><b><?=$final?></b></td>
    <td align='center' valign='top'>
      <a href='admin_prereg.php?id=<?=$g_id?>' title='Cooper prereg count' target='_blank'>
        <?=$prereg_count?>
      </a>
    </td>
    <td align='center' valign='top'><?=$alloted_feet?></td>
    <td align='left'><pre><?=$neighbors?></pre></td>
  </tr>
    <?
    @ob_flush();
  } // next result

  if( $count == 0 ) {
    ?>
  <tr>
    <td colspan="<?=$columns?>" align='center'><font size='+2'><b>No Groups</b></font></td>
  </tr>
    <?
  } else {
    ?>
  <tr>
    <td colspan="<?=$columns?>" style="font-size:1.2em; font-weight:bold" align='center'>
      Total of <?=$count?> Groups
    </td>
  </tr>
    <?
  } // endif count
  ?>
  </tbody>
</table>
  <?
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>