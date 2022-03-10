<?

require('formcache.php');

class formcache_web extends formcache{

  function formcache_web($prefix, $Verbose=0, $directives=array('RECACHEALL', 'RECACHE'), $array_base='', $noCaching=0){
	
	  global $HTTP_GET_VARS;
		global $HTTP_POST_VARS;
	
	  if($array_base){
		  $this->keySource = $array_base;
		}else{
	    $this->keySource = array_merge( $HTTP_POST_VARS, $HTTP_GET_VARS);
		}
	
	  $this->Verbose = $Verbose;
	  $this->directives = $directives;	 
		$this->prefix = $prefix;
		$this->cacheDir = '/tmp/'.$prefix . '/';
		$this->noCaching = $noCaching;
	

	
	  $this->formcache($this->prefix, $this->cacheDir);
	
	}
	
	function processDirectives(){
	  $this->Trace("processing directives");
	  if( $this->keySource['RECACHE'] == 1 && in_array('RECACHE', $this->directives) ){
		  $this->Trace("directive: RECACHE");
		  // recache this page
			$this->reCacheThis = 1;
			
			// dlete this from keySource
			unset($this->keySource['RECACHE']);			
		}
		
		if( $this->keySource['RECACHEALL'] == 1 && in_array('RECACHEALL', $this->directives) ){
		  $this->Trace("directive: RECACHEALL");
		  // recache all pages in $this->cacheDir
			$this->reCacheAll();
			unset($this->keySource['RECACHEALL']);
		}
		
	}
	
	function start(){
	  if($this->noCaching == 1){
		  return 0;
		}	
	  $this->noCacheHeaders();
	  $this->Trace("prefix: $this->prefix");
		$this->Trace("cache Dir: $this->cacheDir");		
	
	  $this->processDirectives();
		
		if($this->reCacheThis != 1){
			if( $this->exists( $this->keySource )){
			  $this->Trace("showing from cache");
			  echo join( '', $this->serve());
				exit;
			}
		}else{
		  $this->Trace("recaching this page");
		}
	  ob_start();
	  ob_implicit_flush(0);	 	
		
	}
	
	function finish(){
	  if($this->noCaching == 1){
		  return 0;
		}
	  $this->Trace("finish");
		$output = ob_get_contents();
		ob_end_clean(); 
		echo $output;
		$this->Trace("writing "  . strlen($output) . " to  cache file");
		$this->cache(array($output), $this->keySource);		
		if( is_array($this->err) ){
			foreach($this->err as $msg){
			  $this->Trace($msg);
			}		
		}
	}

	function reCacheAll(){
	  $this->Trace("recaching all files");
		$d = dir($this->cacheDir);
		while($entry=$d->read()) {
		    if($entry[0] == '.'){ continue; }				
				if( strpos($entry, ".cache") ){
					$this->Trace("delete - $CACHEDIR$entry");
		      unlink($this->cacheDir . $entry);
				}
		}
		$d->close();	
	}
	
	function Trace($msg){
	  if($this->Verbose == 1){
		  echo "$msg<br>\n";
		}
	}

	function noCacheHeaders(){
		$now = gmdate('D, d M Y H:i:s') . ' GMT';
		header('Expires: ' . $now);
		header('Last-Modified: ' . $now);
		header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
		header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
		header('Pragma: no-cache'); // HTTP/1.0
	}
	
}

?>