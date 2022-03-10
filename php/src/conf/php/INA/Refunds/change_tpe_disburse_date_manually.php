<?

readConfigurationFile('conf.properties');
		
// setup databases
require('DB.php');		
$DB = DB::connect(DB_DSN, false);
if( DB::isError($DB) ){
	echo $DB->getMessage() . ' ' . $DB->getDebugInfo();
}
$DB->setFetchMode(DB_FETCHMODE_ASSOC);

$DB_Slave = DB::connect(DB_SLAVE_DSN, false);
if( DB::isError($DB_Slave) ){
	echo $DB_Slave->getMessage() . ' ' . $DB_Slave->getDebugInfo();
}
$DB_Slave->setFetchMode(DB_FETCHMODE_ASSOC);

$arr_orders = array(

'20130222031754256',
'20130222094546805',
'20130222104726354',
'20130222081733856',
'20130222031132297',
'20130222100338215',
'20130222074103133',
'20130222083801837',
'20130222093822394',
'20130222015323914',
'20130222110353905',
'20130222112643472',
'20130222020602334',
'20130222094738777',
'20130222053918888',
'20130222032003186',
'20130222045056281',
'20130222090742330',
'20130222021313731',
'20130222123222626',
'20130222030948634',
'20130222104142543',
'20130222011117875',
'20130222034248262',
'20130222035319960',
'20130222023725855',
'20130222051009286',
'20130222043152634',
'20130222050322401',
'20130222034138700',
'20130222113422395',
'20130222123425823',
'20130222124223916',
'20130222120635925',
'20130222034628482',
'20130222103435641',
'20130222011926471',
'20130222043509777',
'20130222114747639',
'20130222012125988',
'20130222010203577',
'20130222050937163',
'20130222054210587',
'20130222053956564',
'20130222044228882',
'20130222055949927',
'20130222031506648',
'20130222035001341',
'20130222101803344',
'20130222015559386',
'20130222095428236',
'20130222025847687',
'20130222035246169',
'20130222115503106',
'20130222123537534',
'20130222042204887',
'20130222043102848',
'20130222115510956',
'20130222033222603',
'20130222035755511',
'20130222121710720',
'20130222024732999',
'20130222042622481',
'20130222043911224',
'20130222095604943',
'20130222014153531',
'20130222124826597',
'20130222012612165',
'20130222110431507',
'20130222013303870',
'20130222014531402',
'20130222010616765',
'20130222085459459',
'20130222074017177',
'20130222082927335',
'20130222072759744',
'20130222084404296',
'20130222080651834',
'20130222015806692',
'20130222100231174',
'20130222111934169',
'20130222033719740',
'20130222090037357',
'20130222094712644',
'20130222125448320',
'20130222024320430',
'20130222085739407',
'20130222100402901',
'20130222111218952',
'20130222032424913',
'20130222035028359'
);

foreach($arr_orders as $pmt_orderid){

	$orderid = sprintf('%s', $pmt_orderid);

	$URL = TPE2_DISBURSE_WS_URL . "?secure_key=" . KEY . "&action=CHANGE_DISBURSE_DATE&orderid=" . $orderid;
		
	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$URL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 18);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = curl_exec($ch);
		
	list($success, $message) = json_decode($data);
	if($success){
		$success = "Completed";
	}else{
		$success = "Failed";
	}
	echo "Changing Disbursement date for $orderid: ( $success , $message )\n";
			
}


function readConfigurationFile($filename){
	$ConfFile = file($filename, 1);
	foreach($ConfFile as $line){
		if(!$line){ continue; }
		$line = trim($line);
		list($key, $value) = split('=', $line,2);
		
		$key = trim($key);
		$value = trim($value);
		if(!$key){ continue; }
		
		define($key, $value);
	}
}

?>
