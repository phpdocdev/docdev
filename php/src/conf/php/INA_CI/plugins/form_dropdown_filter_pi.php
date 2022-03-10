<?php

/**
 * @author Jay Callicott
 * @copyright Copyright &copy; 2007, INA
 * 
 * This creates a very simple form drop down filter for any dropdown list. All it requires is the js function which could optionally
 * be included in a separate file to keep the html cleaner. The filter function does assume the dropdown select is in a form and
 * within the same form as the input box. Later features could be added to allow for ajax filtering or allowing for more flexibility
 * but right now it is just a simple filter for any dropdown list.
 * 
 */

function form_dropdown_filter($name, $options = array(), $selected = '', $extra = ''){
	$output .= form_input(array('size' => 6, 'onkeyup' => "filtery(this.value,this.form.{$name})", 'onchange' => "filtery(this.value,this.form.{$name})"));
	$output .= form_dropdown($name, $options, $selected, $extra) . "\n";
	
	return $output;
}

function form_dropdown_filter_js(){
	return "<script language=\"JavaScript\" type=\"text/javascript\">
		<!--
			function filtery(pattern,list){
				pattern = new RegExp('^'+pattern,\"i\"); 
				i=0;
				sel=0;
				while(i < list.options.length){
					if(pattern.test(list.options[i].text)){sel=i;break}
					i++;
				}
				list.options.selectedIndex=sel;
			}
		//-->
		</script>";
}