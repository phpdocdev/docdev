<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * Derived from db_mysql.inc by Sascha Schumann <sascha@schumann.cx>
 *
 * $Id: db_msql.inc,v 1.1.1.1 2001/05/08 21:49:56 david Exp $
 *
 */ 

class DB_Sql {
  var $Host     = "";
  var $Database = "";

  var $Link_ID  = 0;
  var $Query_ID = 0;
  var $Record   = array();
  var $Row;

  var $Error    = "";
  
  var $Auto_Free = 0;     ## Set this to 1 for automatic msql_free_result()

  function connect() {
    if ( 0 == $this->Link_ID ) {
      $this->Link_ID=msql_pconnect($this->Host);
      if (!$this->Link_ID) {
        $this->halt("Link-ID == false, pconnect failed");
      }
	  if(!msql_select_db($this->Database, $this->Link_ID)) {
        $this->halt("cannot use database ".$this->Database);
      }
    }
  }

  function query($Query_String) {
    $this->connect();

#   printf("Debug: query = %s<br>\n", $Query_String);

    $this->Query_ID = msql_query($Query_String,$this->Link_ID);
    $this->Row   = 0;
    $this->Error = msql_error();
    if (!$this->Query_ID) {
      $this->halt("Invalid SQL: ".$Query_String);
    }

    return $this->Query_ID;
  }

  function next_record() {
    $this->Record = msql_fetch_array($this->Query_ID);
    $this->Row   += 1;
    $this->Error = msql_error();

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      msql_free_result($this->Query_ID);
      $this->Query_ID = 0;
    }
    return $stat;
  }

  function seek($pos) {
    $status = msql_data_seek($this->Query_ID, $pos);
    if ($status)
      $this->Row = $pos;
    return;
  }

  function metadata($table) {
    $count = 0;
    $id    = 0;
    $res   = array();

    $this->connect();
    $id = @msql_list_fields($this->Database, $table);
    if ($id < 0) {
      $this->Error = msql_error();
      $this->halt("Metadata query failed.");
    }
    $count = msql_num_fields($id);
    
    for ($i=0; $i<$count; $i++) {
      $res[$i]["table"] = msql_fieldtable ($id, $i);
      $res[$i]["name"]  = msql_fieldname  ($id, $i);
      $res[$i]["type"]  = msql_fieldtype  ($id, $i);
      $res[$i]["len"]   = msql_fieldlen   ($id, $i);
      $res[$i]["flags"] = msql_fieldflags ($id, $i);
      $res["meta"][$res[$i]["name"]] = $i;
      $res["num_fields"]= $count;
    }
    
    msql_free_result($id);
    return $res;
  }

  function affected_rows() {
	  return msql_affected_rows($this->Query_ID);
  }

  function num_rows() {
    return msql_num_rows($this->Query_ID);
  }

  function num_fields() {
    return msql_num_fields($this->Query_ID);
  }

  function nf() {
    return $this->num_rows();
  }

  function np() {
    print $this->num_rows();
  }

  function f($Name) {
    return $this->Record[$Name];
  }

  function p($Name) {
    print $this->Record[$Name];
  }
  
  function halt($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>MSQL Error</b>: %s<br>\n", $this->Error);
    die("Session halted.");
  }
}
?>
