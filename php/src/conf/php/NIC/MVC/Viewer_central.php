<?
class Viewer_central{

	function Viewer_central( $templateDir = '' ){
    $this->warnings = array();
    $this->templateKeys = array();
    $this->pathToTemplates = '';
    $this->templateFile = '';
    $this->webContext = '';
    $this->sharedWebContext = '';
    $this->Debug = false;
	}
	
	function setDebug($val=false){
		$this->Debug = $val;
	}
	
	function setTemplateKey($key, $value){
		$this->templateKeys[$key] = $value;
	}
	
	function setSharedWebContext($path){
		$this->sharedWebContext = $path;
	}
	
  function setTemplateBase($pathToTemplates){
  	// e.g. '/Users/bob/Sites/dev/templates'
  	$this->pathToTemplates = $pathToTemplates;
  }
  
  function setTemplate($templateFile){
  	// e.g. 'counties/pulaskicounty/skin.html'
  	$this->templateFile = $templateFile;
  }
  
  function setTemplateContent($templateContent){
  	// e.g. 'counties/pulaskicounty/skin.html'
  	$this->templateContent = $templateContent;
  }
  
  function setWebContext($pathToWeb){
  	// e.g. https://www.ark.org/agfc/
  	//      ./
  	//      ../../images/
  	$this->webContext = $pathToWeb;
  }
	
	function fatalError($message, $email, $Content){
		$this->show('', array(), $Content);
	}
	
	function getView($filename, $dataElements=array()){
    
    foreach( $dataElements as $k=>$v ){
      ${$k} = $v;
    }
    
    // capture output from $filename using ob_
    ob_start();
    ob_implicit_flush(0);
    require_once($filename);    
    $Content = ob_get_contents();
    ob_end_clean();     
		
		return $Content;	
	}
	
	// TODO: try to make this more efficient
	function show($filename, $dataElements=array(), $Content='' ){
		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central showing $filename";   			

		if($Content){
		
		}else{
			$Content = $this->getView($filename, $dataElements);
		}
    // add warnings if they exist
    $Content = $this->showWarnings() . $Content;
	
		if ($this->Demo) {
			$Content='<hr><p class=warn align=center>DEMONSTRATION</p><hr>' . $Content;
		}

    $this->_wrapContent($Content);

		if( $this->Debug){
			$Content .= $this->_showState();
		}

    // wrap Content in the skin
    echo $Content;
		exit;
	}

	function getContents($filename, $dataElements=array() ){
    foreach( $dataElements as $k=>$v ){
      ${$k} = $v;
    }
    ob_start();
    ob_implicit_flush(0);
    require($filename);    
    $Content = ob_get_contents();
    ob_end_clean();     
    return $Content;
	}


	function _getTemplateContents(){
		
		if($this->templateContent){
			return $this->templateContent;
		}
		if( strpos($this->pathToTemplates, 'db:')==0 ){
			$dsn = str_replace('db:', '', $this->pathToTemplates);
			
			$GLOBALS['FW_STATE']['Template'][] = "Using template from $dsn template:".$this->templateFile;
			
			
			require_once('DB.php');
			$DB = DB::Connect($dsn, false);
			if( DB::isError($DB) ){
				$GLOBALS['FW_STATE']['Template'][] = "Unable to load template ".$this->templateFile. " from $dsn";			
				$GLOBALS['FW_STATE']['Template'][] = "Viewer_central using default template";	
				return '<INANoHeader>Default Template<hr><%% $BODY %%><hr>';
			}
			$DB->setFetchMode(DB_FETCHMODE_ASSOC);
			
			$content = $DB->getRow("select template from gtemplate where name='".$this->templateFile."'");
			
			//if( !$this->webContext && $content['defaultContext'] ){
				//$this->setWebContext($content['defaultContext']);
		//	}
			
			return $content['template'];			
		}

		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central looking for template in ".$this->pathToTemplates;
		
		$startat = $this->pathToTemplates.'/'.$this->templateFile;


		if( file_exists($startat) ){
			return join('', file($startat));
		}
		
		// now work backwards in the template path, trying to find a file
		$parts = split('/', $startat);
		
		$filename = array_pop($parts);
		
		while( count($parts)>0 ){
			array_pop($parts);
			$try = join('/',$parts).'/'.$filename;
			if( file_exists($try) ){
				$GLOBALS['FW_STATE']['Template'][] = "Viewer_central using $try";
				return join('', file($try));				
			}
		}
		
		// if nothing found, return a blank template
		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central using default template";	
		return '<INANoHeader>Default Template<hr><%% $BODY %%><hr>';
		
	}

