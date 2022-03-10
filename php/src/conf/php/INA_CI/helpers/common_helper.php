<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * flash - generate system message for output or set a system message, intended for 1 time messages
 *
 * @param string $message system message
 * @param string $redirect URL to be redirected (if set)
 * @return string returns flash message from session or NULL if there is no message
 */
function flash($message = NULL, $redirect = NULL){
	if ($message) {//set flash message
		session_register('flash');
		$_SESSION['flash'] .= $message;//concat flash messages if more than one

		if ($redirect) redirect($redirect);//if redirect page is specified, redirect
	}
	elseif (isset($_SESSION['flash'])) {//get flash message & immediately delete
		$flash = $_SESSION['flash'];
		unset($_SESSION['flash']);
		session_unregister('flash');//remove flash from session

		return $flash;		
	}
	else return NULL;//session not set
}

/**
 * authenticate user - check user access, if not logged in, redirect
 * 
 * @access public
 */
function authenticate_user(){
	if (!user_access('user'))
		flash('You must be logged in to view this page.(User)', '/user/login');

	check_hijack();
	check_csrf();
}

/**
 * authenticate admin - check admin access, if not superadmin, redirect
 * 
 * @access public
 */
function authenticate_admin(){
	if (user_access('anonymous'))
		flash('You must be logged in to view this page.(Admin)', 'user/login');
	
	if (!user_access('superadmin')) //not a superuser
		flash('You do not have access to this content', 'user/home');
		
	check_hijack();
	check_csrf();
}

/**
 * user access - check user access and return true/false whether or not user has access based on global user object
 * 
 * @access public
 * @param string $type type of user, e.g. superadmin, anonymous or user
 * @return bool true if user has access, false if user doesn't
 */
function user_access($type){
	global $user;//get global user information
	
	switch ($type) {
		case 'superadmin'	: $access = ($user->admin == 1);break;//ID of 1 = superadmin
		case 'anonymous'	: $access = (empty($user));break;//no session var
		case 'user'			:
		default				: $access = (!empty($user));//authenticated user
	}
	
	return $access;//true or false
}

function check_hijack(){
	$CI =& get_instance();

	$param = array('ina_sec_csrf' => get_token());

	if ($CI->Security->is_valid_hijack_string($param) == false) {
		session_unset();
		flash('There is a problem with your user authentication, please log in again.', 'user/home');//return friendly error msg
	}
	else return false;
}

function check_csrf(){
	$CI =& get_instance();

	$param = array('ina_sec_csrf' => get_token());

	if ($CI->Security->is_valid_csrf($param) == false) {
		session_unset();
		flash('There is a problem with your user authentication, please log in again.', 'user/home');//return friendly error msg
	}
	else return false;
}

function get_token(){
	$CI =& get_instance();
	
	global $token;
	
	return $token ? $token : $token = $CI->uri->segment_pop();
}

function h($str){
	return htmlentities($str, ENT_QUOTES, 'ISO-8859-1');  
}

function curl_file_get_contents($url, $p=array()) {
	if (function_exists(curl_init)) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if ($p['timeout'])
			curl_setopt ($ch, CURLOPT_TIMEOUT, $p['timeout']);
		
		if ($p['followlocation'])
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			
		if ($p['header']) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $p['header']);
		}
		
		if (isset($p['ssl_verifypeer'])) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $p['ssl_verifypeer']) ;
		}
		
		if ($p['post']) {
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $p['post']);
		}
		
		$res=curl_exec($ch);

		curl_close($ch);
		return $res;
	} else {
		return false;
	}
}

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

function divide_array_by(&$array, $num = 2){
	$count = count($array);
	
	if (is_array($array))
	   $array = array_chunk($array, ceil($count/$num), true);
	
	$array = array_pad($array, $num, array());
	
	return $array;
}

function get_keywords($var){
	$var = preg_replace('/\s+/', ' ', $var);#trim out extra whitespace
	$var = str_replace(',', '', $var);#trim out comma's
	
	$keywords = explode(' ', $var);
	
	if (is_array($keywords) && $keywords[0]) return $keywords;
}

/**
 * Mysql keyworld filter (open db connection required)
 */
function get_mysql_keyword_filter($keywords, $fields = array()){
	foreach ($keywords as $term) {
		$term = mysql_real_escape_string($term);#escape term
		
		$filter = array();
		
		foreach ($fields as $f)
			$filter [] = "{$f} RLIKE '{$term}'";#look for term in each field specified
		
		$where [] = sprintf("(%s)", join($filter, ' OR '));
	}
	
	return sprintf("(%s)", join($where, ' AND '));#join filters to make where clause
}
