<?
//processform.php
//Wendy Roseberry
//November 20, 2001
//Object that supports any HTML form data
require('lib.mysql_encrypt.php');		
class FormGenie{

	function FormGenie($input_values, $files=''){
		//Input_values should be a hash where key is the name of the input and value is the value. (duh)
		//set object references
		//make input_values "template" friendly for use with curly's
		$ConfFile = file('/web/php/GeniePay/conf.properties');
		foreach($ConfFile as $line){
			if(!$line){ continue; }
			$line = trim($line);
			list($key, $value) = split('=', $line,2);
			
			$key = trim($key);
			$value = trim($value);
			if(!$key){ continue; }
			
			define($key, $value);
		}
		
		if( ! class_exists('sql_mysql') ){
		  require('sql_mysql.php');
		}
		$DB = new sql_mysql(DBUser, DBName, DBPass, DBHost);
		$GT = new sql_mysql(DBUser2, DBName2, DBPass2, DBHost2);

		if( ! class_exists('ina_sanitize') ){
		require('INA/Security/class.ina_sanitize.php');
	  	}
	  	$this->Sanitize = new ina_sanitize();
	  	
		$this->files=$files;
		foreach ($input_values as $k=>$v){
			//Make line wrap if longer than 100 chars (decided to use html tables to enforce this)
			
			$strlength = strlen($v);
//			
			$use_v = $v;
			
			if(!is_array($use_v)){
				$use_v = str_replace("\r\n","<br>", $use_v);
				
				$this->InputValues['{'.$k.'}'] = stripslashes($use_v);
				
				if (preg_match('/[a-zA-z0-9_]{1,25}/', $use_v)){
					if(strlen('{'.$k.'_'.$use_v.'}') > 251){  
					
						$this->InputValues['{'.$k.'_'.substr($use_v, 0, 200).'}'] = 'X';

                    }else{
						$this->InputValues['{'.$k.'_'.$use_v.'}'] = 'X';
					}
				}
			}else{
				// Handle checkboxes
				
				// Default delimiter.
				$delim = ', ' ;
				
				
				if ($_REQUEST[$k . '_delim']){
				
					// What delimiter should we use.
					switch($_REQUEST[$k . '_delim']){
						case 'CRLF':
							$delim = "\r\n" ;
							break ;
						case 'COMMA':
							$delim = ', ' ;
							break ;
						case 'TAB':
							$delim = "\t" ;
							break ;
						case 'SPACE':
							$delim = ' ' ;
							break ;
						case 'COLON':
							$delim = ': ' ;
							break ;
						case 'SEMICOLON':
							$delim = '; ' ;
							break ;
						case 'SEMICOLONPOUND':
							$delim = ';#' ;
							break ;
						default:
							$delim = ', ' ;
					}
				}
				$use_v = join($delim, $use_v) ;
				
				if($k != '_validate_pattern' && $k != '_price'){
			
					$this->InputValues['{'.$k.'}'] = stripslashes($use_v) ;
	
					if (preg_match('/[a-zA-z0-9_]{1,25}/', $use_v)){
						if(strlen('{'.$k.'_'.$use_v.'}') > 251){  
						
							$this->InputValues['{'.$k.'_'.substr($use_v, 0, 200).'}'] = 'X';
	
						}else{
							$this->InputValues['{'.$k.'_'.$use_v.'}'] = 'X';
						}
					}
				}
				
				if($k=='_price'){
					foreach( $input_values['_price'] as $field => $value ){
						$this->InputValues['{'.$k.'_'.$field.'}'] = $value;
					}
				}
			}
		}
		
		$this->SQL= $DB;
		$this->GT= $GT;
		$this->InputValues['{'.time_stamp.'}']=date("D m/d/Y h:i a");
		$this->emailTemplate = $input_values['emailtemplate'];
		$this->govpayTemplate = $input_values['govpaytemplate'];
		$this->globaltemplate = $input_values['globaltemplate'];
		$this->emailTemplateLink = $input_values['emailtemplateLink'];
		$this->emailTo = $input_values['emailto'];
		$this->emailSubject = $input_values['subject'];
		$this->emailFrom = $input_values['emailfrom'];
		$this->confirmationTemplate = $input_values['confirmationpage'];
		$this->storedFileLocation = $input_values['storedFileLocation'];
		$this->storedFileLink = $input_values['storedFileLink'];
		$this->contactEmail = $input_values['contactemail'];
		$this->userEmail = $input_values['useremail'];
		$this->emailBody = $input_values['emailbody']?$input_values['emailbody']:'This information was recently submitted via the web.';
		$this->filename = $input_values['filename']?$input_values['filename']:'webform.html';
		$this->errorurl = $input_values['errorurl'];
		$this->_required = $input_values['_required'];
		$this->_validate_pattern = $input_values['_validate_pattern'];
		$this->_validate_message = $input_values['_validate_message'];
		$this->govpay = $input_values['govpay'];
		$this->VendorID = $input_values['vendorid'];
		$this->ServiceCode = $input_values['servicecode'];
		$this->baseurl = $input_values['baseurl'];
		$this->GPCSessionService = $input_values['GPCSessionService'];
		$this->GPCAffiliateId = $input_values['GPCAffiliateId'];
		$this->GPGServiceURL = $input_values['GPGServiceURL'];
		$this->_price = $input_values['_price'];
		$this->Total = $input_values['totalamount'];
		$this->EncryptData = $input_values['encrypt'];
		$this->EmailData=trim($input_values['emaildata']);
		$this->Demo=1;
	}	
	
