<?php

function html_select_date($prefix = "Date", $param = NULL){
	if ($param['date'])#if not array, assume it is a date 
		list($param['year'], $param['month'], $param['day']) = explode('-', date('Y-m-d', strtotime($param['date'])));#will convert many types of dates

	if ($param['js'])
		$param['month_js'] = $param['day_js'] = $param['year_js'] = $param['js'];

	if ($param['month'] !== FALSE) {
		if ($param['blank'])
			$options = array('' => '');
		else
			$options = array();
			
		$options += array (
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
		
		if (!$param['month'] && !$param['blank'])
			$m = date('m');
		else
			$m = $param['month'];
			
		$months = form_dropdown($prefix.'Month', $options, $m, $param['month_js']. " id=\"{$prefix}Month\"");
		$output .= "{$months}\n ";
	}

	if ($param['day'] !== FALSE) {
		if ($param['blank'])
			$options = array('' => '');
		else
			$options = array ();
			
		for ($d = 1; $d <= 31; $d++) {
			$day = sprintf("%02d", $d);
			$options[$day] = $day;
		}
		
		if (!$param['day'] && !$param['blank'])
			$d = date('d');
		else
			$d = $param['day'];
		
		$days = form_dropdown($prefix.'Day', $options, $d, $param['day_js']. " id=\"{$prefix}Day\"");
		
		$output .= "{$days}\n ";
	}
	
	if ($param['year'] !== FALSE) {
		if ($param['blank'])
			$options = array('' => '');
		else
			$options = array ();
		
		$last_year = $param['last_year'] ? $param['last_year'] : date('Y');
		
		if ($param['first_year'] && $param['years_back'] == NULL)
			$num_back_years = $last_year - $param['first_year'] + 1;
		else
			$num_back_years = $param['years_back'] !== NULL ? $param['years_back'] + 1 : 10;			 		 

		for ($y = 0; $y < $num_back_years; $y++) {
			$first_year = $param['future_year'] ? ($last_year + 9): $last_year;
			$year = $first_year - $y;
			
			$options[$year] = $year;
		}
			
		if (!$param['year'] && !$param['blank'])
			$y = $last_year;
		else
			$y = $param['year'];
			
		$years = form_dropdown($prefix.'Year', $options, $y, $param['year_js']. " id=\"{$prefix}Year\"");
		
		$output .= "{$years}\n ";
	}
	
	return $output;
}