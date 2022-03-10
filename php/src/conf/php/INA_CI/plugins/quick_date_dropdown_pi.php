<?php
/*
 * Created on Oct 10, 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
function quick_date_dropdown(){
	
	$options = array (
		'' 						=> '',
		'today'					=> 'Today',
		'yesterday'				=> 'Yesterday',
		'last_seven_days'		=> 'Last 7 Days',
		'last_month'			=> 'Last Month',
		'last_year'				=> 'Last Year',
		'last_calendar_month'	=> 'Last Calendar Month',
		'last_calendar_year'	=> 'Last Calendar Year'
	);
	
	return form_dropdown('date_select', $options, '', "onChange=\"quick_date(this.value)\"");
}