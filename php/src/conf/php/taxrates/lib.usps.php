<?
/**
 * USPS Address Library
 * 
 * PHP4-Compatible library that calls a PHP5 psuedo web service
 * that serves as a wrapper for the USPS Address APIs
 *
 */
require_once 'lib.curl_urlfile.php' ;

usps_readConf() ;
/**
 * Verify a U.S. Address.
 */
function usps_verifyAddress($address1='', $address2='', $city='', $state='', $zip=''){
	$params = array('address1', 'address2', 'city', 'state', 'zip') ;
	foreach($params as $p){
		$$p = urlencode($$p) ;
	}
	$url = USPS_SERVICE_URL . "?address1=$address1&address2=$address2&city=$city&state=$state&zip=$zip" ;

	$result = curl_file_get_contents($url) ;
	
	if (preg_match('/Error/', $result)){
		return false ;
	}else{
		return wddx_deserialize($result) ;
	}
}

/**
 * Return Zip and Zip+4 from address
 */
function usps_getZipPlusFour($address1='', $address2='', $city='', $state='', $zip=''){
	$address = usps_verifyAddress($address1, $address2, $city, $state, $zip) ;
	if (!$address){
		return false ;
	}else{
		return array($address['Zip5'], $address['Zip4']) ;
	}
}

function usps_readConf($configFile='taxrates/conf.properties'){

	$params = array() ;

	$lines = @file($configFile, 1) ;

	if (!$lines){
		return $params ;
	}
	
	foreach ($lines as $line){
		if (strpos($line, "#") === 0){
			# Ignore commented line.
		}elseif (strpos($line, "=")){
			list($key, $value) = explode('=', $line) ;
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
	
	foreach($params as $k=>$v){
		@define($k, $v) ;
	}

	return $params ;
}
?>