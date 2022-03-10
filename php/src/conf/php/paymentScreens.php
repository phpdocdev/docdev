<?

class paymentScreens{



  function addCommas($num){
    $num = sprintf ("%01.2f", $num);

    // save decimal portion
    $dec = substr($num, strlen($num)-2 , 2);

    // remove decimal portion
    $num = substr($num, 0, strlen($num)-3);
   
    
    $num = strrev($num);
    
    $num = ereg_replace("([0-9]{3,3})","\\1,",$num);
    
    $num = strrev($num);    

    $num = ereg_replace("^\,","",$num);    

    return $num . '.' . $dec;  
  }

  function verifyCreditCard($DATA){
    
    $ErrorMessage = array();
    
    if(!$DATA['CC_TYPE']){
     $ErrorMessage[count($ErrorMessage)] = "Please select a credit card type";
    }

    $CC_NUM = $DATA['CC_NUM1'].$DATA['CC_NUM2'].$DATA['CC_NUM3'].$DATA['CC_NUM4'];
    
    
    if(!$CC_NUM){
     $ErrorMessage[count($ErrorMessage)] = "Please enter a credit card number";    
    }    
    
    if(strlen($CC_NUM) != 16){
     $ErrorMessage[count($ErrorMessage)] = "The credit card number you entered (".$CC_NUM.") is not a valid credit card number";
    }    

    if(!$DATA['CC_NAME']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter a the name that is on the credit card";    
    }    
    
    if(!$DATA['CC_ADDR']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter an address";    
    }    
    
    if(!$DATA['CC_CITY']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter a city";    
    }    
    
    if(!$DATA['CC_STATE']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter a state";    
    }    
    
    if(!$DATA['CC_ZIP']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter a zip code";    
    }                    

    if(!$DATA['CC_AMOUNT']){
     $ErrorMessage[count($ErrorMessage)] = "Please supply an amount";    
    }                    
    
    if( count($ErrorMessage)==0 ){
      return '';
    }else{
      return $ErrorMessage;      
    }

  }
  