	function _wrapContent(&$Content){
	
		$file = $this->_getTemplateContents();
    $Content = str_replace('<%% $BODY %%>', $Content, $file);
    //$Content = str_replace('RESOURCE/', $this->templateDir.'/RESOURCE/', $Content);
    
   	$Content = str_replace('@image_path@', $this->webContext, $Content);    
    
   	// src="RESOURCE/header.jpg"
   	// find all the src= background= href=...css href=...js
   	preg_match_all('/ (?:background|src)[ ]*=[\" \']*([^ \"\'\>]+)/i', $Content, $matches);
		preg_match_all('/ href\s*=\s*[\"\']([^ \"\'\>]+\.(?:css|js))/i', $Content, $cssmatches);
   	$matches=array_merge($matches[1], $cssmatches[1]);


   	$imagesToReplace = array();
   	foreach($matches as $m){
   		if( !$imagesToReplace[$m] && 
   				!preg_match('/(?:http|https):\/\//', $m) && 
   				!preg_match('/^\//', $m) ){
		   	$imagesToReplace[$m] = 1;
		  }
   	}   	
   	
		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central applying webContext ".$this->webContext;   	
		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central applying sharedWebContext ".$this->sharedWebContext;   	
   	
   	foreach($imagesToReplace as $m=>$trash){
   		// if the path starts with "./" treat it as a local file
   		if( preg_match('/^\.\//', $m) ){
				// do nothing
				
			}else if( preg_match('/FW_SHARED\//', $m) ){
				$m2 = str_replace('FW_SHARED/', '', $m);
				$Content = str_replace($m, $this->sharedWebContext.$m2, $Content);			
   		}else{
				$Content = str_replace($m, $this->webContext.'/'.$m, $Content);
			}
   	}
   	
   	foreach( $this->templateKeys as $k=>$v ){
   		$Content = str_replace($k, $v, $Content);
   	}

	}
	
	function warn($message){
    $this->warnings[] = $message;
	}
		
	function showWarnings(){
	
     // if warnings exist, show them
    if( count($this->warnings)>0 ){
      ob_start();
      ob_implicit_flush(0);

      ?>
        <table class=warn>
          <tr>
            <td><b>There was a problem with your input</td>
          </tr>
          <?
            foreach($this->warnings as $warning){
              ?>
                <tr>
                  <td> &nbsp; &nbsp; <?=$warning?></td>
                </tr>
              <?
            }
          ?>
        </table>
      <?
      $Warning = ob_get_contents();
      ob_end_clean();
      return $Warning;
    }else{
      return '';
    }
	}

	function setFatalPage($page) {
		$this->FatalPage=$page;
	}

	function showFatal() {
		while (@ob_end_clean()); # clear all current buffers
		require($this->FatalPage);
		exit;
		# $this->show($this->FatalPage);
		# how should it behave if the FatalPage has not been set?
	}

	function showShared($sharename) {
		#$this->show('SharedViews/'.$sharename);
		$GLOBALS['FW_STATE']['Template'][] = "Viewer_central showing $sharename";   			
		require('NIC/MVC/SharedViews/'.$sharename);
	}

	function _showState(){
    ob_start();
    ob_implicit_flush(0);
    ?>
    	<script language="javascript">
				//win = window.open('','FW_STATE_WIN','location=no,toolbar=no,menubar=no,width=450,height=400,top=0,left=0');    		
				//fwin = window.open('index.php?FW_STATE_WIN=1','FW_STATE_WIN');    		
    	</script> 
    <?
    $Content = ob_get_contents();
    ob_end_clean();
	
		$ret = '';		
		foreach($GLOBALS['FW_STATE'] as $cat=>$messages){
			foreach($messages as $m){
				$ret .= "$cat: $m<br>";
			}
		}		
		return $ret;
	}

}
?>
