<?
/**
 * Ninja PHP MVC Framework.
 *
 * @author Josh Moody <josh@ark.org>
 *
 * Date Written: Feb, 2007
 *
 * {@link https://cvs.ark.org/cvsweb.cgi/ninja-frame/}
 *
 * Yeah, it could be described as bloated, but I prefer to call it "Feature Rich".
 *
 * Provides:  MVC Framework with a bunch of security and form handling helpers.  Incorporates
 *            the INA Global Template System + file based template files.
 *
 * This framework automatically utilizes the ina security class to provide protection from:
 *            - Cross Site Request Forgery
 *            - Session Hijacking
 *            - Brute force login attacks
 *            - All possibly tainted data is automatically escaped with the ina sanitize class prior to display
 *
 * Includes both a public and admin class.
 *
 * For an example of an application written on this framework, check out cvs: {@link https://cvs.ark.org/cvsweb.cgi/ninja-sample/}
 *
 * For a working demonstration, see {@link http://dev.ark.org/ninja-sample/index.php}.
 *
 * @package NinjaPHPFramework
 * @filesource
 */

/**
 * Shared libraries / Dependencies
 *
 * 		PEAR Libraries
 * 			MDB2 w/ MySQL support
 * 		Classes/Libraries from cvs: ina-php-security
 * 			INA Security Class -- class.ina_security.php
 * 			INA Sanitization Class -- class.ina_sanitize.php
 * 			INA Session Encryption -- lib.encrypt_session.php
 */

/**
 * PEAR database abstraction
 */

require_once 'MDB2.php' ;

/**
 * INA Security Class
 *
 * Can be found in cvs: ina-php-security
 */
require_once 'INA/Security/class.ina_security.php' ;

/**
 * INA Sanitization class
 *
 * Can be found in cvs: ina-php-security
 */
require_once 'INA/Security/class.ina_sanitize.php' ;

/**
 * Encrypted Sessions
 * Can be found in cvs: ina-php-security
 */
require_once 'lib.encrypt_session.php' ;

/**
 * Add ninja's directory to the include_path
 */
 define('NINJA_PATH', dirname(__FILE__)) ;
 ini_set('include_path', join(PATH_SEPARATOR, array(ini_get('include_path'), NINJA_PATH))) ;

/**
 * Include library to get the src path
 */
require_once 'set_src_path.php' ;

/**
 * Include function to use old peardb parseDSN function.  The version in MDB2 doesn't support mysql://username@hostname/database format
 */
require_once 'peardb_parse_dsn.php' ;

/**
 * Debugging utility for getting values of variables in a pretty format.
 * @param mixed $var Variable to be displayed
 * @param string $print_type Output format.  Options print_r | var_dump
 * @return mixed Formatted representation of the string.
 */
function print_var($var, $print_type='print_r'){
	echo '<pre style="text-align:left">' ;
	if ($print_type == 'print_r' && ( is_array($var) || is_object($var) ) ){
		if (!$var){
			var_dump($var) ;
		}else{
			print_r($var) ;
		}
	}else{
		var_dump($var) ;
	}
	echo '</pre>' ;
}

/**
 * Provides shortcut for filtering using ina_sanitize class.
 * Acceptable types:
 * - db (requires reference to db object as 3rd param),
 * - html
 * - ldap
 * - url
 * - number
 * - date (str optional 3rd param: date()-compatible format)
 * - name
 * - email
 * - phone (int optional format as 3rd param: [0: strip all non digits | 1: strip all non digits, apply (xxx) xxx-xxxx | 2: strip all non digits, apply xxx-xxx-xxxx])
 * @param mixed $data Data to be filtered
 * @param string $type Type of filtering
 * @param mixed $extra Typically a database handle
 * @return mixed escaped string or array
 */
function filter($data='', $type='html', $extra=false){
	// Instantiate the sanitize class.
	$ina_sanitize = new ina_sanitize() ;

	// Convert type to lower case for comparisons.
	$type = strtolower($type) ;
	switch($type){
		case 'db':
			return $ina_sanitize->recurs_escape_db($data, $extra) ;
			break ;
		case 'html':
			return $ina_sanitize->recurs_escape_html($data) ;
			break ;
		case 'ldap':
			return $ina_sanitize->recurs_escape_ldap($data) ;
			break ;
		case 'url':
			return $ina_sanitize->recurs_escape_url($data) ;
			break ;
		case 'number':
			return $ina_sanitize->make_safe_number($data) ;
			break ;
		case 'date':
			return $ina_sanitize->make_safe_date($data, $extra) ;
			break ;
		case 'name':
			return $ina_sanitize->make_safe_name($data) ;
			break ;
		case 'email':
			return $ina_sanitize->make_safe_email($data) ;
			break ;
		case 'phone':
			return $ina_sanitize->make_safe_phone($data, $extra) ;
			break ;
		default:
			return $ina_sanitize->recurs_escape_html($data) ;
			break ;
	}
}// End sanitization

/**
 * Saves POSTed variables to the session.
 *
 * Useful for multi-screen forms where you need to save previous steps, but
 * aren't quite ready to save anything to the database.
 *
 * @param mixed $vars POSTed variables to save
 * @return array Cleansed versions of the posted variables.
 */
function post2sess($vars) {
	if (is_array($vars)) {
		$values=array();
		foreach ($vars as $var) {
			$_SESSION[$var]=$values[]=$_POST[$var];
		}
		return $values;
	} else {
		$_SESSION[$vars]=$value=$_POST[$vars];
		return $value;
	}
}


/**
 * Cleans up bad ampersand sometimes found in xml.
 * @param string $value
 * @return string Value with corrected ampersand.
 */
function fixAmpersand($value){
	return str_replace('andamp;', '&amp;', $value) ;
}

/**
 * Import readConf
 */
require_once 'ninja_readConf.php' ;

/**
 * Really ugly date validator class I found someplace on the net.  It's obese, but works.
 * @package NinjaPHPFramework
 */
