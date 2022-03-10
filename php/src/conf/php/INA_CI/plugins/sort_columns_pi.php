<?php

function sort_columns($url, $row_columns, $sort_uri = 3) {
	$CI =& get_instance();
	
	if (is_string($row_columns)) {
		$_ = array($row_columns);
		$row_columns = $_;	
	}
	
	foreach ($row_columns as $sort => $title) {
		if ($sort == $CI->uri->segment($sort_uri)) {
			$direction_class = $CI->uri->segment($sort_uri+1) ? sprintf('class="%s"', $CI->uri->segment($sort_uri+1)) : NULL;
			$direction = $CI->uri->segment($sort_uri+1) == 'desc' ? 'asc' : 'desc';
		}
		else {
			$direction = 'asc';
			unset($direction_class);
		} 
					
		if (is_numeric($sort))
			echo '<th>'.h($title).'</th>';
		else
			echo '<th>'.anchor("{$url}{$sort}/{$direction}", h($title), $direction_class).'</th>';
   	}	
}

#using assoc instead of a uri number
function sort_columns_assoc($url, $row_columns) {
	$CI =& get_instance();
	
	$uri = $CI->uri->uri_to_assoc();
	
	if (is_string($row_columns)) {
		$_ = array($row_columns);
		$row_columns = $_;	
	}
	
	foreach ($row_columns as $sort => $title) {
		if ($sort == $uri['sort']) {
			$direction_class = $uri['direction'] ? sprintf('class="%s"', $uri['direction']) : NULL;
			$direction = $uri['direction'] == 'desc' ? 'asc' : 'desc';
		}
		else {
			$direction = 'asc';
			unset($direction_class);
		}
					
		if (is_numeric($sort))
			echo '<th>'.h($title).'</th>';
		else
			echo '<th>'.anchor("{$url}sort/{$sort}/direction/{$direction}", h($title), $direction_class).'</th>';
   	}	
}