  function showCreditCard($TYPE = '', $NUM = '', $CC_EXP_MO = '', $CC_EXP_YR = '', $CC_NAME = '', $CC_ADDR = '', $CC_CITY = '', $CC_STATE = '', $CC_ZIP = '', $CC_AMOUNT = ''){
  
    if($TYPE == 'M'){
      $TypeM = 'checked';
    }else if ($TYPE == 'V'){
      $TypeV = 'checked';    
    }
    
    $NUM1 = substr($NUM, 0, 4);
    $NUM2 = substr($NUM, 4, 4);
    $NUM3 = substr($NUM, 8, 4);
    $NUM4 = substr($NUM, 12, 4);   
    
    $Month = array();
    $Month[$CC_EXP_MO] = 'selected';     
    
    // show the next 15 years
    $year_sel = '<select name="CC_EXP_YR">';
    $thisYear = date("Y", mktime());
    settype($thisYear, "integer");    
    
    if(!$CC_EXP_YR){
      $CC_EXP_YR = $thisYear;
    }else{
      settype($CC_EXP_YR, "integer");
    }    
    
    for($i=$thisYear; $i<($thisYear+15); $i++){
      if($i == $CC_EXP_YR){
        $sel = ' selected';
      }else{
        $sel = '';      
      }
      $year_sel .= '<option'.$sel.' value="'.$i.'">'.$i.'</option>';    
    }    
    $year_sel .= '</select>';
    
    if(!$CC_STATE){
      $CC_STATE = 'AR';
    }
    
    ?> 
 <script language="JavaScript">
 function Validate(theForm)
{
  var checkOK = "0123456789";
  var checkStr = theForm.CC_NUM1.value + theForm.CC_NUM2.value + theForm.CC_NUM3.value + theForm.CC_NUM4.value ;
  var CrValid = true;
  var checksum=0;
  var ddigit=0;
  var kdig = 0;
  if (checkStr.length < 13) alert ('You have not entered enough digits. Please check the number for errors.');
  for (i = checkStr.length-1;  i >= 0;  i--)
  {
    kdig++;
    ch = checkStr.charAt(i);
    if ((kdig % 2) != 0)
       checksum=checksum+parseInt(ch)
    else {
       ddigit=parseInt(ch)*2;
       if (ddigit >= 10)
          checksum=checksum+1+(ddigit-10)
       else
          checksum=checksum+ddigit;
    }
    for (j = 0;  j < checkOK.length;  j++)
      if (ch == checkOK.charAt(j))
        break;
    if (j == checkOK.length)
    {
      alert('Please enter only digits. No dashes or non-numeric characters.');
      return(false);
    }
  }
  if ((checksum % 10) != 0){
       alert('You have entered an invalid credit card number. Please check the number for errors.');
       theForm.CC_NUM1.focus();
       return (false);
  }else{
     //  alert ('Your credit card number is valid.');
       return(true);
  }
}

 </script>
 <input type="hidden" name="CC_AMOUNT" value="<? echo $CC_AMOUNT ?>">
<table width="465" border="0" cellpadding="4" cellspacing="0">
  <tr bgcolor="#CCCCCC"> 
    <td colspan="2" height="5"><font face="Arial, Helvetica, sans-serif" size="2" color="#FF0033"><font color="333366"><b><font color="#000000"> 
      All fields are Required.</font></b></font></font> </td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Name 
      on Credit Card</b></font></td>
    <td width="302" height="15"> <font face="Arial, Helvetica, sans-serif" size="2"> 
      <font size="-2"> <font size="2">
      <input type="text" name="CC_NAME" size="30" maxlength="30" value="<? echo $CC_NAME?>">
      </font></font></font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Address</b></font></td>
    <td width="302" height="15"> <font face="Arial, Helvetica, sans-serif" size="2"> 
      <input type="text" name="CC_ADDR" size="30" maxlength="30" value="<? echo $CC_ADDR ?>">
      </font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>City 
      <br>
      </b></font></td>
    <td width="302" height="15"> <font face="Arial, Helvetica, sans-serif" size="2"> 
      <input type="text" name="CC_CITY" size="20" maxlength="20" value="<? echo $CC_CITY ?>">
      </font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>State 
      </b></font></td>
    <td width="302" height="15"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $this->chooseState('CC_STATE', $CC_STATE,'') ?> 
      </font><font face="Arial, Helvetica, sans-serif" size="2" color="#FF0033"><b><font color="333366"> 
      </font></b></font><font face="Arial, Helvetica, sans-serif" size="2"> </font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Zip</b></font></td>
    <td width="302" height="15"><font face="Arial, Helvetica, sans-serif" size="2"> 
      <input type="text" name="CC_ZIP" size="10" maxlength="10" value="<? echo $CC_ZIP ?>">
      </font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Credit 
      Card Type</b></font></td>
    <td width="302" height="15" align="left" valign="middle"> <font face="Arial, Helvetica, sans-serif" size="2"> 
      <input type="radio" name="CC_TYPE" value="V" <? echo $TypeV ?>>
      <img src="https://www.ark.org/ina/visa.gif" width="43" height="29"
                      alt="Visa" align="absmiddle"> 
      <input type="radio" name="CC_TYPE" value="M" <? echo $TypeM ?>>
      <img src="https://www.ark.org/ina/mastercard.gif" width="44"
                      height="29" alt="MasterCard" align="absmiddle"></font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Credit 
      Card Number</b></font></td>
    <td width="302" height="15"> <font face="arial" size="3"> <font size="2"> 
      <font face="Arial, Helvetica, sans-serif">
      <input type="text" name="CC_NUM1" value="<? echo $NUM1 ?>" maxlength="4" size="4">
      </font></font></font><font face="Arial, Helvetica, sans-serif" size="2"> 
      <input type="text" name="CC_NUM2" value="<? echo $NUM2 ?>" maxlength="4" size="4">
      <input type="text" name="CC_NUM3" value="<? echo $NUM3 ?>" maxlength="4" size="4">
      <input type="text" name="CC_NUM4" value="<? echo $NUM4 ?>" maxlength="4" size="4"  onChange="Validate(this.form)"  >
      </font><font face="Arial, Helvetica, sans-serif" size="2"></font></td>
  </tr>
  <tr> 
    <td width="147" height="15" bordercolor="#FFFFFF"><font face="Arial, Helvetica, sans-serif" size="2" color="#000000"><b>Expiration 
      Date</b></font></td>
    <td width="302" height="15"> <font face="Arial, Helvetica, sans-serif" size="2"> 
      <select name="CC_EXP_MO">
        <option <? echo $Month['1'] ?> value="1">January</option>
        <option <? echo $Month['2'] ?> value="2">February</option>
        <option <? echo $Month['3'] ?> value="3">March</option>
        <option <? echo $Month['4'] ?> value="4">April</option>
        <option <? echo $Month['5'] ?> value="5">May</option>
        <option <? echo $Month['6'] ?> value="6">June</option>
        <option <? echo $Month['7'] ?> value="7">July</option>
        <option <? echo $Month['8'] ?> value="8">August</option>
        <option <? echo $Month['9'] ?> value="9">September</option>
        <option <? echo $Month['10'] ?> value="10">October</option>
        <option <? echo $Month['11'] ?> value="11">November</option>
        <option <? echo $Month['12'] ?> value="12">December</option>
      </select>
      / </font><? echo $year_sel ?></td>
  </tr>
</table>
<?
  }
  
