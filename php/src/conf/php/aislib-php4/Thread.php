<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

class Aislib_Thread {

  function start() {

    $this->run();

    // TODO Uncomment me when fork is available
//     $pid = pcntl_fork();
//     if ($pid == -1) {
//       die("could not fork");
//     } else if ($pid) {
//       // we are the parent
//     } else {
//       // we are the child
//       $this->run();
//       exit();
//     }

  }

}


/*
 * $Log: Thread.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.2  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 *
 *
 */

?>