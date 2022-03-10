<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

/*
 *
 * This module is DEPRECATED and should not be used any more.
 * Use Application and setErrorPage method instead.
 *
 */

error_reporting (E_ALL);

function errorPage($message) {

    $response = new Aislib_Response("Error");
    global $ERROR_MESSAGE;
    $ERROR_MESSAGE = $message;
    $response->getResponse();
    exit();
}

function myErrorHandler ($errno, $errstr, $errfile, $errline) {

  if ($errno == E_NOTICE) {
    return;
  }

  global $log;
  $log = Aislib_LogHandler::instantiate($log);
  $log->critical("Errno: " . $errno . "; " .
                 "Message: " . $errstr . "; " .
                 "File: " . $errfile . "; " .
                 "Line: " . $errline . ";");

  $tpl = new Aislib_TemplateHandler();
  $tpl->setValues(array("errno"   => $errno,
                        "errstr"  =>$errstr,
                        "errfile" =>$errfile,
                        "errline" => $errline));
  $tpl->display("errorhandler.html");
  exit();
}

function transErrorHandler ($errno, $errstr, $errfile, $errline) {

  if ($errno == E_NOTICE) {
    return;
  }

  global $log;
  $log = Aislib_LogHandler::instantiate($log);
  $log->error("Database Update Error !!! Errno: " . $errno . "; " .
              "Message: " . $errstr . "; " .
              "File: " . $errfile . "; " .
              "Line: " . $errline . ";");
  return;
}

$old_error_handler = set_error_handler("myErrorHandler");

/*
 * $Log: ErrorHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.5  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.4  2003/10/24 13:01:04  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/10/23 17:37:24  chmielu
 * *** empty log message ***
 *
 * Revision 1.2  2003/09/04 11:14:02  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */

?>