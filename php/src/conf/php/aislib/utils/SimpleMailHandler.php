<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/Thread.php";
require_once "aislib/MailHandler.php";

class Aislib_SimpleMailHandler extends Aislib_MailHandler {

  function Aislib_SimpleMailHandler($to, $from, $subject) {
    $this->to = $to;
    $this->from = $from;
    $this->subject = $subject;
  }

  function run() {
    $res = mail($this->to,
                $this->subject,
                $this->message,
                "From: ".$this->from."\r\n");
  }

}


/*
 * $Log: SimpleMailHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.1  2004/03/12 08:40:04  chmielu
 * SMTP MailHandler added
 *
 *
 *
 *
 */

?>