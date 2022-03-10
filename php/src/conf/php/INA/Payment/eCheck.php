<?

require_once('/web/html/development/bob/cust/INA/Payment.php');

class eCheck extends Payment{

  function eCheck($mode, $email, $projectId, $serviceCode, $orderId=0){
    $this->Payment($mode, $email, $projectId, $serviceCode);    
    $this->objectName = 'INA::Payment::EFT';
    $this->objectParams = func_get_args();  
  }

}

?>