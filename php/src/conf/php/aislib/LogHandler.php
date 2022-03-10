<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "Log.php";
require_once "aislib/MailHandler.php";

class Aislib_LogHandler{

  var $log;
  var $mail;
  var $mailOn;

  function Aislib_LogHandler($logFile = null, $applicationName = null, $logLevel = null, $mailHandler = null) {

    if ($logFile == null) {
      $this->log =&Log::singleton('composite', "", $applicationName, array(), $logLevel);
    } else {
      $this->log =&Log::singleton('file', $logFile, $applicationName, array(), $logLevel);
    }

    if (($mailHandler != null) && (is_a($mailHandler, "Aislib_MailHandler"))) {
      $this->mail = $mailHandler;
    } else {
      $this->mail = null;
    }

    if (defined("PEAR_LOG_DEBUG")) {
      define("_AISLIB_LOG_EMERG",   PEAR_LOG_EMERG);
      define("_AISLIB_LOG_ALERT",   PEAR_LOG_ALERT);
      define("_AISLIB_LOG_CRIT",    PEAR_LOG_CRIT);
      define("_AISLIB_LOG_ERR",     PEAR_LOG_ERR);
      define("_AISLIB_LOG_WARNING", PEAR_LOG_WARNING);
      define("_AISLIB_LOG_NOTICE",  PEAR_LOG_NOTICE);
      define("_AISLIB_LOG_INFO",    PEAR_LOG_INFO);
      define("_AISLIB_LOG_DEBUG",   PEAR_LOG_DEBUG);
    } else {
      define("_AISLIB_LOG_EMERG",   LOG_EMERG);
      define("_AISLIB_LOG_ALERT",   LOG_ALERT);
      define("_AISLIB_LOG_CRIT",    LOG_CRIT);
      define("_AISLIB_LOG_ERR",     LOG_ERR);
      define("_AISLIB_LOG_WARNING", LOG_WARNING);
      define("_AISLIB_LOG_NOTICE",  LOG_NOTICE);
      define("_AISLIB_LOG_INFO",    LOG_INFO);
      define("_AISLIB_LOG_DEBUG",   LOG_DEBUG);
    }

    $this->mailOn = array(_AISLIB_LOG_EMERG, _AISLIB_LOG_ALERT, _AISLIB_LOG_CRIT, _AISLIB_LOG_ERR);
  }

  function instantiate($log = null) {
    if (($log != null) && (is_a($log, "Aislib_LogHandler"))) {
      return $log;
    } else {
      return new Aislib_LogHandler();
    }
  }

  function emergency($message) {
    $this->log($message, _AISLIB_LOG_EMERG);
  }
  function alert($message) {
    $this->log($message, _AISLIB_LOG_ALERT);
  }
  function critical($message) {
    $this->log($message, _AISLIB_LOG_CRIT);
  }
  function error($message) {
    $this->log($message, _AISLIB_LOG_ERR);
  }

  function warning($message) {
    $this->log($message, _AISLIB_LOG_WARNING);
  }
  function notice($message) {
    $this->log($message, _AISLIB_LOG_NOTICE);
  }
  function info($message) {
    $this->log($message, _AISLIB_LOG_INFO);
  }
  function debug($message) {
//    $this->log($message, _AISLIB_LOG_DEBUG);
  }
  
  function log($message, $level) {
    $this->log->log("[".session_id()."] ".$message, $level);

    if (($this->mail != null) && (in_array($level, $this->mailOn))) {
        $this->mail->setMessage("[".session_id()."] ".$message);
        $this->mail->start();
    }
  }
}

/*
 * $Log: LogHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.7  2003/12/10 09:53:14  chmielu
 * Making LogHnadler more flexible
 *
 * Revision 1.6  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.5  2003/10/24 13:01:04  chmielu
 * *** empty log message ***
 *
 * Revision 1.4  2003/10/22 16:18:15  chmielu
 * Application functionality added.
 *
 * Revision 1.3  2003/09/10 09:15:44  chmielu
 * *** empty log message ***
 *
 * Revision 1.2  2003/09/03 16:42:50  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */


?>
