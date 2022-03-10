<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/form/RegExpValidator.php";
require_once "aislib/form/ArrayValidator.php";

class Aislib_Field {
  var $name;
  var $class;  
  var $validations;
  var $messageCode;
  function Aislib_Field($name, $class) {
    $this->name        = $name;
    $this->class       = $class;
    $this->validations = array();
  }
  function addValidation($validation) {
    $this->validations = array_merge($this->validations, array($validation));
  }
  function validate($value) {
    $validatorClass = new $this->class();
    foreach ($this->validations as $validator) {
      if (!$validatorClass->validate($validator->properties, $value)) {
        $this->messageCode = $validator->messageCode;
        return false;
      }
    }
    return true;
  }
}

class Aislib_Validation {
  var $messageCode;
  var $properties;
  function Aislib_Validation($messageCode) {
    $this->messageCode = $messageCode;
    $this->properties  = array();
  }
  function addProperty($property) {
    $this->properties = array_merge($this->properties, array($property));
  }
}

class Aislib_Property {
  var $name;
  var $value;
  function Aislib_Property($name, $value) {
    $this->name  = $name;
    $this->value = $value;
  }
}

class Aislib_Rule {
  var $name;
  var $class;  
  var $messageCode;
  var $properties;
  var $mappings;

  function Aislib_Rule($name, $class, $messageCode) {
    $this->name        = $name;
    $this->class       = $class;
    $this->messageCode = $messageCode;
    $this->properties  = array();
    $this->mappings    = array();
  }
  function addProperty($property) {
    $this->properties = array_merge($this->properties, array($property));
  }
  function addMapping($property) {
    $this->mappings = array_merge($this->mappings, array($property));
  }
  function getInstant() {
    $rule = new $this->class();
    foreach($this->properties as $property) {
      $propName = $property->name;
      $rule->$propName = $property->value;
    }
    return $rule;
  }
  function validate($values) {
    $rule = $this->getInstant();
    return $rule->validate($values);
  }
  function conditionalValidate($map) {
    $rule = $this->getInstant();
    $methods = get_class_methods(get_class($rule));
    if (in_array(strtolower("conditionalValidate"), $methods)) {
      return $rule->conditionalValidate($map);
    }
    return true;
  }
}

/*
 * $Log: Field.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.3  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.2  2003/09/02 06:51:06  chmielu
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