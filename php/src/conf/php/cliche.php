<%
class cliche {

	var $cres;

	function cliche () {
	}
	
	function nodigits ($string, $return=0) {
		if (is_array($string)) {
			foreach ($string as $item) {
				$this->cres=array();
				if ($return) {
					$this->cres[]=ereg_replace("[0-9]","",$string);
				} else {
					$this->cres[]=!(ereg("[0-9]",$string));
				}
			}
		} else {
			if ($return) {
				$this->cres=ereg_replace("[0-9]","",$string);
			} else {
				$this->cres=!(ereg("[0-9]",$string));
				if (!$this->cres) { $this->cres=0; }
			}
		}
		return $this->cres;
	}
	
	function noalpha ($string, $return=0) {
		if (is_array($string)) {
			foreach ($string as $item) {
				$this->cres=array();
				if ($return) {
					$this->cres[]=ereg_replace("[a-zA-Z]","",$string);
				} else {
					$this->cres[]=!(ereg("[a-zA-Z]",$string));
				}
			}
		} else {
			if ($return) {
				$this->cres=ereg_replace("[a-zA-Z]","",$string);
			} else {
				$this->cres=!(ereg("[a-zA-Z]",$string));
				if (!$this->cres) { $this->cres=0; }
			}
		}
		return $this->cres;
	}
	
	function onlydigits ($string, $dec=0, $return=0) {
		if (is_array($string)) {
			foreach ($string as $item) {
				$this->cres=array();
				if ($return) {
					if ($dec) {
						$this->cres[]=ereg_replace("[^0-9.]","",$string);
					} else {
						$this->cres[]=ereg_replace("[^0-9]","",$string);
					}
				} else {
					if ($dec) {
						$this->cres[]=!(ereg("[^0-9.]",$string));
					} else {
						$this->cres[]=!(ereg("[^0-9]",$string));
					}
				}
			}
		} else {
			if ($return) {
				if ($dec) {
					$this->cres=ereg_replace("[^0-9.]","",$string);
				} else {
					$this->cres=ereg_replace("[^0-9]","",$string);
				}
			} else {
				if ($dec) {
					$this->cres=!(ereg("[^0-9.]",$string));
					if (!$this->cres) { $this->cres=0; }
				} else {
					$this->cres=!(ereg("[^0-9]",$string));
					if (!$this->cres) { $this->cres=0; }
				}
			}
		}
		return $this->cres;
	}
	
	function onlyalpha ($string, $case=0, $return=0) {
		if (is_array($string)) {
			foreach ($string as $item) {
				$this->cres=array();
				if ($return) {
					if ($case=='u') {
						$this->cres[]=ereg_replace("[^A-Z]","",$string);
					} elseif ($case=='l') {
						$this->cres[]=ereg_replace("[^a-z]","",$string);
					} else {
						$this->cres[]=eregi_replace("[^a-z]","",$string);
					}
				} else {
					if ($case=='u') {
						$this->cres[]=!(ereg("[^A-Z]",$string));
					} elseif ($case=='l') {
						$this->cres[]=!(ereg("[^a-z]",$string));
					} else {
						$this->cres[]=!(eregi("[^a-z]",$string));
					}
				}
			}
		} else {
			if ($return) {
				if ($case === 'u') {
					$this->cres=ereg_replace("[^A-Z]","",$string);
					$this->debug="case is u";
				} elseif ($case === 'l') {
					$this->cres=ereg_replace("[^a-z]","",$string);
					$this->debug="case is l";
				} else {
					$this->cres=ereg_replace("[^A-Za-z]","",$string);
					$this->debug="case is other";
				}
			} else {
				if ($case === 'u') {
					$this->debug="case is u";
					$this->cres=!(ereg("[^A-Z]",$string));
					if (!$this->cres) { $this->cres=0; }
				} elseif ($case === 'l') {
					$this->debug="case is l";
					$this->cres=!(ereg("[^a-z]",$string));
					if (!$this->cres) { $this->cres=0; }
				} else {
					$this->debug="case is other";
					$this->cres=!(ereg("[^A-Za-z]",$string));
					if (!$this->cres) { $this->cres=0; }
				}
			}
		}
		return $this->cres;
	}
	
