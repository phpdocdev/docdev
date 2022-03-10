<?php // vim: set expandtab ts=4 sw=4 sts=4:
/**
 * NIC Payment Portal Objects
 *
 * These classes implement a native PHP interface to the NIC Payment Engine.
 * All interactions with the payment engine are initiated through an 
 * NIC_PE_Gateway object.
 *
 * @package NIC_PE
 * @author Trent Bills <tbills@nicusa.com>
 * @version $Id: PE.php,v 1.12 2005/05/26 19:09:44 patrick Exp $
 * @copyright 2001-2004 NIC, Inc. All Rights Reserved http://www.nicusa.com
 *
 * @TODO Complete API Documentation (in doc-blocks that have the keyword 'TODO')
 */

require_once('NIC/TPE/XML.php'); # JOSH CHANGED PATH FROM NIC/XML.php to NIC/TPE/XML/PHP

//--- START CONFIGURATION -----------------------------------------------------
/**
 * The port to connect to the NIC Payment Engine.
 */
define('NIC_PE_DefaultPort', 4430);

/**
 * The host name or IP address of the NIC Payment Engine.
 */
define('NIC_PE_DefaultHost', 'ssl://tpetest.dev.cdc.nicusa.com');

/**
 * The unique merchant ID.
 */
define('NIC_PE_DefaultMerchant', 'TESTACCT');

/**
 * The merchant's secret key that is used to authenticate the merchant when 
 * making a request to the NIC Payment Engine.
 */
define('NIC_PE_DefaultKey', 'TEST_KEY_PW');
//--- END CONFIGURATION -------------------------------------------------------

/* !! You shouldn't need to modify anything below this point... !! */

/**
 * string value
 */
define('NIC_PE_STRING_ATTRIBUTE', 'STRING');

/**
 * date value
 */
define('NIC_PE_DATE_ATTRIBUTE', 'DATE');

/**
 * integer value
 */
define('NIC_PE_INTEGER_ATTRIBUTE', 'INTEGER');

/**TODO
 * define doc block
 */
define('NIC_PE_CardType_Visa', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_CardType_MasterCard', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_CardType_Discover', 3);

/**TODO
 * define doc block
 */
define('NIC_PE_CardType_AmEx', 4);

/**TODO
 * define doc block
 */
define('NIC_PE_CardType_Diners', 5);

/**TODO
 * define doc block
 */
define('NIC_PE_AccountType_Checking', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_AccountType_Savings', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_AccountType_GeneralLedger', 3);

/**TODO
 * define doc block
 */
define('NIC_PE_AccountType_Loan', 4);

/**TODO
 * define doc block
 */
define('NIC_PE_AuthType_Unknown', -1);

/**TODO
 * define doc block
 */
define('NIC_PE_AuthType_Web', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_AuthType_Signature', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_AuthType_Telephone', 3);

/**TODO
 * define doc block
 */
define('NIC_PE_CustomerType_Unknown', -1);

/**TODO
 * define doc block
 */
define('NIC_PE_CustomerType_Consumer', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_CustomerType_Business', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_Authorization', 'AUTH');

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_Payment', 'PAYMENT');

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_Refund', 'REFUND');

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_Return', 'RETURN');

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_Void', 'VOID');

/**TODO
 * define doc block
 */
define('NIC_PE_TransType_ReverseReturn', 'REVERSE_RETURN');

/**TODO
 * define doc block
 */
define('NIC_PE_TransactionType_Unknown', -1);

/**TODO
 * define doc block
 */
define('NIC_PE_TransactionType_Single', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_TransactionType_Recurring', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_PaymentImplement_Unknown', -1);

/**TODO
 * define doc block
 */
define('NIC_PE_PaymentImplement_CreditCard', 1);

/**TODO
 * define doc block
 */
define('NIC_PE_PaymentImplement_BankAccount', 2);

/**TODO
 * define doc block
 */
define('NIC_PE_PaymentImplement_BillingAccount', 3);

/**TODO
 * define doc block
 */
define('NIC_PE_ItemClass_Basic', 'BASIC');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemClass_Calculated', 'CALCULATED');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemClass_TableBased', 'TABLE_BASED');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemType_General', 'GENERAL');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemType_Fee', 'FEE');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemType_Tax', 'Tax');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemType_Discount', 'DISCOUNT');

/**TODO
 * define doc block
 */
define('NIC_PE_ItemType_Global', 'GLOBAL');

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_None', 0x00);

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_General', 0x01);

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_Fee', 0x02);

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_Tax', 0x04);

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_Discount', 0x08);

/**TODO
 * define doc block
 */
define('NIC_PE_IncMode_All', 0x0f);

/**
 * Returns a date/time stamp formated for the payment engine 
 * ``CCYY-MM-DD HH:MI:SS ZZZ'' (eg. '2002-04-01 11:34:15 CST')
 *
 * @return string
 */
function NIC_PE_Date()
{
    return(date("Y/m/d G:i:s T"));
} 

/**
 * Mailing address information
 *
 * This class contains mailing address information including two address 
 * lines, city, state, and zip code.
 *
 * @package NIC_PE
 */
class NIC_PE_Address extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty address.
     *
     * @return NIC_PE_Address
     */
    function NIC_PE_Address()
    {
        $this->NIC_XML();
        $this->name = 'address';
    } 

    /**
     * Retrieves the first line of the address.
     *
     * Longer description...
     *
     * @return string
     */
    function getAddress1()
    {
        return($this->vars['address-1']);
    } 

    /**
     * Retrieves the second line of the address.
     *
     * Longer description...
     *
     * @return string
     */
    function getAddress2()
    {
        return($this->vars['address-2']);
    } 

    /**
     * Retrieves the city name for the address.
     *
     * Longer description...
     *
     * @return string
     */
    function getCity()
    {
        return($this->vars['city']);
    } 

    // TODO::Document this function
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getCountryCode()
    {
        return($this->vars['country-code']);
    } 

    /**
     * Retrieves the state abbreviation for the address.
     *
     * Longer description...
     *
     * @return string
     */
    function getState()
    {
        return($this->vars['state']);
    } 

    /**
     * Retrieves the zip or postal code for the address.
     *
     * Longer description...
     *
     * @return string
     */
    function getZip()
    {
        return($this->vars['zip']);
    } 

    /**
     * Sets the first line of the address.
     *
     * Longer description...
     *
     * @param string $addr1
     * @return void
     */
    function setAddress1($addr1)
    {
        $this->vars['address-1'] = $addr1;
    } 

    /**
     * Sets the second line of the address.
     *
     * Longer description...
     *
     * @param string $addr2
     * @return void
     */
    function setAddress2($addr2)
    {
        $this->vars['address-2'] = $addr2;
    } 

    /**
     * Sets the city name for the address.
     *
     * Longer description...
     *
     * @param string $city
     * @return void
     */
    function setCity($city)
    {
        $this->vars['city'] = $city;
    } 

    // TODO::Document this function
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param string $code
     * @return void
     */
    function setCountryCode($code)
    {
        $this->vars['country-code'] = $code;
    } 

    /**
     * Sets the state abbreviation for the address.
     *
     * Longer description...
     *
     * @param string $state
     * @return void
     */
    function setState($state)
    {
        $this->vars['state'] = $state;
    } 

    /**
     * Sets the zip or postal code for the address.
     *
     * Longer description...
     *
     * @param string $zip
     * @return void
     */
    function setZip($zip)
    {
        $this->vars['zip'] = $zip;
    } 
} 

/**
 * Container for multiple attributes
 *
 * This class is a container for any number of NIC_PE_Attribute objects.
 *
 * @package NIC_PE
 */
class NIC_PE_Attributes extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty attribute container.
     *
     * @return NIC_PE_Attributes
     */
    function NIC_PE_Attributes()
    {
        $this->NIC_XML();
        $this->name = 'attributes';
        $this->objary['attributes'] = array();
        $this->nodisp = true;
    } 

    /**
     * Retrieves an attribute from the container by $name
     *
     * Longer description...
     *
     * @param string $name
     * @return NIC_PE_Attribute
     */
    function getAttribute($name)
    {
        return($this->objary['attributes'][$name]);
    } 

    /**
     * Retrieves the number of attributes in the container.
     *
     * Longer description...
     *
     * @return integer
     */
    function numberAttributes()
    {
        return(count($this->objary['attributes']));
    } 

    // TODO:: Re-word this to show the differences in use
    /**
     * Sets an attribute in the container based on the previously created 
     * NIC_PE_Attribute $attrib.  
     *
     * Sets an attribute in the container given an attribute name and value. 
     * The type of the attribute added will be NIC_PE_STRING_ATTRIBUTE.
     *
     * Longer description...
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    function setAttribute($name, $value = '')
    {
        if (is_object($name)) {
            $attrib = $name;
        } else {
            $attrib = new NIC_PE_Attribute(NIC_PE_STRING_ATTRIBUTE, $name, $value);
        } 
        
        $this->objary['attributes'][$attrib->getName()] = $attrib;
    } 
} 

/**
 * Name, value pairs.
 *
 * This class contains attribute information which consists of a string name 
 * and an integer, date, or string value.
 *
 * @package NIC_PE
 */
class NIC_PE_Attribute extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty attribute. 
     *
     * $type is one of:
     *   NIC_PE_STRING_ATTRIBUTE
     *   NIC_PE_DATE_ATTRIBUTE
     *   NIC_PE_INTEGER_ATTRIBUTE
     *
     * @param string $type
     * @param string $name
     * @param string $value
     * @return NIC_PE_Attribute
     */
    function NIC_PE_Attribute($type = '', $name = '', $value = '')
    {
        $this->NIC_XML();
        $this->name = 'attribute';
        $this->attribs['type'] = $type;
        $this->setName($name);
        $this->setValue($value);
    } 

    /**
     * Retrieves the attribute name.
     *
     * Longer description...
     *
     * @return string
     */
    function getName()
    {
        return($this->attribs['name']);
    } 
    
    /**
     * Retrieves the type of the attribute.
     *
     * Longer description...
     *
     * @return string
     */
    function getType()
    {
        return($this->attribs['type']);
    } 

    /**
     * Retrieves the value of the attribute.
     *
     * Longer description...
     *
     * @return string
     */
    function getValue()
    {
        return($this->text);
    } 

    /**
     * Sets the attribute name.
     *
     * Longer description...
     *
     * @param string $name
     * @return void
     */
    function setName($name)
    {
        $this->attribs['name'] = $name;
    } 

    /**
     * Sets the value of the attribute.
     *
     * Longer description...
     *
     * @param string $value
     * @return void
     */
    function setValue($value)
    {
        $this->text = $value;
    } 

    /**
     * Sets the attribute type.
     *
     * Where $type is one of:
     *   NIC_PE_STRING_ATTRIBUTE
     *   NIC_PE_DATE_ATTRIBUTE
     *   NIC_PE_INTEGER_ATTRIBUTE
     *
     * @param string $type
     * @return void
     */
    function setType($type)
    {
        $this->attribs['type'] = $type;
    } 

    // TODO::Document this function if it is of use?
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function validate()
    {
    } 
} 

/**
 * Bank account information for ACH/e-check payments
 *
 * This class contains information about a bank account for use in ACH or 
 * e-check transactions.
 *
 * @package NIC_PE
 */
