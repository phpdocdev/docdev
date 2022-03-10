<?

require_once('/web/html/development/bob/cust/INA/Payment.php');

class CreditCard extends Payment{

  function CreditCard($mode, $email, $projectId, $serviceCode, $orderId=0){
    $this->Payment($mode, $email, $projectId, $serviceCode);    
    $this->objectName = 'INA::Payment::CC';
    $this->objectParams = func_get_args();  
  }

}

?>