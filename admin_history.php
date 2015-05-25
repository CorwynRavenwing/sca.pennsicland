<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "View History", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

$id = @$_GET['id'];
$groups = @$_GET['groups'];
$blocks = @$_GET['blocks'];

$year = @$_GET['year'];

$ymin = @$_GET['year_min'];
$ymax = @$_GET['year_max'];

if ($year) { $ymin = $year; $ymax = $year; }

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} elseif ($id) {
  # this section copied from history.php, with small changes

  # no template

  $detail_group_name = group_name($id);
  if (!$detail_group_name) {
    $detail_group_name = "(no group name)";
  }
  ?>
<h3>History for group #<?=$id?>: <a href='admin_groups.php?id=<?=$id?>'><?=$detail_group_name?></a></h3>
<table align="center" border="1" cellpadding="4">
  <tr>
    <td>Pennsic Year</td>
    <td>Calendar Year</td>
    <td>Location (count)</td>
  </tr>
  <?
  $sql = "SELECT year, attendance, h.block_id, block_name
      FROM land_group_history AS h
        LEFT JOIN land_blocks USING(block_id)
      WHERE group_id = '$id'
  ";
  if ($ymin) {
    $sql .= "AND year >= '$ymin'\n";
  }
  if ($ymax) {
    $sql .= "AND year <= '$ymax'\n";
  }
  $sql .= "
      ORDER BY year
  ";
  # need to filter by ymin and ymax here

  $min_year = 21;                 # first year with Pennsic history
  $max_year = $pennsic_number;    # last posible year == now
  if ($ymin) { $min_year = max($min_year, $ymin); }
  if ($ymax) { $max_year = min($max_year, $ymax); }

  print ("<!-- group history sql:\n$sql\n-->\n");
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  if (mysql_num_rows($query)) {
    $prev_pennsic = "";
    $count = 0;
    $count_active = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $pennsic    = $result['year'];
      $year       = $pennsic + 1971;
      $block_id   = $result['block_id'];
      $location   = $result['block_name'];
      if (! $location) {
        $location   = "<b>B[$block_id]</b>";
      }
      $attendance = $result['attendance'];
      if (($prev_pennsic+1) != $pennsic) {
        if ($prev_pennsic) {
          for ($p = $prev_pennsic+1; $p < $pennsic; $p++) {
            $year       = $p + 1971;
            ?>
  <tr class='<?=$class?>'>
    <td><?=$p?></td>
    <td><?=$year?></td>
    <td>&nbsp;</td>
  </tr>
            <?
            $class = (++$count % 2) ? "odd" : "even";
          } // next p
        } else {
          # SKIP FROM PENNSIC 1 TO FIRST RECORD
        } //. endif prev_pennsic
      } // endif prev_pennsic+1

      $prev_pennsic = $pennsic;
      $count_active++;
      ?>
  <tr class='<?=$class?>'>
    <td><?=$pennsic?></td>
    <td><?=$year?></td>
    <td><?=$location?>&nbsp;(<?=$attendance?>)</td>
  </tr>
      <?
    } // next result

    if (($pennsic+1) != $max_year) {
      for ($p = $pennsic+1; $p < $max_year; $p++) {
        $year       = $p + 1971;
        $class = (++$count % 2) ? "odd" : "even";
        ?>
  <tr class='<?=$class?>'>
    <td><?=$p?></td>
    <td><?=$year?></td>
    <td>&nbsp;</td>
  </tr>
        <?
      } // next p
    } //.endif pennsic+1
    ?>
  <tr>
      <td colspan="3" style="text-align:center; font-size:1.25em; font-weight;bold">
          Total of <?=$count_active?> years of history
      </td>
  </tr>
    <?
  } else {
    ?>
  <tr>
      <td colspan="3" style="text-align:center; font-size:1.25em; font-weight;bold">
          This group has no history
      </td>
  </tr>
    <?
  } // endif num_rows
  ?>
