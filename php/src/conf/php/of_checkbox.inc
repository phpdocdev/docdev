<?php
/* OOHForms: checkbox
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_checkbox.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_checkbox extends of_element {

  var $checked;

  // Constructor
  function of_checkbox($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    if ($this->multiple) {
      $n = $this->name . "[]";
      echo "<input type=\"checkbox\" name=\"$n\" value=\"$val\"";
      if (is_array($this->value)) {
	reset($this->value);
	while (list($k,$v) = each($this->value))
	  if ($v==$val) { echo " checked"; break; }
      }
    } else {
      echo "<input type=\"checkbox\" name=\"$this->name\"";
      echo " value=\"$this->value\"";
      if ($this->checked) echo " checked";
    }
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">\n";
    return 1;
  }

  function self_show_frozen($val,$which) {
    $x = 0;
    $t="";
    if ($this->multiple) {
      $n = $this->name . "[]";
      if (is_array($this->value)) {
	reset($this->value);
	while (list($k,$v) = each($this->value))
	  if ($v==$val) {
	    $x = 1;
	    echo "<input type=\"hidden\" name=\"$this->name\" value=\"$v\">\n";
	    $t =" bgcolor=#333333";
	    break;
	  }
      }
    } else {
      if ($this->checked) {
	$x = 1;
	$t = " bgcolor=#333333";
	echo "<input type=\"hidden\" name=\"$this->name\"";
	echo " value=\"$this->value\">";
      }
    }
    echo "<table$t border=1><tr><td>&nbsp</td></tr></table>\n";
    return $x;
  }
  
  function self_load_defaults($val) {
    if ($this->multiple)
      $this->value = $val;
    elseif (isset($val) && 
	(!$this->value || $val==$this->value)) $this->checked=1;
    else $this->checked=0;
  }

} // end CHECKBOX

?>
