<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class INA_Controller extends Controller {
	
	function __construct(){
		parent::__construct();

		global $user;
		$data = array (
			'flash' => flash(),
			'user'	=> $user
		);//default vars
		
		$this->load->vars($data);
	}
}