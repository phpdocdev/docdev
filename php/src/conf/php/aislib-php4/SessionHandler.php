<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

GLOBAL $_SESSION; //Notice the 'GLOBAL $_SESSION' --- not needed right? Well without it the Windows PHP/Apache Environment crashes.

global $_sessionPrefix;
$_sessionPrefix = APPLICATION_NAME;

function getSessionAttribute($name) {
  global $_sessionPrefix;
  return $_SESSION[$_sessionPrefix.$name];
}

function hasSessionAttribute($name) {
  global $_sessionPrefix;
  return isset($_SESSION[$_sessionPrefix.$name]);
}

function setSessionAttribute($name, $value) {
  global $_sessionPrefix;
  $_SESSION[$_sessionPrefix.$name] = $value;
}

function removeSessionAttribute($name) {
  global $_sessionPrefix;
  unset($_SESSION[$_sessionPrefix.$name]);
}

function cleanSession () {
  global $_sessionPrefix;
  foreach ($_SESSION as $key => $value) {
    $pos = strpos("--".$key, $_sessionPrefix);
    if ($pos != false) {
      unset($_SESSION[$key]);
    }
  }
}

/*
 * $Log: SessionHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.2  2003/10/22 16:18:15  chmielu
 * Application functionality added.
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */


?>