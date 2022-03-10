<?

// images needed for this function
//   lefttaba.gif
//   righttaba.gif
//   lefttabi.gif
//   righttabi.gif
//   stretch.gif
//   bar.gif

function render_tabs1($active, $subpage, $param){	

		echo '<table width="100%" cellpadding=0 cellspacing=0 border=0><tr><td><table border="0" cellspacing="0" cellpadding="0">';		
		echo '<tr>';
		$i=0;
		foreach($param['tabs'] as $id=>$link){
			$i++;
			if($active == $id){
				?>
				  <td bgcolor="<?=$param['active_bg']?>" rowspan="3" valign="top" width="7"><font face="Arial"><img src="RESOURCE/lefttaba.gif" width="8" height="20" border="0" alt></font></td>
				  <td bgcolor="#000000" height="1"><font face="Arial"><img src="RESOURCE/stretch.gif" width="1" height="1" alt border="0"></font></td>
				  <td rowspan="3" bgcolor="<?=$param['active_bg']?>" valign="top" width="7"><img src="RESOURCE/righttaba.gif" width="7" height="20" border="0"></td>              			
				<?
			}else{
				?>
				  <td bgcolor="<?=$param['inactive_bg']?>" rowspan="3" valign="top" width="7"><font face="Arial"><img src="RESOURCE/lefttabi.gif" width="8" height="20" border="0" alt></font></td>
				  <td bgcolor="#000000" height="1"><font face="Arial"><img src="RESOURCE/stretch.gif" width="1" height="1" alt border="0"></font></td>
				  <td rowspan="3" bgcolor="<?=$param['inactive_bg']?>" valign="top" width="7"><img src="RESOURCE/righttabi.gif" width="7" height="20" border="0"></td>              							  
				<?			
			}
			if( $i < count($param['tabs']) ){
				?>
					<td rowspan="3" bgcolor="white" valign="bottom" width="5"><img src="RESOURCE/bar.gif" width="5" height="20" border="0"></td>              				
				<?
			}
		}
		
		echo '</tr>';	

		echo '<tr>';
		foreach($param['tabs'] as $id=>$link){
			if($active == $id){
			?>
			  <td bgcolor="<?=$param['active_bg']?>" height="17" nowrap><?=$param['active_font']?><?=$link[0]?></font></td> 
			<?			
			}else{
			?>
			  <td bgcolor="<?=$param['inactive_bg']?>" height="17" nowrap>
			    <a href="<?=$link[1]?>" style="text-decoration:none"><?=$param['inactive_font']?><?=$link[0]?></a>
			  </td> 
			<?
			}
		}
		echo '</tr>';	

		echo '<tr>';
		foreach($param['tabs'] as $id=>$link){
			if($active == $id){
			?>
			  <td bgcolor="<?=$param['active_bg']?>" height="1"><img src="stretch.gif" width="1" height="1" alt border="0"></font></td>              
			<?			
			}else{
			?>
			  <td bgcolor="black" height="1" valign=top><font face="Arial"><img src="stretch.gif" width="1" height="1" border="0"></td>                            
			<?
			}
		}
		echo '</tr>';			
		echo '</table></td><td align=right>'.$param['topleft'].'</td></tr></table>';


		?>
			<table cellSpacing="0" cellPadding="5" border="0" width="100%" bgcolor="<?=$param['active_bg']?>">
			  <tr>
			    <td width="100%">&nbsp;</td>
			  </tr>
			</table>			
		<?

		
}



?>