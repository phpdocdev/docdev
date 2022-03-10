<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model_helper {
	protected $CI;

	function __construct(){
		$this->CI =& get_instance();
	}

	function username_check($user_name) {
		if ($this->CI->db->getwhere('user', array('username' => $user_name))->row())
			return "Sorry, but that username already exists, please choose another.";
			
		return TRUE;
	}
	
	function send_password_reset(){
		$site_name = $this->CI->config->item('site_name');
		
		$message = "This email is being sent to you because an administrator has added a new account for you on {$site_name}. " .
				"Please use the link below to login and set your password. For your security, this link will only work for the " .
				"next 2 hours. This email has been sent only to {$_POST['email']}. Please set your password immediately.";//POST vars already sanitized and validated
				
		//email new user link on how to set password
		$this->CI->Security->send_password_reset($_POST['username'], $this->CI->config->item('application_id'), $_POST['email'], $this->CI->config->item('reset_password_url'), $message);		
	}
}