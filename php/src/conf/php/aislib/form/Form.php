<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/form/FormHandler.php";
require_once "aislib/form/MessagesHandler.php";
require_once "aislib/LogHandler.php";

class Aislib_Form {

  var $fields;
  var $rules;
  var $messages;
 
  var $messageCodes;
  var $values;
  var $originalValues;

  var $log;

  function Aislib_Form($formName, $log = null) {
    $formHandler = new Aislib_FormHandler($formName);
    $this->fields = $formHandler->getFields();
    $this->rules  = $formHandler->getRules();

    $messagesHandler = new Aislib_MessagesHandler();
    $this->messages = $messagesHandler->getMessages();

    $this->log = Aislib_LogHandler::instantiate($log);
  }

  function getFields() {
    return $this->fields;
  }
  function getFieldNames() {
    $result = array();
    foreach ($this->fields as $f) {
      $result = array_merge($result, array($f->name => ""));
    }
    return $result;
  }

  function validate($fieldValues) {

    $this->messageCodes = array();
    $this->values       = array();

    $this->originalValues  = array();
    foreach ($this->fields as $field) {
      $this->originalValues = array_merge($this->originalValues, array($field->name => $fieldValues[$field->name]));
    }

    /* Initialize result */
    $result = true;

    /* Find all fields which are connected with rules */
    $rulesFields = array();
    foreach ($this->rules as $rule) {
      $rulesFields = array_merge($rulesFields, $this->_getFieldsForRule($rule));
    }

    /* All fields which are not connected with rules */
    $fieldsToValidation = array_diff_assoc($this->fields, $rulesFields);

    $fieldsValidated = array();
    $fieldsSuccessfulyValidated = array();


    /* Validate fields, which are not connected with fields: */
    foreach ($fieldsToValidation as $field) {
      $this->log->debug("Validating field ".$field->name."...");
      if (!$field->validate($fieldValues[$field->name])) { 
        $this->messageCodes = array_merge($this->messageCodes, array($field->messageCode));
        $this->log->debug("Validating field ".$field->name."... failed ('".$fieldValues[$field->name]."').");
        $result = false;
      } else {
        $fieldsSuccessfulyValidated = array_merge($fieldsSuccessfulyValidated, array($field->name => $field));
        $this->_addValue($field->name, $fieldValues[$field->name]);
      }
      $fieldsValidated = array_merge($fieldsValidated, array($field->name => $field));
    }

    /* Validate rules: */
    foreach ($this->rules as $rule) {

      $fieldsForRule = $this->_getFieldsForRule($rule);
      $resultForRule = true;

      /* Check if this rule should be validates */
      if ($rule->conditionalValidate($this->_getValuesForRule($rule, $fieldValues))) {

        /* Get fields for rule and validate them */
        foreach ($fieldsForRule as $field) {
          if (!in_array($field->name, array_keys($fieldsValidated))) {
            $this->log->debug("Validating field ".$field->name."...");
            if (!$field->validate($fieldValues[$field->name])) {
              $this->log->debug("Validating field ".$field->name."... failed ('".$fieldValues[$field->name]."').");
              $this->messageCodes = array_merge($this->messageCodes, array($field->messageCode));
              $result = false;
              $resultForRule = false;
            } else {
              $fieldsSuccessfulyValidated = array_merge($fieldsSuccessfulyValidated, array($field->name => $field));
              $this->_addValue($field->name, $fieldValues[$field->name]);
            }
            $fieldsValidated = array_merge($fieldsValidated, array($field->name => $field));
          } else {
            if (!in_array($field->name, array_keys($fieldsSuccessfulyValidated))) {
              $resultForRule = false;
            }
          }
        }

        /* If all fields for this rule validated successfuly, validate rule */
        if ($resultForRule) {
          $map = $this->_getValuesForRule($rule, $this->getValues());
          $this->log->debug("Validating rule ".$rule->name."...");
          if (!$rule->validate($map)) {
          $this->log->debug("Validating rule ".$rule->name."... failed.");
            $this->messageCodes = array_merge($this->messageCodes, array($rule->messageCode));
            $result = false;
          }
        }
      }
    }

    return $result;
  }

  function _getFieldsForRule($rule) {
    $result = array();
    foreach ($rule->mappings as $mapping) {
      foreach ($this->fields as $field) {
        if (strcmp($mapping->name, $field->name) == 0) {
          $result = array_merge($result, array($field->name => $field));
          break;
        }
      }
    }
    return $result;
  }

  function _getValuesForRule($rule, $fromMap) {
    $result = array();
    foreach ($rule->mappings as $mapping) {
      $result = array_merge($result, array($mapping->value => $fromMap[$mapping->name]));
    }
    return $result;
  }

  function _addValue($name, $value) {
    $this->values = array_merge($this->values, array($name => $value));
  }

  function getValues() {
    return $this->values;
  }

  function getOriginalValues() {
    //return $this->trimValues($this->originalValues);
    return $this->originalValues;
  }

  function getMessageContents() {
    return $this->_getMessages("content");
  }

  function getMessageKeys() {
    return $this->_getMessages("key");
  }

  function getMessageCodes() {
    return $this->messageCodes;
  }

  function getMessage($code) {
    foreach ($this->messages as $msg) {
      if ($msg->code == $code) {
        return $msg;
      }
    }
    return null;
  }

  function getMessages() {
    $result = array();
    foreach ($this->messageCodes as $msgCode) {
      foreach ($this->messages as $msg) {
        if ($msg->code == $msgCode) {
          $result = array_merge($result, array($msg));
          break;
        }
      }
    }
    return $result;
  }

  function _getMessages($whatField) {
    $result = array();
    $msgs = $this->getMessages();
    foreach ($msgs as $msg) {
      $result = array_merge($result, $msg->$whatField);
    }
    return $result;
  }

  function trimValues($values) {

    $trimedValues = array();
    foreach ($values as $value)
      array_push($trimedValues, trim($value));
    return $trimedValues;
  }

}

/*
 * $Log: Form.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.5  2003/12/02 14:38:16  chmielu
 * Aislib class names chenged
 *
 * Revision 1.4  2003/10/17 09:22:59  chmielu
 * Messages are available from Page
 *
 * Revision 1.3  2003/10/08 12:36:37  chmielu
 * *** empty log message ***
 *
 * Revision 1.2  2003/09/09 11:29:44  chmielu
 * *** empty log message ***
 *
 * Revision 1.1  2003/09/01 17:28:57  chmielu
 * Initial import
 *
 *
 *
 */


?>
