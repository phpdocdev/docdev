<?php
class forms{
	
	var $fvalid;
	var $ret;
	var $form;
	var $fname;

	function forms($name, $action, $method="post", $valid='') {
		$this->form="<form name=\"$name\" action=\"$action\" method=\"$method\"";
		if ($valid) {
			$this->fvalid=1;
			$this->form.=" onsubmit='return $valid($name);'";
		} else {
			$this->fvalid=0;
		}
		$this->fname=$name;
		$this->form.=">";
	}

	function makeTextArea($name, $cols='', $rows='', $value='', $wrap='', $validate='', $error='', $extra='') {    
    
      if(!$wrap){
        $wrap = 'soft';
      }
    
      $this->ret = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="'.$wrap.'">'.$value.'</textarea>';
    
      return $this->ret;
    }
    
  function makeHidden($name, $value){
    $base = '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    return $base;
  }    
    
	function makeText($name, $size='', $max='', $value='', $validate='', $error='', $extra='') {
		if ($value == '' && isset($value)) {
	  	global $HTTP_POST_VARS;
			$value=$HTTP_POST_VARS[$name];
		}
		$base="<input type=\"text\" name=\"$name\" value=\"$value\"";
		if ($size) {
			$base.=" size=$size";
		}
		if ($max) {
			$base.=" maxlength=$max";
		}
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}
	
	function makeRadio($name, $value, $current='', $validate='', $error='', $extra='') {
		if ($current == '' && isset($current)) {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		if ($value==$current) {
			$sel="checked";
		} else {
			$sel="";
		}
		$base="<input type=radio name=\"$name\" value=\"$value\" $sel";
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}

	function makeCheckbox($name, $value, $current='', $validate='', $error='', $extra='') {
		if (!$current && $current != '0' && isset($current)) {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		if (isset($current) && ((is_array($current) && in_array($value, $current)) || $value==$current)) {
			$sel="checked";
		} else {
			$sel=""; 
		}
		$base="<input type=checkbox name=\"$name\" value=\"$value\" $sel";
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}

	function makeSelect($name, $valarray, $lablearray='', $size=1, $current='')  {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		$this->ret="<select name=\"$name\" size=$size>\n";
		if (!$lablearray) {
			$lablearray=$valarray;
		}
		$i=0;
		foreach ($valarray as $value) {
			if ($value==$current) {
				$sel="selected";
			} else {
				$sel="";
			}
			$this->ret.="<option $sel value=\"$value\">$lablearray[$i]</option>\n";
			$i++;
		}
		$this->ret.="</select>";
		return $this->ret;
	}


	function safeSubmit($name, $value='Submit') {
		$this->ret="<INPUT TYPE=\"submit\" NAME=\"$name\" VALUE=\"$value\" ONCLICK=\"if(this.disabled || typeof(this.disabled)=='boolean') this.disabled=true ; form_submitted_test=form_submitted ; form_submitted=true ; form_submitted=(!form_submitted_test || confirm('You have alredy clicked the button one. Are you sure you want to submit this form again?')) ; if(this.disabled || typeof(this.disabled)=='boolean') this.disabled=false ; sub_form='' ; return true\">";
		return $this->ret;
	}

	function selectState($name, $selected='', $parts=''){
  	if(!$selected){
    	$selected = 'AR';
  	}
  	$States = array(
			AA => 'AA (Military)',
			AE => 'AE (Military)',
			AP => 'AP (Military)',
			AL => 'Alabama',
			AK => 'Alaska',
			'AS' => 'American Samoa',
			AZ => 'Arizona',
			AR => 'Arkansas',
			CA => 'California',
			CO => 'Colorado',
			CT => 'Connecticut',
			DE => 'Delaware',
			DC => 'District of Columbia',
			FM => 'Federated States of Micronesia',
			FL => 'Florida',
			GA => 'Georgia',
			GU => 'Guam',
			HI => 'Hawaii',
			ID => 'Idaho',
			IL => 'Illinois',
			IN => 'Indiana',
			IA => 'Iowa',
			KS => 'Kansas',
			KY => 'Kentucky',
			LA => 'Louisiana',
			ME => 'Maine',
			MH => 'Marshall Islands',
			MD => 'Maryland',
			MA => 'Massachusetts',
			MI => 'Michigan',
			MN => 'Minnesota',
			MS => 'Mississippi',
			MO => 'Missouri',
			MT => 'Montana',
			NE => 'Nebraska',
			NV => 'Nevada',
			NH => 'New Hampshire',
			NJ => 'New Jersey',
			NM => 'New Mexico',
			NY => 'New York',
			NC => 'North Carolina',
			ND => 'North Dakota',
			MP => 'Northern Mariana Islands',
			OH => 'Ohio',
			OK => 'Oklahoma',
			'OR' => 'Oregon',
			PW => 'Palau',
			PA => 'Pennslyania',
			PR => 'Puerto Rico',
			RI => 'Rhode Island',
			SC => 'South Carolina',
			SD => 'South Dakota',
			TN => 'Tennessee',
			TX => 'Texas',
			TT => 'Trust Territories',
			UT => 'Utah',
			VT => 'Vermont',
			VI => 'Virgin Islands',
			VA => 'Virginia',
			WA => 'Washington',
			WV => 'West Virginia',
			WI => 'Wisconsin',
			WY => 'Wyoming'
  	);
  	$this->ret = '<select name="'.$name.'" '.$parts.'>';
  	while(list($ab,$nm) = each($States)){
    	if($selected == $ab){
      	$sel = 'selected';
    	}else{
      	$sel = '';
    	}
    	$this->ret .= "\n<option ".$sel.' value="'.$ab.'">'.$nm.'</option>';
  	}

  	return $this->ret . '</select>';
	}

	function selectMon($name, $selected='', $parts=''){
  	if(!$selected){
    	$selected = 0;
  	}
  	$Months = array(
			'' => '(Select)',
    	1 => 'Jan',
    	2 => 'Feb',
    	3 => 'Mar',
    	4 => 'Apr',
    	5 => 'May',
    	6 => 'Jun',
    	7 => 'Jul',
    	8 => 'Aug',
    	9 => 'Sep',
    	10 => 'Oct',
    	11 => 'Nov',
    	12 => 'Dec'
  	);
  	$this->ret = '<select name="'.$name.'" '.$parts.'>';
  	while(list($ab,$nm) = each($Months)){
    	if($selected == $ab){
      	$sel = 'selected';
    	}else{
      	$sel = '';
    	}
    	$this->ret .= "\n<option ".$sel.' value="'.$ab.'">'.$nm.'</option>';
  	}

  	return $this->ret . '</select>';
	}
	function selectCCYear($name, $selected='', $parts=''){
  	if(!$selected){
    	$selected = 0;
  	}
  	$year_sel = '<select name='.$name.'>';
    $thisYear = date("Y", mktime());
    settype($thisYear, "integer");    
    
    if(!$selected){
      $selected = $thisYear;
    }else{
      settype($selected, "integer");
    }    
    
    for($i=$thisYear; $i<($thisYear+15); $i++){
      if($i == $selected){
        $sel = ' selected';
      }else{
        $sel = '';      
      }
      $year_sel .= '<option'.$sel.' value="'.$i.'">'.$i.'';    
    }    
    $year_sel .= '</select>';
	return $year_sel;
	}
}
?>
