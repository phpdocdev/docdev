<?php
//This software is based on XML-RPC for PHP

//originally by Edd Dumbill (C) 1999-2000

// Modified by Victor Zou (C) 2000-2001
//<victor@gigaideas.com.cn>

// License is granted to use or modify this software ("SOAP for PHP")
// for commercial or non-commercial use provided the copyright of the author
// is preserved in any distributed or derivative work.

// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESSED OR
// IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
// OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
// NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
// THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
// THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

$soapTargetId="";
$soapTargetMethod="";

if (!function_exists('xml_parser_create')) {
// Win 32 fix. From: "Leo West" <lwest@imaginet.fr>
	if($WINDIR) {
		dl("php3_xml.dll");
	} else {
		dl("xml.so");
	}
}

$soapI4="i4";
$soapInt="int";
$soapBoolean="boolean";
$soapDouble="double";
$soapString="string";
$soapDateTime="dateTime.iso8601";
$soapBase64="base64";
$soapArray="array";
$soapStruct="struct";


$soapTypes=array( $soapI4 => 1,
				  $soapInt => 1,
				  $soapBoolean => 1,
				  $soapString => 1,
				  $soapDouble => 1,
				  $soapDateTime => 1,
				  $soapBase64 => 1,
				  $soapArray => 2,
				  $soapStruct => 3);

$xmlEntities=array("quot" => '"',
				   "amp" => "&",
				   "lt" => "<",
				   "gt" => ">",
				   "apos" => "'");

$soaperr["unknown_method"]=1;
$soapstr["unknown_method"]="Unknown method";
$soaperr["invalid_return"]=2;
$soapstr["invalid_return"]="Invalid return payload: enabling debugging to examine incoming payload";
$soaperr["incorrect_params"]=3;
$soapstr["incorrect_params"]="Incorrect parameters passed to method";
$soaperr["introspect_unknown"]=4;
$soapstr["introspect_unknown"]="Can't introspect: method unknown";
$soaperr["http_error"]=5;
$soapstr["http_error"]="Didn't receive 200 OK from remote server.";

$soap_defencoding="UTF-8";

// let user errors start at 800
$soaperruser=800; 
// let XML parse errors start at 100
$soaperrxml=100;

// formulate backslashes for escaping regexp
$soap_backslash=chr(92).chr(92);

$soap_twoslash=$soap_backslash . $soap_backslash;
$soap_twoslash="2SLS";

// used to store state during parsing
// quick explanation of components:
//   st - used to build up a string for evaluation
//   ac - used to accumulate values
//   qt - used to decide if quotes are needed for evaluation
//   cm - used to denote struct or array (comma needed)
//   isf - used to indicate a fault
//   lv - used to indicate "looking for a value": implements
//        the logic to allow values with no types to be strings
//   params - used to store parameters in method calls
//   method - used to store method name

$_xh=array();

