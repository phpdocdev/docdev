<?
class Whatajob{	
function buildhidden($keyname, $keycheck, $value){
		$ret = '<input type=hidden name='.$keyname.' value=';
		
		if (!$keycheck){
  				if ($value){
						foreach ($value as $n){
								$keycheck .= $n.'|';
					}
				}
		}
	$ret = $ret .$keycheck.'>';	
	
	return ($ret);
	    
}

function buildwhere($value, $field, $inwhere, $type){
	if ($value){
		foreach ($value as $s){
			if ($s){
				if ($type == 'like'){
						$end = $type . " '%".$s."%' ";
				}
				else {
					$end = $type . "'".$s."'";
				}
				if ($where) {
						$where = $where . " or $field $end";
				}
				else {
					$where = "$field $end";
				}
			}	
		}
	}
	if (($inwhere) && ($where)){
		return ' and ' .$where;
	}
	else {
		return $where;
	}
	
	
}

function getregion($county){
	switch ($county) {
        case "1":
        	$region = 'East';
        	break;
        case "2":
        	$region = 'East';
        	break;
        case "3":
        	$region = 'Northwest';
        	break;
        case "4":
        	$region = 'Northwest';
        	break;
        case "5":
        $region = 'Northwest';
        	break;
        case "6":
        $region = 'East';
        	break;
        case "7":
        $region = 'Southwest';
        	break;
        case "8":
        $region = 'Northwest';
        	break;
        case "9":
        $region = 'East';
        	break;
        case "10":
        $region = 'Southwest';
        	break;
        case "11":
        $region = 'East';
        	break;
        case "12":
        $region = 'North Central';
        	break;
        case "13":
        $region = 'East';
        	break;
        case "14":
        $region = 'Southwest';
        	break;
        case "15":
        $region = 'Central';
        	break;
        case "16":
        $region = 'East';
        	break;
        case "17":
        $region = 'Northwest';
        	break;
        case "18":
        $region = 'East';
        	break;
        case "19":
        $region = 'East';
        	break;
        case "20":
        $region = 'Southwest';
        	break;
        case "21":
        $region = 'East';
        	break;
        case "22":
        $region = 'East';
        	break;
        case "23":
        $region = 'Central';
        	break;
        case "24":
        $region = 'Northwest';
        	break;
        case "25":
        $region = 'North Central';
        	break;
        case "26":
        $region = 'Southwest';
        	break;
        case "27":
        $region = 'Southwest';
        	break;
        case "28":
        $region = 'East';
        	break;
        case "29":
        $region = 'Southwest';
        	break;
        case "30":
        $region = 'Southwest';
        	break;
        case "31":
        $region = 'Southwest';
        	break;
        case "32":
        $region = 'North Central';
        	break;
        case "33":
        $region = 'North Central';
        	break;
        case "34":
        $region = 'North Central';
        	break;
        case "35":
        $region = 'East';
        	break;
        case "36":
        $region = 'Northwest';
        	break;
        case "37":
        $region = 'Southwest';
        	break;
        case "38":
        $region = 'North Central';
        	break;
        case "39":
        $region = 'East';
        	break;
        case "40":
        $region = 'East';
        	break;
        case "41":
        $region = 'Southwest';
        	break;
        case "42":
        $region = 'Northwest';
        	break;
        case "43":
        $region = 'Central';
        	break;
        case "44":
        $region = 'Northwest';
        	break;
        case "45":
        $region = 'Northwest';
        	break;
        case "46":
        $region = 'Southwest';
        	break;
        case "47":
        $region = 'East';
        	break;
        case "48":
        $region = 'East';
        	break;
        case "49":
        $region = 'Southwest';
        	break;
        case "50":
        $region = 'Southwest';
        	break;
        case "51":
        $region = 'Northwest';
        	break;
        case "52":
        $region = 'Southwest';
        	break;
        case "53":
        $region = 'Central';
        	break;
        case "54":
        $region = 'East';
        	break;
        case "55":
        $region = 'Southwest';
        	break;
        case "56":
        $region = 'East';
        	break;
        case "57":
        $region = 'Southwest';
        	break;
        case "58":
        $region = 'Northwest';
        	break;
        case "59":
        $region = 'Central';
        	break;
        case "60":
        $region = 'Central';
        	break;
        case "61":
        $region = 'North Central';
        	break;
        case "62":
        $region = 'Central';
        	break;
        case "63":
        $region = 'Northwest';
        	break;
        case "64":
        $region = 'Northwest';
        	break;
        case "65":
        $region = 'Northwest';
        	break;
        case "66":
        $region = 'Southwest';
        	break;
        case "67":
        $region = 'North Central';
        	break;
        case "68":
        $region = 'East';
        	break;
        case "69":
        $region = 'North Central';
        	break;
        case "70":
        $region = 'Southwest';
        	break;
        case "71":
        $region = 'Northwest';
        	break;
        case "72":  
        $region = 'Northwest';
        	break;
        case "73":
        $region = 'Central';
        	break;
        case "74":
        $region = 'East';
        	break;
    		case "75":
    		$region = 'Northwest';
        	break;
       }
     return $region;
  }
  
}
?>