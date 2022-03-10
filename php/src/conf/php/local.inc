<?php

/* This is the Horde local.inc file for PHPLIB. You can either include
   this file in your local.inc, replace your local.inc with it, or
   copy the necessary contents over. There's no need for these to be
   the only classes in local.inc. */


class HordeDB extends DB_Sql {
  var $Host = 'localhost';
  var $Database = 'horde';
  var $User = 'www';
  var $Password = '';
  var $Port = '';

  function halt($msg) {
    printf("<b>Database error (HordeDB):</b> %s<br>\n", $msg);
  }
}



class HordeCT extends CT_Sql {
  var $database_class = 'HordeDB';         // Which database class to use...
  var $database_table = 'active_sessions'; // and find our session data in this table.
}


/*
class HordeCT extends CT_Shm {
  var $max_sessions   = 500;               // number of maximum sessions
  var $shm_key        = 0x123754;          // unique shm identifier
  var $shm_size       = 64000;             // size of segment
}
*/

/*
class HordeCT extends CT_Ldap {
  var $ldap_host = "localhost";
  var $ldap_port = 389;
  var $basedn    = "o=INA,c=US";
  var $rootdn    = "cn=root, dc=your-domain, dc=com";
  var $rootpw    = "secret";
  var $objclass  = "phplibdata";
}
*/

/*
class HordeCT extends CT_DBM {
  var $dbm_file  = '/users/chuck/horde/lib/session.dbm';
}
*/


class HordeSession extends Session {
  var $classname = 'HordeSession';
  
  var $cookiename     = '';           // defaults to classname
  var $magic          = 'chuckmIMP';  // ID seed
  var $mode           = 'cookie';     // We propagate session IDs with cookies
  var $fallback_mode  = 'get';
  var $lifetime       = 0;            // 0 = do session cookies, else minutes
  var $that_class     = 'HordeCT';    // name of data storage container
  var $gc_probability = 5;
  var $allowcache     = 'no';         // don't allow any caching of pages
}

class HordeSessionCached extends HordeSession {
  var $allowcache     = 'private';    // allow private caching of pages
}

class HordeUser extends User {
  var $classname = 'HordeUser';
  
  var $magic          = 'Abracadabra'; // ID seed
  var $that_class     = 'HordeCT';     // data storage container
}

class HordeAuth extends Auth {
  
  var $classname      = 'HordeAuth';
  var $lifetime       =  360;
  var $magic          = 'FrappucinoMochass';
  var $database_class = 'HordeDB';
  var $database_table = 'auth_user_md5';
  
  function auth_loginform() {
    global $sess, $challenge;
    
    $challenge = md5(uniqid($this->magic));
    $sess->register('challenge');
    
    include 'horde_loginform.ihtml';
  }
  
  function auth_validatelogin() {
    global $username, $password, $challenge, $response;
    
    if (isset($username))
      $this->auth['uname'] = $username;
    else {
      $username = '';
      $this->auth['uname'] = 'nobody';
    }
    if (!isset($password)) $password = '';
    $pass = '';
    
    $this->db->query(sprintf("select user_id, perms, password from %s where username = '%s'", $this->database_table, addslashes($username)));
    while ($this->db->next_record()) {
      $uid  = $this->db->f('user_id');
      $perm = $this->db->f('perms');
      $pass = $this->db->f('password');   // Password is stored as a md5 hash
    }
    
    // True when JS is disabled
    if (empty($response)) {
      if (md5($password) != $pass) {       // md5 hash for non-JavaScript browsers
	return false;
      } else {
        $this->auth['perm'] = $perm;
        return $uid;
      }
    }
    
    // Response is set, JS is enabled
    if (md5("$username:$pass:$challenge") != $response) {
      return false;
    } else {
      $this->auth['perm'] = $perm;
      return $uid;
    }
  }
  
  // sign up a new user
  function auth_registerform () {
    ;
  }
  
  // actually create a new user
  function auth_doregister () {
    ;
  }
  
}

class HordeDefaultAuth extends HordeAuth {
  var $classname = 'HordeDefaultAuth';
  var $nobody    = true;
}

class HordePerm extends Perm {
  var $classname = 'HordePerm';
  
  var $permissions = array(
			   'nobody'            => 0,
			   'Horde User'        => 1,
			   'Horde Admin'       => 16,
                           'Skattek User'      => 32,
                           'Skattek Admin'     => 64
			   );
  
  function perm_invalid ($does_have, $must_have) {
    global $perm, $auth, $sess;
    global $_PHPLIB;
    
    include $_PHPLIB['libdir'] . 'horde_perminvalid.ihtml';
  }
}

/* IMP-specific classes */
class impPrefs extends DB_Sql {
  var $classname = "impPrefs";
  var $Host = 'localhost';
  var $Database = 'imp';
  var $User = '';
  var $Password = '';
  var $Port = '';
  
  function halt($msg) {
    printf("</td></table><b>Database error (impPrefs):</b> %s<br>\n", $msg);
    printf("<b>SQL Error</b>: %s (%s)<br>\n", $this->Errno, $this->Error);
    die("Execution halted.");
  }
}

/* Whups-specific classes */
class whupsDB extends DB_Sql {
  var $classname = "whupsDB";
  var $Host = 'localhost';
  var $Database = 'whups';
  var $User = '';
  var $Password = '';
  var $Port = '';
  
  function halt($msg) {
    printf("</td></table><b>Database error (whupsDB):</b> %s<br>\n", $msg);
    printf("<b>SQL Error</b>: %s (%s)<br>\n", $this->Errno, $this->Error);
    die("Execution halted.");
  }
}

?>