function soap_se($parser, $name, $attrs) {
	global $_xh, $soapDateTime, $soapString;
	
	if(ereg("^[n][s][1-9][:]",$name)) {
	while(list($key,$value)=each($attrs)) 
		if(ereg("^xmlns:ns",$key)) $_xh[$parser]['tid']=$value;
	}
	
	while(list($key,$value)=each($attrs)) {
		if(ereg("xsi:type",$key))  $name_type=substr(strrchr($value,":"),1);
	}
	
	//echo "this is type of val:".$name_type;
	if(ereg("return",$name))
		$_xh[$parser]['rt']=$name_type;
	if(ereg("params",$name)) {
		$name="parameter";	
		$_xh[$parser]['pt']=$name_type;
	}
	switch($name) {	
	case "item"  : 
	    //$_xh[$parser]['flag']++;
		// this last line turns quoting off
		// this means if we get an empty array we'll 
		// simply get a bit of whitespace in the eval
	    $_xh[$parser]['qt']=0;
	    $_xh[$parser]['ac']="";
		break;
	case "STRUCT":
	case "ARRAY":
	  $_xh[$parser]['st'].="array(";
	  $_xh[$parser]['cm']++;
		// this last line turns quoting off
		// this means if we get an empty array we'll 
		// simply get a bit of whitespace in the eval
	  $_xh[$parser]['qt']=0;
	  break;
	case "NAME":
	  $_xh[$parser]['st'].="'"; $_xh[$parser]['ac']="";
	  break;
	case "FAULT":
	  $_xh[$parser]['isf']=1;
	  break;
	case "PARAM":
	  $_xh[$parser]['st']="";
	  break;
	case "parameter":
	  $_xh[$parser]['st']="";
	  $_xh[$parser]['st'].="new soapval("; 
	  if(($_xh[$parser]['pt']=='Array')||($_xh[$parser]['pt']=='Vector')||($_xh[$parser]['pt']=='Map'))
	  	$_xh[$parser]['st'].="array(";
	  $_xh[$parser]['lv']=1;
	  $_xh[$parser]['vt']=$soapString;
	  //$_xh[$parser]['vt']='int';
	  //echo "*****".$soapString."*****";
	  $_xh[$parser]['ac']="";
	  // look for a value: if this is still 1 by the
	  // time we reach the first data segment then the type is string
	  // by implication and we need to add in a quote
		break;
	case "return":
	  $_xh[$parser]['st'].="new soapval("; 
	  if(($_xh[$parser]['rt']=='Array')||($_xh[$parser]['rt']=='Vector')||($_xh[$parser]['rt']=='Map'))
	  	$_xh[$parser]['st'].="array(";
	  $_xh[$parser]['lv']=1;
	  $_xh[$parser]['vt']=$soapString;
	  $_xh[$parser]['ac']="";
	  // look for a value: if this is still 1 by the
	  // time we reach the first data segment then the type is string
	  // by implication and we need to add in a quote
		break;
	case "I4":
	case "INT":
	case "STRING":
	case "BOOLEAN":
	case "DOUBLE":
	case "DATETIME.ISO8601":
	case "BASE64":
	  $_xh[$parser]['ac']=""; // reset the accumulator

	  if ($name=="DATETIME.ISO8601" || $name=="STRING") {
			$_xh[$parser]['qt']=1; 
			if ($name=="DATETIME.ISO8601")
				$_xh[$parser]['vt']=$soapDateTime;
	  } else if ($name=="BASE64") {
			$_xh[$parser]['qt']=2;
		} else {
			$_xh[$parser]['qt']=0;
	  }
		break;
	case "MEMBER":
		$_xh[$parser]['ac']="";
	case "key":
		$_xh[$parser]['qt']=0;
		$_xh[$parser]['ac']="";
	  break;
	case "value":
		$_xh[$parser]['qt']=0;
		$_xh[$parser]['ac']="";
	  break;
	default:
		break;
	}

	if ($name!="VALUE") $_xh[$parser]['lv']=0;
}

