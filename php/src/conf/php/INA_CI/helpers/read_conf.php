<?php

function read_conf($configFile) {
    
    $lines = file($configFile, 1) or die( "Can't read config file" ) ;
    
    foreach ($lines as $line){
        if (strpos($line, "#") === 0){
            # Ignore commented line.
        }elseif (strpos($line, "=")){
            list($key, $value) = explode('=', $line) ;
            $key = trim($key);
            $value = trim($value) ;
            
            $opts[$key] = $value ;
            //echo "KEY: $key = $value <BR>" ;
        }
    }
    return $opts;
}

?>