<?php
class odbc{

	var $conn_id;
	var $ores;

  function odbc($dsn,$user="",$pass=""){
		$this->conn_id=odbc_connect($dsn,$user,$pass) or die("Error connecting to DSN $dsn");
  }
	
	function getConn() {
		return $this->conn_id;
	}
	
	function getTables(){
		$this->ores= array();
		$temp=odbc_tables($this->conn_id);
		while (odbc_fetch_row($temp)) {
			array_push($this->ores, odbc_result($temp,3));
		}
		return $this->ores;
	}
	
	function getColumns($table) {
		$this->ores=array();
		$result=odbc_exec($this->conn_id,"select * from $table");
		$num=odbc_num_fields($result);
		for ($i=1; $i<=$num; $i++) {
			$this->ores[]=odbc_field_name($result, $i);
		}
		return $this->ores;
	}
	
	function fetchrow_array($sql){
		$this->ores=array();
		$temp=odbc_exec($this->conn_id,$sql);
		for ($i=1; $i<=(odbc_num_fields($temp)); $i++) {
			$this->ores[]=odbc_result($temp, $i);
		}
		return $this->ores;
	}
	
	function fetchall_array($sql,$label=0){
		$this->ores=array();
		$result=odbc_exec($this->conn_id,$sql);
		$num=odbc_num_fields($result);
		if ($label) {
			for ($i=1; $i<=$num; $i++) {
				$temp[]=odbc_field_name($result, $i);
			}
			$this->ores[]=$temp;
		}
		while (odbc_fetch_row($result)) {
			$temp=array();
			for ($i=1; $i<=$num; $i++) {
				$temp[]=odbc_result($result, $i);
			}
			$this->ores[]=$temp;
		}
		return $this->ores;
	}
	
	function Run($sql){
		return (odbc_exec($this->conn_id,$sql));
	}
	
	function Save(){
		return (odbc_commit($this->conn_id) && odbc_close($this->conn_id));
	}
	
	function Quit(){
		return (odbc_rollback($this->conn_id) && odbc_close($this->conn_id));
	}
	
	function Close(){
		odbc_close($this->conn_id);
	}
	
	function AndWhere(&$sql,$where){
		if (eregi('where',$sql)) {
			preg_replace("/ +$/", "", $sql);
			$sql.=" and $where";
		} else {
			preg_replace("/ +$/", "", $sql);
			$sql.=" where $where";
		}
	}
}
?>
