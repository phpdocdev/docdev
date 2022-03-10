<?

require_once('http_post_auth.php');

class cgi_translator{

  function cgi_translator(){


  }

  function call(){
    $param = func_get_args();
    $func = array_shift($param);
    return $this->call_function($func, $param);
  }
  
  function call_function($func, $param=array()){
  
    // send the function out
    $ret = $this->send_request($func, $param);

    // we get a wddx packet back, decode it    
    //echo "<pre>($ret)</pre>";
    
    $value = wddx_deserialize($ret); 

    // return it
    return $value;
  }
  
  function send_request($func, $param=array()){    
    
    $this->Poster = new http_post();
    $this->Poster->set_action("http://www.ark.org/development/bob/cust/ina.cgi");
    $this->Poster->setAuth(1,2);

    $this->Poster->set_element('INA_OBJECT_NAME', $this->objectName );        
    $this->Poster->set_element('INA_OBJECT_PARAM', wddx_serialize_value($this->objectParams) );        
    
    $this->Poster->set_element('INA_FUNCTION_NAME', $func );        
    $this->Poster->set_element('INA_FUNCTION_PARAM', wddx_serialize_value($param) );  
    
    //$this->Poster->show_post();
  
    $ret = $this->Poster->send(0);
    
    //strip the header tags
    $ret = substr($ret, strpos($ret, "<wddxPacket"), strlen($ret));
    
    return $ret;    
    
  }
  
}

?>