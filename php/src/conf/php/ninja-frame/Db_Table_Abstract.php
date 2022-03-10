<?
/**
 * Table abstraction class
 * 
 * @author Josh Moody <josh@ark.org>
 * @package DbTableAbstract
 * @filesource
 */

/**
 * Shared libraries / Dependencies
 *
 * 		PEAR Libraries
 * 			MDB2 w/ MySQL support
 * 		Classes/Libraries from cvs: ina-php-security
 * 			INA Sanitization Class -- class.ina_sanitize.php
 */
 
/**
 * PEAR database abstraction
 */
require_once 'MDB2.php' ;

/**
 * INA Sanitization class
 *
 * Can be found in cvs: ina-php-security
 */
require_once 'INA/Security/class.ina_sanitize.php' ;

require_once 'ninja-frame/peardb_parse_dsn.php' ;

/**
 * Table Abstraction class
 *
 * Provides abstracted table functionality, including:
 * 		- fetchRow() Find records by a WHERE condition
 * 		- find() Find a single record by primary key
 * 		- findAll() Find records meeting a WHERE condition
 * 		- fetchAll() Return all records from a table
 * 		- save() Insert a new record
 * 		- update() Update specified values of a record, by primary key
 *
 * Of course, you are free to write custom methods within child classes to
 * provide additional functionality
 *
 *
 * Example Usage:
 * @example examples/db_table_abstract_example.php
 *
 * @todo Add handling of combination primary keys.
 * @package DbTableAbstract
 * @filesource
 */
class Db_Table_Abstract{
	/**
	 * Database Object
	 */
	var $_db = false ;
	
	/**
	 * Debug Mode
	 */
	var $_debug = false ;
	
	/**
	 * Which columns do we select? Default * (all)
	 */
	var $_cols = '*' ;

	/**
	 * Constructor
	 *
	 * @param mixed $dsn DSN Connection Array
	 * @return void
	 */
	function Db_Table_Abstract($dsn = null, $debug = false){
		$this->__construct($dsn, $debug) ;
	}
	
	function __construct($dsn = null, $debug=false){
		if ($debug){
			$this->_debug = $debug ;
		}
		
		$this->setLogFiles() ;
		
		GLOBAL $Default_DSN ;

		if (!$dsn && !$Default_DSN){
			PEAR::raiseError("No connection string defined for table {$this->_name}") ;
		}
		
		if (!$dsn){
			$dsn = $Default_DSN ;
		}
		
		$this->_db = $this->dbConnect($dsn) ;
	}
	