  function verifyECheck($DATA){
    $ErrorMessage = array();
    
    if(!$DATA['CHECK_ROUTING']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter your routing number";    
    }    

    if(strlen($DATA['CHECK_ROUTING']) != 9){
     $ErrorMessage[count($ErrorMessage)] = "The routing number must be 9 digits";    
    }    
    
    if(!$DATA['CHECK_ACCOUNT']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter your account number";    
    }    
    
    if(!$DATA['CHECK_NUMBER']){
     $ErrorMessage[count($ErrorMessage)] = "Please enter your check number";    
    }    
    
    
    if( count($ErrorMessage)==0 ){
      return '';
    }else{
      return $ErrorMessage;      
    }  
  }
  
  function showECheck($CHECK_AMOUNT = '', 
                      $CHECK_DATE = '', 
                      $CHECK_ROUTING = '', 
                      $CHECK_ACCOUNT = '', 
                      $CHECK_NUMBER = ''){
  
    // set the amount
    if(!$CHECK_AMOUNT){
      $CHECK_AMOUNT = '<input name="CHECK_AMOUNT" type="text" size=6"" value="">';
    }else{
      $CHECK_AMOUNT = $this->addCommas($CHECK_AMOUNT);
    }
    
    if(!$CHECK_DATE){
      $CHECK_DATE = date("m/d/Y", mktime());
    }  
    
    ?> 
<table width="502" border="0">
  <tr> 
    <td height="116"> 
      <p><font face="Arial, Helvetica, sans-serif" size="2"> An electronic check 
        is a method of writing a check from your checking account online. The 
        safety and security of your bank account is important to you and to us. 
        We provide a secure environment against unauthorized withdrawals, and 
        will never make electronic withdrawals from your bank account without 
        your explicit permission, unless otherwise authorized for monthly transactions. 
        </font></p>
    </td>
  </tr>
  <tr> 
    <td height="49"><font face="Arial, Helvetica, sans-serif" size="2">Please 
      take out your checkbook and turn to the next available check. Write VOID 
      on this check and continue with the process below. </font></td>
  </tr>
  <tr> 
    <td height="47"> 
      <p>&nbsp; 
      <ol>
        <li><font face="Arial, Helvetica, sans-serif" size="2"> Enter the Bank 
          Routing number from your check in the space provided below. </font></li>
        <li><font face="Arial, Helvetica, sans-serif" size="2"> Enter the Checking 
          Account number from your check in the space provided below. </font></li>
        <li><font face="Arial, Helvetica, sans-serif" size="2"> Enter the Check 
          number (if available) from the upper right corner of your check in the 
          space provided below. </font></li>
      </ol>
      </p>
    </td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="500" height="334">
  <tr>
      <td height="20"></td>
    </tr>
    <tr>
      <td height="20"></td>
    </tr>
    <tr>
      <td height="236">
        <div align="center">
        
          <table border="0" cellpadding="0" cellspacing="0" width="501">
            <tr>
              <td width="529" height="234" style="background-image: url('https://www.ark.org/ina/check1.jpg'); background-repeat: no-repeat" background="https://www.ark.org/ina/check1.jpg" valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="497" style="background-image: url('https://www.ark.org/ina/check1.jpg')">
                  <tr>
                    <td width="495" height="68" valign="bottom" colspan="2">
                      <div align="left">
                      <table border="0" cellpadding="0" cellspacing="0" width="496">
                        <tr>
                          <td width="319" style="background-image: none"></td>
                          <td width="173" style="background-image: none">&nbsp; <font face="arial" size="2">
                            <? echo $CHECK_DATE; ?>
                          </font>
                          </td>
                        </tr>
                      </table>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td width="392" height="32" valign="bottom" style="background-image: none" ></td>
                    <td width="103" height="32" valign="bottom"  style="background-image: none"><? echo $CHECK_AMOUNT ?></td>
                  </tr>
                  <tr>
                    <td width="495" colspan="2"></td>
                  </tr>
                  <tr>
                    <td width="495" colspan="2" height="80"></td>
                  </tr>
                  <tr>
                    <td width="495" colspan="2" style="background-image: none">
                      <div align="center">
                        
                        
                      <table border="0" cellpadding="0" cellspacing="0" width="395" style="background-image: none">
                        <tr> 
                          <td width="17"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/one.gif" alt="1" width="20" height="20"></font></td>
                          <td width="12"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar1.gif" width="12" height="17" alt="barcode"></font></td>
                          <td width="72"><font face="Arial, Helvetica, sans-serif" size="2"> 
                            <input type="text" name="CHECK_ROUTING" size="9" maxlength="9" value="<? echo $CHECK_ROUTING ?>">
                            </font></td>
                          <td width="35"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar2.gif" width="12" height="17" alt="barcode"></font></td>
                          <td width="27"> 
                            <p align="center"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/two.gif" width="20" height="20"></font>
                          </td>
                          <center>
                            <center>
                              <td width="79"><font face="Arial, Helvetica, sans-serif" size="2"> 
                                <input type="text" name="CHECK_ACCOUNT" size="10" value="<? echo $CHECK_ACCOUNT ?>">
                                </font></td>
                              <td width="36"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/bar3.gif" width="11" height="14" alt="barcode"></font></td>
                            </center>
                          </center>
                          <td width="31"> 
                            <p align="center"><font face="Arial, Helvetica, sans-serif" size="2"><img border="0" src="https://www.ark.org/ina/three.gif" width="20" height="20"></font>
                          </td>
                          <td width="84"><font face="Arial, Helvetica, sans-serif" size="2"> 
                            <input type="text" name="CHECK_NUMBER" size="8" value="<? echo $CHECK_NUMBER ?>">
                            </font></td>
                        </tr>
                      </table>
                        
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          
        </div>
      </td>
    </tr>
    <tr>
      <td height="25"></td>
    </tr>
  <tr>
    <td height="20">
      <p align="center"><img border="0" src="https://www.ark.org/ina/check5.jpg" width="503" height="163"></td>
  </tr>
    <tr>
      <td height="20"></td>
    </tr>
  </table>    
    
<?

  }


function chooseState($name, $selected, $parts){

  if(!$selected){
    $selected = 'AR';
  }

  $States = array(
    AK => 'Alaska',
    AL => 'Alabama',
    AR => 'Arkansas',
    AZ => 'Arizona',
    CA => 'California',
    CO => 'Colorado',
    CT => 'Connecticut',
    DC => 'District of Columbia',
    DE => 'Delaware',
    FL => 'Florida',
    GA => 'Georgia',
    HI => 'Hawaii',
    IA => 'Iowa',
    ID => 'Idaho',
    IL => 'Illinois',
    IN => 'Indiana',
    KS => 'Kansas',
    KY => 'Kentucky',
    LA => 'Louisiana',
    MA => 'Massachusetts',
    MD => 'Maryland',
    ME => 'Maine',
    MI => 'Michigan',
    MN => 'Minnesota',
    MO => 'Missouri',
    MS => 'Mississippi',
    MT => 'Montana',
    NC => 'North Carolina',
    ND => 'North Dakota',
    NE => 'Nebraska',
    NH => 'New Hampshire',
    NJ => 'New Jersey',
    NM => 'New Mexico',
    NV => 'Nevada',
    NY => 'New York',
    OH => 'Ohio',
    OK => 'Oklahoma',
    'OR' => 'Oregon',
    PA => 'Pennsylvania',
    RI => 'Rhode Island',
    SC => 'South Carolina',
    SD => 'South Dakota',
    TN => 'Tennessee',
    TX => 'Texas',
    UT => 'Utah',
    VA => 'Virginia',
    VT => 'Vermont',
    WA => 'Washington',
    WI => 'Wisconsin',
    WV => 'West Virginia',
    WY => 'Wyoming'
  );

  $ret = '<select name="'.$name.'" '.$parts.'>';

  while(list($ab,$nm) = each($States)){
    if($selected == $ab){
      $sel = 'selected';
    }else{
      $sel = '';
    }
    $ret .= '<option '.$sel.' value="'.$ab.'">'.$nm.'</option>';
  }
  
  return $ret . '</select>';


}

function showCreditCardInfo($amount, $orderid, $ccname, $cctype, $ccnum, $ccexpr){?> 
<table width="348" border="0">
  <tr> 
    <td colspan="2" height="51"><font face="Arial, Helvetica, sans-serif" size="2">A 
      total of<b> <?echo $amount?> </b>was charged to your credit card by the 
      <b>AR Government Services.</b></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Transaction 
      ID:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $orderid;?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Name:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><?echo $ccname;?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Card Type:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><?
    if ($cctype == 'V') echo 'Visa'; else if ($cctype == 'M') {echo 'MasterCard';}?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Card Number:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo substr($ccnum, 0, 2).'************'.substr($ccnum, -4, 4); ?></font></td>
  </tr>
  <tr>
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Expires:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $ccexpr?></font></td>
  </tr>
</table>
<?}

function showECheckInfo($amount, $orderid, $routing, $account, $number){?> 
<table width="348" border="0">
  <tr> 
    <td colspan="2" height="51"><font face="Arial, Helvetica, sans-serif" size="2">A 
      total of<b> <?echo $amount?> </b>was deducted from your account by
the <b>Virtual Pay Arkansas
.</b></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Transaction 
      ID:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $orderid;?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Routing 
      Number: </font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><?echo $routing;?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Account 
      Number: </font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $account?></font></td>
  </tr>
  <tr> 
    <td width="172"><font face="Arial, Helvetica, sans-serif" size="2">Check Number:</font></td>
    <td width="166"><font face="Arial, Helvetica, sans-serif" size="2"><? echo $number ?></font></td>
  </tr>
</table>
<?}



function chargeCreditCard ($demo, $servicecode, $amount, $D, $previous){
//$D is $HTTP_POST_VARS
//if demo it writes info to democharges in ccarddemo database

$CC_NUM = $D['CC_NUM1'].$D['CC_NUM2'].$D['CC_NUM3'].$D['CC_NUM4'];

//check to see if the card has already been charged for this transaction
#require('sql_mysql.php');

	$cctable = 'charges';


$SQL = new sql_mysql('ccarddemo');
$chkqry = "select orderid from ".$cctable." where name= '$D[CC_NAME]' and ";
$chkqry .= "address= '$D[CC_ADDR]' and city = '$D[CC_CITY]' and ";
$chkqry .= "state = '$D[CC_STATE]' and zip = '$D[CC_ZIP]' and ";
$chkqry .= "cardtype = '$D[CC_TYPE]' and cardno='$CC_NUM' and ";
$chkqry .= "amount = '$amount' and service='$servicecode'";
//$chkqry .= "TO_DAYS(curtime()) - TO_DAYS(orderdate) = 0 and HOUR(curtime()) = HOUR(orderdate)";

list ($check) = $SQL->fetchrow($chkqry);
//    if ($check) {
//    	if ($previous != ''){
//    		$er = $previous;
//    	}
//    	else {
//    		//$er = "Your credit card has already been charged for this transaction.  Thank you!";
//    	}
//    	return array ('', $er);
//    	
//    }
//    else{
require('CyberCash.php');
if ($demo == 1){
$CC = new CyberCash(1);
}
else{
$CC = new CyberCash(0);
}


list($cd, $response) = $CC->ChargeCard( array(
    'ServiceCode'    => $servicecode,     
    'Amount'         => $amount,
    'Card-Number'    => $CC_NUM, 
    'Card-Address'   => $D['CC_ADDR'], 
    'Card-City'      => $D['CC_CITY'], 
    'Card-State'     => $D['CC_STATE'], 
    'Card-Zip'       => $D['CC_ZIP'], 
    'Card-Country'   => 'USA',
    'Card-Exp-Month' => $D['CC_EXP_MO'],      // Must be <= 12
    'Card-Exp-Year'  => $D['CC_EXP_YR'],      // Must be 4 digit year
    'Card-Name'      => $D['CC_NAME'],
    'Card-Type'      => $D['CC_TYPE']         // Must be 'V' or 'M'
));

if($cd == 1){
//   the charge was good and has
//   been recorded in the database
  $orderid = $response['Order-Id'];
  
}else{
//   the error is in $response['MErrMsg']
//   or a nicer, user friendly message is in NiceMessage
       $er = $response['NiceMessage'];
}

// this code will echo out all the 
// variables returned from cybercash
// while(list($key, $val) = each($response)){ 
//   echo "$key = $val<br>\n"; 
//}
return array($orderid, $er);

}}

//}

?>
