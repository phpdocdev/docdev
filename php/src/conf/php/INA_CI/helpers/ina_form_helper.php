<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Code Igniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Form Declaration
 *
 * Creates the opening portion of the form.
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */	
function form_open($action = '', $attributes = array(), $hidden = array())
{
	$CI =& get_instance();

	$form = '<form action="'.$CI->config->site_url($action).'"';
	
	if ( ! isset($attributes['method']))
	{
		$form .= ' method="post"';
	}
	
	if (is_array($attributes) AND count($attributes) > 0)
	{
		foreach ($attributes as $key => $val)
		{
			$form .= ' '.$key.'="'.$val.'"';
		}
	}
	
	$form .= '>';

	if (is_array($hidden) AND count($hidden > 0))
	{
		$form .= form_hidden($hidden);
	}
	
	return $form;
}
	
// ------------------------------------------------------------------------

/**
 * Form Declaration - Multipart type
 *
 * Creates the opening portion of the form, but with "multipart/form-data".
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */	
function form_open_multipart($action, $attributes = array(), $hidden = array())
{
	$attributes['enctype'] = 'multipart/form-data';
	return form_open($action, $attributes, $hidden);
}
	
// ------------------------------------------------------------------------

/**
 * Hidden Input Field
 *
 * Generates hidden fields.  You can pass a simple key/value string or an associative
 * array with multiple values.
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @return	string
 */	
function form_hidden($name, $value = '')
{
	
	/******* jay additions ****/
	$CI =& get_instance();
		
	#if ($value == '' && isset($CI->validation)) 
	if (isset($CI->validation) && $CI->validation->$name)
		$value = $CI->validation->$name;//if value doesn't exist, look in validation
	
	/**************************/	

	if ( ! is_array($name))
	{
		return '<input type="hidden" name="'.$name.'" value="'.form_prep($value).'" />';
	}

	$form = '';
	foreach ($name as $name => $value)
	{
		$form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value).'" />';
	}
	
	return $form;
}
	
// ------------------------------------------------------------------------

/**
 * Text Input Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */	

function form_input($data = '', $value = '', $extra = '', $validate_value = TRUE)
{
	/******* jay additions ****/
	if (is_string($data)) {
		$temp = $data;
		$data = NULL;
		$data['name'] = $temp;
	} 
	
	$CI =& get_instance();
	
	$error_field = $data['name'] . '_error'; #name of error field is name of form field plus '_error'
	if ($CI->validation->$error_field) 
		$data['class'] ? $data['class'] .= ' error' : $data['class'] = 'error'; #make field class 'error' if it is an error
		
	if ($validate_value) {//some fields won't want to show validation value (e.g. password fields)
		$valid_val = h($_POST[$data['name']]);
		
		if ($valid_val)#override value
			$value = $valid_val;
		elseif ($valid_val === '0')#makes it work for zero values
			$value = 0;
	}
	
	if ($data['auto_complete']) {
		$min_length = $data['auto_complete']['min_length'] ? $data['auto_complete']['min_length'] : 2; 
		$auto_url   = $data['auto_complete']['url'] ? $data['auto_complete']['url'] : site_url();		
		
		
		#$auto_loader = "var indicator = new Element('div', {'class': 'autocompleter-loading', 'styles': {'display': 'none'}}).setHTML('').injectAfter(this);";
		#$extra .= " onFocus=\"new Autocompleter.Ajax.Json(this, '{$auto_url}', { minLength: {$min_length}, onRequest: function(this) { indicator.setStyle('display', ''); }, onComplete function(this) { indicator.setStyle('display', 'none'); } });\"";

		$extra .= " onFocus=\"new Autocompleter.Ajax.Json(this, '{$auto_url}', { minLength: {$min_length}});\"";
		
		unset($data['auto_complete']);#so that it doesn't get added to inline form
		$data['autocomplete'] = 'off';#disable html autocomplete (for browsers like firefox) 
	}		

	/**************************/

	$defaults = array('type' => 'text', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value, 'maxlength' => '500', 'size' => '25');

	return "<input ".parse_form_attributes($data, $defaults).$extra." />\n";
}
	
// ------------------------------------------------------------------------

/**
 * Password Field
 *
 * Identical to the input function but adds the "password" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */	
