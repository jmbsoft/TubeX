<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Digits extends Zend_Validate_Abstract
{

    const NOT_DIGITS = 'notDigits';

    const STRING_EMPTY = 'stringEmpty';

    protected static $_filter = null;

    protected $_messageTemplates = array(
        self::NOT_DIGITS   => "'%value%' contains not only digit characters",
        self::STRING_EMPTY => "'%value%' is an empty string"
    );

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if ('' === $valueString) {
            $this->_error(self::STRING_EMPTY);
            return false;
        }

        if (null === self::$_filter) {

            require_once 'Zend/Filter/Digits.php';
            self::$_filter = new Zend_Filter_Digits();
        }

        if ($valueString !== self::$_filter->filter($valueString)) {
            $this->_error(self::NOT_DIGITS);
            return false;
        }

        return true;
    }

}
