<?
class Controller{

	function Controller(){
		$this->action_key = 'do';
		$this->action_delim = ':';
		
		//index.php?do=actionname
		//<input type=submit name="do:actionname"

	}
	
		
	function start(){
	
		$GLOBALS['FW_STATE']['Controller'][] = "Controller starting on host ".$_SERVER['SERVER_NAME'] . ' with PHP version ' . phpversion();

		if( $_REQUEST[FW_STATE_WIN] ){
			?>
				FW_STATE
				<form>
					<input type=text name=blah value="1">
				</form>
			<?
			exit;
		}
	
		// look in _REQUEST for actions
		list($action, $param) = $this->_interpret_request();
		
		// dispatch the action
		$this->_dispatch($action, $param);
		
	}

	function _interpret_request(){
		// scan for actions in _POST, _GET, _REQUEST
		
		// action examples
		//   index.php?do=action_name
		//   input type=submit name="do:action_name" value="Click Here"
		//   input type=image name="do:action_name" value="Click Here"
		
		if( $_REQUEST[$this->action_key] ){
			// this is easy, probably a get or simple post
			return array($_REQUEST[$this->action_key], NULL);
			//return array($_REQUEST[$this->action_key]);
		}else{
			// scan INPUT for keys matching actions
			foreach($_REQUEST as $k=>$v){

				if( substr($k, 0, strlen($this->action_delim)+strlen($this->action_key)) == $this->action_key.$this->action_delim ){
					$k = preg_replace("/_x|_y$/", "", $k);
					// found an action, extract the name and params
					list($do, $action, $param) = split($this->action_delim, $k);
					return array($action, $param);
				}
			}
		}
	
		return array('no_action', NULL);		
	
	}	
	
	function _dispatch($action, $param=''){
		// look for an existing function matching this action and run it
		// else, attempt to throw an error to the viewer
		// else, die gracefully with raiseError
		
		$GLOBALS['FW_STATE']['Controller'][] = "Controller dispatching $action";
		
		if( method_exists($this, $action) ){
			return call_user_func( array(&$this, $action), $param );
		}else{
		  // pass to global error handling
		  echo "Bad method: $action<br>";
		  exit;
		}
		
	}	
	

}
?>