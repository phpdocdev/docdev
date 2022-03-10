<?

require_once('http_post_auth.php');

class ina_translator{

  function ina_translator(){
    // store params as object vars

    $this->objectParams = array();    
    
    $this->objectName = func_get_arg(0);
    
    for ($i = 1; $i < func_num_args(); $i++) {
      $this->objectParams[] = func_get_arg($i);
    } 
  
  }
  
  function call($func){
 		return array( 'Error' => 1 ); 
    $func_params = array();
    for ($i = 1; $i < func_num_args(); $i++) {
      $func_params[] = func_get_arg($i);
    } 
    
    if(count($func_params)==0){
      $func_params[] = '';
    }
  
  
    // send the function out
    $ret = $this->send_request($func, $func_params);

    // we get a wddx packet back, decode it    
    //echo "<pre>($ret)</pre>";
    
    $value = wddx_deserialize($ret); 

    // return it
    return $value;
  
  }
  
  function send_request($func, $func_params){    
    
    
    $this->Poster = new http_post();
    $this->Poster->set_action("http://www.ark.org/development/bob/cust/ina2.cgi");
    $this->Poster->setAuth(1,2);

    $this->Poster->set_element('INA_OBJECT_NAME', $this->objectName );        
    $this->Poster->set_element('INA_OBJECT_PARAM', wddx_serialize_value($this->objectParams) );        
    
    $this->Poster->set_element('INA_FUNCTION_NAME', $func );        
    $this->Poster->set_element('INA_FUNCTION_PARAM', wddx_serialize_value($func_params) );  
    
    if($this->Verbose == 1){
      echo "<hr>show_post<br>";    
      echo htmlspecialchars($this->Poster->show_post());
    }  
  
    $ret = $this->Poster->send(0);
    
    //strip the header tags
    $ret = substr($ret, strpos($ret, "<wddxPacket"), strlen($ret));

    if($this->Verbose == 1){
      echo "<hr>";
      echo htmlspecialchars($ret);
    }    
    
    return $ret;    
    
  }
  

}

?>
