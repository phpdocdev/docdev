<?php

/*
 * @author <a href="mailto:maciejk@ais.pl">Maciej Kita</a>
 * @version $Revision: 1.1 $
 */

require_once "aislib/prototype/PrototypeXmlHandler.php";
require_once "aislib/TemplateHandler.php";
require_once "aislib/Application.php";

class Aislib_Prototype extends Aislib_Application {

  function Aislib_Prototype($name) {
    $this->name = $name;
  }

  function getParameter($name) {
    if (strcmp(strtolower($_SERVER['REQUEST_METHOD']), "post") == 0) {
      if (array_key_exists($name, $_POST)) {
        return $_POST[$name];
      }
    } else {
      if (array_key_exists($name, $_GET)) {
        return $_GET[$name];
      }
    }
    return null;
  }

  function getParameterMap() {
    if (strcmp(strtolower($_SERVER['REQUEST_METHOD']), "post") == 0) {
      return $_POST;
    } else {
      return $_GET;
    }
  }

  function hasParameter($name) {
    if (strcmp(strtolower($_SERVER['REQUEST_METHOD']), "post") == 0) {
      return isset($_POST[$name]);
    } else {
      return isset($_GET[$name]);
    }
  }

  function dispatch()  {
    // Parse prototype.xml
    $xmlHandler = new Aislib_PrototypeXmlHandler("config/prototype.xml");

    // Which page to show?
    $page = $this->getParameter("page");
    if ($page == "") {
      $page = $xmlHandler->getStartPage();
    }

    // Check if we're to go to another page - check triggers for this page
    $trg = $xmlHandler->getTriggersForPage($page);
    foreach ($trg as $trigger) {
      if ($this->hasParameter($trigger->name) && 
          ($trigger->value == null || $this->getParameter($trigger->name) == $trigger->value)) {
        $page = $trigger->ref;
      }
    }

    if ($this->template == null) {
      $this->template = new Aislib_TemplateHandler();
    }
    $data = $xmlHandler->getData();
    $this->template->doNotEscape = array_merge($this->template->doNotEscape, $xmlHandler->doNotEscape);


    // Generate 'generated' triggers for this page
    $trg = $xmlHandler->getTriggersForPage($page);
    $linkTemplate = new Aislib_TemplateHandler();
    $linkTemplate->template_dir = "aislib/etc/templates/";
    $linkTemplate->setValues(array("triggers" => $trg,
                                   "page" => $page,
                                   "APPLICATION_NAME" => $this->name));
    $generated_links = $linkTemplate->fetch("generatedlinks.html");



    if ($xmlHandler->getSlot() != null) {
      array_push($this->template->doNotEscape, $xmlHandler->getSlot());
      $this->template->setValues(array($xmlHandler->getSlot() => $generated_links));
    } else {
      echo $generated_links;
    }

    $this->template->setValues($data["__generic"]);
    $this->template->setValues($data[$page]);
    $this->template->setValues(array("page" => $page, "encoded_url" => $this->encodedUrl, "APPLICATION_NAME" => $this->name));

    echo $this->template->fetch($xmlHandler->getTemplateNameForPage($page));
  }
}
/**
 * $Log: Prototype.php,v $
 * Revision 1.1  2007/07/09 16:12:10  cvsadmin
 * add aislib
 *
 * Revision 1.1.1.1  2003/12/09 14:00:41  ais
 *
 *
 *
 *
 */

?>