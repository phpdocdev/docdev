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
 * v - spit out value from validation class or data object
 * 
 * @access public
 * @param mixed val_field value field passed (usually validation error, but could be anything)
 * @param mixed edit_field edit field passed in
 * @return mixed value of field that was set, could be either
 */
function v(&$val_field, $edit_field = NULL){
	if ($val_field)//set
		return h($val_field);
	elseif ($edit_field)//set
		return h($edit_field);
	else return NULL;//neither set
}

/**
 * e - show error class for any html tag 
 * 
 * @access public
 * @return string inline html
 */
function e(&$error_field){
	if (($error_field))
		return "class=\"error\"";//return error class, can be used for CSS
}

/**
 * authenticate user - check user access, if not logged in, redirect
 * 
 * @access public
 */
function authenticate_user(){
	if (!user_access('user'))
		flash('You must be logged in to view this page.', '/user/login');

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
		flash('You must be logged in to view this page.', 'user/login');
	
	if (!user_access('superadmin')) //not a superuser
		flash('You do not have access to this content.', 'user/home');
		
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
		case 'superadmin'	: $access = ($user->role == 'admin' OR $user->admin == 1);break;//superadmin
		case 'anonymous'	: $access = (empty($user));break;//no session var
		case 'user'			:
		default				: $access = (!empty($user));//authenticated user
	}
	
	return $access;//true or false
}

function check_hijack(){
	$CI =& get_instance();

	$param = array('ina_sec_csrf' => get_token());

	$sec = new ina_security();

	if ($sec->is_valid_hijack_string($param) == false) {
		session_unset();
		flash('There is a problem with your user authentication, please log in again.', 'user/home');//return friendly error msg
	}
	else return false;
}

function check_csrf(){
	$CI =& get_instance();

	$param = array('ina_sec_csrf' => get_token());

	$sec = new ina_security();

	if ($sec->is_valid_csrf($param) == false) {
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

function html_decode_xml($xmlURL){
	$xmlfile = curl_file_get_contents($xmlURL);

	$xmlfile_stripped = strip_tags($xmlfile);
	
	$xmlfile_decoded = html_entity_decode($xmlfile_stripped);
	
	return trim($xmlfile_decoded);
}

function &error($data){
	return array ('error' => true, 'data' => $data);
}

function curl_file_get_contents($url) {
	if (function_exists(curl_init)) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if (strstr('https', $url)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); 
		}
		
		$res=curl_exec($ch);

		curl_close($ch);
		return $res;
	} else {
		return false;
	}
}

// return first element in associative array
function get_first_val($assoc_array){
	foreach ($assoc_array as $item)
		return $item;
}

function get_first_key($assoc_array){
	foreach ($assoc_array as $key => $item)
		return $key;
}


function array_to_obj($array, &$obj) {
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$obj->$key = new stdClass();
			array_to_obj($value, $obj->$key);
		}
		else {
			$obj->$key = $value;
		}
	}
	return $obj;
}

?>
