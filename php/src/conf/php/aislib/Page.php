<?php

require_once "aislib/SessionHandler.php";
require_once "aislib/form/MessagesHandler.php";
require_once "aislib/form/Form.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_Page {
 
  var $pageName;
  var $templateName;
  var $parameterMap = null;

  var $messages;
  var $application;

  function onLoad() {
  }

  function getPage($name) {
    return $this->application->getPage($name);
  }
  function getResponse($name) {
    $this->application->log->debug("Page redirected: ".$name);
    $newPage = $this->getPage($name);
    return $newPage->getPageResponse();
  }


  function getMessageContent($messageCode) {
    if ($this->messages == null) {
      $messagesHandler = new Aislib_MessagesHandler();
      $this->messages = $messagesHandler->getMessages();
    }
    foreach ($this->messages as $msg) {
      if ($msg->code == $messageCode) {
        return $msg->content;
      }
    }
    return null;
  }

  function getParameter($name) {
    if ($this->parameterMap == null) {
      $this->readParameterMap();
    }
    if (array_key_exists($name, $this->parameterMap)) {
      return $this->parameterMap[$name];
    }
    return null;
  }


  function getParameterMap() {
    if ($this->parameterMap == null) {
      $this->readParameterMap();
    }
    return $this->parameterMap;
  }

  function readParameterMap() {
    if (strcmp(strtolower($_SERVER['REQUEST_METHOD']), "post") == 0) {
      $map = $_POST;
    } else {
      $map = $_GET;
    }
    $this->parameterMap = array();
    if (get_magic_quotes_gpc() == 1) {
      while (list ($key, $val) = each ($map)) {
        $this->parameterMap
           = array_merge($this->parameterMap,
                         array($key => $this->stripslashesobj($val)));
      }
    } else {
      $this->parameterMap = $map;
    }
  }

  function stripslashesobj($value) {
    if (is_string($value)) {
      return stripslashes($value);
    } else if (is_object($value)) {
      return $value;
    } else if (is_array($value)) {
      $result = array();
      while (list ($key, $val) = each ($value)) {
        $result = array_merge($result,
                              array($key => $this->stripslashesobj($val)));
      }
      return $result;
    } else {
      return $value;
    }
  }

  function hasParameter($name) {
    if ($this->parameterMap == null) {
      $this->readParameterMap();
    }
    return isset($this->parameterMap[$name]);
  }

 
  function setPageName($pageName) {
    $this->pageName = $pageName;
  }
  function getPageName() {
    return $this->pageName;
  }

  function setTemplateName($templateName) {
    $this->templateName = $templateName;
  }
  function getTemplateName() {
    return $this->templateName;
  }

  function errorKeysToMap($keys) {
    $result = array();
    foreach($keys as $key) {
      $result = array_merge($result, array($key => "_error"));
    }
    return $result;
  }

}

/*
 * $Log: Page.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.9  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.8  2003/10/29 11:21:28  chmielu
 * *** empty log message ***
 *
 * Revision 1.7  2003/10/27 09:51:56  chmielu
 * *** empty log message ***
 *
 * Revision 1.6  2003/10/24 13:01:04  chmielu
 * *** empty log message ***
 *
 * Revision 1.5  2003/10/23 17:37:24  chmielu
 * *** empty log message ***
 *
 * Revision 1.4  2003/10/17 09:22:58  chmielu
 * Messages are available from Page
 *
 * Revision 1.3  2003/09/23 09:01:54  chmielu
 * *** empty log message ***
 *
 * Revision 1.2  2003/09/22 12:36:20  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 *
 */


?>