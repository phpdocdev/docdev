<?
	function lc_array(&$rows){		
		if( is_array($rows[0]) ){
			for($i=0; $i<count($rows); $i++){
	    	$rows[$i] = array_change_key_case($rows[$i], CASE_LOWER);		
			}
		}else if(is_array($rows)){
    	$rows = array_change_key_case($rows, CASE_LOWER);		
		}else{
			
		}
	}
	
  function makeUpdate($table, $parts, $condition){


    $sql = "update $table set ";
    
    while( list($field,$val) = each($parts)){
      $sql.="$field = " . _evaluate($val) . ", ";        
    }
    
    $sql = ereg_replace(", $", "", $sql);

    if($condition){
      $sql.=' where ' . $condition;
    }
    return $sql;
    
  }

  function makeInsert($table, $parts){


    $sql = "insert into $table (";
    
    while( list($field,$val) = each($parts)){
      $sql.=$field . ', ';
      $sql2.= _evaluate($val) . ", ";
    }
    
    $sql = ereg_replace(", $", "", $sql);
    $sql2 = ereg_replace(", $", "", $sql2);    

    return $sql . ')values(' . $sql2 . ')';
    
  }

  function makeReplace($table, $parts, $condition){

    $sql = "replace into $table (";
    
    while( list($field,$val) = each($parts)){
      $sql.=$field . ', ';
      $sql2.= _evaluate($val) . ", ";
    }
    
    $sql = ereg_replace(", $", "", $sql);
    $sql2 = ereg_replace(", $", "", $sql2);
	$sql_statement= $sql . ')values(' . $sql2 . ')';
	
	if($condition){
      $sql_statement.=' where ' . $condition;
    }
    return $sql_statement;
  }	
	
  function _evaluate($val){
    if( ereg('FORMULA: ', $val) ){
      return substr($val, 9);
    }else{
      $val = mysql_escape_string($val);
      return "'$val'";    
    }

  }

?>