<?
class Calendar{

	function Calendar($Prefs){
		$this->Pref = $Prefs;
	}

	function def_classes(){	
		?>
			<STYLE type=text/css>
			.month_table{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:white; background-color: #945129}
			.month_header{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:white; font-weight:bold; background-color: #945129}
			.active_day{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black;}
			.inactive_day{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:silver;}
			.current_day{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black;}
			.week_header{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; background-color: #F7DFBD}
			.week_row{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; background-color: #FFFFFF}
			.cal_header{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black;}

			.active_day_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:black;}
			.inactive_day_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:silver;}
			.hasevents_day_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:black; background-color: yellow}
			.istoday_day_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:red;}
		
			.week_header_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:black; background-color: #F7DFBD}			
			.week_row_mini{font-family: helvetica, arial, geneva, sans-serif; font-size: 8pt; color:black; background-color: #FFFFFF}
			
			.event_month{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; background-color: yellow;border: thin solid black }
			
			.day_header{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:white; font-weight:bold; background-color: #945129}
			.event_row{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; font-weight:normal; background-color: white}
			
			.week_title{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:white; font-weight:bold; background-color: #945129}
			.week_day{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; font-weight:normal; background-color: #F7DFBD}
			.week_events{font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; color:black; font-weight:normal; background-color: white}
			
			</STYLE>		
		<?
	}
	
	function build_meta($items){
		// get the earliest and latest dates
		// arrange items by date
		
		$this->begin_date = mktime();
		$this->items = array();
		
		foreach( $items as $i ){
			if( $i['datetime'] < $this->begin_date ){
				$this->begin_date = $i['datetime'];
			}
			
			$key = date("m/d/Y", $i['datetime']);
			
			if( !$this->items[$key] ){
				$this->items[$key] = array();
			}
			
			$this->items[$key][] = $i;			
		}
		
		$this->end_date = $this->begin_date;
		foreach( $items as $i ){
			if( $i['datetime'] > $this->end_date ){
				$this->end_date = $i['datetime'];
			}
		}		

	}
	
	function href($p=array()){
		$base = $this->Pref['base'];
		$url = array();
		foreach( $p as $k=>$v ){
			$url[] = "$k=$v";
		}
		$base .= '&'.join('&', $url);
		return $base;
	}	
	
	function show($Items, $def='all', $target=''){
		$this->build_meta($Items);
		$this->def_classes();
		
		if( $_REQUEST['show_month'] ){
			$def = 'month';
			$target = $_REQUEST['show_month'];
		}else if($_REQUEST['show_all']){
			$def = 'all';		
			$target = $_REQUEST['show_all'];			
		}else if($_REQUEST['show_week']){		
			$def = 'week';		
			$target = $_REQUEST['show_week'];			
		}else if($_REQUEST['show_day']){
			$def = 'day';		
			$target = $_REQUEST['show_day'];			
		}else if($_REQUEST['show_list']){
			$def = 'list';		
			$target = $_REQUEST['show_list'];			
		}else if($_REQUEST['show_event']){
			$def = 'event';		
			$target = $_REQUEST['show_event'];			
		}
		
		switch($def){
			case 'all': $this->show_all($target); break;
			case 'month': $this->show_month($target); break;
			case 'week':  $this->show_week($target); break;
			case 'day':   $this->show_day($target); break;
			case 'list':  $this->show_list($target); break;
			case 'event':  $this->show_event($target); break;
		}
	}
	

	
	
	function show_all(){
		// show the months needed
		$this->show_mini_calendars($this->begin_date, $this->end_date);		
	}
	
	function show_mini_calendars($begin, $end){
		
		$max_col = 2;
		$cols = 1;
		
		
		echo '<table border=0 cellpadding=3 cellspacing=3><tr><td>';
		
		$this->show_mini_calendar(date("m", $begin), date("Y", $begin));
		echo '</td>';
		while($begin < $end){			
			$begin += 60 * 60 * 24 * 31;
			if($cols >= $max_col){
				$cols = 0;
				echo '</tr><tr>';
			}			
			echo '<td>';
			$this->show_mini_calendar(date("m", $begin), date("Y", $begin));
			echo '</td>';			
			$cols++;
		}
		
		echo '</td></tr></table>';
		
	}


	
	function show_mini_calendar($month, $year){
		$month = mktime(0,0,0,$month,1,$year);
		$width = 20;
		$height = 20;
		$oneday = 60*60*24;
		
		// find the sunday near the beginning
		$start = $month;
		while( date("w", $start) > 0 ){
			$start -= $oneday;
		}
		
		// find the saturday near the end
		$end = strtotime("-1 day", strtotime("+1 month", $month));
		while( date("w", $end) < 6){
			$end += $oneday;
		}
		
		?>
			<table class=month_table cellpadding=3 cellspacing=1>
				<tr class=month_header>
					<td colspan=8 align=center><a class="month_header" href="<?=$this->href(array(show_month=>$month))?>"><?=date("F, Y", $month)?></a></td>
				</tr>
				<tr class=week_header_mini>
					<td align=center width="<?=$width?>" height="<?=$height?>"></td>				
					<td align=center width="<?=$width?>" height="<?=$height?>">Sun</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Mon</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Tue</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Wed</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Thu</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Fri</td>
					<td align=center width="<?=$width?>" height="<?=$height?>">Sat</td>																									
				</tr>
				<tr class=week_row_mini>
					<td align=center width="<?=$width?>" height="<?=$height?>"><a href="<?=$this->href(array(show_week=>$start))?>">week</a></td>
				<?
					$done = 0;
					for( $i=$start; $i<=$end; $i+=$oneday ){
						if($done == 7){
							?>
								</tr>
								<tr class=week_row_mini>
									<td align=center width="<?=$width?>" height="<?=$height?>"><a href="<?=$this->href(array(show_week=>$i))?>">week</a></td>
							<?
							$done = 0;
						}						
	
						if( $this->items[date("m/d/Y", $i)] ){
							$class = 'hasevents_day_mini';						
						}else	if( date("m/Y", $i) == date("m/Y", $month) ){
							$class = 'active_day_mini';						
						}else{
							$class = 'inactive_day_mini';
						}
						
						echo '<td class="'.$class.'" align=center width="'.$width.'" height="'.$height.'"><a class="'.$class.'" href="'.$this->href(array(show_day=>$i)).'">'.date("j", $i).'</a></td>';					
						$done++;
					}
				?>
				</tr>		
			</table>
		<?
	}

	function show_month($target){
		$month = $target;
		$width = 50;
		$height = 50;
		$wheight = 20;		
		$oneday = 60*60*24;
		
		// find the sunday near the beginning
		$start = $month;
		while( date("w", $start) > 0 ){
			$start -= $oneday;
		}
		
		// find the saturday near the end
		$end = strtotime("-1 day", strtotime("+1 month", $month));
		while( date("w", $end) < 6){
			$end += $oneday;
		}
		
		?>
			<table class=month_table cellpadding=3 cellspacing=1>
				<tr class=month_header>
					<td colspan=8 align=center><a class="month_header" href="<?=$this->href(array(show_month=>$month))?>"><?=date("F, Y", $month)?></a></td>
				</tr>
				<tr class=week_header_mini>
					<td align=center width="<?=$width?>" height="<?=$wheight?>"></td>				
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Sun</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Mon</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Tue</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Wed</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Thu</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Fri</td>
					<td align=center width="<?=$width?>" height="<?=$wheight?>">Sat</td>																									
				</tr>
				<tr class=week_row>
					<td align=center width="<?=$width?>" height="<?=$height?>"><a href="<?=$this->href(array(show_week=>$start))?>">week</a></td>
				<?
					$done = 0;
					for( $i=$start; $i<=$end; $i+=$oneday ){
						if($done == 7){
							?>
								</tr>
								<tr class=week_row>
									<td align=center width="<?=$width?>" height="<?=$height?>"><a href="<?=$this->href(array(show_week=>$i))?>">week</a></td>
							<?
							$done = 0;
						}						
	
						if( date("m/Y", $i) == date("m/Y", $month) ){
							$class = 'active_day';						
						}else{
							$class = 'inactive_day';
						}
						
						echo '<td class="'.$class.'" align=left valign=top width="'.$width.'" height="'.$height.'"><a class="'.$class.'" href="'.$this->href(array(show_day=>$i)).'">'.date("j", $i).'</a>';
						if( $this->items[date("m/d/Y", $i)] ){
							echo '<br>';
							$edone = 0;
							foreach( $this->items[date("m/d/Y", $i)] as $d ){
								if($edone >= $this->Pref['day_limit']){
									continue;
								}
								echo "<div class=event_month><a href=\"".$this->href(array(show_day=>$i))."\">" . substr($d[descr], 0, 15) . "</a></div>";
								$edone++;
							}
							
							if( count($this->items[date("m/d/Y", $i)]) > $this->Pref['day_limit'] ){
									echo "<div><a href=\"".$this->href(array(show_day=>$i))."\">more...</a></div>";
							}
							
						}
						echo '</td>';					
						$done++;
					}
				?>
				</tr>		
			</table>
		<?		
		
	}
	
	function show_week($target){
		// get the sunday
		if( date("w", $target) == 0 ){
			$sun = $target;
		}else{
			$sun = strtotime("last sunday", $target);		
		}
		// get the sat
		if( date("w", $target) == 6 ){
			$sat = $target;
		}else{		
			$sat = strtotime("next saturday", $target);
		}
		
		?>
			<table class="week_title" cellpadding=3 cellspacing=1>
					<tr>
						<td>Week of <?=date("D M jS", $sun)?> to <?=date("D M jS, Y", $sat)?></td>
						<td><a href="<?=$this->href(array(show_month=>$target))?>" class="day_header">month</a></td>
					</tr>			
				<?for( $i=$sun; $i<=$sat; $i+=60*60*24 ){?>
					<tr>
						<td colspan=2 class="week_day"><?=date("l F jS, Y", $i)?></td>
					</tr>
					<?
						if( !$this->items[date("m/d/Y", $i)]){
								?>
									<tr class="week_events">
										<td colspan=2>No events today</td>
									</tr>						
								<?							
						}else{					
							foreach( $this->items[date("m/d/Y", $i)] as $d ){
								?>
									<tr class="week_events">
										<td colspan=2><?=date("g:i a", $d['datetime'])?> - <?=$d['descr']?></td>
									</tr>						
								<?
							}			
						}	
					?>
			<?}?>
			</table>
		<?	
	}
	
	function show_day($target){
		if( !$this->items[date("m/d/Y", $target)] ){
			echo "No Events on this day";
			return 0;
		}
		
		?>
			<table class="day_header" cellpadding=3 cellspacing=1>
				<tr>
					<td><?=date("l F jS, Y", $target)?></td>
					<td align=right>
						<a href="<?=$this->href(array(show_week=>$target))?>" class="day_header">week</a> 
						| 
						<a href="<?=$this->href(array(show_month=>$target))?>" class="day_header">month</a>
					</td>					
				</tr>
				<?
					foreach( $this->items[date("m/d/Y", $target)] as $d ){
						?>
							<tr class="event_row">
								<td colspan=2><?=date("g:i a", $d['datetime'])?> - <?=$d['descr']?></td>
							</tr>						
						<?
					}				
				?>
			</table>
		<?
		
	}
	
	function show_list(){
		
	
	}
	

	
}
?>