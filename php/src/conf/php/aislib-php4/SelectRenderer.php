<?php

require_once "aislib/Select.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_SelectRenderer {

  var $select;
  var $properties;

  function Aislib_SelectRenderer($selectInstance) {
    if (($selectInstance == null) ||
        (!is_subclass_of($selectInstance, "Aislib_Select") && (!is_a($selectInstance, "Aislib_Select")))) {
      $this->select = new Aislib_Select();
    } else {
      $this->select = $selectInstance;
    }
    $this->properties = array();

  }

  function setProperty($key, $value) {
    $this->properties = array_merge($this->properties, array($key => $value));
  }

  function render($name, $default = null) {

    $isMultiple = false;
    $props = "";

    if ($default == null) {
      $default = $this->select->getDefault();
    }

    foreach ($this->properties as $k => $v) {
      if ((strToUpper(trim($k)) == "MULTIPLE") && (strToUpper(trim($v)) == "MULTIPLE")){
        $isMultiple = true;
      }
      $props .= "$k=\"$v\" ";
    }

    $result = "<select name=\"$name";
    if ($isMultiple) {
      $result.="[]";
    }
    $result .= "\" ".$props.">";

    foreach ($this->select->hashmap->keys as $option_value) {
      $option_label = $this->select->hashmap->get($option_value);

      $result .= "<option value=\"$option_value\"";

      if ($this->select->compareValues($default, $option_value)) {
        $result .= " selected=\"selected\"";
      }
      $result.=">$option_label</option>";
    }
    $result .= "</select>\n";

    return $result;
  }

}

/*
 * $Log: SelectRenderer.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.5  2004/02/04 10:57:43  chmielu
 * HashMap added
 *
 * Revision 1.4  2004/01/13 12:32:33  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.2  2003/09/12 14:19:38  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */


?>