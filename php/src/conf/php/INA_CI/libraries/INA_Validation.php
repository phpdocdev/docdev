<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

#require parent class
require_once(BASEPATH.'libraries/Validation'.EXT);

class INA_Validation extends CI_Validation {
	protected $obj_model;
	
	/**
	 * Constructor
	 *
	 */	
	function __construct()
	{	
		$this->CI =& get_instance();
		
		if (function_exists('mb_internal_encoding') AND $this->CI->config->item('charset'))//jay modification
		{
			mb_internal_encoding($this->CI->config->item('charset'));
		}
		
		log_message('debug', "Validation Class Initialized");
		
		$this->CI =& get_instance();//jay addition
	}
	
	
	#accepts object string name of object as parameter
	function set_model($model, $param = NULL, $param2 = NULL) {
		$CI =& get_instance();
		
		if (is_array($model)) {
			$rules = array();
			$fields = array();
			
			foreach ($model as $m) { 
				$_ = $this->_set_model($m, $param, $param2);
				$rules = array_merge($rules, $_['rules']);
				$fields = array_merge($fields, $_['fields']);
			}
		}
		else {		
			$result = $this->_set_model($model, $param, $param2);
			extract($result);
		}
		
		$this->obj_model = $model;
				
		$CI->validation->set_rules($rules);
		$CI->validation->set_fields($fields);
	}
	
	function _set_model($model, $param = NULL, $param2 = NULL) {
		$CI =& get_instance();

		if (is_object($model)) {
			if (method_exists($model, $param))
				$rules = $model->$param();
			else
				$rules = $model->rules($param, $param2);//returns rules
			
			$fields = $model->fields($param, $param2);//returns fields
		}		
		elseif (is_string($model)) {
			if (method_exists($CI->$model, $param))
				$rules = $CI->$model->$param($param2);
			else
				$rules = $CI->$model->rules($param, $param2);//returns rules
			
			$fields = $CI->$model->fields($param, $param2);//returns fields
		}
		
		return array ('fields' => $fields, 'rules' => $rules);
	}
	
	/*
	 * custom validation
	 * (deprecated)
	 */
	function custom($str, $param) {
		$p = explode(',', $param);//get params

		$obj = array_shift($p);
		$function = array_shift($p);

		$CI =& get_instance();
		
		$result = $CI->$obj->$function($str, $p);//call custom model function
			
		if ($result === TRUE){
			return TRUE;
		}
		else {
			$this->_error_array[] = $result;//a little hackish
			return FALSE;
		}
	}
	
	/*
	 * Newer function for custom validation
	 */
	function user($str, $param) {
		$p = explode(',', $param);//get params

		$function = array_shift($p);

		$CI =& get_instance();
		
		if (is_array($this->obj_model)) {#look for function in each obj_model
			foreach ($this->obj_model as $obj)
				if (method_exists($obj, $function))
					$result = $CI->$obj->$function($str, $p);//call custom model function
		}
		else {
			$obj =& $this->obj_model;
			$result = $CI->$obj->$function($str, $p);//call custom model function
		}			
		
		if ($result === TRUE){
			return TRUE;
		}
		else {
			$this->_error_array[] = $result;//a little hackish
			return FALSE;
		}
	}
	
	function alpha_ext($str) {
		$_POST[$this->_current_field] = $str = $this->fix_curly_quotes($str);
	
		return ( ! preg_match("/^([\~\`\!\@\#\$\%\^\&\*\(\)\-\+\=\[\]\{\}\:\;\"\'\?\/\,\.\w\s\d])+$/i", $str)) ? FALSE : TRUE;
	}
	
	/**
	 * This function is intended to strip out special characters pasted from word processors like Microsoft Word
	 */
	protected function fix_curly_quotes($text) {
            # source: http://shiflett.org/archive/165
            # additional source: user notes in http://us3.php.net/htmlentities
            $search = array(chr(145), chr(146), chr(147), chr(148), chr(151), chr(96), chr(132), chr(133), chr(150));
            $replace = array("'", "'", '"', '"', '-', "'", '"', '...', '-');
            return str_replace($search, $replace, $text);
    }
	
	
	function alpha_numeric($str) {
		return ( ! preg_match("/^([\w\s\d_-])+$/i", $str)) ? FALSE : TRUE;
	}
	
