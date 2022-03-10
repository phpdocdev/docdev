<?php

function tab($tab, $tab_id){
	if (!isset($tab, $tab_id)) 
		return NULL;

	if ($tab == $tab_id) 
		return array ('id' => 'current');
	else
		return NULL;
}

?>