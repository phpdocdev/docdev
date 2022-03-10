<?php

##
## Copyright (c) 1998,1999 SH Online Dienst GmbH
##                    Boris Erdmann, Kristian Koehntopp
##
## Copyright (c) 1998,1999 Sascha Schumann <sascha@schumann.cx>
## 
## $Id: ct_sql.inc,v 1.1.1.1 2001/05/08 21:49:56 david Exp $
##
## PHPLIB Data Storage Container using a SQL database
##

class CT_Sql {
	##
	## Define these parameters by overwriting or by
	## deriving your own class from it (recommened)
	##
	
	var $database_table = "active_sessions";
	var $database_class = "DB_Sql";
	var $database_lock_semaphore = "";

    var $encoding_mode = "base64";

	## end of configuration

	var $db;

	function ac_start() {
		$name = $this->database_class;
		$this->db = new $name;
	}

	function ac_get_lock() {
		if ( "" != $this->database_lock_semaphore ) {
			while ( ! $this->db->query("SELECT get_lock('%s')", 
						$this->database_lock_semaphore) ) {
				$t = 1 + time(); while ( $t > time() ) { ; }
			}
		}
	}

	function ac_release_lock() {
		if ( "" != $this->database_lock_semaphore ) {
			$this->db->query("SELECT release_lock('%s')", 
					$this->database_lock_semaphore);
		}
	}

	function ac_gc($gc_time, $name) {
		$timeout = time();
		$sqldate = date("YmdHis", $timeout - ($gc_time * 60));
		$this->db->query(sprintf("DELETE FROM %s ".
					"WHERE changed < '%s' AND name = '%s'",
					$this->database_table, 
					$sqldate,
					addslashes($name)));
	}

	function ac_store($id, $name, $str) {
		$ret = true;
        switch ( $this->encoding_mode ) {
          case "slashes":
/* EXPERIMENTAL  -ANT <athompso@commerced.com> */
		    $str = addslashes($name . ":" . $str);
            break;
          case "base64":
          default:
/* EXPERIMENTAL  -ANT <athompso@commerced.com> */
		    $str = base64_encode($name . ":" . $str);
        };
		$name = addslashes($name);
		$this->db->query(sprintf("UPDATE %s SET val='%s', changed='%s' ".
					"WHERE sid='%s' AND name='%s'",
					$this->database_table,
					$str,
					$now = date("YmdHis", time()),
					$id,
					$name));
		if($this->db->affected_rows() == 0 &&
			!$this->db->query(sprintf("INSERT INTO %s VALUES ".
						"('%s','%s','%s','%s')",
						$this->database_table,
						$id,
						$name,
						$str,
						$now))) {
				$ret = false;
		}
		return $ret;
	}

	function ac_delete($id, $name) {
		$this->db->query(sprintf("DELETE FROM %s ".
					"WHERE name = '%s' AND sid = '%s'",
					$this->database_table,
					addslashes($name),
					$id));
	}

	function ac_get_value($id, $name) {
		$this->db->query(sprintf("SELECT val FROM %s ".
    					"WHERE sid  = '%s' AND name = '%s'",
					$this->database_table,
					$id,
					addslashes($name)));
		if($this->db->next_record()) {
/* EXPERIMENTAL  -ANT <athompso@commerced.com> */

          $str = $this->db->f("val");

/* In fact, this is so ******* experimental it's not funny 
 * -ANT <athompso@commerced.com>
 */

          $str2 = base64_decode( $str );
          if( ereg("^".$name.":.*", $str2) ) {
            $str = ereg_replace("^".$name.":", "", $str2 );
          } else {
            $str3 = stripslashes( $str );
            if( ereg("^".$name.":.*", $str3) ) {
              $str = ereg_replace("^".$name.":", "", $str3 );
            } else {
              switch ( $this->encoding_mode ) {
                case "slashes":
                  $str = stripslashes($str);
                  break;
                case "base64":
                default:
                  $str = base64_decode($str);
              }
            }
          };
          return $str;
        };
		return "";
	}

	function ac_newid($str, $name) {
		return $str;
	}

	function ac_halt($s) {
		$this->db->halt($s);
	}

	function ac_auth($username, $password) {
		$this->db->query(sprintf("SELECT uid,perms FROM auth_users ".
					"WHERE username = '%s' AND password = '%s'",
					addslashes($username), addslashes($password)));
		if($this->db->next_record()) {
			return array($this->db->f("uid"),
					$this->db->f("perms"));
		}
		return array();
	}
}
?>
