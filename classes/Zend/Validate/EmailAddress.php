<?php


require_once 'Zend/Validate/Abstract.php';

require_once 'Zend/Validate/Hostname.php';

class Zend_Validate_EmailAddress extends Zend_Validate_Abstract
{
    const INVALID            = 'emailAddressInvalid';
    const INVALID_HOSTNAME   = 'emailAddressInvalidHostname';
    const INVALID_MX_RECORD  = 'emailAddressInvalidMxRecord';
    const DOT_ATOM           = 'emailAddressDotAtom';
    const QUOTED_STRING      = 'emailAddressQuotedString';
    const INVALID_LOCAL_PART = 'emailAddressInvalidLocalPart';
    const LENGTH_EXCEEDED    = 'emailAddressLengthExceeded';

    protected $_messageTemplates = array(
        self::INVALID            => "'%value%' is not a valid email address in the basic format local-part@hostname",
        self::INVALID_HOSTNAME   => "'%hostname%' is not a valid hostname for email address '%value%'",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have a valid MX record for the email address '%value%'",
        self::DOT_ATOM           => "'%localPart%' not matched against dot-atom format",
        self::QUOTED_STRING      => "'%localPart%' not matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for email address '%value%'",
        self::LENGTH_EXCEEDED    => "'%value%' exceeds the allowed length"
    );

    protected $_messageVariables = array(
        'hostname'  => '_hostname',
        'localPart' => '_localPart'
    );

    public $hostnameValidator;

    protected $_validateMx = false;

    protected $_hostname;

    protected $_localPart;

    public function __construct($allow = Zend_Validate_Hostname::ALLOW_DNS, $validateMx = false, Zend_Validate_Hostname $hostnameValidator = null)
    {
        $this->setValidateMx($validateMx);
        $this->setHostnameValidator($hostnameValidator, $allow);
    }

    public function setHostnameValidator(Zend_Validate_Hostname $hostnameValidator = null, $allow = Zend_Validate_Hostname::ALLOW_DNS)
    {
        if ($hostnameValidator === null) {
            $hostnameValidator = new Zend_Validate_Hostname($allow);
        }
        $this->hostnameValidator = $hostnameValidator;
    }

    public function validateMxSupported()
    {
        return function_exists('dns_get_mx');
    }

    public function setValidateMx($allowed)
    {
        $this->_validateMx = (bool) $allowed;
    }

    public function isValid($value)
    {
        $valueString = (string) $value;
        $matches     = array();
        $length      = true;

        $this->_setValue($valueString);

        // Split email address up and disallow '..'
        if ((strpos($valueString, '..') !== false) or
            (!preg_match('/^(.+)@([^@]+)$/', $valueString, $matches))) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_localPart = $matches[1];
        $this->_hostname  = $matches[2];

        if ((strlen($this->_localPart) > 64) || (strlen($this->_hostname) > 255)) {
            $length = false;
            $this->_error(self::LENGTH_EXCEEDED);
        }

        // Match hostname part
        $hostnameResult = $this->hostnameValidator->setTranslator($this->getTranslator())
                               ->isValid($this->_hostname);
        if (!$hostnameResult) {
            $this->_error(self::INVALID_HOSTNAME);

            // Get messages and errors from hostnameValidator
            foreach ($this->hostnameValidator->getMessages() as $code => $message) {
                $this->_messages[$code] = $message;
            }
            foreach ($this->hostnameValidator->getErrors() as $error) {
                $this->_errors[] = $error;
            }
        } else if ($this->_validateMx) {
            // MX check on hostname via dns_get_record()
            if ($this->validateMxSupported()) {
                $result = dns_get_mx($this->_hostname, $mxHosts);
                if (count($mxHosts) < 1) {
                    $hostnameResult = false;
                    $this->_error(self::INVALID_MX_RECORD);
                }
            } else {

                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Internal error: MX checking not available on this system');
            }
        }

        // First try to match the local part on the common dot-atom format
        $localResult = false;

        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d';
        if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->_localPart)) {
            $localResult = true;
        } else {
            // Try quoted string format

            // Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
            // qtext: Non white space controls, and the rest of the US-ASCII characters not
            //   including "\" or the quote character
            $noWsCtl    = '\x01-\x08\x0b\x0c\x0e-\x1f\x7f';
            $qtext      = $noWsCtl . '\x21\x23-\x5b\x5d-\x7e';
            $ws         = '\x20\x09';
            if (preg_match('/^\x22([' . $ws . $qtext . '])*[$ws]?\x22$/', $this->_localPart)) {
                $localResult = true;
            } else {
                $this->_error(self::DOT_ATOM);
                $this->_error(self::QUOTED_STRING);
                $this->_error(self::INVALID_LOCAL_PART);
            }
        }

        // If both parts valid, return true
        if ($localResult && $hostnameResult && $length) {
            return true;
        } else {
            return false;
        }
    }
}
