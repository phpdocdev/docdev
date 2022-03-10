<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "adodb/adodb-time.inc.php";
//require_once "Date.php";

class Aislib_DateTimeHandler {

  function getdate($d=false,$fast=false) {
    return adodb_getdate($d, $fast);
  }

  function date($fmt,$d=false,$is_gmt=false) {
    if ((!$d) | ($d == null)) {
      return null;
    }
    return adodb_date($fmt,$d,$is_gmt);
  }

  function gmdate($fmt,$d=false) {
    if ((!$d) | ($d == null)) {
      return null;
    }
    return adodb_gmdate($fmt,$d);
  }

  function mktime($hr,$min,$sec,$mon,$day,$year,$is_dst=false,$is_gmt=false) {
    return adodb_mktime($hr,$min,$sec,$mon,$day,$year,$is_dst,$is_gmt);
  }

  function gmmktime($hr,$min,$sec,$mon,$day,$year,$is_dst=false) {
    return adodb_gmmktime($hr,$min,$sec,$mon,$day,$year,$is_dst=false);
  }

  function now() {
    return mktime();
  }
  function strtotime($s, $now=null) {
    if ((!$s) || ($s == "")) {
      return null;
    }
    list($year, $month, $day, $hour, $minute, $second) = sscanf($s, "%04u-%02u-%02u %02u:%02u:%02u");
    return Aislib_DateTimeHandler::mktime($hour, $minute, $second, $month, $day, $year, 0);
    //    $d = new Date($s);
    //    return Aislib_DateTimeHandler::mktime($d->hour, $d->minute, $d->second, $d->month, $d->day, $d->year, 0);
  }
  function stripTime($date) {
    $timePieces = Aislib_DateTimeHandler::getdate($date);
    return Aislib_DateTimeHandler::mktime(0, 0, 0, $timePieces["mon"], $timePieces["mday"], $timePieces["year"]);
  }

  function add($timestamp, $seconds, $minutes, $hours, $days, $months, $years) {
    $timePieces = Aislib_DateTimeHandler::getdate($timestamp);
    return Aislib_DateTimeHandler::mktime($timePieces["hours"] + $hours,
                                          $timePieces["minutes"] + $minutes,
                                          $timePieces["seconds"] + $seconds,
                                          $timePieces["mon"] + $months,
                                          $timePieces["mday"] + $days,
                                          $timePieces["year"] + $years);
  }
}


/*
 * $Log: DateTimeHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.11  2004/02/16 11:35:57  chmielu
 * 'add' method added
 *
 * Revision 1.10  2004/02/13 14:11:23  chmielu
 * stripTime function added
 *
 * Revision 1.9  2003/12/09 16:22:22  chmielu
 * *** empty log message ***
 *
 * Revision 1.8  2003/12/02 14:38:17  chmielu
 * Aislib class names chenged
 *
 * Revision 1.7  2003/12/01 10:46:40  chmielu
 * *** empty log message ***
 *
 * Revision 1.6  2003/11/28 13:38:23  chmielu
 * *** empty log message ***
 *
 * Revision 1.5  2003/11/28 13:22:14  chmielu
 * *** empty log message ***
 *
 * Revision 1.4  2003/11/28 13:13:39  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/11/12 10:42:25  chmielu
 * 'strtotime' method added
 *
 * Revision 1.2  2003/11/12 10:26:47  chmielu
 * 'now' method added
 *
 * Revision 1.1  2003/11/12 10:22:19  chmielu
 * DateTimeHandler added
 *
 *
 */


?>