Class CDateValidator {

	var $m_sFormattedDate;
	var $m_sError;

	function CDateValidator() {
	}

	function GetFormattedDate(){
		return($this->m_sFormattedDate);
	}
	function SetFormatedDate($aryDate, $sFormat){

		$sDate = $this->PadSingleDigits($this->GetMonth($aryDate, $sFormat))."/".$this->PadSingleDigits($this->GetDay($aryDate, $sFormat))."/".$this->GuessCentury($this->GetYear($aryDate, $sFormat));
		$this->m_sFormattedDate = $sDate;
	}
	function SetError($sError) {
		$this->m_sError = $sError;
	}
	function GetError() {
		return($this->m_sError);
	}

	function Validate($date){

		$tmpdate = $date;

		$aryFormat = array();

		//Possible date formats
		$aryFormat[] = "m-d-Y";
		$aryFormat[] = "m-d-y";
		$aryFormat[] = "Y-m-d";
		$aryFormat[] = "m-y-d";
		$aryFormat[] = "m-Y-d";
		$aryFormat[] = "d-m-y";
		$aryFormat[] = "d-y-m";
		$aryFormat[] = "d-m-Y";
		$aryFormat[] = "d-Y-m";
		$aryFormat[] = "y-m-d";
		$aryFormat[] = "y-d-m";
		$aryFormat[] = "Y-d-m";

		//Possible fields separators?
		$aryCharsToReplace[] = '\\';
		$aryCharsToReplace[] = '-';
		$aryCharsToReplace[] = '.';
		$aryCharsToReplace[] = ',';
		$aryCharsToReplace[] = ' ';

		//Max days for each month
		$aryMonthDayCount[1] = 31; //January
		$aryMonthDayCount[2] = 29; //February
		$aryMonthDayCount[3] = 31; //March
		$aryMonthDayCount[4] = 30; //April
		$aryMonthDayCount[5] = 31; //May
		$aryMonthDayCount[6] = 30; //June
		$aryMonthDayCount[7] = 31; //July
		$aryMonthDayCount[8] = 31; //August
		$aryMonthDayCount[9] = 30; //September
		$aryMonthDayCount[10] = 31; //October
		$aryMonthDayCount[11] = 30; //November
		$aryMonthDayCount[12] = 31; //December

		$tmpdate = trim($tmpdate);

		//only seperator should be '-'
		foreach ( $aryCharsToReplace as $replaceme ) {
			$tmpdate = str_replace($replaceme, '/', $tmpdate);
		}

		$aryDate =  explode('/', $tmpdate);

		if ( count($aryDate) > 3 ) {
			$this->SetError("Too many fields in date(".$date.")!");
			return(false);
		}

		//just to check
		foreach ( $aryDate as $val ) {
			if ( !is_numeric($val) ) {
				$this->SetError("Non-numeric values in date (".$date.")!");
				return(false);
			}
			if ( strlen($val) == 3 || strlen($val) > 4 ) {
				$this->SetError("Suspicious digit count in date (".$date.")!");
				return(false);
			}

		}

		//Process of elimination:
		//******************************************************************
		//Months...
		if ( $aryDate[0] > 12 ) {
			$this->DeleteArrayValueItem($aryFormat, "m-d-y");
			$this->DeleteArrayValueItem($aryFormat, "m-y-d");
			$this->DeleteArrayValueItem($aryFormat, "m-d-Y");
			$this->DeleteArrayValueItem($aryFormat, "m-Y-d");
		}
		if ( $aryDate[1] > 12 ) {
			$this->DeleteArrayValueItem($aryFormat, "d-m-y");
			$this->DeleteArrayValueItem($aryFormat, "y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "d-m-Y");
			$this->DeleteArrayValueItem($aryFormat, "Y-m-d");
		}
		if ( $aryDate[2] > 12 ) {
			$this->DeleteArrayValueItem($aryFormat, "y-d-m");
			$this->DeleteArrayValueItem($aryFormat, "d-y-m");
			$this->DeleteArrayValueItem($aryFormat, "Y-d-m");
			$this->DeleteArrayValueItem($aryFormat, "d-Y-m");
		}
		//Days...
		if ( $aryDate[0] > 31 ) {
			$this->DeleteArrayValueItem($aryFormat, "d-m-y");
			$this->DeleteArrayValueItem($aryFormat, "d-y-m");
			$this->DeleteArrayValueItem($aryFormat, "d-m-Y");
			$this->DeleteArrayValueItem($aryFormat, "d-Y-m");
		}
		if ( $aryDate[1] > 31 ) {
			$this->DeleteArrayValueItem($aryFormat, "m-d-y");
			$this->DeleteArrayValueItem($aryFormat, "y-d-m");
			$this->DeleteArrayValueItem($aryFormat, "m-d-Y");
			$this->DeleteArrayValueItem($aryFormat, "Y-d-m");
		}
		if ( $aryDate[2] > 31 ) {
			$this->DeleteArrayValueItem($aryFormat, "y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "m-y-d");
			$this->DeleteArrayValueItem($aryFormat, "Y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "m-Y-d");
		}
		//4 digit year check
		if ( strlen($aryDate[0]) == 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "m-d-y");
			$this->DeleteArrayValueItem($aryFormat, "m-y-d");
			$this->DeleteArrayValueItem($aryFormat, "m-d-Y");
			$this->DeleteArrayValueItem($aryFormat, "m-Y-d");
			$this->DeleteArrayValueItem($aryFormat, "d-m-y");
			$this->DeleteArrayValueItem($aryFormat, "d-y-m");
			$this->DeleteArrayValueItem($aryFormat, "d-m-Y");
			$this->DeleteArrayValueItem($aryFormat, "d-Y-m");
			$this->DeleteArrayValueItem($aryFormat, "y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "y-d-m");
		}
		if ( strlen($aryDate[1]) == 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "m-d-y");
			$this->DeleteArrayValueItem($aryFormat, "m-y-d");
			$this->DeleteArrayValueItem($aryFormat, "m-d-Y");
			$this->DeleteArrayValueItem($aryFormat, "d-m-y");
			$this->DeleteArrayValueItem($aryFormat, "d-y-m");
			$this->DeleteArrayValueItem($aryFormat, "d-m-Y");
			$this->DeleteArrayValueItem($aryFormat, "y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "y-d-m");
			$this->DeleteArrayValueItem($aryFormat, "Y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "Y-d-m");
		}
		if ( strlen($aryDate[2]) == 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "m-d-y");
			$this->DeleteArrayValueItem($aryFormat, "m-y-d");
			$this->DeleteArrayValueItem($aryFormat, "m-Y-d");
			$this->DeleteArrayValueItem($aryFormat, "d-m-y");
			$this->DeleteArrayValueItem($aryFormat, "d-y-m");
			$this->DeleteArrayValueItem($aryFormat, "d-Y-m");
			$this->DeleteArrayValueItem($aryFormat, "y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "y-d-m");
			$this->DeleteArrayValueItem($aryFormat, "Y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "Y-d-m");
		}
		if ( strlen($aryDate[0]) != 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "Y-m-d");
			$this->DeleteArrayValueItem($aryFormat, "Y-d-m");
		}
		if ( strlen($aryDate[1]) != 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "d-Y-m");
			$this->DeleteArrayValueItem($aryFormat, "m-Y-d");
		}
		if ( strlen($aryDate[2]) != 4 ) {
			$this->DeleteArrayValueItem($aryFormat, "d-m-Y");
			$this->DeleteArrayValueItem($aryFormat, "m-d-Y");
		}
		//Check for year digit counts
		foreach ( $aryFormat as $val ) {

		}

		//Check for per month maximum days.
		foreach ( $aryFormat as $val ) {

			//Make sure we can get the correct values
			$day = $this->GetDay($aryDate, $val);
			if (!$day){return(false);}
			$month = $this->GetMonth($aryDate,$val);
			if (!$month){return(false);}

			//if ( DayOfMonth is greaterthan MaxDaysInMonth ) {
			if ( $day > $aryMonthDayCount[$month]  ){
				$this->DeleteArrayValueItem($aryFormat, $val);
			}
		}

		//Leap year Check
		foreach ( $aryFormat as $val ) {

			//Make sure we can get the correct values
			$month = $this->GetMonth($aryDate, $val);
			if (!$month){return(false);}
			$day = $this->GetDay($aryDate, $val);
			if (!$day){return(false);}

			if ( $month == 2 && $day == 29 ) {
				//Start figuring out if year is a leap year
				$bLeap = False;

				$iYear = $this->GetYear($aryDate, $val);
				$iYear = $this->GuessCentury($iYear);

				//Divisible by 4 rule
				if ( ($iYear % 4) == 0 ) {
					//Divisible by 100 rule:
					if ( ( $iYear % 100) == 0 ) {
						//Divisible by 400 rule:
						if ( ( $iYear % 400) == 0 ) {
							$bLeap = True;
						}
					} else {
						$bLeap = True;
					}
				}

				if ( !$bLeap ) {
					$this->DeleteArrayValueItem($aryFormat, $val);
				}
			}
		}

		// start: Comment this out
		//echo "Surviving Formats:\n";
		//foreach ( $aryFormat as $val ) {
		//		echo "\t\t$val\n";
		//}
		// end: Comment this out

		//If any formats survived the cut, it can be viewed as a valid date
		if ( count($aryFormat) > 0 ) {
			$this->SetFormatedDate($aryDate, $aryFormat[0]);
			return(true);
		} else {
			$this->SetError("Date fits no acceptable format.");
			return(false);
		}

	}
	function GetDay($aryDate, $sFormat) {

		if ( strpos($sFormat, "d") !== false ) {
			switch (strpos($sFormat, "d")) {
				case (0):
					$return = $aryDate[0];
					break;
				case (2):
					$return = $aryDate[1];
					break;
				case (4):
					$return = $aryDate[2];
					break;
				default:
					$this->SetError("Error in CDateValidator::GetDay");
					return(false);
			}
		}
		return((int)$return);
	}
	function GetMonth($aryDate, $sFormat) {

		if ( strpos($sFormat, "m") !== false ) {
			switch (strpos($sFormat, "m")) {
				case (0):
					$return = $aryDate[0];
					break;
				case (2):
					$return = $aryDate[1];
					break;
				case (4):
					$return = $aryDate[2];
					break;
				default:
					$this->SetError("Error in CDateValidator::GetMonth");
					return(false);
			}
		}
		return((int)$return);
	}
	//Currently not used
	function GetYear($aryDate, $sFormat) {
		if ( strpos(strtolower($sFormat), "y") !== false ) {
			switch (strpos(strtolower($sFormat), "y")) {
				case (0):
					$return = $aryDate[0];
					break;
				case (2):
					$return = $aryDate[1];
					break;
				case (4):
					$return = $aryDate[2];
					break;
				default:
					$this->SetError("Error in CDateValidator::GetYear");
					return(false);
			}
		}

		return((int)$return);
	}

	function GuessCentury($iYear) {
		//if they only entered 2 digits, do some guess work for the century
		if ( strlen((string)$iYear) == 2 || strlen((string)$iYear) == 1 ) {
			if ( $iYear > 30 ) {
				$iYear += 1900;
			} else {
				$iYear += 2000;
			}
		}
		return($iYear);
	}


	function DeleteArrayValueItem(&$ary, $sValue) {
		$aryNew=array();
		foreach ( $ary as $key=>$val ) {
			if ( $val != $sValue ) {
				$aryNew[] = $val;
			}
		}
		$ary = $aryNew;
	}

	function PadSingleDigits($sData) {
		if ( strlen($sData) == 1 ) {
			return("0".$sData);
		} else {
			return($sData);
		}
	}

}//CDateValidator

/**
 * Validates format of a phone number.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 */
function isValidPhone($string){
    if (preg_match('/^\D*(\d{3})\D*(\d{3})\D*(\d{4})$/',$string)) {
        return true ;
    } else {
        return false ;
    }
}

/**
 * Validates format of a URL.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 */
function isValidURL($string){
    if( preg_match( '/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i' ,$string)){
        return true ;
    }else{
        return false ;
    }
}

/**
 * Validates format of an email address.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 */
function isValidEmail($string){
    if (preg_match('/[\w]+@[\w]+.[\w]{2,4}/', $string)) {
        return true ;
    } else {
        return false ;
    }
}

/**
 * Validates format of a date.
 *
 * Utilizes that really ugly date validator class.  Allows common date shortcuts
 * that can be parsed with strtotime().
 * Allowed shortcuts: 'today', 'tomorrow', 'yesterday', 'next week', 'last week', 'next month', 'last month', 'next year', 'last year'
 * @see CDateValidator
 * @param string $string String to test
 * @param bool $allow_shortcuts Should php date short cuts be allowed?
 * @return bool is this a date, or not?
 */
function isValidDate($string, $allow_shortcuts=true){
	if ($allow_shortcuts){
		$shortcuts = array('today', 'tomorrow', 'yesterday', 'next week', 'last week', 'next month', 'last month', 'next year', 'last year') ;
		if (in_array(strtolower($string), $shortcuts)){
			return true ;
		}
	}

	$obDateValidator = new CDateValidator;
	$test = $obDateValidator->Validate($string);

	if ($test){
		return true ;
	} else {
		return false ;
	}
}

/**
 * Validate and format a time value
 * @param string $time Time value to be validated/formatted
 * @param string $format php date format string to be applied.  Defaults to g:i a
 * @return mixed  Formatted time or bool false if invalid.
 *
 * Common Time format strings:
 *		g:i a (12 hour with lower case am/pm, no leading zero for hour) (Default)
 *		g:i A (12 hour with upper case am/pm, no leading zero for hour)
 *		h:i a (12 hour with lower case am/pm, leading zero for hour)
 *		h:i A (12 hour with upper case am/pm, leading zero for hour)
 *		G:i (24 hour, no leading zero for hour)
 *		H:i (24 hour, leading zero for hour)
 * NOTE: Seconds are ignored in this function
 */
function isValidTime($time, $format='g:i a', $badtime=false){
	if ($time == '00:00:00'){
		return $badtime ;
	}

	// Format for saving to mysql
	if ($format == 'mysql'){
		$format = 'H:i:s' ;
	}

	// First, remove anything that doesn't belong in a time field
	$time = preg_replace('/[^pm 0-9\:]/i', '', $time) ;

	// Run regex to validate the time and capture components.
	$timeregex = '/^((?:2[0-3])|(?:[0-1]?\d)):?((?:[1-5][0-9])|(?:0[0-9]))\s*([AP][. ]*M[. ]*)?/i' ;
	if (!preg_match($timeregex, $time, $matches)){
		// Invalid
		return $badtime ;
	}else{
		// Valid, separate time components (hour, minute, am/pm)
		list($allmatches, $h, $m, $am_pm) = $matches ;

		// Get rid of $am_pm if 24 hour time.
		if ($h > 12){
			$am_pm = '' ;
		}
		// Return formatted string
		return date($format, strtotime("$h:$m $am_pm")) ;

	}
}

/**
 * Alias for isValidTime() ;
 * @see isValidTime()
 */
function formatTime($time, $format='g:i a', $badtime=false){
	return isValidTime($time, $format, $badtime) ;
}
/**
 * Validates format of a whole number.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 */
function isWholeNumber ($number){
	if (trim($number) == 'E+'){
		return false ;
	}
	$number = downsize_scientific_notation($number) ;
    if (!isNumber($number)){
        return false ;
    }else{
        $rounded = round($number, 0) ;
        if ($number != $rounded){
            return false ;
        } else {
            return true ;
        }
    }
}

/**
 * When dealing with REALLY BIG NUMBERS, PHP Converts to scientific notation
 * This makes it hard to validate these numbers
 * This function checks to see if the number has been converted
 * to scientific notation - if it has, it divides by 1000 to return a
 * smaller number that can be validated as a number or whole number
 * This allows us to validate numbers as large as 999999999999.49 (Billions)
 */
function downsize_scientific_notation($number){
	if (preg_match('/E\+/', $number)){
		# number was converted to scientific notation
		$number = $number / 1000 ;
	}
	return $number ;
}
/**
 * Validates format of a password.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 * @deprecated Use is_strong_pass() from ina_security instead.
 */
function isValidPass($string){
	// 6 Characters long, no spaces.
	$pattern='/^[\S]{6,}$/' ;

	if (preg_match($pattern, $string)){
		return true ;
	} else {
		return false ;
	}
}

/**
 * Validates format of a number -- Can be whole or decimal.
 * @param string $string String to be checked
 * @return bool  Is this valid or not?
 */
function isNumber ($number, $error='') {
	if (trim($number) == 'E+'){
		return false ;
	}
	$number = downsize_scientific_notation($number) ;
    if (ereg('^[0-9]*\.?[0-9]+$',$number)) {
        return true;
    } else {
        return false ;
    }
}

/**
 * Validates format of a Zip Code. Allows 5-digit or Zip+4 formats.
 * @param string $string String to be checked
 * @return boolean Is this valid or not?
 */
function isValidZip($string){
    if (preg_match('/^\d{5}(-\d{4})?$/', $string)) {
        return true ;
    } else {
        return false ;
    }
}

/**
 * Validates structure of a credit card.
 * @param string $string String to be checked
 * @return array  Boolean valid + Error Message
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
		$cc_error="Please use only a $list.";
		return array(false, $cc_error) ;
	}
	if (strlen($ccnum) < $minlen) {
		$cc_error="Your credit card number has too few digits. Please double check the number.";
		return array(false, $cc_error) ;
	}
	if (strlen($ccnum) > $maxlen) {
		$cc_error="Your credit card number has too many digits. Please double check the number.";
		return array(false, $cc_error) ;
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
		$cc_error="This is not a valid credit card number. Please double check the number.";
		return array(false, $cc_error) ;
	} else {
		$cc_error=0;
		return array(true, $cc_error) ;
	}
}

/**
 * Validate Bank Routing Number using call to AchPay
 *
 *@param string $routing Routing number to verify
 *@return bool
 */
function isValidBankRouting( $routing ){
	if (defined('APP_MODE') && (APP_MODE == 'DEMO' || APP_MODE == 'DEV')){
		// Any 9-digit number will do
		$pattern = '/\d{9}$/' ;
		if(!preg_match($pattern, $routing)){
			return false ;
		}else{
			return true ;
		}
	}else{
		// Use the AchPay tool to validate
		$routing = urlencode($routing) ;
		$URL = 'https://ach.cdc.nicusa.com/achpay/sendMessage.php?portal_id=4&username=argovt&password=alwaysonmymind&method=verifyRouting&cust_routing_num='.$routing;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$URL);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$data = curl_exec($ch);

		$error = curl_error($ch);

		//invalidRouting=The+routing+number+xx+is+not+valid.&method=verifyRouting&status=failure
		//method=verifyRouting&status=success

		if( strpos($data, 'status=success') ){
			return true;
		}else{
			return false;
		}
	}
}

/**
 * Validate Bank Account Number
 *
 * Validate a string as a bank account number. Currently just checks for at least 4 digits.
 * This is the same validation Salestax uses.
 *
 * @param string $routing Routing number to be tested.
 * @param bool Is it valid?
 */
function isValidBankingAccount($account){
	$regex = '/^\d{4,}$/' ;
	return preg_match($regex, $account) ;
}

/**
 * Date formatting
 *
 * @param mixed $timestamp
 * @param bool $isunix Is the timestamp value a unix timestamp?
 * @param string $format PHP Date format to use
 * @return string Formatted in a consistant way.
 *
 * Allows certian date shortcuts that can be parsed with strtotime()
 */
function niceDate($timestamp=false, $isunix=false, $format='n/j/Y'){

	# Allowed date shortcuts.
	$shortcuts = array('today', 'tomorrow', 'yesterday', 'next week', 'last week', 'next month', 'last month', 'next year', 'last year') ;
	if ($timestamp){
		if ($timestamp == '' || $timestamp == '0000-00-00' || $timestamp == '1969-12-31' || $timestamp == '0000-00-00 00:00:00' || $timestamp == '1969-12-31 00:00:00'){
			return '' ;
		}

		if ($isunix){
			return date($format, $timestamp) ;
		}else{
			if (isValidDate($timestamp) || in_array(strtolower($timestamp), $shortcuts)){
				return date($format, strtotime($timestamp)) ;
			} else {
				return $timestamp ;
			}
		}
	}else{
		return '' ;
	}
}

/**
 * Current Date
 * @param string $dateformat PHP date format
 * @return string Formatted date
 *
 * Don't make fun of me for writing a function to get current date!
 *
 * There's a perfectly good reason for doing this:
 *   If working on a date-sensitive application (example: license renewals), use
 *   this function instead of date().  Will enable you to define today's date
 *   <code>define('TODAY', '2007-04-01') ;</code>
 *   to test how the application will respond on different dates.
 *
 * If TODAY isn't defined, will return the output of date().
 *
 */
function curdate($dateformat='m'){
	if (defined('TODAY')){
		$date = strtotime(TODAY) ;
	}else{
		$date = time() ;
	}

	return date($dateformat, $date);
}

/**
 * Save warnings to a session array.
 * @param string $message Warning message.
 * @return array Warnings
 */
function warn($message = NULL, $fieldname = NULL){

	// Save error field names in global context
	if ($fieldname){
		GLOBAL $_errorfields ;
		$_errorfields[] = $fieldname ;
	}

	if (!isset($_SESSION['warnings'])){
		$_SESSION['warnings'] = array() ;
	}

	if ($message) {
		$_SESSION['warnings'][] = $message;
		return $_SESSION['warnings'];
	}else{
		return $_SESSION['warnings'] ;
	}
}

/**
 * Save message to a session array.
 * @param string $message Message message.
 * @return array Message
 */
function message($message = NULL){
	if (!isset($_SESSION['messages'])){
		$_SESSION['messages'] = array() ;
	}

	if ($message) {
		$_SESSION['messages'][] = $message;
		return $_SESSION['messages'];
	}else{
		return $_SESSION['messages'] ;
	}
}

/**
 * Displays warnings and clears the session's warnings array.
 */
function show_warnings(){

	$warnings = $_SESSION['warnings'] ;

	if (is_array($warnings) && sizeof($warnings) > 0){
		echo '<div id="warnings" class="warn fade">' ;
		echo '<ul>' ;
		foreach($warnings as $message){
			echo "<li>$message</li>\n" ;
		}
		echo '</ul>' ;
		echo '</div>' ;
	}
	unset($_SESSION['warnings']) ; // Don't need the warnings anymore once we've printed them.
}

/**
 * Displays JQuery Style warnings and clears the session's warnings array.
 */
function show_jquery_warnings($extra_class = 'ui-state-error ui-corner-all'){

	$warnings = $_SESSION['warnings'] ;

	if (is_array($warnings) && sizeof($warnings) > 0){
		echo '<div id="warnings" class="warn ' . $extra_class . '">' ;
		echo '<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
					<strong>Alert:</strong>';
		echo '<ul>' ;
		foreach($warnings as $message){
			echo "<li>$message</li>\n" ;
		}
		echo '</ul>' ;
		echo '</p>' ;
		echo '</div>' ;
	}
	unset($_SESSION['warnings']) ; // Don't need the warnings anymore once we've printed them.
}

/**
 * Displays JQuery Style message and clears the session's messages array.
 */
function show_jquery_messages($extra_class = 'ui-state-highlight ui-corner-all'){

	$messages = $_SESSION['messages'] ;

	if (is_array($messages) && sizeof($messages) > 0){
		echo '<div id="message" style="padding: 0pt 0.7em; margin-top: 20px;" class="message ' . $extra_class . '">' ;
//		echo '<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>';
		echo '<ul>' ;
		foreach($messages as $message){
			echo "<span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-info\"></span><li>$message</li>\n" ;
		}
		echo '</ul>' ;
		echo '</p>' ;
		echo '</div>' ;
	}
	unset($_SESSION['messages']) ; // Don't need the messages anymore once we've printed them.
}


/**
 * Helper for identifying fields with error in views.
 * @param string $field Name of form field
 * @param mixed $errors Array of field names with errors
 * @return string String denoting a css error class if a field contains errors or blank string if not error.
 */
function field_error($field, $errors=false){
	$ret = 'style="display:none ; width:0 ; height:0"' ;
	if ($errors){
		if(@in_array($field, $errors) ){
			//$ret = ' class="error" ' ;
			$ret = 'style="display:inline; width:auto ; height:auto ;"' ;
		}
	}
	return $ret ;
}

/**
 * Validates format of many common string types.
 * @param array $data Data to be validated
 * @param array rules array of rules.
 * @return mixed Error Messages + fields with error
 *
 * Allowed rules:
 * 		required, min, max, length, match, date, number, wholenumber, phone, zipcode, url, email, regex
 *
 * In addition to using common validators, you can also define a custom regex (uses preg_match).  If using a custom regex,
 * you can also specify 'regex_warning' for a custom warning message to the user if the regex isn't matched.  Otherwise, the error will
 * return a generic "{$label} is not the correct format, please try again."
 *
 * Specify 'label' for each element to display a friendly name for the element.  Label will default to an uppercase version of the field name.
 *
 * See example below:
 * <code>
 * #Example Usage:
 * $rules = array (
 * 		'name' => array (
 * 			'required' => true,
 * 			'max' => 75,
 * 			'label' => 'Name',
 * 		),
 *
 * 		'dob' => array (
 * 			'required' => true,
 *          'date' => true,
 * 			'max' => 10,
 * 			'label' => 'Birth Date',
 * 		),
 *
 *		// There's already a built-in 'zipcode' validator... this is just to show an example of using regexes with the validator.
 *		'myzip' => array (
 *			'regex' => '/^\d{5}(-\d{4})?$/',
 *			'regex_warning' => 'Zipcode must be in the format 99999 or 99999-9999',
 *			'Zip Code'
 *		),
 * 	) ;
 * $form_errors = validate($values, $rules) ;
 * </code>
 */
function validate($data, $rules) {
	# Get external validation functions.

	# Validate the rules
	foreach ($rules as $field => $r) {
		$r['label'] ? $label = $r['label'] : $label = ucfirst($field);

		if ($r['required'] == true && empty($data[$field]) && $data[$field] != '0') {
			$errors[$field][] = "{$label} is a required field.";
			continue;//move on to next iteration
		}

		if ($r['regex'] && $data[$field] && !preg_match($r['regex'], $data[$field])){
			if ($r['regex_warning']){
				$errors[$field][] = $r['regex_warning']; // Allow a custom warning message for regex validation
			}else{
				$errors[$field][] = "{$label} is not the correct format, please try again.";
			}
		}

		if ($r['min'] && (strlen($data[$field]) < $r['min']))
			$errors[$field][] = "{$label} should be at least {$r['min']} character" . ($r['min'] == 1 ? '' : 's') . ", please try again.";

		if ($r['max'] && (strlen($data[$field]) > $r['max']))
			$errors[$field][] = "{$label} cannot be more than {$r['max']} character" . ($r['max'] == 1 ? '' : 's') . ", please try again.";

		if ($r['length'] && $data[$field] && (strlen($data[$field]) != $r['length'])){
			$errors[$field][] = "{$label} must be {$r['length']} character" . ($r['length'] == 1 ? '' : 's') . ", please try again.";
		}

		if ($r['match'] && ($data[$field] != $data[$r['match']])) {
			$errors[$field][] = "{$label} does not match.";
		}

		if ($r['inlist'] && $data[$field] && (!in_array($data[$field], $r['inlist']))) {
			$errors[$field][] = "{$label} must be one of " . join(', ', $r['inlist']) . ".";
		}

		if ($r['date'] == true && $data[$field] && !isValidDate($data[$field]))
			$errors[$field][] = "{$label} is not a valid date.";

		if ($r['time'] == true && $data[$field] && !isValidTime($data[$field]))
			$errors[$field][] = "{$label} is not a valid time.";

		if ($r['number'] == true && $data[$field] && !isNumber($data[$field]))
			$errors[$field][] = "{$label} is not a valid number.";


		if ($r['range'] == true && $data[$field] && (!isNumber($data[$field]) || $data[$field] < $r['range']['min'] || $data[$field] > $r['range']['max']) ){
			if ($r['range_warning']){
				$errors[$field][] = $r['range_warning']; // Allow a custom warning message for range validation
			}else{
				$errors[$field][] = "{$label} must be a number between {$r['range']['min']} and {$r['range']['max']}.";
			}
		}

		if ($r['wholenumber'] == true && $data[$field] && !isWholeNumber($data[$field]))
			$errors[$field][] = "{$label} is not a valid whole number.";

		if ($r['phone'] == true && $data[$field] && !isValidPhone($data[$field]))
			$errors[$field][] = "{$label} is not a valid U.S. phone number.";

		if ($r['zipcode'] == true && $data[$field] && !isValidZip($data[$field]))
			$errors[$field][] = "{$label} is not a valid Zip Code.";

		if ($r['url'] == true && $data[$field] && !isValidURL($data[$field]))
				$errors[$field][] = "{$label} is not a valid URL.";

		if ($r['email'] == true && $data[$field] && !preg_match('/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i', $data[$field]))
			$errors[$field][] = "{$label} is not a valid email address.";

		if ($r['bankrouting'] == true && $data[$field] && !isValidBankRouting($data[$field]))
				$errors[$field][] = "{$label} is not a valid Routing Number.";

		if ($r['bankaccount'] == true && $data[$field] && !isValidBankingAccount($data[$field]))
				$errors[$field][] = "{$label} is not a valid Account Number.";

		if ($r['creditcard'] == true && $data[$field]){
			list($status, $msg) = ValidCC($data[$field]) ;
				if (!$status){
					$errors[$field][] = "{$label} is not a valid Credit Card Number.";
					//$errors[$field][] = "{$label} $msg";
				}
		}
	}

	return $errors;//will be null if none are found
}
// End validators

/**
 * Replace oddball quotes generated by MS Word with standard UTF-8 characters
 * @param string $string Text to fix
 * @return string Fixed String.
 */
function convert_smart_quotes($string){
	$search = array(chr(145), # Left curly apostrophe
					chr(146), # Right curly apostrophe
					chr(147), # Left curly quotation mark
					chr(148), # Right curly quotation mark
					chr(150), # En dash
					chr(151),  # Em dash
					'&reg;',   # Trademark
					chr(174), # Trademark
				);

	$replace = array("'",
					 "'",
					 '"',
					 '"',
					 '-',
					 '-',
					 '&#174;',
					 '&#174;');

	return str_replace($search, $replace, $string);
}

/**
 * Changes form fields with array as field name (eg 'Person[phone]') into valid id property
 * @param string name Name of field
 * @param mixed value Value to be appended to id
 * @return string Valid id property
 */
/*
function get_id_from_name($name, $value = null){
	echo $name . ":" . $value . '<br />';
	// check to see if we have an array variable for a field name
	$id = $name ;
	if (strstr($name, '[')){
		$id = str_replace(array('[]', '][', '[', ']'), array((($value != null) ? '_'.$value : ''), '_', '_', ''), $name);
	}

	return $id;
}
*/

function get_id_from_name($name, $value = null){
	// check to see if we have an array variable for a field name

	if ($value != null){
		$id = $name . "_$value" ;
	}else{
		$id = $name ;
	}

	if (strstr($id, '[')){
		$id = str_replace(array('[]', '][', '[', ']'), array((($value != null) ? '_'.$value : ''), '_', '_', ''), $id);
	}



	return $id;
}

/**
 * Creates a "<label>" tag
 * @param string name Name of html element
 * @param string title Title to display
 * @return string Completed label element.
 */
function makeLabel($name, $title=''){
	$id = get_id_from_name($name) ;
	return "<label for=\"$id\">$title</label>" ;
}

/**
 * Creates an HTML button.
 * @param string $name Name of element
 * @param string $value Value of element
 * @param mixed $warning Message to be passed via JavaScript confirm() function when button clicked.
 * @return string Finished html of form element.
 */
function button($name, $value='', $warning=false){

	if ($warning){
		$confirm = "onClick=\"" . "return confirm('$warning')" . '"' ;
	} else {
		$confirm = '' ;
	}
	$ret = '<input type="submit" name="'.$name.'" value="'.$value.'" '.$confirm."/>\n";

	return $ret;
}

/**
 * Creates an HTML hidden field.
 * @param mixed $name Name of element OR array of all parameters needed by function.
 * @param string $value Value of form element
 * @return string Finished html of form element.
 */
function makeHidden($name, $value=''){
	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}
	if (!$id){
		$id = get_id_from_name($name) ;
	}

	if (html_entity_decode($value, ENT_QUOTES) == $value){
		$value = htmlentities($value, ENT_COMPAT, 'ISO-8859-1') ;
	}

	return "<input type=\"hidden\" id=\"$id\" name=\"$name\" value=\"$value\" />\n";
}

/**
 * Creates an HTML text field.
 * @param mixed $name Name of element OR array of all parameters needed by function.
 * @param string $value Value of form element
 * @param mixed size Size of input
 * @param string max length allowed for input
 * @param mixed $validate
 * @param mixed $error
 * @param mixed extra
 * @return string Finished html of form element.
 */
function makeText($name, $value='', $size=false, $max='100', $validate=false, $error=false, $extra=false) {

	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	if (html_entity_decode($value, ENT_QUOTES) == $value){
		$value = htmlentities($value, ENT_COMPAT, 'ISO-8859-1') ;
	}

	$base="<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\"";
	if ($size) {
		$base.=" size=\"$size\"";
	}
	if ($max) {
		$base.=" maxlength=\"$max\"";
	}
	if ($validate) {
		$ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");' />\n";
	} else {
		$ret=$base." />";
	}
	return $ret;
}

/**
 * Creates an HTML textarea.
 * @param mixed $name Name of element OR array of all parameters needed by function.
 * @param string $value Value of form element
 * @param string $rows Number of rows
 * @param string $cols Number of columns
 * @return string Finished html of form element.
 */
function makeTextArea($name, $value='', $rows='5', $cols='50') {
	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	if (html_entity_decode($value, ENT_QUOTES) == $value){
		$value = htmlentities($value, ENT_COMPAT, 'ISO-8859-1') ;
	}

	$ret="<textarea name=\"$name\" id=\"$id\" rows=\"$rows\" cols=\"$cols\">$value</textarea>\n";

	return $ret;
}

/**
 * Creates an HTML select (dropdown box).
 * @param mixed $name Name of element OR array of all parameters needed by function
 * @param array $values Array of values/labels for the select box
 * @param mixed $current Pre-selected value
 * @param mixed $js Javascript.  Example $js="onClick='return validate()'"
 * @param mixed $title Text to appear as first option in select box.
 * @return string Finished html of form element.
 */
function makeSelectBox($name, $values=array(), $current=false, $js=false, $title=false){

	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	$base="<select name=\"$name\" id=\"$id\"" ;

	if ($js){
		$base .= $js ;
	}

	$base .= ">\n" ;

	$options = '' ;

	if ($title){
		$options = "<option value=\"\">$title</option>\n" ;
	}

	foreach ($values as $value=>$label){
		$selected = '' ;
		if ($value == $current){
			$selected = 'selected="selected"' ;
		}

		//$value = htmlentities($value) ;
		$options.= "<option $selected value=\"$value\">$label</option>\n" ;
	}

	$ret = $base . $options . "</select>\n" ;
	return $ret ;
}

/**
 * Creates an HTML Checkbox.
 * @param mixed $name Name of element OR array of all parameters needed by function
 * @param array $values Array of values/labels for the select box
 * @param mixed $current Currently selected checkbox options.
 * @return string Finished html of form element.
 */
function makeCheckbox($name, $values=array(), $current=array(), $js=array()){

	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}

	$i=1 ;

	$js_event = '';

	if($js){
		foreach($js as $k=>$v){
			$js_event .= "$k = \"$v\"";
		}
	}

	foreach($values as $value=>$label){

		$id = get_id_from_name($name, $i) ;

		if (in_array($value, $current)){
			$checked="checked=\"checked\"" ;
		}else{
			$checked = "" ;
		}

		$ret .= "<label for=\"$id\"><input type=\"checkbox\" name=\"$name\" id=\"$id\" value=\"$value\" $checked $js_event/>$label</label>" ;
		$i++ ;
	}

	return $ret ;
}

