<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_ArrayValidator {


  function Aislib_ArrayValidator(){
  }
  
  function validate($properties, $value) {

    foreach ($properties as $property) {
      if (strcmp(strtoupper($property->name), "CLASS") == 0) {
        $class = $property->value;
      }
    }

    $validatorClass = new $class();

    if ($value != null) {
      foreach ($value as $v) {
        if (!$validatorClass->validate($properties, $v)) {
          return false;
        }
      }
    }

    return true;
  }

}

/**
 * $Log: ArrayValidator.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.2  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.1  2003/09/02 06:51:06  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/04/22 11:57:46  chmielu
 * Code cleaning
 *
 *
 */

?>