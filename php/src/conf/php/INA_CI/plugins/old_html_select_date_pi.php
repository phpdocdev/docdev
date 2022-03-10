<?php

function html_select_date($prefix = "Date"){

	$options = array (
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December'
	);

	$name = $prefix.'Month';
	$months = form_dropdown($name, $options, date('m'), "id=\"{$name}\"");
	
	$options = array ();
	for ($d = 1; $d <= 31; $d++) {
		$day = sprintf("%02d", $d);
		$options[$day] = $day;
	}
	
	$name = $prefix.'Day';
	$days = form_dropdown($name, $options, date('d'), "id=\"{$name}\"");
	
	$options = array ();
	for ($y = 0; $y < 10; $y++) {
		$year = date('Y') - $y;
		$options[$year] = $year;
	}
		
	$name = $prefix.'Year';
	$years = form_dropdown($name, $options, date('Y'), "id=\"{$name}\"");
	
	return "{$months}\n {$days}\n {$years}\n";
}