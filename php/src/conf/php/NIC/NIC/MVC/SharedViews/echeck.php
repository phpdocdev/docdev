<p>
An electronic check will deduct funds directly from a checking account without the need for a paper check. Please refer to your check for routing and account numbers. See example below for information then enter the appropriate numbers in the check.
</p>

<p>
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="425" height="223" valign="top" align="center" background="https://www.ark.org/ina/check1.gif" style="background-repeat: no-repeat">
      <table border="0" cellpadding="0" cellspacing="0" width="427">
        <tr>
          <td width="510" height="68" valign="top" colspan="2" style="background-image: none">
            <div align="left">
              <table border="0" cellpadding="0" cellspacing="0" width="400" style="background-repeat: no-repeat">
                <tr>
                  <td width="255" style="background-image: none" height="20"></td>
                  <td width="145" style="background-image: none" height="20"></td>
                </tr>
                <tr>
                  <td width="265" height="35" style="background-image: none"></td>
                  <td width="151" valign=bottom style="background-image: none" height="35">&nbsp;
                    <font face="arial" size="2">&nbsp; &nbsp; <?=date ('m/d/Y')?> </font> </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr >
          <td width="410" valign="top"></td>
          <td width="175" valign="bottom" align="center">
            <?=number_format($_SESSION['EFT_AMOUNT'], 2)?>
          </td>
        </tr>
        <tr>
          <td width="510" colspan="2" height="85" valign="top"></td>
        </tr>
        <tr>
          <td width="510" colspan="2" style="background-image: none">
            <div align="center">
              <table border="0" cellpadding="0" cellspacing="0" style="background-image: none" width="276">
                <tr>
                  <td width="20" style="background-image: none"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/one.gif" alt="1"></font></td>
                  <td width="12" style="background-image: none"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar1.gif" alt="barcode"></font></td>
                  <td width="72" style="background-image: none">
                    <input type=text size=10 maxlength=9 name="EFT_ROUTE" value="<?=$_SESSION['EFT_ROUTE']?>">
                    </td>
                  <td width="37" style="background-image: none"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar2.gif" alt="barcode"></font></td>
                  <td width="20" style="background-image: none">
                    <p align="center"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/two.gif"></font>
                  </td>
                  <td width="83" style="background-image: none"><input type=text size=10 name="EFT_ACCT" value="<?=$_SESSION['EFT_ACCT']?>">
                  </td>
                  <td width="18" style="background-image: none"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar3.gif" alt="barcode"></font></td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br>
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="80">&nbsp;</td>
  	<td width="20"><img border="0" src="https://www.ark.org/ina/one.gif" alt="Routing"></td>
  	<td width="121"> <font size=-1>Routing number</font></td>
  	<td width="20"><img border="0" src="https://www.ark.org/ina/two.gif" alt="Account"></td>
  	<td width="101"> <font size=-1>Account number</font></td>
	</tr>
</table>
</p>
<p>
<hr align="center" width="50%">
</p>
<p>
  <table border=0 cellpadding=5 cellspacing=0>
    <tr>
      <td valign="top">Please select the type of account.</td>
      <td valign="top"><select name="EFT_TYPE">
				<option value=""></option>
				<option value="C" <? if ($_SESSION['EFT_TYPE']=='C') { echo 'selected';} ?>>Checking</option>
				<option value="S" <? if ($_SESSION['EFT_TYPE']=='S') { echo 'selected';} ?>>Savings</option>
				</select>
			</td>
    </tr>
    <tr>
      <td valign="top">Please enter the name of the bank.</td>
      <td valign="top"><input type=text size=20 name="EFT_BANK" value="<?=$_SESSION['EFT_BANK']?>"></td>
    </tr>
    <tr>
      <td valign="top">Please enter the first name of the owner of the checking account. If the checking account is a business account, please enter the business name.</td>
      <td valign="top"><input type=text size=20 name="EFT_FIRST" value="<?=$_SESSION['EFT_FIRST']?>"></td>
    </tr>
    <tr>
      <td valign="top">Please enter the last name of the owner of the checking account. If the checking account is a business account, please leave this field blank.</td>
      <td valign="top"><input type=text size=20 name="EFT_LAST" value="<?=$_SESSION['EFT_LAST']?>"></td>
    </tr>
  </table>
</p>
