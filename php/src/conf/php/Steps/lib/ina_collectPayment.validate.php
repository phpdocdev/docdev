<?

function ina_collectPayment_validate(&$this){
  if($this->S['paymethod'] == 'C'){
	  // do cc validation
		$this->setRequired('Step5', 'CHECK_ROUTING', 0);
		$this->setRequired('Step5', 'CHECK_ACCOUNT', 0);
		
		$this->setRequired('Step5', 'CC_NAME', 1);				
		$this->setRequired('Step5', 'CC_ADDR', 1);				
		$this->setRequired('Step5', 'CC_CITY', 1);				
		$this->setRequired('Step5', 'CC_STATE', 1);				
		$this->setRequired('Step5', 'CC_ZIP', 1);				
		$this->setRequired('Step5', 'CC_TYPE', 1);				
		$this->setRequired('Step5', 'CC_NUM', 1);				
		$this->setRequired('Step5', 'CC_EXP_MO', 1);
		$this->setRequired('Step5', 'CC_EXP_YR', 1);
		
	}else if($this->S['paymethod'] == 'E'){
	  // do echeck validation
		$this->setRequired('Step5', 'CHECK_ROUTING', 1);
		$this->setRequired('Step5', 'CHECK_ACCOUNT', 1);
		
		$this->setRequired('Step5', 'CC_NAME', 0);				
		$this->setRequired('Step5', 'CC_ADDR', 0);				
		$this->setRequired('Step5', 'CC_CITY', 0);				
		$this->setRequired('Step5', 'CC_STATE', 0);				
		$this->setRequired('Step5', 'CC_ZIP', 0);				
		$this->setRequired('Step5', 'CC_TYPE', 0);				
		$this->setRequired('Step5', 'CC_NUM', 0);				
		$this->setRequired('Step5', 'CC_EXP_MO', 0);
		$this->setRequired('Step5', 'CC_EXP_YR', 0);		
	}else{
	  $this->Error("You must select a payment method", '');
	}
}

?>