	/**
	 * Find records by a WHERE condition.
	 *
	 * @param string $where Condition.  May contain value place holders
	 * @param mixed $data Data to bind to place holders in condition
	 * @param int $limit Number of records to return.
	 * @return mixed query output
	 */
	function fetchRow($where, $data = null, $limit=1){
		$start = mktime();

		$join_sql = '' ;
		
		if (isset($this->join_cond)){
			$join_sql .= join(' ', $this->join_cond) ;
		}

		// Base SQL statement.
		$sql = "SELECT {$this->_cols} FROM {$this->_name} $join_sql WHERE $where" ;
		
		$sql = $this->quoteInto($sql, $data) ;

		$this->logSQL($sql, $start) ;

		if ($limit == 1){
			// Return single row
			$this->_db->setLimit(1);
			$res = $this->_db->queryRow($sql) ;
		}else{
			if ($limit){
				// Return specified limit.
				$limit_safe = floor($limit) ;
				$this->_db->setLimit($limit_safe) ; // Cross Platform record limiter
			}
			// Return all matching
			$res = $this->_db->queryAll($sql) ;
		}

		if ($this->_debug){
			echo($sql . "\n") ;
		}

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Find a single record by primary key
	 *
	 * @param string $value Primary Key Value
	 * @return mixed Result of db query
	 */
	function find($value){
		$where = "{$this->_primary} = ?" ;
		
		$res = $this->fetchRow($where, $value) ;
		
		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Find records meeting a WHERE condition
	 *
	 * @param string $where Condition
	 * @param mixed $data to bind to condition
	 * @param mixed $limit How many results?
	 * @return mixed Result of db query
	 */
	function findAll($where, $data = null, $limit=null){
		$res = $this->fetchRow($where, $data, $limit) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 *
	 * Find a single value meetig a WHERE condition
	 *
	 * @param string $column column to pull from
	 * @param string $where Condition
	 * @param mixed $data to bind to condition
	 * @return mixed Single Value from DB or bool(false).
	 */
	function findOne($column = false, $where = '1=1', $data = false){
		$start = mktime();
		
		if (!$column){
			return false ;
		}
		
		if ($data !== FALSE){
			$where = $this->quoteInto($where, $data) ;
		}
		
		$sql = "SELECT $column FROM {$this->_name} WHERE $where";

		$this->logSQL($sql, $start) ;
		
		if ($this->_debug){
			echo($sql . "\n") ;
		}

		$result = $this->_db->queryOne($sql) ;

		if (PEAR::isError($result)){
			$this->_handle_pear_error($result) ;
		}

		return $result;
	}

	/**
	 * Return a single column from the database
	 *
	 * @param string $column
	 * @param string $where Condition. May contain value place holders
	 * @return mixed query output
	 */
	function findCol($column = false, $where = '1=1', $data = false){
		if (!$column){
			return false ;
		}
		$start = mktime();
		$where = $this->quoteInto($where, $data) ;
		$sql = "SELECT DISTINCT $column FROM {$this->_name} WHERE $where" ;
		
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->queryCol($sql) ;
		
		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}
		
		return $res ;
	}

	/**
	 * Find a single row by a WHERE condition
	 */
	function findRow($where, $data = null){
		return $this->fetchRow($where, $data, 1) ;
	}
	
	/**
	 * Return all records from a table
	 *
	 * @param int $limit Limit results to a specific number.  Default: 1000
	 * @return array All Rows
	 */
	function fetchAll($limit=1000){
		$res = $this->fetchRow('1=1', null, $limit) ;
		
		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Pass a complete SQL query thru to MDB2's queryAll() with query and error logging.
	 */
	function queryAll($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->queryAll($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Pass a complete SQL query thru to MDB2's queryRow() with query and error logging.
	 */
	function queryRow($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->queryRow($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Pass a complete SQL query thru to MDB2's queryOne() with query and error logging.
	 */
	function queryOne($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->queryOne($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Pass a complete SQL query thru to MDB2's queryCol() with query and error logging.
	 */
	function queryCol($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->queryCol($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}

	/**
	 * Pass a complete SQL query thru to MDB2's execute() with query and error logging.
	 * Useful for insert/update statements
	 */
	function exec($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->exec($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}
	
	/**
	 * Pass a complete SQL query thru to MDB2's query() with query and error logging.
	 * Useful for running delete statements
	 */
	function query($sql){
		$start = mktime();
		$this->logSQL($sql, $start) ;
		
		$res = $this->_db->query($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $res ;
	}
	
	/**
	 * Insert new record
	 *
	 * @param array $data Associative array of data to insert
	 * @return type Return value from query
	 */
	 function save($data){


		$start = mktime();
		$values = $this->_db_filter(array_values($data)) ;
		$keys = array_keys($data) ;

		$keys_sql = "(" . join(', ', $keys) . ")" ;
		$values_sql = "VALUES (" . join(", ", $values) . ")" ;

		$base_sql = "INSERT INTO {$this->_name}" ;

		$sql = join(' ' , array($base_sql, $keys_sql, $values_sql)) ;

		if ($this->_debug){
			echo($sql . "\n") ;
		}
		$this->logSQL($sql, $start) ;
		$res = $this->_db->exec($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
		}

		return $this->_db->lastInsertID() ;
	}

	/**
	 * Update specified values of a record, by primary key.
	 *
	 * @param array $data Data to be updated.  Primary key must be in the array
	 * @return mixed Return value of query, or false if no pk in data.
	 * @todo Add ability to update by a condition in addition to primary key.
	 */
	function update($data){
		$start = mktime();

		// Get primary key from data.
		$pk = $data[$this->_primary] ;
		unset($data[$this->_primary]) ;

		$set = array() ;
		
		foreach($data as $k=>$v){
			$set[] = "$k = " . $this->_db_filter($v) ;
		}

		$set_sql = join(', ', $set) ;
		$base_sql = "UPDATE {$this->_name} SET" ;

		if ($pk){
			$pk_safe = $this->_db_filter($pk) ;
			$where = "WHERE {$this->_primary} = $pk_safe" ;
		}else{
			return false ; // Gotta have the primary key.
		}
		$sql = join(' ' , array($base_sql, $set_sql, $where)) ;
		
		$this->logSQL($sql, $start) ;
		
		if ($this->_debug){
			echo($sql . "\n") ;
		}
		$res = $this->_db->exec($sql) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
			return false ;
		}
		
		return $res ;
	}

	/**
	 * Delete specified record, by primary key.
	 *
	 * @param array $data Data to be updated.  Primary key must be in the array
	 * @return mixed Return value of query, or false if no pk in data.
	 */
	function delete($pk){
		$start = mktime();
		$pk_safe = $this->_db_filter($pk) ;
		$sql = "DELETE FROM {$this->_name} WHERE {$this->_primary} = $pk_safe" ;
		$res = $this->_db->query($sql) ;
		$this->logSQL($sql, $start) ;

		if (PEAR::isError($res)){
			$this->_handle_pear_error($res) ;
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * Inserts escaped values into a string for execution in a query.
	 *
	 * 
	 * Example usage with array of values to bind
	 * <code>
	 * 	$sql = 'select foo from bar where fname = ? and lname = ?' ;
	 * 	$fname = 'Josh' ;
	 * 	$lname = 'Moody' ;
	 * 	$sql = $this->quoteInto($sql, array($fname, $lname)) ;
	 * // returns select foo from bar where fname = 'Josh' and lname = 'Moody' ;
	 * </code>
	 *
	 * Example usage with string variable to bind.
	 *
	 * <code>
	 * 	$sql = 'select foo from bar where lname = ?' ;
	 * 	$lname = 'Moody' ;
	 * 	$sql = $this->quoteInto($sql, $lname) ;
	 * // returns select foo from bar where lname = 'Moody' ;
	 * </code>
	 *
	 * @param string $sql SQL string with placeholder values
	 * @param mixed $data Data to bind to place holders
	 * @return string SQL with escaped values.
	 */
	function quoteInto($sql, $data=false){
	
		if ($data !== false){ // Insert escaped data into query, if it exists.
			$data = $this->_db_filter($data, 'db', $this->_db) ;

			if (is_array($data)){
				// Data is an array.  Use vsprintf to replace placeholders with elements from data array.
				$sql = str_replace('?', '%s', $sql) ;
				$sql = vsprintf($sql, $data) ;
			}else{
				// Data is a string. Replace placeholder with data
				$sql = str_replace('?', $data, $sql) ;
			}
		}
		
		return $sql ;
	}
	
	/**
	 * Connect to a database using MDB2
	 *
	 * @param mixed $dsn DSN String or Array
	 * @param bool $persistent Should persisent connections be used?
	 * @return MDB2 Object
	 */
	 function dbConnect($dsn, $persistent=true){
	 	# There's a bug in MDB2's parseDSN feature. We'll parse it into an array using the old PEAR::DB function
	 	$dsn = peardb_parseDSN($dsn) ;

		# Use factory() instead of connect() to avoid db object collisions db handles to multiple databases
		$db =& MDB2::factory($dsn, array('persistent'=>$persistent)) ;
		if (PEAR::isError($db)){
			$this->_handle_pear_error($db) ;
		}
		$db->setFetchMode(MDB2_FETCHMODE_ASSOC) ;
		$db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE) ;
		$db->setErrorHandling(PEAR_ERROR_CALLBACK, array($this, '_handle_pear_error'));
		if (PEAR::isError($db)){
			die('Cannot connect to database') ;
		}
		
		return $db ;
	 }

	/**
	 * Create link to join to different tables
	 *
	 * Example:
	 * <code>
	 * class Cities extends Db_Table_Abstract{
	 * 
	 * 	protected $_name = 'Cities' ;
	 * 
	 * 	protected $_primary = 'CityName' ;
	 * 
	 * 
	 * 	function __construct($dsn, $debug){
	 * 		$this->add_join('Counties', 'CountyCode') ;
	 * 		parent::__construct($dsn, $debug) ;
	 * 	}
	 * }
	 * </code>
	 *
	 * @param string $table Table to join to
	 * @param string $fk Foreign key in other table
	 * @param string $join_type Type of join. Default LEFT
	 * @param string $join_syntax JOIN WITH USING (field) or ON (table1.field = table2.field) syntax. Default USING
	 * @return bool true
	 */
	function add_join($table, $fk, $join_type='LEFT', $join_syntax='USING'){
		if ($join_syntax == 'USING'){
			$this->join_cond[] = "$join_type JOIN $table USING ($fk)" ;
		}elseif ($join_syntax == 'ON' && is_array($fk)){
			$this->join_cond[] = "$join_type JOIN $table ON {$this->_name}.{$fk[0]} = $table.{$fk[1]}" ;
		}
		return true ;
	}
	
	/**
	 * Filter data with INA Sanitize class
	 *
	 * @param mixed $data Data (string or array) to be escaped.
	 * @return mixed Data escaped for the database type.
	 */
	 function _db_filter($data){
		$ina_sanitize = new ina_sanitize() ;
		return $ina_sanitize->recurs_escape_db($data, $this->_db) ;
	 }

	/**
	 * Handle PEAR errors.
	 * @param object $error_object PEAR Error object
	 */
	function _handle_pear_error ($error_obj) {
		$message = mysql_errno() . "-" . mysql_error() . "-" . $error_obj->getMessage() . " on " . $error_obj->getDebugInfo() ;

		//var_dump($message);
		//die();
		$this->logError($message) ;

		if (defined('MAIL_ERRORS_TO')){
			mail (MAIL_ERRORS_TO, 'Error', $message, 'From: support@ark.org');
		}

		if ($this->_debug){
			die($message) ;
		}else{
			die ("<p>An error occurred, and the Administrator has been notified.  Please try again later.</p>") ;
		}
	}

	/**
	 * Set location for log files.
	 *
	 * Default Query Log: /web/app-data/tmp/ninja_sql_query_log.sql
	 * Default Error Log: /web/app-data/tmp/ninja_sql_error_log.sql
	 * 
	 * You can override the file locations in your application by DEFINEing SQL_ERROR_FILE and SQL_QUERY_FILE
	 * Example: define('SQL_ERROR_FILE', '/path/to/log/file') ;
	 *
	 * Tip: For shell scripts, you can write sql errors to STDERR instead of to a file --
	 * this allows it to be picked up by the jobs scheduler's log.
	 * Example: define('SQL_ERROR_FILE', 'php://stderr') ; 
	 *
	 * Logging is disabled by default.
	 * @see logSQL(), logError()
	 */
	function setLogFiles(){
		$this->sql_query_file = '/web/app-data/tmp/ninja_sql_query_log.sql' ;
		$this->sql_error_file = '/web/app-data/tmp/ninja_sql_error_log.sql' ;

		if (defined('SQL_QUERY_FILE')){;
			$this->sql_query_file = SQL_QUERY_FILE ;
		}
		if (defined('SQL_ERROR_FILE')){
			$this->sql_error_file = SQL_ERROR_FILE ;
		}
	}

	/**
	 * Log SQL Queries to file
	 *
	 * Logging is disabled by default.  To enable, define('LOG_QUERIES', true) in your application
	 * @see setLogFiles() ;
	 */
	function logSQL($sql, $start_time = 0){
		if (defined('LOG_QUERIES') && LOG_QUERIES == true){
			$delim = defined('SQL_QUERY_FILE_DELIMITER') ? SQL_QUERY_FILE_DELIMITER : "\t";
			$elapsed = mktime() - $start_time;

			$fh = fopen($this->sql_query_file, 'a') ;
			fwrite($fh, date('Y-m-d H:i:s') . $delim) ;

			// if SQL_QUERY_FILE is provided we don't need it written to the log
			//   (the programmer knows what is going into the file
			if(!defined('SQL_QUERY_FILE')){
				fwrite($fh, $_SERVER['SCRIPT_FILENAME'] . $delim) ;
			}
			
			fwrite($fh, $elapsed . $delim) ;
			fwrite($fh, $sql . "\n") ;
			fclose($fh) ;
		}
	}

	/**
	 * Log SQL Errors to file
	 *
	 * Logging is disabled by default.  To enable, define('LOG_SQL_ERRORS', true) in your application
	 * @see setLogFiles() ;
	 */
	function logError($message){
		if(defined('LOG_SQL_ERRORS') && LOG_SQL_ERRORS == true){
			$fh = fopen($this->sql_error_file, 'a') ;
			fwrite($fh, date('Y-m-d H:i:s') . "\t") ;
			fwrite($fh, $_SERVER['SCRIPT_FILENAME'] . "\t") ;
			fwrite($fh, $message . "\n") ;
			fclose($fh) ;
		}
	}
}
?>
