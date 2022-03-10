<?
  class ina_translator{
    
    function ina_translator(){
		
		  $this->hostname = 'ina.ark.org';
			$this->port = 2000;
		
			$this->allowRestart = 0;
		
      $this->SOCKET = 0;    
      $this->VERBOSE = 0;
      $this->showWDDX = 0;
      
			if(func_num_args() > 0){
	      $this->ObjectName = func_get_arg(0);
	      $this->ObjectParams = array();
	      for($i=1; $i<func_num_args(); $i++){
	        $this->ObjectParams[] = func_get_arg($i);
	      }
			}
      
    }
    
		function setHost($host, $port=2000){
		  $this->hostname = $host;
			$this->port = $port;
			if($this->SOCKET){
	  		$this->closeSocket();					
  			$this->openSocket();			
			}
		}
		
		function restartServer(){
	    $this->Trace("attempting to restart the server");		
		  if($this->allowRestart){		    
				//exec("/ina/misc/ina-daemon/restart.pl", $ret);
				$this->Trace(join(" ",$ret));
			}
		}
		
		function closeSocket(){
		  fclose($this->SOCKET);
		}		
		
    function openSocket(){
    
      $this->Trace("Opening socket");
    
		  if(! $this->doOpen()){
			  // restart and try again
				$this->restartServer();
				if(! $this->doOpen()){
				  $this->fatalError('I cant restart the daemon!');
					return 0;
				}
			}
			
      $this->Trace("Socket opened");
			
			return 1;
    
    }
    
		function doOpen(){
      $this->SOCKET = fsockopen($this->hostname, $this->port, $this->errno, $this->errstr, 15);
  
      if(!$this->SOCKET){
        $this->Trace("Could not create socket: " . $this->errstr);
        $this->Status = 0;
        return 0;      
      }else{
        $this->Trace("Socket created");      
				return 1;
      }
                                      
      // test for readiness
      //$msg = $this->getline();		
		}
		
		function fatalReturnVal(){
		  return array(
					  Error => 1,
						ErrorMessage => "Cant Connect",
					);
		}
		
		function fixArrays(&$p){
			
			$this->recurseDepth++;
			
			if($this->recurseDepth > 100){
			  return 0;
			}
			
			for($i=0; $i<count($p); $i++){			
			  if( is_array($p[$i]) ){
				  if( count($p[$i])==0 ){
					  echo 'fix';
					  $p[$i] = '';
					}else{
					  $this->fixArrays($p[$i]);
					}
				}
			}
			
		}
		
    function sendCommand($Function, $Params){
		
	    // go through Params and fix any 0 length arrays
			$this->recurseDepth = 0;
			$this->fixArrays(&$Params);
	
      if($this->SOCKET == 0){
        if(! $this->openSocket()){
				  return $this->fatalReturnVal();
				}
      }

  		$this->Trace("Sending command"); 			
			
      if( count($Params)==0 ){
        $Params = '';
      }
      
			if( count($this->ObjectParams)==0 ){
			  $this->ObjectParams[] = '';
			}
		
      $Packet = wddx_serialize_value(
        array(
          INA_OBJECT_NAME => $this->ObjectName,
          INA_OBJECT_PARAM => $this->ObjectParams,
          INA_FUNCTION_NAME => $Function,
          INA_FUNCTION_PARAM => $Params,
        )
      ); 
      
      if($this->showWDDX == 1){      
        echo '<hr>'.htmlspecialchars($Packet).'<hr>';
      }
      
      $this->putline($Packet);
      $response = $this->getline(); 
			
			if(!$response){
			  return $this->fatalReturnVal();
			}
			
      if($this->showWDDX == 1){      
        $this->Trace("Response: " . htmlspecialchars($response) . "<hr>");      
      }
      $value = wddx_deserialize($response);   
      $this->Trace("Response decoded: ($value)<hr>");          

      return $value;
      
    }
    
    function call(){
      $FunctionName = func_get_arg(0);
      $FunctionParams = array();
      for($i=1; $i<func_num_args(); $i++){
        $FunctionParams[] = func_get_arg($i);
      }    
      return $this->sendCommand($FunctionName, $FunctionParams);      
    }
    
    function call_hash(){
      $FunctionName = func_get_arg(0);
      $FunctionParams = array();
      for($i=1; $i<func_num_args(); $i++){
        $FunctionParams[] = func_get_arg($i);
      }    
      return $this->sendCommand($FunctionName, $FunctionParams);      
    }		
		
    function doCmd(){
      $FunctionName = 'INA_RUN_COMMAND';
      $FunctionParams = array();
      for($i=0; $i<func_num_args(); $i++){
        $FunctionParams[] = func_get_arg($i);
      }    
      return $this->sendCommand($FunctionName, $FunctionParams);      
    }		
		
    function getParam($paramName){
      return $this->sendCommand('getParam', array($paramName));
    }
    
    function Trace($msg){
      if($this->VERBOSE == 1){
        echo $msg . "<br>\n";
      }
    }

		function fatalError($msg){
  	  // big bad problems, email some people and fail permanently
			$this->Trace('Fatal error: ' . $msg);
			//mail('root@ark.org', 'BIG DAEMON PROBLEMS!', $msg);		  
		}
		
    function getline(){
		  $line = @fgets($this->SOCKET, 50000);
			if($php_errormsg){
        $this->fatalError('on getline: ' . $php_errormsg);
				return 0;
			}else{
  			return chop($line);
			}
      
    }
    
    function putline($str){
      @fputs($this->SOCKET, $str."\n");
			if($php_errormsg){
        $this->fatalError('on putline: ' . $php_errormsg);				
			}		
    }

    
  }

?>
