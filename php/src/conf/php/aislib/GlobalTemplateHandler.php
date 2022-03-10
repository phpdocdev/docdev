<?php

require_once "aislib/TemplateHandler.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */
class Aislib_GlobalTemplateHandler extends Aislib_TemplateHandler {

  var $resourceName;
  var $slot;
  var $_parent_fetch = false;

  function Aislib_GlobalTemplateHandler($resourceName, $slot) {
    $this->resourceName = $resourceName;
    $this->slot         = $slot;
    parent::Aislib_TemplateHandler();
  }

  function _prepare($tplName) {
    $this->doNotEscape = array_merge($this->doNotEscape, array($this->slot));
    $this->setValues(array($this->slot => $this->_parent_fetch($tplName)));
  }
  function _parent_fetch($tplName) {
    $this->_parent_fetch = true;
    $result = $this->fetch($tplName);
    $this->_parent_fetch = false;
    return $result;
  }
  function fetch($tplName) {
//     $tpl = new Aislib_TemplateHandler();
//     $tpl->doNotEscape = array_merge($this->doNotEscape, array($this->slot));
//     $tpl->_tpl_vars = $this->_tpl_vars;
//     $body = $tpl->fetch($tplName);
//     $tpl->setValues(array($this->slot => $body));
//     return $tpl->fetch($this->resourceName);
    if ($this->_parent_fetch == true) {
      return parent::fetch($tplName);
    } else {
      $this->_prepare($tplName);
      return parent::fetch($this->resourceName);
    }
  }


}



?>