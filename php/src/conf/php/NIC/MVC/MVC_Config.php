<%
	ini_set("include_path", ini_get ("include_path" ) . ":/web/html/development/david/MVC/PEAR/:/web/php/");
	require_once('Config.php');

	class MVC_Config extends Config{

		var $defaultdir='./src/conf/';
		var $livefile='config.xml';
		var $devfile='config.dev.xml';
		var $localfile='config.dev.SERVER.xml';
		var $ConfArray=array();


		function MVC_Config($confdir='', $filename='') {
			$this->confdir=$confdir?$confdir:$this->defaultdir;
			$this->filename=$filename?$filename:$this->livefile;
			$search=$filename?0:1;

			$test=$this->_loadfile($this->confdir.'/'.$this->filename);
			if (!$test) {
				return 0;
			}

			if ($search) {
				# check for a development file
				if (is_readable($this->confdir.'/'.$this->devfile)) {
					$test=$this->_loadfile($this->confdir.'/'.$this->devfile);
				 if (!$test) {
					 return 0;
				 }
				}

				# check for a server-specific file
				$this->localfile=str_replace('SERVER', $_SERVER['SERVER_NAME'], $this->localfile);
				if (is_readable($this->confdir.'/'.$this->localfile)) {
					$test=$this->_loadfile($this->confdir.'/'.$this->localfile);
					if (!$test) {
						return 0;
					}
				}
			}
			return 1;
		}


		function _loadfile($filename) {
			$this->Config();
			if (! is_readable($filename)) {
				$this->Error="Config file $filename not readable";
				return 0;
			}
			$root= & $this->parseConfig($filename, 'XML');
			if (PEAR::isError($root)) {
				$this->Error=$root->message;
				return 0;
			}
			$FileArray=$root->toArray();
			$this->ConfArray=array_merge($this->ConfArray, $FileArray['root']['Config']);
			return 1;
		}


		function Get($field) {
			return $this->ConfArray[$field];
		}
	}
%>
