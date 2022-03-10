<?

class SimpleMysqlSessionHandler implements SessionHandlerInterface{
    protected $dbConnection;
    protected $dbTable;
    
	/**
	 * Set db data if no connection is being injected
	 * @param 	string	$dbHost	
	 * @param	string	$dbUser
	 * @param	string	$dbPassword
	 * @param	string	$dbDatabase
	 */	
	public function setDbDetails($dbHost, $dbUser, $dbPassword, $dbDatabase){
		$this->dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase);
		if (mysqli_connect_error()) {
		    throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
	}

	/**
	 * Inject DB connection from outside
	 * @param 	object	$dbConnection	expects MySQLi object
	 */
	public function setDbConnection($dbConnection){
		$this->dbConnection = $dbConnection;
	}

	/**
	 * Inject DB connection from outside
	 * @param 	object	$dbConnection	expects MySQLi object
	 */
	public function setDbTable($dbTable){
		$this->dbTable = $dbTable;
	}    
    
    public function open($savePath, $sessionName)
    {
        //delete old session handlers
        $limit = time() - (3600 * 24);
        $sql = sprintf("DELETE FROM %s WHERE timestamp < %s", $this->dbTable, $limit);
        return $this->dbConnection->query($sql);
    }

    public function close()
    {
		return $this->dbConnection->close();
    }

    public function read($id)
    {
        $sql = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        if ($result = $this->dbConnection->query($sql)) {
            if ($result->num_rows && $result->num_rows > 0) {
                $record = $result->fetch_assoc();
                return $record['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function write($id, $data)
    {
        $sql = sprintf("REPLACE INTO %s VALUES('%s', '%s', '%s')",
        			   $this->dbTable, 
                       $this->dbConnection->escape_string($id),
                       $this->dbConnection->escape_string($data),
                       time());
        return $this->dbConnection->query($sql);
    }

    public function destroy($id)
    {
        $sql = sprintf("DELETE FROM %s WHERE `id` = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        $r = $this->dbConnection->query($sql);
        return $r; 
    }

    public function gc($maxlifetime)
    {
        $sql = sprintf("DELETE FROM %s WHERE `timestamp` < '%s'", $this->dbTable, time() - intval($max));
        return $this->dbConnection->query($sql);
    }
}

$handler = new SimpleMysqlSessionHandler();
$handler->setDbDetails('db.dev.ark.org', 'session', 'phpsess', 'php_sessions');
$handler->setDbTable('plain_session');
session_set_save_handler($handler, true);
