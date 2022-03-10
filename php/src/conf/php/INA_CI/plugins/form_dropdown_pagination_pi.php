<?php

function form_dropdown_pagination($path, $limit, $count, $selected=NULL){

	$CI =& get_instance();
	$uri = $CI->uri->uri_string();

	$start_values = range(0, $count, $limit);
	$page_numbers = range(1, count($start_values));
		
	$url = site_url() . $path . "'+this.value+'/".$_SESSION['ina_sec_csrf'];
	
	return form_dropdown('pagination', array('' => '') + array_combine($start_values, $page_numbers), $selected, 'onChange="window.location=\''.$url."';\"");
}