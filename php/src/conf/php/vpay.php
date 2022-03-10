<%
class vpay {
	var $MerchantID;
	var $URL;
	var $Action;
	var $LockFile;
	var $Demo;
	

	function vpay ($demo=1; $lockfile='/ina/misc/vpay/lock'; $merchant=''; $url='https://vpaytran.virtualpay.com/v7/vpay-bin/vpay?ReadPayMethod=Y'; $action='ConfirmPay'){
		$this->Demo=$demo;
		if ($demo && !$merchant) {
			$this->MerchantID='NICTEST';
		} elseif (!$merchant) {
			$this->MerchantID='NICINA';
		} else {
			$this->MerchantID=$merchant;
		}
		$this->LockFile=$lockfile;
		$this->Action=$action;
		$this->URL=$url."&Action=".$action;
		return 1;
	}



	function CheckConn {
		return (!(file_exists($this->LockFile)));
	}



	function MakeTrans ($route, $acct, $type, $first, $last, $uid, $amt, $service, $appid='') {
		if ($this->Demo) {
			$this->URL.="&RoutingNumOrExpDate=083000056&DDAOrCCDNum=000000";
			$this->URL.="&Type=CHK&CustFirstName=NIC&CustLastName=Portal";
			$this->URL.="&CustAcctNum=NIC.test&TransAmount=0.01";
		} else {
			$this->URL.="&RoutingNumOrExpDate=$route&DDAOrCCDNum=$acct";
			$this->URL.="&Type=$type&CustFirstName=$first&CustLastName=$last";
			$this->URL.="&CustAcctNum=$uid&RefNum=$service&TransAmount=$amt";
		}
		$ch=curl_init($this->URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$cr=curl_exec($ch);
		$result=array();
		foreach ($cr as $line) {
			preg_match("/^(\w*)\: (.*)$/", $line, $regs);
			$k=$regs[1]; $v=$regs[2];
			$result[$k]=$v;
		}
		if (!($result['Error'])) {
			// connect to customer daemon here
		}
		return $result;
	}



	function ErrorText ($code) {
		$errors=array(
			1       => "ERROR"
			"01101" => "TRANS NO REFERENCENUM"
			"01102" => "TRANS NO CUSTACCTNUM"
			"01103" => "TRANS NO TRANSAMOUNT"
			"01104" => "TRANS NO ROUTINGNUM"
			"01105" => "TRANS NO DDANUMBER"
			"01106" => "TRANS NO MERCHANTID"
			"01107" => "TRANS BAD ROUTINGNUM"
			"01108" => "TRANS NO REMITTANCETARGET"
			"01110" => "TRANS INSERT FAILED"
			"01111" => "TRANS COMMIT FAILED"
			"01200" => "TRANSACTION NOT FOUND"
			"01201" => "TRANS ALREADY EXISTS"
			"01301" => "HISTORY ERROR OPENING CURSOR"
			"01302" => "MISSING AUTHORIZATION"
			"02100" => "CUSTOMER PROFILE INSERT FAILED"
			"02101" => "CUSTOMER PROFILE ALREADY EXISTS"
			"02102" => "CUSTOMER PROFILE COMMIT FAILED"
			"02200" => "CUSTOMER PROFILE NOT FOUND"
			"02300" => "CUSTOMER PROFILE NOT VALID"
			"02301" => "CUSTOMER PROFILE INVALID RT"
			"02302" => "CUSTOMER PROFILE INVALID DDA"
			"02303" => "CUSTOMER PROFILE INVALID MERCHANT"
			"02304" => "CUSTOMER PROFILE INVALID CUSTACCT"
			"02305" => "CUSTOMER PROFILE INVALID EMAIL"
			"02306" => "CUSTOMER PROFILE INVALID PIN"
			"02307" => "PROFILE ROUTINGNUMS DONT MATCH"
			"02308" => "PROFILE DDANUMBERS DONT MATCH"
			"02309" => "CUSTOMER PROFILE INVALID BANKNAME"
			"02310" => "DATA FAILS MERCHANT VALIDATION RULES"
			"08001" => "TRANS ROUTINGNUM FAILS CHECK"
			"08002" => "DATA CONTAINS NONNUMERIC"
			"08003" => "MERCHANTID FAILS CHECK"
			"08004" => "CUSTPIN FAILS CHECK"
			"08005" => "CUSTACCTNUM FAILS CHECK"
			"08006" => "EMAIL NULL"
			"08007" => "EMAIL NOT DELIVERABLE"
			"08008" => "FILENAME INVALID"
			"08009" => "FILE UNOPENABLE"
			"08010" => "PROFILE INVALID KEYVALUE"
			"08011" => "PROFILE INVALID CUSTLASTNAME"
			"08012" => "PROFILE INVALID ADDRESS"
			"08013" => "PROFILE INVALID CITY"
			"08014" => "PROFILE INVALID STATEPROVINCE"
			"08015" => "PROFILE INVALID POSTALCODE"
			"08016" => "PROFILE INVALID COUNTRY"
			"08017" => "PROFILE INVALID EMAIL"
			"08018" => "PROFILE INVALID ROUTINGNUM"
			"08019" => "PROFILE INVALID DDANUMBER"
			"08020" => "DATA FAILS LUHNCHECK"
			"08021" => "PROFILE INVALID CUSTACCTNUM"
			"08022" => "COULDNT OPEN CUSTOMER CURSOR"
			"08023" => "PROFILE INVALID CUSTMIDNAME"
			"08024" => "PROFILE INVALID CUSTFIRSTNAME"
			"09031" => "TRANS EXCEEDS RECENT AMOUNT"
			"09032" => "TRANS EXCEEDS RECENT NUMBER"
			"09036" => "DBMS CONNECT FAILED"
			"09038" => "TRANS INVALID CUSTLASTNAME"
			"09039" => "TRANS INVALID ROUTINGNUM"
			"09040" => "TRANS INVALID CUSTACCTNUM"
			"09041" => "TRANS INVALID DDANUMBER"
			"09042" => "TRANS MISSING DDANUMBER"
			"10004" => "PAYMETHOD INVALID BANKNAME"
			"10005" => "PAYMETHOD INVALID DDAORCCDNUM"
			"10006" => "PAYMETHOD INVALID ROUTINGNUMOREXPDATE"
			"10007" => "PAYMETHODS NOT UPDATED"
			"10008" => "PAYMETHOD INVALID TYPE"
			"10009" => "PAYMETHOD MISSING FROMACCOUNT"
			"10010" => "PAYMETHOD MISSING BANKNAME"
			"10011" => "PAYMETHOD INVALID ACCTHOLDERNAME"
			"10012" => "PAYMETHOD INVALID PAYMETHODNAME"
			"11001" => "TRANSACTION PAYMETHOD NOT ALLOWED LATE"
			"11004" => "TRANSACTION EXCEEDS HIGH LIMIT"
			"11005" => "TRANSACTION BELOW LOW LIMIT"
			"20001" => "EPIC UR UA"
			"20002" => "EPIC UR MA"
			"20003" => "EPIC UR RAUNK"
			"20004" => "EPIC UR AUNK"
			"20005" => "EPIC MR UA"
			"20006" => "EPIC MR MA"
			"20007" => "EPIC MR RAUNK"
			"20008" => "EPIC MR AUNK"
			"20009" => "EPIC PDO"
			"20010" => "EPIC RN NOTFOUND"
			"20011" => "EPIC RN INVALID"
		)
		$rv=$errors[$code];
		return $rv;
	}
}
%>
