<%
// Modified 2002-01-14 by Wendy Roseberry 

/*
DISCLAIMER AND COPYRIGHT NOTE
Copyright (C) 2001  n-dee (n-dee@softhome.net)

This utility is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This utility is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, you may visit 
http://www.gnu.org/copyleft/gpl.html write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,
USA.
*/



// Here we start with our input form

class DateCalc {

	function DateCalc($Config){
		require ('holidays.php');
		$this->Holidays = array_merge($configHolidays, $Config['myHolidays']);
		//$this->Debug = 1;
	}
		
	function getTargetDate($input_hash){
		$this->intUnixTargetDate = '';
		$startyear = $input_hash['startyear'];
		$startmonth = $input_hash['startmonth'];
		$startday = $input_hash['startday'];
		$num_days = $input_hash['num_days'];
		$business_days= $input_hash['business_days'];
		
			
		//$business_days = 1 or '' triggers calculate business days
	
		// the startdate is composed just like mySQL date results
		$startdate = "$startyear-$startmonth-$startday";
	
		// here we run calculation, if no enddate is specified, but days instead
	
		$string = ($business_days == 1 ) ? "businessdays" : "calendardays";
		// converting Startdate to integer unixvalue
		$intUnixStartDate=$this->ConvertMysqlDate($startdate);
		$arrStartDate=getdate($intUnixStartDate);
		$startWeekday=$arrStartDate['wday'];

		$intOriginalTargetDate = $intUnixStartDate+($num_days*86400);
		$arrOrigEndDate=getdate($intOriginalTargetDate);

		if($business_days == 1) {
			
			// Calculate Enddate with result in two vars: 'no of special days' and 'new target date'
			$this->CalculateEndDate($intUnixStartDate,$num_days);
			
			settype($this->intUnixTargetDate,"integer");
			$arrEndDate=getdate($this->intUnixTargetDate);
			
		}
		elseif($business_days ==0) {
			$intUnixStartDate = $intOriginalTargetDate;
		}
		// ### assign vars to be returned
		$requested_formula = $arrStartDate['weekday'].", ".date("d.m.y",$intUnixStartDate)." plus ".$num_days." ".$string;
		$original_target = $intOriginalTargetDate; 
		$real_target = $this->intUnixTargetDate; 
		
		return $real_target;
	}
	function getNumDays($input_hash){
		// here we run calculation, if enddate is specified, but we need to know no. of days
		$startyear = $input_hash['startyear'];
		$startmonth = $input_hash['startmonth'];
		$startday = $input_hash['startday'];
		$endyear = $input_hash['endyear'];
		$endmonth = $input_hash['endmonth'];
		$endday = $input_hash['endday'];
		$business_days= $input_hash['business_days'];
		
		$startdate = "$startyear-$startmonth-$startday"; 
		$enddate = "$endyear-$endmonth-$endday"; 
		$string = ($business_days == 1 ) ? "businessdays" : "calendardays";
		// converting dates to integer unixvalues
		$intUnixStartDate=$this->ConvertMysqlDate($startdate);
		$intOriginalTargetDate=$this->ConvertMysqlDate($enddate);
		$arrStartDate=getdate($intUnixStartDate);
		$startWeekday=$arrStartDate['wday'];
		$arrOrigEndDate=getdate($intOriginalTargetDate);
		$num_days = ($intOriginalTargetDate-$intUnixStartDate)/86400;
		$weekdays = $num_days;
		if($business_days == 1) {
			$counter=0;
			$this->intUnixTargetDate = $intUnixStartDate;
			
			for($x=0; $x < abs($num_days); $x++) {
				$this->intUnixTargetDate += 86400;
				
				$test=FALSE;
				$test=$this->CheckHolidays($this->intUnixTargetDate);
				if($test == TRUE) {
					$counter++;
				}
			}
				
				if ($weekdays < 0){
					$abs_week = abs($weekdays);
					$weekdays = -($abs_week - $counter);
				}else{
					$weekdays -= $counter;
				}
				
			}
		// ASSIGN VARS TO BE RETURNED
		
 			$orig_startdate = $intUnixStartDate;
 			$orig_targetdate = $intOriginalTargetDate;
          	$diff_calendar_days = $num_days;
          	$diff_business_days = $weekdays;
          	if ($business_days == 1){
          		return $diff_business_days;
          	}else{
          		return $diff_calendar_days;
          	}
          	
	}
	
	function CalculateEndDate($startDate,$num_days) {
		
		
		$counter=0;
		for($x=1; $x<=$num_days; $x++) {
			$this->intUnixTargetDate = $startDate;
			$this->intUnixTargetDate = $this->intUnixTargetDate+($x*86400);
			$test=FALSE;
			$test=$this->CheckHolidays($this->intUnixTargetDate);
			if($test == TRUE) {
				$counter++;
			}
		}
		
		if($counter != 0) {
			$this->intSpecialDays += $counter;
			if($this->Debug) {
				echo "<br><small><strong>$counter special days!</strong>";
				echo "&nbsp;This is ".date("d.m.y",$this->intUnixTargetDate)." plus ".$counter." days</small><br><br>";
			}
			$this->CalculateEndDate($this->intUnixTargetDate,$counter);
			}
		elseif($counter == 0) {
			
			return TRUE;
		}
		return FALSE;
	}

	// simple function for users who use date format from mySQL
	function ConvertMysqlDate($thisDate) {
		$arrDateVals = explode("-",$thisDate);
		$intUnixDate = mktime(0,0,0,$arrDateVals[1],$arrDateVals[2],$arrDateVals[0]);
		return($intUnixDate);
	}	
	
	function CheckHolidays($thisDay) {
		
		// holidays are an array either manually set, included from file or from database
		
		$arrTargetDate=getdate($thisDay);
		
		
		// Calculate Public Holidays
		for($x=0; $x<count($this->Holidays); $x++) {
			$ftdate=$this->ConvertMysqlDate($this->Holidays[$x]);
			if($thisDay==$ftdate) {
				if($this->Debug) {
					echo "<small><b>".date("d.m.y",$thisDay)." is a Holiday !</b></small><br>";
					}
				return TRUE;
				}
			}
			// here we need to check, whether day is a saturday
			
			if($arrTargetDate['wday']==6) {
				
				if($this->Debug) {
					echo "<small><b>".date("d.m.y",$thisDay)." is a Saturday!</b></small><br>";
					}
				return TRUE;
				}
			// here we need to check, whether day is a sunday
			elseif($arrTargetDate['wday']==0) {
				if($this->Debug) {
					echo "<small><b>".date("d.m.y",$thisDay)." is a Sunday!</b></small><br>";
					}
				return TRUE;
				}
		return FALSE;
	}               
}