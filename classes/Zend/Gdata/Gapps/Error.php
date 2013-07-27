<?php


require_once 'Zend/Gdata/App/Base.php';

class Zend_Gdata_Gapps_Error extends Zend_Gdata_App_Base
{
    
    // Error codes as defined at 
    // http://code.google.com/apis/apps/gdata_provisioning_api_v2.0_reference.html#appendix_d
    
    const UNKNOWN_ERROR = 1000;
    const USER_DELETED_RECENTLY = 1100;
    const USER_SUSPENDED = 1101;
    const DOMAIN_USER_LIMIT_EXCEEDED = 1200;
    const DOMAIN_ALIAS_LIMIT_EXCEEDED = 1201;
    const DOMAIN_SUSPENDED = 1202;
    const DOMAIN_FEATURE_UNAVAILABLE = 1203;
    const ENTITY_EXISTS = 1300;
    const ENTITY_DOES_NOT_EXIST = 1301;
    const ENTITY_NAME_IS_RESERVED = 1302;
    const ENTITY_NAME_NOT_VALID = 1303;
    const INVALID_GIVEN_NAME = 1400;
    const INVALID_FAMILY_NAME = 1401;
    const INVALID_PASSWORD = 1402;
    const INVALID_USERNAME = 1403;
    const INVALID_HASH_FUNCTION_NAME = 1404;
    const INVALID_HASH_DIGEST_LENGTH = 1405;
    const INVALID_EMAIL_ADDRESS = 1406;
    const INVALID_QUERY_PARAMETER_VALUE = 1407;
    const TOO_MANY_RECIPIENTS_ON_EMAIL_LIST = 1500;
    
    protected $_errorCode = null;
    protected $_reason = null;
    protected $_invalidInput = null;
    
    public function __construct($errorCode = null, $reason = null, 
            $invalidInput = null) {
        parent::__construct("Google Apps error received: $errorCode ($reason)");
        $this->_errorCode = $errorCode;
        $this->_reason = $reason;
        $this->_invalidInput = $invalidInput;
    }

    public function setErrorCode($value) {
       $this->_errorCode = $value;
    }

    public function getErrorCode() {
        return $this->_errorCode;
    }

    public function setReason($value) {
       $this->_reason = $value;
    }

    public function getReason() {
       return $this->_reason;
    }

    public function setInvalidInput($value) {
       $this->_invalidInput = $value;
    }

    public function getInvalidInput() {
       return $this->_invalidInput;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_errorCode !== null) {
            $element->setAttribute('errorCode', $this->_errorCode);
        }
        if ($this->_reason !== null) {
            $element->setAttribute('reason', $this->_reason);
        }
        if ($this->_invalidInput !== null) {
            $element->setAttribute('invalidInput', $this->_invalidInput);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'errorCode':
            $this->_errorCode = $attribute->nodeValue;
            break;
        case 'reason':
            $this->_reason = $attribute->nodeValue;
            break;
        case 'invalidInput':
            $this->_invalidInput = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function __toString() {
        return "Error " . $this->getErrorCode() . ": " . $this->getReason() .
            "\n\tInvalid Input: \"" . $this->getInvalidInput() . "\"";
    }

}
