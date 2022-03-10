<?php

/*
 * @author <a href="mailto:chmielu@ais.pl">Pawel Chmielewski</a>
 * @version $Revision: 1.3 $
 */

define("SERVICE_STATUS_UP",       1);
define("SERVICE_STATUS_UNKNOWN",  0);
define("SERVICE_STATUS_DOWN",    -1);

define ("MT_LIB_APPMGR_IMPL_XML_CONFIGURATION", "/web/app-data/ina/ina-services.xml");
//define ("MT_LIB_APPMGR_IMPL_XML_CONFIGURATION", "../metadata/mt/lib/appmgr/test/test.xml");

class ServiceParser {
  
  var $file;
  var $parser;

  var $offs;

  var $_current_id;
  var $_current_message;

  var $ids_already_handled;

  function ServiceParser($file, $appId) {
    
    $this->file     = $file;
    $this->offs     = array();
    $this->appId    = $appId;

    $this->ids_already_handled = array();
  }

  function _dataElement($parser, $data) {
  }

  function _startElement($parser, $name, $attrs) {

    if (strcmp(strtoupper($name), "SERVICE") == 0) {
      $this->_current_id = $attrs['ID'];
    }

    if (strcmp($this->_current_id, $this->appId) == 0) {

      if (strcmp(strtoupper($name), "OFF-GROUP") == 0) {
        $this->_current_message = $attrs['MESSAGE']; 
        
      } else if (strcmp(strtoupper($name), "OFF") == 0) {
        $off = new Off($this->_current_message);
        $this->offs = array_merge($this->offs, $off->parse($attrs));

      } else if (strcmp(strtoupper($name), "DEP") == 0) {

        $service = new ServiceParser($this->file, $attrs['SERVICE-ID']);
        $service->ids_already_handled = array_merge($this->ids_already_handled, array($this->appId));
        
        $this->offs = array_merge($this->offs, $service->getOffs());
      }
    }

  }

  function _endElement($parser, $name) {
  }

  function _free() {
    xml_parser_free($this->parser);
  }

  function getOffs() {

    if (in_array($this->appId, $this->ids_already_handled)) {
      return array();
    }

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
        die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser)));
      }
    }
    
    return $this->offs;
  }


}


class Off {
  
  var $message;

  var $type;

  var $value;
  var $calValue;

  var $from;
  var $calFrom;

  var $to;
  var $calTo;

    //    $DAY_FORMAT     = "MM/dd/yyyy";
    //    $FROM_TO_FORMAT = "HH:mm";

  function Off($message) {
    $this->message = $message;
  }
  
  function parse($attributies) {

    /* temp: */
     $this->value = $attributies['VALUE'];
     $this->from  = $attributies['FROM'];
     $this->to    = $attributies['TO'];
     /* */

    $this->type = strtolower($attributies['TYPE']);

    if (strcmp($this->type, "day") == 0) {
      $t = explode("/", $attributies['VALUE']);
      $this->calValue = mktime (0, 0, 0, $t[0], $t[1], $t[2]);

    } else if (strcmp($this->type, "weekly") == 0) {
      $this->calValue = intval($attributies['VALUE']);

    } else if (strcmp($this->type, "daily") == 0) {
      ;
    } else if (strcmp($this->type, "now") == 0) {
      ;
    } else {
      die("Unknown off type '".$this->type."'");
    }

    if ($attributies['FROM'] != null) {
      $t = explode(":", $attributies['FROM']);
      $this->calFrom = mktime ($t[0], $t[1]);
    }
    if ($attributies['TO'] != null) {
      $t = explode(":", $attributies['TO']);
      $this->calTo = mktime ($t[0], $t[1]);
    }

    $result = array();

    if ((strcmp($this->type, "now") != 0) 
        && ($this->calFrom != null) 
        && ($this->calTo != null) 
        && ($this->calFrom > $this->calTo)) {

      $offFrom = new Off($this->message);
      $result = array_merge($result, 
                            $offFrom->parse(array('TYPE'  => $this->type,
                                                  'VALUE' => $this->value,
                                                  'FROM'  => $attributies['FROM'])));

      $offTo = new Off($this->message);
      $arr = array('TYPE' => $this->type,
                   'TO'   => $attributies['TO']);
      if (strcmp($this->type, "day") == 0) {
        $arr = array_merge($arr, array('VALUE' => strftime("%m/%d/%Y", $this->calValue + 60*60*24)));
      } else if (strcmp($this->type, "weekly") == 0) {
        $arr = array_merge($arr, array('VALUE' => (($this->calValue + 1) % 7)));
      } else {
        $arr = array_merge($arr, array('VALUE' => $attributies['VALUE']));
      }
      $result = array_merge($result, $offTo->parse($arr));

    } else {
      $result = array_merge($result, array($this));
    }

    return $result;
  }


  function getStatus($when) {

    $match = false;
    if (strcmp($this->type, "now") == 0) {
      $match = true;
    } else if (strcmp($this->type, "day") == 0) {
      $when_f = mktime(0, 0, 0, date("n", $when), date("j", $when), date("Y", $when));
      $bound = mktime(0, 0, 0, date("n", $this->calValue), date("j", $this->calValue), date("Y", $this->calValue));
      if ($when_f == $bound) {
        $match = $this->_matchFromTo($when);
      }
    } else if (strcmp($this->type, "weekly") == 0) {
      if (intval(date("w", $when)) == $this->calValue) {
        $match = $this->_matchFromTo($when);
      }
    } else if (strcmp($this->type, "daily") == 0) {
      $match = $this->_matchFromTo($when);
    }
    
    if ($match == true) {
      return SERVICE_STATUS_DOWN;
    } else {
      return SERVICE_STATUS_UP;
    }
  }

