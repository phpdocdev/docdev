<?php

class INA_URI extends CI_URI {
	
	function __construct() {
		parent::__construct();
	}
	
	function segment_pop(){		
		static $security_string = false;
		
		if (!$security_string)
			$security_string = array_pop($this->segments);//as of 1.6.3 modified segments array ref

		return $security_string;
	}
	
	function referrer_path(){
		$referrer_url = $_SERVER['HTTP_REFERER'];
		
		preg_match('/index.php(\/.+)\/.+$/', $referrer_url, $matches);
		
		return $matches[1];
	}
	
	function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')#upgraded to 1.6.3
	{
		if ($_SESSION['ina_sec_csrf'])
			$this->segment_pop();#discard security string from segment list (jay change)
		
		if ($which == 'segment')
		{
			$total_segments = 'total_segments';
			$segment_array = 'segment_array';
		}
		else
		{
			$total_segments = 'total_rsegments';
			$segment_array = 'rsegment_array';
		}
		
		if ( ! is_numeric($n))
		{
			return $default;
		}
	
		if (isset($this->keyval[$n]))
		{
			return $this->keyval[$n];
		}
	
		if ($this->$total_segments() < $n)
		{
			if (count($default) == 0)
			{
				return array();
			}
			
			$retval = array();
			foreach ($default as $val)
			{
				$retval[$val] = FALSE;
			}		
			return $retval;
		}

		$segments = array_slice($this->$segment_array(), ($n - 1));

		$i = 0;
		$lastval = '';
		$retval  = array();
		foreach ($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = FALSE;
				$lastval = $seg;
			}
		
			$i++;
		}

		if (count($default) > 0)
		{
			foreach ($default as $val)
			{
				if ( ! array_key_exists($val, $retval))
				{
					$retval[$val] = FALSE;
				}
			}
		}

		// Cache the array for reuse
		$this->keyval[$n] = $retval;
		return $retval;
	}
}