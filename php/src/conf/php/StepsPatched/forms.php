<?php

// This file is no longer called by steps

class forms {
	
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
    
    if( is_array($name) ){
      $name     = $name['name'];    
      $cols     = $name['cols'];    
      $rows     = $name['rows'];     
      $value    = $name['value'];   
      $wrap     = $name['wrap'];
      $validate = $name['validate'];   
      $error    = $name['error'];   
      $extra    = $name['extra'];         
    }    
    
      if(!$wrap){
        $wrap = 'soft';
      }
    
      $this->ret = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="'.$wrap.'">'.$value.'</textarea>';
    
      return $this->ret;
    }

  function makeHidden($name, $value){
    if( is_array($name) ){
      $name     = $name['name'];    
      $value    = $name['value'];   
    }
      
    $base = '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    return $base;
  }

  function makeMultiText($name, $parts, $values=array(), $max = array(), $req = array(), $sep = array(), $validate='', $error='', $extra=''){
  
    $ret = '';
    
    $i=1;
    foreach($parts as $k){
      $ret .= $this->makeText($name.'_'.$i, $k, $max[$i-1], $values[$i-1]);
      $ret .= $sep[$i-1];
      $i++;
    }
    
    return $ret;  
  }
    
	function makeText($name, $size='', $max='', $value='', $validate='', $error='', $extra='') {
  
    if( is_array($name) ){
      $name     = $name['name'];    
      $size     = $name['size'];    
      $max      = $name['max'];     
      $value    = $name['value'];   
      $validate = $name['validate'];
      $error    = $name['error'];   
      $extra    = $name['extra'];   
    }
  
		if (! isset($value) ) {
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

	function makeYesNo($name, $YesVal='Y', $NoVal='N', $current='', $validate='', $error='', $extra='') {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
    
		if ($value==$YesVal) {
			$Ychecked = 'checked';
			$Nchecked = '';
		} else {
			$Ychecked = '';
			$Nchecked = 'checked';
		}
    
		$base="<input type=radio name=\"$name\" value=\"$YesVal\" $Ychecked";
		if ($validate) {
      $base .= " onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$base .= "> Yes";
		}
    
		$base.="<input type=radio name=\"$name\" value=\"$NoVal\" $Nchecked";
		if ($validate) {
			$base .= " onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$base .= "> No";
		}    
    
		return $base;
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

	function makeSelect($name, $values, $size=1, $current='', $mult=0)  {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
    
    if($mult == 1){
      $mult = ' multiple';
    }else{
       $mult = '';
    }

		$this->ret="<select name=\"$name\" size=$size".$mult.">\n";    


    foreach($values as $k=>$v){
			if ($current==$k) {
				$sel="selected";
			} else {
				$sel="";
			}
			$this->ret.="<option $sel value=\"$k\">$v</option>\n";

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

	function selectMon($name, $selected='', $prompt='', $abbrev=1, $parts=''){
  	if(!$selected){
    	$selected = 0;
  	}
    
    if($prompt){
      $Months = array(
        '' => $prompt
      );
    }else{
      $Months = array();
    }
    
    $Months[1] =  $abbrev==1 ? 'Jan' : 'January';
    $Months[2] =  $abbrev==1 ? 'Feb' : 'February';
    $Months[3] =  $abbrev==1 ? 'Mar' : 'March';
    $Months[4] =  $abbrev==1 ? 'Apr' : 'April';
    $Months[5] =  $abbrev==1 ? 'May' : 'May';
    $Months[6] =  $abbrev==1 ? 'Jun' : 'June';
    $Months[7] =  $abbrev==1 ? 'Jul' : 'July';
    $Months[8] =  $abbrev==1 ? 'Aug' : 'August';
    $Months[9] =  $abbrev==1 ? 'Sep' : 'September';
    $Months[10] = $abbrev==1 ? 'Oct' : 'October';
    $Months[11] = $abbrev==1 ? 'Nov' : 'November';                                        
    $Months[12] = $abbrev==1 ? 'Dec' : 'December';
    
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