/**
 * Creates an HTML radio button.
 * @param mixed $name Name of element OR array of all parameters needed by function
 * @param array $values Array of values/labels for the select box
 * @param string $current Currently selected radio button.
 * @return string Finished html of form element.
 */
function makeRadio($name, $values=array(), $current='', $divider=''){

	if( is_array($name) ){
		$name_array = $name ;
		$name = '' ;
		foreach($name_array as $k=>$v){
			$$k = $v ;
		}
	}

	$i=1 ;

	foreach($values as $value=>$label){
		$id = get_id_from_name($name, $i) ;

		if ($value == $current){
			$checked="checked=\"checked\"" ;
		}else{
			$checked = "" ;
		}

		$ret .= "<label for=\"$id\"><input type=\"radio\" name=\"$name\" id=\"$id\" value=\"$value\" $checked />$label</label>$divider\n" ;
		$i++ ;
	}

	return $ret ;
}

/**
 * Creates an HTML select (drop down box) with AR counties
 * @param mixed $name Name of element
 * @param mixed $current Pre-selected value
 * @param mixed $id ID of element
 * @param mixed $js Javascript.  Example $js="onClick='return validate()'"
 * @return string Finished html of form element.
 */

function ninjaSelectCounty($name, $value=false, $id=false, $js=false, $title=false){
	$counties = array(
		"Arkansas",
		"Ashley",
		"Baxter",
		"Benton",
		"Boone",
		"Bradley",
		"Calhoun",
		"Carroll",
		"Chicot",
		"Clark",
		"Clay",
		"Cleburne",
		"Cleveland",
		"Columbia",
		"Conway",
		"Craighead",
		"Crawford",
		"Crittenden",
		"Cross",
		"Dallas",
		"Desha",
		"Drew",
		"Faulkner",
		"Franklin",
		"Fulton",
		"Garland",
		"Grant",
		"Greene",
		"Hempstead",
		"Hot Spring",
		"Howard",
		"Independence",
		"Izard",
		"Jackson",
		"Jefferson",
		"Johnson",
		"Lafayette",
		"Lawrence",
		"Lee",
		"Lincoln",
		"Little River",
		"Logan",
		"Lonoke",
		"Madison",
		"Marion",
		"Miller",
		"Mississippi",
		"Monroe",
		"Montgomery",
		"Nevada",
		"Newton",
		"Ouachita",
		"Perry",
		"Phillips",
		"Pike",
		"Poinsett",
		"Polk",
		"Pope",
		"Prairie",
		"Pulaski",
		"Randolph",
		"Saline",
		"Scott",
		"Searcy",
		"Sebastian",
		"Sevier",
		"Sharp",
		"St. Francis",
		"Stone",
		"Union",
		"Van Buren",
		"Washington",
		"White",
		"Woodruff",
		"Yell"
	);

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	if (!$title){
		$title = 'Choose' ;
	}
	?>
		<select name="<?=$name?>" id="<?=$id?>" <?=($js ? $js : '' )?>>
			<option value="" <?=(!$value) ? 'selected="selected" ' : ''?>><?=$title?></option>
			<?
				foreach($counties as $a){
					?>
						<option value="<?=$a?>" <?=($a==$value)?'selected="selected"':''?>><?=$a?></option>
					<?
				}
			?>
		</select>
	<?
}

