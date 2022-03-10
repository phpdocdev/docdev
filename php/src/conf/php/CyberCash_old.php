<?php

require('CyberClass.php');

class CyberCash{

  var $CyberClass;
  var $DEBUG;

  function CyberCash($DEBUG){
    if($DEBUG == 1){ 
      $this->DEBUG=1;
    }else{
      $this->DEBUG=0;    
    }

    if( ! class_exists('SQL_mysql') ){
      require('sql_mysql.php');
    }

    $this->CyberClass = new cyberclass("/web/shtml/informationnetworkofarkansa-29/mck-cgi/conf/merchant_conf");   
    $this->SQL = new SQL_mysql('ccarddemo');
  
  }
  

  function luhn($cc) {
		$ccnum=ereg_replace("\D", '', $cc);
		if (strlen($ccnum)<16) {
			return array(0,0,"Your credit card number has too few digits. Please double check the number.");
		}
		if (ereg("^4", $ccnum)) {
			$type='V';
		} elseif (ereg("^5", $ccnum)) {
			$type='M';
		} else {
			return array(0,0,"Please use only a Mastercard or Visa.");
		}
		$digits=preg_split('//', $ccnum, 0, PREG_SPLIT_NO_EMPTY);
		$rdigits=array_reverse($digits);
		$i=1;
		$sum=0;
		foreach ($rdigits as $dig) {
			if ($i) {
				$sum+=$dig;
				$i=0;
			} else {
				$tempsum=2*$dig;
				if ($tempsum>9) {
					$temp=preg_split('//', $tempsum, 0, PREG_SPLIT_NO_EMPTY);
					$tempsum=0;
					foreach ($temp as $t) {
						$tempsum+=$t;
					}
				}
				$sum+=$tempsum;
				$i=1;
			}
		}
		if ($sum % 10) {
			return array(0,$type,"This is not a valid credit card number. Please double check the number.");
		} else {
			return array(1,$type,'');
		}
	}
  