function soap_ee($parser, $name) {
	global $_xh,$soapTypes,$soapString;
	
	if(ereg("^[n][s][1-9][:]",$name)) {
		$methName=substr(strrchr($name,":"),1);
		$name="ismethod";
	}
	if(ereg("params",$name))
		$name="parameter";	
	
	switch($name) {
	case "item"  : 
		if($_xh[$parser]['rt']=='Map')
			$_xh[$parser]['st'].= ",";
		else
			$_xh[$parser]['st'].= utf8_decode($_xh[$parser]['ac']).",";
	    $_xh[$parser]['vt']=strtolower($name);
	    $_xh[$parser]['ac']="";
		break;
	case "key"  : 
		$_xh[$parser]['st'].= "\"".utf8_decode($_xh[$parser]['ac'])."\""."=>";
	    $_xh[$parser]['vt']=strtolower($name);
	    $_xh[$parser]['ac']="";
		break;
	case "value"  : 
		$_xh[$parser]['st'].="\"".utf8_decode($_xh[$parser]['ac'])."\"";
	    $_xh[$parser]['vt']=strtolower($name);
	    $_xh[$parser]['ac']="";
		break;
	case "STRUCT":
	case "ARRAY":
	  if ($_xh[$parser]['cm'] && substr($_xh[$parser]['st'], -1) ==',') {
		$_xh[$parser]['st']=substr($_xh[$parser]['st'],0,-1);
	  }
	  $_xh[$parser]['st'].=")";	
	  $_xh[$parser]['vt']=strtolower($name);
	  $_xh[$parser]['cm']--;
	  break;
	case "NAME":
	  $_xh[$parser]['st'].= $_xh[$parser]['ac'] . "' => ";
	  break;
	case "BOOLEAN":
		// special case here: we translate boolean 1 or 0 into PHP
		// constants true or false
		if ($_xh[$parser]['ac']=='1') 
			$_xh[$parser]['ac']="true";
		else 
			$_xh[$parser]['ac']="false";
		$_xh[$parser]['vt']=strtolower($name);
		// Drop through intentionally.
	case "I4":
	case "INT":
	case "STRING":
	case "DOUBLE":
	case "DATETIME.ISO8601":
	case "BASE64":
	  if ($_xh[$parser]['qt']==1) {
			// we use double quotes rather than single so backslashification works OK
			$_xh[$parser]['st'].="\"". $_xh[$parser]['ac'] . "\""; 
		} else if ($_xh[$parser]['qt']==2) {
			$_xh[$parser]['st'].="base64_decode('". $_xh[$parser]['ac'] . "')"; 
		} else {
			$_xh[$parser]['st'].=$_xh[$parser]['ac'];
		}
		$_xh[$parser]['ac']=""; $_xh[$parser]['qt']=0;
	  break;
	case "parameter":
		//echo $_xh[$parser]['vt']."ngleng;lewmk";
		//$_xh[$parser]['vt']=$_xh[$parser]['pt'];
		
		if(($_xh[$parser]['pt']=='Array')||($_xh[$parser]['pt']=='Vector')||($_xh[$parser]['pt']=='Map')) {
			$_xh[$parser]['st']=substr($_xh[$parser]['st'],0,-1);
			$_xh[$parser]['st'].=")"; 
		}
		if(($_xh[$parser]['pt']=='Array')||($_xh[$parser]['pt']=='Vector'))
			$_xh[$parser]['vt']='array';
		if($_xh[$parser]['pt']=='Map')
			$_xh[$parser]['vt']='struct';
		if($_xh[$parser]['pt']=='string')
			$_xh[$parser]['vt']='string';
		if($_xh[$parser]['pt']=='int')
			$_xh[$parser]['vt']='int';
		if($_xh[$parser]['pt']=='double')
			$_xh[$parser]['vt']='double';
		// deal with a string value
		//if (strlen($_xh[$parser]['ac'])>0 && //$_xh[$parser]['vt']==$soapInt) {
		//	$_xh[$parser]['vt']==$soapInt) {
		if(($_xh[$parser]['pt']=='Array')||($_xh[$parser]['pt']=='Vector')||($_xh[$parser]['pt']=='Map')) 	
			//$_xh[$parser]['st'].=$_xh[$parser]['ac'];
			 $_xh[$parser]['st'].="";
		else
			$_xh[$parser]['st'].="\"". $_xh[$parser]['ac'] . "\""; 	
		//}
		// This if() detects if no scalar was inside <VALUE></VALUE>
		// and pads an empty "".
//		if($_xh[$parser]['st'][strlen($_xh[$parser]['st'])-1] == '(') {
//			$_xh[$parser]['st'].= '""';
//		}
		$_xh[$parser]['st'].=", '" . $_xh[$parser]['vt'] . "')";
		if ($_xh[$parser]['cm']) $_xh[$parser]['st'].=",";
		
		//echo $_xh[$parser]['st']."HIJKLMN";
		
		$_xh[$parser]['params'][]=$_xh[$parser]['st'];
		
		break;
	case "return":
		if(($_xh[$parser]['rt']=='Array')||($_xh[$parser]['rt']=='Vector')||($_xh[$parser]['rt']=='Map')) {
			$_xh[$parser]['st']=substr($_xh[$parser]['st'],0,-1);
			$_xh[$parser]['st'].=")"; 
		}
		if(($_xh[$parser]['rt']=='Array')||($_xh[$parser]['rt']=='Vector'))
			$_xh[$parser]['vt']='array';
		if($_xh[$parser]['rt']=='Map')
			$_xh[$parser]['vt']='struct';
		if($_xh[$parser]['rt']=='string')
			$_xh[$parser]['vt']='string';
		if($_xh[$parser]['rt']=='int')
			$_xh[$parser]['vt']='int';
		if($_xh[$parser]['rt']=='double')
			$_xh[$parser]['vt']='double';
		// deal with a string value
		//if (strlen($_xh[$parser]['ac'])>0 && //$_xh[$parser]['vt']==$soapInt) {
		//	$_xh[$parser]['vt']==$soapInt) {
		
		if(($_xh[$parser]['rt']=='Array')||($_xh[$parser]['rt']=='Vector')||($_xh[$parser]['rt']=='Map')) 	
			//$_xh[$parser]['st'].=$_xh[$parser]['ac'];
			 $_xh[$parser]['st'].="";
		else	  	
		$_xh[$parser]['st'].="\"". $_xh[$parser]['ac'] . "\""; 
		//}
		// This if() detects if no scalar was inside <VALUE></VALUE>
		// and pads an empty "".
		if($_xh[$parser]['st'][strlen($_xh[$parser]['st'])-1] == '(') {
			$_xh[$parser]['st'].= '""';
		}
		$_xh[$parser]['st'].=", '" . $_xh[$parser]['vt'] . "')";
		if ($_xh[$parser]['cm']) $_xh[$parser]['st'].=",";
		
		$_xh[$parser]['params'][]=$_xh[$parser]['st'];
		
		break;
	case "MEMBER":
	  $_xh[$parser]['ac']=""; $_xh[$parser]['qt']=0;
	 break;
	case "DATA":
	  $_xh[$parser]['ac']=""; $_xh[$parser]['qt']=0;
	  break;
	case "PARAM":
	  $_xh[$parser]['params'][]=$_xh[$parser]['st'];
	  break;
	case "ismethod":
      //echo "hello\n";		
	  //echo $_xh[$parser]['ac'];
	  //echo "\nhello\n";
	  //$_xh[$parser]['method']=ereg_replace("^[\n\r\t ]+", "", $_xh[$parser]['ac']);
	  $_xh[$parser]['method']=$methName;
		break;
	case "BOOLEAN":
		// special case here: we translate boolean 1 or 0 into PHP
		// constants true or false
		if ($_xh[$parser]['ac']=='1') 
			$_xh[$parser]['ac']="true";
		else 
			$_xh[$parser]['ac']="false";
		$_xh[$parser]['vt']=strtolower($name);
		break;
	default:
		break;
	}
	// if it's a valid type name, set the type
	if ($soapTypes[strtolower($name)]) {
		$_xh[$parser]['vt']=strtolower($name);
	}
	
}

