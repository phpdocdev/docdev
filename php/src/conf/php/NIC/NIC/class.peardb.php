<?
class peardb extends DB{

	function peardb($dsn, $persist=true, $error_handler='', $log_file=''){
	
		if($error_handler){
			$this->set_error_handler($error_handler);
		}
		
		if($log_file){
			$this->log_file = $log_file;
		}
	
		$this->db = DB::connect($dsn, $persist);
		$this->discard_errors = false;
		
		if (DB::isError($this->db)) {
			$msg = $this->db->getMessage();			
			ob_start();
			ob_implicit_flush(0);
			var_dump($this->db);						
			$Content = ob_get_contents();
			ob_end_clean(); 
			$msg .= ' ' . $Content;
			$this->handle_error($msg);
		}	
		
		$this->session_caching = true;
		$this->auto_strip_slashes = true;
		
		$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
	}
	
	function set_error_handler($function=''){
		if($function){
			$this->error_handler = $function;		
		}else{
			$this->error_handler = NULL;
		}
	}
	
	function logsql($sql){
		$this->lastsql = $sql;
		
		if( $this->log_file == 'stdout' ){
			echo "$sql<br>";	
		}else if( $this->log_file ){
			$fp = @fopen($this->log_file, "a");
			if( $fp ){
				fputs($fp, date("Y-m-d h:i:s") . ' - ' . $sql . "\n"  );
				fclose($fp);
			}			
		}
			
	}
	
	function log_message($msg){
		//$this->db->query("insert into sqlerrors(user, msg, etime, page)values('".$_SESSION['login']."', '".addslashes($msg)."', NOW(), '".addslashes(str_replace('https://devweb.ark.org/nic/oc/index.php?', '', $_SERVER['HTTP_REFERER']))."')");
	}

  function nextId($str){
    return $this->db->nextId($str);
  } 	
	
	function handle_error($msg){
	
		if($this->discard_errors){
			return 0;
		}
	
		if( $this->error_handler == 'stdout' ){
			echo $msg;
			exit;
			return 0;
		}else if( $this->error_handler ){
			call_user_func($this->error_handler, $msg);
			return 0;
		}
	
		$this->log_message($msg);		
		
		?>
		<font face=arial size=2>
		<p align=center>		
		An error has occured with the database. The adminstrator has been notified.
		<?
		exit;
	}
	
	function wrap_query(&$ret){
		if (DB::isError($ret)) {
			$this->handle_error($ret->getMessage() . ' on ' . $this->lastsql);
		}				
		return $ret;
	}
	
	function getOne($sql){
		$this->logsql($sql);
		$ret = $this->db->getOne($sql);
		$ret = $this->wrap_query(&$ret);
		if($this->auto_strip_slashes){
			$ret = stripslashes($ret);
		}
		return $ret;
	}
	
	function getRow($sql, $method=NULL){
		$this->logsql($sql);	
		if($method){
			$ret = $this->db->getRow($sql, $method);
		}else{
			$ret = $this->db->getRow($sql);		
		}
		$ret = $this->wrap_query(&$ret);		

		if($this->auto_strip_slashes){
			if(is_array($ret)){				
				foreach($ret as $k=>$v){
					$ret[$k] = stripslashes($v);
				}		
			}
		}
		
		return $ret;
	}
	
	function getRowArr($sql){
		$this->logsql($sql);	
		$ret = $this->db->getRow($sql, DB_FETCHMODE_ORDERED);
		$ret = $this->wrap_query(&$ret);		
		
		if($this->auto_strip_slashes){
			if(is_array($ret)){				
				foreach($ret as $k=>$v){
					$ret[$k] = stripslashes($v);
				}		
			}
		}
		
		return $ret;		
		
	}	
	
	function getAll($sql){
		$this->logsql($sql);	
		$ret = $this->db->getAll($sql);
		$ret = $this->wrap_query(&$ret);		
		
		if($this->auto_strip_slashes){		
			for($i=0; $i<count($ret); $i++){
				foreach($ret[$i] as $k=>$v){
					$ret[$i][$k] = stripslashes($v);
				}
			}
		}
		return $ret;
	}
	
	function query($sql){
		$this->logsql($sql);	
		$ret = $this->db->query($sql);
		$ret = $this->wrap_query(&$ret);				
		//echo $sql."<br><br>";
		return $ret;
	}
	
	function get_last_id(){
		return $this->db->getOne("select LAST_INSERT_ID()");
	}

}
?>
