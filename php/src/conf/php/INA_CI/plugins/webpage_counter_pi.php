<?php

/**
 * Webpage Counter
 * 
 * @param string $filename path of flat file used for counter
 * @return integer count
 */
function webpage_counter($filename) {
	
	@$fptr = fopen($filename, "r+");
	
	if ($fptr == NULL) {
	    @$fptr = fopen($filename, "w+");
	    fwrite($fptr, "1");
	    fclose($fptr);
	    return "1";
	}
	else {
	    $data = fread($fptr, filesize($filename));
	    $dataInt = (int) $data;
	    $dataInt++;
	    rewind($fptr);
	    fwrite($fptr, $dataInt);
	    fclose($fptr);
	    return $dataInt;
	}
}