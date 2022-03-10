<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/form/Message.php";
require_once "aislib/form/Field.php";

class Aislib_MessagesHandler {

  var $file;
  var $parser;
  var $validated;

  var $_current_message;
  var $_current_text;

  var $messages;
  

  function Aislib_MessagesHandler($file = "config/messages.xml") {
    $this->file      = $file;
    $this->validated = false;
    $this->messages  = array();
  }


  function getMessages() {
    if (!$this->validated) {
      $this->validate();
    }
    return $this->messages;
  }

  function _startElement($parser, $name, $attrs) {
    if (strcmp(strtoupper($name), "MESSAGE") == 0) {
      $this->_current_message = new Aislib_Message(intval($attrs['CODE']));
    } else {
      $this->_current_text = "";
    }
  }

  function _endElement($parser, $name) {
    if (strcmp(strtoupper($name), "MESSAGE") == 0) {
      $this->messages = array_merge($this->messages, array($this->_current_message));
    } else if (strcmp(strtoupper($name), "KEY") == 0) {
      $this->_current_message->key = $this->_current_text;
    } else if (strcmp(strtoupper($name), "CONTENT") == 0) {
      $this->_current_message->content = $this->_current_text;
    }
  }
  function _dataElement($parser, $data) {
    $this->_current_text .= $data;
  }

  function _free() {
    xml_parser_free($this->parser);
  }

  function validate() {
    $this->parser   = xml_parser_create();

    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_element_handler($this->parser, array(&$this, "_startElement"), array(&$this, "_endElement"));
    xml_set_character_data_handler($this->parser, array(&$this, "_dataElement"));
    register_shutdown_function(array(&$this, "_free")); //make a destructor

    if (!($fp = fopen($this->file, "r"))) {
      die("Could not open XML input '$this->file'");
    }
    
    while ($data = fread($fp, 4096)) {
      if (!xml_parse($this->parser, $data, feof($fp))) {
        die(sprintf("XML error: %s at line %d (%s)",
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser),
                    $this->file));
      }
    }
    $this->validated = true;
  }
    
}

/*
 * $Log: MessagesHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.3  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.2  2003/10/17 09:22:59  chmielu
 * Messages are available from Page
 *
 * Revision 1.1  2003/09/01 17:28:57  chmielu
 * Initial import
 *
 *
 *
 */


?>