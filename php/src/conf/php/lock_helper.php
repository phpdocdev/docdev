<?
/**
 * Runaway script protection
 *
 * Uses combination of a lock file + process id to determine if another instance
 * of the process is already running and prevents execution if another instance
 * is found.
 *
 * Automatically creates/deleted lock files as needed, and notifies developer if
 * process has been running too long.
 *
 * To use, simply require 'lock_helper.php' at top of your php script.
 * Optionally, you can define LF_NAME, LF_MAXAGE, LF_EMAIL, LF_VERBOSE before
 * including lock_helper.php to over-ride default settings for these values.
 * See constant definitions below for more info.
 *
 * All function / constant names are prefixed with 'LF_' to avoid collision with calling script.
 */

/*
Pseudocode:
IF lockfile exists
	if lockfile is too old
		alert developers
	else
		get contents of lockfile
		if PID is still running
			exit
		else
			rm lockfile
			create lockfile with current PID
else
	create lockfile with current PID
*/


// Set default values for constants
if (!defined('LF_NAME')) define('LF_NAME', 'PID.LOCK') ;		// Name of lock file
if (!defined('LF_MAXAGE')) define('LF_MAXAGE', 120) ;			// Max age of lock file, in minutes
if (!defined('LF_EMAIL')) define('LF_EMAIL', 'neo@ark.org') ;	// Who should be notified?
if (!defined('LF_VERBOSE')) define('LF_VERBOSE', false) ;		// Should we output messages?

// Get currently executing script name.
if (!file_exists($argv[0])){
	define('LF_SCRIPT_NAME', $argv[0]) ;
}else{
	define('LF_SCRIPT_NAME', getcwd() . DIRECTORY_SEPARATOR . $argv[0]) ;
}

// Get info about current lock file, if one exists.
$LF_Info = LF_Info() ;
if ($LF_Info){ // Lock file exists
	$create_dt = $LF_Info['create_dt'] ;
	$pid = $LF_Info['pid'] ;

	// Is the previous process still running?
	if (LF_isRunning($pid)){
		$now = time() ;
		$diff = ($now - $create_dt) / 60 ;
		LF_echo ("Time diff: $diff") ;
		if ($diff > LF_MAXAGE){
			// Process has been running to long.  Notify developer.
			LF_Notify($create_dt, $diff, $pid) ;
			LF_echo("Process $pid has been running too long") ;
		}
		exit ;
	}else{
		// Previous execution is finished.  Delete old lock file and create new one with current PID
		LF_Delete() ;
		LF_echo ('Deleting old lock file') ;
		$pid = LF_Create() ;
		LF_echo('Creating lock file ' . LF_NAME . ' with PID ' . $pid) ;
	}

}else{ // No lock file.  Create new one with current PID
	$pid = LF_Create() ;
	LF_echo('Creating lock file ' . LF_NAME . ' with PID ' . $pid) ;
}


/**
 * Return PID and creation date of lock file
 * @param void
 * @return mixed array of create_dt pid or false
 */
function LF_Info(){
	if (file_exists(LF_NAME)){
		$create_dt = filectime(LF_NAME) ;
		$file = file(LF_NAME) ;
		$pid = $file[0] ;
		
		return array('create_dt'=>$create_dt, 'pid'=>$pid) ;
	}else{
		return false ;
	}
}

/**
 * See if specified PID is running
 * @param int $pid Process ID of the previous script.
 * @return bool
 */
function LF_isRunning($pid){
	$cmd = "ps -p $pid" ;
	exec($cmd, $output) ;

	$found = false ;
	if ($output[1]){
		preg_match('/'.$pid.'/', $output[1], $matches) ;
		if ($matches[0] == $pid){
			$found = true ;
		}
	}
	if ($found){
		LF_echo("Process $pid is running") ;
	}else{
		LF_echo("Process $pid is not running") ;
	}
	return $found ;
}

/**
 * Notify developers of runway script.
 * @param int $last_run timestamp for when lock file was created
 * @param float $difference between previous execution and current time, in minutes
 * @return bool
 */
function LF_Notify($last_run, $run_time, $pid){

	$mail_body[] = 'The following cron job has not terminated after ' . round($run_time,0) . ' minutes' ;
	$mail_body[] = LF_SCRIPT_NAME ;
	$mail_body[] = 'Max run time for this job is ' . LF_MAXAGE . ((LF_MAXAGE == 1) ? 'minute' : 'minutes');
	$body = join("\n", $mail_body) ;
	$status = mail(LF_EMAIL, 'Runaway CRON Job: ' . LF_SCRIPT_NAME, $body, 'FROM: support@ark.org') ;
	return $status ;
}

/**
 * Delete previous lock file
 * @param void
 * @return bool $status
 */
function LF_Delete(){
	$status = false ;
	if (file_exists(LF_NAME)){
		$status = unlink(LF_NAME) ;
	}
	
	if ($status){
		LF_echo("Deleted LF_NAME") ;
	}else{
		LF_echo("Problem deleting LF_NAME") ;
	}
	return $status ;
}

/**
 * Create new lock file
 * @param void
 * @return int $pid PHP's Process ID
 */
function LF_Create(){
	if (!DEFINED('PID')){
		$pid = getmypid() ;
	}else{
		$pid = PID;
	}
	$fh = fopen(LF_NAME, 'w') ;
	fwrite($fh, $pid) ;
	return $pid ;
}

/**
 * Conditionally print messages.
 * @param string $msg Message to print.
 * @return void
 */
function LF_echo ($msg){
	if (defined('LF_VERBOSE') && LF_VERBOSE == true){
		echo $msg . "\n" ;
	}
}
?>