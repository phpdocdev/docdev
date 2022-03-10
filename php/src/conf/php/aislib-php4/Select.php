<?php 

include_once "utils/HashMap.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_Select {

  var $map;
  var $hashmap;

  var $default;

  function setMap($value) {
    $this->map    = $value;
    $this->hashmap = new HashMap();
    foreach ($this->map as $key => $val) {
      $this->hashmap->add($key, $val);
    }
  }
  function setHashMap($value) {
    $this->hashmap = $value;
  }


  function compareValues($v1, $v2) {
    if (is_array($v1)) {
      return in_array($v2, $v1);
    }

    if (strcmp($v1, $v2) == 0) {
      return true;
    } 
    return false;
  }

  function valueToLabel($value) {
    if (is_array($value)) {
      $result = array();
      foreach ($value as $v) {
        //        $result = array_merge($result, array($this->map[$v]));
        $result = array_merge($result, array($this->hashmap->get($v)));
      }
      return implode(", ", $result);
    }
    return $this->hashmap->get($value);
      //    return  $this->map[$value];
  }

  function getDefault() {
    return $this->default;
  }

  function setDefault($value) {
    $this->default = $value;
  }
}

/*
 * $Log: Select.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.3  2004/02/04 10:57:43  chmielu
 * HashMap added
 *
 * Revision 1.2  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */


?>