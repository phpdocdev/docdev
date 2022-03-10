<?
class Monitor{
	
	var $applicationId;
	var $monitorName;
	var $enabled;
	var $email;
	
	function Monitor(&$Conf, $applicationId, $monitorName, $email, $enabled){
		$this->Conf = $Conf;
		$this->appId = $applicationId;
		$this->monitorName = $monitorName;
		$this->enabled = $enabled;
		$this->email = $email;
		
		$this->ApplicationLogDir = $Conf->Get('ApplicationLogDir');
		
		if( !$this->ApplicationLogDir ){
			$this->ApplicationLogDir = '/tmp/' . $this->appId . '.' . $this->monitorName . '.log';			
		}else{
			$this->ApplicationLogDir .= '/' . $this->appId . '.' . $this->monitorName . '.log';					
		}
		
		if( !@touch($this->ApplicationLogDir) ){
			$this->ApplicationLogDir = '/tmp/' . $this->appId . '.' . $this->monitorName . '.log';			
		}
		
		if( $this->enabled ){
			$GLOBALS['FW_STATE']['Config'][] = "Monitor logging to ".$this->ApplicationLogDir;
		}else{
			$GLOBALS['FW_STATE']['Config'][] = "Monitor logging to ".$this->ApplicationLogDir . ' (disabled)';		
		}

	}

	
	function setEnabled($val=true){
		$this->enabled = $val;
	}
	
	function log($message){

		if(!$this->enabled){
			return 0;
		}
	
		if( $this->email ){
			mail($this->email, "From: Log for ".$this->appId, $message, "From: Log for ".$this->appId." <".$this->email.">");
		}
		
		$fp = @fopen($this->ApplicationLogDir, "a");
		if( $fp ){
			fputs($fp, join(':', array(
				date("Y-m-d H:i:s"),
				$message,
			))."\n");			
			fclose($fp);
		}
	
	
	}
	
}
?>