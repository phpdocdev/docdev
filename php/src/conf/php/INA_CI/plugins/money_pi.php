<?php

function money($number, $show_dollar = true){
	setlocale(LC_MONETARY, 'en_US');
	
	if ($show_dollar)
		return money_format('%n', $number);
	
	if (!$number) return NULL;
	
	return h(number_format($number, 2, '.', ''));
}