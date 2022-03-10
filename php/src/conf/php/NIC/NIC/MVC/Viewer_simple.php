<?
class Viewer_simple{

	function Viewer_simple( $templateDir = '' ){
    $this->templateDir = $templateDir;
    $this->warnings = array();
	}
	
	// TODO: try to make this more efficient
	function show($filename, $dataElements=array() ){

    foreach( $dataElements as $k=>$v ){
      ${$k} = $v;
    }
    
    // capture output from $filename using ob_
    ob_start();    ob_implicit_flush(0);    require_once($filename);        $Content = ob_get_contents();    ob_end_clean();     
	
    // add warnings if they exist
    $Content = $this->showWarnings() . $Content;
	
    $this->_wrapContent($Content);
	
    // wrap Content in the skin
    echo $Content;
	}
	
	function _wrapContent(&$Content){
    if(!$this->templateDir){   
      return 1;
    }
    
    $file = join('', file($this->templateDir.'/template.html'));
    
    $Content = str_replace('{PAGECONTENT}', $Content, $file);
    $Content = str_replace('RESOURCE/', $this->templateDir.'/RESOURCE/', $Content);
    
    
	}
	
	function warn($message){
    $this->warnings[] = $message;
	}
	
	function showWarnings(){ 
	
     // if warnings exist, show them
    if( count($this->warnings)>0 ){
      ob_start();      ob_implicit_flush(0);
      ?>
        <table class=warn>
          <tr>
            <td><b>There was a problem with your input</td>
          </tr>
          <?
            foreach($this->warnings as $warning){
              ?>
                <tr>
                  <td><?=$warning?></td>
                </tr>
              <?
            }
          ?>
        </table>
      <?      $Warning = ob_get_contents();      ob_end_clean();
      return $Warning;
    }else{
      return '';
    }
	}

}
?>