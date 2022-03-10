<?

// class TableColumns arranges a set of data into a table, 
// with items in order going down the columns instead of across. 
// 
// Example: 
// array: A B C D E F G 
// 
// comes out 
// 
// A D G 
// B E 
// C F 
// 
// A table with data running across is easy to do, but to get 
// the data to go down the columns you have to calculate the 
// number of items and determine the number per column, 
// etc.. this code does that for you.. 

// include('TableColumns.php');
// $tc = new TableColumns();
// 
// $Data = array(1,2,3,4,5,6,7,8);
// 
// $tc->makeTable($Data, 3);
// 
// 
// 
// You can alter the table tags, by default they are
// 
// $tc->table = '<table>';
// $tc->tr = '<tr>';
// $tc->td = '<td>';
// 
// so if you want the columns to be 150 pixels, do this:
// 
// $tc->td = '<td width="150">';


class TableColumns{

  function TableColumns(){
    $this->table = '<table>';
    $this->tr = '<tr>';
    $this->td = '<td>';        
  }

  function makeTable($data, $columns){
  
    $cnt = count($data);  
    
    $base = (int)($cnt / $columns);
    $base++;
    
    echo "\n$this->table";
    
    $pass = 0;
    for($i=0; $i<count($data); $i+=3){
      echo "\n$this->tr";
      
      for($j=0; $j<$columns; $j++){
        $b = $pass + ($j*$base);
        echo $this->td;
        echo $data[$b];
        echo '</td>';
      }
      
      echo '</tr>';
      $pass++;
    }
    
    echo "</table>\n";
  
  
  }
  
}



?>