function soap_cd($parser, $data)
{	
  global $_xh, $soap_backslash, $soap_twoslash;

  //if (ereg("^[\n\r \t]+$", $data)) return;
  // print "adding [${data}]\n";
  if ($_xh[$parser]['lv']==1) {  
		$_xh[$parser]['qt']=1; 
		$_xh[$parser]['lv']=2; 
	}
  if ($_xh[$parser]['qt']) { // quoted string
		$_xh[$parser]['ac'].=str_replace('\$', '\\$',
														str_replace('"', '\"', 
														str_replace(chr(92),$soap_backslash, $data)));
	}
	else 
		$_xh[$parser]['ac'].=$data;
}

function soap_dh($parser, $data)
{
  global $_xh;
  if (substr($data, 0, 1) == "&" && substr($data, -1, 1) == ";") {
		if ($_xh[$parser]['lv']==1) {  
			$_xh[$parser]['qt']=1; 
			$_xh[$parser]['lv']=2; 
		}
		$_xh[$parser]['ac'].=$data;
  }
}

class soap_client {
  var $path;
  var $server;
  var $port;
  var $errno;
  var $errstring;
  var $debug=0;
	var $username="";
	var $password="";

  function soap_client($path, $server, $port=80) {
		$this->port=$port; $this->server=$server; $this->path=$path;
  }

  function setDebug($in) {
		if ($in) { 
			$this->debug=1;
		} else {
			$this->debug=0;
		}
  }

	function setCredentials($u, $p) {
		$this->username=$u;
		$this->password=$p;
	}

  function send($msg, $timeout=0) {
		// where msg is an soapmsg
		$msg->debug=$this->debug;
		return $this->sendPayloadHTTP10($msg, $this->server, $this->port,
																		$timeout, $this->username, 
																		$this->password);
  }

	function sendPayloadHTTP10($msg, $server, $port, $timeout=0, $username="", $password="") {
		if($timeout>0)
			$fp=fsockopen($server, $port,
										&$this->errno, &$this->errstr, $timeout);
		else
			$fp=fsockopen($server, $port,
										&$this->errno, &$this->errstr);
		if (!$fp) {   
			return 0;
		}
		// Only create the payload if it was not created previously
		if(empty($msg->payload)) $msg->createPayload();
		
		// thanks to Grant Rauscher <grant7@firstworld.net>
		// for this
		$credentials="";
		if ($username!="") {
			$credentials="Authorization: Basic " .
				base64_encode($username . ":" . $password) . "\r\n";
		}

		$op= "POST " . $this->path. " HTTP/1.0\r\nUser-Agent: PHP soap 1.0\r\n" .
			"Host: ". $this->server  . "\r\n" .
			$credentials . 
			"Content-Type: text/xml\r\nContent-Length: " .
			strlen($msg->payload) . "\r\n\r\n" .
			$msg->payload;
		
		if (!fputs($fp, $op, strlen($op))) {
			$this->errstr="Write error";
			return 0;
		}
		$resp=$msg->parseResponseFile($fp);
		fclose($fp);
		return $resp;
	}

} // end class soap_client

