<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Build_config {
	
	protected $CI;

	/**
	 * Constructor - get CI resource
	 * 
	 * @access public
	 */	
	function __construct($config_file_path=NULL)
	{	
		if (config_path) $config_file_path = config_path;
		
		if (!$config_file_path) die ('No configuration path found, cannot continue');
		
		$this->CI =& get_instance();
		
		$this->CI->config->set_item('config_file_path', $config_file_path);
		
		$this->load_app_settings();//load build settings
		
		$this->load_security();//load security settings
	}
	
	protected function load_security(){	
		
		require_once("/web/php/INA/Security/class.ina_security.php");//$this->CI->config->item('security_class_path'));//require INA security class
		
		$this->CI->Security = new ina_security();
	}
	
	protected function load_app_settings(){
		//settings from conf.properties /////////
		$config = $this->read_conf();
		
		foreach ($config as $key => $val)
			$this->CI->config->set_item($key, $val);//set config vars
			
		/////////////////////////////////////////
		if ($this->CI->config->item('encrypt_session'))
			require_once('lib.encrypt_session.php');
		
		if ($this->CI->config->item('mysql_encrypt'))
			require_once('lib.mysql_encrypt.php');
		
		/////////////////////////////////////////
		
		if ($this->CI->config->item('application_id'))
			session_name($this->CI->config->item('application_id'));#set session name
		
		session_start();//initialize session
		
		//load user settings ////////////////////
		global $user;
		
		if (isset($_SESSION['user']))
			$user = $_SESSION['user'];
			
		/////////////////////////////////////////
			
		if (isset($_SESSION['ina_sec_csrf']))//no filtering needed as its just spit back out to get string (or checked against another string in the future)
			$this->CI->config->set_item('url_suffix', '/'.$_SESSION['ina_sec_csrf']);//set config vars
			
		/////////////////////////////////////////
	}
	
	/*protected function read_conf(){//old
	    $this->CI->load->helper('file');

	    $lines = file($this->CI->config->item('config_file_path'), 1) or die( "Can't read config file" );
	    
	    foreach ($lines as $line){
	        if (strpos($line, "#") === 0){
	            # Ignore commented line.
	        }elseif (strpos($line, "=")){
	            list($key, $value) = explode('=', $line, 2) ;#jay fix to allow equal signs
	            $key = trim($key);
	            $value = trim($value) ;
	            
	            $opts[$key] = $value ;
	        }
	    }
	    
	    return $opts;
	}*/
	
	/**
	 * Read configuration
	 * 
	 * This will now read from the local conf.properties and global config, the app will die if either fails
	 * 
	 * @return array hash of options used as configuration key value pairs
	 */
	protected function read_conf(){
		require_once('INA/lib.properties.php');//include global properties helpers

		$file_opts = prop_getFileConfiguration($this->CI->config->item('config_file_path'));//get file properties
		
		if (!$file_opts) die("Can't read config file");//quit if config fails bc the app won't function
		
		$db_opts = prop_getAppProperties($file_opts['application_id']);//if no appid or not found it will only get global properties
		
		if (!$db_opts) die("Can't read global configuration");//quit if db config fails, otherwise could have big consequences
		
		foreach($file_opts as $k => $v)
			$Opts[$k] = $v;
		
		foreach($db_opts as $k => $v)//override values
			$Opts[$k] = $v;	
		
		return $Opts;
	}
}