  function _matchFromTo($when) {
    
    $from = $this->_getFrom($when);
    $to   = $this->_getTo($when);

//     print ("\nWHEN: ".$when);
//     print ("\nFROM: ".$from);
//     print ("\nTO  : ".$to  );
//     print ("\n");
    if (($when >= $from) && ($when < $to)) {
      return true;
    }
    return false;
  }

  function _getFrom($when) {
    $result = 0;
    if ($this->calFrom != null) {
      $result = mktime(date("G", $this->calFrom), date("i",$this->calFrom), 0, date("n", $when), date("j", $when), date("Y", $when));
    } else {
      $result = mktime(0, 0, 0, date("n", $when), date("j", $when), date("Y", $when));
    }
    return $result;
  }

  function _getTo($when) {
    $result = 0;
    if ($this->calTo != null) {
      $result = mktime(date("G", $this->calTo), date("i",$this->calTo), 0, date("n", $when), date("j", $when), date("Y", $when));
    } else {
      $result = mktime(0, 0, 0, date("n", $when), intval(date("j", $when)) + 1, date("Y", $when));
    }
    return $result;
  }

  function getUpTime($when) {
    if (strcmp($this->type, "now") == 0) {
      return null;
    } else if ((strcmp($this->type, "day") == 0) || (strcmp($this->type, "weekly") == 0)) {
      return $this->_getTo($when);
    } else if (strcmp($this->type, "daily") == 0) {      
      if (($this->calFrom == null) && ($this->calTo == null)) {
        return null;
      } else {
        return $this->_getTo($when);
      }
    } else {
      return mktime(0, 0, 0, date("n", $when), intval(date("j", $when)) + 1, date("Y", $when));
    }
  }

}


class ServiceStatus {

  var $serviceParser;
  var $offs;

  function ServiceStatus($serviceId) {
    $this->serviceParser = new ServiceParser(MT_LIB_APPMGR_IMPL_XML_CONFIGURATION, $serviceId);
    $this->offs = $this->serviceParser->getOffs();
    //    print_r($this->offs);
  }
  
  function getStatus($when) {
    foreach($this->offs as $off) {
        //  print_r($off);
        //  print($off->getStatus($when)."\n");      
      if ($off->getStatus($when) == SERVICE_STATUS_DOWN) {
        return SERVICE_STATUS_DOWN;
      }
    }
    return SERVICE_STATUS_UP;
  }

  function getUpTime($when) {
    $limit = $when + 60*60*24*31; // plus month
    $result = $this->_getUpTime($when, $limit);
    if (($result != null) && ($result > $limit)) {
      return null;
    }
    return $result;
  }

  function getMessage($when) {
    $result = null;
    $upTime = null;

    foreach($this->offs as $off) {
      if ($off->getStatus($when) == SERVICE_STATUS_DOWN) {

        if (strcmp($off->type, "now") == 0) {
          return $off->message;
        }
        
        $offUpTime = $off->getUpTime($when);
        if ($upTime != null) {
          if ($upTime != $this->_getLater($upTime, $offUpTime)) {
            $result = $off->message;
            $upTime = $offUpTime;
          }
        } else {
          $result = $off->message;
          $upTime = $offUpTime;
        }        
      }
    }

    if ($upTime != null) {
      $nextDate = null;
      while (($nextDate = $this->getUpTime($upTime)) != null) {
        $nextMsg = $this->getMessage($upTime);
        $upTime = $nextDate;
        if (($nextMsg != null) && (strcmp(trim($nextMsg), "") != 0)) {
          $result = $nextMsg;
        }
      }
    }

    return $result;
  }

  function _getUpTime($when, $limit) {
    if ($when > $limit) {
      return null;
    }

    $result = null;

    foreach($this->offs as $off) {
      if ($off->getStatus($when) == SERVICE_STATUS_DOWN) {
        
        if (strcmp($off->type, "now") == 0) {
          return null;
        }
        
        $result = $this->_getLater($result, $off->getUpTime($when));        
      }
    }

    if ($result != null) {      
      while (($nextResult = $this->_getUpTime($result, $limit)) != null) {
        $result = $nextResult;
      }
    }
    
    return $result;
  }

  function _getLater($prevDate, $nextDate) {
    if ($prevDate == null) {
      return $nextDate;
    }
    if ($nextDate == null) {
      return $prevDate;
    }
    if ($nextDate > $prevDate) {
      return $nextDate;
    }
    return $prevDate;
  }

}


/**
 * $Log: ServiceStatus.php,v $
 * Revision 1.3  2003/03/12 16:11:15  chmielu
 * Bug fixed
 *
 * Revision 1.2  2003/03/05 13:16:44  chmielu
 * Slight changes
 *
 * Revision 1.1  2003/03/05 12:24:47  chmielu
 * Added PHP implementation of Application Manager
 *
 *
 */


?>
