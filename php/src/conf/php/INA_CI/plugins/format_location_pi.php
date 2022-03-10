<?php

function format_location($city, $state, $zip, $format = NULL){
	if (!$format){
		if ($city && $state && $zip)
			$format = '%s, %s %s';
		else
			$format = "%s %s %s";
	}
	
	return sprintf($format, $city, $state, $zip);
}