<?

class sqltools{

  var $Auto_Strip_Slashes;


  function Auto_Strip_Slashes($val){
    $this->Auto_Strip_Slashes = $val;
  }

  function LogToBrowser($val = 0){
    $this->LogToBrowser = $val;
  }
  
  function doLogToBrowser($sql){
    if($this->LogToBrowser==1){
      #echo "\n\n<!-- SQL: $sql -->\n\n";
    }  
  }
  
  function evaluate($val){
  	$dummyLink = mysql_connect('db.dev', 'sqltools', '');
    if( ereg('FORMULA: ', $val) ){
      return substr($val, 9);
    }else{
      $val = mysql_real_escape_string($val);
      return "'$val'";    
    }
  }

  function logSQL($type, $sql){
    if( $this->LogFile[$type] ){
      global $REMOTE_USER;
      $fd = @fopen($this->LogFile[$type], "a");
      if( $fd ){
        fputs( $fd, sprintf("%-15s %-20s %s\n", $REMOTE_USER, date("m/d/Y H:i"), $sql) );
        fclose($fd);
      }
    }
  }  
  
  function setLogFile($logfile, $type){  
    $this->LogFile[$type] = $logfile;
  }
  
  function getId($table, $field){
    $res = $this->fetchrow("select max($field) from $table");
    if(!$res[0]){ $res[0] = 0; }
    return $res[0]+1;
  }


  function makeUpdate($table, $parts, $condition){


    $sql = "update $table set ";
    
    while( list($field,$val) = each($parts)){
      $sql.="$field = " . $this->evaluate($val) . ", ";        
    }
    
    $sql = ereg_replace(", $", "", $sql);

    if($condition){
      $sql.=' where ' . $condition;
    }

    
    $this->logSQL('makeUpdate', $sql);    
    
    return $sql;
    
  }

  function makeInsert($table, $parts){


    $sql = "insert into $table (";
    
    while( list($field,$val) = each($parts)){
      $sql.=$field . ', ';
      $sql2.= $this->evaluate($val) . ", ";
    }
    
    $sql = ereg_replace(", $", "", $sql);
    $sql2 = ereg_replace(", $", "", $sql2);    

    $sql .= ')values(' . $sql2 . ')';
    
    $this->logSQL('makeInsert', $sql);
    
    return $sql;
    
  }

  /* function makeReplace($table, $parts){

    $sql = "replace into $table (";
    
    while( list($field,$val) = each($parts)){
      $sql.=$field . ', ';
      $sql2.= $this->evaluate($val) . ", ";
    }
    
    $sql = ereg_replace(", $", "", $sql);
    $sql2 = ereg_replace(", $", "", $sql2);    

    return $sql . ')values(' . $sql2 . ')';
    
  }  */
  
  function makeReplace($table, $parts){


    $sql = "replace into $table (";
    
    while( list($field,$val) = each($parts)){
      $sql.=$field . ', ';
      $sql2.= $this->evaluate($val) . ", ";
    }
    
    $sql = ereg_replace(", $", "", $sql);
    $sql2 = ereg_replace(", $", "", $sql2);    

    return $sql . ')values(' . $sql2 . ')';
    
  }

  function niceDate($dt){
    return ereg_replace("([0-9]+)-([0-9]+)-([0-9]+)","\\2/\\3/\\1",$dt);
  }
  

  function makesqllist($n,$k, $vals){
  	$dummyLink = mysql_connect('db.dev', 'sqltools', '');
    if($k['QUAL'] == 'orlist'){
      $qual = 'or';
    }else{
      $qual = 'and';    
    }

    if( count($k['values'])>0 ){
      $t = $k['values'];
      for($i=0; $i<count($t); $i++){
        $t[$i] = str_replace('PARAM', mysql_real_escape_string($t[$i]), $k['name']); 
		    
      }

      $sql = implode(' ' . $qual . ' ', $t);
      return '(' . $sql . ')';
    
    }else{
      return 0;
    } 

    return 0;
  }

  function getParts($p, $top, $vals){
  	$dummyLink = mysql_connect('db.dev', 'sqltools', '');
    $parts = array();
  
    foreach( $p as $k=>$v ){
    
      if($v['QUAL'] == 'orlist'){
        $t = $this->makesqllist($k,$v, $vals);
        if($t != '0'){
          array_push($parts, $t);      
        }

     }else if($v['QUAL'] == 'andlist'){
     	
        $t = $this->makesqllist($k,$v, $vals);
        if($t != '0'){
          array_push($parts, $t);      
        }
        
      }else if( is_array($v) ){
        $t = $this->getParts($v, 0, $vals);
  
        if($t != '0'){
          array_push($parts, $t);
        }

     }else if($k == 'QUAL'){
     	
        $qual = $v;        
      }else{
			  if( (is_array($vals)) && ($vals[$k]) ){
          array_push($parts, str_replace('PARAM', mysql_real_escape_string($vals[$k]), $v));				
				}else if($vals == 0){
  				array_push($parts, $k . ' ' . str_replace('PARAM', mysql_real_escape_string($vals[$k]), $v));
				}
      }
    }

    if(count($parts) == 0){
      return 0;
    }

    $sql = implode(' ' . $qual . ' ', $parts);       
    
    if( ($top == 0) && (count($parts)>1) ){
      $sql = '(' . $sql . ') ';      
    }

    return $sql;
  }

