<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/form/Form.php";
require_once "aislib/form/Field.php";

class Aislib_FormHandler {

  var $file;
  var $formName;
  var $parser;
  var $validated;

  var $_current_formName;
  var $_current_field;
  var $_current_validation;
  var $_current_rule;

  var $fields;
  var $rules;

  function Aislib_FormHandler($formName, $file = "config/forms.xml") {
    $this->file     = $file;
    $this->formName = $formName;
    $this->validated = false;
    $this->fields   = array();
    $this->rules    = array();
  }


  function getFields() {
    if (!$this->validated) {
      $this->validate();
    }
    return $this->fields;
  }

  function getRules() {
    if (!$this->validated) {
      $this->validate();
    }
    return $this->rules;
  }

  function _startElement($parser, $name, $attrs) {

    if (strcmp(strtoupper($name), "FORM") == 0) {
      $this->_current_formName = $attrs['NAME'];
    }

    if (strcmp($this->_current_formName, $this->formName) == 0) {

      if (strcmp(strtoupper($name), "FIELD") == 0) {
        $this->_current_field = new Aislib_Field($attrs['NAME'], $attrs['CLASS']);
        
      } else if (strcmp(strtoupper($name), "VALIDATION") == 0) {
        $this->_current_validation = new Aislib_Validation($attrs['MSG-CODE']);

      } else if (strcmp(strtoupper($name), "PROPERTY") == 0) {
        $prop = new Aislib_Property($attrs['NAME'], $attrs['VALUE']);
        if ($this->_current_validation != null) {
          $this->_current_validation->addProperty($prop);
        }
        if ($this->_current_rule != null) {
          $this->_current_rule->addProperty($prop);
        }

      } else if (strcmp(strtoupper($name), "RULE") == 0) {
        $this->_current_rule = new Aislib_Rule($attrs['NAME'], $attrs['CLASS'], $attrs['MSG-CODE']);

      } else if (strcmp(strtoupper($name), "MAPPING") == 0) {
        $prop = new Aislib_Property($attrs['FIELD-NAME'], $attrs['RULE-PARAM']);
        $this->_current_rule->addMapping($prop);

      }
    }

  }

  function _endElement($parser, $name) {
    if (strcmp($this->_current_formName, $this->formName) == 0) {
      if (strcmp(strtoupper($name), "FIELD") == 0) {
        $this->fields = array_merge($this->fields, array($this->_current_field->name => $this->_current_field));
        $this->_current_field = null;
      } else if (strcmp(strtoupper($name), "VALIDATION") == 0) {
        $this->_current_field->addValidation($this->_current_validation);
        $this->_current_validation = null;
      } else if (strcmp(strtoupper($name), "RULE") == 0) {
        $this->rules = array_merge($this->rules, array($this->_current_rule->name => $this->_current_rule));
        $this->_current_rule = null;
      }
    }
  }

  function _dataElement($parser, $data) {
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
    $this->validated = true;
  }
    
}

/*
 * $Log: FormHandler.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.4  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.3  2003/10/17 09:22:59  chmielu
 * Messages are available from Page
 *
 * Revision 1.2  2003/10/08 12:36:37  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:57  chmielu
 * Initial import
 *
 *
 *
 *
 */

?>