class soapresp {
	var $xv;
	var $fn;
	var $fs;
	var $hdrs;

    function soapresp($val, $fcode=0, $fstr="") {
		if ($fcode!=0) {
			$this->fn=$fcode;
			$this->fs=htmlspecialchars($fstr);
			$this->xv="Error Occur.";
		} else {
		    $this->xv=$val;
		}
    }

	function faultCode() { return $this->fn; }
	function faultString() { return $this->fs; }
	function value() { return $this->xv; }

    function serialize() { 
    	global $soapTargetMethod,$soapTargetId;
    	
    	//echo "**************".$soapTargetId."***************";
    	
		$rs="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/1999/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/1999/XMLSchema\">\n".
			"<SOAP-ENV:Body>\n"."<ns1:".$soapTargetMethod."Response xmlns:ns1=\"".$soapTargetId."\" SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\">\n";
		if ($this->fn) {
		  $rs.="<fault>
				  <value>
				    <struct>
				      <member>
				        <name>faultCode</name>
				        <value><int>" . $this->fn . "</int></value>
				      </member>
				      <member>
				        <name>faultString</name>
				        <value><string>" . $this->fs . "</string></value>
				      </member>
				    </struct>
				  </value>
				</fault>";
		} else {
		  $rs.=$this->xv->serialize_response();
		}
		$rs.="</ns1:".$soapTargetMethod."Response>\n"."</SOAP-ENV:Body>\n</SOAP-ENV:Envelope>";
		return $rs;
    }
}

class soapmsg {
	var $payload;
	var $methodname;
	var $targetid;
	var $params=array();
	var $debug=0;

	function soapmsg($meth,$tid="",$pars=0) {
		global $soapTargetId,$soapTargetMethod;
		$this->methodname=$meth;
		$this->targetid=$tid;
		$soapTargetId=$tid;
		$soapTargetMethod=$meth;
		
		if (is_array($pars) && sizeof($pars)>0) {
			for($i=0; $i<sizeof($pars); $i++) 
				$this->addParam($pars[$i]);
		}
  	}

	function xml_header() {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			   "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/1999/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/1999/XMLSchema\">\n".
		       "<SOAP-ENV:Body>\n";
	       
	}

	function xml_footer() {
		return "</SOAP-ENV:Body>\n</SOAP-ENV:Envelope>\n";
	}

	function createPayload() {
		$this->payload=$this->xml_header();
		$this->payload.="<ns1:$this->methodname xmlns:ns1=\"$this->targetid\" SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\">\n";
        for($i=0; $i<sizeof($this->params); $i++) {
			$p=$this->params[$i];
			$this->payload.="" . $p->serialize($i+1);
		}
	    $this->payload.="</ns1:$this->methodname>\n";
		$this->payload.=$this->xml_footer();
		$this->payload=str_replace("\n", "\r\n", $this->payload);
	}

  	function method($meth="") {
		if ($meth!="") {
			$this->methodname=$meth;
		}
		return $this->methodname;
    }

	function serialize() {
		$this->createPayload();
		return $this->payload;
	}

	function addParam($par) { $this->params[]=$par; }
	function getParam($i) { return $this->params[$i]; }
	function getNumParams() { return sizeof($this->params); }

