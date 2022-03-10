<?php
/*
 * Query generation for PHP3
 *
 * (C) Copyright 1998 Alex Aulbach
 *     Credits: Gerard Hickey <Gerard.Hickey@nsc.com>
 *              I took many ideas from his SQL.inc, thanks! :-)
 *     The idea is of this class is based in November 1997,
 *     it was a collection of functions for PHP/FI and mSQL.
 *
 * $Id: query_mysql.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 *
 */


/*

The Query-class is inteded to help you with creating selects, inserts,
updates etc. Not just *simple* selects, but longer ones. It is indeed a
great help for tables with more than 10-20 columns. But it can also be used
for simple and small selects. The inbuilt checks help you programming saver.

The best thing is, that you don't have to care, if a field is a string or a
number. The values are automatically converted into the right form. The type
of the vars are read from the table. Stringvalues are encapsulated with '
(configurable) and escaped (the code for this is currently not good), int-
and real-values are casted.

You will make less errors.

mySQL and most other DB's accept a a short form of insert-clause. The
Query-class will always make the longer form. This makes it possible to use
ALTER TABLE-commands without changing the program! E.g. changing a field
in a table from INT to VARCHAR(10) is fully encapsulated with this class.

The class is currently called query_*mysql*. But I think the differences
between the DBs are encapsulated enough in the db_* classes, so it
is perhaps possible to handle the small differences inside this class
(this affects mainly the function db2phptype() ). So an aim of programming
should be to have at the end query_sql.inc.

For the future I plan to add functions to create a where clause from a
search string with rules to handle searching for more than one word.


TODO-list:
- cleaner escaping, handling of \ and NUL (current code is bullshit)
  Some ideas?

- DB-Class inside or outside of this class? 
  Currently for a DB-access we make a new instance:
	var $database_class="DB_myDB";
	[...]
	eval("\$db = new ".$this->database_class.";");
  But perhaps a better model could be to create the new DB-class outside of
  this class and give it as call by reference-parameter to the functions


- automatic configurable manipulation of values, eg. 
  triming of strings (delete whitespace at begin and end)
  also : TOUPPER, TOLOWER etc
- SELECT-Clause (GROUP BY, HAVING, JOIN...)
- make new functions insert_Clause() etc. which inserts only the
  fields they got from your call (the current will do "plain" insert)
- where_Clause() - creating WHERE for select, update, exists etc.
- serv all queries directly into db, return just the handle
  (hm, how to deal with the DB-handle?)
- Return a 2-dimensional (Table-compatible) field from select
- half automatic generating search patterns into a where-clause
- simple search engine support, simple support for semi full-text-search
- The db2phptype() can be used to help creating automatic input forms
  for a table

DEPENDING:
- db_mysql: new function metatabledata(), which returns the table-info from
  current selected table (will return multiple table-columns with a join)
- db_mysql: perhaps the function db2phptype() should be placed there?

*/



class Query {
	var $classname="Query";

	var $database_class="DB_myDB";

	## For debugging: if set to TRUE the Query is printed out,
	## before executing or returning 
	var $debug=false;

	## set this to another value, if you want to hide it
	## in your HTML-code
	## example: var $debug_print="\n<!-- %s -->\n";
	var $debug_print="<BR>\n%s\n<BR>\n";

	## Set this to TRUE if you only want to test, which query
	## will be created (ideal in combination with $debug)
	## This depends only functions which will make changes
	var $no_write=false;

	## currently unused, this var is just an idea for later use.
	var $backslash_N_is_NULL = false;

	## This is the char, which will be replaced by \char.
	## PHP3 seems to be NOT binary-safe, so not quoting
	## for \0  (some ideas?)
	## we use ereg_replace to do the replacements.
	## with PHP3.0.6 you should replace this with str_replace()!
	var $quoting=true;
	var $quotechar="'";

	###########################
	## _dbug
	function _dbug ($str) {
		if ($this->debug) {
			printf($this->debug_print,$str);
		}
	}

	###########################
	## Set DB-Classname
	## This is only a 3rd posibility for setting the classname
	##
	function set_db_class ($db_class) {
		$this->database_class=$db_class;
	}


	###########################
	## This function gets a datatype from the DB and returns an
	## equivalent datatype for PHP
	##
	## It returns also a subtype for a string
	##
	function db2phptype ($type) {
		switch ($type) {
			case "var string":
			case "string" :
			case "char" :
				return(Array("string",""));
				break;
			case "timestamp" :
			case "datetime" :
			case "date" :
			case "time" :
				return(Array("string","date"));
				break;
			case "blob" :
				return(Array("string","blob"));
				break;
			case "real" :
				return(Array("double",""));
				break;
			case "long" :
			default :
				return(Array("int",""));
				break;
		}
	}


