<?php
/* OOHForms: text
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_text.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_text extends of_element {

  var $maxlength;
  var $minlength;
  var $length_e;
  var $valid_regex;
  var $valid_icase;
  var $valid_e;
  var $pass;
  var $size;

  // Constructor
  function of_text($a) {
    $this->setup_element($a);
    if ($a["type"]=="password") $this->pass=1;
  }

  function self_show($val,$which) {
    if (is_array($this->value))
      $v = $this->value[$which];
    else $v = $this->value;
    $n = ($this->multiple ? $this->name . "[]" : $this->name);
    echo "<input name=\"$n\" value=\"$v\"";
    if ($this->pass) echo " type=\"password\""; else echo " type=\"text\"";
    if ($this->maxlength) echo " maxlength=\"$this->maxlength\"";
    if ($this->size) echo " size=\"$this->size\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">\n";
    return 1;
  }

  function self_show_frozen($val,$which) {
    if (is_array($this->value))
      $v = $this->value[$which];
    else $v = $this->value;
    $n = ($this->multiple ? $this->name . "[]" : $this->name);
    echo "<input type=\"hidden\" name=\"$n\" value=\"$v\">\n";
    echo "<table border=1><tr><td>$v</td></tr></table>\n";
    return 1;
  }

  function self_print_js($ndx_array) {
    reset($ndx_array);
    while (list($k,$n) = each($ndx_array)) {
      if ($this->length_e) {
	echo "if (f.elements[${n}].value.length < $this->minlength) {\n";
	echo "  alert(\"$this->length_e\");\n";
	echo "  f.elements[${n}].focus();\n";
	echo "  return(false);\n}\n";
      }
      if ($this->valid_e) {
	$flags = ($this->icase ? "gi" : "g");
	echo "if (window.RegExp) {\n";
	echo "  var reg = new RegExp(\"$this->valid_regex\",\"$flags\");\n";
	echo "  if (!reg.test(f.elements[${n}].value)) {\n";
	echo "    alert(\"$this->valid_e\");\n";
	echo "    f.elements[${n}].focus();\n";
	echo "    return(false);\n";
	echo "  }\n}\n";
      }
    }
  }

  function self_validate($val) {
    if (!is_array($val)) $val = array($val);
    reset($val);
    while (list($k,$v) = each($val)) {
      if ($this->length_e && (strlen($v) < $this->minlength))
	return $this->length_e;
      if ($this->valid_e && (($this->icase && 
			      !eregi($this->valid_regex,$v)) ||
			     (!$this->icase &&
			      !ereg($this->valid_regex,$v))))
	return $this->valid_e;
    }
    return false;
  } 

} // end TEXT

?>
