<?php
class TPERefundService{

	protected $db;
	protected $refund_service_wsdl;
	protected $tpe_service_endpoint;
	protected $merchantId;
	protected $merchantKey;

	public $messages;
		
	public function __construct($dsn,$o_dsn,$wsdl,$endpoint){
		ini_set('soap.wsdl_cache_enabled', 1) ;
		// @TODO - READ THESE VALUES FROM CONFIG FILE, or at least receive in the constructor.
		$this->db = $this->_connectDb($dsn);	
		$this->tpe = $this->_connectOracle($o_dsn);
		$this->tpe_service_wsdl = $wsdl;
		// Note: The endpoint from the WSDL uses a non-SSL URL, which is unavailable for our environment.  Had to override here to use SSL.
		$this->tpe_service_endpoint = $endpoint;
		
		
		$messages = array();
	}

	/**
	 * If an order has already been disbursed (or is disbursing today), we'll change the disbursement date to prevent it from going to Agency.
	 * If order has not already been disbursed, we can use the Refund() web service method provided by TPE.
	 *
	 * 
	 * @param INA's Order Id
	 * @return array(bool success, string message);
	 */
	public function refund($orderId = false, $updateDb = false){
		
		if (!$orderId){
			throw new Exception("OrderId not specified in " . __METHOD__);
		}
		$order_detail = $this->_getOrderDetail($orderId);
		if (!$order_detail){
			return array(false, 'Order Not Found');
		}

		
		$this->merchantId = $order_detail->tpe_merchant_id;
		$this->merchantKey = $order_detail->tpe_merchant_key;
		$this->tpe_service = $order_detail->tpe_service;
		$disbursement = $this->_getDisbursementInfo($orderId);
		
		if (!$disbursement || !$disbursement[0]['DISBURSEMENT_ITEM_ID']){
			return array(false, 'Disbursement Record Not Found');
		}
		
		$disbursement_date_ts = strtotime($disbursement[0]['DISBURSEMENT_EFFECTIVE_DATE']);
		$disbursement_date_created = strtotime($disbursement[0]['DATE_CREATED']);
		$now = time();
		//check if refunded
		$refunded = $this->_checkForRefund($disbursement[0]['ORDER_ID'],$disbursement_date_ts,$disbursement_date_created);
		if(!$refunded)
		{
			$salecost = $order_detail->salecost;
			$fee = $order_detail->amount - $order_detail->salecost;
			$this->_log("Issuing Refund for $orderId");
			$result = $this->_tpeRefund($disbursement[0]['ORDER_ID'], $disbursement[0]['INVOICE_ID'], $salecost, $fee);
			
			// Disbursement Date is a future date.  Change the disbursement date to 10 years in the future to prevent agency from receiving funds.
			if ($disbursement_date_ts > $now)
			{	
				$this->_log("Checking for net: $orderId");
				$net = $this->_serviceNets($orderId);
				if($net)
				{
					$this->_log("Service nets for $orderId. Setting complete.");
					$status = "Complete";
				}
				else
				{
					$future_date = date('c', strtotime('+10 years', mktime(0,0,0) ) );
					$this->_log("Setting disbursement date for $orderId to $future_date. Setting pending.");
					$res = $this->_updateDisbursementDate($disbursement, $future_date);
					$result[0] = $res[0] && $result[0];
					$result[1] .= " " . $res[0];
					$status = "Pending";
				}
			}
			else
			{
				$status = "Complete";
				
			}
			$sql = sprintf("INSERT INTO trans_data (
						`orderid` ,
						`key` ,
						`value`
						)
						VALUES (
						%s, 'Cancellation Status', '%s'
						);", $this->db->quote($orderId) , $status);
			$this->db->query($sql);
			if($updateDb)
			{
				$sql = sprintf("UPDATE trans_history set status = 'G' where orderid = %s", $this->db->quote($orderId));
				$this->db->query($sql);
			}
				
		}
		else
		{
			$result = array(false,'Order has already been refunded');
		}
		return $result;
	}	
	
