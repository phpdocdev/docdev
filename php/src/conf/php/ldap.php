<?php
class ldap{
	
	var $ldap_ds;
	var $lres;
	
	function ldap($admin=0) {
		$this->$ldap_ds=ldap_connect("ldap.ark.org:389");
  	if ($this->$ldap_ds) { 
			if ($admin) {
				$adminname="cn=Directory Manager";
				$passfile=fopen("/ina/misc/nsds.pw", "r");
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
	
	function adduser($uid, $pass='', $group='customer', $ou='Customers') {
		$this->lres=array();
		if ($pass) {
			exec("adduser -c -u $uid -p $pass -g $group -o $ou", $this->lres, $err);
		} else {
			exec("adduser -c -u $uid -g $group -o $ou", $this->lres, $err);
		}
		if ($err) {
			return 0;
		} else {
			return $this=>lres;
		}
	}
	
	function finduser($search, $exact) {
		$this->lres=array();
		if ($exact) {
			exec("finduser -c -e $search", $this->lres, $err);
		} else {
			exec("finduser -c $search", $this->lres, $err);
		}
		if ($err) {
			return 0;
		} else {
			return $this=>lres;
		}
	}
	
	function findgroups($search) {
	
	}
	
	function deluser($dn) {
	
	}
	
	function resetpw($uid) {
	
	}


}
?>
