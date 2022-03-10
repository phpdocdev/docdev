<?php

/**
 * This uses a jQuery plugin located at http://www.dyve.net/jquery/?autocomplete
 * Required script includes are jquery.js and jquery.autocomplete.js (currently found at https://www.ark.org/common/)
 * 
 */

/** 
 * CSS Styles (can alternatively use jquery_autocomplete_default.css and cascade stylesheets, located in ark.org/common)
 * 
 * .ac_input
 * .ac_results
 * .ac_results ul
 * .ac_results iframe
 * .ac_results li
 * .ac_loading
 * .ac_over
 * 
 */

/**
 * Main function
 */
function form_input_auto($data = '', $value = '', $extra = '', $validate_value = TRUE){
	static $jquery_count = 1;#set static variable for incrementing class name
	
	if ($data['auto_complete']) {#id is required, otherwise auto complete is ignored
		
		echo display_autocomplete_script($data['auto_complete'], $jquery_count);
		
		$data['class'] = ($data['class'] ? ($data['class'] . ' jquery_auto_complete') : 'jquery_auto_complete').$jquery_count;
		
		unset($data['auto_complete']);#so that it doesn't get added to inline form
		$data['autocomplete'] = 'off';#disable html autocomplete (for browsers like firefox)
	}
	
	$jquery_count++;#increment jquery count
	
	return form_input($data, $value, $extra, $validate_value);	
}

/**
 * Helper function spits out script and function call
 */
function display_autocomplete_script($auto_complete, $jquery_count = 1){
	$output  .= '<script type="text/javascript">'."\n";
		
	#set url
	$auto_url    = $auto_complete['url'] ? $auto_complete['url'] : site_url();
	unset($auto_complete['url']);
	
	/**
	 * Set defaults to parameters
	 */
	if (!$auto_complete['delay'])
		$auto_complete['delay'] = 0;
	
	if (!$auto_complete['postMethod'])
		$auto_complete['postMethod'] = 1;
	 	
	if (!$auto_complete['minChars'])
		$auto_complete['minChars'] = 2;
		
	if (!$auto_complete['matchSubset'])
		$auto_complete['matchSubset'] = 1;
	
	if (!$auto_complete['matchContains'])
		$auto_complete['matchContains'] = 1;
	
	if (!$auto_complete['cacheLength'])
		$auto_complete['cacheLength'] = 10;
		
	if (!$auto_complete['formatItem']) {
		$default_format_item = "function(row) { txt = $('.jquery_auto_complete{$jquery_count}')[0].value; return row[0].replace(new RegExp(txt, 'gi'),'<strong>'+txt+'</strong>'); }";
		
		$auto_complete['formatItem'] = $default_format_item;
	}
		
	/**
	 * Set params in javascript
	 */
	foreach ($auto_complete as $key => $value)
		$extra_param .= ", {$key}:{$value}";

	$output .= '$(document).ready(function() {'."\n";
	$output .= "  $('.jquery_auto_complete{$jquery_count}').autocomplete(\"{$auto_url}\", { makeUrl:'clean' {$extra_param} });"."\n";
	$output .= '});'."\n";

	$output .= '</script>'."\n";
	
	return $output;
}