<?php
/*
 * Created on Sep 15, 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
function show_flash($message){
	if (is_array($message)){
		$output .= "<ul>\n";
		foreach ($message as $item)
			$output .= "<li>{$item}</li>\n";
		$output .= "</ul>\n";
		return $output;
	}
	else return $message;
}
 
?>
