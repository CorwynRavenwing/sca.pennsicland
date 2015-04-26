/*
 * check_block_answers.js
 *
 * Validate the fields on the present form named:
 *		groupname
 *		first_block_choice
 *		second_block_choice
 *		third_block_choice
 *		fourth_block_choice
 * and put the commentary into the fields named:
 *		block_1_comment
 *		block_2_comment
 *		block_3_comment
 *		block_4_comment
 * Also provides a function that returns whether there is an error on this record
 */

var block_answers_error = false;

function block_answers_ok() {
	return (! block_answers_error);
}

// usage: <form ... onsubmit="return block_answers_enforce();">
function block_answers_enforce() {
	check_block_answers();
	
	if (block_answers_error) {
		alert("please correct your block answers before continuing.");
		return false;
	} else {
		// alert("there is no error");
		return true;
	}
}

function block_answers_warn() {
	check_block_answers();
	
	if (block_answers_error) {
		alert("warning: saving data but with invalid block answers.");
//	} else {
//		alert("there is no error");
	}
}

function check_block_answers() {
	block_answers_error = false;
	
	var groupname_ob = document.getElementById( "groupname" );
	
	var block_1_ob = document.getElementById( "first_block_choice" );
	var block_2_ob = document.getElementById( "second_block_choice" );
	var block_3_ob = document.getElementById( "third_block_choice" );
	var block_4_ob = document.getElementById( "fourth_block_choice" );
	
	var comment_1_ob = document.getElementById( "block_1_comment" );
	var comment_2_ob = document.getElementById( "block_2_comment" );
	var comment_3_ob = document.getElementById( "block_3_comment" );
	var comment_4_ob = document.getElementById( "block_4_comment" );
	
	var valid_ob     = document.getElementById( "block_choices_valid" );
	
	var block_1 = get_select_value(block_1_ob);
	var block_2 = get_select_value(block_2_ob);
	var block_3 = get_select_value(block_3_ob);
	var block_4 = get_select_value(block_4_ob);
	
	var groupname = groupname_ob.value;
	
	var comment_1 = "";
	var comment_2 = "";
	var comment_3 = "";
	var comment_4 = "";
	
/*
	X blocks, Other (first), OOB, and DSA, as well as kingdom camps,
		it fills in the rest as the same value automatically.
	unless the preceeding is true,
		don't allow the same block to be entered twice.
*/
	
	var must_be_all_same = false;
	
	var kingdom_label = "Kingdom of ";
	if (groupname.substring(0, kingdom_label.length) == kingdom_label) {
		// comment_1 += "\nDEBUG: kingdom camp";
		must_be_all_same = true;
	} else {
		// comment_1 += "\nDEBUG: '" + groupname.substring(0, kingdom_label.length) + "'";
	}
	
	var same_2 = false;
	var same_3 = false;
	var same_4 = false;
	
	if (block_2 == block_1) { same_2 = true; }
	
	if (block_3 == block_1) { same_3 = true; }
	if (block_3 == block_2) { same_3 = true; }
	
	if (block_4 == block_1) { same_4 = true; }
	if (block_4 == block_2) { same_4 = true; }
	if (block_4 == block_3) { same_4 = true; }
	
	var blank_msg     = "\nplease choose a location";
	var dupl_msg      = "\nplease don't list a location twice";
	var auto_msg      = "\ndata automatically changed.  please press SUBMIT";
	var diff_msg      = "\nfirst block choice NOT available: please contact Land staff to correct this issue";
	var diff_msg_old  = "\nplease list the same location as above";
	// var ok_msg     = "\n(ok)";
	var ok_msg        = "";
	var xblock_msg    = "\nI assume you arranged with the Coopers to camp here?";
	var dsa_msg       = "\nNote: only Disabilities Camping may choose the DSA block";
	var onlyfirst_msg = "\nThis location cannot be used as a secondary choice";
	
	if      (block_1 == "") {
		comment_1 += blank_msg;
		block_answers_error = true;		// first choice CANNOT be blank
	}
	else if (block_1 == "Other") {
		must_be_all_same = true;		// but CAN be "Other"
	}
	else if (block_1 == "OOB") {
		must_be_all_same = true;
	}
	else if (block_1 == "DSA") {
		must_be_all_same = true;
	}
	else if (block_1.substring(0,1) == "X") {
		must_be_all_same = true;
		comment_1 += xblock_msg;
	}
	
	if (must_be_all_same) {
		
		// here, being different FROM FIRST outranks being blank
		
		if      (block_2 != block_1) {
			if ( select_option_byvalue(block_2_ob, block_1) ) {
				comment_2 += "\n(was " + block_2 + ")" + auto_msg;
			} else {
				comment_2 += diff_msg;
				block_answers_error = true;		// Must not be different
			}
		}
		else if (block_2 == "") {
			comment_2 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_2 == "Other") {
			comment_2 += blank_msg;			// Legal
		}
	//	else if (block_2.substring(0,1) == "X") {
	//		comment_2 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
	//	else if (block_2 == "DSA") {
	//		comment_2 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
		else { comment_2 += ok_msg; }
	
		if      (block_3 != block_1) {
			if ( select_option_byvalue(block_3_ob, block_1) ) {
				comment_3 += "\n(was " + block_3 + ")" + auto_msg;
			} else {
				comment_3 += diff_msg;
				block_answers_error = true;		// Must not be different
			}
		}
		else if (block_3 == "") {
			comment_3 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_3 == "Other") {
			comment_3 += blank_msg;			// Legal
		}
	//	else if (block_3.substring(0,1) == "X") {
	//		comment_3 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
	//	else if (block_3 == "DSA") {
	//		comment_3 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
		else { comment_3 += ok_msg; }
		
		if      (block_4 != block_1) {
			if ( select_option_byvalue(block_4_ob, block_1) ) {
				comment_4 += "\n(was " + block_4 + ")" + auto_msg;
			} else {
				comment_4 += diff_msg;
				block_answers_error = true;		// Must not be different
			}
		}
		else if (block_4 == "") {
			comment_4 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_4 == "Other") {
			comment_4 += blank_msg;			// Legal
		}
	//	else if (block_4.substring(0,1) == "X") {
	//		comment_4 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
	//	else if (block_4 == "DSA") {
	//		comment_4 += onlyfirst_msg;
	//		block_answers_error = true;
	//	}
		else { comment_4 += ok_msg; }
	
	} else { // not must be all same
		
		// here, being blank outranks being different FROM ALL
		
		if      (block_2 == "") {
			comment_2 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_2 == "Other") {
			comment_2 += blank_msg;			// Legal
		}
		else if (same_2) {
			comment_2 += dupl_msg;
			block_answers_error = true;		// not here
		}
		else if (block_2.substring(0,1) == "X") {
			comment_2 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else if (block_2 == "DSA") {
			comment_2 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else { comment_2 += ok_msg; }
	
		if      (block_3 == "") {
			comment_3 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_3 == "Other") {
			comment_3 += blank_msg;			// Legal
		}
		else if (same_3) {
			comment_3 += dupl_msg;
			block_answers_error = true;		// not here
		}
		else if (block_3.substring(0,1) == "X") {
			comment_3 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else if (block_3 == "DSA") {
			comment_3 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else { comment_3 += ok_msg; }
		
		if      (block_4 == "") {
			comment_4 += blank_msg;
			block_answers_error = true;		// Illegal
		}
		else if (block_4 == "Other") {
			comment_4 += blank_msg;			// Legal
		}
		else if (same_4) {
			comment_4 += dupl_msg;
			block_answers_error = true;		// not here
		}
		else if (block_4.substring(0,1) == "X") {
			comment_4 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else if (block_4 == "DSA") {
			comment_4 += onlyfirst_msg;
			block_answers_error = true;		// not here
		}
		else { comment_4 += ok_msg; }
		
	} // endif must be all same
	
	// comment_4 += "\nDEBUG: " + block_answers_ok();
	
	comment_1_ob.innerHTML = comment_1;
	comment_2_ob.innerHTML = comment_2;
	comment_3_ob.innerHTML = comment_3;
	comment_4_ob.innerHTML = comment_4;
	
	if (valid_ob) {
		valid_ob.value = (block_answers_error ? 0 : 1);
	}
} // end functions check_block_answers

function find_option_byvalue(select_ob, option_val) {
	if (! select_ob.options) {
		return -1;
	}
	for (i=0; i<select_ob.options.length; i++) {
		if (select_ob.options[i].value == option_val) {
			return i;
		} // endif
	} // next
	return -1;
} // end function find_option_byvalue

function select_option_byvalue(select_ob, option_val) {
	i = find_option_byvalue(select_ob, option_val);
//	alert("found option " + option_val + " at location " + i);
	
	if (i == -1) {
		// not found
		return false;
	} else {
		select_ob.selectedIndex = i;
		return true;
	}
} // end function select_option_byvalue

function get_select_value(ob) {
	var val = ob.value;
	if (val == "") {
		var selIndex = ob.selectedIndex;
		val = ob[selIndex].value;
		
		if (val == "") {
			val = ob[selIndex].text;
		}
	}
	
	return val;
} // end function get_select_value
