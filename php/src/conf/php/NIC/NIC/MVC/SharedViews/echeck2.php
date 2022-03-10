<table class=dark cellpadding=3 cellspacing=0>
	<tr>
		<td colspan=2><B>Please enter Electronic Check Information</td>
	</tr>
	<tr class=medium>
		<td colspan=2>
			An electronic check will deduct funds directly from a checking 
			account without the need for a paper check. Please refer to your 
			check for routing and account numbers. See the example below.
		</td>
	</tr>
	<tr class=light>
		<td colspan=2 align=center><img src="FW_SHARED/payment/check2.gif"></td>
	</tr>
	<tr class=light>
		<td valign=top>
			<table class=light cellpadding=3 cellspacing=0>
				<tr>
					<td nowrap align=right>Account Holder Type:</td>
					<td>
						<select name="EFT_HOLDER_TYPE">
							<option value="P" <? if ($_SESSION['EFT_HOLDER_TYPE']=='P') { echo 'selected';} ?>>Personal</option>
							<option value="B" <? if ($_SESSION['EFT_HOLDER_TYPE']=='B') { echo 'selected';} ?>>Business</option>
						</select>					
					</td>
				</tr>
				<tr>
					<td nowrap align=right>Account Holder Name:</td>
					<td>
						<input type=text name="EFT_FIRST" value="<?=$_SESSION[EFT_FIRST]?>">
					</td>
				</tr>
				<tr>
					<td nowrap align=right>Phone number:</td>
					<td>
						<input type=text name="EFT_PHONE" value="<?=$_SESSION[EFT_PHONE]?>">
					</td>
				</tr>
				<tr>
					<td nowrap align=right>Account Type:</td>
					<td>
						<select name="EFT_ACCT_TYPE">
							<option value="C" <? if ($_SESSION['EFT_ACCT_TYPE']=='C') { echo 'selected';} ?>>Checking</option>
							<option value="S" <? if ($_SESSION['EFT_ACCT_TYPE']=='S') { echo 'selected';} ?>>Savings</option>
						</select>					
					</td>
				</tr>				
				<tr>
					<td nowrap align=right>Routing number:</td>
					<td>
						<input type=text name="EFT_ROUTING" value="<?=$_SESSION[EFT_ROUTING]?>">
					</td>
				</tr>
				<tr>
					<td nowrap align=right>Account number:</td>
					<td>
						<input type=text name="EFT_ACCOUNT" value="<?=$_SESSION[EFT_ACCOUNT]?>">
					</td>
				</tr>
				<tr>
					<td nowrap align=right>Payment Effective Date:</td>
					<td>
						<?=date("m/d/Y", strtotime($_SESSION[EFT_DATE]))?>
					</td>
				</tr>
			</table>
		</td>
		<td valign=top>
			<table class=light cellpadding=1 cellspacing=0>
				<tr class=dark>
					<td colspan=2><b>Payment Summary</td>					
				</tr>
				<?if($PMT_SUBTOTAL_DESCR):?>
				<tr class=light>
					<td><?=$PMT_SUBTOTAL_DESCR?></td>
					<td align=right><?=number_format($PMT_SUBTOTAL_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_FEE_DESCR):?>
				<tr class=light>
					<td><?=$PMT_FEE_DESCR?></td>
					<td align=right><?=number_format($PMT_FEE_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_DISCOUNT_DESCR):?>
				<tr class=light>
					<td><?=$PMT_DISCOUNT_DESCR?></td>
					<td align=right><?=number_format($PMT_DISCOUNT_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_DISCOUNT_FINAL_DESCR):?>
				<tr class=light>
					<td><?=$PMT_DISCOUNT_FINAL_DESCR?></td>
					<td align=right><?=number_format($PMT_DISCOUNT_FINAL_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_TOTAL_DESCR):?>
				<tr class=medium>
					<td><b><?=$PMT_TOTAL_DESCR?></td>
					<td align=right><b><?=number_format($PMT_TOTAL_AMT,2)?></td>
				</tr>
				<?endif;?>
				<tr>
					<td class=tiny colspan=2>
						*An eCheck payment is considered the same as cash, and therefore receives a cash discount as noted above.					
					</td>
				</tr>
			</table>		
		</td>
	</tr>
	<tr>
		<td colspan=2 class=light align=center>
			<table class=dark cellpadding=3 cellspacing=0 width=400>
				<tr>
					<td colspan=2><B>Payment Agreement</td>
				</tr>
				<tr class=light>
					<td colspan=2 align=center>
						<?=$PMT_AUTH_LANGUAGE?> 		
					</td>
				</tr>
				<tr>
					<td colspan=2><B>E-Check Authorization Number</td>
				</tr>
				<tr class=medium>
					<td colspan=2 align=center>
						<?=$PMT_AUTH_INSTRUCT?>
						<input type=text size=15 name="EFT_AUTH" value="<?=$_SESSION[EFT_AUTH]?>">
					</td>
				</tr>				
			</table>
		</td>
	</tr>	
</table>