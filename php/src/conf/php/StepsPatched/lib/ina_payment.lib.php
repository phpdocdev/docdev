<?

  function makePayment(&$this, $service, $orderid, $amount, $fee, $name1, $name2){
    // this function will make the appropriate payment
		// returns 1 if successful, 0 if not
		
		if($this->Demo == 1 || $this->Debug == 1){
		  $Debug = 1;		
		}else{
		  $Debug = 0;		
		}
		
	  if($this->S['paymethod'] == 'C'){
	    // charge the credit card		
			require_once('ina_translator_daemon.php');  
			
 			$INA = new ina_translator("INA::Payment::CC", $Debug, 'bob', $this->ProjectID, $service);				
			
			$result = $INA->call('SendTrans', array(
				'card-number'   => $this->S['CC_NUM'],
				'card-expmon'   => $this->S['CC_EXP_MO'],
				'card-expyr'    => $this->S['CC_EXP_YR'],
				'card-name'     => $this->S['CC_NAME'],
				'card-address'  => $this->S['CC_ADDR'],
				'card-city'     => $this->S['CC_CITY'],
				'card-st'       => $this->S['CC_STATE'],
				'card-zip'      => $this->S['CC_ZIP'],
				'amount'        => $amount,
				'salecost'      => $amount-$fee,
				'orderid'       => $orderid,
			));			
			
			if($result['MStatus'] == 'success'){
			  return 1;
			}else{
			  $this->Error($result['StatusMessage'], '');			
			  return 0;
			}			
			
		}else if($this->S['paymethod'] == 'E'){
	    // charge the echeck
			require_once('ina_translator_daemon.php');  
			
 			$INA = new ina_translator("INA::Payment::EFT", $Debug, 'bob', $this->ProjectID, $service);				
			
			$result = $INA->call('SendTrans', array(
				CustFirstName        => $name1,
				CustLastName         => $name2,
				CustAcctNum          => 10,
				RoutingNumOrExpDate  => $this->S['CHECK_ROUTING'],
				DDAOrCCDNum          => $this->S['CHECK_ACCOUNT'],
				Type                 => 'CHK',
				TransAmount          => $amount,
				salecost             => $amount-$fee,
				'orderid'            => $orderid,					
			));			
			
			if($result['Error'] == 0){
			  return 1;
			}else{
			  $this->Error($result['StatusMessage'], '');			
			  return 0;
			}
			
			
		}else{
		  $this->Error("You must select a payment method", '');
		}	  

	  // if we get here something bad happened
	  return 0;
	}

  function sendReceipt(&$this, $orderid, $body, $subj, $to, $from, $cc='', $bcc='', $projectid=''){
	  require_once('ina_translator_daemon.php');
		$INA = new ina_translator("INA::Payment", 0, 'bob', $projectid);
		//echo "call: $this, $orderid, $body, $subj, $to, $from, $cc, $bcc, $projectid";

		$INA->call('custom_receipt', $orderid, $body, $subj, $to, $from, $cc, $bcc, $projectid);
	
	}
	
	function orderid(&$this){
	  require_once('ina_translator_daemon.php');
		$INA = new ina_translator("INA::Payment", 0, 'bob', $projectid);
		list($orderid) = $INA->call('orderid', '');	
		return $orderid;
	}
	
	function summarizePaymentInfo(&$this){
	  if($this->S['paymethod'] == 'C'){
	    // show cc info
			
			//0123 4567 8901 2345
			//1111 2222 3333 4444
			
			$num =  substr($this->S['CC_NUM'], 0, 2);
			$num .= '**********';
			$num .= substr($this->S['CC_NUM'], 12, 4);
			
			return $this->fancyTable('Payment Information', '', array(
			  array('Payment method:','Credit card'),
			  array('Name:',          $this->S['CC_NAME']),
			  array('Address:',       $this->S['CC_ADDR'] . ' ' . $this->S['CC_CITY'] . ' ' . $this->S['CC_STATE'] . ' ' . $this->S['CC_ZIP']),				
			  array('Number:',        $num),								
			  array('Expiration:',    $this->S['CC_EXP_MO'] . '/' . $this->S['CC_EXP_YR']),												
			  array('Payment Date:',  date("m/d/Y")),												
			));
			
		}else if($this->S['paymethod'] == 'E'){
	    // show echeck info
			return $this->fancyTable('Payment Information', '', array(
			  array('Payment method','Credit card'),
			  array('Routing number', $this->S['CHECK_ROUTING']),				
			  array('Account number',$this->S['CHECK_ACCOUNT']),						
			  array('Payment Date',  date("m/d/Y")),
			));			
		}else{
		  $this->Error("You must select a payment method", '');
		}	 	
	}

?>	