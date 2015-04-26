<?
require_once("include/nav.php");
require_once("include/connect.php");

nav_start();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
);

$title = "View Groups";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

$sql = "SELECT Group_Name, Block_Name 
		FROM land_groups
			LEFT JOIN land_blocks
				ON land_groups.final_block_location = land_blocks.block_id
		WHERE land_groups.User_ID != 0
		ORDER BY Group_Name";

print "<!-- group_list SQL:\n$sql\n-->\n";

$query = mysql_query($sql)
	or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);

$num = mysql_num_rows($query);
?>

<h2>Registered Groups</h2>

<center>

<table width="100%">
	<tr>
		<td valign="top">

	<table width="100%">
<?
$groups = 0;
$prev_initial = "";
$second_column = 0;
while ($result = mysql_fetch_assoc($query)) {
	$group_name = $result['Group_Name'];
	$initial = strtoupper( substr($group_name, 0, 1) );
	if ($prev_initial != $initial) {
		if ((!$second_column) and ($groups >= $num/2)) {
			$second_column++;
			?>
	</table>

		</td>
<!-- START NEXT COLUMN -->
		<td valign="top">

	<table width="100%">
			<?
		}
		$prev_initial = $initial;
		?>
	<tr>
	    <td colspan="2" align="center">
			<span style="font-size:large; font-weight:bold">&nbsp;-&nbsp;<?=$initial?>&nbsp;-&nbsp;</span>
			<!-- <?=$groups?> <?=($num/2)?> -->
		</td>
	</tr>
		<?
	} // endif prev_initial
	$groups++;
	?>
	<tr>
	    <td align="left">
	        <?=$group_name?>
	    </td>
	    <td>
	        &nbsp;
			<? /* =$result['Block_Name'] */ ?>
	    </td>
	</tr>
	<?
} // next result
?>
	</table>

		</td>
	</tr>
</table>

</center>

<h2>Total of <?=$groups?> registered groups.</h2>

<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
<!--


	$filename = "view_groups_by_alpha.html";
	
	set_template_globals();

	$template->param( registered_groups => $grouplist  );
	
	$page = $template->output();

# 	open( OUT, ">$filename" ) or die $!;

	print $page;

# 	close( OUT );

# 	my $mode = 0755;	
# 	chmod( $mode, $filename );

#print "SUCCESS";

sub safe_template_param($;$;$)
{
        # set a value if it exists, but don't die if it doesn't
        
        my($template, $label, $value) = @_;

        if ( $template->query(name => $label) ) {
                $template->param($label => $value);
        } else {
        }
}

sub set_template_globals()
{
        # set variables that are global to all templates
        safe_template_param( $template, 'current_year'    , $current_year    );
        safe_template_param( $template, 'pennsic_number'  , $pennsic_number  );
        safe_template_param( $template, 'pennsic_roman'   , $pennsic_roman   );
        safe_template_param( $template, 'webmaster_name'  , $webmaster_name  );
        safe_template_param( $template, 'webmaster_email' , $webmaster_email );
        safe_template_param( $template, 'landone_email'   , $landone_email   );
}
-->
