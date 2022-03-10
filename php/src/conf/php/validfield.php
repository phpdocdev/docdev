<?
/**
* Contains various error checking methods for input validation
* @author   David Felio/INA
*/

class validfield {

	var $cres;

	function validfield () {
	}
	
/**
* Check that a string matches a given regex
*
* @access   public
* @param    string  inputstring
* @param    string  regex
* @param    string  error_message
* @return   string
*/
	function validRegEx ($string, $error='', $regex='') {
		if (is_array($string)) {
			list($string, $error, $regex)=$string;
		}
		if (!$error) {
			$error="The value '$string' is not valid";
		}
		$regex=preg_replace("/\\\\/","\\",$regex);
		if (preg_match("/$regex/", $string)) {
			$this->cres=0;
		} else {
			$this->cres=$error;
		}
		return $this->cres;
	}
// end func validRegEx


/**
* Check that a string contains no digits
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function nodigits ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="The value '$string' can not contain numbers";
		}
		if (ereg("[0-9]",$string)) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func nodigits


/**
* Check that a string contains no letters
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function noalpha ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="The value '$string' can not contain letters";
		}
		if (ereg("[a-zA-Z]",$string)) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func noalpha


/**
* Check that a string is a number
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function isNumber ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="The value '$string' must be a number";
		}
		if (ereg("^[0-9]*\.?[0-9]+$",$string)) { //"
			$this->cres=0;
		} else {
			$this->cres=$error;
		}
		return $this->cres;
	}
// end func isNumber


/**
* Check that a string contains only letters
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function onlyalpha ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="The value '$string' must contain letters only";
		}
		if (ereg("[^A-Z]",$string)) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func onlyalpha


/**
* Check that a string contains no special characters
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function nospec ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="The value '$string' must contain letters and numbers only";
		}
		if (ereg("[^a-zA-Z0-9]",$string)) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func nospec


/**
* Check that a string is a validdate
*
* @access   public
* @param    string  inputstring
* @param    string  postion_of_year
* @param    string  position_of_month
* @param    string  position_of_day
* @param    string  lowest_year
* @param    string  highest_year
* @return   string
*/
	function validDate ($string, $yr='', $mon='', $day='', $bottomyr='', $topyr='') {
		if (is_array($string)) {
			list($string, $yr, $mon, $day, $bottomyr, $topyr)=$string;
		}
		if (!$topyr) {
			$topyr=date("Y");
		}
		if (!$yr && !$mon && !$day) {
			$yr=3; $mon=2; $day=1;
		}
		if (!preg_match("/^\D*(\d*)\D*(\d*)\D*(\d*)\D*$/", $string, $parts)) {
			$temp="'$string' is not a valid date.";
			return $temp;
		}
		if ($parts[$mon] < 1 || $parts[$mon] > 12) {
			$temp="The month in the date '$string' is not valid.";
			return $temp;
		}
		if ($parts[$mon]==1 || $parts[$mon]==3 || $parts[$mon]==5 || $parts[$mon]==7 || $parts[$mon]==8 || $parts[$mon]==10 || $parts[$mon]==12) {
			$maxday=31;
		} elseif ($parts[$mon]==2) {
			$maxday=29;
		} else {
			$maxday=30;
		}
		if ($parts[$day] < 1 || $parts[$day] > $maxday) {
			$temp="The day in the date '$string' is not valid.";
			return $temp;
		}
		if ($parts[$yr] > $topyr) {
			$temp="The year in the date '$string' is too high. The year cannot be higher than $topyr.";
			return $temp;
		} elseif (strlen($parts[$yr])<4) {
			$temp="The year in the date '$string' is too short. Please write the year as 4 digits.";
			return $temp;
		} elseif ($bottomyr && $parts[$yr] < $bottomyr) {
			$temp="The year in the date '$string' is too low. The year cannot be lower than $bottomyr.";
			return $temp;
		}
		return 0;
	}
// end func validDate


/**
* Check that a string is a valid month
*
* @access   public
* @param    string  month
* @param    string  error_message
* @return   string
*/
	function validMon ($mon, $error='') {
		if (is_array($mon)) {
			list($mon, $error)=$mon;
		}
		if (!$error) {
			$error="'$mon' is not a valid month. The month must be a number between 1 and 12.";
		}
		if ($mon < 1 || $mon > 12 || $mon % 1) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func validMon


/**
* Check that a string is a valid day of a month
*
* @access   public
* @param    string  day
* @param    string  month
* @param    string  error_message
* @return   string
*/
	function validDay ($day, $mon='', $error='') {
		if (is_array($day)) {
			list($day, $mon, $error)=$day;
		}
		if ($mon) {
			if ($mon==4 || $mon==6 || $mon==9 || $mon==11) {
				$maxday=30;
			} elseif ($mon==2){
				$maxday=29;
			} else {
				$maxday=31;
			}
		} else {
			$maxday=31;
		}
		if (!$error) {
			$error="'$day' is not a valid day of the month. The day must be a number between 1 and $maxday.";
		}
		if ($day < 1 || $day > $maxday || $day % 1) {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func validDay


/**
* Check that a string is a valid 4-digit year
*
* @access   public
* @param    string  inputstring
* @param    string  lowest_year
* @param    string  highest_year
* @return   string
*/
	function validYr ($yr, $start='', $end='') {
		if (is_array($yr)) {
			list($yr, $start, $end)=$yr;
		}
		if (!$end) {
			$end=date("Y");
		}
		if ($yr > $end) {
			$this->cres="'$yr' is too high. The year cannot be higher than $end.";
		} elseif (strlen($yr)<4) {
			$this->cres="'$yr' is too short. Please write the year as 4 digits.";
		} elseif ($start && $yr < $start) {
			$this->cres="'$yr' is too low. The year cannot be lower than $start.";
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func validYr


/**
* Check that a string is not blank
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function notBlank($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$string && $string != '0') {
			$this->cres=$error;
		} else {
			$this->cres=0;
		}
		return $this->cres;
	}
// end func notBlank


/**
* Check that a string contains a valid social security number
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function validSSN ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="'$string' is not a valid social security number.";
		}
		if (preg_match("/^\d{3}\D*\d{2}\D*\d{4}$/",$string)) {
			$this->cres=0;
		} else {
			$this->cres=$error;
		}
		return $this->cres;
	}
// end func validSSN


/**
* Check that a string contains a valid US phone number
*
* @access   public
* @param    string  inputstring
* @param    string  error_message
* @return   string
*/
	function validPhone ($string, $error='') {
		if (is_array($string)) {
			list($string, $error)=$string;
		}
		if (!$error) {
			$error="'$string' is not a valid US phone number.";
		}
		if (preg_match("/^\d{3}\D*\d{3}\D*\d{4}$/",$string)) {
			$this->cres=0;
		} else {
			$this->cres=$error;
		}
		return $this->cres;
	}
// end func validPhone


/**
* Check that a string contains a valid email address
*
* @access   public
* @param    string  inputstring
* @return   string
*/
	function validEmail ($email) {
		if (is_array($email)) {
			list($email)=$email;
		}
		$email=strtolower($email);
		$userRE='^[0-9a-z_]([-_.]?[0-9a-z.])*$';
		$hostRE='^([0-9a-z]([-]?[0-9a-z])*\.)*$';
		$domRE='^[a-z][a-z](op|fo|g|l|m|me|pa|t|u|v|z)?$';
		$ipRE="/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/";
		$at=preg_match_all("/@/", $email, $dump);
		if ($at != 1) {
			return ("The e-mail address is incorrect. There must be exactly 1 @ sign (such as my_email@aol.com or my_email@state.ar.us). Please double check to make sure you typed it correctly.");
		}
		list($user, $host)=split("@", $email); //"
		if (!eregi($userRE, $user)) {
			return ("The user name (the part before the @ sign) is incorrect. Please double check to make sure you typed it correctly.");
		}
		$per=preg_match_all("/\./", $host, $dump);
		if ($per == 0) {
			return ("The e-mail address is incorrect. There must be at least 1 \".\" after the @ sign (such as my_email@aol.com or my_email@state.ar.us). Please double check to make sure you typed it correctly.");
		}
		if (preg_match($ipRE, $host)) {
			$octets=split(".", $host);
			foreach ($octets as $oct) {
				if ($oct > 255 || $oct < 0) {
					return ("The IP address (the numbers after the @ sign) is not correct. Each number must be a positive number less than 256. Please double check to make sure you typed it correctly.");
				}
			}
			return $this->hostcheck($host);
		} else {
			$temp=strrpos($host, '.');
			$temp++;
			$domain=substr($host, $temp);
			$server=substr($host, 0, $temp);
			if (!ereg($hostRE, $server)) {
				return ("The host name (the part after the @ sign) is not correct. Please double check to make sure you typed it correctly.");
			}
			if (!ereg($domRE, $domain)) {
				return ("The domain (the part after the last \".\") is not correct. Please double check to make sure you typed it correctly.");
			}
			return $this->hostcheck($host);			
		}
	}
// end func validEmail


/**
* Check that a string is an existing host
*
* @access   public
* @param    string  inputstring
* @return   string
*/
	function hostcheck ($host) {
		if (is_array($host)) {
			list($host)=$host;
		}
		if (checkdnsrr($host, 'MX')) {
			return 0;
		} else {
			return ("The host name (the part after the \"@\") is not correct. Please double check to make sure you typed it correctly.");
		}
	}
// end func hostcheck


/**
* Check that a string contains no special characters
*
* @access   public
* @param    string  ccnum1
* @param    string  ccnum2
* @param    string  ccnum3
* @param    string  ccnum4
* @param    string  amex
* @return   string
*/
	function validCC($cc, $cc2='', $cc3='', $cc4='', $amex='') {
		if (is_array($cc)) {
			list($cc, $cc2, $cc3, $cc4, $amex)=$cc;
		}
		if ($cc3) {
			$cc.=$cc2.$cc3.$cc4;
		} else {
			$amex=$cc2;
		}
		if ($amex) {
			$list="Visa, Mastercard, Discover or American Express";
		} else {
			$list="Visa, Mastercard or Discover";
		}
		$ccnum=preg_replace("/\D/", '', $cc);
		if (ereg("^4", $ccnum)) {
			$type='V';
			$minlen=13;
			$maxlen=16;
		} elseif (ereg("^5", $ccnum)) {
			$type='M';
			$maxlen=$minlen=16;
		} elseif (ereg("^6", $ccnum)) {
			$type='D';
			$maxlen=$minlen=16;
		}elseif (ereg("^34", $ccnum) || ereg("^37", $ccnum)) {
			$type='A';
			$maxlen=$minlen=15;
		} else {
			$this->cres="Please use only a $list.";
			return $this->cres;
		}
		if (strlen($ccnum) < $minlen) {
			$this->cres="Your credit card number has too few digits. Please double check the number.";
			return $this->cres;
		}
		if (strlen($ccnum) > $maxlen) {
			$this->cres="Your credit card number has too many digits. Please double check the number.";
			return $this->cres;
		}
		$digits=preg_split('//', $ccnum, 0, PREG_SPLIT_NO_EMPTY);
		$rdigits=array_reverse($digits);
		$i=1;
		$sum=0;
		foreach ($rdigits as $dig) {
			if ($i) {
				$sum+=$dig;
				$i=0;
			} else {
				$tempsum=2*$dig;
				if ($tempsum>9) {
					$temp=preg_split('//', $tempsum, 0, PREG_SPLIT_NO_EMPTY);
					$tempsum=0;
					foreach ($temp as $t) {
						$tempsum+=$t;
					}
				}
				$sum+=$tempsum;
				$i=1;
			}
		}
		if ($sum % 10) {
			$this->cres="This is not a valid credit card number. Please double check the number.";
			return $this->cres;
		} else {
			$this->cres=0;
			return $this->cres;
		}
	}
// end func validCC

}
// end class validfield
?>
