
<? 
class ARCODE {

function showARCode($code){
  if( ereg("http", $code) ){
    // filter from the arkleg site
  }else{
    // pull from our cache
   $output = $this->CacheSearch($code);    
    
  }
  return $output;
}
  
  function SiteSearch($code){
    require('http_post.php');
    $a = new http_post();
    $a->set_action("http://www.arkleg.state.ar.us/newsdcode/lpext.dll");    
    $a->set_enctype("multipart/form-data");    
	$a->set_element(array(
        "f"	 => "hitlist",
        "t"	 => "main-hit-h.htm",
        "tf" => "doc",
        "tt" => "document-frame.htm",
        "x"	 => "Simple",
        "d"	 => "",
        "c"	 => "redirect",
        "s"	 => "Relevance-Weight",
        "ht" => "hitlist.htm",
        "a"	 => "Title",
        "h1" => "Hit[,5]",
        "h2" => "Relevance-Weight[,10]",
        "h3" => "Title[,85]",
        "q"  => $code
    ));
	$resp = $a->send(1);    
    
    echo $resp;
      
      
    
  }
  
  function CacheSearch($code){
  require ('sql_mysql.php');
  $arCode = new SQL_mysql('arcode', 'arcode', 'arcode', 'proddb');
  
  $info = $arCode->fetchrow("select * from code where code='$code'");
  
  $info['content'] = eregi_replace("/newsdcode","http://www.arkleg.state.ar.us/newsdcode",$info['content']);

  if($info['code']){
    ?>
      <table>
        <tr>
          <td><font face="arial" size="2">
            <? echo $info['title'] ?>
          </td>          
        </tr>        
        <tr>
          <td><font face="arial" size="2">
            <? echo $info['content'] ?>
          </td>          
        </tr>                
        <tr>
          <td><font face="arial" size="2">            
            <a href="<? echo $info['link'] ?>" target="_blank">Details at the Arkansas Code Website</a>
          </td>          
        </tr>                
      </table>
    <? 
  }else{
    echo 'This code section could not be found, please look at the <a href="http://www.arkleg.state.ar.us/data/ar_code.asp">Arkansas Code Website</a>';
  }
  
  }
  

}
?>
