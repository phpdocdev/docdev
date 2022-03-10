<?php

class Uri_sort_helper { 

	static public function get_orderby($sort_array, $default_sort, $uri_param = 'sort'){
		$CI =& get_instance();	
			
		$uri = $CI->uri->uri_to_assoc();
		
		/**
		 * Look for value in uri
		 */
		foreach ((array)$sort_array as $uri_val => $sort_column)
			if ($uri[$uri_param] == $uri_val)
				return $sort_column;
		
		return $default_sort;#or return default
	}
	
	/**
	 * This gets the sort var (not the order by value) from the URI, used for pagination
	 * @return string returns sort param
	 */
	static public function get_sort($default, $uri_param = 'sort'){
		$CI =& get_instance();	
		
		$uri = $CI->uri->uri_to_assoc();
		
		/**
		 * filing date desc is default
		 */
		return $uri[$uri_param] ? $uri[$uri_param] : $default;
	}
	
	/**
	 * This gets the sort direction var from the URI, used for pagination
	 * @return string Returns 'asc' or 'desc' to indicate sort direction
	 */
	static public function get_direction($default = 'desc', $uri_param = 'direction') {
		$CI =& get_instance();	
		
		$uri = $CI->uri->uri_to_assoc();

		if ($default == 'asc')		
			return $uri[$uri_param] == 'desc' ? 'desc' : 'asc';
		else
			return $uri[$uri_param] == 'asc' ? 'asc' : 'desc';
	}
}