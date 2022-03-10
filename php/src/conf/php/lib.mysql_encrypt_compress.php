<?
/*
	This library provides for encrypting data using the standard password
*/

	require_once('DB.php');
	define('DB_ENCRYPT_DEFAULT_DSN', 'mysql://arportal:arportal@db.dev.ark.org/php_sessions');
	define('DB_ENCRYPT_PASSWORD_FILE', '/etc/aes_payment.txt');
	$datestr=date('Ymd');
	define('DB_ENCRYPT_LOG_FILE', "/var/log/httpd/sessions/encrypt_$datestr.log");

	global $encrypt_db;
	__db_encrypt_connect();


	function db_decrypt($enc_data, $sql_log=false, $compressed=false) {
		global $encrypt_db;
		$password=mysql_real_escape_string(__db_encrypt_getPassword());
		$enc_data=mysql_real_escape_string($enc_data);
		if($compressed){
			$sql="select uncompress(aes_decrypt('$enc_data', '$password'))";
		}else{
			$sql="select aes_decrypt('$enc_data', '$password')";
		}
		$data=$encrypt_db->getOne($sql);
		if ($sql_log) {
			$fp=fopen('/tmp/decryption.log', 'a+');
			fputs($fp, "$sql\n");
			ob_start();
			print_r($data);
			$var=ob_get_clean();
			fputs($fp, "\ndecrypted var: $var\n");
			fclose($fp);
		}
		if (PEAR::isError($data)) {
			__db_encrypt_writeLog("Could not decrypt data: ".$data->getMessage());
			return false;
		}
		echo '<pre>';
		print_r($data);
		exit(__FILE__ . ' on Line ' . __LINE__);
		return $data;
	}

	function db_encrypt($data, $escaped=true, $compress=false) {
		global $encrypt_db;
		$password=mysql_real_escape_string(__db_encrypt_getPassword());
		$data=mysql_real_escape_string($data);
		if($compress){
			$encrypt_sql="select aes_encrypt(compress('$data'), '$password')";		
		}else{
			$encrypt_sql="select aes_encrypt('$data', '$password')";
		}
		$enc_data=$encrypt_db->getOne($encrypt_sql);
		if (PEAR::isError($enc_data)) {
			__db_encrypt_writeLog("Could not encrypt data: ".$enc_data->getMessage());
			return false;
		}
		if ($escaped) {
			$enc_data=mysql_real_escape_string($enc_data);
		}
		return $enc_data;
	}


	function __db_encrypt_connect($dsn='') {
		global $encrypt_db;
		$dsn=$dsn?$dsn:DB_ENCRYPT_DEFAULT_DSN;
		$encrypt_db=DB::connect($dsn);
		if (PEAR::isError($encrypt_db)) {
			__db_encrypt_writeLog("Could not connect to $dsn: ".$encrypt_db->getMessage());
			die;
		}
		$encrypt_db->setFetchMode(DB_FETCHMODE_ARRAY);
	}


	function __db_encrypt_writeLog($msg) {
		echo "$msg";
//		$logfile=fopen(DB_ENCRYPT_LOG_FILE, 'a+');
//		fwrite($logfile, date('r')." - $msg\n");
//		fclose($logfile);
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