    function parseResponseFile($fp) {
		$ipd="";

		while($data=fread($fp, 32768)) {
	    	$ipd.=$data;
		}
		return $this->parseResponse($ipd);
    }
    function parseResponse($data="") {
		global $_xh,$soaperr,$soapstr;
		global $soap_defencoding;
	
		$parser = xml_parser_create($soap_defencoding);

		$_xh[$parser]=array();

		$_xh[$parser]['st']=""; 
		$_xh[$parser]['cm']=0; 
		$_xh[$parser]['isf']=0; 
		$_xh[$parser]['ac']="";
		$_xh[$parser]['rt']="";
		$_xh[$parser]['flag']=0;  //decide whether the first element of array

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($parser, "soap_se", "soap_ee");
		xml_set_character_data_handler($parser, "soap_cd");
		xml_set_default_handler($parser, "soap_dh");
		$soap_value=new soapval;

		$hdrfnd=0;
		if ($this->debug)
	    print "<PRE>---GOT---\n" ."#######\n". htmlspecialchars($data) . "#######\n".
		"\n---END---\n</PRE>";
		// see if we got an HTTP 200 OK, else bomb
		// but only do this if we're using the HTTP protocol.
		if (ereg("^HTTP",$data) && 
			!ereg("^HTTP/[0-9\.]+ 200 ", $data)) {
			$errstr= substr($data, 0, strpos($data, "\n")-1);
			error_log("HTTP error, got response: " .$errstr);
			$r=new soapresp(0, $soaperr["http_error"],
												$soapstr["http_error"]. " (" . $errstr . ")");
			xml_parser_free($parser);
			return $r;
		}
		// gotta get rid of headers here
		
		if ((!$hdrfnd) && ereg("^(.*)\r\n\r\n",$data,$_xh[$parser]['ha'])) {
			$data=ereg_replace("^.*\r\n\r\n", "", $data);
			$hdrfnd=1;
		}
	    if (!xml_parse($parser, $data, sizeof($data))) {
			// thanks to Peter Kocks <peter.kocks@baygate.com>
			if((xml_get_current_line_number($parser)) == 1)   
				$errstr = "XML error at line 1, check URL";
			else
			$errstr = sprintf("XML error: %s at line %d",
												xml_error_string(xml_get_error_code($parser)),
												xml_get_current_line_number($parser));
			error_log($errstr);
			$r=new soapresp(0, $soaperr["invalid_return"],$soapstr["invalid_return"]);
			xml_parser_free($parser);
			return $r;
		}
		xml_parser_free($parser);
		if ($this->debug) {
	  		print "<PRE>---EVALING---[" . 
			strlen($_xh[$parser]['st']) . " chars]---\n" . 
			htmlspecialchars($_xh[$parser]['st']) . ";\n---END---</PRE>";
		}
		if (strlen($_xh[$parser]['st'])==0) {
	  		// then something odd has happened
	  		// and it's time to generate a client side error
	  		// indicating something odd went on
	  		$r=new soapresp(0, $soaperr["invalid_return"],
						$soapstr["invalid_return"]);
		} else {
	  		eval('$v=' . $_xh[$parser]['st'] . '; $allOK=1;');
	  		if ($_xh[$parser]['isf']) {
				$f=$v->structmem("faultCode");
				$fs=$v->structmem("faultString");
				$r=new soapresp($v, $f->scalarval(), 
								  $fs->scalarval());
	  		} else {
				$r=new soapresp($v);
	  		}
		}
		$r->hdrs=split('\r?\n', $_xh[$parser]['ha'][1]);
		return $r;
  	}

}

class soapval {
	var $me=array();
	var $mytype=0;

  	function soapval($val=-1, $type="") {
		global $soapTypes;
		$this->me=array();
		$this->mytype=0;
		if ($val!=-1 || $type!="") {
			if ($type=="") $type="string";
			if ($soapTypes[$type]==1) {
				$this->addScalar($val,$type);
			}
	    	else if ($soapTypes[$type]==2)
				$this->addArray($val);
			else if ($soapTypes[$type]==3)
				$this->addStruct($val);
		}
    }

	function addScalar($val, $type="string") {
		global $soapTypes, $soapBoolean;

		if ($this->mytype==1) {
			echo "<B>soapval</B>: scalar can have only one value<BR>";
			return 0;
		}
		$typeof=$soapTypes[$type];
		if ($typeof!=1) {
			echo "<B>soapval</B>: not a scalar type (${typeof})<BR>";
			return 0;
		}
		
		if ($type==$soapBoolean) {
			if (strcasecmp($val,"true")==0 || $val==1 || $val==true) {
				$val=1;
			} else {
				$val=0;
			}
		}
		
		if ($this->mytype==2) {
			// we're adding to an array here
			$ar=$this->me["array"];
			$ar[]=new soapval($val, $type);
			$this->me["array"]=$ar;
		} else {
			// a scalar, so set the value and remember we're scalar
			$this->me[$type]=$val;
			$this->mytype=$typeof;
		}
		return 1;
    }

