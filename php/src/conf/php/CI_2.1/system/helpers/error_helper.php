<?php
function error_summary($errors = NULL){

	if (isset($errors) && is_array($errors) && count($errors) > 0){
		print '<ul class="error_summary">';
		foreach($errors as $field=>$messages){
			foreach($messages as $m){
				printf('<li>%s</li>', $m);
			}
		}
		print '</ul>';
	}
}