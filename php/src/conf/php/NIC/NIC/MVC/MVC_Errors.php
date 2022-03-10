<%
	class MVC_errors {
	
		var $Level=771; # Errors & Warnings: 1+2+256+512
		var $Notify='0';
		var $VarDump=0;
		var $BackTrace=0;
		var $Error='';
		var $FailOnWarn=0;

/*
	error levels:
		1    => 'Error',
		2    => 'Warning',
		4    => 'Parsing Error',
		8    => 'Notice',
		16   => 'Core Error',
		32   => 'Core Warning',
		64   => 'Compile Error',
		128  => 'Compile Warning',
		256  => 'User Error',
		512  => 'User Warning',
		1024 => 'User Notice',
	
	descriptions:
		8 - Notices are not printed by default, and indicate that the script encountered something that could indicate an error, but could also happen in the normal course of runnning a script.

		4 - Parse errors should only be generated by the parser.

		2 - Warnings are printed but default, but do not interrupt script execution. These inidicate a problem that should have been trapped by the script before tha call was made.

		1 - Errors are also printed by default, and execution of the script is halted after the function returns.

Note: The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR and E_COMPILE_WARNING .

*/

		/*
			user passes in an array of settings:
				Notify: 0=default logging (usually in phperrlog)
								1=email notification
								2=use debugger (not enabled in here)
								3=use log file
				Email: email address to get errors; only used for Notify == 1
				LogFile: name of error log file; only used for Notify == 3
				Level: what errors should be handled
				Handler: optional user-specified error handling function
				VarDump: boolean if the developer wants variables dumped on error
				BackTrace: boolean if the developer wants a backtrace on error
				FailOnWarn: boolean if PHP warnings should cause the app to stop
		*/
		function MVC_errors($settings) {
			$this->Level=$settings['Level']? $settings['Level']: $this->Level;
			error_reporting($this->Level);
			$this->Notify=$settings['Notify']? $settings['Notify']: $this->Notify;
			$this->VarDump=$settings['VarDump']? $settings['VarDump']: $this->VarDump;
			$this->BackTrace=$settings['BackTrace']? $settings['BackTrace']: $this->BackTrace;
			$this->FailOnWarn=$settings['FailOnWarn']? $settings['FailOnWarn']: $this->FailOnWarn;

				# to do: option for logging to a database
			switch ($this->Notify) {
				case 1:	# email errors
					if ($settings['Email']) {
						$this->Destination=$settings['Email'];
					} else {
						$this->Destination='root@ark.org';
					}
					break;

				case 3:	# log errors
					if ($settings['LogFile']) {
						$this->Destination=$settings['LogFile'];
						if (! is_writeable($this->Destination)) {
							$this->Error=$this->Destination.' is not writable';
						}
					} else {
						$this->Error="Must supply LogFile for error file logging";
					}
					break;
			}

			if ($settings['Handler']) {
				set_error_handler($settings['Handler']);
			} else {
				$this->BuiltInHandler=1;
				set_error_handler(array(&$this, 'error_handler'));
			}

			if ($this->Error) {
				return 0;
			} else {
				return 1;
			}
		}


		function log($message) {
			if ($this->VarDump) {
				ob_start();
				print_r($vartext);
				$vars=ob_get_contents();
				ob_end_clean();
				$message.="\tVar dump:\n$vars\n";
			}
			if ($this->BackTrace) {
				ob_start();
				debug_print_backtrace();
				$trace=ob_get_contents();
				ob_end_clean();
				$message.="\tBack Trace:\n$trace\n";
			}
			return error_log($message, $this->Notify, $this->Destination);
		}


		function error_handler($code, $message, $file, $line, $vars) {
			$datetag=date('Y-m-d H:i:s');
			switch ($code) {
				case 256:
					if ($this->Notify) {
						$msg="\n$datetag\n\t$file [line $line]\n\tFatal error code $code\n\t$message\n";
						$this->log($msg);
						$this->_fail();
					}
					break;

				case 2:
				case 512:
					if ($this->Notify) {
						$msg="\n$datetag\n\t$file [line $line]\n\t$message\n";
						$this->log($msg);
						if ( $this->FailOnWarn ) {
							$this->_fail();
						}
					}
					break;

				case 8:
				case 1024:
					if ($this->Notify) {
						$msg="\n$datetag\n\t$file [line $line]\n\t$message\n";
						#$this->log($msg);
					}
					break;
			}
			return $code;
		}


		function setViewer(&$Viewer) {
			$this->Viewer=$Viewer;
			if ($this->BuiltInHandler) {
				set_error_handler(array(&$this, 'error_handler'));
			}
		}


		function _fail() {
			while (ob_end_clean());
			if ( $this->Viewer ) {
				$this->Viewer->showFatal();
			}
			exit;
		}

	}
%>
