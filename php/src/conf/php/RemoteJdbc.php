<?

class RemoteJdbc{

  function RemoteJdbc($verbose=0){
    $this->VERBOSE = $verbose;
    $this->Status = 1;    
    
    $this->SOCKET = fsockopen('127.0.0.1', 5381, $this->errno, $this->errstr, 15);

    if(!$this->SOCKET){
      $this->Trace("Could not create socket: " . $this->errstr);
      $this->Status = 0;
      return 0;      
    }      
                                    
    // test for readiness
    $msg = $this->getline();
    
    if($msg == 'JdbcServer ready.'){
    
    }else{
      $this->Trace("Database server not ready ($msg)");
      $this->errstr = $msg;
      $this->Status = 0;    
      return 0;
    }
                                      
  
  }
  
  function Trace($msg){
    if($this->VERBOSE == 1){
      echo $msg . "<br>\n";
    }
  }
  
  function getline(){
    return chop(fgets($this->SOCKET, 5000));
  }
  
  function putline($str){
    fputs($this->SOCKET, $str);
  }
  
  function Run($sql){
    $this->Trace("Running $sql");
    $this->putline($sql . "\n");
    
    $this->ColNames = split("\|", $this->getline());
    
    $this->Rows = array();
    while($row = $this->getline()){
      $this->Trace("Got row $row");
      $this->Rows[] = split("\|", $row);
    }
    
    $this->RowCount = count($this->Rows);
    
  }

}

?>