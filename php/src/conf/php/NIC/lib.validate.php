<?
// validate functions ----------------------------
function validate_email($email){
	return true;
}

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
?>