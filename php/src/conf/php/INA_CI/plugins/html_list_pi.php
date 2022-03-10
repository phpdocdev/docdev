<?php

function html_list($array, $id = NULL, $class = NULL, $type = 'ul', $delimiter = '|'){
	if (!$array) return NULL;
	
	//get class
	if ($class)	$class = " class=\"{$class}\"";
		
	//get ID
	if ($id) $class = " id=\"$id\"";

	if (!is_array($array)) {
		$item = $array;
		$output .= "<li>{$item}</li>\n";
	}
	else {//build list
		$last = sizeof($array) - 1;
		foreach ($array as $item) {
			if ($item == $array[$last])
				$output .= "<li>{$item}</li>\n";
			else
				$output .= "<li>{$item} {$delimiter}</li>\n";
		}
	}
		
	//add ol or ul, ul is default	
	if ($type == 'ol')
		$output = "<ol{$class}{$id}>\n{$output}</ol>";
	else
		$output = "<ul{$class}{$id}>\n{$output}</ul>";
		
	return $output;//return output
}

?>