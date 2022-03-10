<?

//(to encrypt {smd5} just change MHASH_SHA1 to MHASH_MD5) 
//$password = 'FAR7wish';
//mt_srand((double) microtime()*1000000); 
//$salt=mhash_keygen_s2k(MHASH_SHA1,$password,substr(pack("h*",md5(mt_rand())),0,8),4); 
//$passsword="{ssha}".base64_encode(mhash(MHASH_SHA1, $password.$salt).$salt);
//echo "($password)";

function ldap_validate($uid, $pass, $group=''){
	$ds=ldap_connect('ldap.ark.org:389');
	$dn = "o=INA,c=US";	
	$filter = '(uid='.$uid.')';
	
	$status = 'failure';
	$message = 'uknown error';

	if(!$uid || !$pass){
		return array(
			'status' => $status,
			'error_message' => 'No user name or password supplied',
		);		
	}
	
	$sr = ldap_search($ds, $dn, $filter, array('ou', 'cn', 'uniquemember')); //, 200, 30);
		
	if($sr){
		$entry = ldap_first_entry($ds, $sr);
		if($entry){
			$udn = ldap_get_dn($ds,$entry); 
			$inGroup = false;
			
			if( is_array($group) ){
				foreach($group as $gr){
					if($inGroup){
						continue;
					}
					//echo "($gr) - $udn<br>";
										
					//$r = ldap_compare($ds, "cn=staff,o=INA,c=US", 'uniquemember', $udn);
					$r = ldap_compare($ds, "$gr,$dn", 'uniquemember', $udn);
					if( $r === TRUE ){
						$inGroup = true;
					}										
					// get all uniquemember attributes in this group
//					$sr = @ldap_search($ds, "$gr,$dn", "(objectclass=*)", array("uniquemember")); //, 200, 30);
//					if($sr){
//						$en = ldap_first_entry($ds, $sr);
//						$ret = ldap_get_attributes($ds, $en);
//						//var_dump($ret);
//						$inGroup = in_array($udn, $ret['uniquemember']);
//					}
				}			
			}else if($group){
				$r = ldap_compare($ds, "$gr,$dn", 'uniquemember', $udn);
				if( $r === TRUE ){
					$inGroup = true;
				}			
				// get all uniquemember attributes in this group
//				$sr = @ldap_search($ds, "$gr,$dn", "(objectclass=*)", array("uniquemember")); //, 200, 30);
//				$en = ldap_first_entry($ds, $sr);
//				$ret = ldap_get_attributes($ds, $en);
//				$inGroup = in_array($udn, $ret['uniquemember']);
			}else{
				$inGroup = true;
			}
			
			if( $inGroup ){
				$ret = @ldap_bind($ds, $udn, $pass);
				if($ret){
					$status = 'success';
					$message = '';
				}else{
					$message = 'Invalid password';
				}
			}else{
				$message = 'Not in group';
			}				
		}else{
			$message = 'Invalid user name';		
		}
	}else{
		$message = 'Invalid user name';
	}
	
	return array(
		'status' => $status,
		//'error_code' => '2',
		'error_message' => $message,
		'descr' => "base:$dn, filter:$filter",
		'userdn' => $udn,
	);		
}

?>