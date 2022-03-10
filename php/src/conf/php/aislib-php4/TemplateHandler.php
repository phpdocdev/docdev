<?php

require_once "Smarty.class.php";

require_once "smarty/SmartyWeb.php";
require_once "smarty/SmartyDB.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */
class Aislib_TemplateHandler extends Smarty {

  var $doNotEscape;

  function Aislib_TemplateHandler() {
    parent::Smarty();
    $this->template_dir = "./etc/templates/";
    $this->compile_dir = TMP_DIR;
    $this->left_delimiter = "<%%";
    $this->right_delimiter = "%%>";
    $this->doNotEscape = array();
    $this->register_resource("web", array('smarty_resource_web_source',
                                          'smarty_resource_web_timestamp',
                                          'smarty_resource_web_secure',
                                          'smarty_resource_web_trusted'));
    $this->register_resource("db", array('smarty_resource_db_source',
                                         'smarty_resource_db_timestamp',
                                         'smarty_resource_db_secure',
                                         'smarty_resource_db_trusted'));
  }

  function escapeValue($value) {
    if (is_string($value)) {
      return htmlentities($value, ENT_QUOTES);
    } else if (is_object($value)) {
      return $this->escapeObject($value);
    } else if (is_array($value)) {
      return $this->escapeArray($value);
    } else {
      return $value;
    }
  }

  function escapeArray($values) {
    $result = array();
    foreach (array_keys($values) as $key) {
       if (!in_array($key, $this->doNotEscape)) {
         $result[$key] = $this->escapeValue($values[$key]);
       } else {
         $result[$key] = $values[$key];
       }
    }
    return $result;
  }

  function escapeObject($obj) {
    $vars = get_object_vars($obj);
    foreach (array_keys($vars) as $key) {
      if (!in_array($key, $this->doNotEscape)) {
        $obj->$key = $this->escapeValue($obj->$key);
      } else {
        ;
      }
    }
    return $obj;
  }

  function setValues($values) {
    $this->assign($this->escapeValue($values));
  }

}

/**
 * $Log: TemplateHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.6  2004/01/05 17:32:29  chmielu
 * Global Template support added
 *
 * Revision 1.5  2003/12/24 12:09:29  chmielu
 * Smarty db plugin added
 *
 * Revision 1.4  2003/12/17 11:24:27  chmielu
 * 'web' smarty plugin added
 *
 * Revision 1.3  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.2  2003/10/29 11:21:28  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */


?>