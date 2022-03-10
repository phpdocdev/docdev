<?
/*
	This library provides for encrypting data using the standard password
*/

    $conn_info = array('host'=>'db.dev','user'=>'arportal','pass'=>'arportal') ;
	define('DB_ENCRYPT_PASSWORD_FILE', '/etc/aes_payment.txt');
	
	$datestr=date('Ymd');
	define('DB_ENCRYPT_LOG_FILE', "/var/log/httpd/sessions/encrypt_$datestr.log");
    
	global $encrypt_db;
	__db_encrypt_connect($conn_info);


	function db_decrypt($enc_data) {
		global $encrypt_db;
		$password=mysql_real_escape_string(__db_encrypt_getPassword());
		$enc_data=mysql_real_escape_string($enc_data);
		$decrypt_sql="select aes_decrypt('$enc_data', '$password') as 'data'";

		$res = mysql_query($decrypt_sql, $encrypt_db) ;

		echo '<pre>';
		print_r($res);
		exit(__FILE__ . ' on Line ' . __LINE__);
		if (!$res){
		  __db_encrypt_writeLog("Could not decrypt data: " . mysql_error()) ;
    		return false ;
		} else {
    		$data=mysql_result($res, 0) ;
            return $data ;
		}
	}

	function db_encrypt($data, $escaped=true) {
		global $encrypt_db;
		$password=mysql_real_escape_string(__db_encrypt_getPassword());
		$data=mysql_real_escape_string($data);
        $encrypt_sql="select aes_encrypt('$data', '$password') as 'data'";

		$res = mysql_query($encrypt_sql, $encrypt_db) ;

		if (!$res){
		  __db_encrypt_writeLog("Could not encrypt data: " . mysql_error()) ;
    		return false ;
		} else {
		    $enc_data = mysql_result($res, 0) ;
            if ($escaped){
                $enc_data = mysql_real_escape_string($enc_data) ;
            }
            return $enc_data ;
		}
	}

	function __db_encrypt_connect($dsn=false) {
		global $encrypt_db;

		$encrypt_db = mysql_connect($dsn['host'],$dsn['user'], $dsn['pass']) ;

        if (!$encrypt_db){
			__db_encrypt_writeLog("Could not connect to " . $dsn['host']) ;
			die;
		}
	}


	function __db_encrypt_writeLog($msg) {
		$logfile=fopen(DB_ENCRYPT_LOG_FILE, 'a+');
		fwrite($logfile, date('r')." - $msg\n");
		fclose($logfile);
	}


	function __db_encrypt_getPassword() {
		$password_lines=file(DB_ENCRYPT_PASSWORD_FILE);
		$password=trim($password_lines[0]);
		if (!$password) {
			__db_encrypt_writeLog("Could not get password from ".DB_ENCRYPT_PASSWORD_FILE);
			die;
		}
		return $password;
	}
?>