/**
 * Creates an HTML select (drop down box) with US States.
 * @param mixed $name Name of element
 * @param mixed $current Pre-selected value
 * @param mixed $id ID of element
 * @param mixed $js Javascript.  Example $js="onClick='return validate()'"
 * @return string Finished html of form element.
 */
function selectState($name, $value=false, $id=false, $js=false, $title=false){
	$states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'NE' => 'Nebraska',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NV' => 'Nevada',
		'NY' => 'New York',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WI' => 'Wisconsin',
		'WV' => 'West Virginia',
		'WY' => 'Wyoming',
	);

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	if (!$title){
		$title = 'Choose' ;
	}
	?>
		<select name="<?=$name?>" id="<?=$id?>" <?=($js ? $js : '' )?>>
			<option value="" <?=(!$value) ? 'selected="selected" ' : ''?>><?=$title?></option>
			<?
				foreach($states as $a=>$n){
					?>
						<option value="<?=$a?>" <?=($a==$value)?'selected="selected"':''?>><?=$n?></option>
					<?
				}
			?>
		</select>
	<?
}

/**
 * Creates an HTML select (drop down box) with times of day in 15 min increments.
 * @param string $name Name of element
 * @param string $default Default value.
 * @return string Finished html of form element.
 */
function timeSelect($name='time', $default='8:00 AM'){

	if ($default == ''){
		$default = '8:00 AM' ;
	}

	$hours[] = "12" ;
	for ($i=1; $i<12; $i++){
		$hours[]=$i ;
	}

	$minutes = array("00", "15", "30", "45") ;

	if (!$id){
		$id = get_id_from_name($name) ;
	}

	echo "<select id=\"$id\" name=\"$name\" >" ;

	foreach ($hours as $hour){
		foreach ($minutes as $minute){
			$time = $hour . ":" . $minute . " AM";
			if ($time == $default){
				echo "<option selected=\"selected\">$time</option>" ;
			}else{
				echo "<option>$time</option>" ;
			}
		}
	}

	foreach ($hours as $hour){
		foreach ($minutes as $minute){
			$time = $hour . ":" . $minute . " PM";
			if ($time == $default){
				echo "<option selected=\"selected\">$time</option>" ;
			}else{
				echo "<option>$time</option>" ;
			}
		}
	}

	echo "</select>" ;
}

