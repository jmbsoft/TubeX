<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Identical extends Zend_Validate_Abstract
{

    const NOT_SAME      = 'notSame';
    const MISSING_TOKEN = 'missingToken';


    protected $_messageTemplates = array(
        self::NOT_SAME      => 'Tokens do not match',
        self::MISSING_TOKEN => 'No token was provided to match against',
    );

    protected $_token;

    public function __construct($token = null)
    {
        if (null !== $token) {
            $this->setToken($token);
        }
    }

    public function setToken($token)
    {
        $this->_token = (string) $token;
        return $this;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function isValid($value)
    {
        $this->_setValue($value);
        $token = $this->getToken();

        if (empty($token)) {
            $this->_error(self::MISSING_TOKEN);
            return false;
        }

        if ($value !== $token)  {
            $this->_error(self::NOT_SAME);
            return false;
        }

        return true;
    }
}
