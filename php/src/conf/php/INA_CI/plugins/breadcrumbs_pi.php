<?php

function breadcrumbs($links, $delimiter = '&gt;&gt;') {
	if (!$links) return NULL;
	
	if (is_string($links)) return $links;
	
	if (count($links) == 1) return $links[0];
	
	foreach ($links as $count => $l) {
		if ($count < count($links) - 1)
			$output .= "$l $delimiter ";
		else
			$output .= "$l";
	}
	
	return $output;
}

?>