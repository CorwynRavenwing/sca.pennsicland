<?
# Note: this page does NOT require logging on!

$jump_to = isset($_REQUEST['jump']) ? $_REQUEST['jump'] :
  (isset($_COOKIE['jump_to']) ? $_COOKIE['jump_to'] : "*NONE*");

setcookie("jump_to", $jump_to, time()+60*60*24);  /* expire in 1 day */

require_once("include/nav.php");
require_once("include/block.php");

$html_head_block = '<link rel=stylesheet href="css/mapmenu.css" type="text/css">';
$body_onload = "";

nav_start($html_head_block, $body_onload);

?>

<style type="text/css">
.block_label {
  clear:both;
  text-align:center;
  padding-top:1em;
  padding-bottom:0.5em;
}
</style>

<?
$block = @$_GET['block'];

if (! $block) {
  $crumb = array(
    "Zoning and Planning (Land)"    => "http://land.pennsicwar.org/",
  );
  $title = "Pennsic War Official Land Maps";
} else {
  $crumb = array(
    "Zoning and Planning (Land)"    => "http://land.pennsicwar.org/",
    "Pennsic War Official Land Maps"  => "map.php",        // REMOVE 1 WHEN PUBLISHING
  );
  $title = "Block $block";
}
//         <li><a href="../LAND_SYSTEM/land.cgi?linkcode=$jump_to&pg=return_from_map">Land Agent Menu</a></li>

nav_head($title, $crumb);
?>
<div id="mapmenu-container">
    <div id="mapmenu">
<?

  $blocks = block_list();

  $prev_initial = "";
  $initial_count = 0;
  foreach ($blocks as $b) {
    $initial = substr($b, 0, 2);  # was ,0,1

    if ( ($prev_initial != $initial) or !(++$count % 10) ) {
        if ($prev_initial != "") {
      ?>
    </ul>
      </li>
  </ul>

      <?
        }
        if ($prev_initial != $initial) {
      $initial_count = 0;
      $prev_initial = $initial;
      $count = 0;
        }
        $initial_count++;
        ?>
  <ul>
      <li><a><?=$initial?>0's</a>
    <ul>

        <?
    }

    // nav_leftnav_active($b,  "map.php", 1, "block=$b");
    ?>
        <li><a href="map.php?block=<?=$b?>"><?=$b?></a></li>
    <?
  } # next b
  ?>
    </ul>
      </li>
  </ul>

  <ul>
      <li><a>X's</a>
    <ul>

        <li><a>The X</a></li>
        <li><a>blocks</a></li>
        <li><a>are not</a></li>
        <li><a>mapped</a></li>

    </ul>
      </li>
  </ul>

  <?
?>

    </div>
</div>

<br/>
<br/>
<?
$current_date = date("Y-m-d");

  // nav_leftnav_end();

nav_right_begin();

global $pennsic_number;

if (! $block) {
  // overall map page
?>
  <b>Click on block names in the menu above.</b>
  <br />
  Clicking on map to zoom has been temporarily disabled.
  <br />
  <span style="color:green; font-weight:bold;">
    NEW: click <a href=<?=image("maps/pennsic${pennsic_number}_L.png");?> target="_blank">here</a>
    for a larger version of this map.
  </span>
  <br />
  <!-- form starts here -->
  <img
    src=<?=image("maps/pennsic${pennsic_number}.gif");?>
    alt="Pennsic <?=$pennsic_number?> Map"
    border="1"
  />
  <!-- form ends here -->
<?
} else {
  // single map page
?>
  <b>Click on block map to view PDF file,</b><br />
  or choose another block from the menu above.<br />

  <!-- <a href="maps/<?=$pennsic_number?>_<?=$block?>_L.pdf" > -->
  <a href=<?=image("maps/${pennsic_number}_${block}_L.pdf");?> >
     <img
      src=<?=image("maps/${pennsic_number}_${block}_S.png");?>
      alt="Pennsic <?=$pennsic_number?> Block <?=$block?> Map"
        border="1"
      />
    </a>
  <!-- was:
        height="1583"
        width ="1224"
  -->
<?
} // endif block

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>