	protected function _serviceNets($orderId){
		$sql = sprintf("
			SELECT collection_method
			FROM service_codes
			LEFT JOIN trans_history ON trans_history.service = service_codes.servcode
			WHERE trans_history.orderid =  %s", $this->db->quote($orderId));
		$result = $this->db->queryRow($sql);
		return $result->collection_method == 'Reduce Disbursement';
	}

	public function checkForDuplicate($orderId){
		$sql = sprintf("SELECT count(*) FROM orders where local_ref = '%s'",$orderId);
		$res = $this->tpe->query($sql);
		while($row = $res->fetchRow())
		{
			$items[] = $row;
		}
		if($items)
		{
			$val = $items[0];
			$status = $val>1?true:false;
		}			
		return array($status,$status?'Duplicate Found':'No Duplicate Found');
		
	}

	/**
	 * If an order has not been disbursed (or is disbursing today), we'll change the disbursement date to prevent it from going to Agency.
	 * If order has already been disbursed or refunded, we prevent the disbursement date change.
	 *
	 * 
	 * @param INA's Order Id
	 * @return array(bool success, string message);
	 */
	public function change_disbursement_date($orderId = false, $check_refund = true){
		
		
		if (!$orderId){
			throw new Exception("OrderId not specified in " . __METHOD__);
		}
		$order_detail = $this->_getOrderDetail($orderId);
		if (!$order_detail){
			return array(false, 'Order Not Found');
		}
		
		if($this->_serviceNets($orderId))
		{
			return array(false, 'Service nets--cannot change date.');
		}

		
		$this->merchantId = $order_detail->tpe_merchant_id;
		$this->merchantKey = $order_detail->tpe_merchant_key;
		$this->tpe_service = $order_detail->tpe_service;
		$disbursement = $this->_getDisbursementInfo($orderId);
		
		if (!$disbursement || !$disbursement[0]['DISBURSEMENT_ITEM_ID']){
			return array(false, 'Disbursement Record Not Found');
		}
		
		$disbursement_date_ts = strtotime($disbursement[0]['DISBURSEMENT_EFFECTIVE_DATE']);
		$disbursement_date_created = strtotime($disbursement[0]['DATE_CREATED']);
		$now = time();
		//check if refunded
		if($check_refund){
		$refunded = $this->_checkForRefund($disbursement[0]['ORDER_ID'],$disbursement_date_ts,$disbursement_date_created);
		}
		
		if(!$refunded)
		{
			// Disbursement Date is a future date.  Change the disbursement date to 10 years in the future to prevent agency from receiving funds.
			if ($disbursement_date_ts > $now)
			{	
				$future_date = date('c', strtotime('+10 years', mktime(0,0,0) ) );
				$this->_log("Setting disbursement date for $orderId to $future_date");
				$res = $this->_updateDisbursementDate($disbursement, $future_date);
				
				$result = array($res[0], $res[1]);
				
				$sql = sprintf("REPLACE INTO trans_data (
						`orderid` ,
						`key` ,
						`value`
						)
						VALUES (
						%s, 'New Disbursement Date', '%s'
						);", $this->db->quote($orderId) , $future_date);
				$this->db->query($sql);
			}else{
				$result = array(false,'Could not change disbursement date. Current Disbursement Date '. date('Y-m-d',strtotime($disbursement[0]['DISBURSEMENT_EFFECTIVE_DATE'])));
			}
		
		}else{
			$result = array(false,'Could not change disbursement date. See order details in TPE Admin');
		}
		
		return $result;
	}
	
	protected function _checkForRefund($tpeOrderId,$disb_date,$create_date){
		$checkQuery = new stdclass();
		$checkQuery->merchantId = $this->merchantId;
		$checkQuery->merchantKey = $this->merchantKey;
		$checkQuery->orderId = $tpeOrderId;
		try{
			$client = new SoapClient($this->tpe_service_wsdl, array('trace'=>1));
			$client->__setLocation($this->tpe_service_endpoint);
			$result = $client->getOrder($checkQuery);
			$refunded = false;
			foreach($result->return->ftransArray as $trans)
			{	if($trans->ftransTypeDescription == "REFUND")
				 {
					 $refunded = true;
				 }
								
			}
			if(strtotime('+10 years', $create_date ) == $disb_date)
			{
				$refunded = true;
			}
	
			
			return $refunded;

		}catch (SoapFault $fault){
			return $this->_handleSoapFault($fault, $client);
		}
		
	}

	/**
	 * This method not currently used, though we may end up needing it.  
	 */
	protected function _getInvoice($tpeOrderId){
		$invoiceQuery = new stdclass();
		$invoiceQuery->merchantId = $this->merchantId;
		$invoiceQuery->merchantKey = $this->merchantKey;
		$invoiceQuery->orderId = $tpeOrderId;
		
		try{
			$client = new SoapClient($this->tpe_service_wsdl, array('trace'=>1));
			$client->__setLocation($this->tpe_service_endpoint);
			$result = $client->getInvoices($invoiceQuery);
			
			return $result->return->invoices;

		}catch (SoapFault $fault){
			return $this->_handleSoapFault($fault, $client);
		}
	}
	
	/**
	 * Query Oracle for disbursement information.
	 */
	protected function _getDisbursementInfo($inaOrderId){
		
		$sql = sprintf("select 	o.order_id,
								i.invoice_id,
								di.disbursement_item_id,
								f.timestamp as capture_date,
								i.effective_date as invoice_effective_date,
								di.effective_date as disbursement_effective_date,
								dh.history_date as date_created
						from
							tpe2ar.orders o, tpe2ar.service s, tpe2ar.merchant m, tpe2ar.invoice i, tpe2ar.ftrans f, tpe2ar.order_ledger ol, tpe2ar.disbursement_item di, tpe2ar.disbursement_hist dh
						where
							o.service_id = s.service_id and
							s.merchant_id = m.merchant_id and
							s.short_desc = '%s' and
							m.short_desc = '%s' and
							o.order_id = i.order_id and
							o.order_id = ol.order_id and
							o.order_id = f.order_id and
							di.disbursement_item_id = dh.disbursement_item_id and
							ol.order_ledger_id = di.order_ledger_id and
							f.ftrans_type_id = 2 and
							f.failure = 'N' and
							(o.local_ref = '%s' or o.order_id='%s')", $this->tpe_service, $this->merchantId, $inaOrderId, $inaOrderId);
		$res = $this->tpe->query($sql);
		while($row = $res->fetchRow())
		{
			$items[] = $row;
		}			
		return $items;
	}
	
	/**
	 * Update disbursement date in TPE.
	 *
	 * This is a hack to keep un-disbursed from disbursing.
	 */
	protected function _updateDisbursementDate($itemId, $disbursementDate){
		/**
		 * Service Documentation: 
		 * https://devwiki.cdc.nicusa.com/Development_Operations/TPE/TPE_Web_Services/updateDisbursementEffectiveDates
		 */
		foreach($itemId as $item)
		{	
			$ids[] = $item['DISBURSEMENT_ITEM_ID'];
			$dates[] = $disbursementDate;
		}
		try{
			$updateObj = new stdclass();
			$updateObj->merchantId = $this->merchantId;
			$updateObj->merchantKey = $this->merchantKey;
			$updateObj->disbursementItemIds = $ids;
			$updateObj->effectiveDate = $dates;
			
			$client = new SoapClient($this->tpe_service_wsdl, array('trace'=>1));
			$client->__setLocation($this->tpe_service_endpoint);
			$result = $client->updateDisbursementEffectiveDates($updateObj);

			//$this->_printSoapDebug($client);

			if (!$result || $result->return->failed == '1'){
				return array(false, $result->return->errorMessage);
			}

			return array(true, 'Success');
			
		}catch (SoapFault $fault){
			return $this->_handleSoapFault($fault, $client);
		}
	}
	
	/**
	 * Refund the order in TPE.
	 */
	protected function _tpeRefund($tpeOrderId, $tpeInvoiceId, $salecost, $fee){

		try{
			$updateObj = new stdclass();
			$updateObj->merchantId = $this->merchantId;
			$updateObj->merchantKey = $this->merchantKey;
			$updateObj->orderId = $tpeOrderId;
			$updateObj->invoiceId = $tpeInvoiceId;
			$updateObj->cosvalue = $salecost;
			$updateObj->feevalue = $fee;
			$updateObj->comments = 'Refunded via web service API';
			
			$client = new SoapClient($this->tpe_service_wsdl, array('trace'=>1));
			$client->__setLocation($this->tpe_service_endpoint);
			$result = $client->refund($updateObj);

			if (!$result || $result->return->response->failure === true){
				return array(false, $result->return->response->failureMessage);
			}else{
				return array(true, 'Success');	
			}

			
			
		}catch (SoapFault $fault){
			return $this->_handleSoapFault($fault, $client);
		}
			
	}

	/**
	 * Get order detail from trans_history.
	 */
	protected function _getOrderDetail($orderId){
		$sql = sprintf("SELECT trans_history.*, service_codes.*
					FROM trans_history
					JOIN service_codes ON (trans_history.service = service_codes.servcode)
					WHERE trans_history.orderid = %s", $this->db->quote($orderId));
		$result = $this->db->queryRow($sql);
		$result->tpe_orderid = $this->_getTpeOrderId($orderId);
		
		return $result;
	}
	
	/**
	 * Get the TPE order id for an order from trans_data.
	 */
	protected function _getTpeOrderId($orderId){
		$sql = sprintf("SELECT trans_data.value as 'tpe_orderid'
					FROM trans_data
					WHERE trans_data.orderid = %s
					AND trans_data.key = 'TPE ID'", $this->db->quote($orderId));
		return $this->db->queryOne($sql);
	}

	protected function _connectOracle($dsn){
		
		require_once 'DB.php';
		$db = DB::connect($dsn);
		$db->setFetchMode(DB_FETCHMODE_ASSOC);
		return $db;
	}
		
	protected function _connectDb($dsn){
		require_once 'MDB2.php';
		$db =& MDB2::factory($dsn);
		$db->setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'handle_db_error'));
		$db->setFetchMode(MDB2_FETCHMODE_OBJECT);
		$db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);
		return $db;
	}
	
	function handle_db_error($error_obj){
		$message = mysql_errno() . "-" . mysql_error() . "-" . $error_obj->getMessage() . " on " . $error_obj->getDebugInfo();
		print $message;
	}
	
	protected function _handleSoapFault(&$fault, &$client){
		//print "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
		//$this->_printSoapDebug($client);
		return array(false,'Error connecting to TPE web service');
	}
	
	protected function _printSoapDebug(&$client){
		echo '<pre>';
		echo "REQUEST:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
		echo '<hr>';
		echo "RESPONSE:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
		echo '</pre>';	
	}
	
	protected function _log($message){
		$this->messages[] = $message;
	}
}
