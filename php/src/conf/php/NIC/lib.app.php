<?
	function clear_payment_info(){
		$_SESSION['CC_NAME']   = NULL;
		$_SESSION['CC_ADDR']   = NULL;
		$_SESSION['CC_CITY']   = NULL; 
		$_SESSION['CC_STATE']  = NULL; 
		$_SESSION['CC_ZIP']    = NULL; 
		$_SESSION['CC_TYPE']   = NULL; 
		$_SESSION['CC_NUM']    = NULL; 
		$_SESSION['CC_EXP_YR'] = NULL; 
		$_SESSION['CC_EXP_MO'] = NULL; 
	
		$_SESSION['CHECK_ROUTING']        = NULL;
		$_SESSION['CHECK_ACCOUNT']        = NULL;
		$_SESSION['CHECK_NAME']           = NULL;
		$_SESSION['CHECK_TYPE']           = NULL;
		$_SESSION['CHECK_AUTH']           = NULL;
		$_SESSION['CHECK_CONSUMERTYPE']   = NULL;
	}
	
	function save_cc_info(){
		$_SESSION['CC_NAME']   = $_POST['CC_NAME'];   
		$_SESSION['CC_ADDR']   = $_POST['CC_ADDR'];   
		$_SESSION['CC_CITY']   = $_POST['CC_CITY'];   
		$_SESSION['CC_STATE']  = $_POST['CC_STATE'];  
		$_SESSION['CC_ZIP']    = $_POST['CC_ZIP'];    
		$_SESSION['CC_TYPE']   = $_POST['CC_TYPE'];   
		$_SESSION['CC_NUM']    = $_POST['CC_NUM'];    
		$_SESSION['CC_EXP_YR'] = $_POST['CC_EXP_YR']; 
		$_SESSION['CC_EXP_MO'] = $_POST['CC_EXP_MO']; 
	}
	
	function save_echeck_info(){
		$_SESSION['CHECK_ROUTING']        = $_POST['CHECK_ROUTING'];     
		$_SESSION['CHECK_ACCOUNT']        = $_POST['CHECK_ACCOUNT'];     
		$_SESSION['CHECK_NAME']           = $_POST['CHECK_NAME'];        
		$_SESSION['CHECK_TYPE']           = $_POST['CHECK_TYPE'];        
		$_SESSION['CHECK_AUTH']           = $_POST['CHECK_AUTH'];        
		$_SESSION['CHECK_CONSUMERTYPE']   = $_POST['CHECK_CONSUMERTYPE'];
	}

	function mask_cc_num($num){
		for($i=2; $i<strlen($num)-4; $i++){
			$num[$i] = '*';		
		}
		return $num;
	}
	
	function mask_check_account($num){
		for($i=0; $i<strlen($num)-4; $i++){
			$num[$i] = '*';		
		}
		return $num;
	}
	
function db_paginate($DB, $sql, $page=0, $limit=10, $count=0){

	$hard_limit = 1000; // wont process more than this
	$hard_count = 0;

	$from = $page * $limit;
	$to = ($page + 1) * $limit;
	
	$results = array();
	
	if(!$count){
		// if count is not supplied, do a count(*)
		//$count_sql = "select count(*) from ($sql)";
		$count_sql = preg_replace('/^select (.*) from/', 'select count(*) from', $sql);
		//echo $count_sql;
		$count = $DB->getOne($count_sql);
	}
	
	if($to > $count){
		$to = $count;
	}
	
	$sql .= " limit $from, $limit";
	//echo $sql;

	$results = $DB->getAll($sql);
	return array($results, $count, $from+1, $to);	
	
	$qry = $DB->query($sql);
	if (DB::isError($qry)) {    
		return array($qry);
	}		
	
	for($i=0; $i<$to; $i++){
		$qry->fetchInto($row);
		if($i>=$from && $row){
			$results[] = $row;
		}else{
		}
	}	

	return array($results, $count, $from+1, $to);
}	
	
?>