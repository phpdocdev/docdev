<?
// payment functions -----------------------------
function get_orderid(){
	$INA = new ina_translator("INA::Payment", 0, $this->Dev, $projectid);
	list($orderid) = $INA->call('orderid', '');	
	return $orderid;
}

function get_echeck_effective_date(){
	$target = strtotime("+1 day");
	
	$wday = date("w", $target);
	
	if($wday == 0){
		$target = strtotime("next monday");
	}else if($wday == 6){
		$target = strtotime("next monday");		
	}	
	
	return $target;
}

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

function make_echeck_payment(){
	$INA = new ina_translator("INA::Payment::EFT", $this->Demo, $this->Dev, $this->ProjectID, $this->ServiceCode, $this->orderid);				
	
	$names = split(' ', $DATA['CHECK_NAME']);
	$fname = array_shift($names);
	$lname = join(' ', $names);
	
	if( preg_match("/ch/i", $DATA['CHECK_TYPE']) ){
		$type = 'CHK';
	}else{
		$type = 'SAV';
	}
	
	$result = $INA->call('SendTrans', array(
		CustFirstName        => $fname,
		CustLastName         => $lname,
		CustAcctNum          => $DATA['CHECK_CONSUMERTYPE'],
		RoutingNumOrExpDate  => $DATA['CHECK_ROUTING'],
		DDAOrCCDNum          => $DATA['CHECK_ACCOUNT'],
		Type                 => $type,
		TransAmount          => $DATA['TRANS_SUBTOTAL'] + $DATA['TRANS_FEEAMOUNT'],
		salecost             => $DATA['TRANS_SUBTOTAL'],
	));				

	if($result['Error'] == 0){
		return 'success';			
	}else{
		return $result['StatusMessage'];
	}

}

function make_cc_payment(){
	$INA = new ina_translator("INA::Payment::CC", $this->Demo, $this->Dev, $this->ProjectID, $this->ServiceCode, $this->orderid );				

	$amt = round($DATA['TRANS_SUBTOTAL'] + $DATA['TRANS_FEEAMOUNT'], 2);

	$result = $INA->call('SendTrans', array(
		'card-number'   => $DATA['CC_NUM'],
		'card-expmon'   => $DATA['CC_EXP_MO'],
		'card-expyr'    => $DATA['CC_EXP_YR'],
		'card-name'     => $DATA['CC_NAME'],
		'card-address'  => $DATA['CC_ADDR'],
		'card-city'     => $DATA['CC_CITY'],
		'card-st'       => $DATA['CC_STATE'],
		'card-zip'      => $DATA['CC_ZIP'],
		'amount'        => $amt,
		'salecost'      => $DATA['TRANS_SUBTOTAL'],
	));			
		
	if($result['MStatus'] == 'success'){
		return 'success';			
	}else{
		return $result['StatusMessage'];
	}	

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

?>