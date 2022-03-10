<?

function getPreference($file, $default){
  if(! file_exists($file) ){
    setPreference($file, $default);
    return $default;
  }else{
    $fd = fopen($file, "r");
    $var = '';
    while (!feof ($fd)) {
        $var .= fgets($fd, 4096);
    }
    fclose($fd);
    return unserialize($var);
  }
}

function setPreference($file, $set){
  $fd = fopen($file, "w");
  
  if(!$fd){
    echo "could not open preference file for writing\n";
    return;
  }
  
  fputs($fd, serialize($set));
  
  fclose($fd);
  
  
}

?>     
