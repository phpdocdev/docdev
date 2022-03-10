<%

class Transactions {

	var $ServiceCode;
	var $ProjectId;
	var $OrderId;
	var $User='support';
	var $Demo=0;
	var $Errors=array();
	var $ErrCodes=array();


	function Transactions($settings) {
		$this->ServiceCode=$settings['ServiceCode'];
		$this->ProjectId=$settings['ProjectId'];
		$this->OrderId=$settings['OrderId'];
		$this->User=$settings['User']?$settings['User']:$this->User;
		$this->Demo=$settings['Demo']?$settings['Demo']:$this->Demo;
	}


	function getOrderId() {
		$this->OrderId=date(YmdHi).sprintf('%05s', getmypid());
		return $this->OrderId;
	}


	function saveCCVars(&$pmtvars) {
		$ccvars=array(
			'CC_CARD_NUM',
			'CC_CARD_TYPE',
			'CC_EXP_MON',
			'CC_EXP_YR',
			'CC_NAME',
			'CC_ADDR',
			'CC_CITY',
			'CC_STATE',
			'CC_ZIP',
		);
		foreach ($ccvars as $var) {
			$_SESSION[$var]=$pmtvars[$var];
		}
		return 1;
	}


	function validateCC($pmtvars='') {
		require_once('/web/php/validfield.php');
		$valid=new validfield();
		$vars=array(
			'CC_CARD_NUM',
			'CC_EXP_MON',
			'CC_EXP_YR',
			'CC_NAME',
			'CC_ADDR',
			'CC_CITY',
			'CC_STATE',
			'CC_ZIP',
			'CC_AMOUNT',
		);
		foreach ($vars as $var) {
			$$var=$pmtvars[$var]?$pmtvars[$var]:$_SESSION[$var];
		}
		if (!$CC_CARD_NUM) {
			$this->Errors[]='Please enter your credit card number.';
		} elseif (preg_match('/\D/', $CC_CARD_NUM)) {
			$this->Errors[]='Please do not include any letters, dashes, or spaces in your credit card number';
		} else {
			$err=$valid->validCC($CC_CARD_NUM);
			if ($err) {
				$this->Errors[]=$err;
			}
		}
		if (!$CC_NAME) {
			$this->Errors[]='Please enter your name as it appears on the credit card.';
		}
		if (!$CC_ADDR) {
			$this->Errors[]='Please enter your billing address.';
		}
		if (!$CC_CITY) {
			$this->Errors[]='Please enter your city.';
		}
		if (!$CC_STATE) {
			$this->Errors[]='Please enter your state.';
		}
		if (! preg_match('/^\d{5}(-\d{4})?$/', $CC_ZIP)) {
			$this->Errors[]='Please enter a valid zip code.';
		}
		if (! preg_match('/^\d*(\.\d{0,2})?$/', $CC_AMOUNT)) {
			$this->Errors[]='Please enter a valid amount to be charged to your credit card.';
		}
		if ($CC_EXP_YR<date('Y') || ($CC_EXP_YR==date('Y') && $CC_EXP_MON<date('m')) ) {
			$this->Errors[]='You may not use an expired credit card.';
		}

		if (count($this->Errors)) {
			return 0;
		} else {
			return 1;
		}
	}


	function sendCCTrans($pmtvars='') {
		# $pmtvars will override vars in the session
		if (!$this->OrderId) {
			$this->getOrderid();
		}
		foreach (array('ServiceCode', 'ProjectId') as $reqd) {
			if (!$this->${reqd}) {
				$this->Errors[]='The system is not able to process your transaction at this time. We apologize for the inconvenience. Please try again later.';
				$this->ErrCodes="$reqd is missing";
			}
		}
		$vars=array(
			'card-number' => 'CC_CARD_NUM',
			'card-expmon' => 'CC_EXP_MON',
			'card-expyr' => 'CC_EXP_YR',
			'card-name' => 'CC_NAME',
			'card-address' => 'CC_ADDR',
			'card-city' => 'CC_CITY',
			'card-st' => 'CC_STATE',
			'card-zip' => 'CC_ZIP',
			'amount' => 'CC_AMOUNT',
			'salecost' => 'CC_SALECOST',
			'Appid' => 'CC_APPID',
		);
		foreach ($vars as $pmt => $sess) {
			$pmtdetails[$pmt]=$pmtvars[$sess]?$pmtvars[$sess]:$_SESSION[$sess];
		}
		require_once('/web/php/ina_translator_daemon.php');
		$pmt=new ina_translator('INA::Payment::CC', $this->Demo, $this->User, $this->ProjectId, $this->ServiceCode, $this->OrderId);
		$res=$pmt->call('SendTrans', $pmtdetails);
		if ($res['MStatus'] != 'success') {
			$this->Errors[]=$res['StatusMessage'];
			$this->ErrCodes[]=$res['MErrMsg'];
			return 0;
		} else {
			return 1;
		}
	}