	function number_only($str) {
		return ( ! preg_match("/^[0-9]+$/", $str)) ? FALSE : TRUE;
	}
	
	function strip_non_digits($str) {
		$_POST[$this->_current_field] = $this->__strip_non_digits($str);
	}
	
	protected function __strip_non_digits($str){
		return preg_replace("/\D/", '', $str);
	}
	
	function alpha_basic($str) {
		return ( ! preg_match("/^([\w\s\d\_\-\'\,])+$/i", $str)) ? FALSE : TRUE;
	}
	
	function valid_url($str){
		return ( ! preg_match("/^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i", $str)) ? FALSE : TRUE;
	}
	
	function numeric_ext($str){
		return ( ! preg_match("/^([\s\d-])+$/i", $str)) ? FALSE : TRUE;
	}
	
	function nice_date($str){#more flexible date checker/reformatter
		if (preg_match("/^(\d){2}\-(\d){2}\-(\d){4}$/", $str)) #this formats mm-dd-yyy as mm/dd/yyyy so that strtotime interprets it correctly
			$_POST[$this->_current_field] = $str = str_replace('-', '/', $str);#replace dashes with slashes
		
		if ($unix_time = strtotime($str))#reformat if valid date
			$_POST[$this->_current_field] = $str = date('m/d/Y', $unix_time);
		
		return ( ! preg_match("/^(\d){2}\/(\d){2}\/(\d){4}$/", $str) ? FALSE : TRUE);
	}
	
	function format_unix_time($str) {
		$_POST[$this->_current_field] = date('Y-m-d H:i:s', $str);
	}
	
	function format_unix_date($str) {
		$_POST[$this->_current_field] = date('Y-m-d', $str);
	}
	
	function great_than_zero($str) {
		return $str > 0 ? TRUE : FALSE;
	}
	
	function not_negative($str) {
		return $str >= 0 ? TRUE : FALSE;
	}
	
	function nice_phone($str) {
		return $this->__format_phone($str, "%03d-%03d-%04d");
	}
	
	function nice_phone_ext($str){
		$phone_reg_ex = '/^(\d{3})-(\d{3})-(\d{4})(.+)?$/';
				
		if (preg_match($phone_reg_ex, $str, $matches))
			return TRUE;
		else 
			return FALSE;
	}
	
	function nice_phone_alt($str) {
		return $this->__format_phone($str, "(%03d) %03d-%04d");
	}
	
	protected function __format_phone($str, $format = "(%03d) %03d-%04d"){
		$str = $this->__strip_non_digits($str);
		
		if (strlen($str) == 10) {
			$phone_reg_ex = '/^(\d{3})(\d{3})(\d{4})$/';
				
			preg_match($phone_reg_ex, $str, $matches);
			
			$_POST[$this->_current_field] = sprintf($format, $matches[1], $matches[2], $matches[3]);
			
			return TRUE;
		}
		else 
			return FALSE;
	}
	
	function valid_zip($zip, $custom_zip_regex= NULL){
		
		$str = $this->__strip_non_digits($zip);#strip non digits
		
		if (strlen($str) < 5) {#less than 5 digits
			$_POST[$this->_current_field] = $str;
			return FALSE;
		}
		elseif (preg_match("/^(\d{5})(\d+)?$/", $str, $matches))#has at least 5 digits
			if ($matches[2])
				$zip = $_POST[$this->_current_field] = sprintf("%05s-%s", $matches[1], $matches[2]);#reformat to 5 digit - ##
			else
				$zip = $_POST[$this->_current_field] = $matches[1];#format to 5 digit zip
		
		$zip_regex = $custom_zip_regex ? $custom_zip_regex : "/^(\d{5}-\d{4})|(\d{5})$/";
		
		return ( ! preg_match($zip_regex, $zip)) ? FALSE : TRUE;
	}
	
	function valid_zip_four($zip){
		return $this->valid_zip($zip, "/^(\d{5}-\d{4})$/");
	}
	
	function min_value($str, $val) {
		return $str >= $val ? TRUE : FALSE;
	}
	
	function max_value($str, $val, $test=NULL) {
		return $str <= $val ? TRUE : FALSE;
	}
	
	function security_check($str, $param=NULL){#added for captcha plugin (not to be confused with Captcha class)
		if ($_SESSION['security_code'] == $str)
			return TRUE;
		else
			return FALSE;
	}
}