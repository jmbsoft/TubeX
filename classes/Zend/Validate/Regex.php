<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Regex extends Zend_Validate_Abstract
{

    const NOT_MATCH = 'regexNotMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => "'%value%' does not match against pattern '%pattern%'"
    );

    protected $_messageVariables = array(
        'pattern' => '_pattern'
    );

    protected $_pattern;

    public function __construct($pattern)
    {
        $this->setPattern($pattern);
    }

    public function getPattern()
    {
        return $this->_pattern;
    }

    public function setPattern($pattern)
    {
        $this->_pattern = (string) $pattern;
        return $this;
    }

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        $status = @preg_match($this->_pattern, $valueString);
        if (false === $status) {

            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Internal error matching pattern '$this->_pattern' against value '$valueString'");
        }
        if (!$status) {
            $this->_error();
            return false;
        }
        return true;
    }

}
