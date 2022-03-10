<?

define('CDB_PASSWORD', 'Password');
define('CDB_USERNAME', 'arWsUser');
define('CDB_URL', 'https://cmbs-admin.soltn.dev.cdc.nicusa.com');		
define('WS_URL_CUST',  CDB_URL.'/ar/gateway/CDBCustomerService');
require_once('NIC/CDB/CDB_Gateway.php');

function cdbCreateAccount($app_name, $local_id, $pmt_info){
	if(isset($pmt_info['cc_number'])){
		$createCustParams = array(
			'customer' => array(
				'orgName'               => sprintf("Storage for %s: %s", $app_name, $local_id),
				/*
					Status "G" is Pending, "A" is active.
					Production card storage library doesn't set this, but it errors on dev
					without setting status code.
				*/
				'statusCode' => 'G',
				'customerBillingOption' => array(
					'billType'   => 'C',
					'creditCard' => array(
						'ccExp'          => sprintf("%02d%04d", $pmt_info['cc_exp_month'], $pmt_info['cc_exp_year']),
						'ccName'         => $pmt_info['cc_name'],
						'ccNo'           => $pmt_info['cc_number'],
						'creditCardType' => array('creditCardTypeId' => $pmt_info['cc_type'])
					)
				)
			)
		);
	}else{
		$createCustParams = array(
			'customer' => array(
				'orgName'               => sprintf("Storage for %s: %s", $app_name, $local_id),
				
				/*
					Status "G" is Pending, "A" is active.
					Production card storage library doesn't set this, but it errors on dev
					without setting status code.
				*/
				'statusCode' => 'G',
				'customerBillingOption' => array(
					'custId'   => $cust_id,
					'billType' => 'A',
					'customerBankAccount' => array(
						'acctNo'       => $pmt_info['bank_account'],
						'bankName'     => $pmt_info['bank_name'],
						'routeNo'      => $pmt_info['bank_routing'],
						'bankAcctType'	=> array('bankAcctTypeId' => $pmt_info['bank_type']),
					)
				)
			)
		);
	}
	
	$CDB = new CDB_Gateway(CDB_USERNAME,CDB_PASSWORD);
	$ret = $CDB->createCustomer($createCustParams);

// 	$params_string = print_r($createCustParams, true);
// 	file_put_contents('/tmp/cdb_test.log', $params_string . "\n", FILE_APPEND);
// 	$return_string = print_r($ret, true);
// 	file_put_contents('/tmp/cdb_test.log', $return_string . "\n", FILE_APPEND);
// 	
	if (PEAR::isError($ret)) {
		return 0;
	}elseif(!$ret->customerId || $ret->status < 0){
		return 0;
	}	
	
	return $ret->customerId;
}

function cdbUpdatePaymentInfo($local_id, $cust_id, $pmt_info){

	if(isset($pmt_info['cc_number'])){
		$modifyCreditCardCustParams = array(
			  'customerBillingOption' => array(
				  'custId' => $cust_id,
				  'billType' => 'C',
				  'creditCard' => array(
					'ccExp' => sprintf("%02d%04d", $pmt_info['cc_exp_month'], $pmt_info['cc_exp_year']),
					'ccName' => $pmt_info['cc_name'],
					  'ccNo' => $pmt_info['cc_number'],
					  'creditCardType' => array(
						  'creditCardTypeId' => $pmt_info['cc_type']
					)
				)
			)
		);	
	}else{	
		$modifyCreditCardCustParams = array(
			'customerBillingOption' => array(
				'custId'   => $cust_id,
				'billType' => 'A',
				'customerBankAccount' => array(
					'acctNo'   => $pmt_info['bank_account'],
					'bankName' => $pmt_info['bank_name'],
					'routeNo'  => $pmt_info['bank_routing'],
					'bankAcctType'	=> array('bankAcctTypeId' => $pmt_info['bank_type']),
				)
			)
		);
	}
	
	$client = new CDB_Gateway(CDB_USERNAME,CDB_PASSWORD);
	
	$ret = $client->modifyCustomerBillingOption($modifyCreditCardCustParams);
	if (PEAR::isError($ret)) {
		 die('Error: '. $ret->getMessage() ."\n");
	}
	return ($ret->status == 0) ? true : false;
}

function cdbFetchPaymentInfo($local_id, $cust_id){
	$CDB = new CDB_Gateway(CDB_USERNAME,CDB_PASSWORD);

	$cust = $CDB->retrieveCustomerBillingInfo(array(
		 'retrieveCustomerBillingInfoRequest' => array(
			'customerId' => $cust_id,
		 )
	));	
	if (PEAR::isError($cust)) {
		return array();
	}

	switch($cust->customerBillingOptions->billType){
		case 'C':
			preg_match('/(\d\d)(\d\d\d\d)/', $cust->customerBillingOptions->creditCard->ccExp, $exp);
			return array(
				'cc_number'    => $cust->customerBillingOptions->creditCard->ccNo,
				'cc_exp_month' => $exp[1],
				'cc_exp_year'  => $exp[2],		
				'cc_type'      => $cust->customerBillingOptions->creditCard->creditCardType->creditCardTypeId,
				'cc_type_str'  => $cust->customerBillingOptions->creditCard->creditCardType->name,
				'cc_name'      => $cust->customerBillingOptions->creditCard->ccName,
			);

		case 'A':
			return array(
				'bank_name'     => $cust->customerBillingOptions->customerBankAccount->bankName,		
				'bank_account'  => $cust->customerBillingOptions->customerBankAccount->acctNo,
				'bank_routing'  => $cust->customerBillingOptions->customerBankAccount->routeNo,
				'bank_type'     => $cust->customerBillingOptions->customerBankAccount->bankAcctType->bankAcctTypeId,
				'bank_type_str' => $cust->customerBillingOptions->customerBankAccount->bankAcctType->name,
			);
			
		default:
			return array();
	}

}


?>