	function out($key, $value){
		echo $key.": ".$value.'<br>';
	}
	
	function parsePDFTemplate($template_file){
		$fp = fopen($template_file, "r");
		$filesize = filesize($template_file) + 100;
		
		while(!feof($fp)) {
			$buffer = fread($fp, $filesize);
   			
   		}
   		fclose($fp);
   		
   		foreach ($this->InputValues as $k=>$v){
   			$buffer = str_replace($k, str_replace("&lt;br&gt;", "<br>",nl2br(htmlentities($v, ENT_QUOTES, 'ISO-8859-1'))), $buffer);
   		}
   		//now get rid of all the other placeholders
   		$buffer = preg_replace("/\{[a-zA-Z0-9_]*}/", " ", $buffer);
   		
   		
   		return $buffer;
   		
   	}
   	
   	function makeFDF($template_file){
   		
   		$newfile = md5 (uniqid (rand())).'.fdf';
   		$new = fopen ($this->storedFileLocation.$newfile, "w");
		fclose ($new);
   		
		$outfdf = fdf_create();
		
		foreach ($this->InputValues as $k => $v){
				$key = str_replace ("}", "", $k);
				$key = str_replace ("{", "", $key);
				
				fdf_set_value($outfdf, $key, str_replace("&lt;br&gt;", "<br>",nl2br(htmlentities($v, ENT_QUOTES, 'ISO-8859-1'))), 1);
			
		}
		
		fdf_set_file($outfdf, "$template_file");
				
		fdf_save($outfdf, $this->storedFileLocation.$newfile);
		fdf_close($outfdf);
				
		return $newfile;
   		
   	}
	
	function parseHTMLTemplate($template_file){
		
		if(substr($template_file,0,4)=='http' || substr($template_file,0,5)=='https'){
		
			$buffer = $this->getUrl($template_file);
			
		}else{

			# must not contain .., ;, null byte, conf, or options
			$template_file = preg_replace('/[^\w\/.-]/', '', $template_file);
			if (preg_match('/conf/', $template_file) || preg_match('/options/', $template_file) || preg_match('/\.\./', $template_file)) {
				$this->sendError("The file supplied ($template_file) is not valid. Invalid characters in the name. Email was not sent.");
				return false;
			}
			# must be in /web/html or /web/shtml
			if (!(preg_match('/^\/web\/html/', $template_file)) && !(preg_match('/^\/web\/shtml/', $template_file))) {
				$this->sendError("The file supplied ($template_file) is not valid. Not located in the web root. Email was not sent.");
				return false;
			}
			# must end with html or text
			if (
				!(preg_match('/\.txt$/', $template_file)) && 
				!(preg_match('/\.html$/', $template_file)) && 
				!(preg_match('/\.htm$/', $template_file)) && 
				!(preg_match('/\.doc$/', $template_file)) && 
				!(preg_match('/\.fdf$/', $template_file)) && 
				!(preg_match('/\.pdf$/', $template_file))) {
				$this->sendError("The file supplied ($template_file) is not valid. Invalid file extension. Email was not sent.");
				return false;
			}

			if (!file_exists($template_file)){
				$this->sendError($template_file." is not found.  Email not sent.");
				return false;
			}
			
			$fp = fopen($template_file, "r");
			$filesize = filesize($template_file) + 100;
			
			while(!feof($fp)) {
				$buffer = fread($fp, $filesize);
				
			}
			fclose($fp);
				
		}
		
		foreach ($this->InputValues as $k=>$v){
		
			if (!(preg_match('/\.txt$/', $template_file)) && 
				!(preg_match('/\.doc$/', $template_file))){
				$buffer = str_replace($k, str_replace("&lt;br&gt;", "<br>", nl2br(htmlentities($v, ENT_QUOTES, 'ISO-8859-1'))), $buffer);
			}else{
				$buffer = str_replace($k, $v, $buffer);
			}
		}
		//now get rid of all the other placeholders
		$buffer = preg_replace("/\{[a-zA-Z0-9_]*}/", " ", $buffer);
		$template_data = $buffer;
		
		if (!$template_data){
			$this->sendError = $template_file." returned no data.";
			return false;
		}
		
		return $template_data;
	}
	