	#######################################
	## This function returns a PHP-variable depending
	## on type. E.g. a string is returned as 'string'
	##
	## The parameters mean
	## $val - the value
	##        There is a special case: If value is "NULL" and
	##        the type is not "string" or subtype is empty, then
	##        a value "NULL" is inserted. This let you just spare
	##        a little bit work with $special
	##
	## $meta - the meta information for this field (that's what
	##         is returned by metadata() from DB_sql-class, but just a
	##         single row, e.g. $meta[2]).
	##
	## $special - Overwrites the type of the var if set. Some special
	##            meanings:
	##            "NULL" means, that this value must be set to "NULL"
	##            "func" means, that $val should be untouched -
	##            e.g. to insert the value of a SQL-function
	##            [ INSERT INTO bla VALUES ( time=NOW() ) ]
	##

	function convert ($val,$meta,$special="") {
		list($type)=$this->db2phptype($meta["type"]);
		if (($val == "NULL" &&
		    ($type != "string" || $type[1] != "")) ||
		    $special == "NULL") {
			$type="NULL";
		} else {
			if ($special) {
				$type=$special;
				if ($type!="func") {
					$val=$type;
					$type="func";
				}
			}
		}
		switch ($type) {
			case "string" :
				$val=(string)$val;
				if ($this->quoting) {
#					$val=ereg_replace($this->quotechar,"\\".$this->quotechar,$val);
					$val=AddSlashes($val);
				}
				$val=$this->quotechar . $val . $this->quotechar;
				break;
			case "int" :
				$val=(int)$val;
				break;
			case "double" :
				$val=(double)$val;
				break;
			case "NULL" :
				$val="NULL";
				break;
			case "func" :
				$val=(string)$val;
				break;
			default :
				echo "UNKNOWN TYPE: $type<BR>";
		}
		return(Array($val,$meta["name"]));
	}


	##
	## Function to generate a plain INSERT-Clause
	## ("plain" means, that every field in the table will
	##  be set to a value, default is '' or 0 if nothing said
	##  in $special)
	##
	## $fields  is an assoc. Array consisting out of
	##          table_name => value-pairs
	## $special is an assoc. field which will commit special
	##          handling to convert() (See there)
	## $check   could be "strong" or "soft".
	##          "soft" won't tell you if there were to less
	##          or too much fields (good for debuging)
	##
	## returns the insert clause. It's on you to modify it
	## and send it to your DB
	##
	function insert_plain_Clause ($table,$fields,$special,$check="soft") {
		eval("\$db = new ".$this->database_class.";");
		$meta=$db->metadata($table,true);

		for ($i=0; $i < $meta["num_fields"]; $i++) {
			$j=$meta[$i]["name"];
			## NOT IMPLEMENTED: SEARCHING FOR $fields[$i]
			list($val["val"][$i],$val["name"][$i])=
			     $this->convert($fields[$j],$meta[$i],$special[$j]);
		}
		if (Count($fields)!=Count($val["name"]) && $check=="strong") {
			echo "WARNING: There are not the same number of".
			     " fields as in table for INSERT<BR>";
		}
		$q=sprintf("INSERT INTO %s (%s) VALUES (%s)",
		   $table,join($val["name"],","),
		          join($val["val"],","));
		$this->_dbug($q);
		return($q);
	}

	# Replace, a special mySQL-function, same as INSERT
	function replace_plain_Clause ($table,$fields,$special,$check="soft") {
		eval("\$db = new ".$this->database_class.";");
		$meta=$db->metadata($table,true);

		for ($i=0; $i < $meta["num_fields"]; $i++) {
			$j=$meta[$i]["name"];
			## NOT IMPLEMENTED: SEARCHING FOR $fields[$i]
			list($val["val"][$i],$val["name"][$i])=
			     $this->convert($fields[$j],$meta[$i],$special[$j]);
		}
		if (Count($fields)!=Count($val["name"]) && $check=="strong") {
			echo "WARNING: There are not the same number of".
			     " fields as in table for INSERT<BR>";
		}
		$q=sprintf("REPLACE %s (%s) VALUES (%s)",
		   $table,join($val["name"],","),
		          join($val["val"],","));
		$this->_dbug($q);
		return($q);
	}

