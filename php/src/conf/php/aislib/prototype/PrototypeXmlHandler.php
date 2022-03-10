<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version 1.1
 */

require_once "aislib/form/Form.php";

class Object {}

class Aislib_Trigger {
	var $name;
	var $value;
	var $ref;
	var $type;
	var $caption;
	
	function Aislib_Trigger($name, $value, $ref, $type, $caption) {
		$this->name = $name;
		$this->value = $value;
		$this->ref = $ref;
		$this->type = $type;
		$this->caption = $caption;
	}
	
}

class Aislib_PrototypeXmlHandler {

  var $file;
  var $parser;
  
  var $start_page;
  var $_current_page;
  
  var $data;
  var $triggers;
  var $page_templates;
  
  var $object_stack;
  
  var $slot;
  var $doNotEscape;

  var $phpTag;
  var $phpCode;

  function Aislib_PrototypeXmlHandler($file) {
    $this->file     = $file;
    $this->object_stack = array();
    $this->data = array();
    $this->triggers = array();
    $this->page_templates = array();
    $this->_current_page = "__generic";
    $this->doNotEscape = array();
    $this->phpTag = false;
    $this->phpCode = "";
    $this->validate();
  }
  
  function getStartPage() {
  	return $this->start_page;
  }
  
  function getData() {
  	return $this->data;
  }

  function getTriggersForPage($page) {
  	return $this->triggers[$page];
  }

  function getTemplateNameForPage($page) {
  	return $this->page_templates[$page];
  }

  function getSlot() {
  	return $this->slot;
  }

  function _startElement($parser, $name, $attrs) {
    if (strcmp(strtoupper($name), "PROTOTYPE") == 0) {
      $this->start_page = $attrs['START'];
      $this->slot = $attrs['SLOT'];
    }

    if (strcmp(strtoupper($name), "PHP") == 0) {
      $this->phpCode = "";
      $this->phpTag  = true;
    }

    if (strcmp(strtoupper($name), "PAGE") == 0) {
    	$parent = $attrs['EXTENDS'];
      $this->_current_page = $attrs['NAME'];
      // Start new page
      if ($parent == null) {
        $this->data[$this->_current_page] = array();
      } else {
      	$this->data[$this->_current_page] = $this->data[$parent];
      }
      $this->triggers[$this->_current_page] = array();
      $this->page_templates[$this->_current_page] = $attrs['TEMPLATE'];
    }

    if (strcmp(strtoupper($name), "TRIGGER") == 0) {
      array_push($this->triggers[$this->_current_page], new Aislib_Trigger($attrs['NAME'], $attrs['VALUE'], 
         $attrs['REF'], $attrs['TYPE'], $attrs['CAPTION']));
    }

    if (strcmp(strtoupper($name), "TEXT") == 0) {
    	$value = $attrs['VALUE'];
        if ($attrs['PHP']) {
          $value = eval($attrs['PHP']);
        }
        if (($attrs['ESCAPE']) && ($attrs['ESCAPE'] == 'false')) {
          $this->doNotEscape[] = $attrs['NAME'];
        }
    	if (sizeof($this->object_stack) > 0) {
    		// We're inside of an object or array - assign property or set value
        $object = array_pop($this->object_stack);
        if (is_array($object)) {
          $object[$attrs['NAME']] = $value;
        } else {
          $object->$attrs['NAME'] = $value;
        }
        array_push($this->object_stack, $object);
    	} else {
    		// Create an array entry
        $this->data[$this->_current_page][$attrs['NAME']] = $value;
    	}
    }

    if (strcmp(strtoupper($name), "MESSAGE") == 0) {
    	$form = new Aislib_Form("");
    	$message = $form->getMessage($attrs['KEY']);
    	$value = $message->content;
    	if (sizeof($this->object_stack) > 0) {
    		// We're inside of an object or array - assign property or set value
        $object = array_pop($this->object_stack);
        if (is_array($object)) {
          $object[$attrs['NAME']] = $value;
        } else {
          $object->$attrs['NAME'] = $value;
        }
        array_push($this->object_stack, $object);
    	} else {
    		// Create an array entry
        $this->data[$this->_current_page][$attrs['NAME']] = $value;
    	}
    }

    if (strcmp(strtoupper($name), "OBJECT") == 0) {
    	$object = new Object();
    	$object->__name = $attrs['NAME'];
    	array_push($this->object_stack, $object);
    }

    if (strcmp(strtoupper($name), "ARRAY") == 0) {
    	$object = array();
    	$object['__name'] = $attrs['NAME'];
    	array_push($this->object_stack, $object);
    }
  }

  function _endElement($parser, $name) {
    if (strcmp(strtoupper($name), "PAGE") == 0) {
      $this->_current_page = "__generic";
    }

    if (strcmp(strtoupper($name), "PHP") == 0) {
      $this->phpTag  = false;
      eval($this->phpCode);
    }

    if (strcmp(strtoupper($name), "OBJECT") == 0 || strcmp(strtoupper($name), "ARRAY") == 0) {
    	$object = array_pop($this->object_stack);
      if (is_array($object)) {
        $name = $object['__name'];
        unset($object['__name']);
      } else {
        $name = $object->__name;
        unset($object->__name);
      }
    	if (sizeof($this->object_stack) > 0) {
    		// We're inside of an object - assign property
        $object2 = array_pop($this->object_stack);
        if (is_array($object2)) {
          $object2[$name] = $object;
        } else {
          $object2->$name = $object;
        }
        array_push($this->object_stack, $object2);
    	} else {
    		// Create an array entry
        $this->data[$this->_current_page][$name] = $object;
    	}
    }
  }

  function _dataElement($parser, $data) {
    if ($this->phpTag) {
      $this->phpCode .= $data;
    }
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
      die("could not open XML input");
    }
    
    while ($data = fread($fp, 4096)) {
      if (!xml_parse($this->parser, $data, feof($fp))) {
        die(sprintf("XML error: %s at line %d (%s)",
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser),
                    $this->file));
      }
    }
  }
    
}

/**
 * $Log: PrototypeXmlHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 *
 */

?>