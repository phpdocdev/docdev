<?php

class User_access {
	protected $token;
	protected $user_home;
	
	function __construct($p=NULL){
		$this->user_home  = $p['user_home'] ? $p['user_home'] : '/user/home';
		$this->user_login = $p['user_login'] ? $p['user_login'] : '/user/login';  
	} 
	
	/**
	 * authenticate user - check user access, if not logged in, redirect
	 * 
	 * @access public
	 */
	function authenticate_user(){
		$this->_authenticate_user();
	
		$this->verify_user_session();
	}
	
	/**
	 * authenticate admin - check admin access, if not superadmin, redirect
	 * 
	 * @access public
	 */
	function authenticate_admin(){
		$this->_authenticate_user();
		
		if (!$this->user_access('superadmin')) //not a superuser
			flash('You do not have access to this content.', $this->user_home);

		$this->verify_user_session();
	}
	
	/**
	 * authenticate any type of user 
	 */
	function authenticate($user_type, $user_label = ''){
		$this->_authenticate_user();
		
		if (!$this->user_access('superadmin') && !$this->user_access($user_type))
			if ($user_label)
				flash("Only {$user_label} have access to this content.", 'user/home');
			else
				flash('You do not have access to this content.', 'user/home');
			
		$this->verify_user_session();
	}
	
	protected function _authenticate_user(){
		$access = $this->user_access('anonymous');
		if ($access === true){
			flash('You must be logged in to view this page.', $this->user_login);
		}
	}
	
	/**
	 * user access - check user access and return true/false whether or not user has access based on global user object
	 * 
	 * @access public
	 * @param string $type type of user, e.g. superadmin, anonymous or user
	 * @return bool true if user has access, false if user doesn't
	 */
	protected function user_access($type){
		global $user;//get global user information
		
		switch ($type) {
			case 'superadmin'	: $access = ($user->admin);break;//ID of 1 = superadmin
			case 'anonymous'	: $access = (empty($user));break;//no session var
			case 'user'			:
			default				: $access = (!empty($user));//authenticated user
		}

		return $access;//true or false
	}
	
	protected function verify_user_session(){
		$this->set_token();
		
		$this->check_hijack();
		$this->check_csrf();
	}
	
	protected function check_hijack(){
		$CI =& get_instance();
	
		$param = array('ina_sec_csrf' => $this->token);
	
		if ($CI->Security->is_valid_hijack_string($param) == false) {
			session_unset();
			flash('There is a problem with your user authentication, please log in again.', $this->user_login);//return friendly error msg
		}
		else return false;
	}
	
	protected function check_csrf(){
		$CI =& get_instance();
	
		$param = array('ina_sec_csrf' => $this->token);

		if ($CI->Security->is_valid_csrf($param) == false) {
			session_unset();
			
			flash('There is a problem with your user authentication, please log in again.', $this->user_login);//return friendly error msg
		}
		else return false;
	}
	
	protected function set_token(){
		$CI =& get_instance();
		
		return $this->token = $CI->uri->segment_pop();
	}
}
