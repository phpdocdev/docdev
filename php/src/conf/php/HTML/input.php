<?php
class forms {

	var $fvalid;
	var $ret;
	var $form;
	var $fname;

	function forms($name, $action, $method="post", $valid='') {
		$this->form="<form name=\"$name\" action=\"$action\" method=\"$method\"";
		if ($valid) {
			$this->fvalid=1;
			$this->form.=" onsubmit='return $valid($name);'";
		} else {
			$this->fvalid=0;
		}
		$this->fname=$name;
		$this->form.=">";
	}

	function makeTextArea($name, $cols='', $rows='', $value='', $wrap='', $validate='', $error='', $extra='') {

    if( is_array($name) ){
      $name     = $name['name'];
      $cols     = $name['cols'];
      $rows     = $name['rows'];
      $value    = $name['value'];
      $wrap     = $name['wrap'];
      $validate = $name['validate'];
      $error    = $name['error'];
      $extra    = $name['extra'];
    }

      if(!$wrap){
        $wrap = 'soft';
      }

      $this->ret = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="'.$wrap.'">'.$value.'</textarea>';

      return $this->ret;
    }

  function makeHidden($name, $value){
    if( is_array($name) ){
      $name     = $name['name'];
      $value    = $name['value'];
    }

    $base = '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    return $base;
  }

  function makeMultiText($name, $parts, $values=array(), $max = array(), $req = array(), $sep = array(), $validate='', $error='', $extra=''){

    $ret = '';

    $i=1;
    foreach($parts as $k){
      $ret .= $this->makeText($name.'_'.$i, $k, $max[$i-1], $values[$i-1]);
      $ret .= $sep[$i-1];
      $i++;
    }

    return $ret;
  }

	function makeText($name, $size='', $max='', $value='', $validate='', $error='', $extra='') {

    if( is_array($name) ){
      $name     = $name['name'];
      $size     = $name['size'];
      $max      = $name['max'];
      $value    = $name['value'];
      $validate = $name['validate'];
      $error    = $name['error'];
      $extra    = $name['extra'];
    }

		if (! isset($value) ) {
	  	global $HTTP_POST_VARS;
			$value=$HTTP_POST_VARS[$name];
		}
		$base="<input type=\"text\" name=\"$name\" value=\"$value\"";
		if ($size) {
			$base.=" size=$size";
		}
		if ($max) {
			$base.=" maxlength=$max";
		}
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}

	function makeRadio($name, $value, $current='', $validate='', $error='', $extra='') {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		if ($value==$current) {
			$sel="checked";
		} else {
			$sel="";
		}
		$base="<input type=radio name=\"$name\" value=\"$value\" $sel";
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}

	function makeYesNo($name, $YesVal='Y', $NoVal='N', $current='', $validate='', $error='', $extra='') {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}

		$Ychecked = '';
		$Nchecked = '';

		if ($current==$YesVal) {
			$Ychecked = 'checked';
		} elseif ($current==$NoVal) {
			$Nchecked = 'checked';
		}

		$base="<input type=radio name=\"$name\" value=\"$YesVal\" $Ychecked";
		if ($validate) {
      $base .= " onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$base .= "> Yes";
		}

		$base.="<input type=radio name=\"$name\" value=\"$NoVal\" $Nchecked";
		if ($validate) {
			$base .= " onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$base .= "> No";
		}