class NIC_PE_BankAccount extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty BankAccount.
     *
     * @return NIC_PE_BankAccount
     */
    function NIC_PE_BankAccount()
    {
        $this->NIC_XML();
        $this->vars['authorization-type'] = NIC_PE_AuthType_Web;
        $this->vars['customer-type']      = NIC_PE_CustomerType_Consumer;
        $this->vars['transaction-type']   = NIC_PE_TransactionType_Single;
        $this->name                       = 'bank-account';
    } 

    /**
     * Retrieves the address of the account.
     *
     * Longer description...
     *
     * @return NIC_PE_Address
     */
    function getAccountAddress()
    {
        return($this->objs['address']);
    } 

    /**
     * Retrieves the account number of the bank account.
     *
     * Longer description...
     *
     * @return string
     */
    function getAccountNumber()
    {
        return($this->vars['account-number']);
    } 

    /**
     * Retrieves the type of the bank account.
     *
     * The type will be one of NIC_PE_AccountType_Checking or NIC_PE_AccountType_Savings.
     *
     * @return string
     */
    function getAccountType()
    {
        return($this->vars['account-type']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getAuthorizationType()
    {
        return($this->vars['authorization-type']);
    } 

    /**
     * Retrieves the check number for a particular ACH/e-check transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getCheckNumber()
    {
        return($this->vars['check-number']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getCustomerType()
    {
        return($this->vars['customer-type']);
    } 

    /**
     * Retrieves the name on the bank account.
     *
     * Longer description...
     *
     * @return string
     */
    function getNameOnAccount()
    {
        return($this->vars['name-on-account']);
    } 

    /**
     * Retrieves the routing number of the bank account.
     *
     * Longer description...
     *
     * @return string
     */
    function getRoutingNumber()
    {
        return($this->vars['routing-number']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getTransactionType()
    {
        return($this->vars['transaction-type']);
    } 

    /**
     * Sets the bank account address. $address should be an NIC_PE_Address object
     *
     * Longer description...
     *
     * @param string $addr
     * @return void
     */
    function setAccountAddress($addr)
    {
        $this->objs['address'] = $addr;
    } 

    /**
     * Sets the account number of the bank account.
     *
     * Longer description...
     *
     * @param string $num
     * @return void
     */
    function setAccountNumber($num)
    {
        $this->vars['account-number'] = $num;
    } 

    /**
     * Sets the type of the bank account.
     *
     * Type should be one of NIC_PE_AccountType_Checking or NIC_PE_AccountType_Savings
     *
     * @param string $type
     * @return void
     */
    function setAccountType($type)
    {
        $this->vars['account-type'] = $type;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setAuthorizationType($auth)
    {
        $this->vars['authorization-type'] = $auth;
    } 

    /**
     * Sets the check number for a particular ACH/e-check transaction.
     *
     * Longer description...
     *
     * @param string $num
     * @return void
     */
    function setCheckNumber($num)
    {
        $this->vars['check-number'] = $num;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setCustomerType($cust)
    {
        $this->vars['customer-type'] = $cust;
    } 

    /**
     * Sets the name on the bank account.
     *
     * Longer description...
     *
     * @param string $name
     * @return void
     */
    function setNameOnAccount($name)
    {
        $this->vars['name-on-account'] = $name;
    } 

    /**
     * Sets the routing number for the bank account.
     *
     * Longer description...
     *
     * @param string $rtnum
     * @return void
     */
    function setRoutingNumber($rtnum)
    {
        $this->vars['routing-number'] = $rtnum;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setTransactionType($type)
    {
        $this->vars['transaction-type'] = $type;
    } 

    /**
     * If true, you do not need to set the Address for the bank account. The customer's address will be used instead.
     *
     * Longer description...
     *
     * @param boolean $yes
     * @return void
     */
    function setUseCustomerAddress($yes)
    {
        if ($yes) {
            $this->attribs['use-customer-address'] = 'YES';
        } else {
            unset($this->attribs['use-customer-address']);
        } 
    } 

    /**
     * Returns true if the customer's address is being used instead of the Address property of the bank account.
     *
     * Longer description...
     *
     * @return boolean
     */
    function useCustomerAddress()
    {
        if ($this->attribs['use-customer-address'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Validates the contents of the BankAccount object. A string error message is returned if the validate fails.
     *
     * Longer description...
     *
     * @return string
     */
    function validate()
    {
        if (!preg_match('/^\d+$/', $this->vars['routing-number'])) {
            return('Missing or invalid routing number ' . $this->vars['routing-number']);
        }

        if (!preg_match('/^\d+$/', $this->vars['account-number'])) {
            return('Missing or invalid account number ' . $this->vars['account-number']);
        }
        
        return(null);
    } 
} 

/**
 * Container for multiple comments
 *
 * This class is a container for any number of NIC_PE_Comment objects.
 *
 * @package NIC_PE
 */
class NIC_PE_Comments extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty comment container.
     *
     * @return NIC_PE_Comments
     */
    function NIC_PE_Comments()
    {
        $this->NIC_XML();
        $this->name = 'comments';
        $this->objary['comments'] = array();
        $this->nodisp = true;
    } 

    /**
     * Adds to the comments container.
     *
     * Constructs a new comment from $comment and adds it to the container. If 
     * $comment is an NIC_PE_Comments object, then it will add it to the 
     * container.
     *
     * @param string $comment
     * @return void
     */
    function addComment($comment)
    {
        if (!is_object($comment)) {
            $comment = new NIC_PE_Comment($comment);
        } 

        array_push($this->objary['comments'], $comment);
    } 

    /**
     * Retrieves the array of NIC_PE_Comment objects in the container.
     *
     * Longer description...
     *
     * @return array
     */
    function comments()
    {
        return($this->objary['comments']);
    } 

    /**
     * Retrieves the number of comments in the container.
     *
     * Longer description...
     *
     * @return integer
     */
    function numberComments()
    {
        return(count($this->objary['comments']));
    } 

    /**
     * Validates the contents of the comments object.
     *
     * A string error message is returned if comments cannot be validated.
     *
     * @return string
     */
    function validate()
    {
        for ($i = 0; $i < count($this->objary['comments']); $i++) {
            $comment = &$this->objary['comments'][$i];
            $err     = $comment->validate();
            
            if (strlen($err)) {
                return($err);
            }
        } 
        
        return(null);
    } 
} 

/**
 * Administrative comment
 *
 * This class contains an administrative comment which consists of a date/time 
 * stamp, administrative username, and a text comment.
 *
 * @package NIC_PE
 */
class NIC_PE_Comment extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty comment, if $text is not empty then the objects
     * comment will be set to it.
     *
     * @param string $text
     * @return NIC_PE_Comment
     */
    function NIC_PE_Comment($text = '')
    {
        $this->NIC_XML();
        $this->name = 'comment';
        $this->text = $text;
        $this->attribs['date'] = NIC_PE_Date();
    } 

    /**
     * Retrieves the admin username that created the comment.
     *
     * Longer description...
     *
     * @return string
     */
    function getAdminUsername()
    {
        return($this->attribs['admin-username']);
    } 

    /**
     * Retrieves the date/time stamp of the comment.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**
     * Retrieves the text of the comment.
     *
     * Longer description...
     *
     * @return string
     */
    function getText()
    {
        return($this->text);
    } 

    /**
     * Sets the text of the comment.
     *
     * Longer description...
     *
     * @param string $text
     * @return void
     */
    function setText($text)
    {
        $this->text = $text;
    } 

    /**
     * Validates the contents of the comment object.
     *
     * A string error message is returned if the comment cannot be validated.
     *
     * @return string
     */
    function validate()
    {
        if (!strlen($this->text)) {
            return('Comment is empty');
        }
        
        return(null);
    } 
} 

/**
 * Credit card information for credit card payments
 *
 * This class contains credit card information.
 *
 * @package NIC_PE
 */
class NIC_PE_CreditCard extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiates a new empty CreditCard.
     *
     * @return NIC_PE_CreditCard
     */
    function NIC_PE_CreditCard()
    {
        $this->NIC_XML();
        $this->name = 'credit-card';
    } 

    /**
     * Retrieves the billing address of the credit card.
     *
     * Longer description...
     *
     * @return NIC_PE_Address
     */
    function getBillingAddress()
    {
        return($this->objs['address']);
    } 

    /**
     * Retrieves the card number of the credit card.
     *
     * Longer description...
     *
     * @return string
     */
    function getCardNumber()
    {
        return($this->vars['card-number']);
    } 

    /**
     * Retrieves the type of the credit card.
     *
     * The type will be one of NIC_PE_CardType_Visa, NIC_PE_CardType_MasterCard, 
     * NIC_PE_CardType_Discover, NIC_PE_CardType_AmEx, or NIC_PE_CardType_Diners.
     *
     * @return string
     */
    function getCardType()
    {
        return($this->vars['card-type']);
    } 

    /**
     * Retrieves the expiration date of the credit card.
     *
     * The date will be in the form MM/YY.
     *
     * @return string
     */
    function getExpirationDate()
    {
        return($this->vars['expiration-date']);
    } 
    /**
     * Retrieves the cvv of the credit card.
     *
     * Longer description...     
     *
     * @param string $cvv
     * @return string
     */
    function getCvvCode()
    {
        return($this->vars['cvv']);
    } 
    /**
     * Retrieves the name on the credit card.
     *
     * Longer description...
     *
     * @return string
     */
    function getNameOnCard()
    {
        return($this->vars['name-on-card']);
    } 
    
    /**
     * returns true if the expiration date has passed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isExpired()
    {
        $matches = array();

        if (!preg_match('/^(\d+)\/(\d+)$/', $this->vars['expiration-date'], $matches)) {
            return(true);
        }
        
        $cmpmonth = $matches[1];
        $cmpyear  = $matches[2];
        $cmpyear += 2000;
        $curmonth = date('m');
        $curyear  = date('Y');
        $curdate  = sprintf("%04d%02d", $curyear, $curmonth);
        $cmpdate  = sprintf("%04d%02d", $cmpyear, $cmpmonth);
        
        if ($cmpdate < $curdate) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Sets the credit card billing address. $address should be an NIC_PE_Address object
     *
     * Longer description...
     *
     * @param string $address
     * @return void
     */
    function setBillingAddress($address)
    {
        $this->objs['address'] = $address;
    } 

    /**
     * Sets the card number of the credit card.
     *
     * Longer description...
     *
     * @param string $cardno
     * @return void
     */
    function setCardNumber($cardno)
    {
        $this->vars['card-number'] = $cardno;
    } 

    /**
     * Sets the type of the credit card.
     *
     * Type should be one of NIC_PE_CardType_Visa, NIC_PE_CardType_MasterCard, 
     * NIC_PE_CardType_AmEx, NIC_PE_CardType_Discover, or NIC_PE_CardType_Diners.
     *
     * @param string $ctype
     * @return void
     */
    function setCardType($ctype)
    {
        $this->vars['card-type'] = $ctype;
    } 

    /**
     * Sets the expiration date of the credit card.
     *
     * The expiration date should be specified in the form ``MM/YY'' where YY 
     * is the last two digits of the year.
     *
     * @param string $expdate
     * @return void
     */
    function setExpirationDate($expdate)
    {
        $this->vars['expiration-date'] = $expdate;
    } 

    /**
     * Sets the name on the credit card.
     *
     * Longer description...
     *
     * @param string $name
     * @return void
     */
    function setNameOnCard($name)
    {
        $this->vars['name-on-card'] = $name;
    } 

    /**
     * Sets the pin number of the credit card.
     *
     * Longer description...
     *
     * @param string $pin
     * @return void
     */
    function setPin($pin)
    {
        $this->vars['pin'] = $pin;
    } 
    
    /**
     * Sets the cvv of the credit card.
     *
     * Longer description...     
     *
     * @param string $cvv
     * @return void
     */
    function setCvvCode($cvv)
    {
        $this->vars['cvv'] = $cvv;
    } 
    
    /**
     * If true, you do not need to set the Address for the credit card. 
     * The customer's address will be used instead.
     *
     * Longer description...
     *
     * @param boolean $yes
     * @return void
     */
    function setUseCustomerAddress($yes)
    {
        if ($yes) {
            $this->attribs['use-customer-address'] = 'YES';
        } else {
            unset($this->attribs['use-customer-address']);
        } 
    } 

    /**
     * Returns true if the customer's address is being used instead of the 
     * Address property of the credit card.
     *
     * Longer description...
     *
     * @return boolean
     */
    function useCustomerAddress()
    {
        if ($this->attribs['use-customer-address'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Validates the contents of the CreditCard object.
     *
     * A string error message is returned if the validate fails.
     *
     * @return string
     */
    function validate()
    {
        if (!strlen($this->vars['card-number'])) {
            return('Missing card number');
        }
        
        if (!strlen($this->vars['expiration-date'])) {
            return('Missing expiration date');
        }
        
        if ($this->isExpired()) {
            return('Expiration date is past');
        }
        
        return(null);
    } 
} 

/**
 * Contains information about the user of a service
 *
 * This class contains information about a customer.
 *
 * @package NIC_PE
 */
class NIC_PE_Customer extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty customer.
     *
     * @return NIC_PE_Customer
     */
    function NIC_PE_Customer()
    {
        $this->NIC_XML();
        $this->name = 'customer';
        $this->objs['attributes'] = new NIC_PE_Attributes();
    } 
    
    // START Attribute functions
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function attributes()
    {
        return($this->objs['attributes']->attributes());
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getAttribute($name)
    {
        return($this->objs['attributes']->getAttribute($name));
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberAttributes()
    {
        return($this->objs['attributes']->numberAttributes());
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setAttribute($attrib, $value = '')
    {
        $this->objs['attributes']->setAttribute($attrib, $value);
    } 
    // STOP end Attribute functions
    
    /**
     * Retrieves the NIC_PE_Address for the customer.
     *
     * Longer description...
     *
     * @return NIC_PE_Address
     */
    function getAddress()
    {
        return($this->objs['address']);
    } 

    /**
     * Retrieves the contact name for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getContactName()
    {
        return($this->vars['contact-name']);
    } 

    /**
     * Retrieves the email address for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getEmailAddress()
    {
        return($this->vars['email']);
    } 

    /**
     * Retrieves the ip address for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getIpAddress()
    {
        return($this->vars['ip-address']);
    } 

    /**
     * Retrieves the phone number for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getPhoneNumber()
    {
        return($this->vars['phone-number']);
    } 

    /**
     * Retrieves the alternate phone number for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getPhoneNumber2()
    {
        return($this->vars['phone-number-2']);
    } 

    /**
     * Retrieves the username for the customer.
     *
     * Longer description...
     *
     * @return string
     */
    function getUsername()
    {
        return($this->vars['username']);
    } 

    /**
     * Sets the NIC_PE_Address $address for the customer.
     *
     * Longer description...
     *
     * @param string $addr
     * @return void
     */
    function setAddress($addr)
    {
        $this->objs['address'] = $addr;
    } 

    /**
     * Sets the contact name for the customer.
     *
     * Longer description...
     *
     * @param string $name
     * @return void
     */
    function setContactName($name)
    {
        $this->vars['contact-name'] = $name;
    } 

    /**
     * Sets the email address for the customer.
     *
     * Longer description...
     *
     * @param string $addr
     * @return void
     */
    function setEmailAddress($addr)
    {
        $this->vars['email'] = $addr;
    } 

    /**
     * Sets the ip address for the customer.
     *
     * Longer description...
     *
     * @param string $ip
     * @return void
     */
    function setIpAddress($ip)
    {
        $this->vars['ip-address'] = $ip;
    } 

    /**
     * Sets the phone number for the customer.
     *
     * Longer description...
     *
     * @param string $phone
     * @return void
     */
    function setPhoneNumber($phone)
    {
        $this->vars['phone-number'] = $phone;
    } 

    /**
     * Sets the alternate phone number for the customer.
     *
     * Longer description...
     *
     * @param string $phone2
     * @return void
     */
    function setPhoneNumber2($phone2)
    {
        $this->vars['phone-number-2'] = $phone2;
    } 

    /**
     * Sets the username for the customer.
     *
     * Longer description...
     *
     * @param string $username
     * @return void
     */
    function setUsername($username)
    {
        $this->vars['username'] = $username;
    } 

    /**
     * Validates the contents of the customer object.
     *
     * Returns an error message string if the validate fails.
     *
     * @return string
     */
    function validate()
    {
    } 
} 

/**
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_PE_GWRequest extends NIC_XML
{
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function NIC_PE_GWRequest($gw, $action)
    {
        $this->NIC_XML();
        $this->name = 'gateway-request';
        $this->attribs['merchant-id']  = $gw->merchant_id;
        $this->attribs['merchant-key'] = $gw->merchant_key;
        $this->attribs['action'] = $action;
        $this->objary['order']   = array();
        $this->objary['invoice'] = array();
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setRefundAmount($amount)
    {
        $this->vars['refund-amount'] = $amount;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setInvoice($invoice)
    {
        $this->objs['invoice'] = $invoice;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setInvoiceId($invoiceid)
    {
        $this->vars['invoice-id'] = $invoiceid;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setLocalReference($lref)
    {
        $this->vars['local-ref'] = $lref;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setOrder($order)
    {
        $this->objs['order'] = $order;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setOrderId($orderid)
    {
        $this->vars['order-id'] = $orderid;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setOrderQuery($orderquery)
    {
        $this->objs['order-query'] = $orderquery;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setServiceCode($service)
    {
        $this->vars['service-code'] = $service;
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setStatus($status)
    {
        $this->vars['status'] = $status;
    } 
} 

/**
 * Response from the payment engine.
 *
 * This class contains a response from the payment engine including an error 
 * code, error message, and/or other appropriate information to be included in 
 * the respone.
 *
 * @package NIC_PE
 */
class NIC_PE_GWResponse extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty gateway response.
     *
     * @return NIC_PE_GWResponse
     */
    function NIC_PE_GWResponse()
    {
        $this->NIC_XML();
        $this->name = 'gateway-response';
        $this->objary['order-headers'] = array();
        $this->attribs['date']    = NIC_PE_Date();
        $this->objary['orders']   = array();
        $this->objary['invoices'] = array();
    } 

    /**
     * Retrieves the date/time stamp of the gateway response.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**
     * Retrieves the failure code (if any).
     *
     * Longer description...
     *
     * @return string
     */
    function getFailureCode()
    {
        return($this->attribs['failure-code']);
    } 

    /**
     * Retrieves the descriptive failure message (if any).
     *
     * Longer description...
     *
     * @return string
     */
    function getFailureMessage()
    {
        return($this->attribs['failure-message']);
    } 

    /**
     * Retrieves the server id for the response.
     *
     * Longer description...
     *
     * @return string
     */
    function getServerId()
    {
        return($this->attribs['server-id']);
    } 

    /**
     * Returns true if the request corresponding to this response failed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isFailure()
    {
        if ((isset($this->attribs['is-failure'])) && ($this->attribs['is-failure'] == 'YES')) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Returns true if the request corresponding to this response succeeded.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isSuccess()
    {
        if ((!isset($this->attribs['is-failure'])) || ($this->attribs['is-failure'] != 'YES')) {
            return(true);
        }
        
        return(false);
    } 
} 

/**
 * Communicates with the payment engine
 *
 * This class is used to communicate with the NIC Payment Engine.
 *
 * @package NIC_PE
 */
class NIC_PE_Gateway
{
    /**
     * This class is used to communicate with the NIC Payment Engine.
     *
     * Instantiate a new gateway using the connection parameters $host, $port, 
     * $merchant_id, and $merchant_key if available, otherwise the defaults 
     * are used.
     *
     * @param string $host
     * @param string $port
     * @param string $merchant_id
     * @param string $merchant_key
     * @return NIC_PE_Gateway
     */
    function NIC_PE_Gateway($host = '', $port = '', $merchant_id = '', $merchant_key = '')
    {
        if (!strlen($host)) {
            $host = NIC_PE_DefaultHost;
        }
        
        if (!strlen($port)) {
            $port = NIC_PE_DefaultPort;
        }
        
        if (!strlen($merchant_id)) {
            $merchant_id = NIC_PE_DefaultMerchant;
        }
        
        if (!strlen($merchant_key)) {
            $merchant_key = NIC_PE_DefaultKey;
        }
        
        $this->host = $host;
        $this->port = $port;
        $this->merchant_id  = $merchant_id;
        $this->merchant_key = $merchant_key;
        $this->socket_timeout = 30; // added by bob sanders
        $this->emulate_connection = false; // added by bob sanders
        $this->request_log  = null; // added by bob sanders
        $this->response_log = null; // added by bob sanders
    } 
    
    /**
     * Performs a ping against the payment engine.
     *
     * Useful for checking if the payment engine is accepting request.
     *
     * @return boolean
     */
    function ping()
    {
        $gr = new NIC_PE_GWRequest($this, 'PING');
        $gr->attribs['request-seq-nbr'] = 1;
        $resp = $this->sendRequest($gr);
        
        /* TODO
        if ($resp->IsFailure() == true) {
            if ($resp->getFailureCode != 'PROC-34') {
                $fail = new NIC_PE_GWResponse();
                $resp->setFailureMessage('Database server not available');
                $resp->setFailureCode('DBEX-16');
            }
            
            return(true);
        }
        */
        
        return($resp->IsFailure());
    } 

    /**
     * Set to true for testing without connecting to a TPE instance.
     *
     * Longer description...
     *
     * @param boolean $doEmulate
     * @return void
     */
    function emulate_connection($doEmulate = true) // added by bob sanders
    {
        $this->emulate_connection = $doEmulate;
    } 

    /**
     * Log gateway requests to $filename.
     *
     * Longer description...
     *
     * @param string $filename full path and name for logfile
     * @return void
     */
    function log_requests($filename) // added by bob sanders
    {
        $this->request_log = $filename;
    } 

    /**
     * Log gateway responses to $filename.
     *
     * Longer description...
     *
     * @param string $filename full path and name for logfile
     * @return void
     */
    function log_responses($filename) // added by bob sanders
    {
        $this->response_log = $filename;
    } 

    /**
     * Sets the socket timeout for connections to TPE.
     *
     * Longer description...
     *
     * @param integer $timeout defaults to 30 seconds
     * @return void
     */
    function set_socket_timeout($timeout = 30) // added by bob sanders
    {
        $this->socket_timeout = $timeout;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function append_log($filename, $msg) // added by bob sanders
    {
	$msg = preg_replace('/([^>]+)<\/card-number>/s', '****************</card-number>', $msg);
        $fp = @fopen($filename, 'a');
        
        if ($fp) {
            fputs($fp, date("Y-m-d H:i:s") . ' ' . $msg . "\n\n");
            fclose($fp);
        } 
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function sendRequest($req)
    {
        $tmp = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $tmp .= $req->ToXML(); 
        // $len = strlen($tmp);
        // $xml = $len . '*' . $tmp;
        $xml = $tmp; 
        // print 'send request xml: ' . $xml . "\n";
        $fail = new NIC_PE_GWResponse();
        $fail->attribs['is-failure'] = 'YES';
        
        // TODO
        // this is always going to execute, but the else won't
        // was this intentional?
        if (1) {
           // $service = getservbyport($this->port, 'tcp'); // this var is never used
            //$address = gethostbyname($this->host); // this var is never used
            // $this->emulate_connection = false;
            // $this->request_log = NULL;
            // $this->response_log = NULL;
            
            if ($this->request_log) {
                $this->append_log($this->request_log, 'Request: ' . $xml);
            } 

            if ($this->emulate_connection) {
                // so I can develop without a connection to TPE
                // we just won't send anything
                // * this doesn't work, b/c the code depends on objects created
                // by the remote PE. I might be able to write a workaround
                // but I'm not sure its worth it.
            } else {
                // connect to the PE
                $socket = @fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->socket_timeout);

                if (!$socket) {
                    if ($this->response_log) {
                        $this->append_log($this->response_log, 'Gateway Error: Can\'t create socket tp ' . $this->host . ':' . $this->port . "\n\n");
                    } 
                    
                    $fail->attribs['failure-message'] = 'Gateway Error: Can not create socket to ' . $this->host . ':' . $this->port;
                    $this->lastresp = $fail;
                    
                    return($fail);
                } 
                
                // added by bob sanders (to fclose(), below)
                $erCode = @fputs($socket, $xml . "\n");
                
                if ($erCode == -1) {
                    if ($this->response_log) {
                        $this->append_log($this->response_log, 'could not write to socket' . "\n\n");
                    } 
                    
                    $fail->attribs['failure-message'] = 'could not write to socket';
                    $this->lastresp = $fail;
                    
                    return($fail);
                } 

                $buf = '';
                
                while (!feof($socket)) {
                    $buf .= fgets($socket, 8192);
                } 
                
                fclose($socket);

                if (!$buf) {
                    if ($this->response_log) {
                        $this->append_log($this->response_log, 'nothing read from port' . "\n\n");
                    } 
                    
                    $fail->attribs['failure-message'] = 'nothing read from port';
                    $this->lastresp = $fail;
                    
                    return($fail);
                } 
            } 

            if ($this->response_log) {
                $this->append_log($this->response_log, 'Reponse: ' . $buf . "\n\n");
            } 

            //$ary = explode("\n", $buf, 2); 
            // print 'parsing ' . $ary[1] . "\n";
            
            $objlist = array('address'          => 'NIC_PE_Address',
                             'attribute'        => 'NIC_PE_Attribute',
                             'attributes'       => 'NIC_PE_Attributes',
                             'bank-account'     => 'NIC_PE_BankAccount',
                             'comment'          => 'NIC_PE_Comment',
                             'comments'         => 'NIC_PE_Comments',
                             'credit-card'      => 'NIC_PE_CreditCard',
                             'customer'         => 'NIC_PE_Customer',
                             'gateway-request'  => 'NIC_PE_GWRequest',
                             'gateway-response' => 'NIC_PE_GWResponse',
                             'invoice'          => 'NIC_PE_Invoice',
                             'item'             => 'NIC_PE_Item',
                             'items'            => 'NIC_PE_Items',
                             'merchant-info'    => 'NIC_PE_MerchantInfo',
                             'order'            => 'NIC_PE_Order',
                             'order-header'     => 'NIC_PE_OrderHeader',
                             'services'         => 'NIC_PE_Services',
                             'service-info'     => 'NIC_PE_ServiceInfo',
                             'ftran'            => 'NIC_PE_Transaction'
                );
                
            $parser = new NIC_XMLParse($objlist);
            $obj    = $parser->parse($buf);
            $this->lastresp = $obj;
            
            return($obj);
        } 
//        else { // UNREACHABLE CODE :: if not 1, but it will always be 1 as set above
//            $objlist = array('address'          => 'NIC_PE_Address',
//                             'attribute'        => 'NIC_PE_Attribute',
//                             'attributes'       => 'NIC_PE_Attributes',
//                             'bank-account'     => 'NIC_PE_BankAccount',
//                             'comment'          => 'NIC_PE_Comment',
//                             'comments'         => 'NIC_PE_Comments',
//                             'credit-card'      => 'NIC_PE_CreditCard',
//                             'customer'         => 'NIC_PE_Customer',
//                             'gateway-request'  => 'NIC_PE_GWRequest',
//                             'gateway-response' => 'NIC_PE_GWResponse',
//                             'invoice'          => 'NIC_PE_Invoice',
//                             'item'             => 'NIC_PE_Item',
//                             'items'            => 'NIC_PE_Items',
//                             'order'            => 'NIC_PE_Order',
//                             'ftrans'           => 'NIC_PE_Transaction'
//                );
//
//            $parser = new NIC_XMLParse($objlist);
//            $obj    = new NIC_PE_GWResponse;
//            $obj->objs['order'] = $parser->parse($xml);
//            $this->lastresp = $obj;
//            
//            return($obj);
//        } 
//        
//        return($fail); // if we get here then something went wrong
    } 

    /**
     * Requests that the order identified by $orderid should be cancelled.
     *
     * Returns false unless the request fails. If an error occurs, use the 
     * getLastResponse() method to retrieve detailed error information.
     *
     * @param integer $orderid
     * @return boolean
     */
    function cancelOrder($orderid)
    {
        $gr = new NIC_PE_GWRequest($this, 'CANCEL_ORDER');
        $gr->setOrderId($orderid);
        $resp = $this->sendRequest($gr);
        
        return($resp->isFailure());
    } 

    /**
     * Requests that the order identified by $orderid should be completed.
     *
     * Returns false unless an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $orderid
     * @param boolean $success will only be completed if true
     * @return boolean
     */
    function completeOrder($orderid, $success)
    {
        $gr = new NIC_PE_GWRequest($this, 'COMPLETE');
        $gr->setOrderId($orderid);
        
        if ($success) {
            $gr->setStatus('SUCCESS');
        } else {
            $gr->setStatus('FAILURE');
        }
        
        $resp = $this->sendRequest($gr);
        
        return($resp->isFailure());
    } 

    /**
     * Retrieves an array of order headers for orders matching the selection criteria specified in NIC_PE_OrderQuery $query.
     *
     * NULL is returned if an error occurs. Use getLastResponse() to obtain detailed error information.
     *
     * @param string $query
     * @return array
     */
    function findMatchingOrders($query)
    {
        $gr = new NIC_PE_GWRequest($this, 'QUERY_ORDER');
        $gr->setOrderQuery($query);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['order-headers']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getEffectiveDate($service)
    {
        $gr = new NIC_PE_GWRequest($this, 'EFFECTIVE_DATE');
        $gr->setServiceCode($service);
        $resp = $this->sendRequest($gr);
        
        return($resp->vars['effective-date']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getMerchantInfo()
    {
        $gr = new NIC_PE_GWRequest($this, 'MERCHANT_INFO');
        $resp = $this->sendRequest($gr);
        
        return($resp->objs['merchant-info']);
    } 

    /**
     * Requests a new order from the payment engine for the service $service.
     *
     * Returns NULL if an error occurs. Use getLastResponse() to obtain detailed error information.
     *
     * @param string $service
     * @return NIC_PE_Order
     */
    function getNewOrder($service)
    {
        $gr = new NIC_PE_GWRequest($this, 'NEW_ORDER');
        $gr->setServiceCode($service);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['orders'][0]);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getServiceInfo($service)
    {
        $gr = new NIC_PE_GWRequest($this, 'SERVICE_INFO');
        $gr->setServiceCode($service);
        $resp = $this->sendRequest($gr);
        
        return($resp->objs['service-info']);
    } 

    /**
     * Requests that a customer be invoiced for the line items contained in the invoice.
     *
     * The set of items may be a subset of the items in the original order. 
     * Returns false unless an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $invoice
     * @return boolean
     */
    function issueInvoice($invoice)
    {
        if (gettype($invoice) == 'object') {
            $errs = $invoice->validate();
            
            if ($errs) {
                $fail = new NIC_PE_GWResponse();
                $fail->attribs['is-failure'] = 'YES';
                $fail->attribs['failure-message'] = 'Gateway Error: Invalid invoice: ' . $errs;
                $this->lastresp = $fail;
                
                return(true);
            } 
            
            $gr = new NIC_PE_GWRequest($this, 'INVOICE');
            $gr->setInvoice($invoice);
        } else {
            $gr = new NIC_PE_GWRequest($this, 'INVOICE_ENTIRE_ORDER');
            $gr->setOrderId($invoice);
        } 
        
        $resp = $this->sendRequest($gr);
        
        return($resp->isFailure());
    } 

    /**
     * Requests that an order be refunded for the specified $amount.
     *
     * If $invoiceid is omitted, the payment engine will attempt to find an 
     * appropriate invoice associated with $orderid. Returns false unless an 
     * error occurs. Use getLastResponse() to obtain detailed error information.
     *
     * @param integer $orderid
     * @param integer $invoiceid
     * @param string $amount
     * @return boolean
     */
    function issueRefund($orderid, $invoiceid, $amount = null)
    {
        $gr = new NIC_PE_GWRequest($this, 'REFUND');
        $gr->setOrderId($orderid);
        
        if (!$amount) {
            $amount = $invoiceid;
            $invoiceid = null;
        } 
        
        if ($invoiceid) {
            $gr->setInvoiceId($invoiceid);
        }
        
        $gr->setRefundAmount($amount); 
        // print "issueing refund:\n";
        // print_r($gr);
        $resp = $this->sendRequest($gr);
        
        return($resp->isFailure());
    } 

    /**
     * Retrieves the invoice specified by $invoice_id from the payment engine.
     *
     * NULL is returned if an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $invoice_id
     * @return NIC_PE_Invoice
     */
    function retrieveInvoice($invoice_id)
    {
        $gr = new NIC_PE_GWRequest($this, 'GET_INVOICE');
        $gr->setInvoiceId($invoice_id);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['invoices'][0]);
    } 

    /**
     * Retrieves the invoices for the given $orderid.
     *
     * NULL is returned if an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $orderid
     * @return NIC_PE_Invoice[]
     */
    function retrieveInvoices($orderid)
    {
        $gr = new NIC_PE_GWRequest($this, 'GET_INVOICES');
        $gr->setOrderId($orderid);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['invoices']);
    } 

    /**
     * Retrieves the order specified by $orderid from the payment engine.
     *
     * NULL is returned if an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $orderid
     * @return NIC_PE_Order
     */
    function retrieveOrder($orderid)
    {
        $gr = new NIC_PE_GWRequest($this, 'GET_ORDER');
        $gr->setOrderId($orderid);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['orders'][0]);
    } 

    /**
     * Retrieves the order(s) specified by $refid from the payment engine.
     *
     * Zero or more orders may be returned since the local reference identifier
     * is not guaranteed to be unique. NULL is returned if an error occurs. Use 
     * getLastResponse() to obtain detailed error information.
     *
     * @param string $refid
     * @return NIC_PE_Order[]
     */
    function retrieveOrderFromReference($refid)
    {
        $gr = new NIC_PE_GWRequest($this, 'GET_ORDERS');
        $gr->setLocalReference($refid);
        $resp = $this->sendRequest($gr);
        
        return($resp->objary['orders']);
    } 

    /**
     * Submits a completed order to the payment engine.
     *
     * Returns false unless an error occurs. Use getLastResponse() to obtain 
     * detailed error information.
     *
     * @param integer $order
     * @return boolean
     */
    function submitOrder($order)
    {
        $errs = $order->validate();
        
        if ($errs) {
            $fail = new NIC_PE_GWResponse();
            $fail->attribs['is-failure'] = 'YES';
            $fail->attribs['failure-message'] = 'Gateway Error: Invalid order: ' . $errs;
            $this->lastresp = $fail;
            
            return(true);
        } 
        
        $gr = new NIC_PE_GWRequest($this, 'SUBMIT');
        $gr->setOrder($order);
        $resp = $this->sendRequest($gr);
        
        return($resp->isFailure());
    } 

    /**
     * Returns the gateway response object corresponding to the last gateway 
     * request.
     *
     * Use this to retrieve detailed error information after an error occurs 
     * during a gateway request.
     *
     * @return NIC_PE_GWResponse
     */
    function getLastResponse()
    {
        return($this->lastresp);
    } 
} 

/**
 * Order fulfillment. Initiates payment.
 *
 * This class contains information an invoice. Invoices are used to initiate 
 * payment of some or all of the items in an order.
 *
 * @package NIC_PE
 */
class NIC_PE_Invoice extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty invoice.
     *
     * @return NIC_PE_Invoice
     */
    function NIC_PE_Invoice()
    {
        $this->NIC_XML();
        $this->name = 'invoice';
        $this->attribs['date'] = NIC_PE_Date();
        $this->objs['items'] = new NIC_PE_Items();
        $this->objs['comments'] = new NIC_PE_Comments();
    } 
    
    // Comment Functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function addComment($comment)
    {
        $this->objs['comments']->addComment($comment);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function comments()
    {
        return($this->objs['comments']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberComments()
    {
        return($this->objs['comments']->numberComments());
    } 
    // end Comment Functions
    
    // Item Functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function addItem($item)
    {
        $this->objs['items']->addItem($item);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function clearItems()
    {
        $this->objs['items']->clearItems();
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItem($index)
    {
        return($this->objs['items']->getItem($index));
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItemSubtotal($type)
    {
        return($this->objs['items']->getItemSubtotal($type));
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItemTotal()
    {
        return($this->objs['items']->getItemTotal());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function items()
    {
        return($this->objs['items']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberItems()
    {
        return($this->objs['items']->numberItems());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function removeItem($num)
    {
        $this->objs['items']->removeItem($num);
    } 
    // end Item Functions
    
    /**
     * Retrieves the date/time of the invoice.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getEffectiveDate()
    {
        return($this->attribs['effective-date']);
    } 

    /**TODO
     * Retrieves the unique identifier for the invoice.
     *
     * Longer description...
     *
     * @return string
     */
    function getId()
    {
        return($this->attribs['id']);
    } 

    /**
     * Retrieves the order id the invoice belongs to.
     *
     * Longer description...
     *
     * @return string
     */
    function getOrderId()
    {
        return($this->attribs['order-id']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getSettlementDate()
    {
        return($this->vars['settlement-date']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function isSettled()
    {
        if (strlen($this->vars['settlement-date'])) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Sets the order id the invoice belongs to.
     *
     * Longer description...
     *
     * @param string $id
     * @return void
     */
    function setOrderId($id)
    {
        $this->attribs['order-id'] = $id;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setEffectiveDate($effective_date)
    {
        $this->attribs['effective-date'] = $effective_date;
    } 

    /**
     * Validates the contents of the invoice object.
     *
     * Returns an error message string if the validate fails.
     *
     * @return string
     */
    function validate()
    {
        if ($this->attribs['order-id'] < 0) {
            return('missing order id');
        }
        
        if ($this->objs['items']->numberItems() < 1) {
            return('Invoice contains no items');
        }
        
        $err = $this->objs['items']->validate();
        
        if (strlen($err)) {
            return($err);
        }
        
        $err = $this->objs['comments']->validate();
        
        if (strlen($err)) {
            return($err); # BOB: this read if (strlen(err)) .. I assume that is wrong
        }
        
        return(null);
    } 
} 

/**
 * Line item component of an order
 *
 * This class contains information for a line item.
 *
 * @package NIC_PE
 */
class NIC_PE_Item extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new line item object:<br>
     * 
     * Valid classes are:<br>
     * <ul>
     *   <li>NIC_PE_ItemClass_Basic - A simple line item</li>
     *   <li>NIC_PE_ItemClass_Calculated - A line item calculated from other line items in the same invoice/order</li>
     *   <li>NIC_PE_ItemClass_TableBased - A line item calculated from other line items in the same invoice/order processed through a PriceTable</li>
     * </ul>
     *
     * Valid types are:<br>
     * <ul>
     *   <li>NIC_PE_ItemType_General - Normal line item.</li>
     *   <li>NIC_PE_ItemType_Fee - A fee item.</li>
     *   <li>NIC_PE_ItemType_Tax - A tax item.</li>
     *   <li>NIC_PE_ItemType_Discount - A discount line item.</li>
     *   <li>NIC_PE_ItemType_Global - A global line item.</li>
     * </ul>
     *
     * @param string $class defaults to NIC_PE_ItemClass_Basic
     * @param string $type defaults to NIC_PE_ItemType_General
     * @return NIC_PE_Item
     */
    function NIC_PE_Item($class = NIC_PE_ItemClass_Basic, $type = NIC_PE_ItemType_General)
    {
        $this->NIC_XML();
        $this->name = 'item';
        $this->objs['attributes'] = new NIC_PE_Attributes();
        $this->attribs['class'] = $class;
        $this->attribs['type'] = $type;
    } 

    // Attribute functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function attributes()
    {
        return($this->objs['attributes']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getAttribute($name)
    {
        return($this->objs['attributes']->getAttribute($name));
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberAttributes()
    {
        return($this->objs['attributes']->numberAttributes());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setAttribute($name, $value = '')
    {
        $this->objs['attributes']->setAttribute($name, $value);
    } 
    // end Attribute functions
    
    /**
     * Retrieves the text description for the item.
     *
     * Longer description...
     *
     * @return string
     */
    function getDescription()
    {
        return($this->vars['description']);
    } 

    /**
     * Returns the net effect this item has on the order/invoice.
     *
     * Longer description...
     *
     * @return float
     */
    function getExtendedPrice()
    {
        if ($this->attribs['class'] == NIC_PE_ItemClass_Basic) {
            if (empty($this->vars['quantity'])) {
                $this->vars['quantity'] = 1;
            }
            
            return($this->vars['unit-price'] * $this->vars['quantity']);
        } else { // table based or calculated
            $total = 0.0;
            $items = &$this->container;
            
            if ($this->attribs['type'] != NIC_PE_ItemType_General && $this->vars['include-mode'] &NIC_PE_IncMode_General) {
                $total += $items->getItemSubtotal(NIC_PE_ItemType_General);
            } 
            
            if ($this->attribs['type'] != NIC_PE_ItemType_Fee && $this->vars['include-mode'] &NIC_PE_IncMode_Fee) {
                $total += $items->getItemSubtotal(NIC_PE_ItemType_Fee);
            } 
            
            if ($this->attribs['type'] != NIC_PE_ItemType_Tax && $this->vars['include-mode'] &NIC_PE_IncMode_Tax) {
                $total += $items->getItemSubtotal(NIC_PE_ItemType_Tax);
            } 
            
            if ($this->attribs['type'] != NIC_PE_ItemType_Discount && $this->vars['include-mode'] &NIC_PE_IncMode_Discount) {
                $total += $items->getItemSubtotal(NIC_PE_ItemType_Discount);
            } 
            
            if ($this->attribs['class'] == NIC_PE_ItemClass_Calculated) {
                $total *= $this->vars['rate'];
                $total += $this->vars['flat-price'];
            } elseif ($this->attribs['class'] == NIC_PE_ItemClass_TableBased) {
                return($this->objs['price-table']->lookupPrice($total));
            } else { // can't figure the price if not a known class
                return(0.00);
            } 
            
            return($total);
        } 
    } 

    /**
     * Retrieves flat price. (for Calculated or TableBased items)
     *
     * Longer description...
     *
     * @return float
     */
    function getFlatPrice()
    {
        return($this->vars['flat-price']);
    } 
    
    /**
     * Retrieves the include mode for the item.
     *
     * The include mode is only used for calculated and table based items. The 
     * include mode value is a bitwise or of the constants:
     * 
     * <ul>
     *   <li>NIC_PE_IncMode_General - Include items of type general</li>
     *   <li>NIC_PE_IncMode_Fee - Include items of type fee</li>
     *   <li>NIC_PE_IncMode_Tax - Include items of type tax</li>
     *   <li>NIC_PE_IncMode_Discount - Include items of type discount</li>
     *   <li>NIC_PE_IncMode_All - Include all item types except global</li>
     * <ul>
     * 
     * @return integer
     */
    function getIncludeMode()
    {
        return($this->vars['include-mode']);
    } 

    /**
     * Returns the integer index of this item in its order/invoice.
     *
     * Index numbers begin a 1 and increase sequentially in the container.
     *
     * @return integer
     */
    function getIndex()
    {
        return($this->attribs['index']);
    } 

    /**
     * Retrieves the instance id.
     *
     * The instance id combines with the sku to form a unique key for an item 
     * with an order or invoice.
     *
     * @return string
     */
    function getInstanceId()
    {
        return($this->vars['instance-id']);
    } 

    /**
     * Retrieves the reference to the container this item is in.
     *
     * Longer description...
     *
     * @return string
     */
    function getItemContainer()
    {
        return($this->container);
    } 

    /**
     * Retrieves the price table for a table based item.
     *
     * Longer description...
     *
     * @return NIC_PE_PriceTable
     */
    function getPriceTable()
    {
        return($this->objs['price-table']);
    } 

    /**
     * Retrieves the rate for a calculated item.
     *
     * Longer description...
     *
     * @return float
     */
    function getRate()
    {
        return($this->vars['rate']);
    } 

    /**
     * Retrieves the sku for the item.
     *
     * Longer description...
     *
     * @return string
     */
    function getSku()
    {
        return($this->vars['sku']);
    } 

    /**
     * Retrieves the type of the item.
     *
     * The result will be one of: 
     * <ul>
     *   <li>NIC_PE_ItemType_General</li>
     *   <li>NIC_PE_ItemType_Fee</li>
     *   <li>NIC_PE_ItemType_Tax</li>
     *   <li>NIC_PE_ItemType_Discount</li>
     *   <li>NIC_PE_ItemType_Global</li>
     * </ul>
     *
     * @return string
     */
    function getType()
    {
        return($this->attribs['type']);
    } 

    /**
     * Retrieves the quantity of a basic item.
     *
     * For basic items, quantity combines with unit price to calculate the extended price.
     *
     * @return integer
     */
    function getQuantity()
    {
        return($this->vars['quantity']);
    } 

    /**
     * Retrieves the unit price of a basic item.
     *
     * Longer description...
     *
     * @return integer
     */
    function getUnitPrice()
    {
        return($this->vars['unit-price']);
    } 

    /**
     * Returns true if the include mode indicates that Discount items should be included.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeDiscount()
    {
        if ($this->vars['include-mode'] &NIC_PE_IncMode_Discount) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the include mode indicates that Fee items should be included.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeFee()
    {
        if ($this->vars['include-mode'] &NIC_PE_IncMode_Fee) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the include mode indicates that General items should be included.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeGeneral()
    {
        if ($this->vars['include-mode'] &NIC_PE_IncMode_General) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the include mode indicates that no items should be included.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeNone()
    {
        if (!$this->vars['include-mode']) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the include mode indicates that Tax items should be included.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeTax()
    {
        if ($this->vars['include-mode'] &NIC_PE_IncMode_Tax) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Sets the class of the item.
     *
     * Valid $class values are:
     * <ul>
     *   <li>NIC_PE_ItemClass_Basic</li>
     *   <li>NIC_PE_ItemClass_Calculated</li>
     *   <li>NIC_PE_ItemClass_TableBased</li>
     * </ul>
     *
     * @param string $class
     * @return void
     */
    function setClass($class)
    {
        $this->attribs['class'] = $class;
    } 

    /**
     * Sets the humanly readable description of the item.
     *
     * Longer description...
     *
     * @param string $desc
     * @return void
     */
    function setDescription($desc)
    {
        $this->vars['description'] = $desc;
    } 

    /**
     * Sets the flat price for a calculated item.
     *
     * Longer description...
     *
     * @param string $price
     * @return void
     */
    function setFlatPrice($price)
    {
        $this->vars['flat-price'] = $price;
    } 

    /**
     * Sets the include mode for calculated and table based items.
     *
     * $mode is a bitwise or one of:
     * <ul>
     *   <li>NIC_PE_IncMode_General</li>
     *   <li>NIC_PE_IncMode_Fee</li>
     *   <li>NIC_PE_IncMode_Tax</li>
     *   <li>NIC_PE_IncMode_Discount</li>
     * </ul>
     * 
     * Additionally, the constants NIC_PE_IncMode_None and NIC_PE_IncMode_All 
     * can be used to select none or all items respectively.
     *
     * @param string $mode
     * @return void
     */
    function setIncludeMode($mode)
    {
        $this->vars['include-mode'] = $mode;
    } 

    /**
     * Sets the index of the item.
     *
     * This is usually taken care of by the container, so you shouldn't need to
     * do this manually.
     *
     * @param string $index
     * @return void
     */
    function setIndex($index)
    {
        $this->attribs['index'] = $index;
    } 

    /**
     * Sets the instance id.
     *
     * The instance id combines with the sku to form a unique key for each item
     * in an invoice or order.
     *
     * @param string $id
     * @return void
     */
    function setInstanceId($id)
    {
        $this->vars['instance-id'] = $id;
    } 

    /**
     * Sets the container reference for this item.
     *
     * This is usually done for you by the container.
     *
     * @param string $container
     * @return void
     */
    function setItemContainer($container)
    {
        $this->container = $container;
    } 

    /**
     * Sets the NIC_PE_PriceTable $table for a table based item.
     *
     * Longer description...
     *
     * @param string $table
     * @return void
     */
    function setPriceTable($table)
    {
        $this->objs['price-table'] = $table;
        $this->vars['price-table-name'] = $table->getName();
    } 

    /**
     * Sets the rate for a calculated item.
     *
     * Other items in the order or invoice are selected by the value of include
     * mode and totalled. The flat price is added at the product of the total 
     * and the rate to get the extended price.
     *
     * @param string $rate
     * @return void
     */
    function setRate($rate)
    {
        $this->vars['rate'] = $rate;
    } 

    /**
     * Sets the sku for the item.
     *
     * The sku is a service specific product identifier.
     *
     * @param string $sku
     * @return void
     */
    function setSku($sku)
    {
        $this->vars['sku'] = $sku;
    } 

    /**
     * Sets the type of item.
     *
     * Valid types are:
     * <ul>
     *   <li>NIC_PE_ItemType_General</li>
     *   <li>NIC_PE_ItemType_Fee</li>
     *   <li>NIC_PE_ItemType_Tax</li>
     *   <li>NIC_PE_ItemType_Discount</li>
     *   <li>NIC_PE_ItemType_Global</li>
     * </ul>
     *
     * @param string $type
     * @return void
     */
    function setType($type)
    {
        $this->attribs['type'] = $type;
    } 

    /**
     * Sets the quantity of the item for basic items.
     *
     * Longer description...
     *
     * @param integer $qty
     * @return void
     */
    function setQuantity($qty)
    {
        $this->vars['quantity'] = $qty;
    } 

    /**
     * Sets the unit price of the item for basic items.
     *
     * Longer description...
     *
     * @param float $price
     * @return void
     */
    function setUnitPrice($price)
    {
        $this->vars['unit-price'] = $price;
    } 

    /**
     * Validates the contents of the Item object.
     *
     * Returns a string error message if the item cannot be validated.
     *
     * @return string
     */
    function validate()
    {
        $this->attribs['extended-price'] = $this->getExtendedPrice();
        
        if (empty($this->vars['sku'])) {
            return('Invalid item, no sku code specified');
        }
        
        if (empty($this->attribs['class'])) {
            return('Invalid item class');
        }
        
        if (empty($this->attribs['type'])) {
            return('Invalid item type');
        }
        
        if ($this->attribs['class'] == NIC_PE_ItemClass_Basic) {
            if (empty($this->vars['unit-price'])) {
                return('Missing unit price for basic item');
            }
        } 
        
        if ($this->attribs['class'] == NIC_PE_ItemClass_Calculated) {
            if (empty($this->vars['flat-price'])) {
                return('Missing flat price for calculated item');
            }
        } 
        
        if ($this->attribs['class'] == NIC_PE_ItemClass_TableBased) {
            if (empty($this->objs['price-table'])) {
                return('Missing price table for table based item');
            }
        } 
        
        return(null);
    } 
} 

/**
 * Container for multiple items
 *
 * This class is a container for any number of NIC_PE_Item objects.
 *
 * @package NIC_PE
 */
class NIC_PE_Items extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty item container.
     *
     * @return NIC_PE_Items
     */
    function NIC_PE_Items()
    {
        $this->NIC_XML();
        $this->name = 'items';
        $this->objary['items'] = array();
        $this->nodisp = true;
    } 

    /**
     * Adds $item to the container.
     *
     * Longer description...
     *
     * @param string $item
     * @return void
     */
    function addItem($item)
    {
        array_push($this->objary['items'], $item);
        $index = count($this->objary['items']) - 1;
        $thisitem = &$this->objary['items'][$index];
        $thisitem->setItemContainer($this);
        $thisitem->setIndex(count($this->objary['items']));
    } 

    /**
     * Removes all items from the container.
     *
     * Longer description...
     *
     * @return void
     */
    function clearItems()
    {
        $this->objary['items'] = array();
    } 

    /**
     * Retrieves an item by its index $index. 
     *
     * Index values range from 1 up to the number of items in the container.
     *
     * @param string $index
     * @return NIC_PE_Item
     */
    function getItem($index)
    {
        return($this->objary['items'][$index - 1]);
    } 

    /**
     * Returns the Total of the items in the container of type $type.
     *
     * $type is one of:
     * <ul>
     *   <li>NIC_PE_ItemType_General</li>
     *   <li>NIC_PE_ItemType_Fee</li>
     *   <li>NIC_PE_ItemType_Tax</li>
     *   <li>NIC_PE_ItemType_Discount</li>
     *   <li>NIC_PE_ItemType_Global</li>
     * </ul>
     *
     * @param string $type
     * @return float
     */
    function getItemSubtotal($type)
    {
        $this->reindex();
        $total = 0.0;
        
        for ($i = 0; $i < count($this->objary['items']); $i++) {
            $item = &$this->objary['items'][$i];
            
            if ($item->getType() == $type) {
                $total += $item->getExtendedPrice();
            }
        } 
        
        return($total);
    } 

    /**
     * Returns the total of all the items in the container.
     *
     * Longer description...
     *
     * @return float
     */
    function getItemTotal()
    {
        $this->reindex();
        $total = 0.0;
        
        for ($i = 0; $i < count($this->objary['items']); $i++) {
            $item = &$this->objary['items'][$i];
            $total += $item->getExtendedPrice();
        } 
        
        return($total);
    } 

    /**
     * Returns an array of the NIC_PE_Item's in the container.
     *
     * Longer description...
     *
     * @return array
     */
    function items()
    {
        return($this->objary['items']);
    } 

    /**
     * Returns the number of items in the container.
     *
     * Longer description...
     *
     * @return integer
     */
    function numberItems()
    {
        return(count($this->objary['items']));
    } 

    /**
     * Removes the item specified by $index.
     *
     * Index values range from 1 up to the number of items in the container.
     *
     * @param string $index
     * @return void
     */
    function removeItem($index)
    {
        array_splice($this->objary['items'], $index - 1, 1);
        $this->reindex();
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function reindex()
    {
        $index = 1;
        
        for ($i = 0; $i < count($this->objary['items']); $i++) {
            $obj = &$this->objary['items'][$i];
            $obj->setIndex($index ++);
            $obj->setItemContainer($this);
        } 
    } 

    /**
     * Validates the contents of the Item object.
     *
     * Returns a string error message if the item cannot be validated.
     *
     * @return string
     */
    function validate()
    {
        for ($i = 0; $i < count($this->objary['items']); $i++) {
            $item = &$this->objary['items'][$i];
            $err = $item->validate();
            
            if (strlen($err)) {
                return($err);
            }
        }
        
        return(null); 
    } 
} 

/**
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_PE_MerchantInfo extends NIC_XML
{
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function NIC_PE_MerchantInfo()
    {
        $this->NIC_XML();
        $this->name = 'merchant-info';
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getMerchantId()
    {
        return($this->attribs['merchant-id']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getMerchantName()
    {
        return($this->attribs['merchant-name']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getContactName()
    {
        return($this->attribs['contact-name']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getPhoneNumber()
    {
        return($this->attribs['phone-number']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getAltPhoneNumber()
    {
        return($this->attribs['alt-phone-number']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getEmailAddress()
    {
        return($this->attribs['email-address']);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function RefundsAllowed()
    {
        if ($this->attribs['is-refunds-allowed'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function Enabled()
    {
        if ($this->attribs['is-enabled'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function Internal()
    {
        if ($this->attribs['is-internal'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getServices()
    {
        return($this->objs['services']->getServices());
    } 
} 

/**
 * Primary container for an online transaction.
 *
 * This class contains information about an order. Orders are essentially a 
 * request for service.
 *
 * @package NIC_PE
 */
class NIC_PE_Order extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty order.
     *
     * @param string $service
     * @return NIC_PE_Order
     */
    function NIC_PE_Order($service = '')
    {
        $this->NIC_XML();
        $this->name = 'order';
        $this->attribs['service-code'] = $service;
        $this->objs['comments']        = new NIC_PE_Comments();
        $this->objs['attributes']      = new NIC_PE_Attributes();
        $this->objs['items']           = new NIC_PE_Items();
        $this->attribs['origin']       = 'WEB'; // default to web
        $this->attribs['date']         = NIC_PE_Date();
    } 

    // Attribute functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getAttribute($name)
    {
        $this->objs['attributes']->getAttribute($name);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberAttributes()
    {
        return($this->objs['attributes']->numberAttributes());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function setAttribute($attrib, $value = '')
    {
        $this->objs['attributes']->setAttribute($attrib, $value);
    } 
    // end Attribute functions
    
    // Comment functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function addComment($comment)
    {
        $this->objs['comments']->addComment($comment);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function comments()
    {
        return($this->objs['comments']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberComments()
    {
        return($this->objs['comments']->numberComments());
    } 
    // end Comment functions
    
    // Item functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function addItem($item)
    {
        $this->objs['items']->addItem($item);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function clearItems()
    {
        $this->objs['items']->clearItems();
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItem($index)
    {
        return($this->objs['items']->getItem($index));
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItemSubtotal($type)
    {
        return($this->objs['items']->getItemSubtotal($type));
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getItemTotal()
    {
        return($this->objs['items']->getItemTotal());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function items()
    {
        return($this->objs['items']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberItems()
    {
        return($this->objs['items']->numberItems());
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function removeItem($index)
    {
        $this->objs['items']->removeItem($index);
    } 
    // end Item functions
    
    /**
     * Returns the current balance associated with the order.
     *
     * The balance is the difference between the total order items amount and 
     * the settled financial transactions. A positive balance indicates that 
     * additional funds need collected. A negative balance indicates the 
     * customer overpaid for the order.
     *
     * @return float
     */
    function getBalance()
    {
        return($this->getItemTotal() - $this->getNetPaymentAmount());
    } 

    /**
     * Retrieves the cancellation date/time stamp.
     *
     * Longer description...
     *
     * @return string
     */
    function getCancelDate()
    {
        return($this->attribs['cancel-date']);
    } 

    /**
     * Retrieves the date/time stamp when the order was closed.
     *
     * Longer description...
     *
     * @return string
     */
    function getClosedDate()
    {
        return($this->attribs['closed-date']);
    } 

    /**
     * Retrieves the NIC_PE_Customer object associated with this order.
     *
     * Longer description...
     *
     * @return NIC_PE_Customer
     */
    function getCustomer()
    {
        return($this->objs['customer']);
    } 

    /**
     * Retrieves the date/time stamp this order was created.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**
     * Returns the eventual balance associated with the order as if all pending 
     * financial transactions were settled.
     *
     * The eventual balance is the difference between the total order items 
     * amount and the settled and unsettled financial transactions.
     *
     * @return string
     */
    function getEventualBalance()
    {
        return($this->getBalance() - $this->getUnsettledPaymentAmount());
    } 

    /**
     * Returns the order's unique identifier.
     *
     * Longer description...
     *
     * @return string
     */
    function getId()
    {
        return($this->attribs['id']);
    } 

    /**
     * Returns the order's optional local reference identifier.
     *
     * Longer description...
     *
     * @return string
     */
    function getLocalReference()
    {
        return($this->attribs['local-ref']);
    } 

    /**
     * Calculates the total amount paid specific to this order.
     *
     * This is accomplished by totalling the net effect of the settled 
     * financial transactions associated with the order.
     *
     * @return float
     */
    function getNetPaymentAmount()
    {
        $total = 0.0;
        
        for ($i = 0; $i < count($this->objary['ftrans']); $i++) {
            $trans = &$this->objary['ftrans'][$i];
            $total += $trans->getNetAmount;
        } 
        
        return($total);
    } 

    /**
     * Returns the origin of this order.
     *
     * One of:
     * <ul>
     *   <li>WEB</li>
     *   <li>IVR</li>
     *   <li>PDA</li>
     *   <li>OTC (over the counter)</li>
     * </ul>
     *
     * @return string
     */
    function getOrigin()
    {
        return($this->attribs['origin']);
    } 

    /**
     * Returns the payment implement for the order.
     *
     * The payment implement can be either an <b>NIC_PE_BankAccount</b> or an <b>NIC_PE_CreditCard</b>.
     *
     * @return NIC_PE_BankAccount
     */
    function getPaymentImplement()
    {
        return($this->objs['payment-implement']);
    } 

    /**
     * Retrieves the service code for the order.
     *
     * Longer description...
     *
     * @return string
     */
    function getServiceCode()
    {
        return($this->attribs['service-code']);
    } 

    /**
     * Calculates the total amount paid, but currently outstanding, specific to 
     * this order.
     *
     * This is accomplished by totalling hte net effect of the unsettled 
     * financial transactions associated with the order.
     *
     * @return float
     */
    function getUnsettledPaymentAmount()
    {
        // TODO::maybe this should do something?
    } 

    /**
     * Returns true if the order has been cancelled.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isCancelled()
    {
        if (isset($this->attribs['cancel-date'])) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Returns true if the order has been closed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isClosed()
    {
        if (isset($this->attribs['closed-date'])) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Returns true if the order is a test.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isTest()
    {
        if ($this->attribs['is-test'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Sets the customer information for the order.
     *
     * Longer description...
     *
     * @param NIC_PE_Customer $customer
     * @return void
     */
    function setCustomer($customer)
    {
        $this->objs['customer'] = $customer;
    } 

    /**
     * Sets the optional local reference identifier for the order.
     *
     * Longer description...
     *
     * @param string $ref
     * @return void
     */
    function setLocalReference($ref)
    {
        $this->attribs['local-ref'] = $ref;
    } 

    /**
     * Sets the origin of the order.
     *
     * $origin should be one of: 
     * <ul>
     *   <li>WEB</li>
     *   <li>IVR</li>
     *   <li>PDA</li>
     *   <li>OTC (over the counter)</li>
     * </ul>
     *
     * @param string $origin
     * @return void
     */
    function setOrigin($origin)
    {
        $this->attribs['origin'] = $origin;
    } 

    /**
     * Sets the payment implement for the order.
     *
     * $pi should be either an <b>NIC_PE_BankAccount</b> or an <b>NIC_PE_CreditCard object</b>.
     *
     * @param string $pi
     * @return void
     */
    function setPaymentImplement($pi)
    {
        $this->objs['payment-implement'] = $pi;
    } 

    /**
     * Sets the service code for the order.
     *
     * Longer description...
     *
     * @param string $service
     * @return void
     */
    function setServiceCode($service)
    {
        $this->attribs['service-code'] = $service;
    } 

    /**
     * Returns an array of NIC_PE_Transaction's for the order.
     *
     * Longer description...
     *
     * @return array
     */
    function transactions()
    {
        return($this->objary['ftrans']);
    } 

    /**
     * Validates the contents of the order object.
     *
     * Returns an error message string if the validate fails.
     *
     * @return string
     */
    function validate()
    {
        if (empty($this->attribs['service-code'])) {
            return('Missing service code');
        }
        
        if (empty($this->objs['payment-implement'])) {
            return('Missing payment implement');
        }
        
        if (strtolower(get_class($this->objs['payment-implement'])) != 'nic_pe_creditcard' && strtolower(get_class($this->objs['payment-implement'])) != 'nic_pe_bankaccount') {
            return('Invalid payment implement ' . get_class($this->objs['payment-implement']));
        }
        
        if (empty($this->attribs['origin'])) {
            return('Missing origin code');
        }
        
        if (!$this->objs['items']->numberItems()) {
            return('Order contains no items');
        }
        
        $err = $this->objs['items']->validate();
        
        if (strlen($err)) {
            return($err);
        }
        
        $err = $this->objs['comments']->validate();
        
        if (strlen($err)) {
            return($err);
        }
        
        return(null);
    } 
} 

/**
 * Abbreviated view of an order.
 *
 * This class contains an abbreviated view of an order.
 *
 * @package NIC_PE
 */
class NIC_PE_OrderHeader extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty order header.
     *
     * @return NIC_PE_OrderHeader
     */
    function NIC_PE_OrderHeader()
    {
        $this->NIC_XML();
        $this->name = 'order-header';
    } 

    /**
     * Retrieves the amount of the order.
     *
     * Longer description...
     *
     * @return float
     */
    function getAmount()
    {
        return($this->attribs['amount']);
    } 

    /**
     * Retrieves the date/time stamp the order was cancelled.
     *
     * Longer description...
     *
     * @return string
     */
    function getCancelDate()
    {
        return($this->attribs['cancel-date']);
    } 

    /**
     * Retrieves the date/time stamp the order was closed.
     *
     * Longer description...
     *
     * @return string
     */
    function getClosedDate()
    {
        return($this->attribs['closed-date']);
    } 

    /**
     * Retrieves the customer's name.
     *
     * Longer description...
     *
     * @return string
     */
    function getCustomerName()
    {
        return($this->attribs['customer-name']);
    } 

    /**
     * Retrieves the customer's username.
     *
     * Longer description...
     *
     * @return string
     */
    function getCustomerUsername()
    {
        return($this->attribs['customer-username']);
    } 

    /**
     * Retrieves the date/time the order was created.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**
     * Retrieves the unique order identifier.
     *
     * Longer description...
     *
     * @return string
     */
    function getId()
    {
        return($this->attribs['id']);
    } 

    /**
     * Retrieves the optional local reference identifier for this order.
     *
     * Longer description...
     *
     * @return string
     */
    function getLocalReference()
    {
        return($this->attribs['local-ref']);
    } 

    /**
     * Retrieves the origin of the order.
     *
     * The origin will be one of:
     * <ul>
     *   <li>WEB</li>
     *   <li>IVR</li>
     *   <li>PDA</li>
     *   <li>OTC (over the counter)</li>
     * </ul>
     *
     * @return string
     */
    function getOrigin()
    {
        return($this->attribs['origin']);
    } 

    /**
     * Retrieves the service code of the order.
     *
     * Longer description...
     *
     * @return string
     */
    function getServiceCode()
    {
        return($this->attribs['service-code']);
    } 

    /**
     * Returns true if the order has been cancelled.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isCancelled()
    {
        if (strlen($this->attribs['cancel-date'])) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the order has been closed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isClosed()
    {
        if (strlen($this->attribs['closed-date'])) {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if the order is a test.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isTest()
    {
        if ($this->attribs['is-test'] == 'YES') {
            return(true);
        } else {
            return(false);
        }
    } 
} 

/**
 * Query parameters for selecting orders.
 *
 * This class contains query parameters for selecting orders.
 *
 * @package NIC_PE
 */
class NIC_PE_OrderQuery extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty order query.
     *
     * @return NIC_PE_OrderQuery
     */
    function NIC_PE_OrderQuery()
    {
        $this->NIC_XML();
        $this->name = 'order-query';
        $this->vars['include-test'] = 'false';
    } 

    /**
     * Returns the customer's username.
     *
     * Longer description...
     *
     * @return string
     */
    function getCustomerUsername()
    {
        return($this->vars['username']);
    } 

    /**
     * Returns the ending date of the date iterval.
     *
     * Longer description...
     *
     * @return string
     */
    function getEndOrderDate()
    {
        return($this->vars['high-order-date']);
    } 

    /**
     * Retrieves the high order id for the interval of order ids.
     *
     * Longer description...
     *
     * @return string
     */
    function getHighOrderId()
    {
        return($this->vars['high-order-id']);
    } 

    /**
     * Retrieves the local reference id.
     *
     * Longer description...
     *
     * @return string
     */
    function getLocalReference()
    {
        return($this->vars['local-ref']);
    } 

    /**
     * Retrieves the low order id for the interval of order ids.
     *
     * Longer description...
     *
     * @param 
     * @return string
     */
    function getLowOrderId()
    {
        return($this->vars['low-order-id']);
    } 

    /**
     * Retrieves origin of the query parameters.
     *
     * Origin will be one of:
     * <ul>
     *   <li>WEB</li>
     *   <li>IVR</li>
     *   <li>PDA</li>
     *   <li>OTC (over the counter)</li>
     * </ul>
     *
     * @return string
     */
    function getOrigin()
    {
        return($this->vars['origin']);
    } 

    /**
     * Retrieves service code of the query.
     *
     * Longer description...
     *
     * @return string
     */
    function getServiceCode()
    {
        return($this->vars['service-code']);
    } 

    /**
     * Retrieves the beginning date of the interval.
     *
     * Longer description...
     *
     * @return string
     */
    function getStartOrderDate()
    {
        return($this->vars['low-order-date']);
    } 

    /**
     * Returns true if cancelled orders are to be included in the query.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeCancelled()
    {
        if ($this->vars['include-cancelled'] == 'true') {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if closed orders are to be included in the query.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeClosed()
    {
        if ($this->vars['include-closed'] == 'true') {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if open orders are to be included in the query.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeOpen()
    {
        if ($this->vars['include-open'] == 'true') {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Returns true if test orders are to be included in the query.
     *
     * Longer description...
     *
     * @return boolean
     */
    function includeTest()
    {
        if ($this->vars['include-test'] == 'true') {
            return(true);
        } else {
            return(false);
        }
    } 

    /**
     * Sets the customer's username to query on.
     *
     * Longer description...
     *
     * @param string $username
     * @return void
     */
    function setCustomerUsername($username)
    {
        $this->vars['username'] = $username;
    } 

    /**
     * Sets the inclusive end of the order date interval for selecting orders.
     *
     * Longer description...
     *
     * @param string $date
     * @return void
     */
    function setEndOrderDate($date)
    {
        $this->vars['high-order-date'] = $date;
    } 

    /**
     * Sets the high order id for the order id interval for selecting orders.
     *
     * Longer description...
     *
     * @param string $high
     * @return void
     */
    function setHighOrderId($high)
    {
        $this->vars['high-order-id'] = $high;
    } 

    /**
     * If $inc is true, cancelled orders will be included in the query.
     *
     * Longer description...
     *
     * @param boolean $inc
     * @return void
     */
    function setIncludeCancelled($inc)
    {
        if ($inc) {
            $this->vars['include-cancelled'] = 'true';
        } else {
            $this->vars['include-cancelled'] = 'false';
        }
    } 

    /**
     * If $inc is true, closed orders will be included in the query.
     *
     * Longer description...
     *
     * @param boolean $inc
     * @return void
     */
    function setIncludeClosed($inc)
    {
        if ($inc) {
            $this->vars['include-closed'] = 'true';
        } else {
            $this->vars['include-closed'] = 'false';
        }
    } 

    /**
     * If $inc is true, open orders will be included in the query.
     *
     * Longer description...
     *
     * @param boolean $inc
     * @return void
     */
    function setIncludeOpen($inc)
    {
        if ($inc) {
            $this->vars['include-open'] = 'true';
        } else {
            $this->vars['include-open'] = 'false';
        }
    } 

    /**
     * If $inc is true, test orders will be included in the query.
     *
     * Longer description...
     *
     * @param bool $inc
     * @return void
     */
    function setIncludeTest($inc)
    {
        if ($inc) {
            $this->vars['include-test'] = 'true';
        } else {
            $this->vars['include-test'] = 'false';
        }
    } 

    /**
     * Sets the local reference id.
     *
     * Longer description...
     *
     * @param string $ref
     * @return void
     */
    function setLocalReference($ref)
    {
        $this->vars['local-ref'] = $ref;
    } 

    /**
     * Sets the low order id for the order id interval for selecting orders.
     *
     * Longer description...
     *
     * @param string $low
     * @return void
     */
    function setLowOrderId($low)
    {
        $this->vars['low-order-id'] = $low;
    } 

    /**
     * Sets the origin for selecting orders by origin.
     *
     * $origin should be one of:<br>
     * <ul>
     *   <li>WEB</li>
     *   <li>IVR</li>
     *   <li>PDA</li>
     *   <li>OTC (over the counter)</li>
     * </ul>
     *
     * @param string $origin
     * @return void
     */
    function setOrigin($origin)
    {
        $this->vars['origin'] = $origin;
    } 

    /**
     * Sets the service code for selecting orders by service code.
     *
     * Longer description...
     *
     * @param string $service
     * @return void
     */
    function setServiceCode($service)
    {
        $this->vars['service-code'] = $service;
    } 

    /**
     * Sets the beginning order date for the order date interval for selecting 
     * orders by order date.
     *
     * Longer description...
     *
     * @param string $startdate
     * @return void
     */
    function setStartOrderDate($startdate)
    {
        $this->vars['low-order-date'] = $startdate;
    } 
} 

/**
 * Computes a price based on a set of PriceTableRange's
 *
 * This class contains a set of PriceTableRange's which is used to compute the 
 * final amount of a table based NIC_PE_Item.
 *
 * @package NIC_PE
 */
class NIC_PE_PriceTable extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty price table.
     *
     * @return NIC_PE_PriceTable
     */
    function NIC_PE_PriceTable()
    {
        $this->NIC_XML();
        $this->name = 'price-table';
        $this->objary['ranges'] = array();
    } 

    /**
     * Adds the range to the price table.
     *
     * if $low is an object NIC_PE_PriceTableRange then it is added to the 
     * price table, otherwise a new NIC_PE_PriceTableRange object is constructed 
     * from the $low, $high, and $price values and adds it to the price table.
     *
     * @param string $low
     * @param string $high optional
     * @param string $price optional
     * @return void
     */
    function addRange($low, $high = '', $price = '')
    {
        if (is_object($low)) {
            if ($this->overlapFound($low->vars['low-value'], $low->vars['high-value'])) {
                return('New range overlaps an existing range');
            }
            
            array_push($this->objary['ranges'], $low);
            
            return(null);
        } 
        
        if ($low >= $high) {
            return("Low value ($low) must be less than high value ($high) for ranges");
        }
        
        if ($this->overlapFound($low, $high)) {
            return('New range overlaps an existing range');
        }
        
        $range = new NIC_PE_PriceTableRange($low, $high, $price);
        array_push($this->objary['ranges'], $range);
        
        return(null);
    } 

    /**
     * Finds the range that contains $amount and returns the corresponding 
     * price for that range.
     *
     * Longer description...
     *
     * @param float $amount
     * @return float
     */
    function lookupPrice($amount)
    {
        for ($i = 0; $i < count($this->objary['ranges']); $i++) {
            $range = &$this->objary['ranges'][$i];
            
            if ($range->includes($amount)) {
                return($range->getPrice($amount));
            }
        } 
        
        return(null);
    } 

    /**
     * Returns true if the range described by [$low, $high] overlaps one or 
     * more of the existing ranges in the price table.
     *
     * Longer description...
     *
     * @param string $low
     * @param string $high
     * @return boolean
     */
    function overlapFound($low, $high)
    {
        for ($i = 0; $i < count($this->objary['ranges']); $i++) {
            $range = &$this->objary['ranges'][$i];
            
            if ($range->includes($low) || $range->includes($high)) {
                return(true);
            }
        } 
        
        return(false);
    } 
} 

/**
 * Contains a low and high value for the range and a corresponding price.
 *
 * This class associates a price with a range of values between a low and high 
 * value inclusive.
 *
 * @package NIC_PE
 */
class NIC_PE_PriceTableRange extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new price table range specified by $low, $high, and $price.
     *
     * @param string $low defaults to zero '0'
     * @param string $high defaults to zero '0'
     * @param string $price defaults to zero '0'
     * @return NIC_PE_PriceTableRange
     */
    function NIC_PE_PriceTableRange($low = 0, $high = 0, $price = 0)
    {
        $this->NIC_XML();
        $this->name = 'range';
        $this->attribs['low-value'] = $low;
        $this->attribs['high-value'] = $high;
        $this->attribs['price'] = $price;
    } 

    /**
     * Retrieves the low value of the range.
     *
     * Longer description...
     *
     * @return float
     */
    function getLowValue()
    {
        return($this->attribs['low-value']);
    } 

    /**
     * Retrieves the high value of the range.
     *
     * Longer description...
     *
     * @return float
     */
    function getHighValue()
    {
        return($this->attribs['high-value']);
    } 

    /**
     * Retrieves the price associated with the range.
     *
     * Longer description...
     *
     * @return float
     */
    function getPrice()
    {
        return($this->attribs['price']);
    } 

    /**
     * Returns true if $value is between the low and high values for the range.
     *
     * Longer description...
     *
     * @param string $value
     * @return boolean
     */
    function includes($value)
    {
        if ($value >= $this->attribs['low-value'] && $value <= $this->attribs['high-value']) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Sets the low value for the range.
     *
     * Longer description...
     *
     * @param string $low
     * @return void
     */
    function setLowValue($low)
    {
        $this->attribs['low-value'] = $low;
    } 

    /**
     * Sets the high value for the range.
     *
     * Longer description...
     *
     * @param string $high
     * @return void
     */
    function setHighValue($high)
    {
        $this->attribs['high-value'] = $high;
    } 

    /**
     * Sets the price for the range.
     *
     * Longer description...
     *
     * @param $price
     * @return void
     */
    function setPrice($price)
    {
        $this->attribs['price'] = $price;
    } 
} 

/**TODO
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_PE_ServiceInfo extends NIC_XML
{
    /**TODO
     * Constructor
     *
     * Longer description...
     *
     * @return NIC_PE_ServiceInfo
     */
    function NIC_PE_ServiceInfo()
    {
        $this->NIC_XML();
        $this->name = 'service-info';
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getServiceCode()
    {
        return($this->attribs['service-code']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getServiceDesc()
    {
        return($this->attribs['service-desc']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getServiceCategory()
    {
        return($this->attribs['service-category']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return boolean
     */
    function Enabled()
    {
        if ($this->attribs['is-enabled'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return boolean
     */
    function AutoComplete()
    {
        if ($this->attribs['is-auto-complete'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return boolean
     */
    function AutoInvoice()
    {
        if ($this->attribs['is-auto-invoice'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return boolean
     */
    function Exempt()
    {
        if ($this->attribs['is-exempt'] == 'YES') {
            return(true);
        }
        
        return(false);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getProcessorName()
    {
        return($this->attribs['processor-name']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getProcessorId()
    {
        return($this->attribs['processor-id']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getProcessorClass()
    {
        return($this->attribs['processor-class']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function getPaymentImplementType()
    {
        return($this->attribs['payment-implement-type']);
    } 
} 

/**TODO
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_PE_Services extends NIC_XML
{
    /**TODO
     * Constructor
     *
     * Longer description...
     *
     * @return NIC_PE_Services
     */
    function NIC_PE_Services()
    {
        $this->NIC_XML();
        $this->name = 'services';
        $this->objary['service-infos'] = array();
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @return 
     */
    function getServices()
    {
        return($this->objary['service-infos']);
    } 
} 

/**
 * Represents transactions with back end payment processors.
 *
 * This class contains information about a financial transaction. Financial 
 * transactions are associated with orders and come in six types:<br>
 * <ul>
 *   <li>authorization</li>
 *   <li>void</li>
 *   <li>payment</li>
 *   <li>refund</li>
 *   <li>return</li>
 *   <li>reverse return</li>
 * </ul>
 *
 * @package NIC_PE
 */
class NIC_PE_Transaction extends NIC_XML
{
    /**
     * Constructor
     *
     * Instantiate a new empty transaction.
     *
     * @return NIC_PE_Transaction
     */
    function NIC_PE_Transaction()
    {
        $this->NIC_XML();
        $this->name = 'ftran';
        $this->objs['comments'] = new NIC_PE_Comments();
    } 
    
    // Comment functions
    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function addComment($comment)
    {
        $this->objs['comments']->addComment($comment);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function comments()
    {
        return($this->objs['comments']);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function numberComments()
    {
        return($this->objs['comments']->numberComments());
    } 
    // end Comment functions
    
    /**
     * Returns the net amount for the transaction.
     *
     * The amount will be negative refunds.
     *
     * @return float
     */
    function calculateNetAmount()
    {
        if ($this->attribs['type'] == NIC_PE_TransType_Authorization) {
            return(0.00);
        }
        
        if ($this->attribs['type'] == NIC_PE_TransType_Payment || $this->attribs['type'] == NIC_PE_TransType_ReverseReturn) {
            return($this->vars['amount']);
        } else {
            return(- $this->vars['amount']);
        }
    } 

    /**
     * Retrieves the admin username that initiated this financial transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getAdminUsername()
    {
        return($this->vars['admin-username']);
    } 

    /**
     * Retrieves the amount of the transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getAmount()
    {
        return($this->vars['amount']);
    } 

    /**
     * Retrieves the authorization code from the back end payment processor.
     *
     * Longer description...
     *
     * @return string
     */
    function getAuthCode()
    {
        return($this->vars['auth-code']);
    } 

    /**
     * Retrieves the date/time stamp of the transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getDate()
    {
        return($this->attribs['date']);
    } 

    /**
     * Retrieves the date/time stamp the transaction was disbursed.
     *
     * Longer description...
     *
     * @return string
     */
    function getDisbursementDate()
    {
        return($this->vars['disbursement-date']);
    } 

    /**
     * Retrieves failure code associated with a failed transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getFailureCode()
    {
        return($this->vars['failure-code']);
    } 

    /**
     * Retrieves failure message associated with a failed transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getFailureMessage()
    {
        return($this->vars['failure-message']);
    } 

    /**
     * Returns the net effect of this financial transaction on the order balance.
     *
     * Longer description...
     *
     * @return float
     */
    function getNetAmount()
    {
        if ($this->isFailure() || $this->isSettled()) {
            return(0.00);
        }
        
        return($this->calculateNetAmount());
    } 

    /**
     * Returns the order id this transaction is associated with.
     *
     * Longer description...
     *
     * @return string
     */
    function getOrderId()
    {
        return($this->attribs['order-id']);
    } 

    /**
     * Returns the payment processor specific reference identifier for the transaction.
     *
     * Longer description...
     *
     * @return string
     */
    function getReferenceId()
    {
        return($this->vars['reference-id']);
    } 

    /**
     * Returns the date/time stamp the financial transaction was settled.
     *
     * Longer description...
     *
     * @return string
     */
    function getSettlementDate()
    {
        return($this->vars['settlement-date']);
    } 

    /**
     * Returns the type of this financial transaction
     *
     * Where financial transaction type is one of:<br>
     * <ul>
     *   <li>NIC_PE_TransType_Authorization</li>
     *   <li>Payment</li>
     *   <li>Refund</li>
     *   <li>Return</li>
     *   <li>Void</li>
     *   <li>ReverseReturn</li>
     * </ul>
     *
     * @return string
     */
    function getType()
    {
        return($this->attribs['type']);
    } 

    /**
     * Returns the net effect of the unsettled portion of the financial transaction.
     *
     * Longer description...
     *
     * @return float
     */
    function getUnsettledAmount()
    {
        if ($this->isFailure() || $this->isSettled()) {
            return(0.00);
        }
        
        return($this->calculateNetAmount());
    } 

    /**
     * Returns true if the transaction has been disbursed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isDisbursed()
    {
        if (strlen($this->vars['disbursement-date'])) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Returns true if the transaction failed.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isFailure()
    {
        if ((isset($this->attribs['is-failure'])) && ($this->attribs['is-failure'] == 'YES')) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Returns true if the transaction has been settled.
     *
     * Longer description...
     *
     * @return boolean
     */
    function isSettled()
    {
        if (strlen($this->vars['settlement-date'])) {
            return(true);
        }
        
        return(false);
    } 

    /**
     * Sets the order id the transaction belongs to.
     *
     * Longer description...
     *
     * @param string $orderid
     * @return void
     */
    function setOrderId($orderid)
    {
        $this->attribs['order-id'] = $orderid;
    } 
} 


/**
 * NIC_PE_Validator class
 *
 * Common methods for validating strings, credit cards,
 * zip codes, etc...
 *
 * @package NIC_PE
 * @author Scott Morrison <scottm@ink.org>
 */
class NIC_PE_Validator
{
    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function NIC_PE_Validator()
    {
    }

    /**
     * Checks if a string contains any white space.
     *
     * @param string $str the string to check
     * @return boolean true if string has white space, false otherwise
     */
    function hasSpace($str)
    {
        if (ereg("[[:space:]]+", $str)) {
            return(true);
        }

        return(false);
    }

    /**
     * Checks minumum and maximum length constraints.
     *
     * @param string $str the string to check
     * @param integer $minlen minimum length allowed
     * @param integer $maxlen maximum length allowed
     * @return boolean
     */
    function checkLength($str, $minlen = 0, $maxlen = 0)
    {
        $str_length = strlen($str);

        if (($minlen != 0 && $str_length < $minlen) || ($maxlen != 0 && $str_length > $maxlen)) {
            return(false);
        }

        return(true);
    }

    /**
     * Checks for clean text
     *
     * Checks if a string contains only a subset of alphanumeric characters
     * allowed in the Western alphabets. Useful for validation of names.
     *
     * @param string $str the string to check
     * @return boolean
     */
    function isCleanText($str)
    {
        if (eregi("^[a-zA-Z0-9\-[:space:]ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþ`´']+$", $str)) {
            return(true);
        }

        return(false);
    }

    /**
     * checks that the input is an alpha string.
     *
     * Returns true if string is all alpha characters.
     *
     * @param string $str the string to check
     * @return boolean
     */
    function isAlpha($str)
    {
        if (ereg("^[[:alpha:]]+$", $str)) {
            return(true);
        }

        return(false);
    }

    /**
     * If the string is alphanumeric
     *
     * Returns true if string is ALL alphanumeric characters
     *
     * @param string $str the string to check
     * @return boolean
     */
    function isAlphaNumeric($str)
    {
        if (ereg("^[[:alnum:]]+$", $str)) {
            return(true);
        }

        return(false);
    }

    /**
     * If a string is all digits
     *
     * Returns true if string contains only numbers.
     *
     * @param string $str the string to check
     * @return boolean
     */
    function isDigits($str)
    {
        if (ereg("^[[:digit:]]+$", $str)) {
            return(true);
        }

        return(false);
    }

    /**
     * Strip spaces from a string
     *
     * Strips whitespace (tab or space) from a string.
     *
     * @param string $str the string to strip.
     * @return string the resultant string with no spaces.
     */
    function stripAllWhiteSpace($str)
    {
        return(ereg_replace("[[:space:]]+", '', $str));
    }

    /**
     * Strip leading/trailing white space from a string
     *
     * @param string $str the string to strip.
     * @return string the resultant string.
     */
    function stripWhiteSpace($str)
    {
        return(trim($str));
    }

    /**
     * Strip digits from a string
     *
     * @param string $str the string to strip.
     * @return string the resultant string.
     */
    function stripDigits($str)
    {
        return(eregi_replace("[[:digit:]]+", '', $str));
    }

    /**
     * Strip alpha characters from a string
     *
     * @param string $str the string to strip.
     * @return string the resultant string.
     */
    function stripAlpha($str)
    {
        return(eregi_replace("[[:alpha:]]+", '', $str));
    }

    /**
     * Validate a phone number
     *
     * Strips (,),-,+ and spaces from number prior to checking
     * Less than 10 digits = fail (require the area code)
     * More than 13 digits = fail
     * Anything other than numbers after the stripping = fail
     *
     * @param string $phone the string to check
     * @return boolean
     */
    function isValidPhone($phone)
    {
        if (empty($phone)) {
            return(false);
        }

        $num = $phone;
        $num = $this->stripAllWhiteSpace($num);
        $num = eregi_replace("(\(|\)|\-|\+)", '', $num);

        if (!$this->isDigits($num)) {
            return(false);
        }

        // 000 000 000 0000
        //     AC  PRE SUFX = min 10 digits
        // CC  AC  PRE SUFX = max 13 digits
        if ((strlen($num)) < 10) {
            return(false);
        }

        if ((strlen($num)) > 13) {
            return(false);
        }

        return(true);
    }

    /**
     * Validates postal code
     *
     * Does not work on internation zip codes
     * 00000 or 00000-0000
     *
     * @param string $zip
     * @return boolean
     */
    function isValidZip($zip)
    {
        if (preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
            return(true);
        }

        return(false);
    }

    /**
     * put commas in a string
     *
     * Returns number with commas and decimals with defined precision defaults to
     * 2 decimal places. Returns false if the $number is not a number.
     *
     * @param string $number
     * @return string
     */
    function commify($number, $precision=2)
    {
        if (is_numeric($number)) {
            return(number_format($number, $precision, '.', ','));
        }

        return(false);
    }

    /**
     * Returns the credit card type based off the number
     *
     * Returns false if card type is unknown, otherwise
     * one of the following:
     * - MasterCard
     * - Visa
     * - AmEx
     * - Discover
     * - Diners
     * - JCB
     * - enRoute
     *
     * @param string $cardnum the card number to check
     * @return string card type, false if unknown
     */
    function getCardType($cardnum)
    {
        // Remove non-numeric characters from $cardnum
        $cardnum = ereg_replace("[^[:digit:]]", '', $cardnum);

        // Return card type based on prefix and length of card number
        if (ereg("^5[1-5].{14}$", $cardnum)) {
            return('MasterCard');
        } else if (ereg("^4(.{12}|.{15})$", $cardnum)) {
            return('Visa');
        } else if (ereg("^3[47].{13}$", $cardnum)) {
            return('AmEx');
        } else if (ereg("^6011.{12}$", $cardnum)) {
            return('Discover');
        } else if (ereg("^3(0[0-5].{11}|[68].{12})$", $cardnum)) {
            return('Diners');
        } else if (ereg("^(3.{15}|(2131|1800).{11})$", $cardnum)) {
            return('JCB');
        } else if (ereg("^2(014|149).{11})$", $cardnum)) {
            return('enRoute');
        } else {
            return(false);
        }
    }

    /**
     * Performs the Luhn/mod10 check on the card number
     *
     * Validates that the card number is "well formed".
     *
     * @param string $cardnum the card number to check
     * @return boolean
     */
    function isValidCard($cardnum)
    {
        // Remove non-numeric characters from $cardnum
        $cardnum = ereg_replace("[^[:digit:]]", '', $cardnum);

        // The formula works right to left, so reverse the number.
        $cardnum = strrev($cardnum);

        // VALIDATION ALGORITHM
        // Loop through the number one digit at a time
        // Double the value of every second digit (starting from the right)
        // Concatenate the new values with the unaffected digits
        for ($ndx = 0; $ndx < strlen($cardnum); ++$ndx) {
            $digits .= ($ndx % 2) ? $cardnum[$ndx] * 2 : $cardnum[$ndx];
        }

        // Add all of the single digits together
        for ($ndx = 0; $ndx < strlen($digits); ++$ndx) {
            $sum += $digits[$ndx];
        }

        // Valid card numbers will be transformed into a multiple of 10
        return ($sum % 10) ? false : true;
    }

    /**
     * Checks that the email address syntax is a valid email format.
     *
     * @param string $email the string to check
     * @return boolean
     */
    function isValidEmail($email)
    {
        // Shift the address to lowercase to simplify checking
        $email = strtolower($email);

        // Rough email address validation using POSIX-style regular expressions
        if (eregi("^[a-z0-9\._\-]+@[a-z0-9\._\-]{2,}\.[a-z]{2,4}$", $email)) {
            return(true);
        }

        return(false);
    }

    /**
     * Checks that the domain has an MX record and can receive email
     *
     * NOTE: This method uses the PHP function getmxrr which is not
     *       implemented on Windows platforms.
     *
     * @param string $email the string to check
     * @param boolean $return_mxhosts if true returns mxhosts as array
     * @return mixed
     */
    function verifyEmailHost($email, $return_mxhosts=false)
    {
        // check that the email format is valid
        if (!$this->isValidEmail($email)) {
            return(false);
        }

        // get the domain from the email address
        $pieces = explode('@', $email, 2);
        $domain = $pieces[1];

        $mxhosts = array();

        if ((gethostbyname($domain) == $domain) && (checkdnsrr($domain, 'MX') == false) || (getmxrr($domain, $mxhosts) == false)) {
            return(false);
        }

        // returns array of mxhost(s), if requested
        if ($return_mxhosts == true) {
            return($mxhosts);
        }

        return(true);
    }

    /**
     * Attempts to query the email system to verify that the user exists.
     *
     * !!! Warnings !!!
     * This check can take a considerable amount of time !
     * And the ability to do a VRFY may be disabled on the remote server,
     * if the sys admin has it secured!
     * Also uses getmxrr which is not implemented on the Windows platform!
     *
     * SMTP Reference: http://www.ietf.org/rfc/rfc0821.txt?number=821
     *
     * @param string $email
     * @return boolean
     */
    function verifyEmailExists($email)
    {
        $mxhosts = $this->verifyEmailHost($email, true);

        if (is_array($mxhosts) == false) {
            return(false);
        }

        $found = false;
        $localhost = $this->_verifyEmailLocalhost();
        $mxsize = sizeof($mxhosts);

        for ($i = 0; $i < $mxsize; $i++) {
            $socket = @fsockopen($mxhosts[$i], 25);

            if (!$socket) {
                continue;
            }

            $foo = fgets($socket, 4096);

            # 220 <domain> Service ready
            if(!preg_match("/^220/i", $foo)) {
                $this->_verifyEmailCloseSocket($socket);
                continue;
            }

            fputs($socket, 'HELO ' . $localhost . "\r\n");
            $foo = fgets($socket);

            while (preg_match("/^220/i", $foo)) {
                $foo = fgets($socket, 4096);
            }

            fputs($socket, 'VRFY ' . $email . "\r\n");
            $foo = fgets($socket, 4096);

            # 250 Requested mail action okay, completed
            if (preg_match("/^250/i", $foo)) {
                $found = true;
                $this->_verifyEmailCloseSocket($socket);
                break;
            }

            # VRFY Command is disabled on host
            if (preg_match("/^502|^252/i", $foo)) {
                $found = false;
                $this->_verifyEmailCloseSocket($socket);
                break;
            }

            # 550 Requested action not taken: mailbox unavailable
            # [E.g., mailbox not found, no access]
            if(preg_match("/^550/i", $foo)) {
                $this->_verifyEmailCloseSocket($socket);
                continue;
            }

            fputs($socket, 'MAIL FROM: <' . $email . ">\r\n");
            $foo = fgets($socket, 4096);

            fputs($socket, 'RCPT TO: <' . $email . ">\r\n");
            $foo = fgets($socket, 4096);

            # 250 Requested mail action okay, completed
            # 251 User not local; will forward to <forward-path>
            if(preg_match("/^[220|251]/i", $foo)) {
                $found = true;
                $this->_verifyEmailCloseSocket($socket);
                break;
            }

            $this->_verifyEmailCloseSocket($socket);
        }

        return($found);
    }

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     * @access private
     */
    function _verifyEmailCloseSocket($socket)
    {
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return(true);
    }

    /**
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     * @access private
     */
    function _verifyEmailLocalhost()
    {
        $localhost = getenv('SERVER_NAME');

        if (!strlen($localhost)) {
            $localhost = getenv('HOST');
        }

        return($localhost);
    }
}
?>
