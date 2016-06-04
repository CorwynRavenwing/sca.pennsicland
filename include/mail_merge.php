<?
require_once("include/land_email.php");
require_once("include/cooper.php");

$left_delimiter	 = "<[";
$right_delimiter = "]>";

function admin_mail_merge_send($merge_id) {
	# no template
	
	print("<h1>Sending mail-merge id $merge_id</h1>");
	
	print "<h2>(1/4) rolling up used-space again</h2>\n";
	roll_up_used_space();
	
	$create_count = create_letters($merge_id);
	print "<h2>(2/4) Created letters: Updated $create_count fields</h2>\n";
	
	$update_count = merge_letters_NEW($merge_id);
	print "<h2>(3/4) Merged letters: Updated $update_count fields</h2>\n";
	
	$send_count = send_letters_NEW($merge_id);
	print "<h2>(4/4) Sending: Sent $send_count emails</h2>\n";
	
	print("<h1>DONE.  Sent mail-merge id $merge_id</h1>");
	
	return;
} // end function admin_mail_merge_send

function create_letters($merge_id) {
	print("stub create_letters()<br/>\n");
	
	$changes = 0;
	
	print("<h4>changed");
	
	$sql = "UPDATE mailmerge_recipients AS mr
				LEFT JOIN mailmerge AS mm USING(mailmerge_id)
				SET mr.letter_subject = mm.letter_subject
			WHERE selected = '1'
				AND mr.mailmerge_id = '$merge_id'
				AND mr.letter_subject = '' ";
	
	print "<!-- create_letters sql 1:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	print(" subj: " . mysql_affected_rows() . ";");
	$changes += mysql_affected_rows();
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	$sql = "UPDATE mailmerge_recipients AS mr
				LEFT JOIN mailmerge AS mm USING(mailmerge_id)
			SET mr.letter_body    = mm.letter_body
			WHERE selected = '1'
				AND mr.mailmerge_id = '$merge_id'
				AND mr.letter_body = '' ";
	
	print "<!-- create_letters sql 2:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	print(" body:" . mysql_affected_rows() . ";");
	$changes += mysql_affected_rows();
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	$sql = "UPDATE mailmerge_recipients AS mr
				LEFT JOIN mailmerge AS mm USING(mailmerge_id)
			SET mr.from_email     = mm.from_email
			WHERE selected = '1'
				AND mr.mailmerge_id = '$merge_id'
				AND mr.from_email = '' ";
	
	print "<!-- create_letters sql 3:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	print(" from email:" . mysql_affected_rows() . ";" );
	$changes += mysql_affected_rows();
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	$field = "email_address";
	# print "<!-- Field $field -->\n";
	
	$merge_field = get_substitution_by_variable( $field );
	
	$sql = "UPDATE mailmerge_recipients AS mr
			SET mr.email_address     = '$merge_field'
			WHERE mr.mailmerge_id = '$merge_id'
				AND mr.email_address = '' ";
	
	print "<!-- create_letters sql 4:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	print(" to email:" . mysql_affected_rows() . ";" );
	$changes += mysql_affected_rows();
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	# if (! $changes) { print("(nothing)"); }
	print("</h4>\n");
	
	return $changes;
} // end function create_letters

function add_merge_variables($merge_id) {
	return "(deprecated)";
} // end function add_merge_variables

/*
# old version that uselessly stores this in the database, only to pull it back out again [Corwyn P39]
function get_merge_variables_list_OLD($merge_id) {
} // end function get_merge_variables_list
*/

$group_mail_merge_methods = array(
	'pre_registration_count',
	'group_name',
	'compression_percentage',
	'used_space',
);

$user_mail_merge_methods = array(
	'user_name',
	'legal_name',
	'alias',
	'street_1',
	'street_2',
	'state',
	'country',
	'city',
	'postal_code',
	'email_address',
	'phone_number',
	'extension',
);

$block_mail_merge_methods = array(
	'block_name',
	'free_space',
	'generate_neighbors',
	'map_link',
	'auth_link',
	'gasline_link',
);

