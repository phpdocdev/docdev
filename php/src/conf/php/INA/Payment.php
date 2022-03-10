<?

require_once('/web/html/development/bob/cust/INA.php');

class Payment extends INA{

  function Payment($mode='', $email='', $projectId=0, $serviceCode='', $orderId=0){
    $this->INA($mode, $email);
    
    $this->projectId = $projectId;
    $this->serviceCode = $serviceCode;
    $this->orderId = $orderId;
    
    $this->objectName = 'INA::Payment';    
    $this->objectParams = func_get_args();    
    
    
  }
  
  function receipt($email, $num, $type='C', $msg=array(), $det=array(), $items=array()){
  
    $receipt = $this->call_function('receipt', array(
      $email, $num, $type, $msg, $det, $items
    ));
    
    // this should be a string   
    return $receipt;
    
  }
  

  function orderid($status){
  
    return $this->call_function('orderid', array(
      $status,
    ));
    
  }

}

?>