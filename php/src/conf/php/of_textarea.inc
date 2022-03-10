<?php
/* OOHForms: textarea
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_textarea.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_textarea extends of_element {

  var $rows;
  var $cols;
  var $wrap;

  // Constructor
  function of_textarea($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    echo "<textarea name=\"$this->name\"";
    echo " rows=\"$this->rows\" cols=\"$this->cols\"";
    if ($this->wrap) echo " wrap=\"$this->wrap\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">$this->value</textarea>\n";
    return 1;
  }

  function self_show_frozen($val,$which) {
    echo "<input type=\"hidden\" name=\"$this->name\"";
    echo " value=\"$this->value\">\n";
    echo "<table border=1><tr><td>\n";
    echo nl2br($this->value);
    echo "\n</td></tr></table>\n";
    return 1;
  }

} // end TEXTAREA

?>