	function nospec ($string, $return=0) {
		if (is_array($string)) {
			foreach ($string as $item) {
				$this->cres=array();
				if ($return) {
					$this->cres[]=ereg_replace("[^a-zA-Z0-9]","",$string);
				} else {
					$this->cres[]=!(ereg("[^a-zA-Z0-9]",$string));
				}
			}
		} else {
			if ($return) {
				$this->cres=ereg_replace("[^a-zA-Z0-9]","",$string);
			} else {
				$this->cres=!(ereg("[^a-zA-Z0-9]",$string));
				if (!$this->cres) { $this->cres=0; }
			}
		}
		return $this->cres;
	}
	

	function datecheck ($string, $yr=3, $mon=1, $day=2, $bottomyr='', $topyr='') {
		if (!$topyr) {
			$topyr=date("Y");
		}
		if (is_array($string)) {
			$this->cres=array();
			foreach ($string as $str) {
				if (!ereg("[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*", $str, $parts)) {
					$this->cres[]="The date $str is not a valid date.";
				}
				if ($parts[$mon] < 1 || $parts[$mon] > 12) {
					$this->cres[]="The month in the date $str is not valid.";
					continue;
				}
				if ($parts[$mon]==1 || $parts[$mon]==3 || $parts[$mon]==5 || $parts[$mon]==7 || $parts[$mon]==8 || $parts[$mon]==10 || $parts[$mon]==12) {
					$maxday=31;
				} elseif ($parts[$mon]==2) {
					$maxday=29;
				} else {
					$maxday=30;
				}
				if ($parts[$day] < 1 || $parts[$day] > $maxday) {
					$this->cres[]="The day in the date $str is not valid.";
					continue;
				}
				if ($parts[$yr] > $topyr) {
					$this->cres[]="The year in the date $str is too high. The year cannot be higher than $topyr.";
					continue;
				} elseif (strlen($parts[$yr])<4) {
					$this->cres[]="The year in the date $str is too short. Please write the year as 4 digits.";
				} elseif ($bottomyr && $parts[$yr] < $bottomyr) {
					$this->cres[]="The year in the date $string is too low. The year cannot be lower than $bottomyr.";
					continue;
				}
				$this->cres[]=0;
			}
			return $this->cres;
		} else {
			if (!ereg("[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*([0-9]*)[^0-9]*", $string, $parts)) { //"
				$this->cres="The date $string is not a valid date.";
				return $this->cres;
			}
			if ($parts[$mon] < 1 || $parts[$mon] > 12) {
				$this->cres="The month in the date $string is not valid.";
				return $this->cres;
			}
			if ($parts[$mon]==1 || $parts[$mon]==3 || $parts[$mon]==5 || $parts[$mon]==7 || $parts[$mon]==8 || $parts[$mon]==10 || $parts[$mon]==12) {
				$maxday=31;
			} elseif ($parts[$mon]==2) {
				$maxday=29;
			} else {
				$maxday=30;
			}
			if ($parts[$day] < 1 || $parts[$day] > $maxday) {
				$this->cres="The day in the date $string is not valid.";
				return $this->cres;
			}
			if ($parts[$yr] > $topyr) {
				$this->cres="The year in the date $string is too high. The year cannot be higher than $topyr.";
				return $this->cres;
			} elseif (strlen($parts[$yr])<4) {
				$this->cres="The year in the date $string is too short. Please write the year as 4 digits.";
				return $this->cres;
			} elseif ($bottomyr && $parts[$yr] < $bottomyr) {
				$this->cres="The year in the date $string is too low.. The year cannot be lower than $bottomyr.";
				return $this->cres;
			}
		}
		return 0;
	}



	function emailcheck ($email, $NoHost=0) {
		$userRE='^[0-9a-z_]([-_.]?[0-9a-z])*$';
		$hostRE='^([0-9a-z]([-]?[0-9a-z])*\.)*$';
		$domRE='^[a-z][a-z](g|l|m|pa|t|u|v)?$';
		$ipRE="/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/";
		$at=preg_match_all("/@/", $email, $dump);
		if ($at != 1) {
			return ("The e-mail address is incorrect. There must be exactly 1 @ sign (such as my_email@aol.com or my_email@state.ar.us). Please double check to make sure you typed it correctly.");
		}
		list($user, $host)=split("@", $email); //"
		if (!ereg($userRE, $user)) {
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
			if ($NoHost) {
				return 0;
			} else {
				return $this->hostcheck($host);
			}
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
			if ($NoHost) {
				return 0;
			} else {
				return $this->hostcheck($host);			
			}
		}
	}
	
	
	function hostcheck ($host) {
		if (checkdnsrr($host, 'ANY')) {
			return 0;
		} else {
			return ("The host name (the part after the \"@\") is not correct. Please double check to make sure you typed it correctly.");
		}
	}
}
%>
