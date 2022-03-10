<?php
/* OOHForms: file
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_file.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_file extends of_element {

  var $isfile = true;
  var $size;

  function of_file($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=$this->size>\n";
    echo "<input type=\"file\" name=\"$this->name\"";
    if ($this->extrahtml)
      echo " $this->extrahtml";
    echo ">\n";
    return 2;
  }

} // end FILE