	function processEmail(){
		$extension = substr($this->emailTemplate, -3, 3);
		//if (!$this->Demo){
		//	//form isn't coming from inside INA
		//	//show user error page
		//	require ('no_access.html');
		//	exit;
		//}
		
		if( $this->errorurl && 
				($this->_required || 
				 $this->_validate_pattern) ){
			$this->validateForm();
		}
		
		//$spam = $this->spamCheck();
		if ($spam == 'true'){
			$sql = sprintf("insert into spam(user_email, sent_to, raw_data)values('%s', '%s', '%s')",
				mysql_real_escape_string($this->emailTo),
				mysql_real_escape_string($this->userEmail),
				mysql_real_escape_string(wddx_serialize_value($this->InputValues)));
				$this->SQL->Run($sql);
				return;
		}
				
		if (($extension == 'tml') || ($extension == 'htm') || ($extension == 'txt')){
			$emaildata = $this->parseHTMLTemplate($this->emailTemplate);
		
		}else if ($extension == 'pdf'){
			
			$emaildata = $this->makeFDF($this->emailTemplateLink);
			$this->emailBody .= "\n\n Click the following link to open the completed form for printing.\n\n";
			$this->emailBody .= $this->storedFileLink.$emaildata;
			
		}elseif($this->EmailData){
			$emaildata = $this->EmailData;
		}
		
		if( $_REQUEST[databaseid] ){
			#if (!$this->SQL){
			#	require ('sql_mysql.php');
			#	$this->SQL = new sql_mysql('formgenie', 'formgenie', '', 'proddb');
			#}
			
			// make sure the table exists
			$tableExists = false;
			$tables = $this->SQL->Run("SHOW TABLE STATUS");
			while( $table = $this->SQL->fetch($tables) ){
				if( $table[0] == $_REQUEST[databaseid] ){
					$tableExists = true;
				}
			}

			if( $tableExists && !$this->EncryptData){
				// insert the data
				$vals = array();
				$fields = $this->SQL->Run("describe " . $_REQUEST[databaseid]);
				while( $field = $this->SQL->fetch($fields) ){	
					switch( $field[0] ){
						case 'surveyid': break;
						case 'surveydate': $vals[ $field[0] ] = 'FORMULA: NOW()'; break;
						default: $vals[ $field[0] ] = $_REQUEST[$field[0]];
					}					
				}
				
				$sql = $this->SQL->makeInsert($_REQUEST[databaseid], $vals);
				$this->SQL->Run($sql);	
				
				$sql="SELECT last_insert_id() as id";
		                $id=$this->SQL->fetchrow($sql);
                
			}else{
				if(!$this->EncryptData){
					// just insert generic data
					$sql = sprintf("insert into submissions(
						dbkey,date_submitted,user_email,sent_to,
						raw_data,formatted_data)values('%s',NOW(),'%s','%s','%s','%s')",
						mysql_real_escape_string($_REQUEST[databaseid]),
						mysql_real_escape_string($this->emailTo),
						mysql_real_escape_string($this->userEmail),
						mysql_real_escape_string(wddx_serialize_value($this->InputValues)),
						mysql_real_escape_string($emaildata)	);
				}else{
					// just insert encrypted data
					$sql = sprintf("insert into submissions(
						dbkey,date_submitted,user_email,sent_to,
						raw_encrypted,formatted_encrypted)values('%s',NOW(),'%s','%s','%s','%s')",
						mysql_real_escape_string($_REQUEST[databaseid]),
						mysql_real_escape_string($this->emailTo),
						mysql_real_escape_string($this->userEmail),
						db_encrypt(wddx_serialize_value($this->InputValues)),
						db_encrypt($emaildata));
				}
				//echo $sql;
				$this->SQL->Run($sql);				
				$sql="SELECT last_insert_id() as id";
                $id=$this->SQL->fetchrow($sql);
				// upload file 
				if($this->files){
					
					foreach($this->files as $k=>$v){
						$content='';
						if($v[name]){
							$file=$v[tmp_name];
							
							$image=$id[id]."_".$v[name];
							  
							 $fp = fopen($file, "rb");
							
							if($fp) {
							 	while (!feof($fp)) {

							 		$content .= fread($fp, filesize($file));
								 
								}
								 fclose($fp);
							}
							 if(!$handle= fopen("/web/app-data/formgenie/images/".$image, "wb")){
								print "\n An administrator has been notified.";
								$msg= $file." Could not be opened";
								exit;
							 }

			 				if(fwrite($handle, $content)== false){
			 					print "can not write to file";
			 					$msg= "Could not write to".$image." at location /web/app-data/formgenie/images/";
			 					exit;	
			 				}
					
							fclose($handle);
							 
							
							$sql='insert into form_files(form_id, name, size, type) values("'.$id[id].'","'.$image.'","'.$v[size].'","'.$v[type].'")';
							$this->SQL->Run($sql);
						}    
					}        
					
				}
				
				
			}
			
		}
		
		if($this->govpay){
					
			$this->forwardToGovPay($id[id]);
		
		}elseif ($emaildata && !$this->EncryptData){
			
		
			if (!$this->InputValues['emailfrom']){
				$this->InputValues['emailfrom'] = 'info@ark.org';
			}
			
			$this->sendEmail("$this->emailFrom", $this->emailTo, $this->emailSubject, $this->emailBody, $emaildata, $this->filename);
			//send to user email?
			if ($this->userEmail){
				$this->sendEmail("$this->emailFrom", $this->userEmail, $this->emailSubject, $this->emailBody, $emaildata, $this->filename);
			}
		}elseif ($emaildata){
			$emailbody = "There has been a new submission to " . $this->emailSubject . ". You can view this form by going to: https://www.ark.org/toolkit/index.php";
			$emaildata = array() ;
			$this->sendEmail("$this->emailFrom", $this->emailTo, $this->emailSubject, $emailbody, $emaildata, null);
		}
	}
	
	function validateForm(){
		// if errors are found, show errorUrl
		$Errors  = array();
		
		if( is_array($this->_required) ){
			foreach( $this->_required as $k=>$v ){
				if(!trim($this->InputValues['{'.$k.'}'])){
					$Errors[] = htmlentities($v, ENT_QUOTES, 'ISO-8859-1');
				}
			}
		}

		if( is_array($this->_validate_pattern) ){
			foreach( $this->_validate_pattern as $k=>$v ){
				if( trim($this->InputValues['{'.$k.'}']) && !preg_match($v, $this->InputValues['{'.$k.'}']) ){
					//echo "value $this->InputValues['{'.$k.'}'] failed $v<br>";
					if( $this->_validate_message && 
						 $this->_validate_message[$k] ){
						$Errors[] =	htmlentities($this->_validate_message[$k], ENT_QUOTES, 'ISO-8859-1');
					}else{
						$Errors[] = "Error on $k";
					}
				}
			}
		}
		
		if($this->govpay){
			$total = $this->getTotalAmount();
			
			if($total <= 0){
				$Errors[] = "Total amount of your transaction is not greater than zero dollars.";
			}
		}
		
		if( count($Errors)>0 ){
			if( $this->errorurl){
				$display = $this->getUrl($this->errorurl);
			}	
			
			if(!$display){
				$display = "There were errors with your input: {errors}";
			}		
			$err_text = '<table align=center cellpadding=2 cellspacing=0>';
			foreach( $Errors as $m ){
				$err_text .= '<tr><td>'.$m.'</td></tr>';
			}
			$err_text .= '<tr><td>Please <a href="javascript:window.history.go(-1)">go back</a> and make corrections</td></tr>';
			$err_text .= '</table>';
			$display = str_replace('{errors}', $err_text, $display);
			echo $display;
			//var_dump($display);
			exit;
		}
	}

	function spamCheck(){
		foreach ($this->InputValues as $k=>$v){
			//Make sure we don't spam check the url's that are being passed to formgenie on purpose
			if (
				!preg_match("/confirmationpage/", $k) &&
				!preg_match("/emailtemplate/", $k) &&
				!preg_match("/errorurl/", $k) &&
				!preg_match("/baseurl/", $k) &&
				!preg_match("/govpaytemplate/", $k) &&
				!preg_match("/filename/", $k) &&
				!preg_match("/govpay/", $k) &&
				!preg_match("/GPCSessionService/", $k) &&
				!preg_match("/GPGServiceURL/", $k) 

			){
				$tobechecked .= $v;
			}
		}
		
		$sql="SELECT keyword from spamblacklist where 1;";
		$rs =$this->SQL->Run($sql);
		
		while($spam = $this->SQL->fetch($rs)){
		
			$check=strpos($tobechecked, $spam['keyword']);
			if($check){
				return true;
			}
		}
		
		return $this->Sanitize->isSpam($tobechecked);
		/*
		if($this->Sanitize->isSpam($tobechecked)){
				$Errors[] = "";
			}

		
		if( count($Errors)>0 ){
			if( $this->errorurl){
				$display = $this->getUrl($this->errorurl);
			}	
			
			if(!$display){
				$display = "There were errors with your input: {errors}";
			}		
			$err_text = '<table align=center cellpadding=2 cellspacing=0>';
			foreach( $Errors as $m ){
				$err_text .= '<tr><td>'.$m.'</td></tr>';
			}
			$err_text .= '<tr><td>Please <a href="javascript:window.history.go(-1)">go back</a> and make corrections</td></tr>';
			$err_text .= '</table>';
			$display = str_replace('{errors}', $err_text, $display);
			echo $display;
			//var_dump($display);
			exit;
		
		if (count($Errors)>0){
			return true; 
		}
		*/
	}
	
	function sendError($error_msg){
		mail ($this->contactEmail, "FormGenie Error", $error_msg);
	}
	
	function storeData($emaildata){
		//This function will write the data that is to be emailed in a database for reference by the agency or a developer 
		//at a later date.  
		#if (!$this->SQL){
		#	require ('sql_mysql.php');
		#	$this->SQL = new sql_mysql('formgenie', 'formgenie', '', 'proddb');
		#}
		$insert_sql = $this->SQL->makeInsert(
			'formdata',
			array (
				'emaildata' => $emaildata,
				'input_values' => wddx_serialize_value($this->InputValues),
			)
		);
		$this->SQL->Run($insert_sql);
		list ($record_id) = $this->SQL->Run("select last_insert_id() from formdata");
		return $record_id;
	}
	
	function getConfirmationPage(){
		if(!$this->govpay){
			$confirmation_page = $this->parseHTMLTemplate($this->confirmationTemplate);
			return $confirmation_page;
		}
	}
	
	function sendEmail($from ='', $emailaddr ='', $subject ='', $body ='', $data ='', $filename =''){
		require_once('class.html.mime.mail.inc');
		$mail = new html_mime_mail('X-Mailer: Html Mime Mail Class');
		if (($filename) && (!$this->storedFileLink)){
			$mail->add_attachment($data, $filename, 'application/octet-stream');
		}
		
		$mail->add_header("Errors-To: ".$this->contactEmail."\nReturn-Path: ".$this->contactEmail);
		$mail->set_body($body);
		$mail->build_message();
		if (preg_match("/,/", $emailaddr)){
			$email_addrs = split(",", $emailaddr);
			foreach ($email_addrs as $email){
				$mail->send($emailaddr, $email, '', "$from", $subject);
			}
		}else{
			$mail->send($emailaddr, $emailaddr, '', "$from", $subject);
		}

	}
	
	function getUrl($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		if($_SERVER['PHP_AUTH_USER']){
			curl_setopt($ch, CURLOPT_USERPWD, 
				$_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW']);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$error = curl_error($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$data = curl_exec($ch);	
		return $data;
	}
	
	function forwardToGovPay($id){
	
	list($receipt, $total) = $this->makeReceipt();
	
	$sessionId = $this->getGovPaySessionId($total, str_replace("<%% BUTTONS %%>", "", $receipt), $this->VendorID, $this->ServiceCode, $id);	
	
	$form="<form action=\"$this->govpay\" method=\"post\">
		<input type=\"hidden\" name=\"vendor_id\" value=\"$this->VendorID\">
		<input type=\"hidden\" name=\"service_id\" value=\"$this->ServiceCode\">
		<input type=\"hidden\" name=\"ws_session_id\" value=\"$sessionId\">
		<table cellpadding=0 cellspacing=0>
			<tr>
				<td><input type=\"button\" value=\"< Back\" onClick=\"window.history.go(-1)\"></td>
				<td align=center>&nbsp;&nbsp;</td>
				<td align=\"right\"><input type=\"submit\" value=\"Proceed to Check Out >\"></td>
			</tr>
			<tr>
				<td colspan=3 align=center height=\"20\" width=\"556\"></td>
			</tr>
			
				
		</table>	
		</form>";
	
		$receipt = str_replace("<%% BUTTONS %%>", $form, $receipt);
		
		
		if( $this->globaltemplate ){
						
			list($content) = $this->GT->fetchrow("select template from gtemplate where name= '".$this->globaltemplate."' ");
		
			$Output = str_replace('<%% $BODY %%>', $receipt, $content);
			$Output = preg_replace('/<%% \$[A-Z|_]*? %%>/', '', $Output);
			
			echo $Output;
		
		}else{
		
			echo $receipt;
		
		}
		
					
	}
	
	function makeReceipt() {
		
		$extension = substr($this->govpayTemplate, -3, 3);
		
		if (($extension == 'tml') || ($extension == 'htm') || ($extension == 'txt')){
			$emaildata = $this->parseHTMLTemplate($this->govpayTemplate);
		
		}else if ($extension == 'pdf'){
			
			$emaildata = $this->makeFDF($this->govpayTemplateLink);
			$this->emailBody .= "\n\n Click the following link to open the completed form for printing.\n\n";
			$this->emailBody .= $this->storedFileLink.$emaildata;
			
		}
		
		$total = $this->getTotalAmount();
		
		return array($emaildata, $total);
	}
	
	function getGovPaySessionId($total, $receipt, $vendor, $service, $id){
	try{
		
		$obj = new SessionBean();
		$obj->affiliateHashId  =  $this->GPCAffiliateId;
		$obj->amount        =  $total;
		$obj->backUrl       =  $this->baseurl;
		//$obj->backUrlText   =  'Create another order';
		$obj->serviceId     =  $service;
		$obj->transnum		=  $id;
		$obj->text          =  $receipt;
		$obj->vendorId      =  $vendor;	
		if( $_POST['bill_same'] ){
			$obj->address      =  $_POST['ship_address1'];
			$obj->city      =  $_POST['ship_city'];
			$obj->name      =  $_POST['recip_name'];
			$obj->state      =  $_POST['ship_state'];
			$obj->zipcode      =  $_POST['ship_zip'];
		}	
		$client = new SoapClient($this->GPCSessionService, array('trace'=>1));
		$r = $client->getSessionId($obj);
		return $r->sessionNumber;
	}catch (Exception $exception) {
		if(DEMO){
			echo $exception;
			echo '<hr></pre>';
			if($client){
				echo "REQUEST:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
				echo '<hr>';
				echo "RESPONSE:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
			}
		}
		// send an email with the error details
		mail('ora@ark.org', "Error with GPC Session service (FormGenie)", 
			sprintf("Error details:\n%s\n%s\n%s",
				print_r($exception, true),
				$client?htmlspecialchars($client->__getLastRequest()):'',
				$client?htmlspecialchars($client->__getLastResponse()):''), 
			"From: support@ark.org");
		echo "Error";
		
		exit;
	}
	return NULL;
	}

	function getTotalAmount(){
		
		$total=0;
		
		if( is_array($this->_price) ){
			
			if($this->Total){
					$total = $this->Total;
			}
				
			foreach( $this->_price as $k=>$v ){
				/*if(!trim($this->InputValues['{'.$k.'}'])){
					$Errors[] = $v;
				}*/
				
				if(trim($this->InputValues['{'.$k.'}']) && !preg_match('/^[1-9]/', $this->InputValues['{'.$k.'}'])){
					$total += $v;
				}else if($this->InputValues['{'.$k.'}'] >=0){
				
					$total += ($this->InputValues['{'.$k.'}'] * $v);
				}
			}
		}else{
			
			$total = $this->Total;
			
		}
		
		return $total;
	}
	
}

class SessionBean{
	public $affiliateHashId  =  '';
	public $sessionId     =  '';
	public $amount        =  '';
	public $appId         =  '';
	public $payBy         =  '';
	public $backUrl       =  '';
	public $backUrlText   =  '';
	public $outerUniqueId =  '';
	public $serviceId     =  '';
	public $text          =  '';
	public $transnum      =  '';
	public $address      =  '';
	public $city      =  '';
	public $name      =  '';
	public $state      =  '';
	public $zipcode      =  '';
}

?>
