<?php

function change_uri($url){

	$page = site_url($url."/'+this.value+'");
		
	return "onChange = \"window.location='{$page}';\"";
}

?>