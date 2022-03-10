<?php

define('LDAP_PROD_HOST', 'ldap.ark.org:389');
define('LDAP_APPDEV_HOST', 'ldap.ark.org:389');

/** Example usage

$ldap = new Ldap_access(array('ldap_host' => LDAP_APPDEV_HOST, 'ldap_rootdn' => 'o=INA,c=US'));

$result = $ldap->authenticate('jay', 'jl10615B');
print_r($result); # returns user object if successful

***/

class Ldap_access {
	protected $ldap_host;
	protected $ldap_rootdn;
	protected $ds;
	protected $debug = FALSE;

	/*
	 * @param $param: array('ldap_rootdn', 'ldap_host')
	 */ 
	function __construct($param) {
		extract($param);#extract params to local vars

		$this->ldap_host = $ldap_host;
		$this->ldap_rootdn = $ldap_rootdn;				
		$this->ds = NULL;
		$this->debug = $ldap_demo;
	}
	
	function connect(){
		if (!$this->ds)
			$this->ds = ldap_connect($this->ldap_host);
	}
	
	/*
	 * @param $group = name of group without 'cn=', can also be an array
	 */
	function authenticate($uid, $pass, $group = NULL){
		if ($this->debug) return $this->get_dummy_user($group);#for those without ldap
		
		$this->connect();
		$dn = $this->ldap_rootdn;
		
		if (!$uid || !$pass)
			return 'Username and password must both be set.';

		// find the user in LDAP
		$sr = ldap_search($this->ds, $this->ldap_rootdn, '(uid='.$uid.')', array('*')); //, 200, 30);			

		if (!$sr) return FALSE;
		
		$entry = ldap_first_entry($this->ds, $sr);
		list($info) = ldap_get_entries($this->ds, $sr);

		if (!$entry) return FALSE;
		
		$info = $this->format_user_info($info);
		$udn = ldap_get_dn($this->ds,$entry); 

		//if ($group == NULL) return $info;
		if ( $group == NULL ){
			$ret = @ldap_bind($this->ds, $udn, $pass);
			return $ret ? $info : FALSE;
		}

		$inGroup = false;

		if ( is_array($group) ){
			foreach($group as $gr){
				$gr = 'cn='.$gr;#prepend cn=
				if ($inGroup)
					continue;
														
				$r = @ldap_compare($this->ds, "$gr,$dn", 'uniquemember', $udn);
				ldap_error($this->ds);
				if( $r === TRUE ) {
					$inGroup 	 = true;
					$info->group = $gr;#add group to user object
				}
			}			
		} else if ($group){
			$group = 'cn=' . $group;
			$r = @ldap_compare($this->ds, "$group,$dn", 'uniquemember', $udn);
			
			if ( $r === TRUE ) {
				$inGroup	 = true;
				$info->group = $group;#add group to user object
			}			
		}
		
		if ( $inGroup ){
			$ret = @ldap_bind($this->ds, $udn, $pass);
			return $ret ? $info : FALSE;
		}			
		
		return FALSE;		
	}

	protected function format_user_info($info){
		$user = new stdClass();

		#$user->name		  = trim("{$user->first_name} {$user->last_name}");
		list($user->first_name, $user->last_name) = Ldap_access_helper::get_name_parts($info['cn'][0]);
		
		$user->name		  = $info['cn'][0];
		$user->email	  = $info['mail'][0];
		$user->dn 		  = $info['dn'];
		$user->username   = $info['uid'][0];
		
		return $user;
	}
	
	protected function get_dummy_user($group){
		$user = new stdClass();
		$user->first_name = 'John';
		$user->last_name  = 'Doe';
		$user->name 	  = sprintf("%s %s", $user->first_name, $user->last_name);
		$user->email 	  = 'Email';
		$user->dn 		  = 'uid=john,ou=Customers,o=INA,c=US';
		$user->username   = 'johndoe';
		
		$user->group	  = is_array($group) ? $group[0] : $group;#add first group for debugging
		
		return $user;
	}
}

class Ldap_access_helper {
	function get_name_parts($name){
		if (strstr($name, ' ')) {
			$name_parts = explode(' ', $_POST['m_attn'], 2);
			
			if (is_array($name_parts)) {
				$first_name = $name_parts[0];
				$last_name  = $name_parts[1];	
			}		
		}
		else
			$first_name = $name;
			
		return array($first_name, $last_name);
	}
}