	function saveEFTVars(&$pmtvars) {
		$eftvars=array(
			'EFT_FIRST',
			'EFT_LAST',
			'EFT_ROUTE',
			'EFT_ACCT',
			'EFT_BANK',
			'EFT_TYPE',
		);
		foreach ($eftvars as $var) {
			$_SESSION[$var]=$pmtvars[$var];
		}
		return 1;
	}


	function validateEFT($pmtvars='') {
		$vars=array(
			'EFT_FIRST',
			'EFT_LAST',
			'EFT_ROUTE',
			'EFT_ACCT',
			'EFT_BANK',
			'EFT_TYPE',
			'EFT_AMOUNT',
		);
		$acctok=1;
		foreach ($vars as $var) {
			$$var=$pmatvars[$var]?$pmtvars[$var]:$_SESSION[$var];
		}
		if (!preg_match('/^\d{9}$/', $EFT_ROUTE)) {
			$this->Errors[]='Please enter a valid 9-digit bank routing number';
			$acctok=0;
		}
		if (!preg_match('/^\d+$/', $EFT_ACCT)) {
			$this->Errors[]='Please enter a valid account number. Please do not use spaces or dashes';
			$acctok=0;
		}
		if ($EFT_TYPE && ($EFT_TYPE != 'C' && $EFT_TYPE != 'S')) {
			$this->Errors[]='Please select the type of account.';
			$acctok=0;
		}
		if (!$EFT_BANK) {
			$this->Errors[]='Please enter the name of the bank.';
		}
		if (!$EFT_LAST) {
			$this->Errors[]='Please enter the last name or the business name of the account holder.';
		}
		if (! preg_match('/^\d*(\.\d{0,2})?$/', $CC_AMOUNT)) {
			$this->Errors[]='Please enter a valid amount to be transferred from your checking account.';
		}
		if ($acctok && !$this->Demo) { # use the daemon for more detailed route checking
			require_once('/web/php/ina_translator_daemon.php');
			$pmt=new ina_translator('INA::Payment::EFT', $this->Demo, $this->User, $this->ProjId, $this->ServiceCode, $this->OrderId);
			$testres=$pmt->call('Verify_php', array('type'=>$EFT_TYPE, 'routing'=>$EFT_ROUTE, 'account'=>$EFT_ACCT));
			if ($testres['MStatus'] != 'success') {
				$this->Errors[]=$testres['StatusMessage']? $testres['StatusMessage']: 'The system was not able to verify this routing number. Please double check the routing number or try a different account.';
				# to do: trigger user warning
			}
		}

		if (count($this->Errors)) {
			return 0;
		} else {
			return 1;
		}
	}


	function sendEFTTrans($pmtvars='') {
		# $pmtvars will override vars in the session
		if (!$this->OrderId) {
			$this->getOrderid();
		}
		foreach (array('ServiceCode', 'ProjectId') as $reqd) {
			if (!$this->${reqd}) {
				$this->Errors[]='The system is not able to process your transaction at this time. We apologize for the inconvenience. Please try again later.';
				$this->ErrCodes="$reqd is missing";
			}
		}
		$vars=array(
			'CustFirstName' => 'EFT_FIRST',
			'CustLastName' => 'EFT_LAST',
			'CustAcctNum' => 'EFT_CUST_NUM',
			'RoutingNumOrExpDate' => 'EFT_ROUTE',
			'DDAOrCCDNum' => 'EFT_ACCT',
			'BankName' => 'EFT_BANK',
			'Type' => 'EFT_TYPE',
			'TransAmount' => 'EFT_AMOUNT',
			'salecost' => 'EFT_SALECOST',
			'Appid' => 'EFT_APPID',
		);
		foreach ($vars as $pmt => $sess) {
			$pmtdetails[$pmt]=$pmatvars[$sess]?$pmtvars[$sess]:$_SESSION[$sess];
		}
		require_once('/web/php/ina_translator_daemon.php');
		$pmt=new ina_translator('INA::Payment::EFT', $this->Demo, $this->User, $this->ProjectId, $this->ServiceCode, $this->OrderId);
		$res=$pmt->call('SendTrans', $pmtdetails);
		if (!$this->Demo && $res['MStatus'] != 'success') {
			$this->Errors[]=$res['StatusMessage'];
			$this->ErrCodes[]=$res['MErrMsg'];
			return 0;
		} else {
			return 1;
		}
	}

}

%>
