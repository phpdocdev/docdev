<?

switch($this->S['paymethod']){
  case 'E': echeckScreen($this); break;
	case 'C': ccScreen($this); break;
}

function ccScreen(&$this){
			require_once('ina_translator_daemon.php');
			$INA = new ina_translator("INA::Payment::CC", 'bob');
      list($screen) = $INA->call('renderPage', 
			                  $this->S['amountToPay'], 
												$this->S['CC_NAME'], 
												$this->S['CC_ADDR'], 
												$this->S['CC_CITY'], 
												$this->S['CC_STATE'], 
												$this->S['CC_ZIP'], 
												$this->S['CC_TYPE'], 
												$this->S['CC_NUM'], 
												$this->S['CC_EXP_MO'], 
												$this->S['CC_EXP_YR'],
												'VM1');
    	echo $screen;	


}

 function echeckScreen(&$this){
		require_once('ina_translator_daemon.php');
		$INA = new ina_translator("INA::Payment::EFT", 'bob');
     list($screen) = $INA->call('renderPage', 
		                   number_format($this->S['amountToPay'],2), 
											 date("m/d/Y"), 
											 $this->S['CHECK_ROUTING'], 
											 $this->S['CHECK_ACCOUNT'], '', '1');
   	echo $screen;	
 }

?>