function form_password($data = '', $value = '', $extra = '')
{
	if ( ! is_array($data))
	{
		$data = array('name' => $data);
	}

	$data['type'] = 'password';
	return form_input($data, $value, $extra, FALSE);
}
	
// ------------------------------------------------------------------------

/**
 * Upload Field
 *
 * Identical to the input function but adds the "file" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */	
function form_upload($data = '', $value = '', $extra = '', $validate_value = TRUE)
{
	if ( ! is_array($data))
	{
		$data = array('name' => $data);
	}

	$data['type'] = 'file';
	
	return form_input($data, $value, $extra, $validate_value);
}
	
// ------------------------------------------------------------------------

/**
 * Textarea field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */	
function form_textarea($data = '', $value = '', $extra = '', $validate_value = TRUE)
{
	/******* jay additions ****/
	if (is_string($data)) {
		$temp = $data;
		$data = NULL;
		$data['name'] = $temp;
	} 
	
	$CI =& get_instance();
	
	$error_field = $data['name'] . '_error'; #name of error field is name of form field plus '_error'
	if ($CI->validation->$error_field) 
		$data['class'] ? $data['class'] .= ' error' : $data['class'] = 'error'; #make field class 'error' if it is an error
		
	if ($validate_value) {//some fields won't want to show validation value (e.g. password fields)
		#if ($value == '')
		if ($CI->validation->$data['name'])  
			#$value = h($CI->validation->$data['name']);//if value doesn't exist, look in validation
			$value = $CI->validation->$data['name'];//if value doesn't exist, look in validation
	}
	
	/**************************/
	
	$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '40', 'rows' => '10');
	
	$val = (( ! is_array($data) OR ! isset($data['value'])) ? $value : $data['value']);
		
	return "<textarea ".parse_form_attributes($data, $defaults).$extra.">".$val."</textarea>\n";
}

// ------------------------------------------------------------------------

/**
 * Multi-select menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	mixed
 * @param	string
 * @return	type
 */
if ( ! function_exists('form_multiselect'))
{
	function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! strpos($extra, 'multiple'))
		{
			$extra .= ' multiple="multiple"';
		}

		return form_dropdown($name, $options, $selected, $extra);
	}
}
	
// ------------------------------------------------------------------------

/**
 * Drop-down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 * @return	string
 */	
function form_dropdown($name = '', $options = array(), $selected = '', $extra = '', $validate_value = TRUE)
{
	/******* jay additions ****/
	$CI =& get_instance();
	
	$error_field = $name . '_error'; #name of error field is name of form field plus '_error'
	if ($CI->validation->$error_field) 
		$extra ? $extra .= 'class="error"' : $extra = 'class="error"'; #make field class 'error' if it is an error
		
	if ($validate_value) {//some fields won't want to show validation value (e.g. password fields)
		if (preg_match('/\[\]/', $name, $matches)) {
			$field = preg_replace('/\[\]/', '', $name);#strip out parameters
		
			if ($_POST[$field])
				$selected = $_POST[$field];#this will allow multiple items to be selected
		}
		elseif ($CI->validation->$name OR ($CI->validation->$name === '0'))
			$selected = $CI->validation->$name;//if value doesn't exist, look in validation
	}
	
	/**************************/		

	if ($extra != '') $extra = ' '.$extra;
		
	$form = '<select name="'.$name.'"'.$extra.">\n";
	
	foreach ($options as $key => $val)
	{
		if (is_array($selected)) {
			/* ##more efficient TO BE ADDED LATER WHEN IT CAN BE TESTED
			if (in_array($key, $selected))
				$sel = ' selected="selected"';
			else
				$sel = ''; 
			*/
			
			foreach ($selected as $s) {
				if ($s == $key) {
					$sel = ' selected="selected"';
					break;
				}
				else
					$sel = '';
			}
		}
		else
			$sel = ($selected != $key) ? '' : ' selected="selected"';
		
		$form .= '<option value="'.$key.'"'.$sel.'>'.$val."</option>\n";
	}

	$form .= '</select>';
	
	return $form;
}
	
// ------------------------------------------------------------------------

/**
 * Checkbox Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */	
