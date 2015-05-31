<?
require_once("include/nav.php");
require_once("include/cooper.php");
require_once("include/group_history.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Block Details", $crumb );

nav_admin_menu();	// special Admin menu nav

require_once("include/javascript.php");

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	template_load("template_redgreen.html");
	print template_output();
	
	$block_id = @$_REQUEST['block_id'];
	if (! $block_id) {
		print "<h2>Error: no block_id passed</h2>\n";
		exit(0);
	} # endif param action
	
	$action		= @$_REQUEST['update'];
	$block_name	= @$_REQUEST['block_name'];
	$mark_sent	= @$_REQUEST['mark_sent'];
	
	  if ($action) {
	    if (! $w_admin) {
		print "<h2>Your access level does not allow this action.</h2>\n";
		$action = "";
	    } // endif w_admin
	  } // endif action

	if ($action) {
		# print "<h2>DEBUG: mark_sent = $mark_sent</h2>\n";
		# print "<h3>DEBUG: Checking for changes from block '$block_id' ...</h3>\n";
		
		# print("DEBUG: _POST[] = <pre>"); print_r($_POST); print("</pre>\n");
		
		$group_data = load_groups_by_final_location_in_match_order( $block_id );
		
		foreach ($group_data as $rec ) {
			$group_id			= $rec['group_id'];
			$group_name			= $rec['group_name'];
			
			# print "<h4>DEBUG: Checking for changes for group '$group_id' ...</h4>\n";
			
			$changes = array();
			
			#move if requested
			$keep_status = @$_POST[ "move_" . $group_id ];
			
			if ( $keep_status == 'move' ) {
				$new_block_location = @$_POST[ "moveto_" . $group_id ];
			
				if ( ! $new_block_location ) {
					$new_block_location = "Other";
				}
				
				if ($new_block_location != $block_name) {
					array_push( $changes, "moving to $new_block_location" );
				
					group_set_final_block_location( $group_id, $new_block_location )
						or die("error setting final block location");
				}
			} // endif move

			#update compression and bonus
			$compression	= @$_POST[ "calculatedcompression_" . $group_id ];
			$old_compression	= $rec['calculated_compression'];

			if ($compression != $old_compression) {
				array_push( $changes, "updating compression from $old_compression to $compression" );
				
				group_set_calculated_compression( $group_id, $compression )
					or die("error setting calculated compression");
			}
			
			$bonus		= @$_POST[ "bonus_" . $group_id ];
			$old_bonus	= $rec['bonus_footage'];
			
			if ($bonus != $old_bonus) {
				array_push( $changes, "updating bonus from $old_bonus to $bonus" );
				
				group_set_bonus_footage( $group_id, $bonus )
					or die("error setting bonus");
			}
			
			if ($mark_sent) {
			      if ( mark_sent($group_id) ) {
					array_push( $changes, "fixing used_space and has_changed" );
			      }
			}
   
			if ( count($changes) ) {
				print("<h4><font color='green'>Changes to $group_name: " . join("; ", $changes) . "</font></h4>\n");
			}
		} // next group_data
	
	} // endif action
	
	template_load("admin_decide_block.htm");
	template_param("admin_message",	 $ADM_MSG );
	
	# might not need these:
	$table_id = "grouptable";
	$table2_id = "warning-table";
	
	$table = "
		<table border='1' id='$table_id'>
			<tr>
				<td title='name of group (popup = land agent)'>
					Groupname (Agent)
				</td>
				<td title='count of completed preregistrations at coopers site.  SECOND SORT FIELD'>
					# of People
				</td>
				<td title='extra square feet granted by Land One choice'>
					Bonus
				</td>
				<td title='requested maximum % compression'>
					Req. Max. Comp.
				</td>
				<td title='current % compression chosen by Land One'>
					Current Comp.
				</td>
				<td title='calculated footage = # of people * 250 square feet'>
					Calc. Footage
				</td>
				<td title='magical Exact Footage value: overrides preregistration number'>
					Exact Footage
				</td>
				<td title='allocated footage = (exact or calc footage), compressed'>
					Alloc. Footage
				</td>
        <td title='Changed: has alloc footage changed since the last land agent letter?'>
          Ch?
        </td>
				<td title='years group has been in this block.  might not be sequential!  FIRST SORT FIELD'>
					Years In Block
				</td>
				<td title='years group has been in existence (as a Pennsic camp)'>
					Total Years
				</td>
				<td title='staff group flag'>
					Staff
				</td>
				<td title='reserved groups can use reserved land blocks'>
					Rsvd
				</td>
				<td title='choice 1'>
					Ch1
				</td>
				<td title='choice 2'>
					Ch2
				</td>
				<td title='choice 3'>
					Ch3
				</td>
				<td title='choice 4'>
					Ch4
				</td>
				<td title='check to allow moving this group'>
					Move
				</td>
				<td title='select where to move this group.  defaults to their next choice'>
					Move To
				</td>
			</tr>
	";

	# roll up used space from landgroups to blocks, BEFORE creating new block object!
	roll_up_used_space();
	
	$block_record = block_record( $block_id );

	# my $user = User->new();
	# $user->set_database_handle( $dbh );
	
	$block_options = block_list_options("", 1, 1);
	# $block_names_html_string = block_list_options();
	
	# substitute into form
	# was: $campable_feet = $block->get_campable_square_footage;
	$campable_feet = $block_record['campable_square_footage'];

	#generate top of page template ( block information )
	template_param("block_id",			$block_id );
	template_param("block_name",			$block_record['block_name'] );
	template_param("block_description",		$block_record['description'] );
	template_param("generate_neighbors",		$block_record['generate_neighbors'] );
	template_param("campable_square_footage",	$campable_feet );

	template_param("group_ids",			"'not set yet'" );
	template_param("group_prereg",			"'not set yet'" );
	template_param("exact_amount",			"'not set yet'" );

	#generate the list for the match
	$group_data = load_groups_by_final_location_in_match_order( $block_id );

	$group_ids = "";
	$pre_reg = "";
	$exact_amount = "";
	
	$group_id_list = array();
	$pre_reg_list = array();
	$exact_amount_list = array();
	
	$agent_ids = array();
	$agent_emails = array();
	
	$count = 0;
	
	foreach ($group_data as $rec ) {
		$count++;
	
		print("<!-- DEBUG group_data->hash:\n");
		foreach ($rec as $a => $b) {
			print("\t'$a' => '$b'\n");
		}
		print("-->\n");
		
		$group_id		= $rec['group_id'];
		$group_name		= $rec['group_name'];
		$prereg_count		= $rec['pre_registration_count'];
		$bonus_footage		= $rec['bonus_footage'];
		$exact_land_amount	= $rec['exact_land_amount'];
		$compression		= $rec['compression_percentage'];
		$calc_compression	= $rec['calculated_compression'];
		$calculated_feet	= $rec['used_space'];			# was $rec['calculated_square_footage'];
		$allocated_footage	= $rec['used_space'];			# was $rec['alloted_square_footage'];
		$used_space_save	= $rec['used_space_save'];
		$has_changed		= $rec['has_changed'];

		$people_change		= ($calculated_feet - $used_space_save) / 250;
		$people_change		= round( $people_change, 0 );
		if ($people_change > 0) {
			$people_change = "+" . $people_change;
		}

		$staff_group		= $rec['staff_group']		? "yes" : "no";
		$reserved_group		= $rec['reserved_group']	? "yes" : "no";
		
		$rep_name		= $rec['on_site_representative'];
		$agent_id		= $rec['user_id'];
		
		if ($agent_id) {
			array_push($agent_ids, $agent_id);
		}
		
		$agent_name = "";
		$agent_text = "";
		
		if ($agent_id) {
			$user_data	= user_record($agent_id);
			$sca_name	= $user_data['alias'];
			$real_name	= $user_data['legal_name'];
			$email		= $user_data['email_address'];
			
			$agent_text = "$sca_name ($real_name)";
			if ($email) {
				array_push($agent_emails, $email);
			}
		} else {
			$agent_text = "NONE";
		}
		
		$block_choice = array();
		
		$other_blockid = block_id("Other");
		
		$block_choice[1]	= $rec['first_block_choice'];
		$block_choice[2]	= $rec['second_block_choice'];
		$block_choice[3]	= $rec['third_block_choice'];
		$block_choice[4]	= $rec['fourth_block_choice'];
		$block_choice[5]	= $other_blockid;
		
		$skip_block = array();
		$block_free = array();
		$unique_blocks = $block_choice;
		for ($i=1;$i<=5;$i++) {
			$skip_block[$i] = 0;
			
			if (! $block_choice[$i] )	{ $block_choice[$i] = $other_blockid; }
			
			$block_free[$i] = block_free( $block_choice[$i] );
			
			for ($j=1; $j<=($i-1); $j++) {
				if ($block_choice[$i] == $block_choice[$j]) {
					$skip_block[$i] = 1;
					$unique_blocks[$i] = "SKIP";
				}
			} // next j
		} // next i
		
		$unique_blocks = array();
		foreach ( $unique_blocks as $b ) {
			if ( $b != "SKIP" ) {
				array_push($unique_blocks, $b);
			}
		}
		
		$next_block_choice = $other_blockid;
		
		for ($i=1; $i<=4; $i++) {
			if ($block_id == @$unique_blocks[$i]) {
				$next_block_choice = $unique_blocks[$i+1];
			}
		}
		
		$chosen_block = array();	# these will contain 0 0 1 0 flags for which choice is selected
		$next_block   = array();
		
		for ($i=1; $i<=4; $i++) {
			$chosen_block[$i] = 0;
			if ($block_id == $block_choice[$i] and ! $skip_block[$i]) {
				$chosen_block[$i] = 1;
			}
			$next_block[$i] = 0;
			if ($next_block_choice == $block_choice[$i] and ! $skip_block[$i]) {
				$next_block[$i] = 1;
			}
		} // next i
		
		for ($i=1; $i<=4; $i++) {
			# print "DEBUG: block_choice[$i]: " . $block_choice[$i] ;
			$block_choice[$i] = block_name( $block_choice[$i], "Other" );
			# print " -> " . $block_choice[$i] . " :DEBUG<br/>\n";
		}
		
		$next_block_choice   = block_name( $next_block_choice, "Other" );
		
		# we do this above someplace, do we need to do it again?
		# but here we do pass in the selected string, so maybe we do [Corwyn 2009]
		$block_options = block_list_options( $next_block_choice , 1, 1);
		
		$prereg_link = (
			$prereg_count ?
			"<a href='admin_prereg.php?id=$group_id' target='_blank'>$prereg_count</a>" :
			$prereg_count
		);
		
		$has_changed_span = ($has_changed ?
			"<span class='changed' title='(was $used_space_save)'><font size='+1'>&nbsp;<b>$people_change</b>&nbsp;</font></span>"
			: "no"
		);
		
		$table .= "
	<tr id='$group_id'>
		<td><a href='admin_groups.php?id=$group_id' target='_blank'>$group_name</a> <a href='admin_users.php?id=$agent_id' title='online agent: $agent_text / onsite: $rep_name' target='_blank'>(*)</a></td>
		<td>$prereg_link</td>
		<td>
			<input type=text id='bonus_$group_id' name='bonus_$group_id' value='$bonus_footage' size='6' 
				onFocus='calculate_footage();' onBlur='calculate_footage();' onChange='calculate_footage();'>
		</td>
		<td><span id='compression_max_$group_id'>$compression</span></td>
		<td>
			<input
				type=text
				id='calculatedcompression_$group_id'
				name='calculatedcompression_$group_id' 
				value='$calc_compression' size='2'
				onBlur='calculate_footage();'
				onChange='calculate_footage();'
			/>
		</td>
		<td>$calculated_feet</td>
		<td>$exact_land_amount</td>
		<td>
			<input
				type=text
				id='allocatedsquarefootage_$group_id'
				name='allocatedsquarefootage_$group_id'
				value='$allocated_footage' size='6'
				onChange='calculate_footage();'
			/>
			<!-- was:		onBlur='calculate_footage();' -->
			<!-- or:		disabled='disabled' -->
			<!-- COULD BE: span -->
		</td>
		<td>
			<input
				type='hidden'
				id='changed_$group_id'
				name='changed_$group_id'
				value='$has_changed'
			/>
			$has_changed_span
		</td>
		<td>" . get_years_in_block_count($group_id, $block_id) . "</td>
		<td>" . get_total_years_in_system($group_id) . "</td>
		<td>$staff_group</td>
		<td>$reserved_group</td>
		<td>
			" . blockchoice_magic(
				$block_choice[1],
				$chosen_block[1],
				$next_block[1],
				$skip_block[1],
				$block_free[1],
				$allocated_footage
			) . "
		</td>
		<td>
			" . blockchoice_magic(
				$block_choice[2],
				$chosen_block[2],
				$next_block[2],
				$skip_block[2],
				$block_free[2],
				$allocated_footage
			) . "
		</td>
		<td>
			" . blockchoice_magic(
				$block_choice[3],
				$chosen_block[3],
				$next_block[3],
				$skip_block[3],
				$block_free[3],
				$allocated_footage
			) . "
		</td>
		<td>
			" . blockchoice_magic(
				$block_choice[4],
				$chosen_block[4],
				$next_block[4],
				$skip_block[4],
				$block_free[4],
				$allocated_footage
			) . "
		</td>
		<td>
			<INPUT
				TYPE=CHECKBOX
				NAME=move_$group_id
				VALUE=move
				onClick='enable_if_checked(this, $group_id)'
				onChange='calculate_groups_in_block();'
			/>
		</td>
		<td>
			<select id='moveto_$group_id' name='moveto_$group_id' size=1> 
				$block_options
			</select>
		</td>

	</tr>
		";
		
		array_push($group_id_list,	"'" . $group_id			. "'");
		array_push($pre_reg_list,	"'" . $prereg_count		. "'");
		array_push($exact_amount_list,	"'" . $exact_land_amount	. "'");
	} // next group_data
	
	if( $count == 0 )
	{
		$table .= "
	<tr>
		<td colspan='20' align='center'>
			<font size='+2'><b>No Groups in this Block</b></font>
		</td>
	</tr>
		";
	} else {
		$table .= "
	<tr>
		<td colspan='20' align='center'>
			<font size='+2'><b>Total of $count Groups in this Block</b></font>
		</td>
	</tr>
	<tr id='master'>
		<td><b>CHANGE&nbsp;ALL&nbsp;GROUPS:</b></td>
		<td>BONUS:</td>
		<td>
			<input
				type=text
				name='bonus_all'
				name='bonus_all'
				value=''
				size=6
				onChange='set_all_bonus(this);'
			/>
		</td>
		<td>COMPR:</td>
		<td>
			<input
				type=text
				id='compression_all'
				name='compression_all'
				value=''
				size=2
				onChange='set_all_compression(this);'
			/>
		</td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>
			<INPUT
				TYPE=CHECKBOX
				id='move_all'
				name='move_all'
				VALUE=master
		" . ($w_admin ? "
				onChange='check_move_all(this);'
	    	" : "
				onChange='return false;'
				disabled='disabled'
	    	" ) . "
			/>
		</td>
		<td>:MOVE</td>
	</tr>
		";
	} # endif count
	
	$table .= "
