<?
/*
admin_id
service
mantis_id
time_down
time_up null
message
scheduled (boolean)
created_by
created_time
lifted_by null
lifted_time null
*/

class ina_admin {
	
	var $errors;

	function ina_admin() {
		$this->errors=array();
		$opts=file('INA/Security/options.txt', 1);
		foreach ($opts as $opt) {
			if (preg_match('/^deploy/', $opt)) {
				continue;
			}
			list($key,$var)=split('=', $opt);
			define($key, trim($var));
		}

		require_once('DB.php');
		$this->ina_secure_db = DB::connect(INA_SECURITY_DSN);
		if (PEAR::isError($this->ina_secure_db)) {
			$this->security_log(__FILE__, __LINE__, $this->ina_secure_db->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		$this->ina_secure_db->setFetchMode(DB_FETCHMODE_ASSOC);

		$this->mantis_db = DB::connect(INA_MANTIS_DSN);
		if (PEAR::isError($this->mantis_db)) {
			$this->security_log(__FILE__, __LINE__, $this->mantis_db->getDebugInfo() );		
			$this->internalError=true;
			return false;
		}
		$this->mantis_db->setFetchMode(DB_FETCHMODE_ASSOC);
	}
	
	function takedown($service, $message, $mantis_id, $user, $time_down='', $time_up='', $id='') {
		if ($this->is_offline($service) && !$time_down) {
			$this->errors[] ='This service is already offline.';
			return false;
		}
		if ($time_down) {
			$scheduled_time = strtotime($time_down);
			if ($scheduled_time < time()) {
				$this->error[] = 'You cannot schedule a take-down in the past.';
				return false;
			}
			$mysql_date=date("'Y-m-d H:i:s'", $scheduled_time);
			$scheduled=1;
		} else {
			$scheduled=0;
			$mysql_date='NOW()';
		}
		if ($time_up) {
			$scheduled_time_up = strtotime($time_up);
			if ($scheduled_time_up < time()) {
				$this->error[] = 'You cannot schedule a go-live in the past.';
				return false;
			}
			$mysql_up_date=date("'Y-m-d H:i:s'", $scheduled_time_up);
			$scheduled=1;
		} else {
			$mysql_up_date='NULL';
		}

		$missing_data=false;
		foreach (array('service', 'message', 'user') as $var) {
			if (!trim($$var)) {
				$missing_data=true;
				$this->errors[] = "You must specify a $var";
			} else {
				$safe_db[$var]=$this->ina_secure_db->quote($$var);
			}
		}
		if (!preg_match('/^\d+$/', $mantis_id)) {
				$missing_data=true;
				$this->errors[] = "You must specify a mantis id";
		}
		if ($missing_data) {
			return false;
		}
		if (preg_match('/^\d+$/',$id)) {
			$sql="update site_admin set service=$safe_db[service], mantis_id=$mantis_id, time_down=$mysql_date, time_up=$mysql_up_date, message=$safe_db[message], scheduled=$scheduled, created_by=$safe_db[user], created_time=NOW() where admin_id=$id";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->errors[]="Internal error";
				return false;
			}
		} else {
			$sql="insert into site_admin (service, mantis_id, time_down, time_up, message, scheduled, created_by, created_time) values ($safe_db[service], $mantis_id, $mysql_date, $mysql_up_date, $safe_db[message], $scheduled, $safe_db[user], NOW())";
			$res=$this->ina_secure_db->query($sql);
			if (PEAR::isError($res)) {
				$this->errors[]="Internal error";
				return false;
			}
			$id=$this->ina_secure_db->getOne('select last_insert_id()');
		}
		$msg="$service taken offline effective $mysql_date by $user (id $id).";
		if ($time_up != 'NULL') {
		 $msg.=" Scheduled for recovery at $mysql_up_date.";	
		}
		$this->addBugNote($mantis_id, $user, $msg);
		return true;
	}
	
	function bringup($id, $user) {
		$this->errors=array();
		if (!preg_match('/^\d+$/', $id)) {
			$this->errors[]='You must provide an admin id';
			return false;
		}
		$safe_db[user]=$this->ina_secure_db->quote($user);
		$sql="update site_admin set time_up=NOW(), lifted_time=NOW(), lifted_by=$safe_db[user] where admin_id=$id";
		$this->ina_secure_db->query($sql);
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->errors[]="Internal error";
			return false;
		}
		$sql="select service, mantis_id from site_admin where admin_id=$id";
		$admin_info=$this->ina_secure_db->getRow($sql);
		$this->addBugNote($mantis_id, $user, "$admin_info[service] brought online by $user (id $admin_info[id])");
		return true;
	}
	