  function ChargeCard($C){
    $tm = getdate( gmmktime() );
    $pid = exec("/web/html/development/bob/ccphp/getnewpid.pl");

    $orderid = sprintf("%04d%02d%02d%02d%02d%05d",$tm['year'],$tm['mon'],$tm['mday'],$tm['hours'],$tm['minutes'], $pid);
    $C['Order-ID'] = $orderid;
     
    $er = 'Missing ';
  
      $NiceNames = array(
        'ServiceCode'    => 'Service Code',
        'Amount'         => 'Amount',
        'Card-Number'    => 'Credit Card number',
        'Card-Address'   => 'Credit Card Address',
        'Card-City'      => 'Credit Card City',
        'Card-State'     => 'Credit Card State',
        'Card-Zip'       => 'Credit Card Zip code',
        'Card-Country'   => 'Credit Card County',
        'Card-Exp-Month' => 'Credit Card expiration month',
        'Card-Exp-Year'  => 'Credit Card expiration year',
        'Card-Name'      => 'Credit Card owner name',
        'Card-Type'      => 'Credit Card type'
      );      
    
		if (!$this->DEBUG) {
			list($ok,$temp,$err)=$this->luhn($C['Card-Number']);
			if (!$ok) {
				return array(0, $err); 
			}
		}

		if(!$C['Card-Type']) {
			if(ereg("^4", $C['Card-Number'])) {
				$C['Card-Type']='V';
			} elseif(ereg("^5", $C['Card-Number'])) {
				$C['Card-Type']='M';
			} elseif( ereg("^34", $C['Card-Number']) || 
                ereg("^37", $C['Card-Number']) ) {
				$C['Card-Type']='A';      
      }
		}
    
    if(!$C['ServiceCode'])         { $er .= $NiceNames['ServiceCode'].', '; }
    if(!$C['Amount'])              { $er .= $NiceNames['Amount'].', '; }
    if(!$C['Card-Number'])         { $er .= $NiceNames['Card-Number'].', '; }
    if(!$C['Card-Address'])        { $er .= $NiceNames['Card-Address'].', '; }
    if(!$C['Card-City'])           { $er .= $NiceNames['Card-City'].', '; }
    if(!$C['Card-State'])          { $er .= $NiceNames['Card-State'].', '; }
    if(!$C['Card-Zip'])            { $er .= $NiceNames['Card-Zip'].', '; }
    if(!$C['Card-Country'])        { $er .= $NiceNames['Card-Country'].', '; }
    if(!$C['Card-Exp-Month'])      { $er .= $NiceNames['Card-Exp-Month'].', '; }
    if(strlen($C['Card-Exp-Year']) != 4)
                                   { $er .= $NiceNames['Card-Exp-Year'].', '; }    
    if(!$C['Card-Name'])           { $er .= $NiceNames['Card-Name'].', '; }
    if( ($C['Card-Type'] != 'M') &&         
        ($C['Card-Type'] != 'V') &&         
        ($C['Card-Type'] != 'A') ) { $er .= $NiceNames['Card-Type'].', '; }    

    if($C['Card-Exp-Year']){
      $C['Card-Exp-Year'] = substr($C['Card-Exp-Year'], 2,2);
    }

    if($er != 'Missing '){    
        $er = ereg_replace(", $","",$er);
        $response = array('MStatus' => $er);
        $response = $this->setNiceError($response);
        return array(0,$response);
    }

    // check for 2 digit year?

    // attempt to charge the card
    if( ($this->DEBUG == 1) && ($C['Card-Number'] == '2222222222222222') ){
      $response = array('MErrMsg' => 'Invalid card number');    
      
    }else if( ($this->DEBUG == 1) && ($C['Card-Number'] == '3333333333333333') ){
      $response = array('MErrMsg' => 'Invalid card number');        
      
    }else if($this->DEBUG == 1){
      $response = array('MStatus' => 'success');

    }else{       

      $response = $this->CyberClass->SendCC2_1Server('mauthonly', array(
          'Order-ID'     => $C['Order-ID'], 
          'Amount'       => 'usd ' . $C['Amount'], 
          'Card-Number'  => $C['Card-Number'], 
          'Card-Address' => $C['Card-Address'], 
          'Card-City'    => $C['Card-City'], 
          'Card-State'   => $C['Card-State'], 
          'Card-Zip'     => $C['Card-Zip'], 
          'Card-Country' => 'USA', 
          'Card-Exp'     => $C['Card-Exp-Month'] . '/' . $C['Card-Exp-Year'], 
          'Card-Name'    => $C['Card-Name'] 
      ));       
    }

    // parse the response
         
    // card-exp = 12/05
    // cust-txn = 42907612
    // avs-code = 
    // paid-amount = usd 11.50
    // MErrMsg = Financial Institution Response: Invalid card number. 
    // MErrLoc = ccsp
    // card-number = 411111
    // card-type = vs
    // order-id = 11223344
    // (must exist, or no status returned) MStatus = failure-hard | success | failure-bad-money
    // ref-code = 024900104452
    // auth-code = 15 
    // merch-txn = 42907612
    // action-code = 111
    // MErrCode = GW-0067    
    

    
    if($response['MStatus'] == 'success'){

      // record in database
      $response['Order-Id'] = $orderid;
      
     
      //$C['Card-Number'] = substr($C['Card-Number'], 0,3) . '**********' . substr($C['Card-Number'], 13, 3);
      
      if($this->DEBUG == 1){
        $chargetable = 'democharges';
      }else{
        $chargetable = 'charges';      
      }
      
      $sql = $this->SQL->makeInsert($chargetable, array(
        name       => $C['Card-Name'],
        address    => $C['Card-Address'],
        city       => $C['Card-City'],
        state      => $C['Card-State'],
        zip        => $C['Card-Zip'],
        country    => 'US',
        cardtype   => $C['Card-Type'],
        cardno     => $C['Card-Number'],
        exp        => $C['Card-Exp-Month'] . '/' . $C['Card-Exp-Year'],
        amount     => $C['Amount'],
        custid     => 'FORMULA: NULL',
        service    => $C['ServiceCode'],
        orderdate  => date("Y-m-d H:i:s", mktime()),
        shipdate   => date("Y-m-d H:i:s", mktime()),
        orderid    => $orderid,
        status     => 'F',
        avs        => $response['avs-code']
      ));


      $this->SQL->Run($sql);     
      
      return array(1, $response);
            
    }else{
      $response = $this->setNiceError($response);        
      return array(0, $response);    
    }
    
    
  }


  function setNiceError($response){

    if(ereg("^Missing ",$response['MStatus'])){
      $response['NiceMessage'] = 
        'It appears that you are missing one 
        or more fields required to complete
        a credit card transaction: ' . 
        ereg_replace("^Missing ","", $response['MStatus']);
                                  
    }else if(ereg("Invalid card number", $response['MErrMsg'])){
      $response['NiceMessage'] = 
        'The application was unable to verify your credit card. This 
        most often happens when an expiration date is incorrect, the address 
        submitted is not consistent with your billing address or the bank 
        has declined your card. You may try to correct any errors or make your 
        purchase using a different card. We apologize for any inconvenience 
        this may have caused.';
        
    }else if(ereg("LUHN", $response['MErrMsg'])){        
      $response['NiceMessage'] = 
        'The credit card number you entered appears to be invalid. Please 
        check the number you entered and try again.';
                
    }else{
      $response['NiceMessage'] = 
        'Unknown Error: '.$response['MErrMsg'].'<br>The application was unable to verify your credit card. This 
        most often happens when an expiration date is incorrect, the address 
        submitted is not consistent with your billing address or the bank 
        has declined your card. You may try to correct any errors or make your 
        purchase using a different card. We apologize for any inconvenience 
        this may have caused.';    
    }
    
    return $response;
  
  }

  function recordDatabase(){
  
    // table: democharges
    // name
    // address
    // city
    // state
    // zip
    // country
    // cardtype
    // cardno
    // exp
    // amount
    // custid
    // service
    // orderdate
    // shipdate
    // orderid
    // status
    // avs
     
    // table: demodetail
    // orderid
    // group
    // name
    // value  
    
  }

}

?>