</table>
  <?
} else {
  # main History page

  # no template

  $sql = "SELECT h.group_id, group_name, user_id, year, attendance, block_name, h.block_id
      FROM land_group_history AS h
        LEFT JOIN land_groups USING(group_id)
        LEFT JOIN land_blocks USING(block_id)
      WHERE group_name IS NOT NULL
  ";
  if ($groups) {
    $group_list = split(",", $groups);
    $sql .= "AND group_id IN ('" . join("','", $group_list) . "')\n";
  }
  if ($blocks) {
    $block_list = split(",", $blocks);
    $sql .= "AND block_name IN ('" . join("','", $block_list) . "')\n";
  }
  if ($ymin) {
    $sql .= "AND year >= '$ymin'\n";
  }
  if ($ymax) {
    $sql .= "AND year <= '$ymax'\n";
  }

  $sql .= "
      ORDER BY group_name, h.group_id, year
  ";

  print ("<!-- group history sql:\n$sql\n-->\n");
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  if (mysql_num_rows($query)) {
    ?>
<table border='1' class='sort-table' cellspacing='0' border='1'>
    <?
    $columns = 0;
    $count = 0;
    ?>
  <thead>
    <tr style="font-weight:bold">
      <td>Group&nbsp;Name</td>          <? $columns++; ?>
      <td>*</td>                  <? $columns++; ?>
    <?
    $min_year = 21;                 # first year with Pennsic history
    $max_year = $pennsic_number;    # last posible year == now
    if ($ymin) { $min_year = max($min_year, $ymin); }
    if ($ymax) { $max_year = min($max_year, $ymax); }

    for ($year = $min_year; $year <= $max_year; $year++) {
      ?>
      <td title='number'>P<?=$year?></td>      <? $columns++; ?>
      <?
    } // next year
    ?>
    </tr>
  </thead>
  <tbody>
    <?
    $prev_u_id = "";
    $prev_group = "---";
    $prev_pennsic = "";
    while ($result = mysql_fetch_assoc($query)) {
      $g_id       = $result['group_id'];
      $group_name = $result['group_name'];
      if (! $group_name) {
        $group_name = "<b>G[$g_id]</b>";
      }
      $u_id       = $result['user_id'];
      $pennsic    = $result['year'];
      $block_id   = $result['block_id'];
      $location   = $result['block_name'];
      if (! $location) {
        $location   = "<b>B[$block_id]</b>";
      }
      $attendance = $result['attendance'];

      if ($prev_group != $group_name) {
        if ($prev_group != "---") {
          if ($prev_pennsic < $max_year) {
            for ($p = $prev_pennsic+1; $p < $max_year; $p++) {
              ?>
      <td>&nbsp;</td>
              <?
            } // next p

            if ($prev_u_id) {
              $reg = "<span title='registered'>***</span>";
            } else {
              $reg = "<span title='not registered'>---</a>";
            }
            ?>
      <td><?=$reg?></td>
            <?
          } // endif $max_year
          ?>
    </tr>
          <?
        } // endif prev_group not blank

        $class = (++$count % 2) ? "odd" : "even";
        ?>
    <tr class="<?=$class?>">
      <td>
        <a href='admin_groups.php?id=<?=$g_id?>' target='_blank'>
          <nobr><?=$group_name?></nobr>
        </a>
      </td>
      <td>
        <a href='?id=<?=$g_id?>' target='_blank'>*</a>
      </td>
        <?
        $prev_u_id    = $u_id;
        $prev_group   = $group_name;
        $prev_pennsic = "";

        if ($pennsic != $min_year) {
          for ($p = $min_year; $p < $pennsic; $p++) {
            ?>
      <td>&nbsp;</td>
            <?
          } // next p
        } // endif min_year
      } // endif prev_group new

      if (($prev_pennsic+1) != $pennsic) {
        if ($prev_pennsic) {
          for ($p = $prev_pennsic+1; $p < $pennsic; $p++) {
            ?>
      <td>&nbsp;</td>
            <?
          } // next p
        } //. endif prev_pennsic
      } // endif prev_pennsic+1

      $prev_pennsic = $pennsic;
      ?>
      <td><span title='<?=$attendance?>'><?=$location?></span></td>
      <?
    } // next result

    if ($prev_pennsic < $max_year) {
      for ($p = $prev_pennsic+1; $p < $max_year; $p++) {
        ?>
      <td>&nbsp;</td>
        <?
      } // next p

      if ($u_id) {
        $reg = "<span title='registered'>***</span>";
      } else {
        $reg = "<span title='not registered'>---</a>";
      }
      ?>
      <td><?=$reg?></td>
      <?
    } // endif $max_year
    ?>
    </tr>
  </tbody>
</table>
    <?
  } else {
    print "<h1>NO GROUP HISTORIES FOUND!</h1>\n";
  } // endif num_rows
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>