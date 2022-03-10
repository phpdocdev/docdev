<?

$this->External = array(
   Comments => 'Please enter your payment information',
   Caption => 'Payment Information',
	 AutoCheck => 1,
   Envelope => array(
     CC_NAME => array(
       "Name on Credit Card", 1, 'John Doe', '', 'text', 20, NULL 
     ),
     CC_ADDR => array(
       "Address", 1, '123 South St.', '', 'text', 20, NULL 
     ),
     CC_CITY => array(
       "City", 1, 'Little Rock', '', 'text', 20, NULL 
     ),
     CC_STATE => array(
       'State', 0, 'AR', 'AR', 'selectState'
     ),          
     CC_ZIP => array(
       "Zip code", 1, '72012', '', 'text', 20, NULL 
     ),              
     CC_TYPE => array(
       "Credit Card Type", 1, 'V','', 'radio', 'V'
     ),                
     CC_NUM => array(
       "Credit Card Number", 1, '1111111111111111', '', 'text', 16, NULL 		 
     ),           
     CC_EXP_MO => array(
       'Expiration Date', 1, '4', '', 'selectMonth'
     ),
     CC_EXP_YR => array(
       'Expiration Date year', 1, 2004, '', 'select', array(2001=>2001, 2002=>2002, 2003=>2003, 2004=>2004, 2005=>2005, 2006=>2006, 2007=>2007, 2008=>2008, 2009=>2009, 2010=>2010, 2011=>2011, 2012=>2012, 2013=>2013, 2014=>2014,2015=>2015), 1, 0
     ),        
     CHECK_ROUTING => array(
       "routing", 1, '123456789', '', 'text', 9, NULL 
     ),        
     CHECK_ACCOUNT => array(
       "account", 1, '41234235', '', 'text', 15, NULL 
     ),        
//     CHECK_NUMBER => array(
//       "check num", 1, '304', '', 'text', 5, NULL 
//     ), 
	),
);

?>