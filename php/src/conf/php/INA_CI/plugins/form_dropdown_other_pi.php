<?php

function form_dropdown_other($name, $options = array(), $selected = '', $extra = '', $p = array()){	

	$other_field = $name.'_other';

	if (!$selected) {
		if ($_POST[$name] == 'other')
			$selected = $_POST[$other_field];
		else
			$selected = $_POST[$field];
	} 
	
	if ($selected && !(in_array($selected, $options) OR $options[$selected])) {
		$other_value = $selected;
		$selected = 'other';
	}
	else
		$default_display = 'display:none';
	
	$onchange = $p['onChange'];
	
	if ($p['js'] === false)
		$extra .= " onChange=\"other_select(this.value, '{$other_field}'); {$onchange}\"";		
	else
		$extra .= " onChange=\"other_field = document.getElementById('{$other_field}'); if (this.value == 'other') other_field.style.display = 'inline'; else other_field.style.display = 'none'; {$onchange}\"";
	
	if (!$options['other'])
		$options['other'] = 'Other';#append other if not already added
	
	$output .= form_dropdown($name, $options, $selected, $extra) . "\n";
	
	
	$size = $p['size'] ? $p['size'] : 15;
	
	$output .= form_input(array('name' => $other_field, 'id' => $other_field, 'size' => $size, 'style' => $default_display, 'value' => $other_value));
	
	return $output;
}

function form_dropdown_other_js(){
	$output = 
	  "<script>
	    function other_select(selected, field){
	    	other_field = document.getElementById(field);
	    	
	    	if (selected == 'other')
	    		other_field.style.display = 'inline';
	    	else
	    		other_field.style.display = 'none';
	    }
	  </script>";
	
	return $output;	
}