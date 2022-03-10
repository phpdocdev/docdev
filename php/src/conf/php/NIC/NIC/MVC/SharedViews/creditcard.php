<table>
	<tr>
		<th colspan=2 align=left>All fields are required</th>
	</tr>
	<tr>
		<th align=left>Name as it appears on the card</th>
		<td><input type=text size=30 maxlength=40 name="CC_NAME" value="<?=htmlentities($_SESSION['CC_NAME'])?>"></td>
	</tr>
	<tr>
		<th align=left>Billing Address</th>
		<td><input type=text size=30 maxlength=50 name="CC_ADDR" value="<?=htmlentities($_SESSION['CC_ADDR'])?>"></td>
	</tr>
	<tr>
		<th align=left>City</th>
		<td><input type=text size=15 maxlength=30 name="CC_CITY" value="<?=htmlentities($_SESSION['CC_CITY'])?>"></td>
	</tr>
	<tr>
		<th align=left>State</th>
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
		<th align=left>ZIP Code</th>
		<td><input size=15 maxlength=10 type=text name="CC_ZIP" value="<?=htmlentities($_SESSION['CC_ZIP'])?>"></td>
	</tr>
	<tr>
		<th align=left>Credit Card Type</th>
		<td>
			<input type=radio name="CC_CARD_TYPE" value='V' <? if ($_SESSION['CC_CARD_TYPE'] == 'V') { echo 'checked'; } ?>> <img src="https://www.ark.org/ina/visa.gif" valign="middle" width=43 height=29 alt="Visa" border=0> &nbsp; 
			<input type=radio name="CC_CARD_TYPE" value='M' <? if ($_SESSION['CC_CARD_TYPE'] == 'M') { echo 'checked'; } ?>> <img src="https://www.ark.org/ina/mastercard.gif" valign="middle" width=43 height=29 alt="Mastercard" border=0> &nbsp; 
			<input type=radio name="CC_CARD_TYPE" value='D' <? if ($_SESSION['CC_CARD_TYPE'] == 'D') { echo 'checked'; } ?>> <img src="https://www.ark.org/ina/discover.jpg" valign="middle" width=43 height=29 alt="Discover" border=0></td>
	</tr>
	<tr>
		<th align=left>Credit Card Number (no dashes or spaces)</th>
		<td><input size=16 maxlength=16 type=text name="CC_CARD_NUM" value="<?=htmlentities($_SESSION['CC_CARD_NUM'])?>"></td>
	</tr>
	<tr>
		<th align=left>Expiration Date</th>
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
	<tr>
		<th align=left>Amount</th>
		<td><strong>$<?=number_format($_SESSION['CC_AMOUNT'], 2)?></strong></td>
	</tr>
</table>
