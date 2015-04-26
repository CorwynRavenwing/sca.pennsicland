<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)" => "http://land.pennsicwar.org/",
);

nav_head( "Group History", $crumb );

nav_menu();

nav_right_begin();

if (! $user_id) {
  ?>
<h3>You aren't logged on.</h3>
  <?
} elseif (! $group_id) {
  ?>
<h3>You haven't yet selected a group.</h3>
  <?
} else {
  template_load("history.htm");
  template_param("landone_email",        $landone_email );
  print template_output();
  ?>
<table align="center" border="1" cellpadding="4">
  <tr>
    <td>Pennsic Year</td>
    <td>Calendar Year</td>
    <td>Location</td>
  </tr>
  <?
  $sql = "SELECT year, attendance, block_name
      FROM land_group_history
        LEFT JOIN land_blocks USING(block_id)
      WHERE group_id = '$group_id'
      ORDER BY year
  ";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  if (mysql_num_rows($query)) {
    $prev_pennsic = "";
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $class = (++$count % 2) ? "odd" : "even";
      $pennsic    = $result['year'];
      $year       = $pennsic + 1971;
      $location   = $result['block_name'];
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
      ?>
  <tr class='<?=$class?>'>
    <td><?=$pennsic?></td>
    <td><?=$year?></td>
    <td><?=$location?>&nbsp;(<?=$attendance?>)</td>
  </tr>
      <?
    } // next result

    if (($pennsic+1) != $pennsic_number) {
      for ($p = $pennsic+1; $p < $pennsic_number; $p++) {
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
  } else {
    ?>
  <tr><td colspan="3"><h2>Your group has no history</h2></td></tr>
    <?
  } // endif num_rows
  ?>
</table>
  <?
} // endif group_id

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>