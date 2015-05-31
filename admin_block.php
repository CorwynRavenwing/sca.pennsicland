<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Make Block Decisions", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  template_load("template_redgreen.html");
  print template_output();

  $action = @$_POST['action'];

  if ($action) {
    if (! $w_admin) {
	print "<h2>Your access level does not allow this action.</h2>\n";
	$action = "";
    } // endif w_admin
  } // endif action
  
  if ($action == "clear") {
    print "<h2>Clearing all 'changed' flags ...<br />";
    mark_everybody_sent();
    print "Done.<br /></h2>\n";
  } # endif param action

  if ( ! allow_groupmoves() ) {
    ?>
  <div style="font-size:1.10em; color:red; margin-bottom:1em;">
    Please bear in mind that the "allow_groupmoves" flag is not set because we have not yet pushed
    the Big Red Button (locking groups into their block choices).  Groups will probably not appear
    anywhere on this list until that happens.  I would not suggest making changes to group locations
    using this tool, until you have pressed the Big Red Button.<br/>
    [Corwyn Ravenwing P41]
  </div>
    <?
  } // endif allow_groupmoves

  $show_people = @$_GET['show_people'];

  if ($show_people) {
    $showing      = "people";
    $other_text   = "sqft";
    $other_value  = 0;
  } else {
    $showing      = "sqft";
    $other_text   = "people";
    $other_value  = 1;
  } // endif show_people
  ?>
<h4>Showing <?=$showing?> (switch to <a href="?show_people=<?=$other_value?>"><?=$other_text?></a>)</h4>
  <?

  $columns = 0;
  ?>
<table border='1' id='$table_id'>
  <tr style='font-weight:bold' >
    <td align='center'>Block<br />Name</td>        <? $columns++; ?>
    <td align='center'>Campable<br /><?=$showing?></td>    <? $columns++; ?>
    <td align='center'>Used<br /><?=$showing?></td>      <? $columns++; ?>
    <td align='center'>Free<br /><?=$showing?></td>      <? $columns++; ?>
          <td align='center' title='Number of groups with changed sizes'>Ch?</td>
                    <? $columns++; ?>
    <td align='center'>Block Description</td>      <? $columns++; ?>
  </tr>
  <?
  # roll up used space from landgroups to blocks, BEFORE creating new block object!
  roll_up_used_space();

  $all_blocks = get_block_ids_ordered_by_block_name();

  global $feet_per_person;

  $feet_per_person = 250;  # should be set in LandGroup include

  $feet = $feet_per_person;

  # print "Reading list of blocks: ";
  $prev_initial = "";
  $count = 0;
  $count_crowded    = 0;
  $count_zero       = 0;
  $count_roomy      = 0;
  $sum_footage      = 0;
  $sum_people       = 0;
  $sum_free_footage = 0;
  $sum_free_people  = 0;

  foreach ($all_blocks as $block_id => $block_name) {
    $count++;

    # $block_name    = block_name($block_id);

    $chars = str_split($block_name);
    $initial = strtoupper( array_shift( $chars ) );
    if ($prev_initial != $initial) {
      $prev_initial = $initial;
      ?>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+2'><b> - <?=$initial?> - </b></font>
    </td>
  </tr>
      <?
    } // endif initial

    list($total_footage,$used_footage,$has_changed,$description)
      = block_data( $block_id );

    # calculate capacity for each block
    $total_people  = round($total_footage / $feet, 0);

    $used_people  = round($used_footage / $feet, 0);

    $free_footage  = $total_footage - $used_footage;
    $free_people   = round($free_footage / $feet, 0);

    # $has_changed   = $block->{block_id_to_changed}{ $block_id };
    # $description   = $block->{block_id_to_description}{ $block_id };

    #color code the table element with the block name
    $class = ($used_footage == 0) ? 'zero' : ( ($free_footage < 0) ? 'crowded' : 'roomy' );

    ($used_footage == 0) ? $count_zero++ : ( ($free_footage < 0) ? $count_crowded++ : $count_roomy++ );

    if ($total_footage == "99999999" or $total_footage == "2147483647") {
        $total_footage = "no limit";
        $total_people  = "OK";

        $free_footage  = "no limit";
        $free_people   = "OK";
    } else {
        $sum_free_footage += $free_footage;
        $sum_free_people  += $free_people;
    }

    # add used people and footage whether we are in an unlimited block or not:
    $sum_footage      += $used_footage;
    $sum_people       += $used_people;
    ?>
  <tr class="<?=$class?>">
    <td align='center'>
      <a href="admin_block_detail.php?block_id=<?=$block_id?>" target="_blank" >
        <?=$block_name?>
      </a>
    </td>
    <td align='center'><?=($show_people  ? $total_people : $total_footage)?></td>
    <td align='center'><?=($show_people  ? $used_people  : $used_footage )?></td>
    <td align='center'><?=($show_people  ? $free_people  : $free_footage )?></td>
    <td align='center'><?=( $has_changed ?
  "<span class='changed'>&nbsp;&nbsp;<b>$has_changed</b>&nbsp;&nbsp;</span>"
  : "no")?>
    </td>
    <td align='center' nowrap="nowrap"><?=$description?></td>
  </tr>
    <?
  } // next block_id

  if( $count == 0 )
  {
    ?>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+2'><b>No Blocks</b></font>
    </td>
  </tr>
    <?
  } else {
    ?>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+2'>
        <b>
          Total of <?=$count?> Blocks
        </b>
        <br/>
        containing <?=$sum_people?> people (<?=$sum_footage?> sqft)
        <br/>
        with room for <?=$sum_free_people?> more (<?=$sum_free_footage?> sqft)
      </font>
      <br/>
      (free space in the unlimited blocks is not counted)
    </td>
  </tr>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+1' class='roomy'><b><?=$count_roomy?> blocks with extra room</b></font>
    </td>
  </tr>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+1' class='zero'><b><?=$count_zero?> blocks with no people</b></font>
    </td>
  </tr>
  <tr>
    <td align='center' colspan="<?=$columns?>">
      <font size='+1' class='crowded'><b><?=$count_crowded?> blocks that are overcrowded</b></font>
    </td>
  </tr>
    <?
  } // endif count 0
  ?>
</table>
<br />
<table border='1' id='key' width='33%'>
  <tr><td align='center' bgcolor='silver'><b>Color Key</b></td></tr>
  <tr><td align='center' class='crowded'>Crowded</td></tr>
  <tr><td align='center' class='zero'>Zero</td></tr>
  <tr><td align='center' class='roomy'>Roomy</td></tr>
  <tr><td align='center' class='changed'>Changed (<a href='?action=clear'>CLEAR&nbsp;FLAGS</a>)</td></tr>
  <tr><td align='center' class='kingdom'>Kingdom</td></tr>
</table>
<br />
<h4>Click here for the <a href="admin_block_list.php" target="_blank">Block Completion Checklist</a>.</h4>
  <?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>