<?php

require_once "aislib/Response.php";
require_once "aislib/Page.php";
require_once "aislib/LogHandler.php";
require_once "aislib/TemplateHandler.php";
require_once "aislib/GlobalTemplateHandler.php";


/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */
class Aislib_Application {
  var $name;
  var $log;
  var $startPage;
  var $encodedUrl;
  var $template;

  var $errorHandlerFunction;
  var $logError;

  function Aislib_Application($name) {

    $this->name = $name;
    $this->_requireDir("pages");
    $this->_requireDir("objects");
    $this->log = new Aislib_LogHandler();
    $this->logError = true;
    $this->startPage = "Index";
    $this->encodedUrl = "main.php";
    $this->template = null;

    /** SESSION */
    session_cache_limiter('none');
    session_start();
    session_cache_expire(180);

    ini_set("magic_quotes_gpc", "0");
    ini_set("magic_quotes_sybase", "0");
    set_magic_quotes_runtime(0);

    error_reporting (E_ALL);
    set_error_handler(array(&$this, '_applicationErrorHandler'));
  }

  function getPage($name) {
    $error = false;
    if (class_exists($name)) {
      $page = new $name();
      if (!is_a($page, "Aislib_Page")) {
        $error = true;
      }
    } else {
      $error = true;
    }
    if ($error) {
      trigger_error("Resource not found (".$name.")!", E_USER_ERROR);
    }
    $page->application = &$this;
    $page->setPageName($name);
    $page->setTemplateName(strtolower($name).".html");
    $page->onLoad();
    return $page;
  }

  function dispatch() {
    $page = new Aislib_Page();
    $pageName = $page->getParameter("page");
    if (($pageName == null) || (strcmp(trim($pageName),"") == 0)) {
      cleanSession();
      $pageName = $this->startPage;
    }
    $this->log->debug("Page requested: $pageName");
    $page = $this->getPage($pageName);
    $this->dispatchPage($page);
  }

  function dispatchPage($page){
    $response = $page->getPageResponse();
    if ($response == null) {
      return null;
    }

    if ($this->template == null) {
      $this->template = new Aislib_TemplateHandler();
    }
    $res = $response->values;

    if (array_key_exists("_do_not_escape", $res)) {
      $this->template->doNotEscape = array_merge($this->template->doNotEscape, $res['_do_not_escape']);
    }

    $this->template->setValues($res);
    $this->template->setValues(array("page" => $response->page->getPageName(),
                                     "APPLICATION_NAME" => $this->name,
                                     "encoded_url" => $this->encodedUrl));
    echo $this->template->fetch($response->page->getTemplateName());
  }


  function processVersionInfo() {
    $page = new Aislib_Page();
    if ($page->hasParameter("version")) {
      header ("Content-Type: text/plain");
      print ("Version: ".CVS_TAG);
      exit();
    }
  }

  function processApplicationManager($serviceId, $downPage) {
    require_once "ServiceStatus.php";
    $when      = time();
    $serviceStatus = new ServiceStatus($serviceId);
    $status        = $serviceStatus->getStatus($when);
    if ($status == SERVICE_STATUS_DOWN) {
      $upTime   = $serviceStatus->getUpTime($when);
      $message  = $serviceStatus->getMessage($when);
      if ($message == null) {
        $message = "Sorry, this applicaton is unavailable at the present time.";
      }
      if ($upTime != null) {
        $message .= "<br>Application will be available after ".strftime("%m/%d/%Y %I:%M %p", $upTime)." Mountain Time USA.";
      }
      $tpl = new Aislib_TemplateHandler();
      $tpl->doNotEscape = array("service_down_reason");
      $tpl->setValues(array("service_down_reason" => $message));
      $tpl->display($downPage);
      exit();
    }
  }

  function setLog($log) {
    $this->log = Aislib_LogHandler::instantiate($log);
  }

