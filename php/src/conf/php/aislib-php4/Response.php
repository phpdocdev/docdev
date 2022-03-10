<?php

require_once "aislib/TemplateHandler.php";

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @author <a href="mailto:ender@ais.pl">Ania Grzebieniak</a>
 * @version $Revision: 1.1 $
 */
class Aislib_Response {

  var $page;
  var $values = array();

  function Aislib_Response($page, $values) {
    $this->page   = $page;
    if ($values != null) {
      $this->values = $values;
    }
  }
}

/*
 * $Log: Response.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.7  2003/12/02 14:38:15  chmielu
 * Aislib class names chenged
 *
 * Revision 1.6  2003/10/27 09:51:56  chmielu
 * *** empty log message ***
 *
 * Revision 1.5  2003/10/24 13:01:04  chmielu
 * *** empty log message ***
 *
 * Revision 1.4  2003/10/23 17:37:24  chmielu
 * *** empty log message ***
 *
 * Revision 1.3  2003/10/22 16:18:15  chmielu
 * Application functionality added.
 *
 * Revision 1.2  2003/10/08 12:36:37  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:56  chmielu
 * Initial import
 *
 * Revision 1.2  2003/07/16 14:19:34  ender
 * *** empty log message ***
 *
 * Revision 1.1.1.1  2003/06/18 13:45:17  ender
 * Initial import
 *
 *
 *
 */


?>