<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_controller_helper {
	protected $CI;

	function __construct(){
		$this->CI =& get_instance();
	}

	function logout($p=NULL){
		session_unset();
		
		$login_page = $p['user_login'] ? $p['user_login'] : '/user/login';
		
		flash('Logout successful.', $login_page);//redirect to home page
	}
	
	function forget_password($id=NULL){
		$id = $id == NULL ? $this->CI->config->item('application_id') : $id;   
		$redirect_page = $this->CI->Security->insert_reset_pw_link($id);
		header("Location: {$redirect_page}");//redirect to reset password page
	}
	
	function login_user($p=NULL) {
		if ($error = $this->exceeded_login_attempts($p))#check login attempts
			$this->CI->validation->error_string = $error;
		else {#if the user has exceeded login attempts don't even try to authenticate any more
			
			$result = $this->CI->User->authenticate();

			if (is_string($result)) #user object
				$this->CI->validation->error_string = $result;
			elseif ($result)
				$this->authenticate($result, $p);
			else #unable to authenticate user, generic message
				$this->CI->validation->error_string = 'Unable to authenticate user.';
		}
	}
	
	function exceeded_login_attempts($p=NULL){
		$username = $p['username'] ? $p['username'] : $_POST['username'];
	
		if ($this->CI->Security->has_exceeded_login_attempts($username, $this->CI->config->item('application_id')) ){		
			if( $this->CI->Security->internalError == true ){
				return "The application is unable to authenticate you at this time. An administrator has been notified.";
			} else {
				$this->CI->Security->security_log(__FILE__, __LINE__, sprintf("Too many logins for '%s'", $username));
				return "Unable to authenticate user.";
			}
		}
	}
	
	function authenticate($user, $p=NULL){
		$token = $this->logged_in($user, $p);
		
		$logged_in_home = $p['user_home'] ? $p['user_home'] : '/user/home';
		
		flash('User logged in successfully.', $logged_in_home.'/'.str_replace('ina_sec_csrf=', '', $token));//tack on csrf string
	}
	
	function logged_in($user, $p=NULL) {
		$_SESSION['user'] = $user;
		
		$username = $p['username'] ? $p['username'] : $user->username;
		
		$this->CI->Security->logged_in($username, $this->CI->config->item('application_id'));//will log user login, regenerate session ID & set hijack string
		
		return $this->CI->Security->insert_csrf_string_get();//create csrf string
	}
	
	function delete_user($p = NULL){
		$user_id 	   = $p['user_id'];
		$user_id_field = $p['user_id_field'];
		
		if (!ctype_digit($user_id))
			flash('Unable to delete user account.', 'user/index');
		else {
			$user_info = $this->CI->User->find($user_id);
			
			if (!$user_info)
				flash('Unable to delete user account.', 'user/index');
			elseif ($user_info->{$user_id_field} == $_SESSION['user']->{$user_id_field})
				flash('Cannot delete your own user account.', 'user/index');
			else {
				if ($result = $this->CI->User->delete($user_id))
					flash('User account successfully deleted.', 'user/index');
				else
					flash('Unable to delete user account.', 'user/index');				
			}
		}
	}
	
	function change_password($p=NULL){
		$application_id = $p['application_id'] ? $p['application_id'] : $this->CI->config->item('application_id');
		
		global $user;
		$change_pw_link = $this->CI->Security->insert_change_pw_link($application_id, $user->username);
		header("Location: {$change_pw_link}");
	}
}