  function setStartPage($startPage) {
    $this->startPage = $startPage;
  }

  function setEncodedUrl($url) {
    $this->encodedUrl = $url;
  }

  function setTemplateHandler($templateHandler) {
    $this->template = $templateHandler;
  }

  function _requireDir($dir) {
    $dir = $dir . "/";
    if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) != false) {
          if ((is_dir($file)) && ($file != "..") && ($file != ".")) {
            $this->_requireDir($dir.$file);
          } else {
            if (is_int($pos = strpos(strrev($file), strrev("php"))) && ($pos == 0)) {
              require_once($dir.$file);
            }
          }
        }
        closedir($dh);
      }
    }
  }


  function setErrorHandlerFunction($errorHandlerFunction, $logError = true) {
    if (($errorHandlerFunction != null) && (is_callable($errorHandlerFunction))) {
      $this->errorHandlerFunction = $errorHandlerFunction;
    } else {
      $this->log->emergency("Function or method '".$errorHandlerFunction."' is not callable!");
    }
    $this->logError = $logError;
    //    $this->log->info("error handler function is set: '".$this->errorHandlerFunction."'");
  }

  function callErrorHandlerFunction($errstr, $errno = E_USER_WARNING) {
    if ($this->errorHandlerFunction != null) {
      if (is_callable($this->errorHandlerFunction)) {
        call_user_func($this->errorHandlerFunction, array("errno"   => $errno,
                                                          "errstr"  => $errstr));
      } else {
        $this->log->emergency("Function or method '".$this->errorHandlerFunction."' is not callable!");
      }
    }
  }

  function _applicationErrorHandler ($errno, $errstr, $errfile, $errline, $vars) {
    if ($errno == E_NOTICE) {
      return;
    }

    if ($this->logError) {
      $this->log->error("Errno: " . $errno . "; "."Message: " . $errstr . "; "
                        ."File: " . $errfile . "; "."Line: " . $errline . ";");
    }

    //    $this->log->info("error is called. Function: '".$this->errorHandlerFunction."'");
    if ($this->errorHandlerFunction != null) {
      if (is_callable($this->errorHandlerFunction)) {
        call_user_func($this->errorHandlerFunction, array("errno"   => $errno,
                                                          "errstr"  => $errstr,
                                                          "errfile" => $errfile,
                                                          "errline" => $errline,
                                                          "vars"    => $vars));
      } else {
        $this->log->emergency("Function or method '".$this->errorHandlerFunction."' is not callable!");
      }
    }
  }

}



/*
 * $Log: Application.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.15  2004/02/04 10:57:43  chmielu
 * HashMap added
 *
 * Revision 1.14  2004/01/15 09:45:53  chmielu
 * Magic quotes switched off
 *
 * Revision 1.13  2004/01/05 17:41:13  chmielu
 * set template method added
 *
 * Revision 1.12  2004/01/05 17:32:29  chmielu
 * Global Template support added
 *
 * Revision 1.11  2003/12/09 16:07:12  chmielu
 * *** empty log message ***
 *
 * Revision 1.10  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.9  2003/11/12 10:22:19  chmielu
 * DateTimeHandler added
 *
 * Revision 1.8  2003/11/03 14:54:16  chmielu
 * *** empty log message ***
 *
 * Revision 1.7  2003/11/03 14:45:19  chmielu
 * *** empty log message ***
 *
 * Revision 1.6  2003/10/29 11:21:28  chmielu
 * *** empty log message ***
 *
 * Revision 1.5  2003/10/27 09:51:56  chmielu
 * *** empty log message ***
 *
 * Revision 1.4  2003/10/24 14:29:14  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/10/23 17:37:24  chmielu
 * *** empty log message ***
 *
 * Revision 1.2  2003/10/22 16:18:15  chmielu
 * Application functionality added.
 *
 * Revision 1.1  2003/10/22 12:55:48  chmielu
 * Application class added
 *
 *
 */

?>