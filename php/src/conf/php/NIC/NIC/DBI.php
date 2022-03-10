<?

class DBI extends NIC{

  function DBI($databaseid, $database='test', $override = array()){

    // import database id definitions
    $this->importDefs('DB');

    // error checking
    if(!$this->Config['DB']['Databases'][$databaseid]){
      echo 'Invliad database id';
    }
  
    

      
    // create a PEAR db object
    require_once('DB.php');
//    $db = DB::connect( "mysql://$dbuser:$dbpass@$dbhost/$dbname" );

    $db = DB::connect( 
      "mysql://".
      $this->Config['DB']['Databases'][$databaseid]['username'].
      ":".
      $this->Config['DB']['Databases'][$databaseid]['password'].
      "@".
      $this->Config['DB']['Databases'][$databaseid]['host'].
      "/$database" );
  
  }
  
  
  
}

?>