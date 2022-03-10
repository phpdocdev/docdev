<? 
/* 
CyberClass - PHP CyberCash Interface (C) C.E Publications 

Author: Nathan Cassano <nathan@cjhunter.com> 

Version: 0.4 

Description: CyberClass is an interface to the CyberCash Cash Register 
Service for online financial transactions (such as credit card processing). This is an 
adaptation to the origional CyberLib. The CyberClass API is mostly a duplicatation of 
the original CyberCash API.  

Requirements: A CyberCash Merchant Account, PHP with compiled with cybercash support (ie ./configure --with-mck=/mck) 

Example: 

$test = new cyberclass("/etc/merchant_conf"); 

$response = $test->SendCC2_1Server('mauthonly', 
    array('Order-ID' => '11223344', 
    'Amount' => 'usd 11.50', 
    'Card-Number' => '4111111111111111', 
    'Card-Address' => '1600 Pennsylvania Avenue', 
    'Card-City' => 'Washington', 
    'Card-State' => 'DC', 
    'Card-Zip' => '20500', 
    'Card-Country' => 'USA', 
    'Card-Exp' => '12/05', 
    'Card-Name' => 'Bill Clinton' 
)); 

while(list($key, $val) = each($response)){ 
    echo "$key = $val<br>\n"; 
} 

*/ 
  
class cyberclass 
{ 
    /* Merchant Configuration array */ 
    var $config; 

    /* Merchant identification */ 
    var $merchant_id; 
     
    /* Private Encryption key */ 
    var $merchant_key; 

    /* CyberCash Payment Service (CCPS) Host Address */ 
    var $ccps_host; 
    var $host; 
    var $base_path; 
    var $port = 80; 
         

    /* 
     * Class Constructor 
     */ 
    function cyberclass($merchant_conf) 
    { 

        /* Read merchant_conf into array */ 
        $fp = fopen($merchant_conf, 'r'); 

        if(!$fp){ 
            die("Unable to open merchant configuration"); 
        } 
                     
        while(!feof($fp)){ 
         
            $line = fgets($fp, 80); 
             
            if(!ereg("^#", $line)){ 
                if(ereg('=', $line)){ 

                    list($key, $value) = split("=", $line, 2); 
                    $key = trim($key); 
                    $value = trim($value); 
                    $this->config[$key] = $value; 
                } 
            } 
        } 

        /* Set class members or die*/ 
        if(!$this->ccps_host = $this->config['CCPS_HOST']){ 
            die("Unable to initialize 'CCPS_HOST' from configuration");} 
        if(!$this->merchant_id = $this->config['CYBERCASH_ID']){ 
            die("Unable to initialize 'CYBERCASH_ID' from configuration");} 
        if(!$this->merchant_key = $this->config['MERCHANT_KEY']){ 
            die("Unable to initialize 'MERCHANT_KEY' from configuration");} 

        $parsed_url = parse_url($this->ccps_host); 
        $this->host = $parsed_url['host']; 
        $this->base_path = $parsed_url['path']; 
         
    } 
     
    /*function SendCC2_1Server($operation, $args) 
     * Desc: Wrapper Interface to the Direct Cash Register 2.1 API 
     * Input: 
     *    $operation - the 2.1 Cash Register operation 
     *    $args - attribute/value pairs that make up the argument list for that operation. 
     * Returns:  
     *    ['MStatus'] - the outcome of the tranaction 
     *    ['*'] - and many other variable pairs 
     */ 
    function SendCC2_1Server($operation, $args) 
    { 
        /* We need to make the url. */ 
        $cgi = 'cr21api.cgi/' . $operation; 
         
        return $this->SendCCServer($cgi, $args);  
    } 
     
    /*function SendCCServer($cgi, $args) 
     * Desc: Common Direct Interface to Cyber Cash Server 
     * Input: 
     *    $cgi - the cgi filename to which to request is being sent 
     *    $args - the attribute/value pairs that make up the argument list for that operation. 
     * Returns:  
     *    ['MStatus'] - the outcome of the tranaction 
     *    ['*'] - and many other variable pairs 
     */ 
    function SendCCServer($cgi, $args) 
    { 
        /* Make url encoded cgi arguments from $args */ 
        $message = $this->urls_encode($args); 
         
        /* Encrypt the message */ 
        $encrypted_message = $this->CCEncrypt($message); 

        return $this->CCSocketSend($cgi, $encrypted_message); 
    } 
     
