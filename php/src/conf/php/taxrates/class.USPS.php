<?
/**
 * Base class for dealing with USPS APIs
 */
require_once 'lib.usps.php' ;

class USPS{
	protected $Username 	= '838INFOR6531' ;
	protected $Password 	= '994WN66JJ479' ;
	protected $URL ;
	protected $HTTPCode ;
	protected $LastQuery ;
	protected $Error ;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		usps_readConf() ;
		
		$this->URL = USPS_API_URL ;
	}
	
	/**
	 * Execute the query
	 */
	protected function query($xml){
		$query_url = $this->URL ; 
		
		$vars = "API=" . $this->API . "&XML=$xml";
	
		$this->LastQuery = $query_url . '?' . $vars ;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $query_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false) ;
		curl_setopt($ch, CURLOPT_USERAGENT, 'Arkansas.Gov');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars) ;
		
		$resultXML = curl_exec($ch);
		$this->HTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) ;
		
		if (curl_errno($ch)) {
			$this->Error = curl_error($ch) ;
			return false ;
		}elseif($this->HTTPCode != 200){
			$this->Error = "Got HTTP Status Code {$this->HTTPCode}" ;
		}else{
			return $resultXML ;
		}
	}
}

/**
 * Interface to USPS API for address verification
 */
class USPS_Address extends USPS{
	protected $API = 'Verify' ;
	protected $Address1 ;
	protected $Address2 ;
	protected $City ;
	protected $State ;
	protected $Zip5 ;
	protected $Zip4 ;
	
	/**
	 * Verify the address
	 */
	public function verify($address1='', $address2='', $city='', $state='', $zip='', $zip_plus_four=''){
		$this->Address1 = $address1 ;
		$this->Address2 = $address2 ;
		$this->City = $city ;
		$this->State = $state ;
		$this->Zip5 = $zip ;
		$this->Zip4 = $zip_plus_four ;
		
		$requestXML = $this->buildQuery() ;
		
		$responseXML = $this->query($requestXML) ;

		if (!$responseXML){
			return new USPS_Address_Response(&$this) ;
		}

		return new USPS_Address_Response($responseXML) ;
	}
	
	/**
	 * Insert the query params into the xml required by the API
	 */
	private function buildQuery(){
		$requestXML = "<AddressValidateRequest%20USERID=\"{$this->Username}\"><Address ID=\"0\"><Address1>{$this->Address1}</Address1><Address2>{$this->Address2}</Address2><City>{$this->City}</City><State>{$this->State}</State><Zip5>{$this->Zip5}</Zip5><Zip4>{$this->Zip4}</Zip4></Address></AddressValidateRequest>" ;
		return $requestXML ;
	}
}

/**
 * Address Response Object
 *
 * Parses the XML result and returns an object containing the verified address, or an error message
 */
class USPS_Address_Response{
	public $Address1 ;
	public $Address2 ;
	public $City ;
	public $State ;
	public $Zip5 ;
	public $Zip4 ;
	public $Error = false ;
	
	public function __construct($responseXML){
	
		if (is_object($responseXML)){;
			$this->Error = $responseXML->Error ;
			return $this ;
		}
		
		$xml = new SimpleXMLElement($responseXML) ;

		if ($xml->Address->Error){
			$this->Error = (string) $xml->Address->Error->Description ;
			return $this ;
		}else{
			$this->Address1 = (string) $xml->Address->Address1;
			$this->Address2 = (string) $xml->Address->Address2 ;
			$this->City		= (string) $xml->Address->City ;
			$this->State	= (string) $xml->Address->State ;
			$this->Zip5		= (string) $xml->Address->Zip5 ;
			$this->Zip4		= (string) $xml->Address->Zip4 ;
			unset($this->Error) ;
			return $this ;
		}
	}
}
?>