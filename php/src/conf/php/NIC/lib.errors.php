<?

function APP_ERROR_SET_EMAIL($to, $subject){
	define("APP_MAIL_ERRORS_TO", $to);
	define("APP_MAIL_ERRORS_SUBJECT", $subject);
}

function APP_SET_ERROR_LOG($filename){
	define("APP_LOG_ERRORS_TO", $filename);
}

function APP_SET_LOG($filename){
	define("APP_LOG_ACTIVITY_TO", $filename);
}


function APP_HANDLE_FATAL_ERRORS($msg){
	
	if(APP_MAIL_ERRORS_TO){
		mail(APP_MAIL_ERRORS_TO, APP_MAIL_ERRORS_SUBJECT, date("Y-m-d H:i:s") . ' ' . $msg);
	}
	
	if(APP_LOG_ERRORS_TO){
		$fp = @fopen(APP_LOG_ERRORS_TO, "a");		
		if($fp){
			fputs($fp, date("Y-m-d H:i:s") . ' ' . $msg."\n");
			fclose($fp);
		}
	}
	
	echo "An error has occurred. An administrator has been notified. Please return later.";
	echo "\n\n<!-- $msg -->\n\n";
	exit;
}

function APP_LOG_MSG($msg){
	if(APP_LOG_ACTIVITY_TO){
		$fp = @fopen(APP_LOG_ACTIVITY_TO, "a");		
		if($fp){
			fputs($fp, date("Y-m-d H:i:s") . ' ' . $msg."\n");
			fclose($fp);
		}
	}	
}
?>