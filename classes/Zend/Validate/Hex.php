<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Hex extends Zend_Validate_Abstract
{

    const NOT_HEX = 'notHex';

    protected $_messageTemplates = array(
        self::NOT_HEX => "'%value%' has not only hexadecimal digit characters"
    );

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if (!ctype_xdigit($valueString)) {
            $this->_error();
            return false;
        }

        return true;
    }

}
