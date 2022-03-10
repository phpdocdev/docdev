<?php

function cycle($one, $two = NULL){
	static $counter = 0;

	if ($counter == 0) {
		$counter = 1;
		return $one;
	}
	else {
		$counter = 0;
		return $two;
	}
}