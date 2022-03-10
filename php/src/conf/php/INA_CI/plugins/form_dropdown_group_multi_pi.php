<?php

function form_dropdown_group_multi($name, $options, $value = NULL, $attr = NULL){
	if ($_POST[$name])
		$value = $_POST[$name];

	if (!is_array($options)) return false;

	$CI =& get_instance();
	
	$error_field = $name . '_error'; #name of error field is name of form field plus '_error'
	if ($CI->validation->$error_field) 
		$attr ? $attr .= 'class="error"' : $attr = 'class="error"'; #make field class 'error' if it is an error
		
	
	$output .= "<select name=\"{$name}\" {$attr} multiple=\"multiple\">\n";
	
	foreach ($options as $label => $optgroup){
		
		if (is_array($optgroup)) {
			$output .= "<optgroup label=\"{$label}\">\n";
			
			foreach ($optgroup as $val => $label) {
				if ($val == $value)
					$output .= "<option value=\"{$val}\" selected=\"selected\">{$label}</option>\n";
				else
					$output .= "<option value=\"{$val}\">{$label}</option>\n";
			}
			
			$output .= "</optgroup>";
		}
		else {
			$val = $optgroup;
			if ($val == $value)
				$output .= "<option value=\"{$val}\" selected=\"selected\">{$label}</option>\n";
			else
				$output .= "<option value=\"{$val}\">{$label}</option>\n";
		}
			
	}
	
	$output .= "</select>\n";
	
	return $output;
}


?>