<?

// images needed for this function
//   lefttaba.gif
//   righttaba.gif
//   lefttabi.gif
//   righttabi.gif
//   stretch.gif
//   bar.gif

function render_tabs3($active, $param){	

		if(!$param['align']){
			$param['align'] = 'left';
		}

		if(!$param['tab_position']){
			$param['tab_position'] = 'top';
		}

		if(!$param['tab_bar']){
			$param['tab_bar'] = 'bottom';
		}

		if( $param['tab_position'] == 'bottom'){
			$t = '_t';
		}else{
			$t = '';		
		}
		
		
		if( !$param['tabs'][$active] && is_array($param[opts])){
			// look in opts
			foreach($param['opts'] as $page=>$opts){
				foreach($opts as $subp=>$opts2){
					if($active == $subp){
						$active = $page;
						$subpage = $subp;
					}
				}
			}
		}

		if($param[opts][$active] && !$subpage){
			// subpage defaults to first
			$subpage = key($param[opts][$active]);
		}

		ob_start();
		ob_implicit_flush(0);
		
		if($param['tab_bar'] == 'top'){
			?>
				<table cellSpacing="0" cellPadding="5" border="0" width="100%" class="<?=$param['active_class']?>">
				  <tr>
				    <td width="100%" class="<?=$param['active_class']?>"><?=$text?></td>
				  </tr>
				</table>			
			<?
		}
		
		
		echo '<table width="100%" cellpadding=0 cellspacing=0 border=0><tr><td><table border="0" cellspacing="0" cellpadding="0" align='.$param['align'].'>';		
		echo '<tr>';
		$i=0;
		foreach($param['tabs'] as $id=>$link){
			$i++;
			if($active == $id){
				if( $param['tab_position'] == 'bottom'){
					?>
					  <td class="<?=$param['active_class']?>" rowspan="3" valign="top" width="7"><font face="Arial"><img src="RESOURCE/lefttaba<?=$t?>.gif" width="8" height="20" border="0" alt></font></td>
					  <td class="<?=$param['active_class']?>" height="1"></td>
					  <td rowspan="3" class="<?=$param['active_class']?>" valign="top" width="7"><img src="RESOURCE/righttaba<?=$t?>.gif" width="7" height="20" border="0"></td>              			
					<?				
				}else{
					?>
					  <td class="<?=$param['active_class']?>" rowspan="3" valign="top" width="7"><font face="Arial"><img src="RESOURCE/lefttaba<?=$t?>.gif" width="8" height="20" border="0" alt></font></td>
					  <td bgcolor="#000000" height="1"><font face="Arial"><img src="RESOURCE/stretch.gif" width="1" height="1" alt border="0"></font></td>
					  <td rowspan="3" class="<?=$param['active_class']?>" valign="top" width="7"><img src="RESOURCE/righttaba<?=$t?>.gif" width="7" height="20" border="0"></td>              			
					<?
				}
			}else{
				?>
				  <td class="<?=$param['inactive_class']?>" rowspan="3" valign="top" width="7"><font face="Arial"><img src="RESOURCE/lefttabi<?=$t?>.gif" width="8" height="20" border="0" alt></font></td>
				  <td bgcolor="#000000" height="1"><font face="Arial"><img src="RESOURCE/stretch.gif" width="1" height="1" alt border="0"></font></td>
				  <td rowspan="3" class="<?=$param['inactive_class']?>" valign="top" width="7"><img src="RESOURCE/righttabi<?=$t?>.gif" width="7" height="20" border="0"></td>              							  
				<?			
			}
			if( $i < count($param['tabs']) ){
				?>
					<td rowspan="3" bgcolor="white" valign="bottom" width="5"><img src="RESOURCE/bar<?=$t?>.gif" width="5" height="20" border="0"></td>              				
				<?
			}
		}
		
		echo '</tr>';	

		echo '<tr>';
		foreach($param['tabs'] as $id=>$link){
			if( !is_array($link) ){
				$link = array($link, str_replace('TABKEY', $id, $param['base_action']));
			}
			if($active == $id){
			?>
			  <td class="<?=$param['active_class']?>" height="17" nowrap><?=$link[0]?></font></td> 
			<?			
			}else{
			?>
			  <td class="<?=$param['inactive_class']?>" height="17" nowrap>
			    <a href="<?=$link[1]?>" class="<?=$param['inactive_class']?>"><?=$link[0]?></a>
			  </td> 
			<?
			}
		}
		echo '</tr>';	

		echo '<tr>';
		foreach($param['tabs'] as $id=>$link){
			if($active == $id){
				if( $param['tab_position'] == 'bottom'){
					?>
					  <td bgcolor=black height="1"><img src="stretch.gif" width="1" height="1" alt border="0"></font></td>              
					<?						
				}else{
					?>
					  <td class="<?=$param['active_class']?>" height="1"><img src="stretch.gif" width="1" height="1" alt border="0"></font></td>              
					<?			
				}
			}else{
				?>
				  <td bgcolor="black" height="1" valign=top><font face="Arial"><img src="stretch.gif" width="1" height="1" border="0"></td>                            
				<?
			}
		}
		echo '</tr>';			
		echo '</table></td>';
		
		if($param['topleft']){
			echo '<td align=right>'.$param['topleft'].'</td></tr>';
		}
		
		echo '</table>';

		if( $param['opts'][$active] ){
			$oplist = array();
			$alt = '';

			foreach($param['opts'][$active] as $id2=>$link2){
				if( !is_array($link2) ){
					$link2 = array($link2, str_replace('TABKEY', $id2, $param['base_action']));
				}						
				if($subpage == $id2){
					$oplist[] = '<b><a href="'.$link2[1].'" class="'.$param['subactive_class'].'">'.$link2[0].'</a></b>';
				}else{
					$oplist[] = '<a href="'.$link2[1].'" class="'.$param['subinactive_class'].'">'.$link2[0].'</a>';
				}
				if( $link2[2] || $alt ){
					$alt .= $oplist[count($oplist)-1] . $link2[2];
				}
			}
			if($alt){
				$text .= '<b>Options:</b> ' . $alt;			
			}else{
				$text .= '<b>Options:</b> ' . join(' - ', $oplist);
			}
		}else{
			$text .= '&nbsp;';
		}
		
		if($param['tab_bar'] == 'bottom'){
			?>
				<table cellSpacing="0" cellPadding="5" border="0" width="100%" class="<?=$param['active_class']?>">
				  <tr>
				    <td width="100%" class="<?=$param['active_class']?>"><?=$text?></td>
				  </tr>
				</table>			
			<?
		}

		$Content = ob_get_contents();
		ob_end_clean(); 
		return $Content;
		
}



?>