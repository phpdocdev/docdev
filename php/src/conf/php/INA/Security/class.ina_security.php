<?php
/**
* INA Security class
* Authors: David Felio, Bob Sanders
* Date: 9/23/2006
*
* -- How to use the INA Security Class --
*
* This object provides several kinds of protection against common attacks.
* Proper use requires an understanding of each kind of attack and proper
* modification of your application. Please read the docs carefully and 
* follow the examples provided.
*
* You will also want to check out class.ina_sanitize.php. This class
* provide functions for sanitizing user-submitted data for many purposes.
* 
* Notes:
* The object sets internalError if database errors are encountered. You must
* Check this property when calling methods that touch the database. See 
* example in has_exceeded_login_attempts()
* 
* 1. CSRF Protection
*    * Add insert_csrf_string_hidden() or insert_csrf_string_get() to all
*      form submissions or get requests in the application. These methods
*      generate a random md5 hash and put it in the session. 
*    * Immediately after session_start(), Call is_valid_csrf($_REQUEST) (?) 
*      to validate the request to ensure it originated from our server. If 
*      this method fails, stop the user and write to the security_log().
*      
* 2. Session Hijack and Fixation Protection
*    * Immediately after session_start(), Call is_valid_hijack_string() to 
*      ensure that requests in this session are coming from the same user. If 
*      this method fails, stop the user and write to the security_log().
*    * Immediately after authenticating the user, call logged_in($un) to
*      insert into the logins table, update the last login time, set an 
*      anti-hijack string and regenerate the session id
* 
* 3. Password security
* 	This object provides several kinds of password protection. Many functions
* 	require an auth_source parameter, which represents the "authentication source" 
* 	for the application. Applications that use LDAP should pass 'ldap' as the value.
* 	Applications that use a private database should pass a descriptive name
* 	consisting of the database and table name (i.e. 'database.table').
* 
* 	* Stopping brute force and password guessing attacks
* 	  See example in has_exceeded_login_attempts
* 
* 	* Auto aging rules
* 	  Use set_last_change and needs_password_change to force password changed
*    Use account_disabled to see if an account is too old to be used
* 
* 	* Strong passwords
* 	  See example in prettyPassword
* 	  
* 	* Safe password reset function
* 	  TODO: users should be emailed a one-time-use password or time limited
* 	        hash that will force setting of a new password.
*/


class ina_security {

