<?

include_once("class.FastTemplate.php3");

class Template{
  
  function Template($dir = ''){
    if(!$dir){
      $dir = getcwd();
    }
    $this->dir = $dir;
  }
  
	function setColors($c1, $c2){
	  $this->Colors = array(
		  1 => $c1,
			-1 => $c2,
		);
		$this->resetColors();
	}
	
	function resetColors(){
	  $this->idx = -1;
	}
	
	function getColor(){	  
		return $this->Colors[$this->idx];
	}
	
	function rotateColor(){
  	$this->idx *= -1;
	}
	
  function printTemplate($templatefile, $param=array()){
  
    // get the directory
    $parts = split("/", $templatefile);
    
    // get the filename
    $filename = array_pop($parts);
    
    if( count($parts)>0 ){
      // user gave a dir for this      
      $tpl=new FastTemplate( join("/", $parts) );        
    }else{
      // use the default
      $tpl=new FastTemplate( $this->dir );        
    } 

  	$tpl->define(array(
   	 "tt"=>$filename
  	));  
        
    $tpl->assign($param);
    
    $tpl->parse("GLOBAL", "tt");
    
    return $tpl->FastPrint(NULL, 1);      
  }
	
	function startCapture(){
    ob_start();
    ob_implicit_flush(0);
	}
	
	function endCapture(){
    $page = ob_get_contents();
    ob_end_clean(); 
		return $page;  	
	}
  
}

?>