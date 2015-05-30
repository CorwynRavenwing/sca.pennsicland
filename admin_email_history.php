<?
require_once("include/nav.php");
require_once("include/mail_merge.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "Email History", $crumb );

nav_admin_menu();	// special Admin menu nav

require_once("include/javascript.php");		// required for template admin_mail_merge_groups

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

$maxlen = 20;

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {

  $my_merge_id	= @$_REQUEST['merge_id'];
  $my_group_id	= @$_REQUEST['group_id'];
  $my_block_id	= @$_REQUEST['block_id'];
  $my_details	= @$_REQUEST['details'];
  
  /*
  # optionally show Help
  javascript_hidable_div_begin("Directions");
	  template_load("admin_mail_merge_help.htm");	// SHOULD BE A DIFFERENT FILE HERE [CORWYN P41]
	  // no template_param() values
	  print( template_output() );
  javascript_hidable_div_end();
  */
  
  print("<h1>Email Merge History</h1>\n");
  
  $where_clause_array = array();
  
  $allow_details = 0;
  if ($my_merge_id) {
	
	$display_merge_id = $my_merge_id;
	if ($my_merge_id) {
		$display_merge_id = "<a href='admin_email.php?merge_id=$my_merge_id'>$display_merge_id</a>";
	}
	
	print( "<h2>mail merge #$display_merge_id   (<a href='?group_id=$my_group_id&block_id=$my_block_id'>clear</a>)</h2>\n" );

	array_push($where_clause_array, "mailmerge_id = '$my_merge_id'");
	$allow_details++;
  }
  if ($my_group_id) {
	$my_group_name = group_name($my_group_id);
	$display_group_name = $my_group_name;
	if ($my_group_id) {
		$display_group_name = "<a href='admin_groups.php?id=$my_group_id'>$display_group_name</a>";
	}
	
	print( "<h2>group: $display_group_name   (<a href='?merge_id=$my_merge_id&block_id=$my_block_id'>clear</a>)</h2>\n" );

	array_push($where_clause_array, "group_id = '$my_group_id'");
	$allow_details++;
  }
  if ($my_block_id) {
	$my_block_name = block_name($my_block_id);
	$display_block_name = $my_block_name;
	if ($my_block_id) {
		$display_block_name = "<a href='admin_block_detail.php?block_id=$my_block_id'>$display_block_name</a>";
	}
	
	print( "<h2>block: $display_block_name   (<a href='?merge_id=$my_merge_id&group_id=$my_group_id'>clear</a>)</h2>\n" );

	array_push($where_clause_array, "block_id = '$my_block_id'");
	$allow_details++;
  }
  if ($my_details) {
	print( "<h2>DETAILS   (<a href='?merge_id=$my_merge_id&group_id=$my_group_id&block_id=$my_block_id'>clear</a>)</h2>\n" );
	
	if (! $allow_details) {
		print( "<h3>No query ... cancelling details</h3>\n" );
		$my_details = 0;
	}
  }
  
  $where_clause = join(" AND ", $where_clause_array);
  
  $query = query_mailmerge_details($where_clause);
  
  $rows = mysql_num_rows($query);
  
  if ( ($rows == 1) or ($my_details) ) {
  
	?>
<table border='1'>
	<?
	while ($result = mysql_fetch_assoc($query)) {
	    $q_merge_id			= $result['mailmerge_id'];
	    $display_from		= $result['from_email'];
	    $display_subject		= $result['letter_subject'];
	    $display_body		= $result['letter_body'];
	    $display_to			= $result['email_address'];
	    $display_name		= $result['group_name'];
	    $q_block_id			= $result['block_id'];
	    
	    $display_body = str_replace("\r\n", "\n",           $display_body);
	    $display_body = str_replace("\n\n", "\n",           $display_body);
	    $display_body = str_replace("\n",   "<br/>\n",	$display_body);
	    ?>
	<tr>
	    <td style='font-weight:bold'>From:</td>		<td>"Land Admin" (<?=$display_from?>)</td>
	</tr>
	<tr>
	    <td style='font-weight:bold'>To:</td>		<td>"<?=$display_name?>" (<?=$display_to?>)</td>
	</tr>
	<tr>
	    <td style='font-weight:bold'>Subject:</td>		<td><?=$display_subject?></td>
	</tr>
	<tr>
	    <td style='font-weight:bold'>X-MailMerge:</td>	<td><?=$q_merge_id?></td>
	</tr>
	    <? if ($q_block_id) { ?>
	<tr>
	    <td style='font-weight:bold'>X-Block:</td>		<td><?=block_name($q_block_id)?></td>
	</tr>
	    <? } // endif block_id ?>
	<tr>
	    <td colspan='2'><?=$display_body?></td>
	</tr>
	    <?
	} // next result
	?>
</table>
	<?
  } elseif ($rows == 0) {
	
	print("<h2>No such email found.</h2>\n");
  
  } else {
	
	?>
<table border='1'>
	<tr style="font-weight:bold; background-color:silver: text-align:center">
	    <td>ID</td>
	    <td>From Email</td>
	    <td><b>Subject</b><br/>Email Body</td>
	    <td>Block</td>
	    <td>Group</td>
	</tr>
	<?
	$none = "<b><font color='red'>(NONE)</font></b>";
	
	while ($result = mysql_fetch_assoc($query)) {
	    $q_merge_id			= $result['mailmerge_id'];
	    
	    if ($q_merge_id == $my_merge_id) {
		$merge_text = $q_merge_id;
	    } else {
		$merge_text = "<a href='?merge_id=$q_merge_id&group_id=$my_group_id&block_id=$my_block_id'>$q_merge_id</a>";
	    }
	    $display_from		= $result['from_email'];
	    $display_subject		= $result['letter_subject'];
	    $display_body		= $result['letter_body'];
	    $display_to			= $result['email_address'];
	    $q_group_id			= $result['group_id'];
	    $display_name		= $result['group_name'];
	    $q_block_id			= $result['block_id'];
	    
	    if (! $display_from)	{ $display_from	   = $none;		}
	    if (! $display_subject)	{ $display_subject = "Subject: $none";	}
	    if (! $display_body)	{ $display_body	   = "Message: $none";	}
	    
	    $display_body    = substr($display_body, 0, $maxlen) . "...";
	    
	    $my_block_name = block_name($q_block_id);
	    
	    if ($q_block_id == $my_block_id) {
	        $block_text = $my_block_name;
	    } elseif (! $q_block_id) {
		$block_text = "--";
	    } else {
		$block_text = "<a href='?merge_id=$my_merge_id&group_id=$my_group_id&block_id=$q_block_id'>$my_block_name</a>";
	    }
	    
	    if ($q_group_id == $my_group_id) {
		$groups_text = $display_name;
	    } elseif (! $q_group_id) {
		$groups_text = $display_name;
	    } else {
		$groups_text = "<a href='?merge_id=$my_merge_id&group_id=$q_group_id&block_id=$my_block_id'>$display_name</a>";
	    }
	    ?>
	<tr>
		<td><?=$merge_text?></td>
		<td><?=$display_from?></td>
		<td>
			<b><?=$display_subject?></b><br />
			<?=$display_body?>
		</td>
		<td style="text-align:center"><?=$block_text?></td>
		<td><?=$groups_text?></td>
	</tr>
	    <?
	} // next result
	
	$details_href = "?merge_id=$my_merge_id&group_id=$my_group_id&block_id=$my_block_id&details=1";
	?>
	<tr>
		<td colspan='5' style="font-weight:bold; text-align:center">
			<?=$rows?> Total Letters
			<? if ($allow_details) { ?>
			(<a href='<?=$details_href?>'>details</a>)
			<? } // endif $allow_details ?>
		</td>
	</tr>
</table>
	<?
  }
  
  
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