	##
	## This function is nearly the same, as insert_Clause,
	## The where parameter is new and should be generated by yourself
	## The check parameter knows 3 values: strong, soft and weak
	## weak enables you to sent a query without $where (enables you
	## to update the hole table)
	##
	function update_plain_Clause ($table,$fields,$special,$where,$check="soft") {
		eval("\$db = new ".$this->database_class.";");
		$meta=$db->metadata($table,true);
		if (!$where && $check!="weak") {
			echo "ERROR: Parameter \$where is empty!<BR>";
			return(false);
		}

		for ($i=0; $i < $meta["num_fields"]; $i++) {
			$j=$meta[$i]["name"];
			## NOT IMPLEMENTED: SEARCHING FOR $fields[$i]
			list($val["val"][$i],$val["name"][$i])=
			     $this->convert($fields[$j],$meta[$i],$special[$j]);
#echo "V: ".$val["name"][$i]." : ". $val["val"][$i]." - ".$fields[$j]."<BR>";
		}
		if (Count($fields)!=Count($val["name"]) && $check=="strong") {
			echo "WARNING: There are not the same number of".
			     " fields for INSERT<BR>";
		}
		for ($i=0 ; $i < Count ($val["name"]); $i++ ) {
			$s[]=$val["name"][$i]."=".$val["val"][$i];
		}
		$q=sprintf("UPDATE %s SET %s",$table,join($s,","));
		if ($where) {
			if (!eregi("^[[:space:]]*WHERE",$where)) {
				## insert "WHERE" if not set
				$where="WHERE $where";
			}
			$q.=" $where";
		}
		$this->_dbug($q);
		return($q);
	}

	##
	## DELETE
	## deletes the selected Table
	## $check can be "soft" and "weak". Weak let's you delete the
	## hole table
	##
	function delete_Clause ($table,$where,$check="soft") {
		if (!$where && $check!="weak") {
			echo "ERROR: Parameter \$where is empty!<BR>";
			return(false);
		}

		$q=sprintf("DELETE FROM %s",$table);
		if ($where) {
			if (!eregi("^[[:space:]]*WHERE",$where)) {
				## insert "WHERE" if not set
				$where="WHERE $where";
			}
			$q.=" $where";
		}
		$this->_dbug($q);
		return($q);
	}

	##
	## This function checks wether in table $table a
	## field $name is set with value $val
	##
	## it returns the number of found matches or zero
	##
	function exists ($table,$name,$val) {
		eval("\$db = new ".$this->database_class.";");
		$meta=$db->metadata($table,true);
		$j=$meta["meta"][$name];
		list($k)=$this->convert($val,$meta[$j]["type"]);
		$q=sprintf("SELECT COUNT(%s) as c FROM %s WHERE %s=%s",
		   $name,$table,$name,$k);
		$this->_dbug($q);
		$db->query($q);
		$db->next_record();
		return($db->f("c"));
	}





	##
	## WHERE-CLAUSE
	## Let you generate a WHERE-Clause in a Loop.
	##
	## Returns a where-clause beginning with " WHERE "
	##
	## This function generates a where-clause
	## $where     An array of simple expressions, eg. "firstname='Alex'"
	## $andor     This string is printed bewtween the where-Array
	##            default is 'AND'. It will handle an existing
	##            $oldwhere correctly. You can set this to '', but then
	##            the correct operator must be set by you in the where
	## $oldwhere  an existing WHERE-clause. Default is empty.
	## $check     if 'strong', it will stop, if an empty where-clause
	##            will be returned, to avoid "full" selects. Default is soft
	##
	function where_Clause ($where,$andor='AND',$oldwhere='',$check="soft") {
		echo "currently not working";
		exit;
	}

	##
	## ANOTHER-WHERE-CLAUSE
	##
	## This function generates a where-clause
	## $tables    A space or comma separated list of tables
	## $fields    Assoc name=>value-fields
	## $op        The operator. If empty, '=' is taken. it is printed
	##            *between* the name/value pairs.
	##            if $op is 'func' the name is taken as function name,
	##            inside the brakets is the value.
	## $special   Affects the calculation of value.
	##            See INSERT for more about this.
	## $andor     This string is printed bewtween the name/value-pairs,
	##            default is 'AND'. If $where is set, it prints
	##            it directly at the end before concatenating
	## $where     an existing WHERE-clause. Default is empty.
	## $check     if 'strong', it will stop, if an empty where-clause
	##            will be returned, to avoid "full" selects. Default is soft
	##
	## Returns a where-clause beginning with " WHERE "
	##
	function where_another_Clause ($tables,$fields,$op,$special,
	                               $andor='AND',$where='',$check="soft") {
		echo "currently not working!";
		exit;
	}


	##
	## capture-vars
	##
	## This function returns an assoc. Array consisting out of
	## name=>value-pairs needed by all the other functions. It reads
	## the name of the vars from the fields in $table and the values
	## from the $GLOBALS-var-field.
	## This has the sense, that you can name the variables in your
	## Input-Form exactly like the names in your table. This again
	## let make you less errors and less side effects.
	##
	## $table     The name of the table
	##
	function capture_vars ($table) {
		eval("\$db = new ".$this->database_class.";");
		$meta=$db->metadata($table,true);
		for ($i=0; $i < $meta["num_fields"]; $i++) {
			$j=$meta[$i]["name"];
			if (isset($GLOBALS[$j])) {
			         $r[$j] = $GLOBALS[$j];
			}
		}
		return($r);
	}

}

?>
