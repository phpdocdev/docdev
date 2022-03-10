<?

function prop_getFileAndDBConfig($app_id, $filename='shared/conf.properties', $define = true){
	$file_opts = prop_getFileConfiguration($filename);
	$db_opts = prop_getAppProperties($app_id);
	
	$Opts = array();
	foreach($file_opts as $k => $v){
		$Opts[$k] = $v;
	}
	foreach($db_opts as $k => $v){
		$Opts[$k] = $v;	
	}

	if($define){
		foreach($Opts as $k => $v){
			define($k, $v);
		}
	}
	
	return $Opts;
}

function prop_getFileConfiguration($filename='shared/conf.properties'){
	$Opts = array();
	$ConfFile = file($filename, 1);
	foreach($ConfFile as $line){
		if(!$line){ continue; }
		$line = trim($line);
		list($key, $value) = split('=', $line,2);
		
		$key = trim($key);
		$value = trim($value);
		if(!$key){ continue; }
		$Opts[$key] = $value;
	}	
	return $Opts;
}


function prop_getAppProperties($app_id){
	require_once('DB.php');		
	$DB = DB::connect('mysql://app_prop_fetch:bgb4PSDC@testdb/app_properties', false);
	if( DB::isError($DB) ){
		echo $DB->getMessage() . ' ' . $DB->getDebugInfo();
	}
	$DB->setFetchMode(DB_FETCHMODE_ASSOC);


	$sql = "
		select	
			properties.app_id,
			properties.name,
			properties.property as default_property,
			properties_bydate.property as date_property
		from
			properties
			left join properties as properties_bydate on 
				properties_bydate.app_id = properties.app_id and
				properties_bydate.name = properties.name and
				(
					NOW() between properties_bydate.rule_begin and properties_bydate.rule_end OR
					(properties_bydate.rule_begin != '0000-00-00 00:00:00' and NOW() > properties_bydate.rule_begin and properties_bydate.rule_end = '0000-00-00 00:00:00') OR
					(properties_bydate.rule_end != '0000-00-0 00:00:00' and properties_bydate.rule_begin = '0000-00-00 00:00:00' and NOW() < properties_bydate.rule_end)
				)
		where
			properties.app_id in( '".$app_id."', 'global') and
			properties.rule_begin = '0000-00-00 00:00:00' and
			properties.rule_end = '0000-00-00 00:00:00'
		order by
			IF( properties.app_id = 'global', 1, 2),
			properties.name";
	$props = $DB->getAll($sql);
	
	$Opts = array();
	foreach($props as $p ){
		$Opts[ $p['name'] ] = $p['date_property'] ? $p['date_property'] : $p['default_property'];
	}

	return $Opts;
}


?>
