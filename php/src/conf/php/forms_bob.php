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

	function makeTextArea($name, $cols='', $rows='', $value='', $wrap='', $validate='', $error='', $extra='', $readonly=0) {    
    
      if($readonly){
        return nl2br($value);
      }
    
      if(!$wrap){
        $wrap = 'soft';
      }
    
      $this->ret = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="'.$wrap.'">'.$value.'</textarea>';
    
      return $this->ret;
    }
    
	function makeText($name, $size='', $max='', $value='', $validate='', $error='', $extra='', $readonly=0) {
        if($readonly){
          return $value;
        }        
        
		if (!$value && $value != '0') {
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
		if (!$current && $current != '0') {
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
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		if ((is_array($current) && in_array($value, $current)) || $value==$current) {
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

	function makeSelect($name, $valarray, $lablearray='', $size=1, $current='', $readonly=0)  {
    
        if($readonly){
          // which in valarray is selected?
          return $current;
        }
    
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

	function selectState($name, $selected='', $parts='', $readonly=0){
  	if(!$selected){
    	$selected = 'AR';
  	}
  	$States = array(
    	AL => 'Alabama',
    	AK => 'Alaska',
    	AZ => 'Arizona',
    	AR => 'Arkansas',
    	CA => 'California',
    	CO => 'Colorado',
    	CT => 'Connecticut',
    	DE => 'Delaware',
    	DC => 'District of Columbia',
    	FL => 'Florida',
    	GA => 'Georgia',
    	HI => 'Hawaii',
    	ID => 'Idaho',
    	IL => 'Illinois',
    	IN => 'Indiana',
    	IA => 'Iowa',
    	KS => 'Kansas',
    	KY => 'Kentucky',
    	LA => 'Louisiana',
    	ME => 'Maine',
    	MD => 'Maryland',
    	MA => 'Massachusetts',
    	MI => 'Michigan',
    	MN => 'Minnesota',
    	MS => 'Mississippi',
    	MO => 'Missouri',
    	MT => 'Montana',
    	NC => 'North Carolina',
    	ND => 'North Dakota',
    	NE => 'Nebraska',
    	NH => 'New Hampshire',
    	NJ => 'New Jersey',
    	NM => 'New Mexico',
    	NV => 'Nevada',
    	NY => 'New York',
    	OH => 'Ohio',
    	OK => 'Oklahoma',
    	'OR' => 'Oregon',
    	PA => 'Pennsylvania',
    	RI => 'Rhode Island',
    	SC => 'South Carolina',
    	SD => 'South Dakota',
    	TN => 'Tennessee',
    	TX => 'Texas',
    	UT => 'Utah',
    	VT => 'Vermont',
    	VA => 'Virginia',
    	WA => 'Washington',
    	WI => 'Wisconsin',
    	WV => 'West Virginia',
    	WY => 'Wyoming'
  	);
    
    if($readonly){
      return $States[$selected];
    }
    
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
}
?>
