<?
require_once("include/nav.php");
require_once("include/cooper.php");
require_once("include/group_history.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Land One View", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  $out_dir  = "datafiles";
  if(! is_dir($out_dir)) {
    mkdir($out_dir, 0755)  or die("can't create '$out_dir'");
  }
  # -w $out_dir or chmod(0755, $out_dir)  or die "can't make '$out_dir' writeable";
  # -x $out_dir or chmod(0755, $out_dir)  or die "can't make '$out_dir' executable";

  $out_file = "${out_dir}/landone_export_${user_id}.csv";
  $fp = fopen($out_file, "w") or die("can't write file '$out_file'");

  $last_year = ($pennsic_number - 1);
  ?>
<!-- h5>You should probably run <a href='admin_prereg_count.php'>Update Prereg Count</a>, if you haven't lately</h5 -->

<table class='sort-table' id='$table_id' cellspacing='0'>
  <col />
  <col />
  <thead>
  <tr bgcolor="silver">
    <td align='center' title='CaseInsensitiveString'>Group Name</td>
    <td align='center' title='CaseInsensitiveString'>Sca Name</td>
    <td align='center' title='CaseInsensitiveString' title='First Choice' >Ch1</td>
    <td align='center' title='CaseInsensitiveString' title='Second Choice'>Ch2</td>
    <td align='center' title='CaseInsensitiveString' title='Third Choice' >Ch3</td>
    <td align='center' title='CaseInsensitiveString' title='Fourth Choice'>Ch4</td>
    <td align='center' title='CaseInsensitiveString'>Final<br />Location</td>
    <td align='center' title='CaseInsensitiveString'>Last Year<br />(P<?=$last_year?>) Location</td>
    <td align='center' title='CaseInsensitiveString'>Number<br />of People</td>
    <td align='center' title='CaseInsensitiveString'>P<?=$last_year?>&nbsp;People<br />(growth&nbsp;%)</td>
    <td align='center' title='CaseInsensitiveString'>Square<br />Footage</td>
  </tr>
  </thead>
  <?
  #header row
  fwrite($fp, "# BlockNumber,CampName,LandAgentReal,LandAgentSca,OnsiteRep,SquareFeet\n");
  ?>
  <tbody>
  <?
  # load registered groups
  $where_clause = "user_id != 0";
  $order_by = "group_name";
  $query = group_query_mult($where_clause, $order_by);

  $count = 0;
  $count_missingpeople = 0;
  $total_people = 0;

  # if (mysql_num_rows($query)) {

  while ($result = mysql_fetch_assoc($query)) {
    $class = (++$count % 2) ? "even" : "odd" ;

    $g_id        = $result['group_id'];
    $u_id        = $result['user_id'];
    $group_name      = $result['group_name'];
    $prereg_count    = $result['pre_registration_count'];

    $cooper_count = count_cooper_records_raw($group_name);

    $alloted_feet = floor( $result['alloted_square_footage'] );
    $choice_1     = block_name( $result['first_block_choice']  );
    $choice_2     = block_name( $result['second_block_choice']  );
    $choice_3     = block_name( $result['third_block_choice']  );
    $choice_4     = block_name( $result['fourth_block_choice']  );
    $final_block  = block_name( $result['final_block_location']  );
    $onsite_rep   = $result['on_site_representative'];

    $user_rec  = user_record($u_id);
    $user_alias  = $user_rec['alias'];
    $user_legal  = $user_rec['legal_name'];

    # $history_block = get_group_history_blockname_by_year(  $g_id, $last_year );
    # $history_count = get_group_history_attendance_by_year( $g_id, $last_year );

    list($history_block, $history_count) = get_group_history_both_by_year( $g_id, $last_year );

    $change_percent = ($history_count) ? floor(($prereg_count - $history_count) * 100 / $history_count) : 100;
    $change_color = ($change_percent >= 40) ? " color='red' style='font-weight:bold' " : "";

    if ($change_percent == 100) {
      $change_percent = "new";
      $change_color     = "";
    } else {
      $change_percent = ( ($change_percent < 0) ? ("-&nbsp;") : ("+&nbsp;") )
        . abs($change_percent) . "&nbsp;%";
    }

    # don't allow linking to the list of campers, when there are zero campers, in both our table and cooper data:
    $label_1 = "Cooper prereg count";
    $label_2 = "camper detail records";

    if ($prereg_count or $cooper_count) {
      # following links to admin_generate_group_prereg:
      $href_start = "<a href='admin_prereg.php?id=" . $g_id . "' title='$label_1' target='_blank'>";
      $href_stop  = "</a>";
    } else {
      $href_start = "<a title='$label_1'>";
      $href_stop  = "</a>";
    }

    if ($prereg_count != $cooper_count) {
      $cooper_count_text = " <a title='$label_2'>[$cooper_count]</a>";
      $count_missingpeople++;
    } else {
      $cooper_count_text = "";
    }

    $preg_link = ( $href_start . $prereg_count . $href_stop . $cooper_count_text );
    $total_people += $prereg_count;
    ?>
  <tr class="<?=$class?>">
    <td>
      <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
        <?=$group_name?>
      </a>
    </td>
    <td>
      <a href='admin_users.php?id=<?=$u_id?>' target='_blank'>
        <?=$user_alias?>
      </a>
    </td>
    <td align='center'><?=$choice_1?></td>
    <td align='center'><?=$choice_2?></td>
    <td align='center'><?=$choice_3?></td>
    <td align='center'><?=$choice_4?></td>
    <td align='center'><b><?=$final_block?></b></td>
    <td align='center'><?=$history_block?></td>
    <td align='center'><?=$preg_link?></td>
    <td align='center'>
      <?=$history_count?>
      (<font <?=$change_color?>><?=$change_percent?></font>)
    </td>
    <td align='center'><?=$alloted_feet?></td>
  </tr>
    <?
    @ob_flush();
    $data = array(
      $final_block,
      $group_name,
      $user_legal,
      $user_alias,
      $onsite_rep,
      $alloted_feet,
    );
    foreach ($data as $d) {
      $d = str_replace($d, '"', "'");    # replace doublequote with singlequote
    }
    fwrite($fp, '"' . join( '","', $data ) . '"' . "\n");
  } // next result

  if( $count == 0 ) {
    ?>
  <tr>
    <td colspan="50" align='center'><font size='+2'><b>No Groups</b></font></td>
  </tr>
    <?
  } else {
    ?>
  <tr>
    <td colspan="50" style="font-size:1.2em; font-weight:bold" align='center'>
      Total of <?=$count?> Groups
    <? if ($count_missingpeople) { ?>
      <span title='Groups with missing camper detail records'>
        [<?=$count_missingpeople?>]
      </span>
    <? } // endif ?>
      (<?=$total_people?> total people)
    </td>
  </tr>
    <?
  } // endif count
  ?>
  </tbody>
</table>
  <?
  fclose($fp);

  if (! is_readable($out_file) ) {
      chmod($out_file, 0644)
          or die("can't make '$out_file' world readable");
  }

  print "<h3>Download <a href='$out_file' target='_blank'>CSV extract</a> file</h3>\n";
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>