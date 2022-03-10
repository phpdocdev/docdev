<?php

class INA_Loader extends CI_Loader {
	protected $CI;

    function __construct() {
        parent::__construct();
        $this->CI =& get_instance();
    }
    
	function global_template($view, $vars, $template = NULL, $stylesheet = NULL){
			
		if ($stylesheet)
			$this->CI->config->set_item('stylesheet', $stylesheet);//if stylesheet specified explicitly
		elseif (!$this->CI->config->item('stylesheet'))//if stylesheet not previously defined, used default
			$this->CI->config->set_item('stylesheet', $this->CI->config->item('default_stylesheet'));//set to default
		
		$content = $this->view($view, $vars, TRUE);
		
		$global_template = $this->_get_template($template);//call protected function for getting template from DB		
		
		if ($this->CI->config->item('default_page_title'))
			$global_template = str_replace('<!--//@title@//-->', $this->CI->config->item('default_page_title'), $global_template);
		
		echo str_replace('<%% $BODY %%>', $content, $global_template);//replace body tag with content then echo the whole template	
	}
	
	/**
	 * Global Template get template - return global template from DB
	 * 
	 * @access protected
	 * @return array template properties
	 */
	protected function _get_template($template = NULL){
		if (!$template)
			$template = $this->config->item('template_name');//get name of template from config
		
		$DB = $this->load->database('template', TRUE);#load template db
		
		$DB->select('template');
		$DB->where('name', $template);
		
		$query = $DB->get('gtemplate');
	
		$row = $query->row();//retrieve just one row

		return $row->template;//template content
	}
	
	function _ci_load_class($class, $params = NULL)#upgraded to 1.6.3
	{	
		// Get the class name
		$class = str_replace(EXT, '', $class);

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$subclass = APPPATH.'libraries/'.config_item('subclass_prefix').$class.EXT;

			// Is this a class extension request?			
			if (file_exists($subclass))
			{
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;
				
				if ( ! file_exists($baseclass))
				{
					log_message('error', "Unable to load the requested class: ".$class);
					show_error("Unable to load the requested class: ".$class);
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($subclass, $this->_ci_classes))
				{
					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}
	
				include_once($baseclass);				
				include_once($subclass);
				$this->_ci_classes[] = $subclass;
	
				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params);			
			}
			/* jay insert */
			// Is this a class extension request found in the basepath ?
			elseif (file_exists(BASEPATH.'libraries/'.config_item('subclass_prefix').ucfirst($class).EXT)) {
				if ( ! file_exists(BASEPATH.'libraries/'.ucfirst($class).EXT))
				{
					log_message('error', "Unable to load the requested class: ".$class);
					show_error("Unable to load the requested class: ".$class);
				}
				
				include(BASEPATH.'libraries/'.ucfirst($class).EXT);
				include(BASEPATH.'libraries/'.config_item('subclass_prefix').ucfirst($class).EXT);
	
				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params);			
			}
		
			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;		
			for ($i = 1; $i < 3; $i++)
			{
				$path = ($i % 2) ? APPPATH : BASEPATH;	
				$filepath = $path.'libraries/'.$class.EXT;
				
				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}
				
				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_ci_classes))
				{
					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}
				
				include_once($filepath);
				$this->_ci_classes[] = $filepath;
				return $this->_ci_init_class($class, '', $params);
			}
		} // END FOREACH
		
		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}
}