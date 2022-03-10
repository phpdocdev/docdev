<?php

/**
 * Filter url function.
 * 
 * This looks in a string for urls, then converts to html links and returns the string
 * 
 * @param string $str string that will be modified
 * 
 * @return string the string with html markup for links
 */
function filter_url($str){
	if (preg_match_all("/(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/\~\+#]*[\w\-\@?^=%&amp;\/\~\+#])?/i", $str, $matches)) {
		$patterns = array_unique($matches[0]);//list of unique urls
		
		/**
		 * Convert each url to an html link
		 */
		foreach ($patterns as $p)
			$replacements [] = auto_link($p, 'both', TRUE);
	
		return str_replace($patterns, $replacements, $str);
	}
	
	return $str;
}