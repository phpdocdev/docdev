<?

/* GLOBAL LIBRARY FOR HOLIDAY WEB SERVICE **

functions: is_holiday, get_holidays, get_next_business_day
global params: xml (bool), php (bool) - if xml is set to any value, all output will be in xml, if php is set to any value all
	output will be displayed as a serialized php object.

### is_holiday function
params: is_holiday (date), xml (bool), php (bool)

description: pass in a date (in any format) and it will return 1, an array of ('result' => 1) or xml of <root><result>1</result></root>
	or 0 for all 3 cases if not found.

### get_holidays function
params: get_holidays (mixed), xml (bool), php (bool)

description: pass 'all' or a year (yyyy) and the function will return a list of dates (either all holidays in the system or only those 
	specified for a particular year). Will display in a space delimited list, an array or xml list of <root><dates><date>, etc. depending
	on the output preference specified. Returns an empty set if none found (empty array or <root><dates><date></date>..)
	
### get_next_business_day function
params: get_next_business_day (date), xml (bool), php (bool)

description: pass in a date of any format and function will return next business date in format YYYY-MM-DD or array('result' => 'YYYY-MM-DD')
	or <root><result>YYYY-MM-DD</result></root> depending on output preference
	UPDATE: This call now respects holidays.  It returns the next non-holiday week day.
*********************************/

class Holiday {
	private $mdb2;
	private $show_xml;
	private $show_serialized;
	private $return_results;
	
	function __construct(){
 		
 		$this->Opts = set_config();//initialize config constants

 		$this->set_show_xml();
		$this->set_show_serialized();
		$this->set_return_results();
		
		require_once 'MDB2.php';

		$this->mdb2 =& MDB2::factory($this->Opts['DSN']);
		$this->mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
	}
	
	public function is_holiday($date= false) {
	
		if($date){
			$date = mysql_escape_string(strtotime($date));
		}else{
			$date = strtotime(date('Y-m-d'));
		}
		
		$date = ($_GET['is_holiday'])? mysql_escape_string(strtotime($_GET['is_holiday'])): $date;
		
		$result = $this->is_holiday_bool($date) ; // Call our private method that returns a bool.

		return $this->show_result($result);
	}
	
	public function get_holidays($request = false){
		if($_GET['get_holidays']){
		$request = $_GET['get_holidays'];
		}
		
		ob_start();
		
		if ($request == 'all')
			$this->_print_all_holidays();
		else
			$this->_print_holidays_with_year($request);
			
		$dates = ob_get_contents();
		ob_end_clean(); 
		
		if($this->return_results)
			return $dates;
		else
			echo $dates;
	}
	
	public function get_next_business_day($unix_date = false){
		if($_GET['get_next_business_day']){
			$unix_date = $_GET['get_next_business_day'];
		}
		
		$unix_date = strtotime($unix_date);

		// Get next week day, and see if it is a holiday
		$next_business_day = $this->get_next_weekday($unix_date) ;
		$is_holiday = $this->is_holiday_bool($next_business_day) ;
		
		// If next week day is a holiday, keep looking until we find a week day that isn't a holiday.
		while ($is_holiday){
			$next_business_day = $this->get_next_weekday($next_business_day) ;
			$is_holiday = $this->is_holiday_bool($next_business_day) ;
		}
		return $this->show_result(date("Y-m-d", $next_business_day));//get next day
	}
	
	### PRIVATE FUNCTIONS #############
	
	private function _print_all_holidays(){
		$sql = "SELECT date FROM holiday";
		$result = $this->mdb2->queryAll($sql);
		
		$this->db_error_check($result);
				
		$this->show_dates($result);
	}
	
	private function _print_holidays_with_year($year){
		if (!is_numeric($year)) return FALSE;
		
		$year = mysql_escape_string($year);
		
		$sql = "SELECT date FROM holiday WHERE date LIKE '{$year}%'";
		$result = $this->mdb2->queryAll($sql);
		
		$this->db_error_check($result);
				
		$this->show_dates($result);
	}
	
	private function show_dates($result){
		if (is_array($result)) {
			if ($this->show_serialized)
				$this->show_serialize_dates($result);
			elseif ($this->show_xml)
				$this->show_xml_dates($result);
			else
				$this->list_dates($result);
		}
		else
			return false;
	}
	
