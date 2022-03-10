<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/MailHandler.php";
include_once "Mail.php";


class Aislib_SMTPMailHandler extends Aislib_MailHandler {

  var $mailHost;

  function Aislib_SMTPMailHandler($mailHost, $to, $from, $subject) {
    $this->mailHost = $mailHost;
    $this->to = $to;
    $this->from = $from;
    $this->subject = $subject;
  }


  function run() {
    $headers['Subject'] = $this->subject;
    $headers['From']    = $this->from;
    $headers['To']      = $this->to;
    $params['host']     = $this->mailHost;
    $mailer = &Mail::factory('smtp', $params);
    $status =$mailer->send($this->to, $headers, $this->message);
    if (PEAR::isError($status)) {
      // nothing...
    }
  }

}


/*
 * $Log: SMTPMailHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.1  2004/03/12 08:40:04  chmielu
 * SMTP MailHandler added
 *
 *
 *
 *
 *
 */

?>
