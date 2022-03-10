<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_RegExpValidator {

  function Aislib_RegExpValidator(){
  }
  
  function validate($properties, $value) {
    foreach ($properties as $property) {

      if (strcmp(strtoupper($property->name), "REGEXP") == 0) {
        if (!preg_match("/".$property->value."/i", $value, $matches)) {
          return false;
        }
      }

    }
    return true;
  }

}

/*
 * $Log: RegExpValidator.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.2  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.1  2003/09/01 17:28:57  chmielu
 * Initial import
 *
 *
 *
 */


?>
