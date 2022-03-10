<?
class Dates{
	function fromnow($compdate, $interval){
		if ($interval == 'm'){
			$date1array  = getdate(strtotime ($compdate));
  		$date2array  = getdate(strtotime (now));
  		$retval = $date1array["mon"] - $date2array["mon"];
  
  	}
  	if ($interval == 'Y'){
  		$date1array  = getdate(strtotime ($compdate));
  		$date2array  = getdate(strtotime (now));
  		$retval = $date1array["year"] - $date2array["year"];
  	
    }
    else{
		$now = strtotime(now);
		$then = strtotime($compdate);
		if ($now > $then){
			$diff = ($now - $then);
		}
		else {
			$diff = ($then - $now);
		}
		switch ($interval) {
        case "w":
            $retval  = ($diff /604800);
            break;
        case "d":
            $retval  = ($diff/86400);
            break;
        case "h":
             $retval =  ($diff/3600);
            break;        
        case "i":
            $retval  =  ($diff/60);
            break;        
        case "s":
            $retval  = $diff;
        break;        
    }
    }    
    return $retval;
	}


	function datediff($date1, $date2, $interval){
		if ($interval == 'm'){
			$date1array  = getdate(strtotime ($date1));
  		$date2array  = getdate(strtotime ($date2));
  		$retval = $date1array["mon"] - $date2array["mon"];
  
  	}
  	if ($interval == 'Y'){
  		$date1array  = getdate(strtotime ($date1));
  		$date2array  = getdate(strtotime ($date2));
  		$retval = $date1array["year"] - $date2array["year"];
    }
  	else {
		$date1= strtotime($date1);
		$date2 = strtotime($date2);
		$diff = $date1 - $date2;
		switch ($interval) {
        case "w":
            $retval  = ($diff /604800);
            break;
        case "d":
            $retval  = ($diff/86400);
            break;
        case "h":
             $retval =  ($diff/3600);
            break;        
        case "i":
            $retval  =  ($diff/60);
            break;        
        case "s":
            $retval  = $diff;
        break;        

    }  
    }  
		return ($retval);
	}
	
	function dateadd ($date,  $number, $interval, $returnstring) {

    $date_time_array  = getdate(strtotime ($date));
    
		$hours =  $date_time_array["hours"];
		$minutes =  $date_time_array["minutes"];
		$seconds =  $date_time_array["seconds"];
		$month =  $date_time_array["mon"];
		$day =  $date_time_array["mday"];
		$year =  $date_time_array["year"];

    switch ($interval) {
    
        case "Y":
            $year +=$number;
            break;        
        case "m":
            $month +=$number;
            break;        
        case "d":
            $day+=$number;
            break;        
        case "w":
             $day+=($number*7);
            break;        
        case "h":
             $hours+=$number;
            break;        
        case "i":
             $minutes+=$number;
            break;        
        case "s":
             $seconds+=$number;
            break;        

    }    
			$timestamp =  date ($returnstring, mktime($hours ,$minutes, $seconds,$month ,$day, $year));
    	return $timestamp;
	}


}
?>	
