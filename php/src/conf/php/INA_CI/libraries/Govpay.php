<?php

/**
 * GovPay class
 * 
 * This class communicates with the GovPay SOAP web service to initialize a session. 
 * It returns a session number if successful or error message if not. This class can
 * be used independently of CodeIgniter as it does not use any of CI's resources.
 */
class Govpay {

	public $affiliateHashId = '';//should be config setting
	public $sessionId		= '';//usually NULL
	public $amount			= '';//total gross
	public $appId			= '';//can be anything, in this case department ID
	public $payBy			= '';//c for credit, e for echeck or NULL to let user decide in GovPay
	public $backUrl			= '';//URL GovPay needs to send user back upon completed or cancellation
	public $backUrlText		= '';//usually NULL
	public $outerUniqueId	= '';//usually NULL
	public $serviceId		= '';//service ID is appended to vendorId in payment system, so vendorId_serviceId = service
	public $text			= '';//html receipt
	public $transnum		= '';//usually NULL
	public $orderId			= '';//NOTE: new feature, this will usually be set to transaction number so local db can be current with payment system
	public $vendorId		= '';//vendor code
	public $exitUrl			= '';
	public $name			= '';
    public $address			= '';
    public $city			= '';
    public $state			= '';
    public $zipcode			= '';
    public $email			= '';//prefill email address (new as of 6/25/2007)
    public $phone			= '';
    public $salestaxZipcode = '';#usually NULL, set if you want to calculate tax
    public $salecost		= '';#can pass in salecost
    public $fee			= '';#can pass in fee 
    public $taxableAmount	= '';#can pass in tax amount
    #public $useFeeFormula	= 'n';//use fee formula
	#public $useScFormula	= 'n';//use sale cost formula

	/**
	 * GovPay get GPC Session ID - this function will try to initialize a GovPay SOAP client and retrieve 
	 * a SessionId so that it can redirect the user to the GovPay payment system.
	 * 
	 * @access protected
	 * @param array $data array of values needed to initialize SOAP client
	 * @return mixed sessionID or string error message
	 */
	function getGPCSessionId($data){
	
		try {
			//load data values
			$this->affiliateHashId	= $data['affiliateHashId'];
			$this->backUrl			= $data['backUrl'];
			$this->exitUrl			= $data['exitUrl'];
			$this->serviceId		= $data['serviceId'];
			$this->vendorId			= $data['vendorId'];
			$this->amount			= $data['amount'];
			$this->appId			= $data['appid'];//notice appid passed in has a lowercase 'i'
			$this->text				= $data['text'];
			
			#opt params
			$this->name				= $data['name'];
			$this->address			= $data['address'];
			$this->city				= $data['city'];
			$this->state			= $data['state'];
			$this->zipcode			= $data['zipcode'];
			$this->email			= $data['email'];
			$this->phone			= $data['phone'];
			
			#tax fields
			$this->taxableAmount	= $data['taxable_amount'];
			$this->salestaxZipcode = $data['salestax_zipcode'];
			
			if ($data['source'])
				$this->source = $data['source'];

			if ($data['useFeeFormula'])
				$this->useFeeFormula = $data['useFeeFormula'];//'y' or 'n'
				
			if ($data['useScFormula'])
				$this->useScFormula	= $data['useScFormula'];//'y' or 'n'
				
			if ($data['salecost'])
				$this->salecost = $data['salecost'];#pass in sale cost instead of formula 
		
			if ($data['fee'])
				$this->fee = $data['fee'];#pass in fee instead of formula			
	
			if (!$data['orderId'])
				$this->orderId		= $this->create_orderid();//generate new transnum
			else
				$this->orderId		= $data['orderId']; 

			$service = $data['GPCSessionService'];

			$client = new SoapClient($service, array('exceptions' => true));//start client
			
			$result = $client->getSessionId($this);//initialize
			
			if ($result->success == 1)//success!
				return $result->sessionNumber;//return session ID if successful
			else
				return $result->errorMessages->item;//return error message
		}
		catch (Exception $e) {
			return $e;//pass back error object
		}
	}
	
	/**
	 * GovPay create transnum - generates a unique transaction number based on today's date and a random 3-digit number
	 * 
	 * @access protected
	 * @return string YYYYMMDDHH:MM:SS
	 */
	function create_orderid(){
		return date("Ymdhis") . rand(111, 999);//YYYYMMDDHH:MM:SS + a random 3 digit number
	}
}

?>