<?
/*
	This library provides for encrypting data using the standard password
*/

require_once('DB.php');
define('DB_ENCRYPT_DEFAULT_DSN', 'mysqli://arportal:arportal@db.dev.ark.org/php_sessions');
define('DB_ENCRYPT_PASSWORD_FILE', '/etc/aes_payment.txt');
$datestr=date('Ymd');
define('DB_ENCRYPT_LOG_FILE', "/var/log/httpd/sessions/encrypt_$datestr.log");

global $encrypt_db;
__db_encrypt_connect();


function db_decrypt($enc_data) {
	global $encrypt_db;
	$password=mysql_real_escape_string(__db_encrypt_getPassword());
	$enc_data=mysql_real_escape_string($enc_data);
	$sql="select aes_decrypt('$enc_data', '$password')";
	$data=$encrypt_db->getOne($sql);
	if (PEAR::isError($data)) {
		__db_encrypt_writeLog("Could not decrypt data: ".$data->getMessage());
# 			$encrypt_db->disconnect();
		return false;
	}
# 		$encrypt_db->disconnect();
	return $data;
}


function db_encrypt($data, $escaped=true) {
	global $encrypt_db;
# 		__db_encrypt_connect();
	$password=mysql_real_escape_string(__db_encrypt_getPassword());
	$data=mysql_real_escape_string($data);
	$encrypt_sql="select aes_encrypt('$data', '$password')";
	$enc_data=$encrypt_db->getOne($encrypt_sql);
	if (PEAR::isError($enc_data)) {
		__db_encrypt_writeLog("Could not encrypt data: ".$enc_data->getMessage());
# 			$encrypt_db->disconnect();
		return false;
	}
	if ($escaped) {
		$enc_data=mysql_real_escape_string($enc_data);
	}
# 		$encrypt_db->disconnect();
	return $enc_data;
}


function __db_encrypt_connect($dsn='') {
	global $encrypt_db;
	$dsn=$dsn?$dsn:DB_ENCRYPT_DEFAULT_DSN;
	$encrypt_db=DB::connect($dsn, array('persistent' => true));
	if (PEAR::isError($encrypt_db)) {
		__db_encrypt_writeLog("Could not connect to $dsn: ".$encrypt_db->getMessage());
		die;
	}
	$encrypt_db->setFetchMode(DB_FETCHMODE_ORDERED);
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
