<?php

function back_button($content){

	switch ($content) {
		case 'user/home' 	: 
		case 'user/admin'	:
		case 'user/login' 	: return;
	}
	
	return '<a id="back_button" href="javascript:history.back()">&lt; Back</a>';
}

?>