	function addArray($vals) {
		global $soapTypes;
		if ($this->mytype!=0) {
			echo "<B>soapval</B>: already initialized as a [" .$this->kindOf() . "]<BR>";
			return 0;
		}
		$this->mytype=$soapTypes["array"];
		$this->me["array"]=$vals;
		return 1;
	}

	function addStruct($vals) {
		global $soapTypes;
		if ($this->mytype!=0) {
	   		echo "<B>soapval</B>: already initialized as a [" .$this->kindOf() . "]<BR>";
	    return 0;
		}
		$this->mytype=$soapTypes["struct"];
		$this->me["struct"]=$vals;
		return 1;
	}

	function dump($ar) {
		reset($ar);
		while ( list( $key, $val ) = each( $ar ) ) {
			echo "$key => $val<br>";
		    if ($key == 'array')
				while ( list( $key2, $val2 ) = each( $val ) ) {
			    echo "-- $key2 => $val2<br>";
		    }
		}
	}

	function kindOf() {
		switch($this->mytype) {
		case 3:
	    	return "struct";
	    break;
		case 2:
	    	return "array";
	    break;
	    case 1:
	    	return "scalar";
	    break;
		default:
	  		return "undef";
		}
	}

	function serializedata($typ, $val, $i=0,$return=0) {
		if(!$return)
			$PARAM="params".$i;
		else
			$PARAM="return";
		$rs="";
		global $soapTypes, $soapBase64, $soapString,$soapBoolean;
		switch($soapTypes[$typ]) {
		case 3:
			// struct
			$rs.="<$PARAM xmlns:ns2=\"http://xml.apache.org/xml-soap\" xsi:type=\"ns2:Map\">\n";
			reset($val);
			while(list($key2, $val2)=each($val)) {
				
				$rs.=$this->serializeval3($key2,$val2);
			}
			$rs.="</$PARAM>\n";
			break;
		case 2:
			// array
			$rs.="<$PARAM xmlns:ns2=\"http://schemas.xmlsoap.org/soap/encoding/\" xsi:type=\"ns2:Array\" ns2:arrayType=\"xsd:ur-type[".sizeof($val)."]\">\n";
			//echo "size of array is:".sizeof($val);
			for($j=0; $j<sizeof($val); $j++) {
				$rs.=$this->serializeval2($val[$j]);
			}
			$rs.="</$PARAM>\n";
			break;
		case 1:
			switch ($typ) {
			case $soapBase64:
				$rs.="<${typ}>" . base64_encode($val) . "</${typ}>";
				break;
			case $soapBoolean:
				$rs.="<${typ}>" . ($val ? "1" : "0") . "</${typ}>";
					break;
			case $soapString:
				$rs.="<$PARAM xsi:type=\"xsd:${typ}\">". htmlspecialchars($val)."</$PARAM>\n";
				break;
			default:
				$rs.="<$PARAM xsi:type=\"xsd:${typ}\">"."${val}"."</$PARAM>\n";//"<${typ}>${val}</${typ}>";
			}
			break;
		default:
			break;
		}
		return $rs;
	}

	function serialize($i) {
		return $this->serializeval($this,$i);
    }
    function serialize_response() {
		return $this->serializeval_response($this);
    }
    function serializeval($o,$i=0) {
		global $soapTypes;
		$rs="";
		$ar=$o->me;
		reset($ar);
		list($typ, $val) = each($ar);
		$rs.=$this->serializedata($typ, $val,$i);
		return $rs;
    }
  	function serializeval2($o) {
  		$type=gettype($o);
  		if($type=='integer') $type='int';
		global $soapTypes;
		$rs="";
		$rs.="<item xsi:type=\"xsd:".$type."\">$o</item>\n";
		//$rs.="<item>$o</item>\n";
		return $rs;
    }
    function serializeval3($k,$o) {
  		$typek=gettype($k);
  		$typeo=gettype($o);
  		if($typek=='integer') $typek='int';
  		if($typeo=='integer') $typeo='int';
		global $soapTypes;
		$rs="";
		$rs.="<item>\n";
		$rs.="<key xsi:type=\"xsd:".$typek."\">$k</key>\n";
		$rs.="<value xsi:type=\"xsd:".$typeo."\">$o</value>\n";
		$rs.="</item>\n";
		return $rs;
    }
    function serializeval_response($o) {
		global $soapTypes;		
		$rs="";
		$ar=$o->me;
		reset($ar);
		list($typ, $val) = each($ar);
		//$rs.="<return xmlns:ns2=\"http://schemas.xmlsoap.org/soap/encoding/\" xsi:type=\"ns2:Array\" ns2:arrayType=\"xsd:ur-type[2]\"\n";
		$rs.=$this->serializedata($typ, $val,0,1);
		//$rs.="</return>\n";
		return $rs;
    }
    function structmem($m) {
		$nv=$this->me["struct"][$m];
		return $nv;
    }

