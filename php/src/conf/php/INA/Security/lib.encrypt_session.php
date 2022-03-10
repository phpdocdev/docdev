<%
/*
	This library provides for storing session data encrypted in MySQL
*/

	require_once('DB.php');
	define('DB_SESSION_DEFAULT_DSN', 'mysqli://session:phpsess@proddb/php_sessions');
#	define('DB_SESSION_DEFAULT_DSN', 'mysqli://session:phpsess@db.dev/php_sessions');
	define('DB_SESSION_PASSWORD_FILE', '/etc/aes_payment.txt');
	$datestr=date('Ymd');
	define('DB_SESSION_LOG_FILE', "/var/log/httpd/sessions/session_$datestr.log");
	define('DB_SESSION_EXPIRATION', 720); # in minutes
	$session_db='';


	function db_sess_start($path, $sess_name) {
		global $session_db;
		__db_sess_connect();
		return true;
	}


	function db_sess_end() {
		global $session_db;
		if ($session_db && !PEAR::isError($session_db)) {
			$session_db->disconnect();
		}
		return true;
	}


	function db_sess_read($sess_id) {
		global $session_db;
# 		__db_sess_connect();
		$password=mysql_real_escape_string(__db_sess_getPassword());
		$sess_id=mysql_real_escape_string($sess_id);
		$sql="select aes_decrypt(session_data, '$password') from sessions where session_id='$sess_id' and expiration > now()";
		$data=$session_db->getOne($sql);
# 		__db_sess_disconnect();
		return $data;
	}


	function db_sess_write($sess_id, $data) {
		global $session_db;
# 		__db_sess_connect();
		$password=mysql_real_escape_string(__db_sess_getPassword());
		$sess_id=mysql_real_escape_string($sess_id);
		$data=mysql_real_escape_string($data);
		$encrypt_sql="select aes_encrypt('$data', '$password')";
		$enc_data=$session_db->getOne($encrypt_sql);
		if (PEAR::isError($enc_data)) {
			__db_sess_writeLog("Could not encrypt data: ".$enc_data->getMessage());
# 			__db_sess_disconnect();
			die;
		}
		$enc_data=mysql_real_escape_string($enc_data);
		$sql="replace into sessions (session_id, session_data, expiration) values ('$sess_id', '$enc_data', DATE_ADD(NOW(), INTERVAL ".DB_SESSION_EXPIRATION." MINUTE))";
		$res=$session_db->query($sql);
		if (PEAR::isError($res)) {
			__db_sess_writeLog("Could not insert session data: ".$res->getMessage());
			__db_sess_disconnect();
			die;
		}
# 		__db_sess_disconnect();
		return true;
	}


	function db_sess_destroy($sess_id) {
		global $session_db;
# 		__db_sess_connect();
		$sql="delete from sessions where session_id='$sess_id'";
		$res=$session_db->query($sql);
		if (PEAR::isError($res)) {
			__db_sess_writeLog("Could not delete session data: ".$res->getMessage());
			__db_sess_disconnect();
			die;
		}
# 		__db_sess_disconnect();
		return true;
	}


	function db_sess_gc($lifetime) {
		global $session_db;
# 		__db_sess_connect();
		$sql="delete from sessions where expiration < now()";
		$res=$session_db->query($sql);
		if (PEAR::isError($res)) {
			__db_sess_writeLog("Could not clean up session data: ".$res->getMessage());
# 			__db_sess_disconnect();
			die;
		}
# 		__db_sess_disconnect();
		return true;
	}


	function __db_sess_connect($dsn='') {
		global $session_db;
		$dsn=$dsn?$dsn:DB_SESSION_DEFAULT_DSN;
		$session_db=DB::connect($dsn, array('persistent' => true));
		if (PEAR::isError($session_db)) {
			__db_sess_writeLog("Could not connect to $dsn: ".$session_db->getMessage());
			die;
		}
		$session_db->setFetchMode(DB_FETCHMODE_ORDERED);
	}


	function __db_sess_disconnect() {
		global $session_db;
		$session_db->disconnect();
	}


	function __db_sess_writeLog($msg) {
		$logfile=fopen(DB_SESSION_LOG_FILE, 'a+');
		fwrite($logfile, date('r')." - $msg\n");
		fclose($logfile);
	}


	function __db_sess_getPassword() {
		$password_lines=file(DB_SESSION_PASSWORD_FILE);
		$password=trim($password_lines[0]);
		if (!$password) {
			__db_sess_writeLog("Could not get password from ".DB_SESSION_PASSWORD_FILE);
			die;
		}
		return $password;
	}

	register_shutdown_function('session_write_close');
	session_set_save_handler('db_sess_start', 'db_sess_end', 'db_sess_read', 'db_sess_write', 'db_sess_destroy', 'db_sess_gc');
	
%>
