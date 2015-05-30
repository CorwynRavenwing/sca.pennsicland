<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

// nav_head( "Block List", $crumb );

// nav_admin_menu();	// special Admin menu nav

// nav_admin_leftnav();	// no left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	# template_load("template_redgreen.html");
	# print template_output();
	
	# roll up used space from landgroups to blocks, BEFORE creating new block object!
	roll_up_used_space();
	
	$all_blocks = get_block_ids_ordered_by_block_name();
	
	$columns = 0;
	?>
<h2>Block Completion Checklist</h2>

<table border='1'>
	<?
	# print "Reading list of blocks: ";
	$prev_initial = "";
	
	foreach ($all_blocks as $block_id => $block_name) {
		$count++;
		
		# $block_name    = block_name($block_id);
	
		$chars = str_split($block_name);
		$initial = strtoupper( array_shift( $chars ) );
		
		// blocks with these initials are completely skipped:
		if ($inital == "D") { break; }
		if ($inital == "O") { break; }
		
		if ($prev_initial != $initial) {
			if ($prev_initial != "") {
				?>
				<tr>
					<td align='center'>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td align='center'>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td align='center'>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td align='center'>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td align='center'>
						&nbsp;
					</td>
				</tr>
			</table>
		</td>
				<?
			} // endif prev_initial blank
			
			$prev_initial = $initial;
			$columns++;
			?>
		<td valign='top'>
			<table border='1' bordercolor='red' style='margin:1em'>
				<tr>
					<td align='center'>
						<font size='+2'><b>&nbsp;-&nbsp;<?=$initial?>&nbsp;-&nbsp;</b></font>
					</td>
				</tr>
			<?
		} // endif initial
		
		list($total_footage,$used_footage,$has_changed,$description)
			= block_data( $block_id );
		
		if ($total_footage <= 1) { break; }
		if ($used_footage  <= 1) { break; }
		?>
				<tr>
					<td align='center'>
						<?=$block_name?>
					</td>
				</tr>
		<?
	} // next block_id

	?>
			</table>
		</td>
	</tr>
	<?
	
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
			<font size='+2'><b>Total of <?=$count?> Blocks</b></font>
		</td>
	</tr>
		<?
	} // endif count 0
	?>
</table>
	<?
} // endif admin

nav_right_end();

# nav_footer_panix();
# nav_footer_disclaimer();

nav_end();
?>