	function structreset() {
		reset($this->me["struct"]);
	}
	
	function structeach() {
		return each($this->me["struct"]);
	}

    function scalarval() {
		global $soapBoolean, $soapBase64;
		reset($this->me);
		list($a,$b)=each($this->me);
		return $b;
    }

    function scalartyp() {
		global $soapI4, $soapInt;
		reset($this->me);
		list($a,$b)=each($this->me);
		if ($a==$soapI4) 
			$a=$soapInt;
		return $a;
    }

    function arraymem($m) {
		$nv=$this->me["array"][$m];
		return $nv;
    }

    function arraysize() {
		reset($this->me);
		list($a,$b)=each($this->me);
		return sizeof($b);
    }
}

// date helpers
function iso8601_encode($timet, $utc=0) {
	// return an ISO8601 encoded string
	// really, timezones ought to be supported
	// but the XML-RPC spec says:
	//
	// "Don't assume a timezone. It should be specified by the server in its
  // documentation what assumptions it makes about timezones."
	// 
	// these routines always assume localtime unless 
	// $utc is set to 1, in which case UTC is assumed
	// and an adjustment for locale is made when encoding
	if (!$utc) {
		$t=strftime("%Y%m%dT%H:%M:%S", $timet);
	} else {
		if (function_exists("gmstrftime")) 
			// gmstrftime doesn't exist in some versions
			// of PHP
			$t=gmstrftime("%Y%m%dT%H:%M:%S", $timet);
		else {
			$t=strftime("%Y%m%dT%H:%M:%S", $timet-date("Z"));
		}
	}
	return $t;
}

function iso8601_decode($idate, $utc=0) {
	// return a timet in the localtime, or UTC
	$t=0;
	if (ereg("([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})",
					 $idate, $regs)) {
		if ($utc) {
			$t=gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		} else {
			$t=mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}
	} 
	return $t;
}

/****************************************************************
* soap_decode takes a message in PHP soap object format and *
* tranlates it into native PHP types.                           *
*                                                               *
* author: Dan Libby (dan@libby.com)                             *
****************************************************************/
function soap_decode($soap_val) {
   $kind = $soap_val->kindOf();

   if($kind == "scalar") {
      return $soap_val->scalarval();
   }
   else if($kind == "array") {
      $size = $soap_val->arraysize();
      $arr = array();

      for($i = 0; $i < $size; $i++) {
         array_append($arr, soap_decode($soap_val->arraymem($i)) );
      }
      return $arr; 
   }
   else if($kind == "struct") {
      $soap_val->structreset();
      $arr = array();

      while(list($key,$value)=$soap_val->structeach()) {
         $arr[$key] = soap_decode($value);
      }
      return $arr;
   }
}

/****************************************************************
* soap_encode takes native php types and encodes them into    *
* soap PHP object format.                                     *
* BUG: All sequential arrays are turned into structs.  I don't  *
* know of a good way to determine if an array is sequential     *
* only.                                                         *
*                                                               *
* feature creep -- could support more types via optional type   *
* argument.                                                     *
*                                                               *
* author: Dan Libby (dan@libby.com)                             *
****************************************************************/
function soap_encode($php_val) {
   global $soapInt;
   global $soapDouble;
   global $soapString;
   global $soapArray;
   global $soapStruct;

   $type = gettype($php_val);
   $soap_val = new soapval;

   switch($type) {
      case "array":
      case "object":
         $arr = array();
         while (list($k,$v) = each($php_val)) {
            $arr[$k] = soap_encode($v);
         }
         $soap_val->addStruct($arr);
         break;
      case "integer":
         $soap_val->addScalar($php_val, $soapInt);
         break;
      case "double":
         $soap_val->addScalar($php_val, $soapDouble);
         break;
      case "string":
         $soap_val->addScalar($php_val, $soapString);
         break;
      case "unknown type":
      default:
         $soap_val = false;
         break;
   }
   return $soap_val;
}

?>