</table>
	";
	
	template_param("table",	 $table );
	
	print template_output();
	
	$count_mailmerge = count_where("mailmerge_recipients", "block_id = '$block_id'");
	
	if ($count_mailmerge) {
		?>
<h4 style="color:green">
	View the
	<a href="admin_email_history.php?block_id=<?=$block_id?>" target="_blank"><?=$count_mailmerge?> email merges</a>
	that have (ever) been sent to this block.
</h4>
		<?
	} else {
		?>
<h4 style="color:red">
	(no mail merges have ever been sent to this block)
</h4>
		<?
	}
	
	$group_ids	= join(",", $group_id_list	);
	$pre_reg	= join(",", $pre_reg_list	);
	$exact_amount	= join(",", $exact_amount_list	);
	?>
	<script script type="text/javascript" language="javascript">
		groupIdArray = new Array( <?=$group_ids?>	);
		groupPreReg  = new Array( <?=$pre_reg?>		);
		exactAmount  = new Array( <?=$exact_amount?>	);
		
		// now that body is loaded, call old body onload= code:
		calculate_footage();
	</script>
	
	<?
	$pattern = "/^[A-Za-z0-9@._-]*$/";
	$agent_emails_good = array();
	foreach ($agent_emails as $e) {
		if ( preg_match( $pattern, $e) ) {
			array_push($agent_emails_good, $e);
		}
	}
	$agent_emails = join(",", $agent_emails_good);
	
	javascript_replace_text("email_href", "<a href='mailto:$agent_emails'>here</a>" );
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