/**
 * Determines if a radio button/checkbox should be checked.
 * @param mixed $field Value of field
 * @param mixed $value Comparison value.
 * @return string 'checked' or ''
 */
function checked($field=false, $value=1) {
	if ($field == $value) {
		return 'checked';
	} else {
		return '';
	}

}
// End Form Helpers

/**
 * Build an "update" sql query from an array.
 * @param string $table Name of table
 * @param array $parts Key=>Value pairs
 * @param string $condition Update condition, ie: "where name='Josh'"
 * @return string sql string.
 *
 * This function escapes parameters with mysql_real_escape_string, so they don't need to be
 * escaped prior to sending thru this function.
 */
function makeUpdate($table, $parts, $condition){
	$sql = "update $table set ";

	while( list($field,$val) = each($parts)){
		$sql.="$field = " . _evaluate($val) . ", ";
	}

	$sql = ereg_replace(", $", "", $sql);

	if($condition){
		$sql.=' where ' . $condition;
	}
	return $sql;
}

/**
 * Build an "insert into" sql query from an array.
 * @param string $table Name of table
 * @param array $parts Key=>Value pairs
 * @return string sql string.
 *
 * This function escapes parameters with mysql_real_escape_string, so they don't need to be
 * escaped prior to sending thru this function.
 */
function makeInsert($table, $parts){
	$sql = "insert into $table (";
	$sql2 = '' ;
	while( list($field,$val) = each($parts)){
		$sql.=$field . ', ';
		$sql2.= _evaluate($val) . ", ";
	}

	$sql = ereg_replace(", $", "", $sql);
	$sql2 = ereg_replace(", $", "", $sql2);

	return $sql . ')values(' . $sql2 . ')';
}

/**
 * Build a "replace into" sql query from an array.
 * @param string $table Name of table
 * @param array $parts Key=>Value pairs
 * @return string sql string.
 *
 * This function escapes parameters with mysql_real_escape_string, so they don't need to be
 * escaped prior to sending thru this function.
 */
function makeReplace($table, $parts){
	$sql = "replace into $table (";

	while( list($field,$val) = each($parts)){
	  $sql.=$field . ', ';
	  $sql2.= _evaluate($val) . ", ";
	}

	$sql = ereg_replace(", $", "", $sql);
	$sql2 = ereg_replace(", $", "", $sql2);

	return $sql . ')values(' . $sql2 . ')';
}

/**
 * Allows you to use mysql formulas when building query.  Also provides escaping.
 * @param string $val Value to be evaluated
 * @return string Evaluated string
 */
function _evaluate($val){
	if( ereg('FORMULA: ', $val) ){
		return substr($val, 9);
	}else{
		$val = mysql_real_escape_string($val);
		return "'$val'";
	}
}

/**
 * Templating
 *
 * @todo I don't think I'm providing access to all of the template vars used in the global template system.  Need to check on that.
 *
 * This provides a templating mechanism that is compatible with both
 * the INA GLobal Template system and template files.
 *
 * For template files, use php variables instead of any funky place holder
 * characters.
 * 		Example: <div><?=$body?></div>
 *
 * Templates from the Global Template system will work as usual.
 * @package NinjaPHPFramework
 *
 */
class Template{
	var $vars; // Holds all the template variables.

	/**
	 * @param mixed $file Global Template DSN or template file path.
	 */
	function Template($file = null){
		$this->file = $file ;

		# Define standard template variables used by the Global Template System.
		$this->gtemplate_vars = array('@image_path@'=>'image_path',
									  'STARTFORM'=>'start_form',
						 			  'ENDFORM'=>'end_form',
						 			  '<%% $BODY %%>'=>'body',
						 			  'PAGETITLE' => 'pagetitle',
						 			  'COMPANYNAME' => 'companyname',
						 			  'WELCOMETXT' => 'welcometxt') ;

		$this->Replacements = array() ;
	}

	/**
	 * Fetch a template
	 * @param mixed $file Template object to parse/fetch
	 * @return string Completed html for template.
	 */
	function fetch($file = NULL){
		if(!$file) $file = $this->file;

		# Extract passed variables into local scope.
		@extract($this->vars) ;

		# Is this a database or file dsn?
		if (preg_match('/^my/i', $file)) {
			require_once('MDB2.php') ;
			$parts=explode(":", $file) ;

			$dsn = '';
			for ($i=0; $i<(sizeof($parts)-1); $i++){
				$dsn_parts[] = $parts[$i];
			}

			# Split mysql dsn and template name
			$dsn = join(':', $dsn_parts) ;
			$file_key = $parts[sizeof($parts)-1] ;
			# Connect to database
	 		# There's a bug in MDB2's parseDSN feature. We'll parse it into an array using the old PEAR::DB function
		 	$dsn = peardb_parseDSN($dsn) ;
			$file_db = MDB2::connect($dsn) ;
			$file_db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE) ;

			if (PEAR::isError ($file_db)){
				die ('Cannot connect to template db.') ;
			}

			# Get template information from database.
			list($file_content, $image_path)=$file_db->queryRow("select template, image_path from gtemplate where name='$file_key'");

			if (strlen($file_content) < 1){
				die ('Specified template cannot be found.'.$file_content) ;
			}

			$this->set('image_path', $image_path) ;
			$file_content = str_replace('<p class="headers">Search Results</p>', '', $file_content) ;

			# Replace GTemplate vars with php vars.
			foreach($this->gtemplate_vars as $k=>$v){
				$file_content = str_replace($k, $this->vars[$v], $file_content) ;
			}

			return $file_content ;
		} else {
			# Template from file
			ob_start() ;
			ob_implicit_flush(0) ;
			include($file);
			$file_content = ob_get_clean() ;
			if (strlen($file_content) < 1){
				die ('Specified template cannot be found.'.$file) ;
			}

			# Replace GTemplate vars with php vars.
			foreach($this->gtemplate_vars as $k=>$v){
				$file_content = str_replace($k, $this->vars[$v], $file_content) ;
			}

			return $file_content ;
		}
	}

	/**
	 * Set the value of a template variable.
	 * @param string $name Name of template variable
	 * @param mixed $value Value of template variable
	 */
	function set($name, $value) {
		//$this->vars[$name] = (is_object($value) && $value instanceof Template) ? $value->fetch() : $value;
		$this->vars[$name] = (is_object($value) && is_a($value, 'Template')) ? $value->fetch() : $value;
	}
}
// End Template Class

/**
 * LDAP utility class
 *
 * For the ninja framework, I'm only using it to verify users, but it has many
 * more features that could be utilized if needed.
 *
 * @package NinjaPHPFramework
 */
class PortalAuth{

	function PortalAuth(){
		$this->ldap_host = 'ldap.ark.org:389';
		$this->ldap_rootdn = "o=INA,c=US";
		$this->portal_ou = 'ou=PortalId';
		$this->portal_dn = $this->portal_ou . ',' . $this->ldap_rootdn;
		$this->ds = NULL;
	}

	function connect(){
		if(!$this->ds){
			$this->ds=ldap_connect($this->ldap_host);
		}
	}

	function update_account($uid){
		// record in the ldap the last_login_time
	}

	function getUserGroups($uid){
		$this->connect() ;

		$filter = "(uid=$uid)";

		$sr = ldap_search($this->ds, $this->ldap_rootdn, $filter);
		$entry = ldap_first_entry($this->ds, $sr);
		$udn = ldap_get_dn($this->ds,$entry);

		$filter = "(&(objectclass=groupofuniquenames)(uniquemember=$udn))";
		$sr = ldap_search($this->ds, $this->ldap_rootdn, $filter, array('cn')); //, 200, 30);
		$info = ldap_get_entries($this->ds, $sr);

		$Groups = array();
		for( $i=0; $i<$info['count']; $i++ ){
			$Groups[$info[$i]['cn'][0]] = $info[$i]['dn'];
		}

		return $Groups ;
	}

	function validate($uid, $pass, $group=''){
		$this->connect();
		$dn = $this->ldap_rootdn;

		// todo check password_needchange on login?
        $pass = trim($pass);

		if(!$uid || !$pass){
			return 'No user name or password supplied';
		}

		// find the user in LDAP
		$sr = ldap_search($this->ds, $this->ldap_rootdn, '(uid='.$uid.')', array('ou', 'cn', 'uniquemember')); //, 200, 30);

		if($sr){
			$entry = ldap_first_entry($this->ds, $sr);
			if($entry){
				$udn = ldap_get_dn($this->ds,$entry);
				$inGroup = false;

				$membership = $this->getUserGroups($uid) ;

				if( is_array($group) ){
					foreach($group as $gr){
						if($inGroup){
							continue;
						}
						if (array_key_exists($gr, $membership)){
							$inGroup = true ;
						}

					}
				}else if($group){
					if (array_key_exists($group, $membership)){
						$inGroup = true ;
					}
				}else{
					$inGroup = true;
				}

				if( $inGroup || !$group ){
					$ret = @ldap_bind($this->ds, $udn, $pass);
					if($ret){
						return 'success';
					}else{
						return 'Invalid password';
					}
				}else{
					return 'Not in group';
				}
			}else{
				// could not find user in ldap (or could not get first entry)
				return 'Invalid user name';
			}
		}else{
			// could not find user in ldap
			return 'Invalid user name';
		}

		return 'unknown error';
	}

	function user_in_group($uid, $group){
		$this->connect();
		$dn = $this->ldap_rootdn;

		if(!$group){
			return false;
		}

		// find the user in LDAP
		$sr = ldap_search($this->ds, $this->ldap_rootdn, '(uid='.$uid.')', array('ou', 'cn', 'uniquemember')); //, 200, 30);

		if($sr){
			$entry = ldap_first_entry($this->ds, $sr);
			if($entry){
				$udn = ldap_get_dn($this->ds,$entry);
				$inGroup = false;

				if( is_array($group) ){
					foreach($group as $gr){
						if($inGroup){
							continue;
						}
						//echo "($gr) - $udn<br>";
						$r = ldap_compare($this->ds, "$gr,$dn", 'uniquemember', $udn);

						if( $r === TRUE ){
							$inGroup = true;
						}

					}
				}else if($group){
					$r = @ldap_compare($this->ds, "$group,$dn", 'uniquemember', $udn);
					//echo "($php_errormsg - $group,$dn)";
					if( $r === TRUE ){
						$inGroup = true;
					}
				}else{
					$inGroup = true;
				}

				if( $inGroup ){
					return true;
				}else{
					return false;
				}
			}else{
				// could not find user in ldap (or could not get first entry)
				return false;
			}
		}else{
			// could not find user in ldap
			return false;
		}

		return false;
	}


	function create_account( $un, $pw, $service, $group, $cn, $sn, $email, $phone='' ){
		$this->connect();
		$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');


		$info["objectclass"]="organizationalPerson";
		$info["objectclass"]="inetOrgPerson";
		$info["cn"] = "Bob";
		$info["sn"] = "Sanders";
		$info["mail"] = "bob@ark.org";
		$info["userpassword"] = "test";
		$info["uid"] = "bob2";
		$info["purpose"] = "test account";
		$info["telephonenumber"] = "123-123-1234";

		return ldap_add($this->ds, 'uid=bob2,ou=PortalId,'.$this->ldap_rootdn, $info);
	}

	function mod_account( $un, $cn, $sn, $email, $phone='' ){
		$this->connect();
		$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');

		$info = array();

		if($cn){	  $info["cn"] = $cn;	}
		if($sn){	  $info["sn"] = $sn;  }
		if($email){	$info["mail"] = $email;  }
		if($pw){	  $info["userpassword"] = $pw;  }
		if($phone){	$info["telephonenumber"] = $phone;	}

		return ldap_mod_replace($this->ds, "uid=$un,".$this->portal_dn, $info);
	}

