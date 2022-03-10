<?php

function form_dropdown_go($name = NULL, $options = NULL, $selected = NULL, $action, $submit_value = 'Go', $select_all = TRUE){
	if (!is_array($options)) return false;
	
	$output = form_open($action);
	if ($select_all)
		$options = array('' => 'Select All') + $options;
	$output .= form_dropdown($name, $options, $selected);
	$output .= ' ' . form_submit('submit', $submit_value);
	$output .= form_close();
	
	return $output;
}