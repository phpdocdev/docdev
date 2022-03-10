<?php
// INA Security class
// Authors: David Felio, Bob Sanders
// Date: 9/23/2006
//
// -- How to use the INA Sanitize Class --
//
// This object provides several kinds of protection against common attacks.
// Proper use requires an understanding of each kind of attack and proper
// modification of your application. Please read the docs carefully and 
// follow the examples provided.
// 
//    * Call these methods to clean data for any prupose
// 	  make_safe_number
// 	  make_safe_date
// 	  make_safe_name
// 	  make_safe_phone
//    * Call these methods on data prior to us to sanitize for a specific purpose
// 	  recurs_escape_html
// 	  recurs_escape_url
// 	  recurs_escape_ldap
// 	  recurs_escape_db
// 	
// 	See lib.sql.php for safe use of 
// 	makeInsert, makeUpdate and makeReplace

class ina_sanitize {

	function ina_sanitize() {	
		$this->internalError=false;
	}


	/*
	*  Calls the DB's quote recursively on all scalar variables in an array.
	*  You must pass a DB object; you may pass a DB, MDB2, or PDO object.
	*  Note that quote places single quotes around the variable!
	*  This method is not needed if you use makeInsert, makeReplace or makeUpdate
	*/
	function recurs_escape_db($data, &$DB, $skip_keys=array()) {
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				if (in_array($key, $skip_keys)) {
					$safe_db[$key]=$var;
					continue;
				}
				$safe_db[$key]=$this->recurs_escape_db($var, $DB);
			}
		} else {
			return $DB->quote($data);
		}
		return $safe_db;
	}

	/*
	*  Calls htmlentities recursively on all scalar variales in an array
	*  Call this on data you are displaying for the user
	*/
	function recurs_escape_html($data, $skip_keys=array()) {
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				if (in_array($key, $skip_keys)) {
					$safe_html[$key]=$var;
					continue;
				}
				$safe_html[$key]=$this->recurs_escape_html($var);
			}
		} else {
			return htmlentities($data, ENT_QUOTES, 'ISO-8859-1');
		}
		return $safe_html;
	}

	/*
	*  Makes data safe for inserting into LDAP
	*/
	function recurs_escape_ldap($data, $skip_keys=array()) {
		/* A DN may contain special characters which require escaping. 
		*  These characters are , (comma), = (equals), + (plus), < (less than),
		*  > (greater than), ; (semicolon), \ (backslash), and "" (quotation marks). 
		*  In addition, the # (number sign) requires escaping if it is the first 
		*  character in an attribute value
		* http://www-03.ibm.com/servers/eserver/iseries/ldap/underdn.htm
		*/

		/* Conflicting special char listing in the RFC:
			If a value should contain any of the following characters

           Character       ASCII value
           ---------------------------
           *               0x2a
           (               0x28
           )               0x29
           \               0x5c
           NUL             0x00

  		the character must be encoded as the backslash '\' character (ASCII
  		0x5c) followed by the two hexadecimal digits representing the ASCII
  		value of the encoded character. The case of the two hexadecimal
  		digits is not significant.
			http://www.ietf.org/rfc/rfc2254.txt		
		*/
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				if (in_array($key, $skip_keys)) {
					$safe_ldap[$key]=$var;
					continue;
				}
				$safe_ldap[$key]=$this->recurs_escape_ldap($var);
			}
		} else {
			$data=trim($data);
			$data=preg_replace('/^#/', '', $data);
			# use RFC specs:
			return str_replace(array('*', '(', ')', '\\', "\x00"), array('\\2a', '\\28', '\\29', '\\5c', '\\00'), $data);
			# use IBM specs:
			#return preg_replace('/([,=+<>;\\"])/', '\\$1', $data);
		}
		return $safe_ldap;
	}


	/*
	*  Makes data safe for placing in a url
	*/ 
	function recurs_escape_url($data, $skip_keys=array()) {
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				if (in_array($key, $skip_keys)) {
					$safe_url[$key]=$var;
					continue;
				}
				$safe_url[$key]=$this->recurs_escape_url($var);
			}
		} else {
			return rawurlencode($data);
		}
		return $safe_url;
	}



	/*
	* Use for generic number fields
	*/
	function make_safe_number($data) {
		return preg_replace('/\D/', '', $data);
	}

	/*
	*  Restricts dates to actual date values. Applies
	*  date()-compatible formats
	*/
	function make_safe_date($data, $format='m/d/Y') {
		return date($format, strtotime($data));	
		//preg_match('/(\d{1,2})\D+(\d{1,2})\D+(\d{2,4})/', $data, $matches);
		//return $matches[1].'/'.$matches[2].'/'.$matches[3];
	}

	/*
	*  Represents a safe "name" field
	*/
	function make_safe_name($data) {
		return preg_replace("/[^a-zA-Z,.'-]*/", '', $data);
	}


	/*
	* Check for what may be a CC # or SSN 
	*/
	function sensitive_info($data) {
		$cc = preg_match('/[\b\D][3456]\d{3}[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}[\b\D]/', $data);
		$ssn = preg_match('/[\b\D]\d{3}[ -]?\d{2}[ -]?\d{4}[\b\D]/', $data);
		return array (($cc || $ssn), array('CC' => $cc, 'SSN' => $ssn));
	}


	/*
	*  Represents a safe "email" field
	*  NOTE: This may not return a valid email address. Just a safe one
	*/
	function make_safe_email($email) {
		$ipRE="/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/";
		$at=preg_match_all("/@/", $email, $dump);
		if ($at < 1) {
			return false;
		} elseif ($at > 1) {
			$email=preg_replace('/@/', '', $email, $at-1);
		}
		list($user, $host)=split("@", $email);
		if (preg_match('/^[^0-9a-z_]/', $user)) {
			return false;
		}
		$user=preg_replace('/[^0-9a-zA-Z._-]/', '', $user);
		if (!preg_match($ipRE, $host)) {
			$host=preg_replace('/[^0-9a-zA-Z.-]/', '', $host);
		}
		return $user.'@'.$host;
	}

	/*
	*  Represents a safe "phone" field. 
	*  Will apply an optional format:
	*    0: strip all non digits
	*    1: strip all non digits, apply (xxx) xxx-xxxx
	*    2: strip all non digits, apply xxx-xxx-xxxx
	*
	*  Note that this only handles US phone numbers.
	*/
	function make_safe_phone($data, $applyFormat=0) {
		// first, strip all non-digits
		$data = preg_replace('/\D*/', '', $data);
		
		switch($applyFormat){
			case 1:
				return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '(\\1) \\2-\\3', $data);
			case 2:
				return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '\\1-\\2-\\3', $data);
			default:
				return $data;
		}
	}


	function isSpam($content) {
		if(
			preg_match_all('/https?:\/\//', $content, $junk) > 1 || 
			preg_match('/<script/', $content)
		){
			return true;
		} else {
			return false;
		}
	}
}
