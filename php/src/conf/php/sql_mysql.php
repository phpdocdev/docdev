<?php

if( ! class_exists('sqltools') ){
  require('sqltools.php');
}


class SQL_mysql extends sqltools{

  var $conn_id;
  var $q_res;
  var $database;

  function SQL_mysql($database, $un='root', $pw='', $host='localhost'){

	  if($database == 'customer'){
      //$host = 'proddb';
			//$un = 'customer';
		}	
if ($_SERVER["SERVER_NAME"] == 'app.ark.org'){
	$this->errorLog = '/var/log/httpd/sqlerrlog';
}else if ($_SERVER['SERVER_NAME'] == 'sos-app.ark.org'){
	$this->errorLog = '/var/log/httpd/sqlerrlog';	
}else{
  	//$this->errorLog = '/web/apache/logs/sqlerrlog';
	$this->errorLog = '/web/app-data/tmp/sqlerrlog';
}
	$this->database = $database;   
    $this->conn_id = mysql_connect($host, $un, $pw) or $this->queryError('', "Can't connect to $host");
     $this->LogFile = array();
  }
  

  function Run($sql){
    $this->logSQL('Run', $sql);
    //$this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or die("Invalid query: $sql - " . mysql_error($this->conn_id) );  
		$this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or $this->queryError($sql, mysql_error($this->conn_id));
    $this->doLogToBrowser($sql);
    return $this->q_res;
  }

  function fetchrow($sql, $enum=0){
    $this->doLogToBrowser($sql);  
  //$this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or die("Invalid query: $sql - " . mysql_error($this->conn_id));    	
    $this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or $this->queryError($sql, mysql_error($this->conn_id));
		if ($enum) {
			$ret = mysql_fetch_row($this->q_res);
		} else {
			$ret = mysql_fetch_array($this->q_res);     
			if( ($ret) && ($this->Auto_Strip_Slashes == 1) )
			{
				foreach(array_keys($ret) as $r){
					$myret[$r] = stripslashes($ret[$r]);
				}
				return $myret; 
			}
  	}
    return $ret;
	}
	
	function queryError($sql, $msg){

	  $this->recordError($sql, $msg);
	print "\n\n $sql $msg  \n\n";
	  die("An error has occured with the database. An administrator has been notified.");
	  
	}
	

	function fetch($qry, $enum=0){
		$this->doLogToBrowser($sql);
		if ($enum) 
		{
			return mysql_fetch_row($qry);
		} 
		else 
		{
			if($this->Auto_Strip_Slashes == 1)
			{
				$ret = mysql_fetch_array($qry);
				if($ret)
				{
					foreach($ret as $k=>$v)
					{
						$ret[$k] = stripslashes($ret[$k]);
					}
				}
				return $ret;
			}
			else
			{
				return mysql_fetch_array($qry);     
			}
		}
	}

	function recordError($sql='', $msg=''){
		if($this->errorLog){
			
			global $REMOTE_USER;
		     $fd = @fopen($this->errorLog, "a");
      		if( $fd ){
		     	fputs( $fd, sprintf("%s | %s | %s | %s\n", date("m/d/Y H:i"), $this->database, $REMOTE_USER, "$msg - $sql") );
        			fclose($fd);
      		}		  
		}
	}

}

?>