	public function __construct($webservice=false) {
		$opts=file('INA/Security/options.txt', 1);
		foreach ($opts as $opt) {
			if (preg_match('/^deploy/', $opt)) {
				continue;
			}
			list($key,$var)=explode('=', $opt);
			define($key, trim($var));
		}

		$this->web_service = $webservice;
		$this->pw_len_min=8;
		$this->pw_len_max=16;
		$this->csrf_secret_string='inaSecure';
		$this->hijack_secret_string='inaHijackSecure';
		$this->pass_secret_string='inaPassHash';
		$this->md5_string='JSBc@c@cAo';
		$this->bad_pass_chars=array("'", '"', '\\', '<', '>', '[', ']');

		$this->max_login_attempts=10;
		$this->login_attempt_window=30; # in minutes
		$this->pass_max_age=90; # in days
		$this->pass_reuse_limit=180; # in days
		$this->pass_reuse_cycle=10; # depth of lookback to prevent password re-use
		$this->reset_time_limit=60; # in minutes
		$this->login_inactivity=546; # in days
		$this->internalError=false;

		require_once('DB.php');
		$this->ina_secure_db = DB::connect(INA_SECURITY_DSN);
		if (PEAR::isError($this->ina_secure_db)) {
			$this->security_log(__FILE__, __LINE__, $this->ina_secure_db->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		$this->ina_secure_db->setFetchMode(DB_FETCHMODE_ASSOC);
	}

	/*
	*  internal function, creates the anti-csrf tracking string
	*/ 
	function __make_csrf_string() {
		$_SESSION['ina_sec_csrf']=md5(uniqid(rand(), true) . $this->csrf_secret_string);
	}

	/*
	*  inserts a hidden variable containing the anti-csrf tracking string
	*  Use this for form submissions
	*/
	function insert_csrf_string_hidden() {
		if (!$_SESSION['ina_sec_csrf']) {
			$this->__make_csrf_string();
		}
		return "<input type=hidden name='ina_sec_csrf' value=\"$_SESSION[ina_sec_csrf]\">";
	}


	/**
	* inserts a hidden variable containing the anti-csrf tracking string
	* Use this for get requests
	* @return String csrf_string
	*/
	function insert_csrf_string_get() {
		if (!$_SESSION['ina_sec_csrf']) {
			$this->__make_csrf_string();
		}
		return "ina_sec_csrf=$_SESSION[ina_sec_csrf]";
	}

	/**
	*  validates the anti-csrf string in the session against the one passed
	*  in the last form submission or get request
	*
	*  TODO: this fails if no session var exists, is that what we want?
	*  @return boolean is_valid
	*/
	function is_valid_csrf(&$gpc) {
		if ($_SESSION['ina_sec_csrf']) {
			if ($gpc['ina_sec_csrf'] === $_SESSION['ina_sec_csrf']) {
				return true;
			} else {
				return false;
			}
		} else {
			$this->__make_csrf_string();
			return true;
		}
	}

	/*
	*  internal function, creates anti-hijack string
	*/
	function __make_hijack_string() {
		return md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT_CHARSET'] . $this->hijack_secret_string);
	}
	
	/**
	*  If an anti-hijack string exists in the session, checks it against
	*  the current anti-hijack string, or inserts if it doesn't exist.
	* @return boolean $is_valid is_valid
	*/
	function is_valid_hijack_string() {
		$current_hijack_string = $this->__make_hijack_string();
		if ($_SESSION['ina_sec_hijack']) {
			if ($_SESSION['ina_sec_hijack'] === $current_hijack_string) {
				return true;
			} else {
				return false;
			}
		} else {
			$_SESSION['ina_sec_hijack']=$current_hijack_string;
			return true;
		}
	}


	/*
	*  Gets user-appropriate descriptions of password rules
	*/
	function get_pass_rules() {
		$rules= array(
			'Your password cannot contain your username.',
			'Your password cannot contain a reversed version of your username.',
			'Your password cannot contain the following characters: ' . join ($this->bad_pass_chars, ' '),
			'Your password must be at least ' . $this->pw_len_min. ' characters long.',
			'Your password cannot be more than ' . $this->pw_len_max. ' characters long.',
			'Your password must contain at least 1 uppercased letter, 1 lowercased letter, and at least 1 number or punctuation mark.',
			'The letters in your password cannot be a word found in the dictionary or a common password.',
		);
		if ($this->pass_reuse_limit > 0) {
			$rules[]='You may not change to a password you have had for this site in the past ' . $this->pass_reuse_limit . ' days.';
		} else {
			$rules[]='You may not change to a password you have had for this site in the past.';
		}
		if ($this->pass_reuse_cycle > 0) {
			$rules[]='You may not change to a password that is one of the previous ' . $this->pass_reuse_cycle . ' passwords you have had for this site.';
		}
		return $rules;
	}

	/*
	*  Generates user interface for setting a new, safe password.
	*  Includes DHTML/Ajax
	*  for real-time rule validation. 
	*  
	*  Parameters
	*     un: pass the username/login you are validating
	*     no_css: pass a true value to supply your own CSS style
	*     submitButtonName:
	*     submitButtonValue: the name and value of the button used 
	*                        to submit the form.
	*
	*  Example usage for "reset password" page:
	*
	*	if( $_POST['current_password'] && $_POST['new_password'] && $_POST['new_password2']){
	* 	// verify current password is correct
	* 	if ($_POST['new_password'] != $_POST['new_password2']) {
	* 		// re-display page w/ non-match error
	* 	}
	* 
	*		list($passOk, $messages) = $this->Security->is_strong_pass(
	*        $_SESSION[user], $_POST['new_password']);
	*     if($passOk){
	*        // change the password in the application
	*        // register the change
	*        $this->Security->set_last_change($_SESSION[user],  'ldap');
	*        return;
	*     }
	*	}
	*	echo $this->Security->pretty_password(
	*		$_SESSION[user], NULL, 
	*		'ac:show:reset_pw:1', "Change Password");
	*/
	function pretty_password($un, $no_css='', 
			$submitButtonName='changePassword', 
			$submitButtonValue='Change Password',
			$requireOriginalPassword=true,
			$displayUserNameTextbox=false) {
		$rules_list='';
		$i=1;
		$min=$this->pw_len_min;
		$max=$this->pw_len_max;
		$un=strtolower($un);
		$revun=strrev($un);
		$submitButtonValue=htmlentities($submitButtonValue);
		$submitButtonName=htmlentities($submitButtonName);
		foreach ($this->get_pass_rules() as $rule) {
			$rules_list .= "<li id=\"pass_rule_$i\"> <span id=\"mark_pass_rule_$i\" class=\"no_flag\">** </span>$rule</li>\n";
			$i++;
		}
		$rules_list .= "<li id=\"pass_rule_$i\"> <span id=\"mark_pass_rule_$i\" class=\"no_flag\">** </span>Your password must be entered a second time, exactly as the first time, to prevent any errors.</li>\n";

		$content = '<script language="javascript" src="'. INA_SECURITY_BASE_STATIC_URL .'/password_check.js"></script>' . "\n";

		if (!$no_css) {
			$content .= '<LINK REL="STYLESHEET" HREF="'. INA_SECURITY_BASE_STATIC_URL . '/password_check.css" TYPE="text/css">' . "\n";
		}

		$un_safe = htmlentities($un);
		$baseurl=INA_SECURITY_BASE_URL;
		if ($requireOriginalPassword) {
			$extra_text='Please enter your current password below for authentication, then enter your new password.';
		} else {
			$extra_text='';
		}
		
		if($displayUserNameTextbox){
			$un_safe_display = '<input type=text class="pass_input" id="pass_user_name" name="pass_user_name" size='.$max.'>';
			$un_safe_js = "document.getElementById('pass_user_name').value";
		}else{
			$un_safe_display = $un_safe;
			$un_safe_js = "'$un_safe'";
		}
		
		
		
		$content .= <<<END_HTML
	<p class=light>
	In an effort to protect your valuable information, we have instituted 
	more secure password requirements. $extra_text
	<div class="pass_rules">
		<p class=light>Your new password must meet the following conditions:
			<ul class="light">
				$rules_list
			</ul>
		</p>
		<p class=light>$extra_text The rules above will indicate when each criteria has been met.</p>
	</div>
	<div class="light">
		<table cellpadding=3 cellspacing=0 border=0 class=pass_entry width="350" align=center>
			<tr>
				<td align=right><span class="pass_title" id="pass_current_title">Username:</span></td>
				<td>$un_safe_display</td>
			</tr>
END_HTML;
if($requireOriginalPassword){
	$content .= <<<END_HTML
			<tr>
				<td align=right><span class="pass_title" id="pass_current_title">Current Password:</span></td>
				<td><input type=password class="pass_input" id="pass_current_input" name="current_password" size=$max></span></td>
			</tr>
END_HTML;
}
	$content .= <<<END_HTML
			<tr>
				<td align=right><span class="pass_title" id="pass_new_title">New Password:</span></td>
				<td><input type=password class="pass_input" id="pass_new_input" name="new_password" size=$max onBlur="check_rules_on_blur($un_safe_js, '$baseurl')" onkeyup="check_rules_on_keyup($un_safe_js, '$min', '$max')"></span></td>
			</tr>
			<tr>
				<td align=right><span class="pass_title" id="pass_new2_title">Repeat New Password:</span></td>
				<td><input class="pass_input" id="pass_new2_input" type=password name="new_password2" size=$max onkeyup="check_pw_match()"></span></td>
			</tr>
			<tr>
				<td colspan=2 align=center>
					<input type="submit" name="$submitButtonName" value="$submitButtonValue" id="pass_submit">
				</td>
			</tr>
		</table>
	</div>				
END_HTML;

		if (is_array($this->rules_results) && count($this->rules_results) > 0) {
			foreach ($this->rules_results as $val) {
				$checklist .= $val ? '1' : '0';
			}
			$content .= "<script language=\"javascript\">
				color_rules('$checklist')
				</script>\n";
		}
		return $content;
	}

	/*
	*  Checks a password against the password rules
	*  Returns array(BOOL, array(messages))
	*    see example in prettyPassword
	*/
	function is_strong_pass($pw, $un, $return_for_ajax=false, $auth_source='ldap') {
		$errors=$this->rules_results=array();
		for($i=1; $i<=count($this->get_pass_rules()); $i++) {
			$this->rules_results[$i]=true;
		}
	
		# does not contain username
		if ($un) {
			if (strpos(strtolower($pw), strtolower($un)) !== false) {
				$this->rules_results[1]=false;
				$errors[]='Your password cannot contain your username.';
			}
			$revun=strrev($un);
			if (strpos(strtolower($pw), strtolower($revun)) !== false) {
				$this->rules_results[2]=false;
				$errors[]='Your password cannot contain a reversed version of your username.';
			}
		}

		# min/max length
		if (strlen($pw) < $this->pw_len_min) {
			$this->rules_results[4]=false;
			$errors[]='Your password must be at least ' . $this->pw_len_min. ' characters long.';
		}
		if (strlen($pw) > $this->pw_len_max) {
			$this->rules_results[5]=false;
			$errors[]='Your password cannot be more than ' . $this->pw_len_max. ' characters long.';
		}

		# at least 1 upper alpha, 1 lower alpha, 1 non-alpha, and no bad chars
		if (!preg_match('/[a-z]/', $pw)) {
			$this->rules_results[6]=false;
			$errors[]='Your password must contain at least 1 lowercased letter.';
		}
		if (!preg_match('/[A-Z]/', $pw)) {
			$this->rules_results[6]=false;
			$errors[]='Your password must contain at least 1 uppercased letter.';
		}
		if (!preg_match('/[0-9!@#$%^&*()+={}|:;_-]/', $pw)) {
			$this->rules_results[6]=false;
			$errors[]='Your password must contain at least 1 number or punctuation mark.';
		}
		foreach ($this->bad_pass_chars as $char) {
			if (strpos($pw, $char) !== false) {
				$this->rules_results[3]=false;
				$errors[]='Your password cannot contain any of the following characters: ' . join ($this->bad_pass_chars) . ' ';
				break;
			}
		}
		
		# dictionary/common password check
		$pw_words=preg_match_all('/([a-zA-Z]+)/', $pw, $matches, PREG_PATTERN_ORDER);
		$safe_pw=$this->ina_secure_db->quote($pw);
		$sql="select word from bad_passwords where word=$safe_pw";
		if ($pw_words == 1) {
			$pw_word=$this->ina_secure_db->quote($matches[1][0]);
			$sql.=" or word=$pw_word";
		}
		$bad_pw=$this->ina_secure_db->getOne($sql);
		if ($bad_pw) {
			$this->rules_results[7]=false;
			$errors[]="Your password cannot contain a dictionary word or a common password ($bad_pw).";
		}

		# check pw history, by time
		$pass_hash=sha1($pw . $this->pass_secret_string);
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_auth=$this->ina_secure_db->quote($auth_source);
		$sql="select count(*) from password_history where password_hash='$pass_hash' and username=$safe_user and auth_source=$safe_auth";
		if ($this->pass_reuse_limit) {
			$last_date=date('Y-m-d', strtotime('-' . $this->pass_reuse_limit . ' days'));
			$sql .= " and date_set >= '$last_date'";
		}
		$repeat_pw=$this->ina_secure_db->getOne($sql);
		if ($repeat_pw) {
			$this->rules_results[8]=false;
			if ($this->pass_reuse_limit) {
				$errors[]='You have used this password in the past ' . $this->pass_reuse_limit . ' days.';
			} else {
				$errors[]='You have previously used this password.';
			}
		}

		# check pw history, by cycle
		if ($this->pass_reuse_cycle) {
			$sql="select password_hash from password_history where username=$safe_user and auth_source=$safe_auth order by date_set desc limit 10";
			$past_passwords = $this->ina_secure_db->getCol($sql);
			if (in_array($pass_hash, $past_passwords)) {
				$this->rules_results[9]=false;
				$errors[]='This password is in your history of your previous ' . $this->pass_reuse_cycle . ' passwords';
			}
		}

		if ($return_for_ajax) {
			$return_string='';
			foreach ($this->rules_results as $val) {
				$return_string .= $val ? '1' : '0';
			}
			return $return_string;
		} else {
			return array(count($errors) == 0, $errors);
		}
	}


	/**
	*  Prevents brute force attacks by limiting the number of failed
	*  password checks in a given time period (currently 30 login attempts
	*  in 30 minutes).
	*
	*	if( $this->Security->has_exceeded_login_attempts($_POST[login_un], 'ldap') ){		
	*		if( $this->Security->internalError == true ){
	*			$this->warn("The application is unable to authenticate you at this time. An administrator has been notified.");
	*			$this->show('home');
	*			$this->stop_application();			
	*		}else{		
	*			$this->Security->security_log(__FILE__, __LINE__, sprintf("Too many logins for '%s'", $_POST[login_un]));
	*			$this->warn("Invalid username or password");
	*			$this->show('home');
	*			$this->stop_application();
	*		}
	*		return;			
	*	}
	* @param String $username username
	* @param String $authSource authSource
	* @return boolean $hasExceeded hasExceeded
	*/
	function has_exceeded_login_attempts($un, $auth_source) {
		if (!$un || !$auth_source) {
			return true;
		}
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_auth_source=$this->ina_secure_db->quote($auth_source);
		
		$this->log_event( $auth_source, "$safe_user attempt login");
		
		# log attempt		
		$sql="insert into login_attempts (username, auth_source, attempt_time) values ($safe_user, $safe_auth_source, NOW())";
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
			$this->internalError=true;
			return true;
		}
		$sql="select last_insert_id()";
		$id=$this->ina_secure_db->getOne($sql);
		if (PEAR::isError($id)) {
			$this->security_log(__FILE__, __LINE__, $id->getDebugInfo() );
			$this->internalError=true;
			return true;
		}
		$this->ina_login_attempt_id = $id;
		

		# count login attempts
		$begin_time=date('Y-m-d H:i:00', strtotime('-' . $this->login_attempt_window . ' minutes'));
		$sql="select count(*) from login_attempts where username=$safe_user and auth_source=$safe_auth_source and attempt_time >= '$begin_time' and (valid_login != 1 or valid_login is null)";
		$attempts=$this->ina_secure_db->getOne($sql);
		$this->log_event( $auth_source, "$safe_user attempts = $attempts");
		if (PEAR::isError($attempts)) {
			$this->security_log(__FILE__, __LINE__, $attempts->getDebugInfo() );
			$this->internalError=true;
			return true;
		}

		# fail if more than x attempts in y minutes
		if ($attempts > $this->max_login_attempts) {
			# if they have successfully reset their password w/in the window, let them through
			$sql="select count(*) from password_change where username=$safe_user and auth_source=$safe_auth_source and change_date >= '$begin_time'";
			$pw_changed=$this->ina_secure_db->getOne($sql);
			if (PEAR::isError($pw_changed)) {
				$this->security_log(__FILE__, __LINE__, $pw_changed->getDebugInfo() );
				$this->internalError=true;
				return true;
			} elseif ($pw_changed) {
				return false;
			}
			
			# mark attempts as suspicious in db
			# go back twice as far to try to catch any additional attempts
			$extra_time=date('Y-m-d H:i:00', strtotime('-' . $this->login_attempt_window * 2 . ' minutes'));
			$sql="update login_attempts set suspect=1 where username=$safe_user and auth_source=$safe_auth_source and attempt_time >= '$extra_time'";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );			
				$this->internalError=true;
				return true;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	*  Checks the password_change table to see when the last password change was
	*  Forces a password change if greater than pass_max_age (e.g. 90 days).
	*  TODO: Forces account disabling if greater than account_max_age 
	* @param String $username username
	* @param String $authSource authSource
	* @return boolean $response response
	*/
	function needs_password_change($un, $auth_source) {
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_auth_source=$this->ina_secure_db->quote($auth_source);
		$sql="select unix_timestamp(max(change_date)) from password_change where username=$safe_user and auth_source=$safe_auth_source";
		$last_date=$this->ina_secure_db->getOne($sql);
		if (PEAR::isError($last_date)) {
			$this->security_log(__FILE__, __LINE__, $last_date->getDebugInfo() );		
			$this->internalError=true;
			return true;
		}
		$max_age_seconds = $this->pass_max_age * 24 * 60 * 60;
		if (!$last_date || date('U') - $last_date > $max_age_seconds) {
			return true;
		} else {
			return false;
		}
	}


	# the url must be complete and include the name expected for the hash key
	# the value of the key will be appended directly to the url
	# e.g. - $url='http://www.example.com/reset_pw.php?resetkey=';
	function send_password_reset($un, $appname, $email, $url, $mail_text='', $issuer='user requested') {
		$hash=sha1(uniqid(rand()));
		$timelimit=$this->reset_time_limit;

		if (!$mail_text) {
			$mail_text=<<<END_MAIL
This email is being sent to you because you indicated that you had forgotten your password. Please use the link below to login and change your password. For your security, this link will only work for the next $timelimit minutes. If you did not indicate that you forgot your password, you may ignore this email. Your password has not been changed and this email has been sent only to $email.

Please login to change your password: $url$hash

If the link above appears on more than 1 line in your email, you may need to copy and paste the link into your web browser.

Thank you.

Arkansas.gov Support Team
END_MAIL;
		} else {
			$mail_text.=<<<END_MAIL

$url$hash

Thank you.

Arkansas.gov Support Team
END_MAIL;
		}

		$safe_user=$this->ina_secure_db->quote($un);
		$safe_appname=$this->ina_secure_db->quote($appname);
		$safe_email=$this->ina_secure_db->quote($email);
		$safe_issuer=$this->ina_secure_db->quote($issuer);
		$sql="insert into password_reset (username, appname, hash_id, date_sent, email, status, issuer) values ($safe_user, $safe_appname, '$hash', NOW(), $safe_email, 'Unused', $safe_issuer)";
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		mail($email, 'Password Information from Arkansas.gov', $mail_text, "From: support@ark.org\r\nReply-To: support@ark.org\r\n");
		return true;
	}
	
	
	function insert_reset_pw_link($appname) {
		if (!$appname) {
			return false;
		}
		return INA_SECURITY_SSO_URL . "?do=forgot_password_form&appname=$appname";
	}
	
	function insert_change_pw_link($appname, $username) {
		if (!$appname || !$username) {
			return false;
		}
		$safe_appname=urlencode($appname);
		$safe_user=urlencode($username);
		return INA_SECURITY_SSO_URL . "?do=change_password_form&appname=$safe_appname&username=$safe_user";
	}
	
	function is_valid_reset_hash($hash) {
		$safe_hash=$this->ina_secure_db->quote($hash);
		$earliest_date=date('Y-m-d H:i:s', strtotime('-' . $this->reset_time_limit . ' hours'));
		$sql="select status, username, appname from password_reset where hash_id=$safe_hash and date_sent >= '$earliest_date'";
		$status=$this->ina_secure_db->getRow($sql);
		if (PEAR::isError($status)) {
			$this->security_log(__FILE__, __LINE__, $status->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		return array($status['status'] == 'Unused', $status['username'], $status['appname']);
	}
	

	/*
	*  Determines if an account should be disabled. 
	*  Currently, accounts unused for 1 year are disabled
	*  Developer can pass in a custom date (e.g. date of 
	*  last filing.)
	*
	*  *pass $date in strtotime parseable format
	*/
	function account_disabled($un, $auth_source, $date='') {
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_auth_source=$this->ina_secure_db->quote($auth_source);

		# allow bypass if succesful pw change in past 24 hours
		$begin_time=date('Y-m-d H:i:00', strtotime('-1 day'));
		$sql="select count(*) from password_change where username=$safe_user and auth_source=$safe_auth_source and change_date >= '$begin_time'";
		$pw_changed=$this->ina_secure_db->getOne($sql);
		
		if ($date) {
			$timediff = time() - strtotime($date);
			$sql="select enabled from logins where username=$safe_user and auth_source=$safe_auth_source";
			$enabled=$this->ina_secure_db->getOne($sql);
			if ($pw_changed && ($enabled !== '0')) {
				# regardless of date, if they just changed their pw and
				# the account has not been explictly disabled then allow them
				return false;
			} else {
				return ($timediff >= ($this->login_inactivity * 86400) || $disabled);
			}
		}
		$sql="select datediff(now(), last_login), enabled from logins where username=$safe_user and auth_source=$safe_auth_source";
		list($diff, $enabled)=$this->ina_secure_db->getRow($sql, array(), DB_FETCHMODE_ARRAY);
		if (PEAR::isError($diff)) {
			$this->security_log(__FILE__, __LINE__, $diff->getDebugInfo() );		
			$this->internalError=true;
			return true;
		}
		$disabled = $enabled === 0 ? true : false;
		
		if ($pw_changed && !$disabled) {
			return false;
		} else {
			return ($diff > $this->login_inactivity || $disabled);
		}
	}
	

	/**
	*  logged_in registers a successul login. Creates a record in logins
	*  if it doesn't exist. Updates last_login to NOW(). Also regenerates
	*  session id and sets anti-hijack session variable
	* @param String $username username
	* @param String $authSource authSource
	* @param String $appName appName
	* @return boolean $response response
	*/
	function logged_in($un, $auth_source, $app_name='') {
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_auth_source=$this->ina_secure_db->quote($auth_source);
		$safe_appname=$this->ina_secure_db->quote($app_name);

		$this->log_event( $app_name ? $app_name : $auth_source, "$safe_user logged_id");
		
		$sql="insert into logins (username, auth_source, last_login, enabled) values ($safe_user, $safe_auth_source, NOW(), 1) on duplicate key update last_login=NOW()";
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		if (preg_match('/^\d+$/', $this->ina_login_attempt_id)) {
			$sql="update login_attempts set valid_login=1 where attempt_id=".$this->ina_login_attempt_id;
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
				$this->internalError=true;
				return false;
			}
		}
		
		if ($app_name) {
			$sql="update password_reset set status='Cancelled' where username=$safe_user and appname=$safe_appname and status='Unused'";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
				$this->internalError=true;
				return false;
			}
		}
		if (!$this->web_service) {
			if (version_compare(phpversion(), '5.1.0', '<')) {
				session_regenerate_id();
			} else {
				session_regenerate_id(true);
			}
			$_SESSION['ina_sec_hijack']=$this->__make_hijack_string();
			$this->__make_csrf_string();
		}
		return true;
	}


	/*
	*  Sets the date/time of the last password change, so that password
	*  aging rules can be applied.
	*  If change is a result of a password reset request, pass the hash key
	*/
	function set_last_change($un, $appname, $date='', $newpass='', $hash='') {
		$app_info=$this->get_app_info($appname);
		$safe_user=$this->ina_secure_db->quote($un);
		$safe_appname=$this->ina_secure_db->quote($appname);
		$safe_auth_source = $this->ina_secure_db->quote($app_info['auth_source']);

		if (!$date) {
			$date=date('Y-m-d H:i:s');
		}
		$sql="insert into password_change (username, auth_source, change_date) values ($safe_user, $safe_auth_source, '$date')";
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		if ($newpass) {
			$pass_hash=sha1($newpass . $this->pass_secret_string);
			$sql="insert into password_history (username, auth_source, password_hash, date_set) values ($safe_user, $safe_auth_source, '$pass_hash', NOW())";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
				$this->internalError=true;
				return false;
			}
		}
		if ($hash) {
			$safe_hash=$this->ina_secure_db->quote($hash);
			$sql="update password_reset set status='Used' where username=$safe_user and appname=$safe_appname and hash_id=$safe_hash";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
				$this->internalError=true;
				return false;
			}
		}
		return true;
	}

	
	function get_app_info($appname) {
		$safe_app=$this->ina_secure_db->quote($appname);
		$sql="select appname, auth_source, SSOfilename, return_url, app_descript, username_label from app_info where appname=$safe_app";
		$info=$this->ina_secure_db->getRow($sql);
		if (PEAR::isError($info)) {
			$this->security_log(__FILE__, __LINE__, $info->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		return $info;
	}


	function get_apps() {
		$sql="select appname, app_descript from app_info where SSOfilename != '' and SSOfilename is not null order by app_descript";
		$apps=$this->ina_secure_db->getAll($sql);
		if (PEAR::isError($apps)) {
			$this->security_log(__FILE__, __LINE__, $apps->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		$applist=array();
		foreach ($apps as $app) {
			$applist[$app['appname']]=$app['app_descript'];
		}
		return $applist;
	}


	/**
	*  writes to the application security log file. This log is monitored
	*  and all issues acted upon. You must pass __FILE__ and __LINE__ as the
	*  first 2 parameters.
	* @param String $file file
	* @param String $line line
	* @param String $message message
	*/
	function security_log($file, $line, $msg) {
		if ($this->web_service) {
			$msg.= ' [Called as web service]';
		}
		error_log(
			sprintf("%s %s %s %s\n", date("r"), $file, $line, $msg), 3, '/var/log/httpd/app_secure'			
		);
	}
	
	/**
	* log a security event
	* @param String $source source
	* @param String $event event
	* @param String $ip ip
	* @param String $user_agent user_agent	
	*/
	function log_event($source, $event, $ip='', $user_agent=''){
		if(!$ip){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		if(!$user_agent){
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
		$sql = sprintf("insert into event_log(source, event, log_date, ip, user_agent)values('%s','%s',NOW(),'%s','%s')",
			($source),
			($event),
			($ip),
			($user_agent)
		);
		$res = $this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->security_log(__FILE__, __LINE__, $res->getDebugInfo() );		
			$this->internalError=true;
			return true;
		}
	}

}