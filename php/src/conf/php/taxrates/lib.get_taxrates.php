<?
taxrates_readConf() ;

/*
regexes:
/^\d{5}-?\d{4}$/				Non-capturing Zip5 or Zip+4 (hyphen optional)
/^\d{4}$/						4 digits
/^\d{5}$/						5 digits
*/

/**
 * Look this zip+4 up in lk_fips to make sure it is valid.
 */
function taxrates_verify_zip($zip = null){
	if (!$zip || !preg_match('/^\d{5}-?\d{4}$/', $zip)){
		return false ;
	}
	$zip = str_replace('-', '', $zip) ;
	$db = taxrates_init_db() ;
	$zip = $db->quote($zip) ;

	$sql = "select zip_plus_four from lk_fips where zip_plus_four = $zip limit 1" ;
	return $db->queryOne($sql) ? true : false ;
}

/**
 * Provides taxrate look up service.
 *
 * @param string $zip 5 or 9 digit zipcode.  Hyphen in zip+4 is optional.
 * @param string $format Return format.  Valid values: delim, wddx
 * @return string Returns either pipe-delimited list of rates state|county|city -
 * OR an assocative array or rates serialized with wddx
 *
 */
function get_taxrate_by_zip($zip=null){
	$db = taxrates_init_db() ;
	if (!$zip){
		return array(false, 'Error: No Zip') ;
	}

	$zip = preg_replace('/[^\d*]/', '', $zip) ;

	$zip_plus_four = $zip ;
	$zip = substr($zip, 0, 5) ;
	$plus_four = substr($zip_plus_four, -4) ;

	$select = "select state_fips, county_fips, city_fips from lk_fips" ;
	$where =  "where zip = '$zip'" ;
	$where .= " and plus_four = '$plus_four'" ;
	$where_city = " and city_fips <> ''" ;
	$limit = 'limit 20' ;
	
	if (strlen($zip_plus_four) != 9){
		return array(false, 'Error: Please use a 9 digit Zip') ;
	}

	$sql = join(' ', array($select, $where, $where_city, $limit)) ;


	$res = $db->queryRow($sql) ;
	
	if (!$res){
		// try the same query again, but without the filter for city fips
		$sql = 	$sql = join(' ', array($select, $where, $limit)) ;
		$res = $db->queryRow($sql) ;
	}
	
	if (!$res){
		return array(false, 'Error: Zip not found') ;
	}else{
		// Look up the tax rates
		$today = $db->quote(date('Y-m-d')) ;

		$date_query = "(($today between from_date and to_date) or ($today >= from_date and to_date = '0000-00-00'))" ;
		
		$rate_sql = "select rate, location from taxrates where fips = " ;
		
		
		$state_rate = 0 ;
		if ($res['state_fips']){
			$state_sql = "select rate, location from taxrates where number = '0' and $date_query limit 1" ;
			list($state_rate, $state_location) = @array_values($db->queryRow($state_sql)) ; ;

			if (!$state_rate){
				$state_rate = 0.00 ;
			}
		}

		$county_rate = 0 ;
		if ($res['county_fips']){
			$county_sql = $rate_sql . $db->quote($res['county_fips']) . " and type='C' and $date_query limit 1" ;
			list($county_rate, $county_location) = @array_values($db->queryRow($county_sql)) ;
			if (!$county_rate){
				$county_rate = 0.00 ;
			}
		}

		$city_rate = 0 ;
		if ($res['city_fips']){
			$city_sql = $rate_sql . $db->quote($res['city_fips']) . " and type='T' and $date_query limit 1" ;
			list($city_rate, $city_location) = @array_values($db->queryRow($city_sql)) ;
			if (!$city_rate){
				$city_rate = 0.00 ;
			}
		}
		$rates = array('state'=>$state_rate, 'county'=>$county_rate, 'city'=>$city_rate, 'total'=>$state_rate+$county_rate+$city_rate) ;
		$codes = array('zip_plus_four'=>$zip_plus_four, 'state_fips'=>$res['state_fips'], 'county_fips'=>$res['county_fips'], 'city_fips'=>$res['city_fips']) ;
		$locations = array('state_name'=>$state_location, 'county_name'=>$county_location, 'city_name'=>$city_location) ;
		return array(true, $rates, $codes, $locations) ;
	}
}

function taxrates_init_db(){
	$dsn = 'mysql://' . TAXRATES_DBUSER . ':' . TAXRATES_DBPASS . '@' . TAXRATES_DBHOST . '/ina_dfa_salestax' ;
	require_once 'MDB2.php' ;
	$db =& MDB2::factory($dsn, array('persistent'=>false)) ;
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC) ;
	$db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE) ;
	if (PEAR::isError($db)){
		return array(false, 'Error: Cannot connect to database') ;
	}else{
		return $db ;
	}

}

function taxrates_readConf($configFile='taxrates/conf.properties'){

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
