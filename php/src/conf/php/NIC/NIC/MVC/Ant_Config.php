<?
	class Ant_Config {

		var $defaultdir='./src/conf/';
		var $ConfArray=array();
		var $livefile = 'conf.properties';

		function Ant_Config($confdir='', $filename='') {
			$this->confdir=$confdir?$confdir:$this->defaultdir;
			$this->filename=$filename?$filename:$this->livefile;

/* 			$this->_loadfile('NIC/MVC/conf/conf-prod.properties'); */
/* 			$this->_loadfile('NIC/MVC/conf/conf-test.properties'); */
/* 			$this->_loadfile('NIC/MVC/conf/conf-dev.properties'); */
/* 			$this->_loadfile('NIC/MVC/conf/conf-'.$_SERVER['SERVER_NAME'].'.properties'); */
/*  */
/* 			$this->_loadfile($this->confdir.'/conf-prod.properties'); */
/* 			$this->_loadfile($this->confdir.'/conf-test.properties'); */
/* 			$this->_loadfile($this->confdir.'/conf-dev.properties'); */
/* 			$this->_loadfile($this->confdir.'/conf-'.$_SERVER['SERVER_NAME'].'.properties'); */

			$this->_loadfile($this->confdir.'/'.$filename);

		}

		function _loadfile($filename) {
			
			$file=@join('', @file($filename, 1));
			$file = str_replace("\r\n", "\n", $file);
			$file = str_replace("\r", "\n", $file);
			$lines = split("\n", $file);
			
			if(count($lines)>1){
				$GLOBALS['FW_STATE']['Config'][] = "Ant_Config loaded $filename ".count($lines);
				
				foreach ($lines as $line) {
					if(!$line){ continue; }
					$line = trim($line);
					list($key, $value) = split('=', $line,2);
					
					$key = trim($key);
					$value = trim($value);
					if(!$key){ continue; }
					
					#echo "($key)($value)<br>";
					$this->ConfArray[$key]=$value;
				}
			}

		}

		function Get($field) {
			return $this->ConfArray[$field];
		}
	}
?>