    /*function CCEncrypt($message) 
     * Desc: Encrypts a HTTP url-encoded message 
     */ 
    function CCEncrypt($message) 
    { 
        $session_key = sprintf("%s #%ld", date("D M j H:i:s Y"), getmypid()); 
        $enc_msg = cybercash_encr($this->merchant_key, $session_key, $message); 
        $message = cybercash_base64_encode($enc_msg['outbuff']); 
        $mac = cybercash_base64_encode($enc_msg['macbuff']); 
         
        /* This adds the information needed to decrypt. */ 
        $encrypted_message = 'message=' . urlencode($message) . '&'; 
        $encrypted_message .= 'session-key=' . urlencode($session_key) . '&'; 
        $encrypted_message .= 'mac=' . urlencode($mac); 

        return $encrypted_message; 
    } 

    /*function CCSocketSend($cgi, $message) 
     * Desc: Sends a raw HTTP request and returns the decoded message 
     */     
    function CCSocketSend($cgi, $message) 
    { 

        /* Send message */ 
        $fp = fopen("http://$this->host:$this->port$this->base_path$cgi/$this->merchant_id?$message", 'r'); 

        if($fp == false){ 
            $response_vars['MStatus'] = 'failure-hard'; 
            $response_vars['MErrMsg'] = 'HTTP request failed'; 
            return $response_vars;             
        } 

        /* and get the response */ 
        while(!feof($fp)){ 
            $response .= fgets($fp, 256); 
        } 

        fclose($fp); 

        /* Decode response */ 
        $response_vars = $this->urls_decode($response); 
        $response_vars['message'] = cybercash_base64_decode($response_vars['message']); 
        $response_vars['mac'] = cybercash_base64_decode($response_vars['mac']); 

        /* Decrypt response */                         
        $deccrypted_response_vars = cybercash_decr($this->merchant_key, $response_vars['session-key'], $response_vars['message']); 

        /* Catch decryption errors */ 
        if($deccrypted_response_vars['errcode']){ 
            $response_vars['MStatus'] = 'failure-hard'; 
            $response_vars['MErrMsg'] = 'Response non-decodable.'; 
            $response_vars['MErrCode'] = $deccrypted_response_vars['errcode'];     
            return $response_vars; 
        } 
         
        /* Verify signitures match */ 
        if($deccrypted_response_vars['macbuff'] !=  $response_vars['mac']){ 
            $response_vars['MStatus'] = 'failure-hard'; 
            $response_vars['MErrMsg'] = 'Signitures do not match.'; 
            return $response_vars; 
        } 
         
        /* Parse again to get message */ 
        return $this->urls_decode($deccrypted_response_vars['outbuff']); 

    } 


    /*function urls_encode($args) 
     * Desc: Creates url-encoded form message from array of attribute/value pairs 
     */ 
    function urls_encode($args) 
    { 
        /* Make sure we're dealing with an array here */ 
        if(is_array($args)){ 

            /* Turn the name and value pairs form $args into a url-encoded message. */ 
            list($key, $val) = each($args);         
            do{ 
                if($more){ 
                    $message .= '&'; } 
     
                $message .= chop($key) . '=' . urlencode(chop($val));             
                list($key, $val) = $more = each($args);         
     
            }while($more); 
        } 
         
        return $message; 
    } 

    /*function urls_decode($message) 
     * Desc: Separates url-encoded form message into array of attribute/value pairs 
     */ 
    function urls_decode($message) 
    { 
     
        $pairs = explode("&", $message); 
         
        while($pair = current($pairs)){ 
         
            list($var, $val) = explode("=", $pair); 
             
            $args[$var] = urldecode(chop($val)); 
             
            next($pairs); 
        } 
         
        return $args; 
    } 

} 


?> 