	function add_service($uid, $service){
		$this->connect();
		$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');

		$attr = array(
			service => $service,
		);

		return ldap_mod_add($this->ds, "uid=$uid,".$this->portal_dn, $attr);
	}

	function delete_service($uid, $service){
		$this->connect();
		$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');

		$attr = array(
			service => $service,
		);

		return ldap_mod_del($this->ds, "uid=$uid,".$this->portal_dn, $attr);
	}


	function send_reminder(){
		// does the user have an email?

		// if NO, return: reminder not possible

		// if YES,
		//   get email
		//   reset to random password
		//   email password to user
		//   set password_needchange flag
	}

//$status = create_account( un, pw, group, purpose, cn, sm, email, phone)
//	status == success or error message
//
//$status = mod_account( un, pw, mod_info )
//	mod_info = cn, sm, email, phone
}

/*
 * Simple interface for the rather lengthy PortalAuth class.
 *
 * @package NinjaPHPFramework
 */

class AuthLDAP extends PortalAuth{

	function AuthLDAP(){
		parent::PortalAuth() ;
	}

	/**
	 * Authenticate a user/pass/group using LDAP.
	 * @param string $user Username
	 * @param string $pass Password
	 * @param string $group LDAP Group
	 */
	function verify($user, $pass, $group='staff'){

		// Call parent validate() method
		$res = $this->validate($user, $pass, $group) ;
		if ($res == 'success'){
			return array(true, 'Success') ;
		}else{
			return array(false, 'Invalid username / password combination') ;
		}
	}
}

// End LDAP Classes.

/**
 * GovPay Session Object
 * @package NinjaPHPFramework
 * @see ninja::GovPayInit
 */
class GovPaySession{
	/**
	 * INA's secure hash.
	 */
	var $affiliateHashId     =  '';

	/**
	 *
	 */
	var $sessionId     		=  '';

	/**
	 * Required. Amount of transaction.
	 */
	var $amount        		=  '';

	/**
	 * Optional. Passed to payment processor as an Appid parameter.
	 */
	var $appId         		=  '';

	/**
	 * Optional. Used to omit the first page - pay method selection.
     *           Allowed values: 'c', 'e', 's' - see services_payment_type table, fee_formula column
     *           or '!c', '!e', '!s' if you want to exclude a given payment method.
     */
	var $payBy         		=  '';

	/**
	 * Optional. Used to go back to the originating application.
	 */
	var $backUrl       		=  '';

	/**
	 * Used to override the default "Exit" button text on receipt page, works both for back_url and exit_url.
	 */
	var $backUrlText   		=  '';

	/**
	 * Services's own unique id number
	 * Usually used to identify session in the calling
	 * application when GovPay sends data back to the application or redirects to application's
	 * receipt screen
	 */
	var $outerUniqueId 		=  '';

	/**
	 * Required. Example: renewals.
	 */
	var $serviceId     		=  '';
	var $text          		=  '';
	var $transnum      		=  '';

	/**
	 * Required. Example: arkbar.
	 */
	var $vendorId      		=  '';

} // End GovPay Session Object

/**
 * The world famous Ninja PHP Framework class.
 *
 * Any of the default class variables can be overridden by declaring in conf.properties
 *
 * @package NinjaPHPFramework
 */
class ninja{

	/**
	 * Name of application to be used in error emails.
	 */
	var $app_name ;

	/**
	 * Database DSN -- Example: mysql://username:password@host/database
	 */
	var $dsn ;

	/**
	 * Default method name.  Defaults to 'index' ;
	 */
	var $default_method ;

	/**
	 * Primary Template
	 *
	 * Two formats available:
	 *		Database DSN: Example - var skin_dsn = 'mysql://username:password@host/ina_templates:template_name' ;
	 *			Uses gtemplate table in chosen database.
	 *		File DSN: Example - var skin_dsn = 'views/themes/mytemplate.php' ;
	 */
	var $skin_dsn ;

	/**
	 * Secure Template - Optional
	 *	@see dsn
	 */
	var $secure_skin_dsn ;

	/**
	 * Optional -- Page body wrapped in inner template before insertion to template.
	 */
	var $inner_template ;

	/**
	 * Base URL for static content.
	 */
	var $image_path ;

	/**
	 * Base URL for secure static content.
	 */
	var $secure_image_path ;

	/**
	 * File where any additional <head> content resides (css, js, etc)
	 */
	var $template_extra_head ;

	/**
	 * URL of public site
	 */
	var $public_url ;

	/**
	 * URL of admin site
	 */
	var $admin_url ;

	/**
	 * URL of admin site
	 */
	var $mail_errors_to ;

	/**
	 * Should we send error emails?
	 */
	var $send_error_email ;

	/**
	 * Display errors on page?
	 */
	var $display_errors ;

	/**
	 * Who do we send mail as?
	 */
	var $send_mail_from ;

	/**
	 * What session name should we use?
	 */
	var $app_session_name ;

	/**
	 * Application Mode: Options: prod, demo, dev
	 */
	var $app_mode ;

	/**
	 * URL of GovPay Web Service
	 * @see GovPayInit
	 */
	var $GPCSessionService ;

	/**
	 * URL Of Gov Pay screens
	 * @see GovPayInit
	 */
	var $GovPayURL ;

	/**
	 * Database Handle for GovPay Database.
	 * @see GovPayInit
	 */
	var $GovPayDB ;

	/**
	 * What kind of authentication: LDAP, DATABASE, SIMPLE
	 */
	var $auth_method ;

	/**
	 * What group must a user be a member of to see this content?
	 *
	 * To allow more than one group, separate list with commas.
	 */
	var $auth_group ;

	/**
	 * Username for SIMPLE auth method
	 */
	var $auth_user ;

	/**
	 * Password for SIMPLE auth method
	 */
	var $auth_pass ;

	/**
	 * Constructor class.
	 * Sets some defaults for class variables and starts the application.
	 */
	function ninja($params = array()){

		# App Info
		$this->app_name 				= 'Ninja Framework' ;
		$this->app_session_name         = 'NINJA' ;
		$this->default_method			= 'index' ;
		$this->safe_methods				= array() ;
		$this->app_mode					= 'dev' ;

		# Database
		$this->dsn 						= false ;

		# Templates
		$this->skin_dsn 				= 'views/themes/yui_template.php' ;
		$this->image_path 				= 'http://dev.ark.org/ninja/themes/yui' ;
		$this->secure_image_path		= false ;
		$this->secure_skin_dsn 			= false ;

		# Template Extras
		$this->inner_template 			= false ;
		$this->template_extra_head 		= false ;

		# URLS
		$this->public_url 				= false ;
		$this->admin_url 				= false ;

		# Error Handling
		$this->mail_errors_to 			= 'josh@ark.org' ;
		$this->send_error_email 		= true ;
		$this->send_mail_from 			= 'support@ark.org' ;
		$this->display_errors			= false ;

		# Security
		$this->security = new ina_security() ;

		# Set default values for class variables.  Can be overriden in conf.properties or in child class
		$this->auth_method 				= 'SIMPLE' ;
		$this->auth_group 				= 'staff' ; 		// Separate multiples with commas
		$this->auth_user 				= 'admin' ;			// Simple username
		$this->auth_pass 				= 'pw4admin' ;		// Simple password
		$this->init($params) ;
	}

	/**
	 * Initializes the application, sets up some defaults.
	 */
	function init($params = array()){

		# Read configuration file
		$conf_params = readConf() ;

		# Combine config file params with params passed from application
		$params = array_merge($conf_params, $params) ;

		# allow the calling app to modify the config before it is loaded
		if( method_exists( $this, '_filterConf' ) ){
			$params = $this->_filterConf($params);
		}

		# Add parameters from child class and conf.properties to class vars.
		if (is_array($params)){
			foreach($params as $k=>$v){
				$this->$k = $v ;
			}
		}

		# I'd like to skip this step, and make all class vars from config keyed with lower case.
		# This should work with most of my apps, but not sure if everyone uses lower case class vars.
		# What this will do is create lower case class vars for every config var.  If var was specified in
		# config with mixed or uppercase, it will end up having two versions.. one lower, and one the way it was specified in conf.
		# In effect, makes config variables case in-sensitive.  There will ALWAYS be a lower case version of each key name.
		if (is_array($params)){
			foreach($params as $k=>$v){
				$k_lower = strtolower($k) ;
				$this->$k_lower = $v ;
			}
		}
		# Sometimes we want to DEFINE params instead of just adding them to class vars.
		# Constants will always be defined with uppercase key
		if (isset($this->define_params)){
			foreach($this->define_params as $k){
				$k_lower=strtolower($k) ;
				if (isset($this->$k_lower)){
					DEFINE(strtoupper($k), $this->$k_lower) ;
				}
			}
		}

		$this->begin_session() ;

		# Globally safe methods.
		$this->global_safe_methods = array('index', 'login', 'logout') ;
		$this->safe_methods = array_merge($this->safe_methods, $this->global_safe_methods) ;

		# Connect to database if dsn given.
		if ($this->dsn){
			$this->db = false ;
			$this->db = $this->dbConnect($this->dsn) ;
		}

		// See if a beforeAction() method has been defined in child class; if so - do it.
		if (method_exists($this, '_beforeAction')){
			call_user_func(array($this,'_beforeAction')) ;
		}

		# Get page action.
		$this->getDirective() ;
	}

