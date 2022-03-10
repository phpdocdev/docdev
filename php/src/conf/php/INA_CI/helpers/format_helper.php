<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Format a 10 digit phone number as (000) 000-0000
 */
function format_phone($phone){
	if (preg_match('/(\d{3})(\d{3})(\d{4})/', $phone, $matches))
		return sprintf("%03d-%03d-%04d", $matches[1], $matches[2], $matches[3]);
	else
		return h($phone);#don't reformat
}

function format_name($first, $last, $middle = NULL){
	if ($first AND $last AND $middle)
		return sprintf("$first $middle $last");
	elseif ($first AND $last)
		return sprintf("$first $last");
}