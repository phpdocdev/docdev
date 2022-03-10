<?php

function format_address($address1=NULL, $address2=NULL){
	if ($address1 && $address2)
		return $address1 . "<br />" . $address2;
	elseif ($address1)
		return $address1;
	elseif ($address2)
		return $address2;
}