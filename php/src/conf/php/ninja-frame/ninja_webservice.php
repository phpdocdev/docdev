<?
$script_name = basename($_SERVER['PHP_SELF']) ;
if (isset($_SERVER['REQUEST_METHOD']) &&
    $_SERVER['REQUEST_METHOD']=='POST') {
	ini_set('soap.wsdl_cache_enabled', 0);
	if (!isset($HTTP_RAW_POST_DATA)){
		$HTTP_RAW_POST_DATA = file_get_contents('php://input');
	}
	$soap = new SoapServer(
		$ws_url . '?wsdl', 
		array('uri' => str_replace($script_name, '', $ws_url))
	);
	$soap->setClass($service_class);
	$soap->handle();
	exit;
} elseif( isset($_REQUEST['wsdl']) || isset($_REQUEST['WSDL']) ) {
	require_once("wsdlgen/classes/WsdlDefinition.php");
	require_once("wsdlgen/classes/WsdlWriter.php");
	$def = new WsdlDefinition() ;
	$def->setDefinitionName($service_class) ;
	$def->setClassFileName($service_class_file) ;
	$def->setNameSpace($namespace) ;
	$def->setEndPoint($ws_url) ;
	$wsdl = new WsdlWriter($def) ;
	header('Content-type: text/xml');
	print $wsdl->classToWsdl();
} elseif( isset($_REQUEST['wstest']) ) {
	if (defined('APP_MODE') && (APP_MODE == 'DEV' || APP_MODE == 'TEST')){
		try{
			echo '<pre>';
			ini_set('soap.wsdl_cache_enabled', 0) ;
			$client = new SoapClient($ws_url . '?wsdl', array('trace'=>1));
			
			$funcs = $client->__getFunctions();
			echo "FUNCTION LIST:\n" ;
			foreach($funcs as $f){
				print $f . "\n\n" ;
			}
			echo '<hr>' ;
	
			$rand = rand(10,20) ;
			echo "Testing call to 'Combine(40, $rand)'...\n" ;
			$r = $client->Combine(40,$rand);
			echo "RESULT:\n" ;
			var_dump($r);
			
			//print '<hr>';
			//$types = $client->__getTypes();
			//print_r($types);
	
			if($client){
				echo '<hr>';
				echo "REQUEST:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
				echo '<hr>';
				echo "RESPONSE:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
			}
			
			echo '</pre>' ;
			
		}catch (Exception $exception) {
			echo $exception;
			echo '<hr></pre>';
			if($client){
				echo "REQUEST:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
				echo '<hr>';
				echo "RESPONSE:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
			}
		}
	}
}else{
	echo 'This is a web service. <a href="?wsdl">Click here for WSDL.</a>' ;
	exit;
}
