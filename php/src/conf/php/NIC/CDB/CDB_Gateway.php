<?php
//ini_set('include_path', '.:' . ini_get('include_path'));
require_once('SOAP/Client.php');

/*
Requires these defined:
	WS_URL_TRANS=https://cmbs-admin.soltn.dev.cdc.nicusa.com/ar/gateway/CDBTransactionService
	WS_URL_AUTH=https://cmbs-admin.soltn.dev.cdc.nicusa.com/ar/gateway/CDBAuthorizationService
	WS_URL_CUST=https://cmbs-admin.soltn.dev.cdc.nicusa.com/ar/gateway/CDBCustomerService
	WS_URL_PMT=https://cmbs-admin.soltn.dev.cdc.nicusa.com/ar/gateway/CDBPaymentService
	WS_URL_PRICE=https://cmbs-admin.soltn.dev.cdc.nicusa.com/ar/gateway/CDBPriceService
	
	CDB_USERNAME=arWsUser
	CDB_PASSWORD=Password
*/

class CDB_Gateway extends SOAP_Client {

	function CDB_Gateway() {
			$this->setOpt('timeout', 60);
			$this->setOpt('curl', CURLOPT_VERBOSE, 0);
			$this->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0);  // needed self signed certs
			$this->setOpt('curl', CURLOPT_TIMEOUT, 60);
	}


	function security_header() {
		$header = new SOAP_Header(
			'{http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd}Security',
			'object',
			'<ns4:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="UsernameToken-12039161"><ns4:Username>'.CDB_USERNAME.'</ns4:Username><ns4:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.CDB_PASSWORD.'</ns4:Password></ns4:UsernameToken>',
			1,
			Array('xmlns:ns4' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd')
		);

		$header->attributes['SOAP-ENV:actor'] = null;

		return($header);
	}


	function &modifyCustomerBillingOption($modifyCustomerBillingOption) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());

		// modifyCustomerBillingOption is a ComplexType, refer to the WSDL for more info.
		$modifyCustomerBillingOption_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$modifyCustomerBillingOption = new SOAP_Value(
			'modifyCustomerBillingOption',
			false,
			$modifyCustomerBillingOption,
			$modifyCustomerBillingOption_attr
		);

		$result = $this->call(
			'modifyCustomerBillingOption',
			$v = array(
				'modifyCustomerBillingOption' => $modifyCustomerBillingOption
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:modifyCustomerBillingOption',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &retrieveCustomerBillingInfo($retrieveCustomerBillingInfo) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());

		// retrieveCustomerBillingInfo is a ComplexType, refer to the WSDL for more info.
		$retrieveCustomerBillingInfo_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$retrieveCustomerBillingInfo = new SOAP_Value(
			'retrieveCustomerBillingInfo',
			false,
			$retrieveCustomerBillingInfo,
			$retrieveCustomerBillingInfo_attr
		);

		$result = $this->call(
			'retrieveCustomerBillingInfo',
			$v = array(
				'retrieveCustomerBillingInfo' => $retrieveCustomerBillingInfo
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:retrieveCustomerBillingInfo',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);


		return($result);
	}

	function &retrieveLoginInfo($retrieveLoginInfo) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());

		// retrieveLoginInfo is a ComplexType, refer to the WSDL for more info.
		$retrieveLoginInfo_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$retrieveLoginInfo = new SOAP_Value(
			'retrieveLoginInfo',
			false,
			$retrieveLoginInfo,
			$retrieveLoginInfo_attr
		);

		$result = $this->call(
			'retrieveLoginInfo',
			$v = array(
				'retrieveLoginInfo' => $retrieveLoginInfo
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:retrieveLoginInfo',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);
		
		
		
		/*
			b/c PEAR SOAP is broken, interpret raw XML response here
				(pear soap doesn't handle repeated elements, screws up the whole structure, so that this XSD:
					<xs:element name="accessGroups" maxOccurs="unbounded" type="xs:string" nillable="true"/>
				Rendered like this:			
				 <accessGroups>
                Bank
             </accessGroups>
             <accessGroups>
                CORPBULK
             </accessGroups>
				messes up the following elements (8/28/08, Bob Sanders)
				
			To use the result of this function:
				Count access groups: count($result[0]->accessGroups)
				Access group names: $result[0]->accessGroups[0] or [1]
				firstName: $result[0]->firstName[0]
				etc for.. <accessGroups>, <custId>, <custStatusCode>, <email>, <firstName>, <lastName>, <login>, <loginId>, <loginType>, <password>, <statusCode>
		*/
		
		$xml = @new SimpleXMLElement($this->xml);
		$xml->registerXPathNamespace('c', 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd');
		$result = $xml->xpath('//c:loginInfo');		

		return $result;
	}




	function &createCustomer($createCustomer) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
	
		// createCustomer is a ComplexType, refer to the WSDL for more info.
		$createCustomer_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$createCustomer = new SOAP_Value('createCustomer', false, $createCustomer, $createCustomer_attr);

		$result = $this->call(
			'createCustomer',
			$v = array(
				'createCustomer' => $createCustomer
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:createCustomer',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}

    function &modifyCustomer($modifyCustomer){
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());

		// modifyCustomer is a ComplexType, refer to the WSDL for more info.
		$modifyCustomer_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';
		
		$modifyCustomer = new SOAP_Value('modifyCustomer', false, $modifyCustomer, $modifyCustomer_attr);
		
		$result = $this->call(
			'modifyCustomer',
			$v = array(
				 'modifyCustomer' => $modifyCustomer
			),
			array(
				 'namespace'  => 'http://cmbs.cdc.nicusa.com.webservices/',
				 'soapaction' => 'urn:modifyCustomer',
				 'style'      => 'document',
				 'use'        => 'literal'
			)
		);
		
		return($result);
    }

	function &createReturnTransaction($createReturnTransaction) {
		$this->SOAP_Client(WS_URL_TRANS, 0);
		$this->addHeader($this->security_header());

		$createReturnTransaction_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$createReturnTransaction = new SOAP_Value('createReturnTransaction', false, $createReturnTransaction, $createReturnTransaction_attr);

		$result = $this->call(
			'createReturnTransaction',
			$v = array(
				'createReturnTransaction' => $createReturnTransaction
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:createReturnTransaction',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return $result;
	}


	function &createChargeTransaction($createChargeTransaction) {
		$this->SOAP_Client(WS_URL_TRANS, 0);
		$this->addHeader($this->security_header());
		// createChargeTransaction is a ComplexType, refer to the WSDL for more info.
		$createChargeTransaction_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$createChargeTransaction = new SOAP_Value('createChargeTransaction', false, $createChargeTransaction, $createChargeTransaction_attr);

		$result = $this->call(
			'createChargeTransaction',
			$v = array(
				'createChargeTransaction' => $createChargeTransaction
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:createChargeTransaction',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return $result;
	}


	function &createSubscription($createSubscription) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
		// createSubscription is a ComplexType, refer to the WSDL for more info.
		$createSubscription_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$createSubscription = new SOAP_Value('createSubscription', false, $createSubscription, $createSubscription_attr);

		$result = $this->call(
			'createSubscription',
			$v = array(
				'createSubscription' => $createSubscription
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:createSubscription',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &authorize($authorize) {
		$this->SOAP_Client(WS_URL_AUTH, 0);
		$this->addHeader($this->security_header());
		// authorize is a ComplexType, refer to the WSDL for more info.
		$authorize_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$authorize = new SOAP_Value('authorize', false, $authorize, $authorize_attr);

		$result = $this->call(
			'authorize',
			$v = array(
				'authorize' => $authorize
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:authorize',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &removeAccessGroups($removeAccessGroups){
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
		// removeAccessGroups is a ComplexType, refer to the WSDL for more info.
		$removeAccessGroups_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$removeAccessGroups = new SOAP_Value('removeAccessGroups', false, $removeAccessGroups, $removeAccessGroups_attr);

		$result = $this->call(
			'removeAccessGroups',
			$v = array(
				'removeAccessGroups' => $removeAccessGroups
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:removeAccessGroups',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);	
	}

	function &assignAccessGroups($assignAccessGroups) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
		// assignAccessGroups is a ComplexType, refer to the WSDL for more info.
		$assignAccessGroups_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$assignAccessGroups = new SOAP_Value('assignAccessGroups', false, $assignAccessGroups, $assignAccessGroups_attr);

		$result = $this->call(
			'assignAccessGroups',
			$v = array(
				'assignAccessGroups' => $assignAccessGroups
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:assignAccessGroups',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &getPrice($getPrice) {
		// getPrice is a ComplexType, refer to the WSDL for more info.
		$getPrice_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$getPrice = new SOAP_Value('getPrice', false, $getPrice, $getPrice_attr);

		$result = $this->call(
			'getPrice',
			$v = array(
				'getPrice' => $getPrice
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:getPrice',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &modifyLoginUser($modifyLoginUser) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
		// modifyLoginUser is a ComplexType, refer to the WSDL for more info.
		$modifyLoginUser_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$modifyLoginUser = new SOAP_Value('modifyLoginUser', false, $modifyLoginUser, $modifyLoginUser_attr);

		$result = $this->call(
			'modifyLoginUser',
			$v = array(
				'modifyLoginUser' => $modifyLoginUser
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:modifyLoginUser',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &createLoginUser($createLoginUser) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());

		// createLoginUser is a ComplexType, refer to the WSDL for more info.
		$createLoginUser_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$createLoginUser = new SOAP_Value('createLoginUser', false, $createLoginUser, $createLoginUser_attr);

		$result = $this->call(
			'createLoginUser',
			$v = array(
				'createLoginUser' => $createLoginUser
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:createLoginUser',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}


	function &getNewBatchPayment($getNewBatchPayment) {
		$this->SOAP_Client(WS_URL_PAYMENT, 0);
		$this->addHeader($this->security_header());

		// createLoginUser is a ComplexType, refer to the WSDL for more info.
		$getNewBatchPayment_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$getNewBatchPayment = new SOAP_Value('getNewBatchPayment', false, $getNewBatchPayment, $getNewBatchPayment_attr);

		$result = $this->call(
			'getNewBatchPayment',
			$v = array(
				'getNewBatchPayment' => $getNewBatchPayment
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:getNewBatchPayment',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}

	function &postPayment($postPayment) {
		$this->SOAP_Client(WS_URL_PAYMENT, 0);
		$this->addHeader($this->security_header());

		// createLoginUser is a ComplexType, refer to the WSDL for more info.
		$postPayment_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$postPayment = new SOAP_Value('postPayment', false, $postPayment, $postPayment_attr);

		$result = $this->call(
			'postPayment',
			$v = array(
				'postPayment' => $postPayment
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:postPayment',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}

	function &retrieveCustomer($retrieveCustomer) {
		$this->SOAP_Client(WS_URL_CUST, 0);
		$this->addHeader($this->security_header());
		// authorize is a ComplexType, refer to the WSDL for more info.
		$retrieveCustomer_attr['xmlns'] = 'http://data.server.webservices.clients.cdb.solutions.nicusa.com/xsd';

		$retrieveCustomer = new SOAP_Value('authorize', false, $retrieveCustomer, $retrieveCustomer_attr);

		$result = $this->call(
			'retrieveCustomer',
			$v = array(
				'retrieveCustomer' => $retrieveCustomer
			),
			array(
				'namespace'	 => 'http://cmbs.cdc.nicusa.com.webservices/',
				'soapaction' => 'urn:retrieveCustomer',
				'style'		 => 'document',
				'use'		 => 'literal'
			)
		);

		return($result);
	}

}
?>