<?php
/* OOHForms: select
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_select.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_select extends of_element {

  var $options;
  var $size;
  var $valid_e;

  // Constructor
  function of_select($a) {
    $this->setup_element($a);
    if ($a["type"]=="select multiple") $this->multiple=1;
  }
  
  function self_show($val,$which) {
    if ($this->multiple) {
      $n = $this->name . "[]";
      $t = "select multiple";
    } else {
      $n = $this->name;
      $t = "select";
    }
    echo "<$t name=\"$n\"";
    if ($this->size) echo " size=\"$this->size\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">\n";
    reset($this->options);
    while (list($k,$o) = each($this->options)) {
      echo "<option";
      if (is_array($o)) echo " value=\"" .  $o["value"] . "\"";
      if (!$this->multiple && ($this->value==$o["value"] 
			       || $this->value==$o))
	echo " selected";
      elseif ($this->multiple && is_array($this->value)) {
	reset($this->value);
	while (list($tk,$v) = each($this->value))
	  if ($v==$o["value"] || $v==$o) { echo " selected"; break; }
      }
      echo ">" . (is_array($o) ? $o["label"] : $o) . "\n";
    }
    echo "</select>\n";
    return 1;
  }

  function self_show_frozen($val,$which) {
    $x = 0;
    $n = ($this->multiple ? $this->name . "[]" : $this->name);
    $v_array = (is_array($this->value) ? $this->value : array($this->value));
    echo "<table border=1>\n";
    reset($v_array);
    while (list($tk,$tv) = each($v_array)) {
      reset($this->options);
      while (list($k,$v) = each($this->options))
	if ((is_array($v) && 
	     (($tmp=$v["value"])==$tv || $v["label"]==$tv))
	    || ($tmp=$v)==$tv) {
	  $x++;
	  echo "<input type=\"hidden\" name=\"$n\" value=\"$tmp\">\n";
	  echo "<tr><td>" . (is_array($v) ? $v["label"] : $v) . "</td></tr>\n";
	}
    }
    echo "</table>\n";
    return $x;
  }

  function self_print_js($ndx_array) {
    if (!$this->multiple && $this->valid_e) {
      echo "if (f.$this->name.selectedIndex == 0) {\n";
      echo "  alert(\"$this->valid_e\");\n";
      echo "  f.$this->name.focus();\n";
      echo "  return(false);\n";
      echo "}\n";
    }
  }

  function self_validate($val) {
    if (!$this->multiple && $this->valid_e) {
      reset($this->options);
      $o = current($this->options);
      if ($val==$o["value"] || $val==$o) return $this->valid_e;
      return false;
    }
  }

} // end SELECT

?>
