<?php

require_once('ina_translator_daemon.php');

class CyberCash{

  var $CyberClass;
  var $DEBUG;

  function CyberCash($DEBUG){
    if($DEBUG == 1){
      $this->DEBUG=1;
    }else{
      $this->DEBUG=0;
    }



  }

  function ChargeCard($C){

     $this->T = new ina_translator('INA::Payment::CC', $this->DEBUG, 'bob', 0, $C['ServiceCode']);

	   $cchash = array (
	    'card-number'  => $C['Card-Number'],
	    'card-expmon'  => $C['Card-Exp-Month'],
	    'card-expyr'   => $C['Card-Exp-Year'],
	    'card-name'    => $C['Card-Name'],
	    'card-address' => $C['Card-Address'],
	    'card-city'    => $C['Card-City'],
	    'card-st'      => $C['Card-State'],
	    'card-zip'     => $C['Card-Zip'],
	    'amount'       => $C['Amount'],
			'Appid'        => $C['Appid'],
			'salecost'  => $C['salecost'],
	   );

	   $results = $this->T->call('SendTrans', $cchash);

		 if( $results['MStatus'] == 'success' ){
		   // IT WORKED
       return array(1, array(
			   'Order-Id' => $results['RefNum'],
				 'MErrMsg' => $results['StatusMessage'],
				 'MStatus' => $results['MStatus'],
				 'NiceMessage' => $results['StatusMessage'],
			 ));
		 }else{
		   // IT FAILED
       return array(0, array(
			   'Order-Id' => $results['RefNum'],
				 'MErrMsg' => $results['StatusMessage'],
				 'MStatus' => $results['MStatus'],
				 'NiceMessage' => $results['StatusMessage']?$results['StatusMessage']:'Error Processing payment',
			 ));
		 }

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
}

?>

