<?

session_start();

list($usec, $sec) = explode(" ",microtime());
$__STEPS_start_time = ((float)$usec + (float)$sec);


/**
* Steps!
*
* @version  $Id: steps.php,
* @author   INA dev team
* @see  class/function()
*/
	class Steps{

	  /**
		* Description
		*
		* @param    mode
		* @access   public
		* @param    string  name
		* @throw(s) PHPDocError
		* @return    boolean
		*/
		function Steps($mode='', $steplist='', $steps=''){
		  $this->cvsAuthor = '$Author: bob $';
		  $this->cvsDate = '$Date: 2001/09/21 18:29:58 $';
		  $this->cvsId = '$Id: steps.php,v 1.43 2001/09/21 18:29:58 bob Exp $';
		  $this->cvsRevision = '$Revision: 1.43 $';
		  $this->cvsSource = '$Source: /usr/local/cvs/php/Steps/steps.php,v $';

		  global $__STEPS_start_time;
			$this->currentMicroTime = $__STEPS_start_time;
			$this->elapsedTime = 0;

      // these mode settings are required for Trace to work
			$this->Benchmark = $mode['Benchmark']?1:0;
			$this->TraceOn = $mode['trace']?1:0;
			$this->useCustomSessionHandler = $mode['CustomSessionHandler']?true:false;
			$this->TraceMsg = array();
			$this->Trace("Steps init",'start');

			global $HTTP_POST_VARS;
			global $HTTP_GET_VARS;
			global $HTTP_SESSION_VARS;
			global $PHPSESSID;

			$this->P = &$HTTP_POST_VARS;
			$this->G = &$HTTP_GET_VARS;
			$this->S = &$HTTP_SESSION_VARS;

			$this->Funcs = array();

			if($this->P){
				foreach($this->P as $k=>$v){
					if (!is_array($this->P[$k])) {
						$this->P[$k] = stripslashes($v);
					}
				}
			}
			//leave this in???	this has been accomplished by remembering the First Step of the app
			//if frompage is blank then it goes to the first step
			if(!$this->P && !$this->G) {
				 //$this->G['STARTOVER'] = 1;
			}

			// object path
			$this->Path = $mode['Path']?$mode['Path']:getcwd();

			// external objects
			require_once('class.FastTemplate.php3');
			$this->FT = new FastTemplate($this->Path);

			require_once('HTML/input.php');
			$this->Forms = new Forms('','');

			require_once('validfield.php');
			$this->VF = new validfield();

			// some defaults
			$this->defaultTemplate = 'defaultTemplate.html';
			$this->defaultContentTemplate = 'contentWindow.html';
			$this->defaultErrorTemplate = 'errorTemplate.html';
			$this->defaultBack = 'back.gif';
			$this->defaultNext = 'next.gif';
			$this->defaultExit = 'exit.gif';
			$this->defaultExitLink = 'http://www.accessArkansas.org';
			$this->defaultChecked = 'check.gif';
			$this->defaultUnchecked = 'uncheck.gif';
			$this->ErrorColor = 'red';
			$this->requiredImage = 'req.gif';
	 		$this->ContentLable = 'Content';

			// GLOBAL STATE VARS
			$this->StepOrder = array();
			$this->fromPage = '';
			$this->toPage = '';
			$this->action = '';
			$this->Completed = array();
			$this->Enabled = array();
			$this->Errors = array();
			$this->Triggers = array();

			$this->PersistValues = array();

			// set the flags
			// booleans
			$this->Demo = $mode['Demo']?1:0;
			$this->Debug = $mode['Debug']?1:0;
			$this->Multilingual = $mode['Multilingual']?1:0;
			$this->StartOver = $mode['StartOver']?1:0;
			$this->DemoLink = $mode['DemoLink']?1:0;
			$this->Ticket = $mode['Ticket']?$mode['Ticket']:0;
			$this->OverWrite = $mode['OverWrite']?1:0;
			$this->SlimMode = $mode['SlimMode']?1:0;
			$this->noTemplates = $mode['noTemplates']?1:0;
			$this->ProjectID = $mode['ProjectID']?$mode['ProjectID']:'NONE';
			$this->setOnClicks = $mode['setOnClicks']?1:0;
			$this->nativeTemplate = $mode['nativeTemplate']?1:0;
			$this->AppColors = $mode['AppColors']?$mode['AppColors']:array();

			// content based flags
			$this->ContentLable = $mode['ContentLable']?$mode['ContentLable']:$this->ContentLable;
			$this->ErrorColor = $mode['ErrorColor']?$mode['ErrorColor']:$this->ErrorColor;
			$this->BackImage = $mode['BackImage']?$mode['BackImage']:$this->defaultBack;
			$this->NextImage = $mode['NextImage']?$mode['NextImage']:$this->defaultNext;
			$this->TemplateItems  = $mode['TemplateItems']?$mode['TemplateItems']:'';
			$this->FormEnc = $mode['FormEnc']?$mode['FormEnc']:'';


			if (isset($mode['AutoFont']) && !$mode['AutoFont']) {
				$this->AutoFont=0;
			} else {
				$this->AutoFont=1;
			}

			if ($mode['ExitImage'] !== 0){
				$this->ExitImage =	$mode['ExitImage']?$mode['ExitImage']:$this->defaultExit;
				$this->Trace('exitImage',$this->ExitImage);
			}else{
				$this->ExitImage = '';
			}
			$this->ExitLink = $mode['ExitLink']?$mode['ExitLink']:$this->defaultExitLink;
			$this->CheckedImage = $mode['CheckedImage']?$mode['CheckedImage']:$this->defaultChecked;
			$this->UncheckedImage = $mode['UncheckedImage']?$mode['UncheckedImage']:$this->defaultUnchecked;
			$this->requiredImage = $mode['RequireImage']?$mode['RequireImage']:$this->requiredImage;
			$this->PersistVars	= $mode['PersistVars']?$mode['PersistVars']:array();
			$this->ScriptName = $mode['ScriptName'] ? $mode['ScriptName'] : $GLOBALS['SCRIPT_NAME'];
			$this->LogFile = $mode['LogFile'] ? $mode['LogFile'] : NULL;
			$this->AppTitle = $mode['AppTitle']?$mode['AppTitle'] : '';
			$this->StepFormat = $mode['StepFormat']?$mode['StepFormat']:NULL;
			$this->CaptionFormat = $mode['CaptionFormat']?$mode['CaptionFormat']:NULL;
			$this->FontFace = $mode['FontFace']?$mode['FontFace']:'Arial, Verdana, Sans Serif';
			$this->TextFontSize = $mode['FontSize']?$mode['FontSize']:'2';
			$this->InputFontSize = $mode['FontSize']?$mode['FontSize']:'3';
			$this->FontColor = $mode['FontColor']?$mode['FontColor']:'';

			if ($mode['StepsDir']) {
				if (preg_match('/^\//', $mode['StepsDir'])) {
					$this->StepsDir = $mode['StepsDir'];
				} else {
					$this->StepsDir = './'.$mode['StepsDir'];
				}
				if (!preg_match('/\/$/', $mode['StepsDir'])) {
					$this->StepsDir .= '/';
				}
			} else {
				$this->StepsDir = './steps/';
			}

      $this->supressEnterKey = $mode['supressEnterKey']?$mode['supressEnterKey']:NULL;
      $this->supressBrowserCache = $mode['supressBrowserCache']?$mode['supressBrowserCache']:NULL;

			// templates
			$this->ContentTemplate = $mode['ContentTemplate'] ? $mode['ContentTemplate'] : $this->defaultContentTemplate;
			$this->MainTemplate = $mode['MainTemplate'] ? $mode['MainTemplate'] : $this->defaultTemplate;
			$this->ErrorTemplate = $mode['ErrorTemplate'] ? $mode['ErrorTemplate'] : $this->defaultErrorTemplate;
			$this->requiredImage = $mode['RequireImage']?$mode['RequireImage']:$this->requiredImage;

			// these can be changed by GET
			if( $this->G['DEMO'] == 1 ){
				$this->Demo = 1;
				$this->endSession();
				$this->Trace('check1', 'going to demo');
			}

			// if the user wants to start over, kill the session
			if($this->G['STARTOVER']){
				$this->Trace('check2','going to startover');
				$this->endSession();
			}
			// is the template set?
			$this->Template = $template;

			// set steps config
			$this->Steps = $steps;

			$this->Trace("Steps init",'init complete');

			// import any external Steps
			$this->importExternalSteps();

			// set steplist
			if(!$steplist){
				$this->MainSteps = array();
				foreach( $this->Steps as $k=>$v ){
					$this->MainSteps[$k] = $v['Step'];
				}
				$this->usingMetaSteps = 0;
			}else{
				$this->MainSteps = $steplist;
				$this->usingMetaSteps = 1;
			}



			//are we in the same application?
			if ($this->ProjectID == $this->S['ProjectID']){
				$this->Trace('Projectid', 'This app: '.$this->ProjectID . ' In Session: '. $this->S['ProjectID']);
				// are the global vars registered?
				// if( session_is_registered('StepOrder') )
				// yes,
				// $this->Trace('init', "Global vars previously registered");
				// load object variables to the session
				$this->loadVars();

			} else {
				$this->Trace('Projectid', 'This app: '.$this->ProjectID . ' In Session: '. $this->S['ProjectID']);

				// if startover, we have already cleared the old
				if (!$this->G['STARTOVER'] && !$this->G['DEMO']){
					//out with the old....
					$this->Trace('clearing old session data');
					$this->registerVars(1);
				}

				//....in with the new
				$this->registerVars();

				$this->reportUsage();

				// no, create the order list
				$this->createStepOrder();

				// register envelope session vars
				// register global vars
				//register Project ID and set
				$this->setVar('ProjectID', $this->ProjectID);
			}

			$this->getCurrentStep();



			// save object variables to the session
			$this->saveVars();
			$this->processAction();

			$this->StepList = $this->createStepList(
			$this->usingMetaSteps?$this->Steps[$this->toPage]['Step']: $this->toPage,
			$this->MainSteps,
			$this->Enabled,
			$this->Completed
			);

			$this->renderPage();
		}

		function reportUsage(){
		    $this->enterFunc('reportUsage');
//    		    if(! class_exists('ina_translator')){
//    				  include_once('ina_translator_daemon.php');
//    				}
//    				$T = new ina_translator('INA', 'bob');
//    //    				$T->call('reportUsage', ($this->S['ProjectID']=='NONE')?0:$this->ProjectID, 'startapp', $this->DEMO, '', '',
//    //    				$GLOBALS['REMOTE_ADDR'], $GLOBALS['REMOTE_USER'], $GLOBALS['HTTP_USER_AGENT'], $GLOBALS['SCRIPT_FILENAME']?$GLOBALS['SCRIPT_FILENAME']:$GLOBALS['SCRIPT_NAME']);
//
//    				$T->call('reportUsageHash', array(
//    					projectid => ($this->S['ProjectID']=='NONE')?0:$this->ProjectID,
//    					descr => 'startapp',
//    					demo => $this->Demo,
//    					servicecode => '',
//    					descr2 => '',
//    					remoteip =>$GLOBALS['REMOTE_ADDR'],
//    					remoteuser => $GLOBALS['REMOTE_USER'],
//    					useragent => $GLOBALS['HTTP_USER_AGENT'],
//    					scriptname => $GLOBALS['SCRIPT_FILENAME']?$GLOBALS['SCRIPT_FILENAME']:$GLOBALS['SCRIPT_NAME'],
//    				));

		}

		function enterFunc(){

		}

		function importExternalSteps(){
		  $this->enterFunc('importExternalSteps');
		  $this->Trace('importExternalSteps', "Looking for exernal steps");

			foreach($this->Steps as $stepid=>$props){


				if($props['External']){
				  // an external was given
          $this->Trace('importExternalSteps', "Installing $props[External] in $stepid");
	        // attempt to import the file
					if(file_exists(ini_get('include_path') . '/Steps/lib/'.$props['External'].'.cnf.php')){
					  require('Steps/lib/'.$props['External'].'.cnf.php');
						foreach($this->External as $k=>$v){
						  // if the user has defined this, don't override it
							if(!$props[$k]){
								$this->Steps[$stepid][$k] = $v;
							}
						}
					}

					if(file_exists(ini_get('include_path') . '/Steps/lib/'.$props['External'].'.load.php')){
					  $this->Trace('importExternalSteps', "Installing load page");
            $this->Steps[$stepid]['Template'] = ini_get('include_path') . '/Steps/lib/'.$props['External'].'.load.php';
					}



				}

			}

		}

		function saveVars(){
			$this->Trace('saveVars');
 	 		$GLOBALS['StepOrder'] = $this->StepOrder;
			$GLOBALS['Demo'] = $this->Demo;
			$this->S['StepOrder'] = $this->StepOrder;
			$this->S['Demo'] = $this->Demo;

			foreach ($this->PersistVars as $k){
				if (preg_match("/\[\]$/", $k)) {
					$k=substr($k,0,-2);
				}
				$this->S[$k] = $this->PersistValues[$k];
				$GLOBALS[$k] = $this->PersistValues[$k];
			}

			if($this->fromPage){

				// save the current envelope
				if($this->Steps[$this->fromPage]['Envelope']){
					foreach($this->Steps[$this->fromPage]['Envelope'] as $k=>$v){
						if (preg_match("/\[\]$/", $k)) {
							$k=substr($k,0,-2);
						}
						if( is_array($v) ){
							if($v[4] == 'multitext'){
								$this->P[$k]='';
								for($i=0; $i<count($v[5]); $i++){
									$this->Trace('saveVars', "saving $k as ${k}_" . ($i+1));
									$GLOBALS[$k.'_'.($i+1)] = $this->P[$k.'_'.($i+1)];
									$this->S[$k.'_'.($i+1)] = $this->P[$k.'_'.($i+1)];
									$this->P[$k].=$this->P[$k.'_'.($i+1)];
									$this->P[$k].=$v[9][$i];
								}
								$this->S[$k]=$this->P[$k];
								$GLOBALS[$k]=$this->P[$k];
							}elseif($v[4] == 'record'){
								// do not save record items each time
							}else{
								$this->Trace('saveVars 1', "saving $k as " . $this->P[$k]);
								$GLOBALS[$k] = $this->P[$k];
								$this->S[$k] = $this->P[$k];
							}
						}else{
							$this->Trace('saveVars 2', "saving $v as " . $this->P[$v]);
							$GLOBALS[$v] = $this->P[$v];
							$this->S[$v] = $this->P[$v];
						}
					}
				}
			}
		}

		function registerVar($varname, $destroy=0, $value='', $isarray=0){
			if ($isarray) {
				if (!$value) {
					#$value=array();
					$this->Trace('registerVar', "$varname set to $value");
				}
			}
			if($destroy == 1){
				$this->Trace('destroyVar', $varname);
				$GLOBALS[$varname] = '';
				$this->S[$varname] = '';
				session_unregister($varname);
			}else{
				session_register($varname);
			}

			if($value || is_array($value)){
				$this->Trace('registerVar', "Create $varname with $value");
				$GLOBALS[$varname] = $value;
				$this->S[$varname] = $value;
				$this->$varname = $value;
			}

		}

		function destroyVars(){
			$this->Trace ('destroyVars');
			$this->registerVars(1);
	 	}

		function registerEnvelopeVars($destroy=0){
			$this->Trace('registerEnvelopeVars', "Registering Envelope vars");
			foreach( $this->Steps as $k=>$v ){
				if( is_array($v['Envelope']) ){
					foreach($v['Envelope'] as $d=>$d2){
						$disarray=0; // $d is not an array by default
						if (preg_match("/\[\]$/", $d)) {
							$d=substr($d,0,-2);
							$disarray=1; // $d is an array
						}
						if($this->Demo){
							// if this is the demo, load the default value if not set

							if(!$this->S[$d]){
								$val = $this->setInputVal($d, $d2);
								// $this->Trace('registerVars', "registering $d as $val");
								$this->S[$d] = $val;
								$GLOBALS[$d] = $val;

							}
						}

						if( is_array($d2) ){
							if($d2[4] == 'multitext'){
								for($i=0; $i<count($d2[5]); $i++){
									//$this->Trace('init', 'Register: ' . $d . '_' . ($i+1));
									$this->registerVar($d . '_' . ($i+1), $destroy, '', $disarray);


								}
							} // else { - we want to register the multitext itself, too

							$this->registerVar($d, $destroy, '', $disarray);
							// }
						}else{
							$this->registerVar($d2, $destroy, '', $disarray);
						}
					}
				}
			}
		}

		function registerPersistVars($destroy=0){
			$this->Trace('registerPersistVars', "Registering Persistant vars");
			//register persisent vars
			foreach ($this->PersistVars as $k){
				$kisarray=0; // $k is not an array by default
				if (preg_match("/\[\]$/", $k)) {
					$k=substr($d,0,-2);
					$kisarray=1; // $k is an array
				}
				$this->registerVar($k, $destroy, '', $kisarray);
				$this->Trace('register persistant', $k, $destroy);
			}
		}

		function resetEnvelopeVars(){
			$this->Trace('resetEnvelopeVars', "Reseting Envelope vars");
			$this->registerEnvelopeVars(1);
			$this->registerEnvelopeVars();
		}

		function resetPersistVars(){
			$this->Trace('resetPersistVars', "Reseting Persist vars");
			$this->registerPersistVars(1);
			$this->registerPersistVars();
		}
		function registerVars($destroy=0){
			$this->Trace('registerVars', "Registering Session vars");

			$this->registerVar('StepOrder', $destroy);
			$this->registerVar('Demo', $destroy);
			$this->registerVar('ProjectID', $destroy);
			$this->registerPersistVars($destroy);

			$this->registerEnvelopeVars($destroy);


		}


		function loadVars(){
			$this->Trace('loadVars');
			$this->StepOrder = $GLOBALS['StepOrder'];
			$this->Demo = $GLOBALS['Demo'];
			$this->FirstStep = $this->S['FirstStep'];
	 		foreach ($this->PersistVars as $k){
				if (preg_match("/\[\]$/", $k)) {
					$k=substr($k,0,-2);
				}
				$this->PersistValues[$k] = $GLOBALS[$k];
			}
		}


		function setVar($varname, $value){
			//$this->Trace('setVar', 'setting '.$varname.' as '.$value);
			$GLOBALS[$varname] = $value;
			$this->S[$varname] = $value;
		}


		function endSession(){
			$this->Trace('endSession', "Killing session");
			session_unset();
			session_destroy();
			if ($this->useCustomSessionHandler) {
				session_set_save_handler('db_sess_start', 'db_sess_end', 'db_sess_read', 'db_sess_write', 'db_sess_destroy', 'db_sess_gc');
			}
			$this->setVar('PHPSESSID', '');
			session_start();
		}

		function getmicrotime(){
		    list($usec, $sec) = explode(" ",microtime());
		    return ((float)$usec + (float)$sec);
		}

		function Trace($fnc, $msg='', $indent=0){

		  if($this->Benchmark == 1){
			  // get current mt
				$mc = $this->getmicrotime();

				// get the elapsed time
				$el = $mc - $this->currentMicroTime;

				// store the new time
				$this->currentMicroTime = $mc;

				$this->elapsedTime += $el;

				// append the elapsed time and total running time to $msg
				$tm1 = number_format($el, 2);
				$tm2 = number_format($this->elapsedTime,2);
			}else{
			  $tm1 = '';
			  $tm2 = '';
			}

			if($this>TraceOn == 1){
				//$this->TraceMsg[] = str_repeat('&nbsp;', ($indent*4)+1) . '<b>'.$fnc.'</b> ' . $msg;
  			$this->TraceMsg[] = array(
					str_repeat('&nbsp;', ($indent*4)+1) . '<b>'.$fnc.'</b> ',
					$msg,
					$tm1,
					$tm2
				);
			}
		}

		function makeTriggerLink($linkname="link", $trigger, $id, $fromPage){
			$url = '<a href="'.$this->ScriptName.'?fromPage='.$fromPage;
			$url .= '&'.$trigger.$id.'_y='.$id;
			$url .= '">'.$linkname.'</a>';
			return $url;
		}

		function getCurrentStep(){
			$this->Trace('getCurrentStep');

			if ($this->P['fromPage']){
				$this->fromPage = $this->P['fromPage'];
			}elseif ($this->G['fromPage']){
				$this->fromPage = $this->G['fromPage'];
		 	}

			// are we going forward or backward?
			if(!$this->fromPage){
				$this->Trace('first step', $this->FirstStep);
				$this->toPage = $this->FirstStep;
			}

			if( isset($this->P['goBack_y']) ){
				$this->Direction = 'Back';
				$this->toPage = $this->StepOrder[$this->P['fromPage']]['prevStep'];
			}elseif( isset($this->P['goNext_y']) ){
				$this->Direction = 'Next';
				$this->toPage = $this->StepOrder[$this->P['fromPage']]['nextStep'];
			}

			if( isset($this->P['onError']) ){
				$this->fromPage = '';
				$this->toPage = $this->P['onError'];
			}
		}


		function showError($var){
			return $this->font(2, $this->Errors[$var], $this->ErrorColor);
		}


		function Error($msg, $var){
			$this->Trace('Error', $msg);
			$msg = htmlentities($msg);
			if( $var && ($this->Steps[$this->fromPage]['AutoCheck'] == 'Highlight') ){
				$this->Errors[$var] = $msg;
			}else{
				$this->Errors[] = $msg;
			}
		}


		function checkMultiText($name, $v){
			$this->Trace('checkMultiText', "checking $name");
			$er = 0;
			for($i=0; $i<count($v[5]); $i++){
				// is this field not optional?
				if($v[8][$i] != 1){
					$this->Trace('checkMultiText', "$name is not optional");
					if(! $this->P[$name.'_'.($i+1)] && $this->P[$name.'_'.($i+1)] != '0'){
						$this->Trace('checkMultiText', $name.'_'.($i+1).' is not there');
						$er = 1;
						break;
					}

					// is there a required length?
					if( $v[7][$i] ){
						if( strlen($this->P[$name . '_' . ($i+1)]) != $v[7][$i]){
							$this->Trace('checkMultiText', $name.'_'.($i+1).' is not long enough');
							$er = 1;
							break;
						}
					}
					// does a validator need to run?
					if ($v[10][$i]) {
						// did the user make a custom validate method?
						if(method_exists($this, $v[10][$i])) {
							$er = $this->{$v[10][$i]}();
							if ($er) {
								break;
							}
						// is the method in our validate library?
						} elseif (method_exists($this->VF, $v[10][$i]) ){
							 // use it to validate
							 $params=$v[11][$i];
							 array_unshift($params, $this->P[$name.'_'.($i+1)]);

               				 $er = $this->VF->{$v[10][$i]}($params);

							if ($er) {
								break;
							}
						}
					}
				}
			}
			$this->Trace('checkMultiText', "returning $er");
			return $er;
		}


		function AutoCheck($step){
			$this->Trace('AutoCheck', $step);
			// run through required variables for this step
			foreach($this->Steps[$step]['Envelope'] as $k=>$v){
				if( $v[1] != '0' ){
					$er = 0;
					// did the user provide a validate method for this?
					$P = $this->getInputProperties($k, $v);
					$this->Trace('AutoCheck', "check $k for " . $P['validate']);

					if (preg_match("/\[\]$/", $k)) {
						$k=substr($k,0,-2);
						$values=$this->P[$k];
					} else {
						$values=array($this->P[$k]);
					}
					foreach ($values as $value) {
						// did the user make a custom validate method?
						if( method_exists($this, $P['validate'])) {
							$er = $this->{$P['validate']}();
							if ($er) {
								$this->Error($er, $k);
							}
						// is the method in our validate library?
						}elseif( method_exists($this->VF, $P['validate']) ){
							// use it to validate
							$params=$P['params'];
							array_unshift($params, $value);
							$er = $this->VF->{$P['validate']}($params);
							if ($er) {
								$this->Error($er, $k);
							}
						}else{
							// use our generic validator
							// is this a multitext?
							if($v[4] == 'multitext'){
								$er = $this->checkMultiText($k,$v);
							}else{
								if(! $value && $value != '0'){
									$er = 1;
								}
							}

							if($er == '1'){
								if($v[1] == '1'){
									if($P['validate']){
										$this->Error($P['validate'], $k);
									}else{
										$this->Error("Please enter a value for: " . $v[0], $k);
									}
								}else{
									$this->Error($v[1], $k);
								}
							}	elseif ($er) {
								$this->Error($er, $k);
							} // end if($er == 1)
						} // end if( method_exists($this, $P['validate']))
					}
				} // end if( $v[1] != '0' )
			} // end foreach
		} // end function AutoCheck


		function requiredIcon($color='#000000'){
			return '<img src="'.$this->requiredImage.'" alt="Field is Required">';
		}


    function checkTriggerAlternates(){

        foreach($this->Steps[$this->fromPage]['TriggerAlternates'] as $k=>$v){
				  // k=trigger, v=single or array of alternates, any of which will trigger the trigger
					// only one trigger alt will be acknowledged

					// only check if the trigger is not already triggered
				  if( ! $this->Triggers[$k] ){
             foreach($v as $alt=>$val){
						  if( strlen($this->P[$alt])>0 ){
 							  // trigger it! (and stop)
								//$this->Triggers[$k] = $this->P[$val]?$this->P[$val]:1;
								$this->setTrigger($k, $this->P[$val]?$this->P[$val]:1);
							}
						}

					}
				}


		}

		function setTrigger($trigger, $val){
		  $this->Trace('setTrigger', "detected trigger $trigger with $val");
		  $this->Triggers[$trigger] = $val;
		}

    function detectTriggers(){
		  $this->Trace('detectTriggers');
			// was a trigger clicked?
			if( $this->Steps[$this->fromPage]['Triggers'] ){
				foreach($this->Steps[$this->fromPage]['Triggers'] as $t){
					//look in Post vars
					if ($this->P){
						foreach($this->P as $k=>$v ){
							// test for trigger
							if( substr($k, 0, strlen($t)) == $t ){
								$val = substr($k, strlen($t), strlen($k));
	//						echo "($t)($k)(".substr($k, 0, strlen($t)).") = (".substr($k, strlen($t), strlen($k)).") <br>";
								$val = ereg_replace("_[x|y]$", "", $val);
								//$this->Triggers[$t] = $val;
								$this->setTrigger($t, $val);
								// we got a trigger, so set the fromPage
								// run the user function
							}
						}
					}
					//look in Get Vars
					if ($this->G){
						foreach( $this->G as $k=>$v ){
							// test for trigger
							if( substr($k, 0, strlen($t)) == $t ){
								$val = substr($k, strlen($t), strlen($k));
								$val = ereg_replace("_[x|y]$", "", $val); //"
								//$this->Triggers[$t] = $val;
								$this->setTrigger($t, $val);
								// we got a trigger, so set the fromPage
								// run the user function
							}
						}
					}
				}
			}

  		// was a trigger alternate supplied?
      if( $this->Steps[$this->fromPage]['TriggerAlternates'] ){
  			$this->checkTriggerAlternates();
			}
		}

		function detectRecordActions(){
		  $this->Trace('detectRecordActions');
			// was a RECORD action taken?
			// RECORD actions are ADD, EDIT, DELETE
			if ($this->P){
				foreach($this->P as $k=>$v ){
					foreach(array('ADD', 'EDIT', 'DEL') as $action){
						if( substr($k, 0, strlen($action.'RECORD')) == $action.'RECORD' ){
							$val = substr($k, strlen($action.'RECORD'), strlen($k));
							// is there a record associated?
							$rec = strstr($val, '_');
							if($rec){
								$val = substr($val, 0, strlen($val)-strlen($rec));
								$rec = substr($rec, 1, strlen($rec));
							}
							$this->recordAction($action, $val, ltrim($rec));
						}
					}
				}
			}

		}

		function setRequired($step, $var, $req){
		  $this->Trace('setRequired', "setting $var to $req");
		  // you can change if a variable is required,
			//  but it must be done before autocheck is run
			$this->Steps[$step]['Envelope'][$var][1] = $req;

		}

		function callExternal($libName, $funcName){
      $p = func_get_args();
		
			// was an external validation method defined for this step
		  $this->Trace('callExternal', "calling $funcName in $libName with " . count($p) . " params");							
			$libpath = 'Steps/lib/'.$libName.'.lib.php';

			$cmd = '$this->ret = call_user_func($funcName, &$this';
			if(count($p)>2){
				for($i=2; $i<count($p); $i++){
				  $cmd .= ', $p['.$i.']';
				}
			}
			
			$cmd .= ');';
						
			include_once($libpath);	
			eval($cmd);					
			$this->Trace('callExternal', "calling $cmd got ($this->ret)");
			return $this->ret;
			//return call_user_func($funcName, &$this);


		}

		function processAction(){
      // process action does several things
			//   runs beforeAction if defined
			//   checks for Triggers and sets them
			//   checks for Record actions and stores values if necessary
			//   runs Autocheck validation if needed
			//   runs checkSTEP if needed (custom validation)
			//   runs external validation function if defined
			//   IF going forward
			//     run beforeSave
			//     run step saveFunc if defined
			//     run saveSTEP if defined
			//   calls beforeStep if defined
			//   IF an error was registered, redo the step
			//   ELSE
			//     call beforeLoad if defined
			//     call loadFunc for the step if defined
			//     call loadStep if defined


			$this->Trace('processAction');

			// before we do anything else, run beforeAction
			if( method_exists($this, 'beforeAction')){
				$this->Trace('processAction', 'Calling:  beforeAction ');
				$this->{'beforeAction'}();
			}

			// was a trigger set?
			$this->detectTriggers();

			// was a record action taken?
			$this->detectRecordActions();

			// where did we come from?



			// call checkSTEP if it is defined
			if( method_exists($this, 'check'.$this->fromPage) ){
				$func = 'check'.$this->fromPage;
				$this->$func();
			}

			// was an external validation method defined for this step
		  $this->Trace('processAction', "checking for library validation: " . $this->Steps[$this->fromPage]['External']);
			if($this->Steps[$this->fromPage]['External']){
				if(file_exists(ini_get('include_path') . '/Steps/lib/'.$this->Steps[$this->fromPage]['External'].'.validate.php')){
				  $this->Trace('processAction', "using library validation: " . $this->Steps[$this->fromPage]['External']);
				  require(ini_get('include_path') . '/Steps/lib/'.$this->Steps[$this->fromPage]['External'].'.validate.php');
					call_user_func($this->Steps[$this->fromPage]['External'].'_validate', &$this);
				}
			}

			// do autocheck?
			if( $this->Steps[$this->fromPage]['AutoCheck'] ){
				if( $this->Steps[$this->fromPage]['Envelope'] ){
					$this->AutoCheck($this->fromPage);
				}
			}

			// was there an error?

			// save last step


			if ($this->Direction != 'Back'){
			  // dont run save functions if an error was detected
			  if(count($this->Errors) == 0){
					//is there a function to call before each save?
					if( method_exists($this, 'beforeSave')){
						$this->Trace('processAction', 'Calling:  beforeSave ');
						$this->{'beforeSave'}();
					}
					if ($this->Steps[$this->fromPage]['saveFunc'] ){
						if( method_exists($this, $this->Steps[$this->fromPage]['saveFunc']) ){
							$this->Trace('processAction', 'Calling: ' . $this->Steps[$this->fromPage]['saveFunc']);
							$this->{$this->Steps[$this->fromPage]['saveFunc']}();
						}
					}elseif( method_exists($this, 'save' . $this->fromPage) ){
						$this->Trace('processAction', 'Calling: ' . 'save' . $this->fromPage);
						$this->{'save' . $this->fromPage}();
					}
				}
			}

			// if beforeSTEP is defined, call it
			if( method_exists($this, 'before' . $this->toPage)){
				$this->{'before' . $this->toPage}();
			}


			// if there was an error, redo the step
			if( (count($this->Errors) > 0) && ($this->Direction != 'Back') ){
				// redo this step
				$this->toPage = $this->fromPage;
			}

			if($this->Direction == 'Back'){
				$this->Errors = array();
			}

			//is there a function to call before each load?
				if( method_exists($this, 'beforeLoad')){
					$this->Trace('processAction', 'Calling:  beforeLoad ');
					$this->{'beforeLoad'}();
				}
			// is there a load function for the step we are going to?
			if( $this->Steps[$this->toPage]['loadFunc'] ){
				if( method_exists($this, $this->Steps[$this->toPage]['loadFunc']) ){
					$this->Trace('processAction', 'Calling: ' . $this->Steps[$this->toPage]['loadFunc']);
					$this->{$this->Steps[$this->toPage]['loadFunc']}();
				}
			}elseif( method_exists($this, 'load' . $this->toPage) ){
				$this->Trace('processAction', 'Calling: ' . 'load' . $this->toPage);
				$this->{'load' . $this->toPage}();
			}
			// was there an error?
		}


		function add_record($val, $record=''){
			// make a little record based on input
			$rec = array();
			foreach($this->Steps[$this->fromPage]['Envelope'][$val][5] as $k=>$v){
				$rec[$k] = $this->P[$k];
			}

			// add it to this var
			if(! is_array($this->S[$val])){
				$this->S[$val] = array();
				$GLOBALS[$val] = array();
			}

			$this->S[$val][] = $rec;
			$GLOBALS[$val][] = $rec;
		}


		function recordAction($action, $val, $record=''){
			$this->Trace('recordAction', "$action $val $record", 4);

			switch($action){
				case 'ADD':
					if( method_exists($this, 'add_'.$val) ){
						$func = 'add_'.$val;
						$this->$func($val, $record);
					}else{
						$this->add_record($val, $record);
					}
					break;
				case 'EDIT':
					break;
				case 'DEL':
					unset($GLOBALS[$val][$record]);
					unset($this->S[$val][$record]);
					// remove this item from the array
					break;
			}
			$this->toPage = $this->fromPage;
		}


		function createStepOrder(){
			$this->Trace('createStepOrder');
			$temp = array();
			foreach( $this->Steps as $k=>$v ){
				if(! $v['Parent']){
					array_push( $temp, $k);
				}
			}

			for( $i=0; $i<count($temp); $i++ ){
				if($i==0){
					$prevStep = 0;
					$nextStep = $temp[$i+1];
					$this->FirstStep = $temp[$i];
				}elseif($i == (count($temp)-1)){
					$prevStep = $temp[$i-1];
					$nextStep = 0;
					$this->LastStep = $temp[$i];
				}else{
					$nextStep = $temp[$i+1];
					$prevStep = $temp[$i-1];
				}
				$this->StepOrder[$temp[$i]] = array(
					prevStep => $prevStep,
					nextStep => $nextStep,
				);
			}
			$this->registerVar('FirstStep');
			$this->setVar('FirstStep', $this->FirstStep);

			$GLOBALS['StepOrder'] = $this->StepOrder;
		}


		function removeStep($step){
			$this->Trace('removeStep', $step , 2);

			// alter the StepOrder
			$this->Trace('removeStep', 'changing prevstep of '.$this->StepOrder[$step]['nextStep'].' to '.$this->StepOrder[$step]['prevStep'],2);

			$this->StepOrder[$this->StepOrder[$step]['nextStep']]['prevStep'] = $this->StepOrder[$step]['prevStep'];

			// after step must point forward to the old step's next step
			$this->Trace('removeStep', 'changing nextStep of '.$this->StepOrder[$step]['prevStep'].' to '.$this->StepOrder[$step]['nextStep'],2);

			$this->StepOrder[$this->StepOrder[$step]['prevStep']]['nextStep'] = $this->StepOrder[$step]['nextStep'];

			//if we were going to the step being removed, go to the next step
			if ($this->toPage == $step){
				$this->toPage = $this->StepOrder[$step]['nextStep'];
			}
			// get rid of step
			unset($this->StepOrder[$step]);

			//saving StepOrder
			$this->setVar('StepOrder', $this->StepOrder);
		}


		function insertStep($step, $afterstep){
			$this->Trace('insertStep', $step . ' ' . $afterstep, 2);
			//if step is already in Steporder do not insert again
			$stepkeys = array_keys($this->StepOrder);
			if (in_array ($step, $stepkeys)){
				$this->Trace ('insertStep', $step .' already in StepOrder - not added again', 2);
				return 0;
			}

			// alter the StepOrder
			// the next step must point back to the inserted step
			$this->StepOrder[$this->StepOrder[$afterstep]['nextStep']]['prevStep'] = $step;

			// the new step points to both
			$this->StepOrder[$step] = array(
				prevStep => $afterstep,
				nextStep => $this->StepOrder[$afterstep]['nextStep']
			);

			// after step must point forward to new step
			$this->StepOrder[$afterstep]['nextStep'] = $step;

			//saving StepOrder
			$this->setVar('StepOrder', $this->StepOrder);

			// we are now going to this step
			$this->toPage = $step;
		}


		function toStep($step){
			$this->Trace('toStep', $step);
			//$this->toPage = $this->StepOrder[$this->fromPage]['nextStep'];
			$this->toPage = $step;
		}

		function writeLog($input){
			$current_time = date ("Y-m-d H:i:s");

			if (is_array($input)){
				foreach ($input as $k=>$v){
					$statement .= "$k: $v | ";
				}
			}else{
				$statement = $input;
			}
    			$fp = fopen ($this->LogFile, "a");

    			fputs ($fp, $current_time.'| '.$statement."\n");
    			fclose($fp);
    		}


		function createStepList($current, $steps, $enabled, $completed){
			$this->Trace('createStepList');
			$ret = $this->font();
			$i=1;
			foreach( $steps as $k=>$v){
				if($this->StepFormat){
					$v = sprintf($this->StepFormat, $i, $v);
				}
				if($current == $k){
					$ret .= '<b>' . $v . '</b><br>';
					$this->pageIndex = $i;
				}else{
					$ret .= $v . '<br>';
				}
				$i++;
			}

			if($this->StartOver == 1){
				$ret .= '<bR><a href="'.$this->ScriptName.'?STARTOVER=1">Start Over</a><br>';
			}
			if($this->DemoLink == 1){
				$ret .= '<a href="'.$this->ScriptName.'?DEMO=1">Demo</a><br>';
			}
			return $ret;
		}


		function getContentWindow($step){
			$this->Trace('getContentWindow', $step);

			// is there a template defined for this step?
			if( (count($this->Errors) > 0) && ($this->Steps[$this->fromPage]['AutoCheck'] != 'Highlight') ){
				// show error page and list errors
				return 0;
			}elseif( file_exists($this->StepsDir . $this->toPage . '.php') ){
				ob_start();
				ob_implicit_flush(0);
				include_once($this->StepsDir . $this->toPage . '.php');
				$Content = ob_get_contents();
				ob_end_clean();
				return $Content;
			}elseif( $this->Steps[$step]['Template'] ){
				// start ob buffer grab
				// require the page
				ob_start();
				ob_implicit_flush(0);
				if( file_exists($this->StepsDir . $this->Steps[$step]['Template']) ){
				  include_once($this->StepsDir . $this->Steps[$step]['Template']);
				}else if( file_exists($this->Steps[$step]['Template']) ){
          include_once($this->Steps[$step]['Template']);
				}else{
				  $this->Error('Could not locate template file: ' . $this->Steps[$step]['Template'], '');
				}

				$Content = ob_get_contents();
				ob_end_clean();
				return $Content;
			}else{
				// no template defined, so make our own
				if($this->Steps[$step]['Envelope']){
					return $this->makePage($step);
				}
			}

			// is there a template for content windows?
			if( $this->ContentTemplate ){
			}else{
				// use the default
			}
			return $ret;
		}

//	0					1				 2				 3				4						5				6					 7				 8
//	text_name, required, demo_def, default, type
//																					text				 size,		max,				validate, error,		extra
//																					textarea		 cols,		rows,			 wrap,		 validate, error, extra
//																					radio				value,	 validate,	 error,		extra
//																					checkbox		 value		validate,	 error,		extra
//																					hidden
//																					select			 hash,		size,			 multiple
//																					yesno				Yesval,	noval,
//																					selectState
//																					selectCounty
//																					selectMonth	prompt, abbrev
//																					multitext		sizes, max, reqlen, optionals, separators

		function getInputProperties($name, $I){
			$ar = array(
				text_name => $I[0],
				required	=> $I[1],
				demo_def	=> $I[2],
				live_def	=> $I[3],
				type			=> $I[4],
			);

			switch($I[4]){
				case 'text':
					$ar['size']     = $I[5];
					$ar['max']      = $I[6];
					$ar['validate'] = $I[7];
					$ar['params']   = array_slice($I,8);
					break;
				case 'textarea':
					$ar['cols']     = $I[5];
					$ar['rows']     = $I[6];
					$ar['wrap']     = $I[7];
					$ar['validate'] = $I[8];
					$ar['params']   = array_slice($I,8);
					break;
				case 'radio':
					$ar['value']    = $I[5];
					$ar['validate'] = $I[6];
					$ar['params']   = array_slice($I,7);
					break;
				case 'checkbox':
					$ar['value']    = $I[5];
					$ar['validate'] = $I[6];
					$ar['params']   = array_slice($I,7);
					break;
				case 'hidden':
					break;
				case 'select':
					$ar['hash']     = $I[5];
					$ar['size']     = $I[6];
					$ar['mult']     = $I[7];
					$ar['validate'] = $I[8];
					$ar['params']   = array_slice($I,9);
					break;
				case 'yesno':
					$ar['yesval']   = $I[5];
					$ar['noval']    = $I[6];
					$ar['validate'] = $I[7];
					$ar['params']   = array_slice($I,8);
					break;
				case 'selectState':
					$ar['prompt']   = $I[6];
					$ar['abbrev']   = $I[7];
					$ar['validate'] = $I[8];
					$ar['params']   = array_slice($I,9);
					break;
				case 'selectCounty':
					$ar['prompt']   = $I[6];
					$ar['abbrev']   = $I[7];
					$ar['validate'] = $I[8];
					$ar['params']   = array_slice($I,9);
					break;
				case 'selectMonth':
					$ar['prompt']   = $I[6];
					$ar['abbrev']   = $I[7];
					$ar['validate'] = $I[8];
					$ar['params']   = array_slice($I,9);
					break;
				case 'ssn':
					break;
				case 'multitext':
					$ar['sizes']     = $I[5];
					$ar['max']       = $I[6];
					$ar['reqlen']    = $I[7];
					$ar['optionals'] = $I[8];
					$ar['separators']= $I[9];
					$ar['validate']  = $I[10];
					$ar['params']    = $I[11];
					break;
				case 'record':
					$ar['parts']    = $I[5];
					break;
				default:
					break;
				}
			return $ar;
		}


		function makeRecordInput($name, $prop){
			$ret = '<table border="1">';

			foreach($prop['parts'] as $k=>$v){
				$p = $this->getInputProperties($k, $v);
				$ret .= '<tr>
					<td>'.$this->font('2', $p['text_name']).'</td>
					<td>'.$this->makeInput($k, $v, '').'</td>
					</tr>';
			}

			$ret .= '<tr>
				<td colspan="2">'.$this->submit('ADDRECORD'.$name, 'Add ' . $prop['text_name']).'</td>
				</tr>
				</table>';

			// make the list of records if there are records
			$ret .= $this->makeRecordList($name, $prop);
			return $ret;
		}


		function makeRecordList($name, $properties=''){
			if(!$prop){
				$prop = $this->getInputProperties($name, $this->Steps[$this->toPage]['Envelope'][$name]);
			}

			if( count($this->S[$name]) > 0){
				$ret .= '<table border="1">';
				$ret .= '<tr>';
				foreach($prop['parts'] as $k=>$v){
					$p = $this->getInputProperties($k, $v);
					$ret .= '<td><b>'.$this->font(2, $p['text_name']).'</tD>'."\n";
				}
				$ret .= '</tr>';

				foreach($this->S[$name] as $i=>$rec){
					$ret .= '<tr>';
					foreach($prop['parts'] as $k=>$v){
						if(!$rec[$k]){$rec[$k]='&nbsp;';}
						$ret .= '<td>'.$this->font(2, $rec[$k]).'</tD>'."\n";
					}
					$ret .= '<td>'.$this->submit('EDITRECORD'.$name.'_'.$i, 'Edit').'</td>';
					$ret .= '<td>'.$this->submit('DELRECORD'.$name.'_'.$i, 'Delete').'</td>';
					$ret .= '</tr>';
				}
				$ret .= '</table>';
			}
			return $ret;
		}


		function submit($name, $value, $image=''){
			if($image){
				return '<input type="image" name="'.$name.'" value="'.$value.'" src="'.$image.'">';
			}else{
				return '<input type="submit" name="'.$name.'" value="'.$value.'">';
			}
		}


		function href($text='', $param=array()){
			$link_script='';
			$req = array(
				fromPage => $this->fromPage,
			);
			$link_script .= '?';

			foreach($req as $k=>$v){
					$link_script .= urlencode($k) . '=' . urlencode($v) . '&';
			}

			if(count($param)>0){
				foreach($param as $k=>$v){
					$link_script .= urlencode($k) . '=' . urlencode($v) . '&';
				}
			}
			return '<a href="'.$link_script.'">'.$text.'</a>';
		}


		function makeInput($name, $I='', $val='', $clean=''){
			if($clean){
				$val = htmlentities($val, ENT_QUOTES, 'ISO-8859-1');
			}

			//$P = getInputProperties($name, $I);
			if ($I || $I == '0') {
				$key=$I;
			}
			if (preg_match("/\[\]$/", $name)) {
				$htmlname=substr($name,0,-2)."[$key]";
			}
			$I = $this->Steps[$this->toPage]['Envelope'][$name];
			if($val == ''){
				$val = $this->setInputVal($name,$I,$key);
			}

			if ($htmlname) {
				$name=$htmlname;
			}

			switch($I[4]){
				case 'text':
					$ret = $this->Forms->makeText($name, $I[5], $I[6], $val, $I[7], $I[8], $I[9]);
					break;
				case 'textarea':
					$ret = $this->Forms->makeTextarea($name, $I[5], $I[6], $val, $I[7]);
					break;
				case 'radio':
					if (!$key) {
						$key=$I[5];
					}
$this->Trace('makeradio',"make with name=$name value=$key current=$val", 2);
					$ret = $this->Forms->makeRadio($name, $key, $val);
					break;
				case 'checkbox':
					$hide=0;
					if ($this->Demo) {
						$def=$I[2];
					} else {
						$def=$I[3];
					}
					if (is_array($def)) {
						$hide=$def[$key];
					} else {
						$hide=$def;
					}
					if ($hide) {
						$ret = $this->Forms->makeHidden($name, '0');
					} else {
						$ret='';
					}
					$ret .= $this->Forms->makeCheckbox($name, $I[5], $val);
					break;
				case 'hidden':
					$ret = $this->Forms->makeHidden($name, $val);
					break;
				case 'select':
					$ret = $this->Forms->makeSelect($name, $I[5], $I[6], $val, $I[7]);
					break;
				case 'yesno':
					$ret = $this->Forms->makeYesNo($name, $I[5], $I[6], $val);
					break;
				case 'selectState':
					$ret = $this->Forms->selectState($name, $val);
					break;
				case 'selectCounty':
					$ret = $this->Forms->selectCounty($name, $val);
					break;
				case 'selectMonth':
					$ret = $this->Forms->selectMon($name, $val, $I[5], $I[6]);
					break;
				case 'ssn':
					$ret = $this->Forms->makeSSN($name, $val, NULL, NULL, '');
					break;
				case 'multitext':
					$ret = $this->Forms->makeMultiText($name, $I[5], $val, $I[6], $I[7], $I[9], $I[10], $I[11]);
					break;
				case 'record':
					$ret = $this->makeRecordInput($name, $this->getInputProperties($name, $I));
					break;
				default:
					$ret = $this->Forms->makeText($name, 25, NULL, $val);
					break;
			}
			if ($this->AutoFont) {
				return $this->font($this->InputFontSize, $ret);
			} else {
				return $ret;
			}
		}


		function setInputVal($name, $v, $key=''){
			if($this->Demo == 1){
				 // Demo mode
				$useDef = $v[2];
			}else{
				// Non-demo mode
				$useDef = $v[3];
			}

			if($v[4] == 'multitext'){
				// multi text is the special case
				// did the user enter anything in any of the fields?
				$didEnter = 0;
				$val = array();
				for($i=0; $i<count($v[5]); $i++){
					if( isset($this->P[$name.'_'.($i+1)]) ){
						//$this->Trace('setInputVal', "setting ".$name.'_'.($i+1)." to ".$GLOBALS[$name.'_'.($i+1)]." " . count($useDef));
						$didEnter=1;
						$val[] = $GLOBALS[$name.'_'.($i+1)];
					}
				}
				if($didEnter == 0){
					// supply the defaults
					$val = $useDef;
					//$this->Trace('setInputVal', 'setting test to default' . count($val));
				}

			}else{
				// normal inputs
				if (preg_match("/\[\]$/", $name)) {
					$name=substr($name,0,-2);
					if( isset($GLOBALS[$name][$key]) ){
						$val = $GLOBALS[$name][$key];
					}else{
						if (is_array($useDef)) {
							$val = $useDef[$key];
						} else {
							$val = $useDef;
						}
					}
				} else {
					if( isset($GLOBALS[$name]) ){
						$val = $GLOBALS[$name];
					}else{
						$val = $useDef;
					}
				}
			}
			return $val;
		}


	 function makePage($step){
			$this->Trace('makePage', $step);

			// run through the Envelope
			$ret = "\n<!-- BEGIN $step -->\n" . '<table>';

			foreach( $this->Steps[$step]['Envelope'] as $k=>$v ){
				if( is_array($v) ){
					$name = $v[0];
					$val = $this->setInputVal($k,$v);
					$input = $this->font(2, $this->makeInput($k, $v, $val));

					if($v[1] != '0'){
						$req = $this->requiredIcon();
					}else{
						$req = '';
					}

					$er = $this->font(2, $this->Errors[$k], $this->ErrorColor);

					if( ($v[4] == 'checkbox') || ($v[4] == 'radio') ){
						$label = '';
						$input .= $this->font(2, $name);
					}else if($v[4] == 'hidden'){
						 $label = '';
					}else{
						$label = $this->font(2, $name);
					}

				}else{
					$val = $GLOBALS[$v];
					$name = $v;
					$req = '';
					$input = $this->Forms->makeText($v, 25, NULL, $val);
					$er = $this->font(2, $this->Errors[$k], $this->ErrorColor);
					$label = $this->font(2, $name);
				}

				$ret .= '<tr>
					<td>'.$label.'</td>
					<td>'.$req.'</td>
					<td>'.$input.'</td>
					<td>'.$er.'</td>
				 </tr>';
			}

			$ret .= '</table>' . "\n<!-- END $step -->\n";

			if ($this->Steps[$step]['Triggers']){
				$ret .= '<table>';
				foreach ($this->Steps[$step]['Triggers'] as $t){
					$ret .= '<tr><td>';
					$ret .= $this->makeTriggerLink($t, $t, 1, $step);
					$ret .= '</td></tr>';
				}
				$ret .= '</table>';
			}

			if($this->OverWrite == 1){
				$this->createStepFile($step, &$ret);
			}
			return $ret;
		}


		function createStepFile($step, $content){
			$this->Trace('createStepFile', "Writing Step: $step", 2);

			$thisDir = $this->StepsDir;

			$thisFile = $step . '_generated.php';

			if( ! is_dir($thisDir) ){
				$this->Trace('createStepFile', $thisDir . ' does not exist, creating', 2);
				mkdir($thisDir, 0777) or $this->Trace('createStepFile', 'could not create ' . $thisDir, 2);
			}

			if( is_writeable($thisDir) ){
				$fd = fopen($thisDir . $thisFile, "w");
				if(!$fd){
					$this->Trace('createStepFile', 'could not write to ' . $thisDir . $thisFile, 2);
				}else{
					fputs($fd, $content);
					fclose($fd);
				}
			}else{
				$this->Trace('createStepFile', $thisDir . $thisFile . ' is not writeable', 2);
			}
		}


		function font($size='', $data='', $color=''){
			if (!$size) {
				$size=$this->TextFontSize;
			}
			if($color){
				$color = "color=\"$color\"";
			}
			$ret = "<font face=\"".$this->FontFace."\" $color size=\"$size.\">";

			if($data){
				$ret .= $data;
			}
			$ret .= '</font>';
			return $ret;
		}



		function debug(){
			$this->Trace('debug');

			?>


			<form>
			  <table border=0>
				  <tr>
					  <td>Debug mode:</td>
						<td>
						  <table width="100%" cellpadding=0 cellspacing=0 border=0>
							  <tr>
								  <td nowrap><b>From:</b> <?=$this->fromPage?$this->fromPage:'none'?>  &nbsp; &nbsp; &nbsp;
									<b>To:</b> <?=$this->toPage?$this->toPage:'none'?></td>
									<td align="right" nowrap><a href="<?=$this->script?>?STARTOVER=1">Start over</a></td>
								</tr>
							</table>
						</td>
					</tr>

				  <tr>
					  <td>Location:</td>
					  <td><?=$this->Path?></td>
					</tr>
				  <tr>
					  <td>Main Page file:</td>
					  <td><?=$this->MainTemplate?></td>
					</tr>
				  <tr>
					  <td>Content window template</td>
					  <td><?=$this->ContentTemplate?></td>
					</tr>
				  <tr>
					  <td>Content window:</td>
					  <td>
						    <?
								  if($this->Steps[$this->toPage]['Template']){
									  echo $this->Steps[$this->toPage]['Template'];
									}else if(file_exists('steps/'.$this->toPage.'.php')){
									  echo 'steps/'.$this->toPage.'.php';
									}else{
									  echo 'none';
									}
						    ?>
						</td>
					</tr>
				  <tr>
					  <td>Steps dir</td>
					  <td><?=$this->StepsDir?></td>
					</tr>
				  <tr>
					  <td>Include path</td>
					  <td><?=ini_get('include_path')?></td>
					</tr>
					<?= $this->debug_showVars("Session vars", &$this->S); ?>
					<?= $this->debug_showVars("Triggers", &$this->Triggers); ?>
					<?= $this->debug_showVars("Post vars", &$this->P); ?>
					<?= $this->debug_showVars("Get vars", &$this->G); ?>
					<?= $this->debug_showVars("Step Order", &$this->StepOrder, 1); ?>

				</table>

			<?

			if($this->TraceOn == 1){
				echo '<hr>Trace<br>';
				echo '<table boder=1>';
				foreach($this->TraceMsg as $msg){
				  echo '<tr>
					        <td>'.$msg[0].'</td>
					        <td>'.$msg[1].'</td>
					        <td>'.$msg[2].'</td>
					        <td>'.$msg[3].'</td>
					      </tr>';
				}
				echo '</table>';
				//echo join("<br>\n", $this->TraceMsg);
			}




			?>
			  <hr>
			   <table>
				   <tr><td><font face="arial" size="2"><b>Version information:</td></tr>
				   <tr><td><font face="arial" size="2"><?=str_replace('$', '', $this->cvsAuthor)?></td></tr>
				   <tr><td><font face="arial" size="2"><?=str_replace('$', '', $this->cvsDate)?></td></tr>
				   <tr><td><font face="arial" size="2"><?=str_replace('$', '', $this->cvsId) ?></td></tr>
				   <tr><td><font face="arial" size="2"><?=str_replace('$', '', $this->cvsRevision)?></td></tr>
				   <tr><td><font face="arial" size="2"><?=str_replace('$', '', $this->cvsSource)?></td></tr>
				 </table>
				 </form>
			<?

		}

		function debug_showVars($name, &$vars, $expand=0){
      ?>
				  <tr>
					  <td><?=$name?> (<?=count($vars)?>):</td>
					  <td>
						<? if(count($vars)>0): ?>
						<select>
						<?
								foreach($vars as $k=>$v){
									echo '<option>'."$k = $v".'</option>';
									if(is_array($v) && $expand){
									  foreach($v as $k2=>$v2){
										  echo '<option> &nbsp; &nbsp; '."$k2 = $v2".'</option>';
										}
									}
								}
						?>
						</select>
						<? else: ?>

						<? endif;?>
						</td>
					</tr>
			<?
		}

		function startCapture(){
	    ob_start();
	    ob_implicit_flush(0);
		}

		function endCapture(){
	    $page = ob_get_contents();
	    ob_end_clean();
			return $page;
		}

		function printTemplate($templatefile, $vars){
		  $this->Trace('printTemplate' , $templatefile . ' with ' . count($vars) . ' vars');

			//foreach($vars as $k=>$v){
			//  echo "$templatefile($k)(".substr(htmlspecialchars($v), 0, 50).")<br>";
			//}

			if($this->nativeTemplate){
			  $this->startCapture();
				foreach($vars as $k=>$v){
					$$k = $v;
				}
				require($this->Path . '/' . $templatefile);
				$page = $this->endCapture();

			}else{
		    $tpl=new FastTemplate( $this->Path );

		  	$tpl->define(array(
		   	 "tt"=>$templatefile
		  	));

		    $tpl->assign($vars);

		    $tpl->parse("GLOBAL", "tt");

		    $page = $tpl->FastPrint(NULL, 1);
			}
			return $page;
		}

		function makeHiddens(){
			$this->Trace('makeHiddens');
			$ret = $this->Forms->makeHidden('fromPage', $this->toPage) . "\n";

			if( (count($this->Errors)>0) && ($this->Steps[$this->fromPage]['AutoCheck'] != 'Highlight') ){
				$ret .= $this->Forms->makeHidden('onError', $this->fromPage) . "\n";
			}
			return $ret;
		}


		function renderPage(){
			if($this->noTemplates == 1){
				// the load function is the output
				 if($this->TraceOn == 1){
					$this->Debug();
				 }
				return 0;
			}

			$this->Trace('renderPage');

			if ($this->Ticket) {
				$vars=serialize($this->S);
				$vars=urlencode($vars);
				$tname="ticket".$this->Ticket.$GLOBALS['PHPSESSID'];
				$fp=fopen("/tmp/$tname","w");
				fwrite($fp,$vars);
				fclose($fp);
				$ticket="http://www.ark.org/support/public.php?f=1&projectid=" . $this->Ticket . "&vars=$tname";
			} else {
				$ticket='';
			}
			
			$vars = array(
        'StepList' => $this->StepList,
				AppTitle => $this->AppTitle,
				TicketLink => $ticket,
				AppTitle => $this->AppTitle,				
				$this->ContentLable => $this->ContentWindow(),
			);

			if (is_array($this->TemplateItems)) {
				foreach ($this->TemplateItems as $k=>$v) {
					$vars[$k] = $v;
				}
			}

			$page = $this->printTemplate($this->MainTemplate, $vars);

      if($this->supressBrowserCache){
			  $sup = $this->supressBrowserCache();
				$page = eregi_replace('<head>', "<head>\n$sup\n", $page);
			}

			echo $page;
			if($this->setOnClicks){
		    $this->setOnClicks();
			}


			if($this->TraceOn == 1){
				$this->Debug();
			}
		}

    function setOnClicks(){
		  ?>
					<SCRIPT LANGUAGE="JavaScript1.1">

					function right(e) {
						//alert(window.event.srcElement.name);
						prompt(window.event.srcElement.name,window.event.srcElement.name);
					}

					function setOnClicks(){
						//alert(document.forms[0].elements.length);
						for(i=0; i<document.forms[0].elements.length; i++){
						//  alert("set " + document.forms[0].elements[i].name);
						  document.forms[0].elements[i].onclick=right;
						}
					}

					setOnClicks();
					</script>
      <?
		}

		function listErrors(){
			$this->Trace('listErrors');
			$ret = '<ul>';
			foreach($this->Errors as $e){
				$ret .= '<li>'.$e.'</li>';
			}
			$ret .= '</ul>';
			return $ret;
		}

  	function supressBrowserCache(){
       $ret = '<META http-equiv="Expires" content="0">'."\n";
       $ret .= '<META http-equiv="Pragma" content="no-cache">'."\n";
 		   return $ret;
		}

		function ContentWindow(){
			$this->Trace('ContentWindow');

			$templateVars = array();
			$templatefile = '';
			$assign=array();

			if($this->CaptionFormat){
				$this->Steps[$this->toPage]['Caption'] = sprintf($this->CaptionFormat, $this->pageIndex, $this->Steps[$this->toPage]['Caption']);
			}

			if ($this->Steps[$this->toPage]['BackButton']) {
				$this->BackImage=$this->Steps[$this->toPage]['BackButton'];
			}
			if ($this->Steps[$this->toPage]['NextButton']) {
				$this->NextImage=$this->Steps[$this->toPage]['NextButton'];
			}

			if ($this->Ticket) {
				$vars=serialize($this->S);
				$vars=urlencode($vars);
				$tname="ticket".$this->Ticket.$GLOBALS['PHPSESSID'];
				$fp=fopen("/tmp/$tname","w");
				fwrite($fp,$vars);
				fclose($fp);
				$ticket="http://www.ark.org/support/public.php?f=1&projectid=" . $this->Ticket . "&vars=$tname";
			} else {
				$ticket='';
			}

			if($this->Demo == 1){
				$this->Steps[$this->toPage]['Caption'] = 'DEMO<bR>' . $this->Steps[$this->toPage]['Caption'];
			}

			if( (count($this->Errors)>0) && ($this->Steps[$this->fromPage]['AutoCheck'] != 'Highlight') ){

			  $templatefile = $this->ErrorTemplate;

				$templateVars = array(
					$this->ContentLable => $this->listErrors(),
					BackButton => '<input name="goBack" type="image" src="'.$this->BackImage.'" border="0" alt="Go to the Previous Step">',
				);

				$this->ContentTemplate = $this->ErrorTemplate;
			}else{

				$templatefile = $this->ContentTemplate;

				if($this->Steps[$this->toPage]['Comments']){
					$this->Steps[$this->toPage]['Comments'] = '<br>' . $this->Steps[$this->toPage]['Comments'];
				}
				if (!$this->StepOrder[$this->toPage]['nextStep']){
					$assign=array(
						Caption => $this->Steps[$this->toPage]['Caption'],
						$this->ContentLable => $this->getContentWindow($this->toPage),
						Comments => $this->Steps[$this->toPage]['Comments'],
						TicketLink => $ticket,
						AppTitle => $this->AppTitle,
						BackButton => '',
						NextButton => $this->ExitImage?'<a href="'.$this->ExitLink.'"><img name="exit" src="'.$this->ExitImage.'" alt="Exit Application" border="0"></a>':'',
					);
				 }else{
					$assign=array(
						Caption => $this->Steps[$this->toPage]['Caption'],
						$this->ContentLable => $this->getContentWindow($this->toPage),
						Comments => $this->Steps[$this->toPage]['Comments'],
						TicketLink => $ticket,
						AppTitle => $this->AppTitle,
						BackButton => $this->StepOrder[$this->toPage]['prevStep'] ? '<input name="goBack" type="image" src="'.$this->BackImage.'" alt="Go to the Previous Step" border="0">' : '',
						NextButton => $this->StepOrder[$this->toPage]['nextStep'] ? '<input name="goNext" type="image" src="'.$this->NextImage.'" alt="Go to the Next Step" border="0">' : '',
					);
				}

				if (is_array($this->TemplateItems)) {
					foreach ($this->TemplateItems as $k=>$v) {
						$assign[$k] = $v;
					}
				}
				$templateVars = $assign;
			}


			$content = $this->printTemplate($templatefile, $templateVars);

			if ($this->FormEnc) {
				$enc='enctype="'.$this->FormEnc.'"';
			} else {
				$enc='';
			}
			if (ereg('/web/html/',$this->Path)) {
				return '<script language="JavaScript" src="http://www.ark.org/js/validfield.js"></script>' .
					'<form action="'.$this->ScriptName.'" method="post" '.$enc.'>' .
					$this->makeHiddens() . $content . '</form>';
			} else {
				return '<script language="JavaScript" src="https://www.ark.org/js/validfield.js"></script>' .
					'<form action="'.$this->ScriptName.'" method="post" '.$enc.'>' .
					$this->makeHiddens() . $content . '</form>';
			}
		}



	function fancyTable($title, $cols='', $rows='', $extra='', $borders=''){

    $tableHeaderBackGround  = $this->AppColors['tableHeaderBackGround'] ? $this->AppColors['tableHeaderBackGround'] : 'black';
		$tableHeaderFont        = $this->AppColors['tableHeaderFont'] ? $this->AppColors['tableHeaderFont'] : '<font face="arial" size="2" color="white">';
		$cellBackground         = $this->AppColors['cellBackground'] ? $this->AppColors['cellBackground'] : 'white';
  	$cellFont               = $this->AppColors['cellFont'] ? $this->AppColors['cellFont'] : '<font face="arial" size="2">';
		$cellBackground2        = $this->AppColors['cellBackground2'] ? $this->AppColors['cellBackground2'] : 'white';
		$cellFont2              = $this->AppColors['cellFont2'] ? $this->AppColors['cellFont2'] : '<font face="arial" size="2">';


		if($borders){
			$borders = ' bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF"';
			$border=1;
		}else{
			$border=0;
		}

?>
	<table border="0" bgcolor="<?=$tableHeaderBackGround?>">
		<? if($title): ?>
			<? if(is_array($extra)): ?>
				<tr>
					<td><?=$tableHeaderFont ?><b><? echo $title ?></b></font></td>
					<td align="right"><font face="arial" size="3">
						<? for($i=0; $i<count($extra); $i++): ?>
							<? echo $extra[$i] . ' '; ?>
						<? endfor; ?>
					</font></td>
				</tr>
			<? else: ?>
				<tr>
					<td width="100%"><?=$tableHeaderFont ?><b><? echo $title ?></b></font></td>
				</tr>
			<? endif; ?>
		<? endif; ?>
		<? if( is_array($rows)): ?>
		<tr>
			<td width="100%" bgcolor="#FFFFFF" <? if(is_array($extra)){echo 'colspan="2"'; } ?>>
				<table border="<? echo $border ?>" cellpadding="3" cellspacing="0">
					<? if( is_array($cols) ): ?>
					<tr>
						<? for($i=0; $i<count($cols); $i++): ?>
							<td bgcolor="<?=$tableHeaderBackGround?>" <? echo $borders ?>><?=$tableHeaderFont?><b><? echo $cols[$i] ?></font></td>
						<? endfor; ?>
					</tr>
					<? endif; ?>
						<? for($i=0; $i<count($rows); $i++): ?>
							<tr>
								<? for($j=0; $j<count($rows[$i]); $j++): ?>
									<td<? echo $borders ?>><font face="arial" size="2"><? echo $rows[$i][$j] ?></font></td>
								<? endfor; ?>
							</tr>
						<? endfor; ?>
				</table>
			</td>
		</tr>
		<? endif; ?>
	</table>
<?
	}
}




?>
