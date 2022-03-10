<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: db_null.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 *
 */ 

class DB_Sql {
  
  /* public: constructor */
  function DB_Sql ($query='') {
  }
  
  /* public: some trivial reporting */
  function link_id() {
    return false;
  }
  
  function query_id() {
    return false;
  }
  
  /* public: connection management */
  function connect ($Database='', $Host='', $User='', $Password='') {
    return false;
  }
  
  /* public: perform a query */
  function query ($Query_String) {
    return false;
  }
  
  /* public: walk result set */
  function next_record () {
    return false;
  }
  
  /* public: position in result set */
  function seek ($pos = 0) {
    return false;
  }
  
  /* public: table locking */
  function lock($table, $mode="write") {
    return false;
  }
  
  function unlock() {
    return false;
  }
  
  
  /* public: evaluate the result (size, width) */
  function affected_rows() {
    return false;
  }
  
  function num_rows() {
    return false;
  }
  
  function num_fields() {
    return false;
  }
  
  /* public: shorthand notation */
  function nf () {
    return false;
  }
  
  function np() {
    print '';
  }
  
  function f($Name) {
    return false;
  }
  
  function p($Name) {
    print '';
  }
  
  /* public: sequence numbers */
  function nextid ($seq_name) {
    return false;
  }
  
  /* public: return table metadata */
  function metadata($table,$full=false) {
    return false;
  }
  
  /* private: error handling */
  function halt($msg) {
    return false;
  }
  
  function table_names() {
    return false;
  }
  
}
?>