  function makeSelect($param){
   
    
    $sql = 'SELECT ';
    
    $sql .= implode(", ", $param['SELECT']);
    
    $sql .= ' FROM ';
    
    $sql .= implode(", ", $param['FROM']);    
   
	 if( (count($param['WHERE']) > 0) && (!$param['VALUES']) ){
	  
		  $ending = $this->getParts($param['WHERE'], 1, 0);
		 
      if (!$ending){
      	$ending = $param['WHERE']['blank'];
      }    
       
      if($ending){ 
        $sql .= ' WHERE ' . $ending;
      }		
		
   }else if(count($param['WHERE']) > 0){
      $ending = $this->getParts($param['WHERE'], 1, $param['VALUES']);
  
      if (!$ending){
      	$ending = $param['WHERE']['blank'];
      }    
       
      if($ending){ 
        $sql .= ' WHERE ' . $ending;
      }
    }

    if(count($param['PARSE']) > 0){
		  foreach($param['PARSE'] as $k => $v){
			  list($search, $score, $nice) = $this->parseSearch($param['VALUES'][$k], $v);
				$this->ParseNice = $nice;
				$this->WordList = $score;
        $sql .= ' WHERE ' . $search;
			}
		}		
		
    if(count($param['JOIN']) > 0){
      $sql .= ' and ' . implode(' and ', $param['JOIN']);
    }
    
    if(count($param['ORDERBY']) > 0){
      $sql .= ' ORDER BY ' . implode(', ', $param['ORDERBY']);
    }
    
    if(count($param['OTHER']) > 0){
      $sql .= ' ' . implode(' ', $param['OTHER']);
    }    
    return $sql;
    
  }

  function makeCount($param){
    $sql = 'SELECT count(*) ';
    
    $sql .= ' FROM ';
    
    $sql .= implode(", ", $param['FROM']);    
    
    if(count($param['WHERE']) > 0){
      $ending = $this->getParts($param['WHERE'], 1, $param['VALUES']);
  
      if (!$ending){
      	$ending = $param['WHERE']['blank'];
      }
      
      if($ending){ 
        $sql .= ' WHERE ' . $ending;
      }      
      
     
    }
    
    if(count($param['JOIN']) > 0){
      $sql .= ' and ' . implode(' and ', $param['JOIN']);
    }
   
    return $sql;
    
  }
	
  function isWord($word){
  	 if( ($word != 'and')&&
  	     ($word != 'or')&&
  	     ($word != 'not') ){
  	 	  return 1;
  	 }else{
  	   return 0;
  	 }
  }	

  function parseSearch($phrase, $field){
  	
  	$parts = array();
  	$words = split(" ", strtolower($phrase));
  	
  	$mode = 1;
  	for($i=0; $i<count($words); $i++){

  		if($this->isWord($words[$i])){
  			// got a word
  		  if($mode == 1){
  		  	// got a word, need a word
  		  	$parts[] = $words[$i];
  		  	$mode = 0;
  		  }else{
  		    // got a word but need a qualifier  
  		    $parts[] = 'and';
  		    $parts[] = $words[$i];
  		  }
  		}else{
  			// got a word
  		  if($mode == 1){
  		  	// got a qualifier but need a word
  		  	$mode = 1;
  		  }else{
  		    // got a qualifier need a qualifier  
  		    $parts[] = $words[$i];
  		    $mode = 1;
  		  }
  			
  		}
	
  	}

  	$score = array();
  	for($i=0; $i<count($parts); $i+=2){
  		if($parts[$i+1] != 'not'){
  			$score[] = $parts[$i];
  		}
  	}
  	
  	$nice = array();
  	
  	for($i=0; $i<count($parts); $i++){
  		if($this->isWord($parts[$i])){
  			$nice[] = $parts[$i];
  			$parts[$i] = str_replace("'", "''", $parts[$i]);
  		  //$parts[$i] = '(content like "%'.$parts[$i].'%" or title like "%'.$parts[$i].'%")';	
				$parts[$i] = '(' . str_replace("PARAM", $parts[$i], $field) . ')';
  		}else{
  		  if($parts[$i] == 'not'){
  		  	$parts[$i] = 'and not';
  		  }
  		  $nice[] = $parts[$i];
  		}
  		
  	}
  	
  	
  	return array(join(' ', $parts), $score, $nice);
  	
  }	
	
  function scoreResult($words, $terms){
	  // $words = "this is what we pull out of the database";
		// $terms = array(this, what, pull)
		// score = 3
  	$points = 0;
  	foreach($terms as $word){
  			
  	  //preg_match("/$word/i", $words, $matches);
  	  $matches = preg_split("/$word/i", $words);
  	  
  	  //echo "(search for $word got ".count($matches).") in ".htmlspecialchars($words)."<br>";
  	  
  	  $points += count($matches)-1;
  		//echo count($matches) . " for $word in $words<br>";
  		
    }
    
    return $points;
  }	


	function SQL_tools_cmp ($a, $b) {   
    if ($a['SQL_score'] == $b['SQL_score']) return 0;
    return ($a['SQL_score'] < $b['SQL_score']) ? 1 : -1;
  }	
	
  function sortResults($qry, $terms, $fields){
	
	  $rows = array();
	
	  while($row = $this->fetch($qry)){
		  $row['SQL_score'] = 0;
			if( is_array($fields) ){
				foreach($fields as $f){
				  $row['SQL_score'] += $this->scoreResult($row[$f], $terms);
				}			
			}else{
   			$row['SQL_score'] += $this->scoreResult($row[$fields], $terms);
			}

		  $rows[] = $row;			
		}
		
  	//uasort ($rows, "SQL_tools_cmp");	
    uasort($rows,array($this,"SQL_tools_cmp"));
	  return $rows;
	}	

}



?>
