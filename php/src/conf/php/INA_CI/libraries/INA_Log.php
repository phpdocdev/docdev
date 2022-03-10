<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

#require parent class
require_once(BASEPATH.'libraries/Log'.EXT);

class INA_Log extends CI_Log {
	var $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4', 'ERROR_AND_INFO' => '5');###jay addition, added new level
	
	function write_log($level = 'error', $msg, $php_error = FALSE)
	{		
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}
	
		$level = strtoupper($level);
		
		###jay addition
		if ($this->_threshold == 5) {
			if ($level != 'ERROR' AND $level != 'INFO')
				return FALSE;
		}
		elseif ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}
		###end jay addition
	
		$filepath = $this->log_path.'log-'.date('Y-m-d').EXT;
		$message  = '';
		
		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}
			
		if ( ! $fp = @fopen($filepath, "a"))
		{
			return FALSE;
		}

		if (is_array($msg)) $msg = join($msg, "\n");#jay addition

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";
		
		flock($fp, LOCK_EX);	
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);
	
		@chmod($filepath, 0666); 		
		return TRUE;
	}
}