function form_checkbox($data = '', $value = 'checked', $checked = FALSE, $extra = '', $validate_value = TRUE)
{	if (is_array($data) AND array_key_exists('value', $data))
		$value = $data['value'];
	
	$defaults = array('type' => 'checkbox', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
	
	if (is_array($data) AND array_key_exists('checked', $data))
	{	
		$checked = $data['checked'];
		
		if ($checked == FALSE)
			unset($data['checked']);
	}
	
	/******* jay additions ****/
	#$CI =& get_instance();
	if ($validate_value && $_POST) {//some fields won't want to show validation value (e.g. password fields)
		#$checked = $CI->validation->$defaults['name'] ? TRUE : FALSE;//if value doesn't exist, look in validation
		
		$field = $defaults['name'] ? $defaults['name'] : $data['name'];
		
		if (preg_match('/\[.+\]/', $field, $matches)) {
			$field = preg_replace('/\[.+\]/', '', $field);
			preg_match('/\d+/', $matches[0], $index_matches);
			$index = $index_matches[0];
			$checked = $_POST[$field][$index] == $value ? TRUE : FALSE;
		}
		else
			$checked = $_POST[$field] == $value ? TRUE : FALSE;//if value doesn't exist, look in POST
	}
	
	/**************************/
	
	if ($checked == $value || $checked === TRUE)
		$defaults['checked'] = 'checked';
	else
		unset($defaults['checked']);

	return "<input ".parse_form_attributes($data, $defaults).$extra." />\n";
}
	
// ------------------------------------------------------------------------

/**
 * Radio Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */	
function form_radio($data = '', $value = '', $checked = FALSE, $extra = '', $validate_value = TRUE)
{
	if ( ! is_array($data))
	{	
		$data = array('name' => $data);
	}

	$data['type'] = 'radio';
	return form_checkbox($data, $value, $checked, $extra, FALSE);#can't check because form elements have the same name
}
	
// ------------------------------------------------------------------------

/**
 * Submit Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */	
function form_submit($data = '', $value = '', $extra = '')
{
	$defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

	return "<input ".parse_form_attributes($data, $defaults).$extra." />\n";
}

function form_image($data = '', $value = '', $extra = ''){
	$defaults = array('type' => 'image', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

	return "<input ".parse_form_attributes($data, $defaults).$extra." />\n";
}
	
// ------------------------------------------------------------------------

/**
 * Form Close Tag
 *
 * @access	public
 * @param	string
 * @return	string
 */	
function form_close($extra = '')
{
	return "</form>\n".$extra;
}
	
// ------------------------------------------------------------------------

/**
 * Form Prep
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @access	public
 * @param	string
 * @return	string
 */	
function form_prep($str = '')
{
	if ($str === '')
	{
		return '';
	}

	$temp = '__TEMP_AMPERSANDS__';
	
	// Replace entities to temporary markers so that 
	// htmlspecialchars won't mess them up
	$str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
	$str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);

	$str = htmlspecialchars($str);

	// In case htmlspecialchars misses these.
	$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);	
	
	// Decode the temp markers back to entities
	$str = preg_replace("/$temp(\d+);/","&#\\1;",$str);
	$str = preg_replace("/$temp(\w+);/","&\\1;",$str);	
	
	return $str;	
}
	
// ------------------------------------------------------------------------

/**
 * Parse the form attributes
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	array
 * @param	array
 * @return	string
 */	
function parse_form_attributes($attributes, $default)
{
	if (is_array($attributes))
	{
		foreach ($default as $key => $val)
		{
			if (isset($attributes[$key]))
			{
				$default[$key] = $attributes[$key];
				unset($attributes[$key]);
			}
		}
		
		if (count($attributes) > 0)
		{	
			$default = array_merge($default, $attributes);
		}
	}
	
	$att = '';
	foreach ($default as $key => $val)
	{
		if ($key == 'value')
		{
			$val = form_prep($val);
		}
	
		$att .= $key . '="' . $val . '" ';
	}

	return $att;
}

function form_cancel($path, $value = 'Cancel', $class='cancel'){
	$url = site_url($path);
	
	return "<input type=\"button\" class=\"{$class}\" onClick=\"window.location='{$url}';\" value=\"{$value}\" />";
}

function form_link_button($path, $value = '', $param=array()){
	$url = site_url().$path;
	
	$security_string = $_SESSION['ina_sec_csrf'] ? '+\'/'.$_SESSION['ina_sec_csrf'] ."'": '';
	
	$location_js = $param['location_js'] ? ('+'+$param['location_js']) : '';
	
	return "<input type=\"button\" onClick=\"window.location='{$url}'{$location_js}{$security_string};\" value=\"{$value}\" />";
}
