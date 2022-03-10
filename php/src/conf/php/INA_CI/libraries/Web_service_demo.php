<?php

class Web_service_demo {
	protected $client;
	protected $ci_library;
	
	function __construct($p=array('client', 'ci_library')){
		$this->client		= $p['client'];
		$this->ci_library	= $p['ci_library'];
	}
	
	function display_homepage($p=array('wsdl', 'demo_html')){
		$content .= "<style>body { margin: 0; padding: 0; } #wrapper { padding: 1em; } h1 { color: #ffffff; font-family: Tahoma; font-size: 26px; font-weight: normal; background-color: #003366; margin-top: 0px; margin-bottom: 0px; padding-top: 10px; padding-bottom: 3px; padding-left: 15px; }</style>";
		$content .= "<h1>{$p['name']}</h2>";
		$content .= "<div id=\"wrapper\">";
		$content .= sprintf("<p>The following operations are supported. For a formal definition, please review the %s.</p>", anchor($p['wsdl'], 'Service Description'));
		
		if ($functions = $this->client->__getFunctions()) {
			$content .= "<ul>";
			foreach ($functions as $function)
				$content .= "<li>{$function}</li>";
			$content .= "</ul>";
		}
		
		if ($p['demo_html']) {
			$content .= "<p>The following are demo functions for this web service.";
			$content .= $p['demo_html'];
		}	
		
		if ($types = $this->client->__getTypes()) {
			$content .= "<p>The following are return types defined by this web service.</p>";
		
			$content .= "<ul>";
			foreach ($types as $type) {
				$content .= "<li>";
				$content .= "<pre>{$type}</pre>";
				$content .= "</li>";
			}
			$content .= "</ul>";
		}
		
		$content .= "</div>";
		
		return $content;
	}
	
	/**
	 * Call soap method
	 */
	function call_soap($p = array(), $method, $output_type){
		try {
		    
		    if ($p)
		    	$_ = $this->client->__soapCall($method, $p);
		    else		    
		    	$_ = $this->client->__call($method, array());
		    
		    if ($output_type == 'html') {
			    echo "Sample response data for {$method} method:";
			    
			    echo "<pre>";
			    print_r($_);
			    echo "</pre>";
		    }
		    elseif ($output_type == 'xml') {
			    header("Content-type: text/xml");		    
			    echo $this->client->__getLastResponse();
		    }
		}
		catch ( SoapFault $e ){
		    echo "Soap Fault: " . $e->faultstring;
		};		   
	}
	
	/**
	 * Call as Code Igniter
	 */
	function call_ci($p = array(), $func){
		$result = call_user_method_array($func, $this->ci_library, $p);

		echo "<pre>";		
		print_r($result);
		echo "</pre>";
	}
}