	function is_offline($service) {
		$this->errors=array();
		if (!trim($service)) {
			$this->errors[]='You must provide a service';
			return false;
		}
		$safe_db[service]=$this->ina_secure_db->quote($service);
		$sql="select admin_id from site_admin where service=$safe_db[service] and time_down < NOW() and (time_up is NULL or time_up > NOW())";
		$id=$this->ina_secure_db->getOne($sql);
		if ($id) {
			return $id;
		} else {
			return false;
		}
	}
	
	function get_message($id) {
		$this->errors=array();
		if (!preg_match('/^\d+$/', $id)) {
			$this->errors[]='You must provide an admin id';
			return false;
		}
		$details=$this->ina_secure_db->getOne("select message from site_admin where admin_id=$id");
		return $details;
	}
	
	function get_outage_details($id) {
		$this->errors=array();
		if (!preg_match('/^\d+$/', $id)) {
			$this->errors[]='You must provide an admin id';
			return false;
		}
		$details=$this->ina_secure_db->getRow("select * from site_admin where admin_id=$id");
		return $details;
	}
	
	function change_message($id, $message) {
		$this->errors=array();
		if (!preg_match('/^\d+$/', $id)) {
			$this->errors[]='You must provide an admin id';
			return false;
		}
		if (!trim($message)) {
			$this->errors[]='You must provide an new message';
			return false;
		}
		$safe_db[message]=$this->ina_secure_db->quote($message);
		$sql="update site_admin set message=$safe_db[message] where admin_id=$id";
		$res=$this->ina_secure_db->query($sql);
		if (PEAR::isError($res)) {
			$this->errors[]="Internal error";
			return false;
		} else {
			return true;
		}
	}
	
	function auto_golive() {
		$sql="select admin_id from site_admin where time_up is not null and time_up < now() and lifted_time is null";
		$golives = $this->ina_secure_db->getCol($sql);
		foreach ($golives as $id) {
			$sql="update site_admin set lifted_time=NOW() and lifted_by='scheduled' where admin_id=$id";
			$this->ina_secure_db->query($sql);
		}
	}
	
	function get_outages($include_scheduled=false) {
		$sql="select admin_id, service, mantis_id, time_down, time_up, message, scheduled, created_by, created_time from site_admin where lifted_time is null and (time_up is null or time_up > NOW())";
		if (!$include_scheduled) {
			$sql.=" and scheduled = 0";
		}
		$sql.=" order by scheduled, service";
		$outages=$this->ina_secure_db->getAll($sql);
		return $outages;
	}
	
	function addBugNote($mantis_id, $user, $msg) {
		$this->errors=array();
		$msg = $this->mantis_db->quote($msg);
		$sql = "insert into mantis_bugnote_text_table(note)values($msg)";
		$this->mantis_db->query($sql);
		$textId = $this->mantis_db->getOne("select last_insert_id()");
	
		// get the mantis id for this user
		$user = $this->mantis_db->quote($user);
		$uid = $this->mantis_db->getOne("select id from mantis_user_table where username like $user");
	
		$sql = sprintf("insert into mantis_bugnote_table(bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified, note_type)value(%s, %s, %s, 10, NOW(), NOW(), 0)",
			$this->mantis_db->quote($mantis_id),
			$this->mantis_db->quote($uid),
			$textId);
		$this->mantis_db->query($sql);
	}

}

?>