# new version which doesn't use the database
function get_merge_variables_list($merge_id="merge id is actually irrelevant [Corwyn P29]") {
	global $group_mail_merge_methods;
	global $user_mail_merge_methods;
	global $block_mail_merge_methods;
	
	$variables = array();
	
	foreach ($group_mail_merge_methods as $i) {
		$variables{$i} = "group";
	}
	
	foreach ($user_mail_merge_methods as $i) {
		$variables{$i} = "user";
	}
	
	foreach ($block_mail_merge_methods as $i) {
		$variables{$i} = "block";
	}
	
	# print("<pre>"); print_r($variables); print("</pre>\n");
	
	return $variables;
} // end function get_merge_variables_list

function get_substitution_by_variable($input) {
	global $left_delimiter, $right_delimiter;
	
	$variable = $left_delimiter . $input . $right_delimiter;
	
	return $variable;
} // end function get_substitution_by_variable

function count_letters_needing_merged($merge_id) {
	$test_var = "%";
	$test_field = get_substitution_by_variable( $test_var );
	
	$sql = "SELECT count(*) as num 
			FROM mailmerge_recipients
			WHERE mailmerge_id = '$merge_id'
			AND (
				(letter_subject LIKE '%$test_field%')
			   OR (letter_body LIKE '%$test_field%')
			   OR (email_address LIKE '%$test_field%')
			) ";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	$count = 0;
	if ( $result = mysql_fetch_assoc($query) ) {
			$count = $result['num'];
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	print("<h3>found $count letters needing to be merged</h3>\n");
	return 	$count;
} // end function count_letters_needing_merged

function merge_letters_NEW($merge_id) {
	print("stub merge_letters_NEW()<br/>\n");
	
	// DEBUG: data is fine now

	if (! count_letters_needing_merged($merge_id) ) {
		return;
	} // endif
	
	// DEBUG: data is fine now

	$merge_variables = get_merge_variables_list($merge_id);
	
	$changes = 0;
	
	$field = "email_address";
	# print "<!-- Field $field -->\n";
	print "<h4>Field $field: ";
	
	$merge_field = get_substitution_by_variable( $field );
	
	$sql = "UPDATE mailmerge_recipients AS mr
			LEFT JOIN user_information AS d ON(mr.user_id = d.user_id)
		SET mr.email_address  = Replace(mr.email_address,'$merge_field', IFNULL(d.$field, '[$field]') )
		WHERE mr.mailmerge_id = '$merge_id'
			AND ( mr.email_address like '%$merge_field%' )";
	print "<!-- merge_letters sql 0:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

	# $changes += $self->check_error( $sbh0, "user" );
	print(mysql_affected_rows() . " emails ");
	$changes += mysql_affected_rows();
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	print "</h4>\n";
	
	// DEBUG: data is fine now

	$variable_count = 0;
	foreach ( $merge_variables as $field => $location ) {
		$variable_count++;
		# print "<!-- Field $field -->\n";
		print "<h4>Field $field: ";
		
		$merge_field = get_substitution_by_variable( $field );
		
	  if ($location == "user") {
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN user_information AS d ON(mr.user_id = d.user_id)
				SET mr.letter_body    = Replace(mr.letter_body,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE mr.mailmerge_id = '$merge_id'
					AND ( mr.letter_body like '%$merge_field%' )";
		print "<!-- merge_letters sql 1:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
		
		# $changes += $self->check_error( $sbh1, "user" );
		print(mysql_affected_rows() . " bodys/");
		$changes += mysql_affected_rows();

		// SHOULD RELEASE $query HERE [Corwyn P41]
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN user_information AS d ON(mr.user_id = d.user_id)
				SET mr.letter_subject = Replace(mr.letter_subject,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE mr.mailmerge_id = '$merge_id'
					AND ( mr.letter_subject like '%$merge_field%' )";
		print "<!-- merge_letters sql 1a:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
			
		# $changes += $self->check_error( $sbh1a, "user" );
		print(mysql_affected_rows() . " subjects ");
		$changes += mysql_affected_rows();
		
		// SHOULD RELEASE $query HERE [Corwyn P41]
	  } elseif ($location == "group") {
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN land_groups AS d ON(mr.group_id = d.group_id)
				SET mr.letter_body    = Replace(mr.letter_body,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE mr.mailmerge_id = '$merge_id'
					AND ( mr.letter_body like '%$merge_field%' )";
		print "<!-- merge_letters sql 2:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
			
		# $changes += $self->check_error( $sbh2, "group" );
		print(mysql_affected_rows() . " bodys/");
		$changes += mysql_affected_rows();

		// SHOULD RELEASE $query HERE [Corwyn P41]
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN land_groups AS d ON(mr.group_id = d.group_id)
				SET mr.letter_subject = Replace(mr.letter_subject,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE mr.mailmerge_id = '$merge_id'
					AND ( mr.letter_subject like '%$merge_field%' )";
		print "<!-- merge_letters sql 2a:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
		
		# $changes += $self->check_error( $sbh2a, "group" );
		print(mysql_affected_rows() . " subjects ");
		$changes += mysql_affected_rows();
		
		// SHOULD RELEASE $query HERE [Corwyn P41]
	  } elseif ($location == "block") {
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN land_blocks AS d ON(mr.block_id = d.block_id)
				SET mr.letter_body    = Replace(mr.letter_body,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE ( mr.mailmerge_id = '$merge_id' )
					AND ( mr.letter_body like '%$merge_field%' )
			";
		print "<!-- merge_letters sql 3:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
			
		# $changes += $self->check_error( $sbh3, "block" );
		print(mysql_affected_rows() . " bodys/");
		$changes += mysql_affected_rows();
		
		// SHOULD RELEASE $query HERE [Corwyn P41]
		
		$sql = "UPDATE mailmerge_recipients AS mr
					LEFT JOIN land_blocks AS d ON(mr.block_id = d.block_id)
				SET mr.letter_subject = Replace(mr.letter_subject,'$merge_field', IFNULL(d.$field, '[$field]') )
				WHERE ( mr.mailmerge_id = '$merge_id' )
					AND ( mr.letter_subject like '%$merge_field%' )
			";
		print "<!-- merge_letters sql 3a:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
			
		# $changes += $self->check_error( $sbh3a, "block" );
		print(mysql_affected_rows() . " subjects ");
		$changes += mysql_affected_rows();
		
		// SHOULD RELEASE $query HERE [Corwyn P41]
	  } else {
		die("invalid location '$location'\n");
	  } // endif location
		
		print "</h4>\n";
		/*
		$sql = "SELECT sum(length(letter_body)) as TOTAL
			FROM mailmerge_recipients WHERE mailmerge_id = '$merge_id'";
		print "<!-- DEBUG sql:\n$sql\n-->\n";
		
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
		
		if ($result = mysql_fetch_assoc($query)) {
			print "<h4>CHECK: DATA " . $result['TOTAL'] . "</h4>";
		} else {
			print "<h4>CHECK: NO DATA</h4>";
		}
		*/
		
	} // next variable
	
	if (! $variable_count) { die("ERROR: NO MAILMERGE VARIABLES FOUND.  STOPPING"); }
	
	return $changes;
} // end function merge_letters_NEW

// new, more efficient version of this function, which is a bad idea in the first place.  Deprecated.  [Corwyn P41]
function mail_merge_groups($merge_id) {
	return array_values( get_merge_recipients_both( $merge_id ) );
}

// this function returns only the group names, and does so very inefficiently, in a random order.
function mail_merge_groups_OLD($merge_id) {
	# print("stub mail_merge_groups($merge_id)<br/>\n");
	
 	$sql = "SELECT group_id
			 FROM mailmerge_recipients
			 WHERE selected = '1'
			   AND mailmerge_id = '$merge_id'
		";
   
	print "<!-- mail_merge_groups sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
			
	$retVal = array();
	while( $result = mysql_fetch_assoc($query) ) {
		$group_id	= $result['group_id'];
		$group_name = group_name($group_id);
		array_push($retVal, $group_name);
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $retVal;
} // end function mail_merge_groups

function send_letters_NEW($merge_id) {
	print("stub send_letters_NEW()<br/>\n");
	
	if (count_letters_needing_merged($merge_id) ) {
		print "<h3>NOT SENDING: LETTERS ARE NOT MERGED\n</h3>\n";
		return;
	} // endif
	
	#get all letters that need to be sent
   
 	$sql = "SELECT email_address, letter_subject, letter_body, mailmerge_recipient_id, from_email, group_id
			  	 FROM mailmerge_recipients
				 WHERE selected = '1'
				   AND letter_sent = '0'
				   AND mailmerge_id = '$merge_id'
		";
   
	print "<!-- send_letters_NEW sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	$letters_to_send = mysql_num_rows($query);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	print "<h3>Found $letters_to_send letters to send</h3>\n";
	
	$sent_count = 0;
	
	while( $result = mysql_fetch_assoc($query) ) {
		$to_address	= $result['email_address']; 
		$subject	= $result['letter_subject'];
		$body		= $result['letter_body'];
		$letter_id	= $result['mailmerge_recipient_id']; 
		$from_email	= $result['from_email']; 
		$group_id	= $result['group_id'];
	
		print "DEBUG: sending email<br />FROM: " . $from_email .
			"<br />TO: " . $to_address .
			"<br />SUBJECT: " . $subject .
			"<br />BODY: <pre>" . $body . "</pre>" .
			"<br />\n";
		$sent_count++;
	
		send_letter_NEW( $from_email, $to_address, $subject, $body );
		
		$sql2 = "UPDATE mailmerge_recipients SET letter_sent = '1' WHERE mailmerge_recipient_id = '$letter_id' ";
		$query2 = mysql_query($sql2)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql2<br/>at file " . __FILE__ . " line " . __LINE__);
		// SHOULD RELEASE $query2 HERE [Corwyn P41]
			
		$sql3 = "UPDATE land_groups SET used_space_save = used_space WHERE group_id = '$group_id' ";
		$query3 = mysql_query($sql3)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql3<br/>at file " . __FILE__ . " line " . __LINE__);
		// SHOULD RELEASE $query3 HERE [Corwyn P41]
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $sent_count;
} // end function send_letters

// returns an array of data as $year => $count
function query_mailmerge_years() {
	$retVal = array();
	
	$sql = "SELECT year(modified_date) as year, count(*) as num
			FROM mailmerge AS mm
			GROUP BY year DESC
		";
	
	print "<!-- query_mailmerge_years sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	while ($result = mysql_fetch_array($query)) {
		$year		= $result['year'];
		$num		= $result['num'];
		
		$retVal[ $year ] = $num;
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $retVal;
} // end function query_mailmerge_years

function query_mailmerge_details($where_clause = "") {
	
	if ($where_clause) {
	    $sql = "SELECT mailmerge_id, from_email, letter_subject, letter_body, email_address, block_id,
			group_id, display_value AS group_name
		    FROM mailmerge_recipients
		    WHERE letter_sent = 1
			AND $where_clause
		    ORDER BY mailmerge_id DESC, block_id ASC, group_name ASC
	    ";
	} else {
	    $sql = "SELECT mailmerge_id, from_email, letter_subject, letter_body, email_address,
			0 AS block_id,
			0 AS group_id,
			concat(count(display_value),'&nbsp;Groups') AS group_name
		    FROM mailmerge_recipients
		    WHERE letter_sent = 1
		    GROUP BY mailmerge_id DESC
	    ";
	}
	
	print "<!-- query_mailmerge_details sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// DO NOT RELEASE $query, WE ARE RETURNING IT:
	return $query;
} // end function query_mailmerge_details

# newer function [Corwyn 2009] - queries the database for mailmerges
function query_mailmerges($q_id = "", $year = "") {
	# print "<h4>DEBUG: called query_mailmerges($q_id,$year)</h4>\n";
	
	if ($q_id) {
		$where_clause = "WHERE mailmerge_id = '$q_id'";
	} elseif ($year) {
		$where_clause = "WHERE year(modified_date) = '$year'";
	} else {
		$where_clause = "";
	}
	
	$sql = "SELECT mm.mailmerge_id, mm.from_email, mm.letter_subject, mm.letter_body,
				letter_sent, selected,
 				count(*) as recipients
			 FROM mailmerge AS mm
			 	LEFT JOIN mailmerge_recipients AS mr USING(mailmerge_id)
			 $where_clause
			 GROUP BY mm.mailmerge_id DESC, letter_sent, selected
		";
	
	print "<!-- query_mailmerges sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// DO NOT RELEASE $query, WE ARE RETURNING IT:
	return $query;
} // end function query_mailmerges

# new function [Corwyn 2011] - queries the database and returns list of statuses
function query_mailmerges_status($q_id = "", $year = "") {
	$retVal = array();
	
	$query = query_mailmerges($q_id, $year);
	
	while ($result = mysql_fetch_array($query)) {
		$merge_id		= $result['mailmerge_id'];
		$letter_sent		= $result['letter_sent'];
		$selected		= $result['selected'];
		$recipients		= $result['recipients'];
		
		$retVal[$merge_id]['from']		= $result['from_email'];
		$retVal[$merge_id]['subject']		= $result['letter_subject'];
		$retVal[$merge_id]['body']		= $result['letter_body'];
		if ($letter_sent) {
			@$retVal[$merge_id]["sent_count"]	+= $result['recipients'];
			@$retVal[$merge_id]["unsent_count"]	+= 0;
		} else {
			@$retVal[$merge_id]["sent_count"]	+= 0;
			@$retVal[$merge_id]["unsent_count"]	+= $result['recipients'];
		}
		if ($selected) {
			@$retVal[$merge_id]["selected_count"]	+= $result['recipients'];
			@$retVal[$merge_id]["unselected_count"]	+= 0;
		} else {
			@$retVal[$merge_id]["selected_count"]	+= 0;
			@$retVal[$merge_id]["unselected_count"]	+= $result['recipients'];
		}
	} // next result
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	foreach ($retVal as $merge_id => $result) {
		$display_from		= $result['from'];
		$display_subject	= $result['subject'];
		$display_body		= $result['body'];

		$selected_count		= $result['selected_count'];
		$unselected_count	= $result['unselected_count'];
		$sent_count		= $result['sent_count'];
		$unsent_count		= $result['unsent_count'];

		$status = "READY";      # assume ready, then overwrite if not ready:

		if (! $display_from)	{ $status = "SETUP"; }
		if (! $display_subject)	{ $status = "SETUP"; }
		if (! $display_body)	{ $status = "SETUP"; }
		if (! $selected_count)	{ $status = "SETUP"; }
	
		if ($sent_count)	{ $status = "SENDING"; }
	
		if (! $unsent_count)	{ $status = "SENT"; }
	
		$retVal[$merge_id]['from']	= $display_from;
		$retVal[$merge_id]['subject']	= $display_subject;
		$retVal[$merge_id]['body']	= $display_body;
		$retVal[$merge_id]['status']	= $status;
	} // next retVal
	
	return $retVal;
} // function query_mailmerges_status

# unused anymore?
function query_mailmerge($merge_id="") {
	print "<h4>DEBUG: called query_mailmerge($merge_id)</h4>\n";
	
 	$sql = "SELECT mm.mailmerge_id, selected, letter_sent, count(*),
			mm.from_email, mm.letter_subject, mm.letter_body
		FROM mailmerge AS mm
			LEFT JOIN mailmerge_recipients USING(mailmerge_id)
		";
	if ($merge_id) {
		$sql .= " WHERE mm.mailmerge_id = '$merge_id'\n";
	}
	$sql .= " GROUP BY mm.mailmerge_id, selected ";
	
	print "<!-- get_merge_statistics sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// DO NOT RELEASE $query, WE ARE RETURNING IT:
	return $query;
} // end function query_mailmerge

function make_new_merge( /* $owner_id,  */ $from_email = "", $letter_subject = "", $letter_body = "" ) {
	global $user_id;
	
	$owner_id = $user_id;	# the logged-on user [NEW Corwyn P40]
	
	$owner_id	= mysql_real_escape_string($owner_id		);
	$from_email	= mysql_real_escape_string($from_email		);
	$letter_subject	= mysql_real_escape_string($letter_subject	);
	$letter_body	= mysql_real_escape_string($letter_body		);
	
	$sql = "INSERT INTO mailmerge
                           ( owner, from_email, letter_subject, letter_body ) 
                           VALUES
                           ( '$owner_id', '$from_email', '$letter_subject', '$letter_body' )";
	print "<!-- make_new_merge sql 1:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	$sql = "SELECT LAST_INSERT_ID() AS id";
	
	print "<!-- make_new_merge sql 2:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	if ($result = mysql_fetch_array($query)) {
		$merge_id = $result['id'];
	} else {
		$merge_id = 0;
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $merge_id;
} // end function make_new_merge

function copy_merge($old_merge_id) {
	print "copying mail merge object<br />";
	
	$query = query_mailmerges($old_merge_id);
	
	if ($result = mysql_fetch_array($query)) {
		$from_email     = $result['from_email'];
		$letter_subject = $result['letter_subject'];
		$letter_body    = $result['letter_body'];
	} else {
		return 0;
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	$new_merge_id = make_new_merge($from_email, $letter_subject, $letter_body);
	
	$sql = "INSERT INTO mailmerge_recipients
		(mailmerge_id, selected, user_id, group_id, block_id, display_value,letter_body,from_email,email_address)
			SELECT '$new_merge_id', selected, user_id, group_id, block_id, display_value,'',from_email,'<[email_address]>'
			FROM mailmerge_recipients
			WHERE mailmerge_id = '$old_merge_id'
		";
	# NOTE: '', '<[email_address]>', and '' should be the default values for letter_body, email_address, and from_email,
	#	in which case we can leave off those two values and selects here.  Corwyn 2008.
	# NOTE: original function queried '1' into 'selected', current function queries 'selected' instead [Corwyn P40]
	
	print "<!-- copy_merge sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	# or perhaps we should call set_merge_recipients( $new_merge_id, get_merge_recipients( $old_merge_id ) );   [Corwyn P41]
	
	return $new_merge_id;
} // end function copy_merge

function delete_merge($merge_id) {
	set_merge_recipients( $merge_id, array() );
	
	$sql = "DELETE FROM mailmerge
			WHERE mailmerge_id = '$merge_id'
		";
	
	print "<!-- delete_merge sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	return;
} // end function delete_merge

function set_merge_from($merge_id, $from_email) {
	$sql = "UPDATE mailmerge
			SET from_email = '$from_email'
			WHERE mailmerge_id = '$merge_id'
	";
	
	print "<!-- set_merge_from sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return 1;
} // end function set_merge_from

function set_merge_subject_body($merge_id, $letter_subject, $letter_body) {
	$sql = "UPDATE mailmerge
			SET letter_subject = '$letter_subject'
			  , letter_body    = '$letter_body'
			WHERE mailmerge_id = '$merge_id'
	";

	print "<!-- set_merge_subject_body sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return 1;
} // end function set_merge_subject_body

function set_merge_recipients($merge_id, $recipient_ids) {
	
	$sql = "DELETE FROM mailmerge_recipients
		WHERE mailmerge_id = '$merge_id'
		";

	print "<!-- set_merge_recipients sql 1:\n$sql\n-->\n";

	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	foreach ($recipient_ids as $group_id) {
		$sql = "INSERT INTO mailmerge_recipients
			(mailmerge_id, selected, user_id, group_id,    block_id, display_value,   letter_body,from_email,email_address)
			VALUES
			('$merge_id',  '1',      '0',     '$group_id', '0',      '',              '',         '',        '<[email_address]>')
			";
	
		print "<!-- set_merge_recipients sql 2:\n$sql\n-->\n";
	
		$query = mysql_query($sql)
			or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
		
		// SHOULD RELEASE $query HERE [Corwyn P41]
	} // next group_id
	
	// set corresponding user_id, block_id, group_name fields
	merge_cleanup_data($merge_id);
	// display_value, letter_body, from_email should update themselves already
} // end function set_merge_recipients

function merge_cleanup_data($merge_id) {
	
	$sql = "UPDATE mailmerge_recipients AS r
		    INNER JOIN land_groups AS g USING(group_id)
		SET display_value = group_name, r.user_id = g.user_id, r.block_id = g.final_block_location
		WHERE mailmerge_id = '$merge_id'
		";
	
	print "<!-- merge_cleanup_data sql:\n$sql\n-->\n";

	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
}

// new function that does the same work as mail_merge_groups and get_merge_recipients, but more efficiently.  [Corwyn P41]
function get_merge_recipients_both($merge_id) {
	merge_cleanup_data($merge_id);
	
	$retVal = array();
	
	$sql = "SELECT group_id, display_value
		FROM mailmerge_recipients
		WHERE selected = '1'
			AND mailmerge_id = '$merge_id'
		ORDER BY display_value
		";
	
	print "<!-- get_merge_recipients_both sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	while ($result = mysql_fetch_array($query)) {
		$merge_group_id		= $result['group_id'];
		$merge_group_name	= $result['display_value'];
		
		$retVal[ $merge_group_id] = $merge_group_name;
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $retVal;
}

// new, more efficient version of this function, deprecated [Corwyn P41]
function get_merge_recipients($merge_id) {
	return array_keys( get_merge_recipients_both( $merge_id ) );
}

// almost a copy of mail_merge_groups, but this one doesn't look up all 856 group names individually :-(  [Corwyn P41]
function get_merge_recipients_OLD($merge_id) {
	$retVal = array();
	
	$sql = "SELECT group_id
		FROM mailmerge_recipients
		WHERE selected = '1'
			AND mailmerge_id = '$merge_id'
		";
	
	print "<!-- get_merge_recipients sql:\n$sql\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	
	while ($result = mysql_fetch_array($query)) {
		$merge_group_id		= $result['group_id'];
		array_push($retVal, $merge_group_id);
	}
	
	// SHOULD RELEASE $query HERE [Corwyn P41]
	
	return $retVal;
} // function get_merge_recipients

# new function [Corwyn 2007] - finds statistics of how many records of what type are in the mail merge system
# returns a hash containing array refs of (#unselected, #selected, #unsent, #sent, from_email, subject, body).
# unused anymore?
function get_merge_statistics($merge_id="") {
	# this is a load of crap.
	/*
	$statistics = array();
	
 	$sqlQuery = "SELECT mailmerge.mailmerge_id, selected, letter_sent, count(*),
					mailmerge.from_email, mailmerge.letter_subject, mailmerge.letter_body
				 FROM mailmerge
				 	LEFT JOIN mailmerge_recipients USING(mailmerge_id)
				";
	$sqlQuery .= " WHERE mailmerge.mailmerge_id = '$merge_id'\n"    if $merge_id ne "";
	$sqlQuery .= " GROUP BY mailmerge.mailmerge_id, selected, letter_sent ";
	
	print "<!-- get_merge_statistics sql:\n$sqlQuery\n-->\n";
	
	$query = mysql_query($sql)
		or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);
	


	my %unselected = ();
	my %selected   = ();
	my %sentdata   = ();
	my %unsentdata = ();
	my %merge_list = ();
	my %froms      = ();
	my %subjects   = ();
	my %bodys      = ();
	
	# print "DEBUG: list of (id, selected, sent, count, from_email, subject, body)<br />\n";
	while ($result = mysql_fetch_array($query)) {
		$x = $result['x'];
		my($merge_id, $selected, $sent, $count, $from, $subject, $body) = @array;
		
		# print "DEBUG: $merge_id, $selected, $sent, $count, $from, $subject, $body<br />\n";
		
		$merge_list{ $merge_id }++;
		
		if ($sent) {
			$sentdata{ $merge_id } += $count;
		} else {
			$unsentdata{ $merge_id } += $count;
		}
		
		if ($selected) {
			$selected{ $merge_id } += $count;
		} else {
			$unselected{ $merge_id } += $count;
		}
		
		$froms{ $merge_id }       = $from;
		$subjects{ $merge_id }    = $subject;
		$bodys{ $merge_id }       = $body;
	}
	
	# print "DEBUG: list of (id, unselected_count, selected_count, unsent_count, sent_count, from, subject, body)<br />\n";
	foreach my $merge_id (keys %merge_list) {
		my $unselected_count = $unselected{ $merge_id } || 0;
		my $selected_count   = $selected{ $merge_id }   || 0;
		my $sent_count       = $sentdata{ $merge_id }   || 0;
		my $unsent_count     = $unsentdata{ $merge_id } || 0;
		my $from             = $froms{ $merge_id }      || "";
		my $subject          = $subjects{ $merge_id }   || "";
		my $body             = $bodys{ $merge_id }      || "";
		
		$statistics{ $merge_id } = [ ( $unselected_count, $selected_count, $unsent_count, $sent_count, $from, $subject, $body ) ];
		
		# print "DEBUG: $merge_id, $unselected_count, $selected_count, $unsent_count, $sent_count, $from, $subject, $body<br />\n";
	}
	
	return %statistics;
	*/
} // end function get_merge_statistics
?>