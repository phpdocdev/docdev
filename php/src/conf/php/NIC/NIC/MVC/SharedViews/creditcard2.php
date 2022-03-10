<table class=dark cellpadding=3 cellspacing=0>
	<tr>
		<td colspan=2><B>Please enter credit card information. </td>
	</tr>
	<tr class=medium>
		<td colspan=2>
			All fields are Required. 
		</td>
	</tr>
	<tr class=light>
		<td valign=top>
			<table class=light cellpadding=3 cellspacing=0>

				<tr>
					<td align=right>Name as it appears on the card</th>
					<td><input type=text size=30 maxlength=40 name="CC_NAME" value="<?=htmlentities($_SESSION['CC_NAME'])?>"></td>
				</tr>
				<tr>
					<td align=right>Billing Address</th>
					<td><input type=text size=30 maxlength=50 name="CC_ADDR" value="<?=htmlentities($_SESSION['CC_ADDR'])?>"></td>
				</tr>
				<tr>
					<td align=right>City</th>
					<td><input type=text size=15 maxlength=30 name="CC_CITY" value="<?=htmlentities($_SESSION['CC_CITY'])?>"></td>
				</tr>
				<tr>
					<td align=right>State</th>
					<td><select name="CC_STATE">
						<option <? if ($_SESSION['CC_STATE']=='AL') { echo 'selected'; }?> value="AL">Alabama</option>
						<option <? if ($_SESSION['CC_STATE']=='AK') { echo 'selected'; }?> value="AK">Alaska</option>
						<option <? if ($_SESSION['CC_STATE']=='AS') { echo 'selected'; }?> value="AS">American Samoa</option>
						<option <? if ($_SESSION['CC_STATE']=='AZ') { echo 'selected'; }?> value="AZ">Arizona</option>
						<option <? if ($_SESSION['CC_STATE']=='AR') { echo 'selected'; }?> value="AR">Arkansas</option>
						<option <? if ($_SESSION['CC_STATE']=='CA') { echo 'selected'; }?> value="CA">California</option>
						<option <? if ($_SESSION['CC_STATE']=='CO') { echo 'selected'; }?> value="CO">Colorado</option>
						<option <? if ($_SESSION['CC_STATE']=='CT') { echo 'selected'; }?> value="CT">Connecticut</option>
						<option <? if ($_SESSION['CC_STATE']=='DE') { echo 'selected'; }?> value="DE">Delaware</option>
						<option <? if ($_SESSION['CC_STATE']=='DC') { echo 'selected'; }?> value="DC">District of Columbia</option>
						<option <? if ($_SESSION['CC_STATE']=='FM') { echo 'selected'; }?> value="FM">Federated States of Micronesia</option>
						<option <? if ($_SESSION['CC_STATE']=='FL') { echo 'selected'; }?> value="FL">Florida</option>
						<option <? if ($_SESSION['CC_STATE']=='GA') { echo 'selected'; }?> value="GA">Georgia</option>
						<option <? if ($_SESSION['CC_STATE']=='GU') { echo 'selected'; }?> value="GU">Guam</option>
						<option <? if ($_SESSION['CC_STATE']=='HI') { echo 'selected'; }?> value="HI">Hawaii</option>
						<option <? if ($_SESSION['CC_STATE']=='ID') { echo 'selected'; }?> value="ID">Idaho</option>
						<option <? if ($_SESSION['CC_STATE']=='IL') { echo 'selected'; }?> value="IL">Illinois</option>
						<option <? if ($_SESSION['CC_STATE']=='IN') { echo 'selected'; }?> value="IN">Indiana</option>
						<option <? if ($_SESSION['CC_STATE']=='IA') { echo 'selected'; }?> value="IA">Iowa</option>
						<option <? if ($_SESSION['CC_STATE']=='KS') { echo 'selected'; }?> value="KS">Kansas</option>
						<option <? if ($_SESSION['CC_STATE']=='KY') { echo 'selected'; }?> value="KY">Kentucky</option>
						<option <? if ($_SESSION['CC_STATE']=='LA') { echo 'selected'; }?> value="LA">Louisiana</option>
						<option <? if ($_SESSION['CC_STATE']=='ME') { echo 'selected'; }?> value="ME">Maine</option>
						<option <? if ($_SESSION['CC_STATE']=='MH') { echo 'selected'; }?> value="MH">Marshall Islands</option>
						<option <? if ($_SESSION['CC_STATE']=='MD') { echo 'selected'; }?> value="MD">Maryland</option>
						<option <? if ($_SESSION['CC_STATE']=='MA') { echo 'selected'; }?> value="MA">Massachusetts</option>
						<option <? if ($_SESSION['CC_STATE']=='MI') { echo 'selected'; }?> value="MI">Michigan</option>
						<option <? if ($_SESSION['CC_STATE']=='MN') { echo 'selected'; }?> value="MN">Minnesota</option>
						<option <? if ($_SESSION['CC_STATE']=='MS') { echo 'selected'; }?> value="MS">Mississippi</option>
						<option <? if ($_SESSION['CC_STATE']=='MO') { echo 'selected'; }?> value="MO">Missouri</option>
						<option <? if ($_SESSION['CC_STATE']=='MT') { echo 'selected'; }?> value="MT">Montana</option>
						<option <? if ($_SESSION['CC_STATE']=='NE') { echo 'selected'; }?> value="NE">Nebraska</option>
						<option <? if ($_SESSION['CC_STATE']=='NV') { echo 'selected'; }?> value="NV">Nevada</option>
						<option <? if ($_SESSION['CC_STATE']=='NH') { echo 'selected'; }?> value="NH">New Hampshire</option>
						<option <? if ($_SESSION['CC_STATE']=='NJ') { echo 'selected'; }?> value="NJ">New Jersey</option>
						<option <? if ($_SESSION['CC_STATE']=='NM') { echo 'selected'; }?> value="NM">New Mexico</option>
						<option <? if ($_SESSION['CC_STATE']=='NY') { echo 'selected'; }?> value="NY">New York</option>
						<option <? if ($_SESSION['CC_STATE']=='NC') { echo 'selected'; }?> value="NC">North Carolina</option>
						<option <? if ($_SESSION['CC_STATE']=='ND') { echo 'selected'; }?> value="ND">North Dakota</option>
						<option <? if ($_SESSION['CC_STATE']=='MP') { echo 'selected'; }?> value="MP">Northern Mariana Islands</option>
						<option <? if ($_SESSION['CC_STATE']=='OH') { echo 'selected'; }?> value="OH">Ohio</option>
						<option <? if ($_SESSION['CC_STATE']=='OK') { echo 'selected'; }?> value="OK">Oklahoma</option>
						<option <? if ($_SESSION['CC_STATE']=='OR') { echo 'selected'; }?> value="OR">Oregon</option>
						<option <? if ($_SESSION['CC_STATE']=='PW') { echo 'selected'; }?> value="PW">Palau</option>
						<option <? if ($_SESSION['CC_STATE']=='PA') { echo 'selected'; }?> value="PA">Pennsylvania</option>
						<option <? if ($_SESSION['CC_STATE']=='PR') { echo 'selected'; }?> value="PR">Puerto Rico</option>
						<option <? if ($_SESSION['CC_STATE']=='RI') { echo 'selected'; }?> value="RI">Rhode Island</option>
						<option <? if ($_SESSION['CC_STATE']=='SC') { echo 'selected'; }?> value="SC">South Carolina</option>
						<option <? if ($_SESSION['CC_STATE']=='SD') { echo 'selected'; }?> value="SD">South Dakota</option>
						<option <? if ($_SESSION['CC_STATE']=='TN') { echo 'selected'; }?> value="TN">Tennessee</option>
						<option <? if ($_SESSION['CC_STATE']=='TX') { echo 'selected'; }?> value="TX">Texas</option>
						<option <? if ($_SESSION['CC_STATE']=='TT') { echo 'selected'; }?> value="TT">Trust Territories</option>
						<option <? if ($_SESSION['CC_STATE']=='UT') { echo 'selected'; }?> value="UT">Utah</option>
						<option <? if ($_SESSION['CC_STATE']=='VT') { echo 'selected'; }?> value="VT">Vermont</option>
						<option <? if ($_SESSION['CC_STATE']=='VI') { echo 'selected'; }?> value="VI">Virgin Islands</option>
						<option <? if ($_SESSION['CC_STATE']=='VA') { echo 'selected'; }?> value="VA">Virginia</option>
						<option <? if ($_SESSION['CC_STATE']=='WA') { echo 'selected'; }?> value="WA">Washington</option>
						<option <? if ($_SESSION['CC_STATE']=='WV') { echo 'selected'; }?> value="WV">West Virginia</option>
						<option <? if ($_SESSION['CC_STATE']=='WI') { echo 'selected'; }?> value="WI">Wisconsin</option>
						<option <? if ($_SESSION['CC_STATE']=='WY') { echo 'selected'; }?> value="WY">Wyoming</option></select>
					</td>
				</tr>
				<tr>
					<td align=right>ZIP Code</th>
					<td><input size=15 maxlength=10 type=text name="CC_ZIP" value="<?=htmlentities($_SESSION['CC_ZIP'])?>"></td>
				</tr>
				<tr>
					<td align=right>Credit Card Type</th>
					<td>
						<select name="CC_CARD_TYPE">
							<option value="M" <? if ($_SESSION['CC_CARD_TYPE']=='C') { echo 'selected';} ?>>Mastercard</option>
							<option value="V" <? if ($_SESSION['CC_CARD_TYPE']=='S') { echo 'selected';} ?>>Visa</option>
							<option value="D" <? if ($_SESSION['CC_CARD_TYPE']=='S') { echo 'selected';} ?>>Discover</option>
						</select>					
					</td>
				</tr>
				<tr>
					<td align=right>Credit Card Number (no dashes or spaces)</th>
					<td><input size=16 maxlength=16 type=text name="CC_CARD_NUM" value="<?=htmlentities($_SESSION['CC_CARD_NUM'])?>"></td>
				</tr>
				<tr>
					<td align=right>Expiration Date</th>
					<td><select name="CC_EXP_MON">
							<option <? if ($_SESSION['CC_EXP_MON']==1) { echo 'selected'; }?> value="1">January</option>
							<option <? if ($_SESSION['CC_EXP_MON']==2) { echo 'selected'; }?> value="2">February</option>
							<option <? if ($_SESSION['CC_EXP_MON']==3) { echo 'selected'; }?> value="3">March</option>
							<option <? if ($_SESSION['CC_EXP_MON']==4) { echo 'selected'; }?> value="4">April</option>
							<option <? if ($_SESSION['CC_EXP_MON']==5) { echo 'selected'; }?> value="5">May</option>
							<option <? if ($_SESSION['CC_EXP_MON']==6) { echo 'selected'; }?> value="6">June</option>
							<option <? if ($_SESSION['CC_EXP_MON']==7) { echo 'selected'; }?> value="7">July</option>
							<option <? if ($_SESSION['CC_EXP_MON']==8) { echo 'selected'; }?> value="8">August</option>
							<option <? if ($_SESSION['CC_EXP_MON']==9) { echo 'selected'; }?> value="9">September</option>
							<option <? if ($_SESSION['CC_EXP_MON']==0) { echo 'selected'; }?> value="10">October</option>
							<option <? if ($_SESSION['CC_EXP_MON']==11) { echo 'selected'; }?> value="11">November</option>
							<option <? if ($_SESSION['CC_EXP_MON']==12) { echo 'selected'; }?> value="12">December</option>
						</select>
						<select name="CC_EXP_YR">
							<? for ($i=date('Y'); $i < date('Y')+15; $i++) { ?>
							<option <? if ($_SESSION['CC_EXP_YR']==$i) { echo 'selected'; }?>><?=$i?></option>
							<? } ?>
						</select
					</td>
				</tr>				

			</table>
		</td>
		<td valign=top>
			<table class=light cellpadding=1 cellspacing=0>
				<tr class=dark>
					<td colspan=2 nowrap><b>Payment Summary</td>					
				</tr>
				<?if($PMT_SUBTOTAL_DESCR):?>
				<tr class=light>
					<td nowrap><?=$PMT_SUBTOTAL_DESCR?></td>
					<td align=right><?=number_format($PMT_SUBTOTAL_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_FEE_DESCR):?>
				<tr class=light>
					<td><?=$PMT_FEE_DESCR?></td>
					<td align=right><?=number_format($PMT_FEE_AMT,2)?></td>
				</tr>
				<?endif;?>
				<?if($PMT_TOTAL_DESCR):?>
				<tr class=medium>
					<td><b><?=$PMT_TOTAL_DESCR?></td>
					<td align=right><b><?=number_format($PMT_TOTAL_AMT,2)?></td>
				</tr>
				<?endif;?>
			</table>		
		</td>
	</tr>
</table>