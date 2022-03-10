<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/Thread.php";

class Aislib_MailHandler extends Aislib_Thread {

  var $to;
  var $from;
  var $subject;
  var $message;

  function Aislib_MailHandler($mailHost, $to, $from, $subject) {
    /*
     * default instance of MailHandler:
     */
    require_once "aislib/utils/SMTPMailHandler.php";
    $this = new Aislib_SMTPMailHandler($mailHost, $to, $from, $subject);
    //    require_once "aislib/utils/SimpleMailHandler.php";
    //    $this = new Aislib_SimpleMailHandler($to, $from, $subject);
  }

  function setTo($to) {
    $this->to = $to;
  }
  function setFrom($from) {
    $this->from = $from;
  }
  function setSubject($subject) {
    $this->subject = $subject;
  }
  function setMessage($m) {
    $this->message = $m;
  }

}


/*
 * $Log: MailHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.7  2004/03/12 08:40:04  chmielu
 * SMTP MailHandler added
 *
 * Revision 1.6  2004/03/12 08:04:00  chmielu
 * SimpleMailHandler added
 *
 * Revision 1.5  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.4  2003/09/23 14:02:43  ender
 * to-parameter to MailHandler added
 *
 * Revision 1.3  2003/09/04 08:49:16  ender
 * redeclaration of MailHandler removed
 *
 * Revision 1.2  2003/09/03 16:42:50  chmielu
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
