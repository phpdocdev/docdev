<?php
class ldap{
	
	var $ldap_ds;
	var $lres;
	
	function ldap($ldap_server, $admin=0, $pwfile='', $base_dn= '') {
		$this->$ldap_ds=ldap_connect("$ldap_server");
		$this->base_dn = "o=INA, c=US";
  		if ($this->$ldap_ds) { 
			if ($admin) {
				$adminname="cn=Directory Manager";
				$passfile=fopen($pwfile, "r");
				$adminpass=fread($passfile);
				$bind=ldap_bind($this->$ldap_ds, $adminname, $adminpass);
			} else {
      			$bind=ldap_bind($this->$ldap_ds); 
			}
			if (!$bind) {
				return 0;
			}
			return $this->$ldap_ds;
  		} else {
      		return 0;
  		}
	}
	
	function addUser($uid, $pass='', $group='customer', $ou='Customers') {
		$this->lres=array();
		if ($pass) {
			exec("adduser -c -u $uid -p $pass -g $group -o $ou", $this->lres, $err);
		} else {
			exec("adduser -c -u $uid -g $group -o $ou", $this->lres, $err);
		}
		if ($err) {
			return 0;
		} else {
			return $this->lres;
		}
	}
	
	function findUser($search, $exact) {
		$this->lres=array();
		if ($exact) {
			exec("finduser -c -e $search", $this->lres, $err);
		} else {
			exec("finduser -c $search", $this->lres, $err);
		}
		if ($err) {
			return 0;
		} else {
			return $this->lres;
		}
	}
	
	function findGroups($search) {
	
	}
	
	function delUser($dn) {
	
	}
	
	function resetPassword($uid) {
	
	}
	
	function validateUser($username='', $password='', $group=''){
		//error codes
		// 1 = either username or password was not provided
		// 2 = bad password
		// 3 = bad username

		if (!$username || !$password){
			return array(
				'status' => 'failure',
				'error_code' => '1',
				'error_message' => 'Please enter your username and password.'
			);
		}
		
		//ina.ark.org:389/cn=staff,o=INA,c=US??base?(objectClass=*)
		
		if ($group){
			//$search_string = "(uniquemember=$username)";
			$search_string = "(uniquemember=$username)";
			//$base_dn = "cn=$group,ou=Customers,o=INA,c=US";
			$base_dn = "cn=$group,o=INA,c=US";
			echo "$base_dn / $search_string";
		}else{
			$search_string = "(uid=$username)";	
		}
		
		$sr = ldap_search($this->$ldap_ds, $base_dn, $search_string); 
    $entry = ldap_first_entry($this->$ldap_ds, $sr);
    if ($entry){
    	$dn = ldap_get_dn ($this->$ldap_ds,$entry); 
    	if ($dn){
    		$goodpassword = @ldap_bind($this->$ldap_ds, $dn, $password);
    		if (!$goodpassword){
    			//password was bad
					return array(
						'status' => 'failure',
						'error_code' => '2',
						'error_message' => 'The username/password combination you supplied is not valid.1'
					);
    		}else{
					return array(
						'status' => 'success',
					);
				}
    	}else{
    		//username was bad
				return array(
					'status' => 'failure',
					'error_code' => '3',
					'error_message' => 'The username/password combination you supplied is not valid.2'
				);
    	}
    }else{
    	//username was bad
    	return array(
				'status' => 'failure',
				'error_code' => '3',
				'error_message' => 'The username/password combination you supplied is not valid.3'
			);
   	}
	}
}
?>
