<?php
class SQL{

  var $conn_id;
  var $q_res;

  function SQL($database, $dbuser='root', $dbpass='', $dbhost='localhost'){
    $this->conn_id = mysql_connect($dbhost, $dbuser, $dbpass) or die("Error in sql.php");
    $this->database = $database;
  }


  function Run($sql){
    $this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or die("Invalid query: $sql");    
    return $this->q_res;
  }

  function fetchrow($sql){
    $this->q_res = mysql_db_query($this->database, $sql, $this->conn_id) or die("Invalid query: $sql");    
    return mysql_fetch_array($this->q_res);     
  }

  function fetch(){
    return mysql_fetch_array($this->q_res);     
  }

  function getLimit($matchlimit, $run){
  
   if($run == 0){
     $low = 0;
     $high = $matchlimit;   
     
   }else{
     $low = $run * $matchlimit;
     $high = (($run+1) * $matchlimit)-1; 
   }
   
   return array($low, $high);  
  
  }

  function getSpan($searchpage, $script, $matchcount, $matchlimit, $run, $paramlist){
  
   if($matchcount == 0){
     return '
       <table align="center">
         <tr>
           <td><font face="arial" size="2">
             No results were found
           </td>
         </tr>
         <tr>
           <td><font face="arial" size="2">
             <a href="'.$searchpage.'">Back to Search Page</a>           
           </td>
         </tr>         
       </table>';
   }
  
   while( list($k,$v) = each($paramlist) ){
     if( ($k != 'run') && ($k != 'matchcount') ){
       $params .= '&' . $k . '=' .$v;
     }
   }

  
   if($run == 0){
     $low = 0;
     $high = $matchlimit;   
     
   }else{
     $low = $run * $matchlimit;
     $high = (($run+1) * $matchlimit); 
   }
  
   if($run == 0){
     $prevlink = "<a href=\"$searchpage\">Back to Search Page</a>";
     $GotBackLink=1;
   }else{
     $trun = $run-1;
     $prevlink = "<a href=\"$script?run=$trun&matchcount=$matchcount$params\">Previous $matchlimit</a>";
   }
   
   $run++;
   if(((($run+1) * $matchlimit)-1) > $matchcount){
     $set = $matchcount - $high;
     
     if($high > $matchcount){
       $high = $matchcount;
     }
     
     if($set <= 0){
       $nextlink = "<a href=\"$searchpage\">Back to Search Page</a>";     
       $GotBackLink=1;     
     }else{
       $nextlink = "<a href=\"$script?run=$run&matchcount=$matchcount$params\">Next $set</a>"; 
     }
     
   }else{
     $nextlink = "<a href=\"$script?run=$run&matchcount=$matchcount$params\">Next $matchlimit</a>";
   }   
    
   $ret = '<table width="100%">
           <tr>
             <td colspan="3"><hr size="1" width="100%"></td>
           </tr>   
           <tr>
             <td width="20%" align="left"><font face="arial" size="2">'.$prevlink.'</td>
             <td width="60%" align="center"><font face="arial" size="2"><b>' . ($low+1) . ' - ' . $high . ' of ' . $matchcount . ' Matches found</b></td>
             <td width="20%" align="right"><font face="arial" size="2">'.$nextlink.'</td>                      
           </tr>';
           
   if(!$GotBackLink){
     $ret.='<tr>
             <td width="20%" align="left"><font face="arial" size="2"></td>
             <td width="60%" align="center"><font face="arial" size="2"><a href="'.$searchpage.'">Back to Search Page</a></td>
             <td width="20%" align="right"><font face="arial" size="2"></td>                      
           </tr>';   
   }           
           
   $ret .='<tr>
             <td colspan="3"><hr size="1" width="100%"></td>
           </tr>
         </table>';  
  

    return $ret;
  }

  function makeSelect($fields, $tables, $condition, $order, $limit1, $limit2){
    $sql = "select ";

    for($i=0; $i<count($fields); $i++){
      $sql.=$fields[$i];
      if($i<count($fields)-1){
        $sql.=', ';
      }
    }

    $sql.=" from ";

    for($i=0; $i<count($tables); $i++){
      $sql.=$tables[$i];
      if($i<count($tables)-1){
        $sql.=', ';
      }
    }

    if($condition){
      $sql.=' where ' . $condition;
    }
    
    if($order){
      $sql .= ' order by ' . $order;
    }

    if(is_int($limit1) && is_int($limit2)){
      $sql .= ' LIMIT ' . $limit1 . ', ' . $limit2;
    }

    return $sql;

  }

  function evaluate($val){
    if( ereg('FORMULA: ', $val) ){
      return substr($val, 9);
    }else{
      $val = addslashes($val);
      return "'$val'";    
    }

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

    return $sql . ')values(' . $sql2 . ')';
    
  }

  function niceDate($dt){
    return ereg_replace("([0-9]+)-([0-9]+)-([0-9]+)","\\2/\\3/\\1",$dt);
  }
  


}

?>
