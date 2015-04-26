<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Group Name Check", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $admin) {
  print "<h2 color='blue'>Please log on as Pennsic Land staff first.</h2>\n";
} else {

  $post_group_id   = @$_POST['post_group_id'];
  $change_status_0 = @$_POST['change_status_0'];
  $change_status_1 = @$_POST['change_status_1'];
  $change_status_2 = @$_POST['change_status_2'];

  if ($post_group_id) {

      $post_status = ( $change_status_0 ? 0 : ( $change_status_1 ? 1 : ( $change_status_2 ? 2 : 3 ) ) );

      if ($post_status == 3) {
    ?>
  <h2 style='color:red'>Unable to change status: invalid POST parameters.</h2>
    <?
      } else {

    $sql2 = "UPDATE land_groups SET status = '$post_status' WHERE group_id = '$post_group_id' LIMIT 1";

    if (headers_sent()) { print "<!-- admin_group_check(status update) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  <h2 style='color:green'>Changing status for group #<?=$post_group_id?> to status #<?=$post_status?>.</h2>
      <?
    } else {
      ?>
  <h2 style='color:red'>Unable to change status: update failed.</h2>
      <?
    } // endif num

      } // endif post_status 3

  } // endif post_group_id


  $my_group_id  = @$_REQUEST['group_id'];
  $my_status_id  = @$_REQUEST['status_id'];
  $my_search  = @$_REQUEST['search'];


  $sql = "SELECT status, count(*) AS num FROM land_groups GROUP BY status";

  if (headers_sent()) { print "<!-- admin_group_check(status count) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
  ?>
    <h2>GROUP STATUS DATA:
  <?
  while ($result = mysql_fetch_array($query)) {
    $g_status  = $result['status'];
    $g_num    = $result['num'];

    $g_status_name  = group_status_name($g_status);

    if ($my_status_id != $g_status) {
      $g_status_name = "<a href='?status_id=$g_status'>$g_status_name</a>";
    }

    print "$g_status_name:$g_num&nbsp;";
  } // next result
  ?>
    </h2>
  <?

  if ($my_group_id) {
    $my_group_name = group_name($my_group_id);
    print("<h3>group #$my_group_id [$my_group_name] <a href='?'>(CLEAR)</a></h3>\n");
    $where_clause = " group_id = '$my_group_id' ";
  } elseif ($my_status_id != "") {
    $my_status_name = group_status_name($my_status_id);
    print "<h3>status #$my_status_id [$my_status_name] <a href='?'>(CLEAR)</a></h3>\n";
    $where_clause = " status = '$my_status_id' ";
  } elseif ($my_search) {
    print "<h3>matching '$my_search' <a href='?'>(CLEAR)</a></h3>\n";
    $where_clause = "
      group_name = '$my_search'
        OR group_name_base = '$my_search'
        OR group_soundex = '$my_search'
        OR group_metaphone = '$my_search'
    ";
  } else {
    print "<h3>New groups needing allow/forbid ruling</h3>\n";
    $where_clause = "
      status IN ( 2, 3 )
        AND group_name_base != ''
        AND group_soundex != ''
        AND group_metaphone != ''
     ";
  } // endif group_id or status_id or search passed in


  $sql = "SELECT group_id, group_name, group_name_base, group_soundex, group_metaphone, status
    FROM land_groups
    WHERE $where_clause
    ORDER BY status DESC,
      group_name
    LIMIT 100
  ";

  if (headers_sent()) { print "<!-- admin_group_check(status query) SQL:\n$sql\n-->\n"; }

  ?>
<table border="1">
    <tr style='background-color:silver; font-weight:bold'>
  <td>Group Name</td>
  <td label='Years of group history'>Yrs</td>
  <td>Name Base</td>
  <td>Soundex</td>
  <td>Metaphone</td>
  <td>Status</td>
    </tr>
  <?
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  $count = 0;
  while ($result = mysql_fetch_array($query)) {
    $g_id        = $result['group_id'];
    $g_name      = $result['group_name'];
    $g_name_base = $result['group_name_base'];
    $g_soundex   = $result['group_soundex'];
    $g_metaphone = $result['group_metaphone'];
    $g_status    = $result['status'];

    if ($count++) {
      ?>
    <tr style='background-color:white'>
  <td colspan="5">&nbsp;</td>
    </tr>
      <?
    }
    $count_history = count_where("land_group_history", "group_id = '$g_id'");
    ?>
    <tr style='font-weight:bold'>
  <td><nobr><a href='admin_groups.php?id=<?=$g_id?>'><?=$g_name?></a></nobr></td>
  <td style='text-align:right'><?=$count_history?></td>
  <td><?=$g_name_base?></td>
  <td><?=$g_soundex?></td>
  <td><?=$g_metaphone?></td>
  <td><? show_group_status($g_id,$g_status); ?></td>
    </tr>
    <?
    $sql2 = "SELECT group_id, group_name, group_name_base, group_soundex, group_metaphone, status
      FROM land_groups
      WHERE group_id != '$g_id' AND (
        group_name_base = '$g_name_base'
           OR group_soundex   = '$g_soundex'
           OR group_metaphone = '$g_metaphone'
      )
      LIMIT 100
    ";

    if (headers_sent()) { print "<!-- admin_group_check(matching groups check) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    while ($result2 = mysql_fetch_array($query2)) {
      $g_id2        = $result2['group_id'];
      $g_name2      = $result2['group_name'];
      $g_name_base2 = $result2['group_name_base'];
      $g_soundex2   = $result2['group_soundex'];
      $g_metaphone2 = $result2['group_metaphone'];
      $g_status2    = $result2['status'];

      $count_history = count_where("land_group_history", "group_id = '$g_id2'");
      ?>
      <tr style='background-color:yellow'>
    <td><a href='admin_groups.php?id=<?=$g_id2?>'><?=$g_name2?></td>
    <td style='text-align:right'><?=$count_history?></td>
    <td style='<?=(($g_name_base == $g_name_base2) ? "background-color:pink" : "")?>'><?=$g_name_base2?></td>
    <td style='<?=(($g_soundex == $g_soundex2) ? "background-color:pink" : "")?>'><?=$g_soundex2?></td>
    <td style='<?=(($g_metaphone == $g_metaphone2) ? "background-color:pink" : "")?>'><?=$g_metaphone2?></td>
    <td><?=group_status_name($g_status2)?></td>
      </tr>
      <?
    } // next result2

  } // next result
  ?>
</table>
  <?
  # global $count_group_check;

  print "<h2>Total of $count_group_check problem groups:</h2>\n";

  print "<h3>Correcting missing group_name_base</h3>\n";

  $sql = "SELECT group_id, group_name, group_name_base FROM land_groups
    WHERE group_name_base = ''
      OR group_name_base LIKE '%xyzzy%'
      OR group_name_base LIKE '%xyzzy%'
    LIMIT 100";
  # NB: use 'xyzzy' above to re-create name-base for things we begin checking for

  if (headers_sent()) { print "<!-- admin_group_check(name_base query) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_array($query)) {
    $g_id    = $result['group_id'];
    $g_name    = $result['group_name'];
    $g_name_base    = $result['group_name_base'];
    $group_name_base= " " . $g_name . " ";

    $group_name_base= str_replace("'", "", $group_name_base);  # single quote
    $group_name_base= str_replace("’", "", $group_name_base);  # right single quote
    $group_name_base= str_replace("’", "", $group_name_base);  # different(?) right single quote
    $group_name_base= str_replace(".", "", $group_name_base);  # period

    $group_name_base= str_replace("-", " ", $group_name_base);  # hyphen => space

    $group_name_base= str_ireplace(" a ", " ", $group_name_base);
    $group_name_base= str_ireplace(" al ", " ", $group_name_base);
    $group_name_base= str_ireplace(" an ", " ", $group_name_base);
    $group_name_base= str_ireplace(" and ", " ", $group_name_base);
    $group_name_base= str_ireplace(" band ", " ", $group_name_base);
    $group_name_base= str_ireplace(" barony ", " ", $group_name_base);
    $group_name_base= str_ireplace(" camp ", " ", $group_name_base);
    $group_name_base= str_ireplace(" casa ", " ", $group_name_base);
    $group_name_base= str_ireplace(" clan ", " ", $group_name_base);
    $group_name_base= str_ireplace(" clann ", " ", $group_name_base);
    $group_name_base= str_ireplace(" clanne ", " ", $group_name_base);
    $group_name_base= str_ireplace(" club ", " ", $group_name_base);
    $group_name_base= str_ireplace(" compagnie ", " ", $group_name_base);
    $group_name_base= str_ireplace(" company ", " ", $group_name_base);
    $group_name_base= str_ireplace(" de ", " ", $group_name_base);
    $group_name_base= str_ireplace(" del ", " ", $group_name_base);
    $group_name_base= str_ireplace(" den ", " ", $group_name_base);
    $group_name_base= str_ireplace(" der ", " ", $group_name_base);
    $group_name_base= str_ireplace(" des ", " ", $group_name_base);
    $group_name_base= str_ireplace(" di ", " ", $group_name_base);
    $group_name_base= str_ireplace(" die ", " ", $group_name_base);
    $group_name_base= str_ireplace(" do ", " ", $group_name_base);
    $group_name_base= str_ireplace(" house ", " ", $group_name_base);
    $group_name_base= str_ireplace(" household ", " ", $group_name_base);
    $group_name_base= str_ireplace(" keep ", " ", $group_name_base);
    $group_name_base= str_ireplace(" kingdom ", " ", $group_name_base);
    $group_name_base= str_ireplace(" la ", " ", $group_name_base);
    $group_name_base= str_ireplace(" le ", " ", $group_name_base);
    $group_name_base= str_ireplace(" shire ", " ", $group_name_base);
    $group_name_base= str_ireplace(" shires ", " ", $group_name_base);
    $group_name_base= str_ireplace(" the ", " ", $group_name_base);
    $group_name_base= str_ireplace(" of ", " ", $group_name_base);

    $group_name_base= str_replace(" ", "", $group_name_base);  # space => blank

    $group_name_base= strtolower($group_name_base);            # upper => lower

    $sql2 = "UPDATE land_groups SET group_name_base = '$group_name_base' WHERE group_id = '$g_id' LIMIT 1";

    if (headers_sent()) { print "<!-- admin_group_check(name_base set) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  <?=$group_name_base?> ;
      <?
    } // endif num
  } // next result
  print "<br/>\n";

  print "<h3>Correcting missing group_soundex</h3>\n";

  $sql = "UPDATE land_groups SET group_soundex = SOUNDEX(group_name) WHERE group_soundex = '' LIMIT 100";

  if (headers_sent()) { print "<!-- admin_group_check(soundex) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  if ($num = mysql_affected_rows()) {
    ?>
<h2>A: Updated <?=$num?> rows</h2>
    <?
  } // endif num

  print "<h3>Correcting missing group_metaphone</h3>\n";

  $sql = "SELECT group_id, group_name, group_metaphone FROM land_groups WHERE group_metaphone = '' LIMIT 100";

  if (headers_sent()) { print "<!-- admin_group_check(soundex query) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_array($query)) {
    $g_id    = $result['group_id'];
    $g_name    = $result['group_name'];
    $g_metaphone  = $result['group_metaphone'];
    $group_metaphone= metaphone($g_name);

    $sql2 = "UPDATE land_groups SET group_metaphone = '$group_metaphone' WHERE group_id = '$g_id' LIMIT 1";

    if (headers_sent()) { print "<!-- admin_group_check(metaphone set) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  metaphone(<?=htmlentities($g_name)?>): <?=$group_metaphone?><br/>
      <?
    } // endif num
  } // next result
  print "<br/>\n";

  print "<h3>Setting status=3 for shared group_name_base</h3>\n";

  $sql = "SELECT group_name_base,  count(*) AS num
    FROM land_groups
    WHERE group_name_base != ''
    GROUP BY group_name_base
    HAVING num > 1
    ORDER BY RAND()
    LIMIT 10
  ";

  if (headers_sent()) { print "<!-- admin_group_check(name_base problem) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_array($query)) {
    $g_name_base  = $result['group_name_base'];

    $sql2 = "UPDATE land_groups SET status = 3 WHERE status = 2 AND group_name_base = '$g_name_base'";

    if (headers_sent()) { print "<!-- admin_group_check(name_base problem set) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  base name (<?=$g_name_base?>): <?=$num?><br/>
      <?
    } // endif num

  } // next result

  print "<h3>Setting status=3 for shared group_soundex</h3>\n";

  $sql = "SELECT group_soundex,  count(*) AS num
    FROM land_groups
    WHERE group_soundex != ''
    GROUP BY group_soundex
    HAVING num > 1
    ORDER BY RAND()
    LIMIT 10
  ";

  if (headers_sent()) { print "<!-- admin_group_check(soundex problem) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_array($query)) {
    $g_soundex    = $result['group_soundex'];

    $sql2 = "UPDATE land_groups SET status = 3 WHERE status = 2 AND group_soundex = '$g_soundex'";

    if (headers_sent()) { print "<!-- admin_group_check(soundex problem set) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  soundex (<?=$g_soundex?>): <?=$num?><br/>
      <?
    } // endif num

  } // next result

  print "<h3>Setting status=3 for shared group_metaphone</h3>\n";

  $sql = "SELECT group_metaphone,  count(*) AS num
    FROM land_groups
    WHERE group_metaphone != ''
    GROUP BY group_metaphone
    HAVING num > 1
    ORDER BY RAND()
    LIMIT 10
  ";

  if (headers_sent()) { print "<!-- admin_group_check(metaphone problem) SQL:\n$sql\n-->\n"; }

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  while ($result = mysql_fetch_array($query)) {
    $g_metaphone  = $result['group_metaphone'];

    $sql2 = "UPDATE land_groups SET status = 3 WHERE status = 2 AND group_metaphone = '$g_metaphone'";

    if (headers_sent()) { print "<!-- admin_group_check(metaphone problem set) SQL:\n$sql2\n-->\n"; }

    $query2 = mysql_query($sql2)
      or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);

    if ($num = mysql_affected_rows()) {
      ?>
  metaphone (<?=$g_metaphone?>): <?=$num?><br/>
      <?
    } // endif num

  } // next result

  print "<h3>Done.</h3>\n";

} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();

function show_group_status($g_id, $g_status) {
  # NOTE: not action="?" because we don't want to lose the filter in the querystring [Corwyn PW41]
  ?>
<form action="" method="POST">
    <nobr>
  <input type="hidden" name="post_group_id" value="<?=$g_id?>" />
  <?
  foreach ( array(0, 1, 2) as $x ) {
    $n = group_status_name($x);

    if ($g_status == $x) {
      print $n;
    } else {
      ?>
  <input type="submit" name="change_status_<?=$x?>" value="<?=$n?>" />
      <?
    }
  } // next x

  $x = 3;
  $n = group_status_name($x);
  if ($g_status == $x) {
    print $n;
  } // else nothing
  ?>
    </nobr>
</form>
  <?
} // end function show_group_status

?>