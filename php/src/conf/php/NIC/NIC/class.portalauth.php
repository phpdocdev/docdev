<?
	class PortalAuth{
	

	
		function PortalAuth(){
			$this->ldap_host = 'ldap.ark.org:389';
			$this->ldap_rootdn = "o=INA,c=US";	
			$this->portal_ou = 'ou=PortalId';
			$this->portal_dn = $this->portal_ou . ',' . $this->ldap_rootdn;			
			$this->ds = NULL;
		}
		
		function connect(){
			if(!$this->ds){
				$this->ds=ldap_connect($this->ldap_host);			
			}
		}
		
		function update_account($uid){
			// record in the ldap the last_login_time
		}
		
		function validate($uid, $pass, $group=''){
			$this->connect();
			$dn = $this->ldap_rootdn;
			
			// TODO: check password_needchange on login?

			if(!$uid || !$pass){
				return 'No user name or password supplied';
			}

			if(!$group){
				// if no group is supplied then just try the bind
				$ret = @ldap_bind($this->ds, $udn, $pass);
				if($ret){
					return 'success';
				}else{
					return 'Invalid password';
				}				
			}
			
			// find the user in LDAP			
			$sr = ldap_search($this->ds, $this->ldap_rootdn, '(uid='.$uid.')', array('ou', 'cn', 'uniquemember')); //, 200, 30);
				
			if($sr){
				$entry = ldap_first_entry($this->ds, $sr);
				if($entry){
					$udn = ldap_get_dn($this->ds,$entry); 
					$inGroup = false;

					if( is_array($group) ){
						foreach($group as $gr){
							if($inGroup){
								continue;
							}
							//echo "($gr) - $udn<br>";												
							$r = ldap_compare($this->ds, "$gr,$dn", 'uniquemember', $udn);

							if( $r === TRUE ){
								$inGroup = true;
							}										

						}			
					}else if($group){
						$r = @ldap_compare($this->ds, "$group,$dn", 'uniquemember', $udn);
						//echo "($php_errormsg - $group,$dn)";
						if( $r === TRUE ){
							$inGroup = true;
						}			
					}else{
						$inGroup = true;
					}
					
					if( $inGroup ){
						$ret = @ldap_bind($this->ds, $udn, $pass);
						if($ret){
							return 'success';
						}else{
							return 'Invalid password';
						}
					}else{
						return 'Not in group';
					}				
				}else{
					// could not find user in ldap (or could not get first entry)
					return 'Invalid user name';		
				}
			}else{
				// could not find user in ldap
				return 'Invalid user name';
			}
			
			return 'unknown error';		
		}
		
		function user_in_group($uid, $group){
			$this->connect();
			$dn = $this->ldap_rootdn;

			if(!$group){
				return false;
			}
			
			// find the user in LDAP			
			$sr = ldap_search($this->ds, $this->ldap_rootdn, '(uid='.$uid.')', array('ou', 'cn', 'uniquemember')); //, 200, 30);
				
			if($sr){
				$entry = ldap_first_entry($this->ds, $sr);
				if($entry){
					$udn = ldap_get_dn($this->ds,$entry); 
					$inGroup = false;
					
					if( is_array($group) ){
						foreach($group as $gr){
							if($inGroup){
								continue;
							}
							//echo "($gr) - $udn<br>";												
							$r = ldap_compare($this->ds, "$gr,$dn", 'uniquemember', $udn);
							
							if( $r === TRUE ){
								$inGroup = true;
							}										

						}			
					}else if($group){
						$r = @ldap_compare($this->ds, "$group,$dn", 'uniquemember', $udn);
						//echo "($php_errormsg - $group,$dn)";
						if( $r === TRUE ){
							$inGroup = true;
						}			
					}else{
						$inGroup = true;
					}
					
					if( $inGroup ){
						return true;
					}else{
						return false;
					}				
				}else{
					// could not find user in ldap (or could not get first entry)
					return false;		
				}
			}else{
				// could not find user in ldap
				return false;
			}
			
			return false;
		}
		

		function create_account( $un, $pw, $service, $group, $cn, $sn, $email, $phone='' ){
			$this->connect();
			$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');	
			

			$info["objectclass"]="organizationalPerson";
			$info["objectclass"]="inetOrgPerson";
			$info["cn"] = "Bob";
			$info["sn"] = "Sanders";
			$info["mail"] = "bob@ark.org";
			$info["userpassword"] = "test";
			$info["uid"] = "bob2";
			$info["purpose"] = "test account";
			$info["telephonenumber"] = "123-123-1234";			
					
			return ldap_add($this->ds, 'uid=bob2,ou=PortalId,'.$this->ldap_rootdn, $info);
		}
		
		function mod_account( $un, $cn, $sn, $email, $phone='' ){
			$this->connect();
			$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');	
			
			$info = array();
			
			if($cn){	  $info["cn"] = $cn;	}  
			if($sn){	  $info["sn"] = $sn;  }
			if($email){	$info["mail"] = $email;  }
			if($pw){	  $info["userpassword"] = $pw;  }
			if($phone){	$info["telephonenumber"] = $phone;	}
			
			return ldap_mod_replace($this->ds, "uid=$un,".$this->portal_dn, $info);
		}		
		
		function add_service($uid, $service){
			$this->connect();
			$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');	
		
			$attr = array(
				service => $service,
			);
		
			return ldap_mod_add($this->ds, "uid=$uid,".$this->portal_dn, $attr);
		}

		function delete_service($uid, $service){
			$this->connect();
			$r=ldap_bind($this->ds, 'cn=Directory Manager', 'ink!Pink');	
		
			$attr = array(
				service => $service,
			);
		
			return ldap_mod_del($this->ds, "uid=$uid,".$this->portal_dn, $attr);		
		}
				

		function send_reminder(){
			// does the user have an email?
			
			// if NO, return: reminder not possible
			
			// if YES, 
			//   get email
			//   reset to random password
			//   email password to user
			//   set password_needchange flag
		}
		
//$status = create_account( un, pw, group, purpose, cn, sm, email, phone)
//	status == success or error message
//
//$status = mod_account( un, pw, mod_info )
//	mod_info = cn, sm, email, phone

	
	}
?>