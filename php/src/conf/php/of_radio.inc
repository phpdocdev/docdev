<?php
/* OOHForms: radio
 * 
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_radio.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_radio extends of_element {

  var $valid_e;

  // Constructor
  function of_radio($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    echo "<input type=\"radio\" name=\"$this->name\" value=\"$val\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    if ($this->value==$val) echo " checked";
    echo ">\n";
    return 1;
  }

  function self_show_frozen($val,$which) {
    $x = 0;
    if ($this->value==$val) {
      $x = 1;
      echo "<input type=\"hidden\" name=\"$this->name\" value=\"$val\">\n";
      echo "<table border=1 bgcolor=#333333>";
    } else echo "<table border=1>";
    echo "<tr><td>&nbsp</tr></td></table>\n";
    return $x;
  }

  function self_print_js($ndx_array) {
    if ($this->valid_e) {
      $n = $this->name;
      echo "var l = f.${n}.length;\n";
      echo "var radioOK = false;\n";
      echo "for (i=0; i<l; i++)\n";
      echo "  if (f.${n}[i].checked) {\n";
      echo "    radioOK = true;\n";
      echo "    break;\n";
      echo "  }\n";
      echo "if (!radioOK) {\n";
      echo "  alert(\"$this->valid_e\");\n";
      echo "  return(false);\n";
      echo "}\n";
    }
  }

  function self_validate($val) {
    if ($this->valid_e && !isset($val)) return $this->valid_e;
    return false;
  }

} // end RADIO

?>