	private function list_dates($dates){
		foreach ($dates as $row)
			echo htmlentities($row['date'])."\n";
	}
	
	private function show_serialize_dates($dates){
		foreach ($dates as $row)
			$dates_array[] = $row['date'];
			
		echo serialize($dates_array);
	}
	
	private function show_xml_dates($dates){
		header ("Content-Type: application/xml");
		
		echo "<root>\n<dates>";
		
		foreach ($dates as $row)
			echo "<date>".htmlentities($row['date'])."</date>";
			
		echo "</dates>\n</root>";
	}
	
	private function show_result($result){
		if($this->return_results)
			return $this->returned_results($result);
		elseif($this->show_serialized)
    		$this->php_result($result);
    	elseif ($this->show_xml)
    		$this->xml_result($result);
    	else
    		echo($result);//show zero
	}
	
	private function xml_result($result){
		header ("Content-Type: application/xml");
	    die("<root><result>".htmlentities($result)."</result></root>");
	}
	
	private function php_result($result){
		die(serialize(array('result' => htmlentities($result))));
	}

	private function returned_results($result){
		if($this->show_serialized){
			return serialize(array('result' => htmlentities($result)));
		}elseif($this->show_xml){
			return "<root><result>".htmlentities($result)."</result></root>";
		}else{
			return $result;
		}
	}
	/**
	 * Internal function to see if a date is a holiday.
	 * @param int $datetotest time stamp for the day in question
	 * @return bool Is this a holiday?
	 */
	public function is_holiday_bool($datetotest = false) {
		$date = mysql_escape_string(date('Y-m-d', $datetotest));

		$sql = "SELECT type FROM holiday WHERE date = '{$date}'";
	
		$holiday_type = $this->mdb2->queryOne($sql);

		$this->db_error_check($holiday_type);
		
		$result = $holiday_type ? true : false;

		return $result ;
	}
	
	public function set_show_serialized($set=false){
		$this->show_serialized =$_GET['php'] ? true : $set;
	}
	
	public function set_show_xml($set=false){
		$this->show_xml =$_GET['xml'] ? true : $set;
	}
	
	public function set_return_results($set=false){
		$this->return_results = $set;
	}
	
	/**
	 * Returns the next weekday.
	 *
	 * @param int $now Unix time stamp for day in question.  Default: today
	 * @return int Unix Time Stamp for next week day.
	 */
	function get_next_weekday($now = false){
		if (!$now){
			$now = _curdate('U') ;
		}
		
		$Tomorrow = date('U', strtotime("+1 day", $now)) ;
		if (date('w', $Tomorrow) == 6){
			return date('U', strtotime("+2 days", $Tomorrow)) ; // Tomorrow is Saturday, return Monday
		}elseif(date('w', $Tomorrow) == 0){
			return date('U', strtotime("+1 day", $Tomorrow)) ; // Tomorrow is Sunday, return Monday
		}else{
			return $Tomorrow ; // Tomorrow isn't a weekend. Return tomorrow.
		}
	}
	
	function db_error_check($handler){
		if (MDB2::isError ($handler)){
			mail ("neo@ark.org", "Error has occurred with Holiday Web Service", $handler->message."\n".$handler->userinfo, "From: support@ark.org");
			die ('An error was encountered while processing your information.  Please try again later.') ;
		}
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
if(!function_exists('_curdate')){
	function _curdate($dateformat='m'){
		if (defined('TODAY')){
			$date = strtotime(TODAY) ;
		}else{
			$date = time() ;
		}
	
		return date($dateformat, $date);
	}
}
function set_config(){

    $lines = file('/web/php/holiday_service/conf.properties', 1) or die( "Can't read config file" );
    
    $Opts = array();
    foreach ($lines as $line){
        if (strpos($line, "#") === 0){
            # Ignore commented line.
        }elseif (strpos($line, "=")){
            list($key, $value) = explode('=', $line) ;
            $key = trim($key);
            $value = trim($value) ;
            
			$Opts[$key]= $value;
        }
    }
    
    return $Opts;
}

?>
