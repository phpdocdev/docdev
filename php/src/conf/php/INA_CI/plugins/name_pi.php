<?php


function name($first, $last, $middle = NULL){
	return h(trim("$last, $first $middle"));
}

?>