	/**
	 * Connect to a database using MDB2
	 *
	 */
	 function dbConnect($dsn, $persistent=true){
	 	# There's a bug in MDB2's parseDSN feature. We'll parse it into an array using the old PEAR::DB function
	 	$dsn = peardb_parseDSN($dsn) ;

		# Use factory() instead of connect() to avoid db object collisions db handles to multiple databases
		$db =& MDB2::factory($dsn, array('persistent'=>$persistent)) ;
		$db->setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'handle_pear_error'));
		$db->setFetchMode(MDB2_FETCHMODE_ASSOC) ;
		$db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE) ;

		return $db ;
	 }

	/**
	 * If no other method is called, this will call the default one.
	 * Normally should display an index page.
	 */
	function show_default(){
		if (method_exists($this, $this->default_method)){
			call_user_func(array($this, $this->default_method)) ;
		}else{
			die ('No index defined') ;
		}
	}

	/**
	 * Default Index Page
	 */
	function index(){
		$this->showPage('views/main.php') ;
	}

	/**
	 * Passes requests from controller to view
	 * @param mixed $output
	 *
	 * Accepts an array that can contain 2 values:
	 * 		$vars -- Array of PHP variables to be made availble in the view and
	 * 		$content -- the view that should be called.
	 * You can also just pass the path of the view to this method if no vars are needed.
	 *
	 * This method filters all vars that may be from user input prior to placement in template
	 * If you want to use un-escaped data for some reason (maybe in a textarea that actually allows html code),
	 * you can use html_entity_decode() within your view.
	 *
	 * Update: Some times you want to use variables in a the view unescaped.
	 * In my mind, using html_entity_decode() in a view violates the MVC model.
	 *
	 * Therefore, I've added a feature that allows you to put variables
	 * in $output['vars_noescape'] array instead of $output['vars'] before calling showPage()
	 *
	 * Note: If there is a variable with the same name in both arrays, the
	 * unescaped version will overwrite the escaped version.
	 *
	 * Just be sure you aren't passing any userinput in vars_noescape!
	 *	 <code>
	 *	 # Exampe usage:
	 *	 $output['vars']['info'] = $_POST['info'] ;
	 *	 $output['vars_noescape']['homelink'] = "<a href=\"http://www.arkansas.gov\">Arkansas.gov</a>" ;
	 *	 $output['content'] = 'views/info.php' ;
	 *	 $this->showPage($output) ;
	 *	 </code>
	 */
    function showPage($output=array(), $blank=false, $return=false){
        if($blank){
            $skin_array = split(':', $this->skin_dsn);
            unset($skin_array[count($skin_array) - 1]);
            $this->skin_dsn = join(':', $skin_array) . ':blank';
        }

        if (!is_array($output)){
			$content = $output ;
			$output = array('content'=>$content) ;
		}
		/*
			Filter all vars that may be from user input prior to placement in template
			If you want to use un-escaped data for some reason (maybe in a textarea that actually allows html code),
			you can use html_entity_decode() within your view.
		*/
		$output['vars'] = filter($output['vars'], 'html') ;

		// Don't escape variables in the $output['vars_noescape'] array.
		if ($output['vars_noescape'] && $output['vars']){
			// Merge escaped and unescaped if both present
			$output['vars'] = array_merge($output['vars'], $output['vars_noescape']) ;
		}elseif($output['vars_noescape']){
			// Only escaped are present.
			$output['vars'] = $output['vars_noescape'] ;
		}

		/* Set some default vars. */
		$output['vars']['public_url'] = $this->public_url ;
		$output['vars']['admin_url'] = $this->admin_url ;

		/* Take care of start/end form template tags. */
		$output['vars']['start_form'] = $this->start_form_tag() ;
		$output['vars']['end_form'] = $this->end_form_tag() ;

		/**
		 * Allow developers to define a method to populate extra template variables
		 * Method should return an associative array of variable name=>value
		 *
		 * Example:
		 * function get_template_vars(){
		 * 		return array('pagetitle'=>'Login', 'pageheader'=>'Please Login') ;
		 * }
		 */
		if (method_exists($this, 'get_template_vars')){
			$extraVars = $this->get_template_vars() ;
			foreach($extraVars as $k=>$v){
				$output['vars'][$k] = $v ;
			}
		}

		/* Outer Template */
		$layout = new Template($this->skin_dsn) ;
		$layout->set('image_path', $this->image_path) ;

		if ($output['vars']){
			foreach($output['vars'] as $k=>$v){
				$layout->set($k, $v) ;
			}
		}

		/* Page Body */
		$body   = new Template($output['content']) ;
		$body->set('image_path', $this->image_path) ;

		if ($output['vars']){
			foreach($output['vars'] as $k=>$v){
				$body->set($k, $v) ;
			}
		}

		/* Inner Template */
		if ($this->inner_template){
			$inner_template = new Template($this->inner_template) ;
			$inner_template->set('image_path', $this->image_path) ;

			if ($output['vars']){
				foreach($output['vars'] as $k=>$v){
					$inner_template->set($k, $v) ;
				}
			}

			/* If using an inner template, set body in it */
			$inner_template->set('body', $body) ;
			$body = $inner_template ;
		}

		/* Set body in layout */
		$layout->set('body', $body) ;

		/* Fetch final page */
		$final = $layout->fetch() ;

		/* Extra HTML for <HEAD> */
		if ($this->template_extra_head){
			$head = new Template($this->template_extra_head) ;
			$head->set('image_path', $this->image_path) ;
			$head_final = $head->fetch() ;
			$final = preg_replace('/<\/HEAD>/is', $head_final . "\n</head>", $final) ;
		}

		if (!defined('NOCSRF')){
			# Insert cross site request forgery protection into all forms.
			$hidden_token = "\n" . $this->security->insert_csrf_string_hidden() ;
			$final = preg_replace("/(<form.*>)/i", "$1$hidden_token\n", $final) ;

			# Insert cross site request forgery protection into app links.
			$token = $this->security->insert_csrf_string_get();
			#$final = preg_replace('/index.php\?*/', "index.php?$token&", $final);
			$final = preg_replace('/\?do:{1}/', "?$token&do:", $final);
			$final = preg_replace('/csrf_token/', $token, $final) ;
		}

		if ($return){
			return $final ;
		}else{
			# Send cache control headers.
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Pragma: no-cache") ;
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			echo $final ;
			exit ;
		}
	}

	/**
	 * Forward user to another page.  Auto appends the CSRF string.
	 *
	 * @param string $url URL to go to
	 * @param string $warning Warning to display prior to redirect
	 * @return void
	 */
	function _forward($url, $warning = false){
        // Fix for "Too many redirects" bug in safari
        if (preg_match('/^\?/', $url)){
            $url = $_SERVER['SCRIPT_NAME'] . $url;
        }

		$url = $this->_appLink($url) ;

		if ($warning){
			warn($warning) ;
		}
		session_write_close() ; // Have to add this, otherwise the header redirect occurs before the session can be saved.

		header("Set-Cookie: " . session_name() . '=' . session_id() . "; path=/") ;
		header("Location: $url") ;
		exit ;
	}

	/**
	 * Forward user to another page.  Auto appends the CSRF string.
	 * Similar the _forward method, except can output a message and uses a meta
	 * refresh tag to wait a couple seconds before redirecting.
	 *
	 * @param string $url URL to go to
	 * @param string $warning Warning to display prior to redirect
	 * @param int seconds to delay
	 * @return void
	 */
	function _flash($url, $warning = false, $delay=2){
		$output['content'] = 'views/flash.php' ;
		$output['vars']['delay'] = $delay ;
		$output['vars']['url'] = $url ;
		$output['vars']['msg'] = $warning ;
		$url = $this->_appLink($url) ;

		$this->showPage($output) ;
	}

	/**
	 * Append CSRF string to a link prior to _forward() ;
	 */
	function _appLink($url){
		$delim = '?' ;
		if (strpos($url, '?') !== false){
			$delim = '&' ;
		}
		$url .= $delim . $this->security->insert_csrf_string_get() ;
		return $url ;
	}

	/**
	 * Route requests.
	 *
	 * Determines what method is being called, and if that method can be called.
	 * Calls method if it can/should. Otherwise calls default method.
	 *
	 * The called method is determined by the presence of $_REQUEST['do:method_name']
	 *
	 * To be called, a method must be defined in $this->safe_methods.
	 *
	 * <code>
	 * # Example:
	 *		require_once 'ninja.php' ; // Import our framework
	 *
	 *		class MyApp extends ninja{
	 *
	 *			function MyApp(){
	 *				$safe_methods = array('main', 'show_info') ;
	 *				$params = array('safe_methods'=>$safe_methods) ;
	 *
	 *				$this->init($params) ;
	 *			}
	 *		}
	 * </code>
	 *
	 * In the above example, the methods main() and show_info() are defined as safe.
	 *
	 * If an un-approved method is called, the application will call default method.
	 */
	function getDirective() {
		$action=false;
        $value=false ;
		foreach($_REQUEST as $var=>$val) {
			if (preg_match('/^do:(.+)$/', $var, $matches)) {
				$params=$matches[1];

				list($action, $value) = explode("=", $params) ;
				break;
			}
		}

		if ($action){
			if (isset($this->safe_methods)){
				if (in_array($action, $this->safe_methods)){
					$is_safe = true ;
				} else {
					$is_safe = false ;
				}
			} else {
				$is_safe = true ;
			}

			if (method_exists($this, $action) && $is_safe){
				if (isset($_SESSION['thisDirective'])){
					$_SESSION['lastDirective'] = $_SESSION['thisDirective'] ;
				}else{
					$_SESSION['thisDirective'] = false ;
				}
				$_SESSION['thisDirective'] = $action ;
				if ($value){
    				$_SESSION['thisDirectiveValue'] = $value ;
	            }else{
	               $_SESSION['thisDirectiveValue'] = false ;
	            }
				call_user_func(array($this, $action)) ;
			}else{
				$_SESSION['thisDirective'] = 'index' ;
				$this->show_default() ;
			}
		}else{
			// No directive exists.	 Show Default Directive.
			$_SESSION['thisDirective'] = 'index' ;
			$this->show_default() ;
		}
	}

	/**
	 * Log application errors.
	 * @param string $error Error message to log
	 *
	 * Displays or emails errors as appropriate for environment.
	 */
	function logError($error, $die=true) {
		# Let just try using part of the error message.  Errors spawned from
		# binary uploads of large files produce to big of an error message.
		$error = substr($error, 0, 2000) ;
		$message = join("\t", array(date('Ymd H:i:s'),$error)) . "\n" ;
		#$backtrace = print_r(debug_backtrace(), true) ;
		#$message .= "\nBack Trace\n" . $backtrace ;

		if ($this->send_error_email){
			mail ($this->mail_errors_to, "Error: " . $this->app_name , $message, "From: " . $this->send_mail_from);
		}
		if ($this->display_errors){
			echo "<pre>$message</pre>" ;
		}

			//echo "<pre>$message</pre>" ;
		if ($die){
			die ("<p>An error occurred, and the Administrator has been notified.  Please try again later.</p>") ;
		}
	}
	/**
	 * Handle PEAR errors.
	 * @param object $error_object PEAR Error object
	 * Catches PEAR errors and routes them to a logger.
	 */
	function handle_pear_error ($error_obj) {
		$message = mysql_errno() . "-" . mysql_error() . "-" . $error_obj->getMessage() . " on " . $error_obj->getDebugInfo() ;
		$this->logError($message) ;
	}

	/**
	 * Begins the session.
	 *
	 * Uses $this->app_session_name as name of session.
	 */
	function begin_session(){
		if (function_exists('db_sess_start')){// Check to see if we using our custom session save handler?
			session_set_save_handler('db_sess_start', 'db_sess_end', 'db_sess_read', 'db_sess_write', 'db_sess_destroy', 'db_sess_gc');
		}

		session_cache_limiter('none') ;
		session_name($this->app_session_name) ;
		session_start() ;
		//printf("N: Start: %s<br>", session_id());

		if (count($_GET) > 0 || count($_POST) > 0){

			if($_REQUEST['do:startOver']){
				return true ;
			}
			if ($_SESSION['ina_sec_csrf']){
				if (!$this->security->is_valid_csrf($_REQUEST)){
					$this->security->security_log(__FILE__ . ':' . $this->app_name, __LINE__,
						sprintf("Invalid CSRF string for user %s, %s != %s",
						$_SESSION['valid_user'],
						$_REQUEST['ina_sec_csrf'],
						$_SESSION['ina_sec_csrf']));
						$link = '<a href="' . htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'ISO-8859-1') . '">Security Check Failed.  Please click here to try again.</a>' ;
						die($link) ;
				}
			}else{
				// Generate a new CSRF string
				$this->security->is_valid_csrf($_REQUEST) ;
			}
		}
	}

	/**
	 * Returns beginning form tag.  Needed for Global Templates.
	 */
	function start_form_tag(){
		return '<form method="POST" id="app_form" name="app_form" action="' . htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'ISO-8859-1') . '" enctype="multipart/form-data">' . "\n" ;
	}

	/**
	 * Returns ending form tag.  Needed for Global Templates.
	 */
	function end_form_tag(){
		return '</form>' ;
	}

	/**
	 * Clear any previous form info from session.
	 */
	function restart(){
		$_GET = False ;
		$_POST = False ;
		$_REQUEST = False ;
		session_destroy() ;
	}

	/**
	 * Initialize a GovPay Object.
	 * - Returns govpay object
	 * - Sets the govpay object's affiliateHashId
	 * - Sets $this->GPCSessionService (url of WSDL)
	 * - Sets $this->GovPayURL (can be used as form action to send user to gov pay)
	 * - Sets $this->GovPayDB (database handle to Gov Pay's Database)
	 * Note: utilizes $this->app_mode to determine the url of the web service and gov pay screen
	 * Usage:
	 *
	 *<code>
	 * $govpayobj = $this->GovPayInit() ;
	 * try {
	 * 		$govpayobj->amount 			= $amount ;
	 * 		$govpayobj->appId  			= $this->app_id ; // Defined in conf.properties
	 * 		$govpayobj->payBy  			= 'c' ;
	 * 		$govpayobj->backUrl 		= $backUrl ;
	 * 		$govpayobj->serviceId 		= $this->service_id  ; // Defined in conf.properties
	 * 		$govpayobj->text			= $this->text ;
	 * 		$govpayobj->transnum		= $transnum ;
	 * 		$govpayobj->vendorID		= $this->vendor_id ; // Defined in conf.properties
	 *
	 * 		$client = new SoapClient($this->GPCSessionService, array('trace'=>1)) ;
	 * 		$r = $client->getSesssionId($govpayobj) ;
	 *		// Redirect user to govpay using $r->sessionNumber and $this->GovPayURL
	 * }
	 * catch (Exception $exception){
	 * 		// Error handling.
	 * }
	 *</code>
	 * See cvs: ina-govpay-collector/docs/readme.txt for more info.
	 */
	function GovPayInit(){
		$govpaysession = new GovPaySession() ;
		$govpaysession->affiliateHashId = '2382938749283749' ;

		switch (strtolower($this->app_mode)){
			case 'prod':
				$this->GPCSessionService = 'https://www.ark.org/govpay/service/Session?WSDL' ;
				$this->GovPayURL = 'https://www.ark.org/govpay/app' ;
				$GovPayDSN = 'mysql://govpay@proddb.ark.org/ina_govpay_collector' ;
				break ;
			default:
				$this->GPCSessionService = 'http://dev.ark.org/govpay/service/Session?WSDL' ;
				$this->GovPayURL = 'https://dev.ark.org/govpay/app' ;
				//$this->GovPayDSN = 'mysql://govpay@db.dev.ark.org/ina_govpay_collector' ;
				$this->GovPayDSN = 'mysql://govpay@testdb.ark.org/ina_govpay_collector' ;
				break ;
		}

		// I don't think we really want to connect to the gov pay database.  It's enough to return the dsn.
		//$this->GovPayDB = $this->dbConnect($GovPayDSN) ;

		return $govpaysession ;
	}

	/**
	* Get methods from child class of Ninja for defining safe methods.
	*
	* Method names not starting with an underscore are considered safe to be called via get/post do: directives.
	*
	* Example:
	* <code>
	* function __construct(){ // or function classname(){ for php4 compatibility
	* 	$safe_methods = array_values($this->_defineSafeMethods()) ;
	* 	$params = array('safe_methods'=>$safe_methods) ;
	* 	parent::ninja($params) ;
	* }
	* </code>
	*
	* @param void
	* @return array Array of class methods considered safe.
	* @see _defineSafeMethodsFilter
	*
	*/
	function _defineSafeMethods(){
		$localMethods = array_diff(
			get_class_methods(get_class($this)),
			get_class_methods('ninjaAdmin'),
			get_class_methods('ninja'));
		$l = array_filter($localMethods, array($this, '_defineSafeMethodsFilter'));
		return $l;
	}

	/**
	 * Filter for method names not starting with an underscore.
	 *
	 * @param string $name Name of method
	 * @return bool Does the method start with an underscore?
	 * @see _defineSafeMethods()
	 */
	function _defineSafeMethodsFilter($name){
		if( strpos($name, '_') === 0 ){
			return false;
		}elseif($name == get_class($this)){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * This method is called for each page load on the admin.
	 * @param mixed $text Any text that should be given to login screen.  Can't use standard "warn()" because session may not exist at this point.
	 *
	 * If user is already logged in, go on.
	 * If user is trying to log in, authenticate them.
	 * Otherwise, display the login page.
	 */
	function login($text=false){
		# Are we using different skin for secure?
		if ($this->secure_skin_dsn){
			$this->skin_dsn = $this->secure_skin_dsn ;
		}

		# Are we using different image path for secure?
		if ($this->secure_image_path){
			$this->image_path = $this->secure_image_path ;
		}


		# Defend against session hijacking.
		if (!$this->security->is_valid_hijack_string()){
				$this->security->security_log(__FILE__, __LINE__,
				sprintf("Invalid session hijack string for user %s, agent %s, charset %s",
				$_SESSION['valid_user'],
				$_SERVER['HTTP_USER_AGENT'],
				$_SERVER['HTTP_ACCEPT_CHARSET']));
				die("Security check failed");
		}

		if ($_SESSION['valid_user']){
			return true ; // Already logged in.
		}elseif (isset($_POST['un'])){

			// $security->max_login_attempts=2; # Test by setting new max attempts here.

			# Has user exceeded max login attempts?
			if ($this->security->has_exceeded_login_attempts($_POST['un'], 'ninja')){
				warn('Too many login attempts.  Account disabled') ;
				$output['content'] = 'views/login.php' ;
				$this->showPage($output) ;
			}

			# Try to validate user.
			$valid = $this->_validate_user() ;
			if (!$valid){
				warn('Invalid Login') ;
				$output['content'] = 'views/login.php' ;
				$this->showPage($output) ;
			}else{
				$this->security->logged_in($_POST['un'], 'ninja', 'ninja') ;
				return true ;
			}
		}else{
			# Send user to login form.
			$this->begin_session() ;
			$output['vars']['note'] = $text ;
			$output['content'] = 'views/login.php' ;
			$this->showPage($output) ;
		}
	}

	/**
	 * Performs logout by reseting SESSION array and clearing cookie.
	 *
	 * Calls login() method afterwards.
	 */
	function logout(){
		# Unset the session
		$_SESSION=array() ;

		# Clear cookie
		unset($_COOKIE[session_name()]);

		# Destroy the session
		session_destroy() ;

		# Show login form.
		$this->login('You have been logged out') ;
	}

	/**
	 * Validates user using whatever auth method is defined in $this->auth_method
	 *
	 * Valid auth methods: LDAP, DATABASE, SIMPLE
	 */
	function _validate_user(){

		$groups = false ; # Valid groups
		$group = false ; # Group that was found for this user.

		if ($this->auth_group){
			// $this->auth_group defined in conf file as comma separated names.
			// Create an array from this value.
			$groupsx = explode(',', $this->auth_group) ;
			$groups = array() ;
			foreach($groupsx as $g){
				$groups[] = trim($g) ;
			}
		}

		switch ($this->auth_method){
			case 'DATABASE':
				list($valid_auth, $group) = $this->_auth_db($_POST['un'], $_POST['pw'], $groups) ;
				break ;
			case 'SIMPLE':
				$valid_auth = $this->_auth_simple($_POST['un'], $_POST['pw']) ;
				break ;
			case 'LDAP':
				list($valid_auth, $msg, $group) = $this->_auth_ldap($_POST['un'], $_POST['pw'], $groups) ;
				break ;
			case 'AGENCYAD':
				list($valid_auth, $msg, $group) = $this->_auth_agencyad($_POST['un'], $_POST['pw'], $groups) ;
				break ;
			default:
				$valid_auth = false ;
				break ;
		}

		// Fall back on LDAP if staff is one of the allowed groups
		if (!$valid_auth && $this->auth_method == 'AGENCYAD' && in_array ( 'staff', $groups)){
			list($valid_auth, $msg, $group) = $this->_auth_ldap($_POST['un'], $_POST['pw'], array('staff')) ;
		}

		if (!$valid_auth){
			return false ;
		}else{
			$this->security->logged_in($_POST['un'], 'ninja') ;
			$_SESSION['valid_user'] = filter($_POST['un'], 'html') ;
			$_SESSION['user_group'] = filter($group, 'html') ;
			return true ;
		}
	}

	/**
	 * Authenticates from INA's Agency AD
	 *
	 * @param string $un Username
	 * @param string $pw Password
	 * @param string $group Default false -- only performs authentication, not authorization if false.
	 */
	function _auth_agencyad($un, $pw, $group=false){
		require_once 'ninja_auth_ad.php' ;

		if (!$un || ! $pw){
			return false ;
		}

		$ADinfo = array('account_suffix' 	 => '@agencyad.ark.org',
						'base_dn'		 	 => 'DC=agencyad,DC=ark,DC=org',
						'domain_controllers' => array('agencydc01.agencyad.ark.org'),
						'ad_username' 		 => 'pwadmin',
						'ad_password' 		 => 'jD5xEcoKQXR71mYccagq',
					);

		$AD=new adLDAP($ADinfo);

		// First, validate user/pass.
		$valid_pass = $AD->authenticate($un, $pw);

		if (!$valid_pass){
			// Invalid username/password
			return array(false, 'Login Failed', false) ;
		}else{
			// User + Pass ok, check group.

			if (!$group){
				// No groups defined.  Just authenticate.
				return array(true, 'Success', false) ;
			}else{
				if (is_array($group)){
					// Check multiple groups. Return true on first match.
					foreach($group as $g){
						$valid_group = $AD->user_ingroup($un, $g) ;

						if ($valid_group){
							return array(true, 'Success', $g) ;
						}
					}
				}else{
					// Checking a single group.
					$valid_group = $AD->user_ingroup($un, $group) ;
					if ($valid_group){
						return array(true, 'Success', $group) ;
					}
				}

				// Unable to authorize with any of the specified groups.
				return array(false, 'Login Failed', false) ;
			}
		}
	}

	/**
	 * Authenticates from INA's LDAP Server.
	 * @param string $un Username
	 * @param string $pw Password
	 * @param string $group Default staff
	 */

	function _auth_ldap($un, $pw, $group='staff'){
		if (!$un || ! $pw){
			return false ;
		}

		$LDAP = new AuthLDAP() ;

		if (is_array($group)){ // Group passed as an array of valid groups.
			foreach($group as $g){
				$g = trim($g) ; // Trim any spaces from group.
				list($valid, $message) = $LDAP->verify($un, $pw, $g) ; // Test each group.

				if ($valid){
					return array($valid, $message, $g) ; // Success!
				}
			}
			return array(false, $message, false) ; // Couldn't validate any group.
		} else { // One group passed as string.
			list($valid, $message) = $LDAP->verify($un, $pw, $group) ;

			if ($valid){
				return array(true, 'Success', $group) ;
			}else{
				return array(false, 'Login Failed', false) ;
			}
		}
	}

	/**
	 * Authenticates from database table.
	 * @param string $un Username
	 * @param string $pw Password
	 * @param string $group Optional group
	 * @return boolean Validated?
	 *
	 * See db/create_users.sql for schema
	 */
	function _auth_db($un, $pw, $group=false){

		if (!$un || ! $pw){
			return false ;
		}

		# Filter username.  No need to filter password, because we run it thru sha1.
		$un_mysql = filter($un, 'db', $this->db) ;

		$group_where = '' ;

		if ($group){
			// Filter group
			$groups = join(", ", filter($group, 'db', $this->db)) ;
			$group_where = 'and `group` in(' . $groups . ')' ;
		}

		# Build query.
		$sql = "select `username`, `group` from `users`
					where `username` = $un_mysql and `password` = '" . sha1($pw) . "' $group_where" ;

		$user = $this->db->queryRow($sql) ;

		if ($user){
			return array(true, $user['group']) ;
		}else{
			return false ;
		}
	}

	/**
	 * Very simple auth method.
	 *
	 * Authenticates username/password using $this->auth_user (default: admin) and
	 * $this->auth_pass (default: pw4admin).  Useful for development or for VERY
	 * lightweight security, like during development.
	 */
	function _auth_simple($un, $pw){
		if (!$un || !$pw){
			return false ;
		}

		# Compare against authentication array.
		if ($this->auth_user == $un && $this->auth_pass == $pw){
			return true ;
		}else{
			return false ;
		}
	}

} // End ninja class.

/**
 * Administrative extension to ninja.
 * @param array $params Extra parameters to use.  Will overwrite params from config file.
 *
 * @package NinjaPHPFramework
 */
class ninjaAdmin extends ninja{

	function ninjaAdmin($params = array()){
		# Call parent's constructor method.
		parent::ninja($params) ;
	}

	/**
	 * Call the login() method before allowing the action.
	 */
	function _beforeAction(){
		$this->login() ;
	}
} // End ninjaAdmin class.
?>
