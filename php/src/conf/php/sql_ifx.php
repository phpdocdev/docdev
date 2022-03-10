<?php

class SQL_ifx{

  var $conn_id;
  var $q_res;
  var $database;
 
  function SQL_ifx($database){ 
		error_log("$PHP_SELF: Informix connection to $database", 0);
    $this->conn_id = ifx_connect("$database@ina_se") or printf("Error on %s@%s: %s<BR>", $dbname, $dbhost, ifx_errormsg());        
    $this->database = $database;
  }


  function Run($sql){
    $this->q_res = ifx_query($sql, $this->conn_id) or die("Invalid query: ".$sql);    
    return $this->q_res;
  }

  function fetchrow($sql){

    $this->q_res = ifx_query($sql, $this->conn_id) or die("Invalid query: $sql");    
    $temp = ifx_fetch_row($this->q_res);  
    if($temp){
      while(list($k,$v)=each($temp)){
        $temp[$k] = stripslashes($v);
      }    
      reset($temp);
    }
    return $temp;
  }

  function fetch($qry){
    $temp = ifx_fetch_array($qry);    
    if($temp){
      while(list($k,$v)=each($temp)){
        $temp[$k] = stripslashes($v);
      }    
      reset($temp);
      }
    return $temp;     
  }

}

?>
