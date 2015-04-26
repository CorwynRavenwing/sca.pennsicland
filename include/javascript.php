<script type="text/javascript" language="javascript">

function dump_obj( ob )
{
    t = "";
    
    t = t + ob + "[" + (typeof ob) + "]";
    
    if (typeof ob == "object") {
        t = t + "len:" + ob.length;
	for(i=0; i<ob.length; i++) {
		//alert( theSel.options[i].text + "_" + i );
		t = t + "|" + ob[i];
	}
    } else {
	t = t + "{not an object}";
    }
    
    return t;
}

function getElementById(id) {
	// browser independent way to get an object by its id
	if (typeof document.getElementById != 'undefined') {
		return document.getElementById( id );
	} else if (typeof document.all != 'undefined') {
		return document.all[ id ];
	} else if (typeof document.layers != 'undefined') {
		return document.layers[ id ];
	} else {
		return null;
	}
}

function element_replace_html(id, new_text) {
	var ob = getElementById(id)
	if (ob) ob.innerHTML = new_text;
}

function element_set_display(id, new_value) {
	var ob = getElementById(id)
	if (ob) ob.style.display = new_value;
}

function hide_object(id) {
	element_set_display(id, "none");
}

function show_object(id) {
	element_set_display(id, "block");
}

</script>
<?
function javascript_execute($script) {
	?>
<script type="text/javascript" language="javascript">
	<?=$script?>
</script>
	<?
} # end function javascript_execute

function javascript_element_set_display($object_id, $new_value) {
	javascript_execute("element_set_display('$object_id','$new_value');");
} # end function javascript_element_set_display

function javascript_hide_object($object_id) {
	javascript_element_set_display($object_id, "none");
} # end function javascript_hide_object

function javascript_show_object($object_id) {
	javascript_element_set_display($object_id, "block");
} # end function javascript_hide_object

function javascript_replace_text($id, $new_text) {
	javascript_execute("element_replace_html('$id','$new_text');");
} // end function javascript_replace_text

$javascript_showable_div_id = 0;

function javascript_hidable_div_begin($label = "div", $default_hidden = 1) {
	$id = "JHD_" . (@$javascript_showable_div_id++);
	$id_a = $id . "_a";
	$id_b = $id . "_b";
	?>
<div id="<?=$id_a?>" style="display:none; margin-bottom:0.5em;">
	<a href="#" onClick="show_object('<?=$id_b?>');hide_object('<?=$id_a?>');">Show <?=$label?></a>
</div>

<div id="<?=$id_b?>" style="display:none; margin-bottom:0.5em;">
	<a href="#" onClick="show_object('<?=$id_a?>');hide_object('<?=$id_b?>');">Hide <?=$label?></a>
	<?
	if ($default_hidden) {
		javascript_execute("show_object('$id_a');");
	} else {
		javascript_execute("show_object('$id_b');");
	}
} // end function javascript_hidable_div_begin

function javascript_hidable_div_end() {
	?>
</div>
	<?
} // end function javascript_hidable_div_end

?>