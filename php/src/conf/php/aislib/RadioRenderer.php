<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */


require_once "aislib/Select.php";
require_once "aislib/TemplateHandler.php";

class Aislib_RadioRenderer {

  var $select;
  var $templateFile;

  function Aislib_RadioRenderer($selectInstance, $templateFile = null) {
    if (($selectInstance == null) || (!is_subclass_of($selectInstance, "Aislib_Select"))) {
      $this->select = new Aislib_Select();
    } else {
      $this->select = $selectInstance;
    }
    $this->templateFile = $templateFile;
  }

  function render($name, $default = null) {

    $result = "";

    if ($this->templateFile != null) {
      $tpl = new Aislib_TemplateHandler();
      foreach ($this->select->map as $option_value => $option_label) {
        $map = array("value" => $option_value,
                     "label" => $option_label,
                     "name"  => $name);
        if ($this->select->compareValues($default, $option_value)) {
          $map = array_merge($map, array("checked" => "checked=\"checked\""));
        } else {
          $map = array_merge($map, array("checked" => ""));
        }
        $tpl->setValues($map);
        $result .= $tpl->fetch($this->templateFile);
      }
    } else {
      foreach ($this->select->map as $option_value => $option_label) {
        $result .= "<input type=\"radio\" name=\"".$name."\" value=\"".$option_value."\" ";
        if ($this->select->compareValues($option_value, $default)) {
          $result .= "checked=\"checked\"";
        }
        $result .= ">$option_label &nbsp;";
      }
    }

    return $result;
  }
} 

/*
 * $Log: RadioRenderer.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.5  2004/01/20 12:55:23  chmielu
 * Rendering without template fixed
 *
 * Revision 1.4  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.3  2003/10/01 13:54:06  ender
 * wrong parameters order in method execution fixed
 *
 * Revision 1.2  2003/09/15 12:35:10  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */

?>
