<?
  class Cart{


    function Cart($info=array()){
	global $cart;
	  if(!session_is_registered('cart') ){
  			session_register('cart');
			$cart = array();
	  }
	 
	  $this->info = $info;
		
		
    }
	function getCart(){
	global $cart;
		return $cart;
	}
	
	function addItem($P){
		global $cart;
	
		$newitem = array();
	
		foreach ($this->info as $k=>$v){
			//check for required variables
			if ($v['req']){
				if (!$P[$k]){
					if ($v['req'] == 1){
						$er .= 'Please complete the following field:  '.$v['display'].'<br>';
					}else{
						$er .= $v['req'].'<br>';
					}
				}
			}
 		}
	if ($er){
				return $er;
	}
	foreach ($this->info as $k=>$v){
			$iteminfo = array ($k => $P[$k]);
			$newitem[$k] = $P[$k];
	}
		array_push ($cart, $newitem);

  	}
  
  	function deleteItem($id){
		global $cart;
		unset ($cart[$id]);
	}
	
	function showCart($deletelink='', $editlink=''){
		global $cart;
		
	if (count ($cart) > 0){?>
	<table border="1" cellpadding="3" cellspacing="0" bordercolorlight="#000000"  bordercolordark="#000000">
  		<tr align="center"> 
	    	<?foreach ($this->info as $k=>$v){
			if (($v['show'] == 1) || ($v['show'] == 2)){?>
			<td bgcolor="d6d6d6" bordercolor="silver">
				<font face="arial" size="2"><b>
					<?echo $v['display'];?>
				</b></font>
			</td>
	    	<?}}?>
			<?if (($editlink) || ($deletelink)){?>
			<td bgcolor="silver" bordercolor="silver">&nbsp; </td>
			<?}?>
		</tr>
   <? 
   foreach ($cart as $a=>$b){
   ?>
       <tr align="left" valign="top">
   <?$numcolumns = count($this->info);
   	foreach ($this->info as $k=>$v){
     
		if (($v['show'] == 1) || ($v['show'] == 2)){?>
 			<td bgcolor="#FFFFFF" bordercolor="silver">
		  		<font face="Arial, Helvetica, sans-serif" size="2">
					<?if ($b[$k]){ echo $b[$k];}else{echo '&nbsp;';}?>
	      		</font>
			</td>
		<?}
		
	}?>
	<?if (($editlink) || ($deletelink)){?>
    <td bgcolor="#FFFFFF" bordercolor="silver" height="29" nowrap>
		<font face="Arial, Helvetica, sans-serif" size="2">
			<?
			$edit = ereg_replace ("%id", "$a", $editlink);
			echo $edit;?>
      		| 
			<?$delete = ereg_replace ("%id", "$a", $deletelink);
			echo $delete;
			?>
		</font>
	</td>
	
	<?
		if ($v['total']){
			$totalamount += $b[$k];
		}
	}?>
  </tr>
<?

}
if ($totalamount){?>
<tr>
<td bgcolor="#FFFFFF" bordercolor="silver" colspan=<?echo $numcolumns-1;?>><font face="Arial, Helvetica, sans-serif" size="2">Sub-Total</font></td>
<td bgcolor="#FFFFFF" bordercolor="silver"><font face="Arial, Helvetica, sans-serif" size="2">$<?echo number_format($totalamount,2);?></font></td>
<td bgcolor="#FFFFFF" bordercolor="silver">&nbsp;</td>
</tr>
<?}?>
</table>
<?}

}
function showEntry($trigger = '', $id ="", $erset ='', $P = '', $picture='add.gif'){
		global $cart;?>
	<table>
  		 
	    	<?foreach ($this->info as $k=>$v){
			if ($v['show'] == 2){?>
			<tr>
			<td>
				<font face="arial" size="2">
					<?echo $v['display'];?>
				</font>
			</td>
			<td>
				<?if ($v['req']){?>
					<img height=10 width=10 src="req.gif">
				<?}else{?>
					&nbsp;
				<?}?>
			</td>
			<td>
				<font face="arial" size="2">
				<?if ($v['type'] == 'text'){?>
					<input type=text name=<?echo $k?> value="<?
						if ($erset){
							echo $P[$k];
						}else{
							echo $cart[$id][$k];
						}
						?>">
				<?}else if ($v['type'] == 'display'){
					if ($erset){
							echo $P[$k];
						}else{
							echo $cart[$id][$k];
						}
				}?>
				</font>
			</td>
			</tr>
	    	<?}}?>
			<tr>
			<td>
				<font face="arial" size="2"><b>
					&nbsp;
				</b></font>
			</td>
			<td>
				<font face="arial" size="2"><b>
					&nbsp;
				</b></font>
			</td>
			<td>
				<font face="arial" size="2"><b>
					<input type=image name=<?echo $trigger?> src='<?echo $picture;?>'>
				</b></font>
			</td>
			</tr>
</table>
<?

}
}
?>