<?
/**
 * Reads INA's deployment config file format for application parameters.
 * @param string $configFile Path to configuration file.
 * @return mixed false or parameters from the config.
 * @package NinjaPHPFramework
 */
/**
 * Read Standard Config File or new properties database
 *
 * If the value USE_GLOBAL_CONFIG exists in the config file, attempts
 * to look up the config for that value in the database
 * Example: USE_GLOBAL_CONFIG = ina-myapp-name
 * @param string $configFile Location of configuration file to parse
 */
function readConf($configFile="common/conf.properties"){

	$params = array() ;

	$lines = @file($configFile, 1) ;

	if (!$lines){
		$lines = @file("controllers/conf.properties", 1) ;
	}
	
	if (!$lines){
		return $params ;
	}
	
	foreach ($lines as $line){
		if (strpos($line, "#") === 0){
			# Ignore commented line.
		}elseif (strpos($line, "=")){
			list($key, $value) = explode('=', $line, 2) ;
			$key = trim($key) ;
			$value = trim($value) ;

			# Make sure boolean values are handled correctly
			if ($value == 'true'){
				$params[$key] = true ;
			}elseif ($value == 'false'){
				$params[$key] = false ;
			}else{
				$params[$key] = $value ;
			}
		}
	}
	
	if (array_key_exists('USE_GLOBAL_CONFIG', $params)){
		if ($params['USE_GLOBAL_CONFIG']){
			require_once 'INA/lib.properties.php' ;
			$props = prop_getAppProperties($params['USE_GLOBAL_CONFIG']) ;
			
			# Make sure boolean values are handled correctly
			foreach($props as $key=>$value){
				if ($value == 'true'){
					$props[$key] = true ;
				}elseif ($value == 'false'){
					$props[$key] = false ;
				}else{
					$props[$key] = $value ;
				}
			}
			return $props ;
		}
	}
	return $params ;
}