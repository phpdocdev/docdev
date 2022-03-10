<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//
// $Id: NIC.php,v 1.0 2001/04/19 23:56:57
//
// NIC office utilities
//

#require_once "PEAR.php";

/**

  Params:
  Programmer: bob
  Mode: debug, live, demo
  
  
  
  $NIC = new NIC('DB', 'mysql', 'counselors');
    Module: DB
    Database ID: 
    Database: 
  
  $NIC_payment = new NIC('Payment/CC', );
    Module: 
    Project: project number
    Service: service code
    Orderid: orderid

  $NIC = new NIC('bob', 'live');
  $DB = $NIC->new('DB', 'mysql', 'counselors');
  $CC = $NIC->new('Payment', 'CC', 1232, 'agfc_lic', 12312312312312);    
  $LDAP = $NIC->new('LDAP');
  $Template = $NIC->new('Template');
  

 */


class NIC{

  function NIC($staff, $mode){
    $this->Config = array();  
    // import definition file
    $this->importDefs('NIC');
  }
  
  function create(){
    // put parameters in array
    $p = &func_get_args();
    
    // return an object based on parameters
    include_once('NIC/' . $p[0] . '.php');
    
    // create a string to eval
    $e = array();
    for($i=1; $i<func_num_args(); $i++){
      $e[] = '$p['.$i.']';
    }
    $eval = '$obj = new '.$p[0].'(' . join(',', $e) . ');';

    eval($eval);
    
    return $obj;
     
  }
  
  function routeError(){
  
  }
  
  function importDefs($filename){  
    // include the file
    include('NIC/NICConfig/' . $filename . '.php');
    $this->Config[$filename] = $Settings;    
  }
  

}

?>