		return $base;
	}

	function makeCheckbox($name, $value, $current='', $validate='', $error='', $extra='') {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}
		if ((is_array($current) && in_array($value, $current)) || $value==$current) {
			$sel="checked";
		} else {
			$sel="";
		}
		$base="<input type=checkbox name=\"$name\" value=\"$value\" $sel";
		if ($validate) {
			$this->ret=$base." onChange='return $validate($name, \"$error\", \"$extra\");'>";
		} else {
			$this->ret=$base.">";
		}
		return $this->ret;
	}

	function makeSelect($name, $values, $size=1, $current='', $mult=0)  {
		if (!$current && $current != '0') {
	  	global $HTTP_POST_VARS;
			$current=$HTTP_POST_VARS[$name];
		}

    if($mult == 1){
      $mult = ' multiple';
    }else{
       $mult = '';
    }

		$this->ret="<select name=\"$name\" size=$size".$mult.">\n";


    foreach($values as $k=>$v){
			if ($current==$k) {
				$sel="selected";
			} else if (is_array($current) && in_array($k, $current)){
				$sel ="selected";
			} else {
				$sel="";
			}
			$this->ret.="<option $sel value=\"$k\">$v</option>\n";

		}
		$this->ret.="</select>";
		return $this->ret;
	}


	function safeSubmit($name, $value='Submit') {
		$this->ret="<INPUT TYPE=\"submit\" NAME=\"$name\" VALUE=\"$value\" ONCLICK=\"if(this.disabled || typeof(this.disabled)=='boolean') this.disabled=true ; form_submitted_test=form_submitted ; form_submitted=true ; form_submitted=(!form_submitted_test || confirm('You have alredy clicked the button one. Are you sure you want to submit this form again?')) ; if(this.disabled || typeof(this.disabled)=='boolean') this.disabled=false ; sub_form='' ; return true\">";
		return $this->ret;
	}

	function selectState($name, $selected='', $parts=''){
  	if(!$selected){
    	$selected = 'AR';
  	}
  	$States = array(
			AA => 'AA (Military)',
			AE => 'AE (Military)',
			AP => 'AP (Military)',
			AL => 'Alabama',
			AK => 'Alaska',
			'AS' => 'American Samoa',
			AZ => 'Arizona',
			AR => 'Arkansas',
			CA => 'California',
			CO => 'Colorado',
			CT => 'Connecticut',
			DE => 'Delaware',
			DC => 'District of Columbia',
			FM => 'Federated States of Micronesia',
			FL => 'Florida',
			GA => 'Georgia',
			GU => 'Guam',
			HI => 'Hawaii',
			ID => 'Idaho',
			IL => 'Illinois',
			IN => 'Indiana',
			IA => 'Iowa',
			KS => 'Kansas',
			KY => 'Kentucky',
			LA => 'Louisiana',
			ME => 'Maine',
			MH => 'Marshall Islands',
			MD => 'Maryland',
			MA => 'Massachusetts',
			MI => 'Michigan',
			MN => 'Minnesota',
			MS => 'Mississippi',
			MO => 'Missouri',
			MT => 'Montana',
			NE => 'Nebraska',
			NV => 'Nevada',
			NH => 'New Hampshire',
			NJ => 'New Jersey',
			NM => 'New Mexico',
			NY => 'New York',
			NC => 'North Carolina',
			ND => 'North Dakota',
			MP => 'Northern Mariana Islands',
			OH => 'Ohio',
			OK => 'Oklahoma',
			'OR' => 'Oregon',
			PW => 'Palau',
			PA => 'Pennslyania',
			PR => 'Puerto Rico',
			RI => 'Rhode Island',
			SC => 'South Carolina',
			SD => 'South Dakota',
			TN => 'Tennessee',
			TX => 'Texas',
			TT => 'Trust Territories',
			UT => 'Utah',
			VT => 'Vermont',
			VI => 'Virgin Islands',
			VA => 'Virginia',
			WA => 'Washington',
			WV => 'West Virginia',
			WI => 'Wisconsin',
			WY => 'Wyoming'
  	);
  	$this->ret = '<select name="'.$name.'" '.$parts.'>';
  	while(list($ab,$nm) = each($States)){
    	if($selected == $ab){
      	$sel = 'selected';
    	}else{
      	$sel = '';
    	}
    	$this->ret .= "\n<option ".$sel.' value="'.$ab.'">'.$nm.'</option>';
  	}
  	return $this->ret . '</select>';
	}

	function selectCounty($name, $selected=''){
  	$Counties = array(
			'ARKANSAS' => 'ARKANSAS',
			'ASHLEY' => 'ASHLEY',
			'BAXTER' => 'BAXTER',
			'BENTON' => 'BENTON',
			'BOONE' => 'BOONE',
			'BRADLEY' => 'BRADLEY',
			'CALHOUN' => 'CALHOUN',
			'CARROLL' => 'CARROLL',
			'CHICOT' => 'CHICOT',
			'CLARK' => 'CLARK',
			'CLAY' => 'CLAY',
			'CLEBURNE' => 'CLEBURNE',
			'CLEVELAND' => 'CLEVELAND',
			'COLUMBIA' => 'COLUMBIA',
			'CONWAY' => 'CONWAY',
			'CRAIGHEAD' => 'CRAIGHEAD',
			'CRAWFORD' => 'CRAWFORD',
			'CRITTENDEN' => 'CRITTENDEN',
			'CROSS' => 'CROSS',
			'DALLAS' => 'DALLAS',
			'DESHA' => 'DESHA',
			'DREW' => 'DREW',
			'FAULKNER' => 'FAULKNER',
			'FRANKLIN' => 'FRANKLIN',
			'FULTON' => 'FULTON',
			'GARLAND' => 'GARLAND',
			'GRANT' => 'GRANT',
			'GREENE' => 'GREENE',
			'HEMPSTEAD' => 'HEMPSTEAD',
			'HOT SPRING' => 'HOT SPRING',
			'HOWARD' => 'HOWARD',
			'INDEPENDENCE' => 'INDEPENDENCE',
			'IZARD' => 'IZARD',
			'JACKSON' => 'JACKSON',
			'JEFFERSON' => 'JEFFERSON',
			'JOHNSON' => 'JOHNSON',
			'LAFAYETTE' => 'LAFAYETTE',
			'LAWRENCE' => 'LAWRENCE',
			'LEE' => 'LEE',
			'LINCOLN' => 'LINCOLN',
			'LITTLE RIVER' => 'LITTLE RIVER',
			'LOGAN' => 'LOGAN',
			'LONOKE' => 'LONOKE',
			'MADISON' => 'MADISON',
			'MARION' => 'MARION',
			'MILLER' => 'MILLER',
			'MISSISSIPPI' => 'MISSISSIPPI',
			'MONROE' => 'MONROE',
			'MONTGOMERY' => 'MONTGOMERY',
			'NEVADA' => 'NEVADA',
			'NEWTON' => 'NEWTON',
			'OUACHITA' => 'OUACHITA',
			'PERRY' => 'PERRY',
			'PHILLIPS' => 'PHILLIPS',
			'PIKE' => 'PIKE',
			'POINSETT' => 'POINSETT',
			'POLK' => 'POLK',
			'POPE' => 'POPE',
			'PRAIRIE' => 'PRAIRIE',
			'PULASKI' => 'PULASKI',
			'RANDOLPH' => 'RANDOLPH',
			'SALINE' => 'SALINE',
			'SCOTT' => 'SCOTT',
			'SEARCY' => 'SEARCY',
			'SEBASTIAN' => 'SEBASTIAN',
			'SEVIER' => 'SEVIER',
			'SHARP' => 'SHARP',
			'ST FRANCIS' => 'ST FRANCIS',
			'STONE' => 'STONE',
			'UNION' => 'UNION',
			'VAN BUREN' => 'VAN BUREN',
			'WASHINGTON' => 'WASHINGTON',
			'WHITE' => 'WHITE',
			'WOODRUFF' => 'WOODRUFF',
			'YELL' => 'YELL',
		);
		$this->ret=$this->makeSelect($name, $Counties, 1, $selected);
		return $this->ret;
	}

	function selectCountry($name, $selected='', $parts=''){
  	if(!$selected){
    	$selected = 'US';
  	}
  	$Countries = array(
  	'US'   =>  'United States',
		'AD'   =>  'Andorra',
'AE'   =>  'United Arab Emirates',
'AF'   =>  'Afghanistan',
'AG'   =>  'Antigua and Barbuda',
'AI'   =>  'Anguilla',
'AL'   =>  'Albania',
'AM'   =>  'Armenia',
'AN'   =>  'Netherlands Antilles',
'AO'   =>  'Angola',
'AQ'   =>  'Antarctica',
'AR'   =>  'Argentina',
'AS'   =>  'American Samoa',
'AT'   =>  'Austria',
'AU'   =>  'Australia',
'AW'   =>  'Aruba',
'AZ'   =>  'Azerbaidjan',
'BA'   =>  'Bosnia-Herzegovina',
'BB'   =>  'Barbados',
'BD'   =>  'Bangladesh',
'BE'   =>  'Belgium',
'BF'   =>  'Burkina Faso',
'BG'   =>  'Bulgaria',
'BH'   =>  'Bahrain',
'BI'   =>  'Burundi',
'BJ'   =>  'Benin',
'BM'   =>  'Bermuda',
'BN'   =>  'Brunei Darussalam',
'BO'   =>  'Bolivia',
'BR'   =>  'Brazil',
'BS'   =>  'Bahamas',
'BT'   =>  'Bhutan',
'BV'   =>  'Bouvet Island',
'BW'   =>  'Botswana',
'BY'   =>  'Belarus',
'BZ'   =>  'Belize',
'CA'   =>  'Canada',
'CC'   =>  'Cocos (Keeling) Islands',
'CF'   =>  'Central African Republic',
'CD'   =>  'Congo, Dem. Rep. of the',
'CG'   =>  'Congo',
'CH'   =>  'Switzerland',
'CI'   =>  "Ivory Coast",
'CK'   =>  'Cook Islands',
'CL'   =>  'Chile',
'CM'   =>  'Cameroon',
'CN'   =>  'China',
'CO'   =>  'Colombia',
'CR'   =>  'Costa Rica',
'CS'   =>  'Former Czechoslovakia',
'CU'   =>  'Cuba',
'CV'   =>  'Cape Verde',
'CX'   =>  'Christmas Island',
'CY'   =>  'Cyprus',
'CZ'   =>  'Czech Republic',
'DE'   =>  'Germany',
'DJ'   =>  'Djibouti',
'DK'   =>  'Denmark',
'DM'   =>  'Dominica',
'DO'   =>  'Dominican Republic',
'DZ'   =>  'Algeria',
'EC'   =>  'Ecuador',
'EE'   =>  'Estonia',
'EG'   =>  'Egypt',
'EH'   =>  'Western Sahara',
'ER'   =>  'Eritrea',
'ES'   =>  'Spain',
'ET'   =>  'Ethiopia',
'FI'   =>  'Finland',
'FJ'   =>  'Fiji',
'FK'   =>  'Falkland Islands',
'FM'   =>  'Micronesia',
'FO'   =>  'Faroe Islands',
'FR'   =>  'France',
'FX'   =>  'France (European Terr.)',
'GA'   =>  'Gabon',
'GB'   =>  'Great Britain',
'GD'   =>  'Grenada',
'GE'   =>  'Georgia',
'GF'   =>  'French Guyana',
'GH'   =>  'Ghana',
'GI'   =>  'Gibraltar',
'GL'   =>  'Greenland',
'GM'   =>  'Gambia',
'GN'   =>  'Guinea',
'GP'   =>  'Guadeloupe (French)',
'GQ'   =>  'Equatorial Guinea',
'GR'   =>  'Greece',
'GS'   =>  'S. Georgia & S. Sandwich Isls.',
'GT'   =>  'Guatemala',
'GU'   =>  'Guam (USA)',
'GW'   =>  'Guinea Bissau',
'GY'   =>  'Guyana',
'HK'   =>  'Hong Kong',
'HM'   =>  'Heard and McDonald Islands',
'HN'   =>  'Honduras',
'HR'   =>  'Croatia',
'HT'   =>  'Haiti',
'HU'   =>  'Hungary',
'ID'   =>  'Indonesia',
'IE'   =>  'Ireland',
'IL'   =>  'Israel',
'IN'   =>  'India',
'IO'   =>  'British Indian Ocean Terr.',
'IQ'   =>  'Iraq',
'IR'   =>  'Iran',
'IS'   =>  'Iceland',
'IT'   =>  'Italy',
'JM'   =>  'Jamaica',
'JO'   =>  'Jordan',
'JP'   =>  'Japan',
'KE'   =>  'Kenya',
'KG'   =>  'Kyrgyz Republic (Kyrgyzstan)',
'KH'   =>  'Cambodia, Kingdom of',
'KI'   =>  'Kiribati',
'KM'   =>  'Comoros',
'KN'   =>  'Saint Kitts & Nevis Anguilla',
'KP'   =>  'North Korea',
'KR'   =>  'South Korea',
'KW'   =>  'Kuwait',
'KY'   =>  'Cayman Islands',
'KZ'   =>  'Kazakhstan',
'LA'   =>  'Laos',
'LB'   =>  'Lebanon',
'LC'   =>  'Saint Lucia',
'LI'   =>  'Liechtenstein',
'LK'   =>  'Sri Lanka',
'LR'   =>  'Liberia',
'LS'   =>  'Lesotho',
'LT'   =>  'Lithuania',
'LU'   =>  'Luxembourg',
'LV'   =>  'Latvia',
'LY'   =>  'Libya',
'MA'   =>  'Morocco',
'MC'   =>  'Monaco',
'MD'   =>  'Moldavia',
'MG'   =>  'Madagascar',
'MH'   =>  'Marshall Islands',
'MK'   =>  'Macedonia',
'ML'   =>  'Mali',
'MM'   =>  'Myanmar',
'MN'   =>  'Mongolia',
'MO'   =>  'Macau',
'MP'   =>  'Northern Mariana Islands',
'MQ'   =>  'Martinique (French)',
'MR'   =>  'Mauritania',
'MS'   =>  'Montserrat',
'MT'   =>  'Malta',
'MU'   =>  'Mauritius',
'MV'   =>  'Maldives',
'MW'   =>  'Malawi',
'MX'   =>  'Mexico',
'MY'   =>  'Malaysia',
'MZ'   =>  'Mozambique',
'NA'   =>  'Namibia',
'NC'   =>  'New Caledonia (French)',
'NE'   =>  'Niger',
'NF'   =>  'Norfolk Island',
'NG'   =>  'Nigeria',
'NI'   =>  'Nicaragua',
'NL'   =>  'Netherlands',
'NO'   =>  'Norway',
'NP'   =>  'Nepal',
'NR'   =>  'Nauru',
'NT'   =>  'Neutral Zone',
'NU'   =>  'Niue',
'NZ'   =>  'New Zealand',
'OM'   =>  'Oman',
'PA'   =>  'Panama',
'PE'   =>  'Peru',
'PF'   =>  'Polynesia (French)',
'PG'   =>  'Papua New Guinea',
'PH'   =>  'Philippines',
'PK'   =>  'Pakistan',
'PL'   =>  'Poland',
'PM'   =>  'Saint Pierre and Miquelon',
'PN'   =>  'Pitcairn Island',
'PR'   =>  'Puerto Rico',
'PT'   =>  'Portugal',
'PW'   =>  'Palau',
'PY'   =>  'Paraguay',
'QA'   =>  'Qatar',
'RE'   =>  'Reunion (French)',
'RO'   =>  'Romania',
'RU'   =>  'Russian Federation',
'RW'   =>  'Rwanda',
'SA'   =>  'Saudi Arabia',
'SB'   =>  'Solomon Islands',
'SC'   =>  'Seychelles',
'SD'   =>  'Sudan',
'SE'   =>  'Sweden',
'SG'   =>  'Singapore',
'SH'   =>  'Saint Helena',
'SI'   =>  'Slovenia',
'SJ'   =>  'Svalbard & Jan Mayen Isls.',
'SK'   =>  'Slovak Republic',
'SL'   =>  'Sierra Leone',
'SM'   =>  'San Marino',
'SN'   =>  'Senegal',
'SO'   =>  'Somalia',
'SR'   =>  'Suriname',
'ST'   =>  'Saint Tome & Principe',
'SU'   =>  'Former USSR',
'SV'   =>  'El Salvador',
'SY'   =>  'Syria',
'SZ'   =>  'Swaziland',
'TC'   =>  'Turks and Caicos Islands',
'TD'   =>  'Chad',
'TF'   =>  'French Southern Territories',
'TG'   =>  'Togo',
'TH'   =>  'Thailand',
'TJ'   =>  'Tadjikistan',
'TK'   =>  'Tokelau',
'TM'   =>  'Turkmenistan',
'TN'   =>  'Tunisia',
'TO'   =>  'Tonga',
'TP'   =>  'East Timor',
'TR'   =>  'Turkey',
'TT'   =>  'Trinidad and Tobago',
'TV'   =>  'Tuvalu',
'TW'   =>  'Taiwan',
'TZ'   =>  'Tanzania',
'UA'   =>  'Ukraine',
'UG'   =>  'Uganda',
'UK'   =>  'United Kingdom',
'UM'   =>  'USA Minor Outlying Islands',
'UY'   =>  'Uruguay',
'UZ'   =>  'Uzbekistan',
'VA'   =>  'Holy See (Vatican City State)',
'VC'   =>  'Saint Vincent & Grenadines',
'VE'   =>  'Venezuela',
'VG'   =>  'Virgin Islands (British)',
'VI'   =>  'Virgin Islands (USA)',
'VN'   =>  'Vietnam',
'VU'   =>  'Vanuatu',
'WF'   =>  'Wallis and Futuna Islands',
'WS'   =>  'Samoa',
'YE'   =>  'Yemen',
'YT'   =>  'Mayotte',
'YU'   =>  'Yugoslavia',
'ZA'   =>  'South Africa',
'ZM'   =>  'Zambia',
'ZR'   =>  'Zaire',
'ZW'   =>  'Zimbabwe',
  	   );
  	$this->ret=$this->makeSelect($name, $Countries, 1, $selected);
  	return $this->ret;
	}

	function selectMon($name, $selected='', $prompt='', $abbrev=1, $parts=''){
  	if(!$selected){
    	$selected = 0;
  	}

    if($prompt){
      $Months = array(
        '' => $prompt
      );
    }else{
      $Months = array();
    }

    $Months[1] =  $abbrev==1 ? 'Jan' : 'January';
    $Months[2] =  $abbrev==1 ? 'Feb' : 'February';
    $Months[3] =  $abbrev==1 ? 'Mar' : 'March';
    $Months[4] =  $abbrev==1 ? 'Apr' : 'April';
    $Months[5] =  $abbrev==1 ? 'May' : 'May';
    $Months[6] =  $abbrev==1 ? 'Jun' : 'June';
    $Months[7] =  $abbrev==1 ? 'Jul' : 'July';
    $Months[8] =  $abbrev==1 ? 'Aug' : 'August';
    $Months[9] =  $abbrev==1 ? 'Sep' : 'September';
    $Months[10] = $abbrev==1 ? 'Oct' : 'October';
    $Months[11] = $abbrev==1 ? 'Nov' : 'November';
    $Months[12] = $abbrev==1 ? 'Dec' : 'December';

  	$this->ret = '<select name="'.$name.'" '.$parts.'>';
  	while(list($ab,$nm) = each($Months)){
    	if($selected == $ab){
      	$sel = 'selected';
    	}else{
      	$sel = '';
    	}
    	$this->ret .= "\n<option ".$sel.' value="'.$ab.'">'.$nm.'</option>';
  	}

  	return $this->ret . '</select>';
}
	function selectCCYear($name, $selected='', $parts='', $tabindex=''){
  		if(!$selected){
    			$selected = 0;
  		}
		if ($tabindex){
   			$tab = ' tabindex='.$tabindex.' ';
  		}else{
   			$tab = '';
   		}
  		$year_sel = '<select name='.$name.$tab.'>';
    		$thisYear = date("Y", mktime());
    		settype($thisYear, "integer");

    		if(!$selected){
      		$selected = $thisYear;
    		}else{
      		settype($selected, "integer");
    		}

    		for($i=$thisYear; $i<($thisYear+15); $i++){
      		if($i == $selected){
       	 		$sel = ' selected';
      		}else{
        			$sel = '';
      		}
      		$year_sel .= '<option'.$sel.' value="'.$i.'">'.$i.'';
    		}
   	 $year_sel .= '</select>';
	return $year_sel;
	}
}
?>
